<?php

declare(strict_types=1);

namespace App\Support;

use RuntimeException;

/**
 * Tiny regular-expression router with support for {parameter} placeholders.
 */
final class Router
{
    /**
     * @var list<array{method: string, pattern: string, handler: callable|array{0: object, 1: string}}>
     */
    private array $routes = [];

    /**
     * @param callable|array{0: object, 1: string} $handler
     */
    public function get(string $path, callable|array $handler): void
    {
        $this->add('GET', $path, $handler);
    }

    /**
     * @param callable|array{0: object, 1: string} $handler
     */
    public function post(string $path, callable|array $handler): void
    {
        $this->add('POST', $path, $handler);
    }

    /**
     * @param callable|array{0: object, 1: string} $handler
     */
    private function add(string $method, string $path, callable|array $handler): void
    {
        $path = '/' . trim($path, '/');

        $this->routes[] = [
            'method'  => $method,
            'pattern' => $path === '/' ? '/' : $path,
            'handler' => $handler,
        ];
    }

    public function dispatch(): void
    {
        $method = strtoupper(Value::string($_SERVER, 'REQUEST_METHOD', 'GET'));
        $path = self::currentPath();

        $allowedMethods = [];

        foreach ($this->routes as $route) {
            $parameters = $this->match($route['pattern'], $path);

            if ($parameters === null) {
                continue;
            }

            if ($route['method'] !== $method) {
                $allowedMethods[] = $route['method'];
                continue;
            }

            $handler = $route['handler'];

            if (is_array($handler)) {
                [$controller, $action] = $handler;

                if (!is_callable([$controller, $action])) {
                    throw new RuntimeException(sprintf('Route handler %s::%s is not callable.', $controller::class, $action));
                }

                $controller->{$action}(...array_values($parameters));

                return;
            }

            $handler(...array_values($parameters));

            return;
        }

        if ($allowedMethods !== []) {
            http_response_code(405);
            header('Allow: ' . implode(', ', array_unique($allowedMethods)));
            View::render('errors/405', ['pageTitle' => 'Method not allowed'], 'error-layout');

            return;
        }

        http_response_code(404);
        View::render('errors/404', ['pageTitle' => 'Page not found'], 'error-layout');
    }

    /**
     * Resolve the request path, stripping the sub-directory the app is hosted in.
     */
    public static function currentPath(): string
    {
        $uri = parse_url(Value::string($_SERVER, 'REQUEST_URI', '/'), PHP_URL_PATH);
        $uri = is_string($uri) ? $uri : '/';

        $scriptName = str_replace('\\', '/', Value::string($_SERVER, 'SCRIPT_NAME', '/index.php'));
        $basePath = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');
        $basePath = $basePath === '.' ? '' : $basePath;

        if ($basePath !== '' && str_starts_with($uri, $basePath)) {
            $uri = substr($uri, strlen($basePath));
        }

        // Support the non-rewritten fallback: /index.php/students
        if (str_starts_with($uri, '/index.php')) {
            $uri = substr($uri, strlen('/index.php'));
        }

        $uri = '/' . trim($uri, '/');

        return $uri === '//' ? '/' : $uri;
    }

    /**
     * @return array<string, string>|null
     */
    private function match(string $pattern, string $path): ?array
    {
        $regex = preg_replace('#\{([a-zA-Z_][a-zA-Z0-9_]*)\}#', '(?P<$1>[^/]+)', $pattern);

        if (!is_string($regex)) {
            return null;
        }

        if (preg_match('#^' . $regex . '$#', $path, $matches) !== 1) {
            return null;
        }

        $parameters = [];

        foreach ($matches as $key => $value) {
            if (is_string($key)) {
                $parameters[$key] = $value;
            }
        }

        return $parameters;
    }
}
