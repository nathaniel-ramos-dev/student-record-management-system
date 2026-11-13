<?php

declare(strict_types=1);

use App\Support\Config;
use App\Support\Csrf;
use App\Support\Router;
use App\Support\Value;

/**
 * Global helpers that are available in every view and controller.
 */

function base_path(string $path = ''): string
{
    $base = dirname(__DIR__);

    return $path === '' ? $base : $base . '/' . ltrim($path, '/');
}

/**
 * Detect HTTPS even when the application sits behind a reverse proxy such as
 * Render's load balancer (which terminates TLS and sets X-Forwarded-Proto).
 */
function is_https(): bool
{
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== '' && $_SERVER['HTTPS'] !== 'off') {
        return true;
    }

    $forwarded = strtolower(trim(explode(',', Value::string($_SERVER, 'HTTP_X_FORWARDED_PROTO'))[0]));

    if ($forwarded === 'https') {
        return true;
    }

    return Value::string($_SERVER, 'SERVER_PORT') === '443';
}

function base_url(): string
{
    static $baseUrl = null;

    if (is_string($baseUrl)) {
        return $baseUrl;
    }

    $configured = Config::string('app.url');

    if ($configured !== '') {
        return $baseUrl = rtrim($configured, '/');
    }

    $scheme = is_https() ? 'https' : 'http';
    $host = Value::string($_SERVER, 'HTTP_HOST', 'localhost');
    $script = str_replace('\\', '/', Value::string($_SERVER, 'SCRIPT_NAME', '/index.php'));
    $directory = rtrim(dirname($script), '/');

    if ($directory === '.' || $directory === '/') {
        $directory = '';
    }

    return $baseUrl = $scheme . '://' . $host . $directory;
}

function url(string $path = ''): string
{
    $path = ltrim($path, '/');

    return base_url() . ($path === '' ? '/' : '/' . $path);
}

function redirect(string $path): never
{
    $target = str_starts_with($path, 'http://') || str_starts_with($path, 'https://')
        ? $path
        : url($path);

    header('Location: ' . $target, true, 302);
    exit;
}

function e(mixed $value): string
{
    return htmlspecialchars(Value::toString($value, trim: false), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * @param array<string, mixed> $values
 */
function old(array $values, string $key, string $default = ''): string
{
    return Value::string($values, $key, $default);
}

/**
 * @param array<string, mixed> $values
 */
function selected(array $values, string $key, string $expected): string
{
    return old($values, $key) === $expected ? 'selected' : '';
}

/**
 * @param array<string, mixed> $values
 */
function checked(array $values, string $key, bool $expected = true): string
{
    return Value::bool($values, $key) === $expected ? 'checked' : '';
}

function format_date(?string $date, string $format = 'M j, Y'): string
{
    if ($date === null || $date === '' || $date === '0000-00-00') {
        return '—';
    }

    try {
        return (new DateTimeImmutable($date))->format($format);
    } catch (\Exception) {
        return '—';
    }
}

function initials(string $firstName, string $lastName): string
{
    $first = mb_substr(trim($firstName), 0, 1);
    $last = mb_substr(trim($lastName), 0, 1);

    return mb_strtoupper($first . $last);
}

function name_initials(string $name): string
{
    $parts = preg_split('/\s+/', trim($name)) ?: [];

    if ($parts === []) {
        return '?';
    }

    if (count($parts) === 1) {
        return mb_strtoupper(mb_substr($parts[0], 0, 1));
    }

    return mb_strtoupper(
        mb_substr($parts[0], 0, 1) . mb_substr($parts[count($parts) - 1], 0, 1),
    );
}

function is_active_path(string ...$paths): bool
{
    $current = Router::currentPath();

    foreach ($paths as $path) {
        if ($path === '/' && $current === '/') {
            return true;
        }

        if ($path !== '/' && ($current === $path || str_starts_with($current, $path . '/'))) {
            return true;
        }
    }

    return false;
}

function csrf_field(): string
{
    return Csrf::field();
}

/**
 * @param array<string, string> $errors
 */
function field_error(array $errors, string $field): string
{
    if (!isset($errors[$field])) {
        return '';
    }

    return '<div class="invalid-feedback d-block">' . e($errors[$field]) . '</div>';
}

/**
 * @param array<string, string> $errors
 */
function field_class(array $errors, string $field): string
{
    return isset($errors[$field]) ? ' is-invalid' : '';
}
