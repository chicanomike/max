<?php

declare(strict_types=1);

namespace App;

use PDO;
use PDOException;

final class Database
{
    private static ?PDO $connection = null;

    public static function connection(): PDO
    {
        if (self::$connection instanceof PDO) {
            return self::$connection;
        }

        $host = (string) Config::get('DB_HOST', '127.0.0.1');
        $database = (string) Config::get('DB_NAME', 'sistema_web');
        $username = (string) Config::get('DB_USER', 'root');
        $password = (string) Config::get('DB_PASS', '');
        $dsn = sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', $host, $database);

        try {
            self::$connection = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $exception) {
            error_log('Database connection failed: ' . $exception->getMessage());
            throw new PDOException('Database connection is unavailable.');
        }

        return self::$connection;
    }
}