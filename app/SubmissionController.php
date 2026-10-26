<?php

declare(strict_types=1);

namespace App;

use Throwable;

final class SubmissionController
{
    public static function register(Router $router): void
    {
        $router->add('POST', '/solicitudes', static function (): void {
            self::store();
        });
    }

    private static function store(): void
    {
        header('Cache-Control: no-store');

        if (!RateLimiter::allow('submission:' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'), 10, 60)) {
            Response::json(['error' => 'Has enviado varias solicitudes. Inténtalo más tarde.'], 429);
        }
        if (!Session::verifyCsrf((string) ($_POST['csrf_token'] ?? ''))) {
            Response::json(['error' => 'La sesión del formulario venció. Recarga la página e inténtalo de nuevo.'], 419);
        }

        $submissionKey = (string) ($_POST['submission_key'] ?? '');
        $data = [
            'nombre' => trim((string) ($_POST['nombre'] ?? '')),
            'telefono' => trim((string) ($_POST['telefono'] ?? '')),
            'correo' => trim((string) ($_POST['correo'] ?? '')),
            'servicio' => trim((string) ($_POST['servicio'] ?? '')),
            'descripcion' => trim((string) ($_POST['mensaje'] ?? '')),
        ];
        $validServices = [
            'Asesoría especializada',
            'Programa interno',
            'Análisis de riesgo',
            'Dictamen técnico',
            'Capacitación',
            'Firma pericial',
            'Otro servicio',
        ];
        $phoneDigits = preg_replace('/\D+/', '', $data['telefono']) ?? '';

        if (
            strlen($submissionKey) !== 64
            || !ctype_xdigit($submissionKey)
            || $data['nombre'] === ''
            || strlen($data['nombre']) > 150
            || preg_match('/[\x00-\x1F\x7F]/', $data['nombre'])
            || strlen($data['telefono']) > 30
            || !preg_match('/^[0-9+(). -]{7,30}$/D', $data['telefono'])
            || strlen($phoneDigits) < 7
            || strlen($phoneDigits) > 15
            || !filter_var($data['correo'], FILTER_VALIDATE_EMAIL)
            || strlen($data['correo']) > 254
            || !in_array($data['servicio'], $validServices, true)
            || $data['descripcion'] === ''
            || strlen($data['descripcion']) > 12000
            || preg_match('//u', $data['nombre']) !== 1
            || preg_match('//u', $data['correo']) !== 1
            || preg_match('//u', $data['descripcion']) !== 1
        ) {
            Response::json(['error' => 'Revisa los campos: todos son obligatorios y deben tener un formato válido.'], 422);
        }

        try {
            $repository = new SubmissionRepository(Database::connection());
            $result = $repository->create($data, strtolower($submissionKey));
            $request = $result['request'];

            if ($result['created']) {
                $delivery = (new WhatsAppCloudApi())->sendRequest($request);
                $repository->markWhatsAppResult((int) $request['id'], $delivery['sent'], $delivery['error']);
                if (!$delivery['sent']) {
                    error_log('WhatsApp notification for request #' . $request['id'] . ' failed: ' . $delivery['error']);
                }
                $request = $repository->find((int) $request['id']) ?? $request;
            }

            Response::json([
                'ok' => true,
                'id' => (int) $request['id'],
                'whatsapp_status' => $request['whatsapp_estado'],
                'submission_key' => bin2hex(random_bytes(32)),
            ], $result['created'] ? 201 : 200);
        } catch (Throwable $exception) {
            error_log('Unable to save request: ' . $exception->getMessage());
            Response::json(['error' => 'No fue posible registrar la solicitud. Inténtalo de nuevo más tarde.'], 503);
        }
    }
}