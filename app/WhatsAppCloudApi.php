<?php

declare(strict_types=1);

namespace App;

final class WhatsAppCloudApi
{
    public function sendRequest(array $request): array
    {
        $token = (string) Config::get('WHATSAPP_ACCESS_TOKEN', '');
        $phoneNumberId = (string) Config::get('WHATSAPP_PHONE_NUMBER_ID', '');
        $recipient = (string) Config::get('WHATSAPP_RECIPIENT', '');
        $templateName = (string) Config::get('WHATSAPP_TEMPLATE_NAME', '');
        $apiVersion = (string) Config::get('WHATSAPP_API_VERSION', '');

        if (
            $token === ''
            || !preg_match('/^\d+$/', $phoneNumberId)
            || !preg_match('/^\+?\d{7,15}$/', $recipient)
            || !preg_match('/^[a-z0-9_]+$/', $templateName)
            || !preg_match('/^v\d+\.0$/', $apiVersion)
        ) {
            return ['sent' => false, 'error' => 'La integración de WhatsApp no está configurada completamente.'];
        }

        $description = function_exists('mb_substr')
            ? mb_substr((string) $request['descripcion'], 0, 900, 'UTF-8')
            : substr((string) $request['descripcion'], 0, 900);
        $parameters = [
            sprintf('#%03d', (int) $request['id']),
            (string) $request['nombre'],
            (string) $request['telefono'],
            (string) $request['correo'],
            (string) $request['servicio'],
            $description,
            date('d/m/Y H:i', strtotime((string) $request['fecha_registro'])),
        ];
        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => ltrim($recipient, '+'),
            'type' => 'template',
            'template' => [
                'name' => $templateName,
                'language' => ['code' => (string) Config::get('WHATSAPP_TEMPLATE_LANGUAGE', 'es_MX')],
                'components' => [[
                    'type' => 'body',
                    'parameters' => array_map(
                        static fn (string $value): array => ['type' => 'text', 'text' => $value],
                        $parameters
                    ),
                ]],
            ],
        ];
        $handle = curl_init(sprintf('https://graph.facebook.com/%s/%s/messages', $apiVersion, $phoneNumberId));
        if ($handle === false) {
            return ['sent' => false, 'error' => 'No se pudo iniciar la conexión con WhatsApp.'];
        }

        curl_setopt_array($handle, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $token,
                'Content-Type: application/json',
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT => 20,
        ]);
        $response = curl_exec($handle);
        $httpStatus = (int) curl_getinfo($handle, CURLINFO_HTTP_CODE);
        $curlError = curl_errno($handle);
        curl_close($handle);

        if ($response === false || $curlError !== 0) {
            return ['sent' => false, 'error' => 'No fue posible conectar con la API de WhatsApp.'];
        }

        $result = json_decode($response, true);
        if ($httpStatus >= 200 && $httpStatus < 300 && isset($result['messages'][0]['id'])) {
            return ['sent' => true, 'error' => null];
        }

        $errorCode = $result['error']['code'] ?? null;
        $errorMessage = 'La API de WhatsApp rechazó el envío (HTTP ' . $httpStatus . ').';
        if (is_int($errorCode) || is_string($errorCode)) {
            $errorMessage .= ' Código: ' . $errorCode . '.';
        }

        return ['sent' => false, 'error' => $errorMessage];
    }
}