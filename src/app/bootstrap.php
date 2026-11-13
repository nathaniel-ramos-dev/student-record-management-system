<?php

declare(strict_types=1);

use App\Support\Config;
use App\Support\View;

/**
 * Application bootstrap: autoloading, configuration, session and error handling.
 */

define('BASE_PATH', dirname(__DIR__));

spl_autoload_register(static function (string $class): void {
    $prefix = 'App\\';

    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $relative = substr($class, strlen($prefix));
    $file = BASE_PATH . '/app/' . str_replace('\\', '/', $relative) . '.php';

    if (is_file($file)) {
        require $file;
    }
});

require BASE_PATH . '/app/helpers.php';

Config::load(BASE_PATH . '/config/config.php');

date_default_timezone_set(Config::string('app.timezone', 'Asia/Manila'));

$debug = Config::bool('app.debug');

error_reporting(E_ALL);
ini_set('display_errors', $debug ? '1' : '0');
ini_set('log_errors', '1');

if (session_status() !== PHP_SESSION_ACTIVE) {
    $https = is_https();

    session_name(Config::string('app.session_name', 'srms_session'));
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => $https,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    ini_set('session.use_strict_mode', '1');
    session_start();
}

set_exception_handler(static function (Throwable $exception) use ($debug): void {
    error_log(sprintf(
        "%s: %s in %s:%d\n%s",
        $exception::class,
        $exception->getMessage(),
        $exception->getFile(),
        $exception->getLine(),
        $exception->getTraceAsString(),
    ));

    if (!headers_sent()) {
        http_response_code(500);
        header('Content-Type: text/html; charset=UTF-8');
    }

    if ($debug) {
        echo '<h1>Application error</h1>';
        echo '<p><strong>' . e($exception::class) . '</strong>: ' . e($exception->getMessage()) . '</p>';
        echo '<pre style="white-space:pre-wrap;background:#111;color:#eee;padding:1rem;border-radius:8px;">'
            . e($exception->getTraceAsString()) . '</pre>';

        return;
    }

    View::render('errors/500', ['pageTitle' => 'Server error'], 'error-layout');
});
