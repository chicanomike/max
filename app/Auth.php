<?php

declare(strict_types=1);

namespace App;

final class Auth
{
    public static function authenticate(string $email, string $password): ?array
    {
        $demoUser = [
            'id' => 1,
            'email' => 'admin@max.local',
            'password_hash' => password_hash('admin123', PASSWORD_DEFAULT),
        ];

        if (strtolower($email) !== strtolower($demoUser['email'])) {
            return null;
        }

        if (!password_verify($password, $demoUser['password_hash'])) {
            return null;
        }

        return [
            'id' => $demoUser['id'],
            'email' => $demoUser['email'],
        ];
    }

    public static function issueToken(string $userId, string $email): string
    {
        $header = base64_encode(json_encode(['alg' => 'HS256', 'typ' => 'JWT'], JSON_UNESCAPED_SLASHES));
        $payload = [
            'sub' => $userId,
            'email' => $email,
            'iat' => time(),
            'exp' => time() + 3600,
        ];
        $payloadEncoded = base64_encode(json_encode($payload, JSON_UNESCAPED_SLASHES));
        $secret = Config::get('JWT_SECRET', 'development-secret');
        $signature = hash_hmac('sha256', $header . '.' . $payloadEncoded, $secret, true);

        return $header . '.' . $payloadEncoded . '.' . base64_encode($signature);
    }

    public static function verifyToken(string $authorizationHeader): ?array
    {
        if (!str_starts_with($authorizationHeader, 'Bearer ')) {
            return null;
        }

        $token = trim(substr($authorizationHeader, 7));
        $parts = explode('.', $token);

        if (count($parts) !== 3) {
            return null;
        }

        [$header, $payload, $signature] = $parts;
        $secret = Config::get('JWT_SECRET', 'development-secret');
        $expected = base64_encode(hash_hmac('sha256', $header . '.' . $payload, $secret, true));

        if (!hash_equals($expected, $signature)) {
            return null;
        }

        $decodedPayload = json_decode(base64_decode($payload), true);
        if (!is_array($decodedPayload)) {
            return null;
        }

        if (($decodedPayload['exp'] ?? 0) < time()) {
            return null;
        }

        return $decodedPayload;
    }
}
