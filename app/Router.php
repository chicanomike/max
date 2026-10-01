<?php

declare(strict_types=1);

namespace App;

final class Router
{
    private array $routes = [];

    public function add(string $method, string $path, callable $handler): void
    {
        $routeKey = trim($path, '/');
        $routeKey = $routeKey === '' ? '/' : $routeKey;

        $this->routes[strtoupper($method)][$routeKey] = $handler;
    }

    public function dispatch(string $method, string $uri): void
    {
        $path = parse_url($uri, PHP_URL_PATH) ?? '/';

        $path = preg_replace('#^/MAX#', '', $path) ?? $path;
        $path = preg_replace('#^/public#', '', $path) ?? $path;

        $normalized = trim($path, '/');
        $routeKey = $normalized === '' ? '/' : $normalized;

        $routes = $this->routes[strtoupper($method)] ?? [];
        $route = $routes[$routeKey] ?? null;

        if ($route === null) {
            Response::json(['error' => 'Ruta no encontrada.'], 404);
        }

        $route();
    }
}
