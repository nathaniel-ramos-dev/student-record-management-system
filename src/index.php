<?php

declare(strict_types=1);

use App\Controllers\ActivityController;
use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\ProfileController;
use App\Controllers\StudentController;
use App\Repositories\ActivityLogRepository;
use App\Repositories\StudentRepository;
use App\Repositories\UserRepository;
use App\Support\Csrf;
use App\Support\Database;
use App\Support\Router;

require __DIR__ . '/app/bootstrap.php';

$pdo = Database::connection();

$studentRepository = new StudentRepository($pdo);
$activityRepository = new ActivityLogRepository($pdo);
$userRepository = new UserRepository($pdo);

$auth = new AuthController();
$dashboard = new DashboardController($studentRepository, $activityRepository);
$students = new StudentController($studentRepository, $activityRepository);
$activity = new ActivityController($activityRepository);
$profile = new ProfileController($userRepository);

$router = new Router();

/*
|--------------------------------------------------------------------------
| Public routes
|--------------------------------------------------------------------------
*/
$router->get('/login', [$auth, 'showLogin']);
$router->post('/login', [$auth, 'login']);

$router->get('/health', static function (): void {
    header('Content-Type: text/plain; charset=UTF-8');

    try {
        Database::connection()->query('SELECT 1');
        echo 'SRMS OK - database connected';
    } catch (Throwable $exception) {
        error_log('Health check failed: ' . $exception->getMessage());
        http_response_code(503);
        echo 'SRMS ERROR - database unavailable';
    }
});

/*
|--------------------------------------------------------------------------
| Authenticated routes
|--------------------------------------------------------------------------
*/
$router->post('/logout', [$auth, 'logout']);

$router->get('/', [$dashboard, 'index']);
$router->get('/dashboard', [$dashboard, 'index']);

$router->get('/students', [$students, 'index']);
$router->get('/students/export', [$students, 'export']);
$router->get('/students/create', [$students, 'create']);
$router->post('/students', [$students, 'store']);
$router->get('/students/{id}/edit', [$students, 'edit']);
$router->post('/students/{id}/delete', [$students, 'destroy']);
$router->post('/students/{id}', [$students, 'update']);
$router->get('/students/{id}', [$students, 'show']);

$router->get('/activity', [$activity, 'index']);

$router->get('/profile/password', [$profile, 'editPassword']);
$router->post('/profile/password', [$profile, 'updatePassword']);

/*
|--------------------------------------------------------------------------
| Dispatch
|--------------------------------------------------------------------------
| Every state-changing request must carry a valid CSRF token.
*/
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    Csrf::verifyOrFail();
}

$router->dispatch();
