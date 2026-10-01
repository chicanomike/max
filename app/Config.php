<?php

declare(strict_types=1);

namespace App;

final class Config
{
    private static array $config = [];

    public static function load(): void
    {
        $envFile = dirname(__DIR__) . '/.env';

        if (!file_exists($envFile)) {
            return;
        }

        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            return;
        }

        foreach ($lines as $line) {
            if (str_starts_with(trim($line), '#')) {
                continue;
            }

            [$key, $value] = array_pad(explode('=', $line, 2), 2, '');
            self::$config[trim($key)] = trim($value, " \n\r\t\v\0\"'");
        }
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return self::$config[$key] ?? $default;
    }
}
