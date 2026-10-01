<?php

declare(strict_types=1);

namespace App;

final class RateLimiter
{
    public static function allow(string $key, int $limitPerMinute, int $windowSeconds): bool
    {
        $file = sys_get_temp_dir() . '/max_rate_' . md5($key) . '.json';

        $now = time();
        $data = ['requests' => []];

        if (file_exists($file)) {
            $json = @file_get_contents($file);
            if ($json !== false) {
                $data = json_decode($json, true) ?: ['requests' => []];
            }
        }

        $requests = $data['requests'] ?? [];
        $filtered = [];

        foreach ($requests as $timestamp) {
            if ($timestamp > $now - $windowSeconds) {
                $filtered[] = $timestamp;
            }
        }

        if (count($filtered) >= $limitPerMinute) {
            return false;
        }

        $filtered[] = $now;
        $data['requests'] = $filtered;

        @file_put_contents($file, json_encode($data));

        return true;
    }
}
