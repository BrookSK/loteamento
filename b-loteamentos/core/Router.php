<?php
declare(strict_types=1);

namespace Core;

final class Router
{
    private array $routes = [
        'GET' => [],
        'POST' => [],
    ];

    public function get(string $path, array $handler): void
    {
        $this->routes['GET'][] = $this->compile($path, $handler);
    }

    public function post(string $path, array $handler): void
    {
        $this->routes['POST'][] = $this->compile($path, $handler);
    }

    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $route = (string)($_GET['route'] ?? '/');
        $path = $this->normalize(parse_url($route, PHP_URL_PATH) ?: '/');

        $match = $this->match($method, $path);
        if ($match === null) {
            http_response_code(404);
            echo '404 - Página não encontrada';
            return;
        }

        $handler = $match['handler'];
        $params = $match['params'];

        [$controllerName, $methodName] = $handler;
        $controllerClass = '\\Controllers\\' . $controllerName;

        if (!class_exists($controllerClass)) {
            http_response_code(500);
            echo 'Controller não encontrado.';
            return;
        }

        $controller = new $controllerClass();
        if (!method_exists($controller, $methodName)) {
            http_response_code(500);
            echo 'Método do controller não encontrado.';
            return;
        }

        $controller->$methodName($params);
    }

    private function normalize(string $path): string
    {
        $path = '/' . trim($path, '/');
        return $path === '/' ? '/' : rtrim($path, '/');
    }

    private function compile(string $path, array $handler): array
    {
        $path = $this->normalize($path);

        $paramNames = [];
        $regex = preg_replace_callback('/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/', function (array $m) use (&$paramNames): string {
            $paramNames[] = $m[1];
            return '([^/]+)';
        }, $path);

        $regex = '#^' . $regex . '$#';

        return [
            'path' => $path,
            'regex' => $regex,
            'params' => $paramNames,
            'handler' => $handler,
        ];
    }

    private function match(string $method, string $path): ?array
    {
        $routes = $this->routes[$method] ?? [];

        foreach ($routes as $route) {
            if (!isset($route['regex'], $route['handler'], $route['params'])) {
                continue;
            }

            if (!preg_match($route['regex'], $path, $m)) {
                continue;
            }

            array_shift($m);
            $params = [];
            foreach ($route['params'] as $idx => $name) {
                $params[$name] = $m[$idx] ?? null;
            }

            return [
                'handler' => $route['handler'],
                'params' => $params,
            ];
        }

        return null;
    }
}
