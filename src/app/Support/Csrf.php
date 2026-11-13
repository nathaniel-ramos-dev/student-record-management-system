<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Session-backed Cross-Site Request Forgery protection.
 */
final class Csrf
{
    private const SESSION_KEY = '_csrf_token';

    public static function token(): string
    {
        if (!isset($_SESSION[self::SESSION_KEY]) || !is_string($_SESSION[self::SESSION_KEY])) {
            $_SESSION[self::SESSION_KEY] = bin2hex(random_bytes(32));
        }

        return $_SESSION[self::SESSION_KEY];
    }

    public static function field(): string
    {
        return sprintf(
            '<input type="hidden" name="_token" value="%s">',
            e(self::token()),
        );
    }

    public static function verify(?string $token): bool
    {
        if (!is_string($token) || $token === '') {
            return false;
        }

        $sessionToken = $_SESSION[self::SESSION_KEY] ?? null;

        return is_string($sessionToken) && hash_equals($sessionToken, $token);
    }

    /**
     * Abort the request with a 419 page when the submitted token is invalid.
     */
    public static function verifyOrFail(): void
    {
        $token = $_POST['_token'] ?? null;

        if (self::verify(is_string($token) ? $token : null)) {
            return;
        }

        http_response_code(403);
        View::render('errors/403', ['pageTitle' => 'Request blocked'], 'error-layout');
        exit;
    }
}
