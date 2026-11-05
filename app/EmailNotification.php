<?php

declare(strict_types=1);

namespace App;

use PHPMailer\PHPMailer\Exception as MailException;
use PHPMailer\PHPMailer\PHPMailer;

final class EmailNotification
{
    public function sendRequest(array $request): array
    {
        $recipient = (string) Config::get('NOTIFICATION_EMAIL_RECIPIENT', 'programas.internos.max@gmail.com');
        $sender = (string) Config::get('NOTIFICATION_EMAIL_FROM', '');
        $smtpHost = (string) Config::get('SMTP_HOST', '');
        $smtpPort = filter_var(Config::get('SMTP_PORT', ''), FILTER_VALIDATE_INT);
        $smtpUsername = (string) Config::get('SMTP_USERNAME', '');
        $smtpPassword = (string) Config::get('SMTP_PASSWORD', '');
        $smtpEncryption = (string) Config::get('SMTP_ENCRYPTION', 'tls');

        if (
            !filter_var($recipient, FILTER_VALIDATE_EMAIL)
            || !filter_var($sender, FILTER_VALIDATE_EMAIL)
            || $smtpHost === ''
            || $smtpPort === false
            || $smtpPort < 1
            || $smtpPort > 65535
            || !filter_var($smtpUsername, FILTER_VALIDATE_EMAIL)
            || $smtpPassword === ''
            || !in_array($smtpEncryption, ['tls', 'ssl'], true)
        ) {
            return ['sent' => false, 'error' => 'Configura el remitente y las credenciales SMTP para enviar notificaciones por correo.'];
        }

        $subject = 'Nueva solicitud de asesoría - MAX Asesoría Empresarial';
        $bodyLines = [
            'Hola,',
            'Tienes una nueva solicitud de asesoría en MAX Asesoría Empresarial.',
            '',
        ];
        $fields = [
            'Nombre' => $request['nombre'] ?? null,
            'Servicio solicitado' => $request['servicio'] ?? null,
            'Fecha de registro' => null,
            'Teléfono' => $request['telefono'] ?? null,
            'Correo' => $request['correo'] ?? null,
        ];
        if (!empty($request['fecha_registro'])) {
            $timestamp = strtotime((string) $request['fecha_registro']);
            $fields['Fecha de registro'] = $timestamp === false ? null : date('d/m/Y H:i', $timestamp);
        }
        foreach ($fields as $label => $value) {
            if ($value !== null && trim((string) $value) !== '') {
                $bodyLines[] = $label . ': ' . (string) $value;
            }
        }
        $bodyLines = array_merge($bodyLines, [
            '',
            'Revisa la solicitud en administración para darle seguimiento.',
            'MAX Asesoría Empresarial',
        ]);
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = $smtpHost;
        $mail->Port = $smtpPort;
        $mail->SMTPAuth = true;
        $mail->Username = $smtpUsername;
        $mail->Password = $smtpPassword;
        $mail->SMTPSecure = $smtpEncryption === 'ssl'
            ? PHPMailer::ENCRYPTION_SMTPS
            : PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Timeout = 20;
        $mail->CharSet = PHPMailer::CHARSET_UTF8;
        $mail->setFrom($sender, 'MAX Asesoría Empresarial');
        $mail->addAddress($recipient);
        if (!empty($request['correo']) && filter_var($request['correo'], FILTER_VALIDATE_EMAIL)) {
            $mail->addReplyTo((string) $request['correo']);
        }
        $mail->Subject = $subject;
        $mail->Body = implode("\r\n", $bodyLines);
        $mail->send();

        return ['sent' => true, 'error' => null];
    }
}
