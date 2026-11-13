<?php

declare(strict_types=1);

use App\Support\Auth;
use App\Support\Config;
use App\Support\Flash;

/** @var string $content */
$currentUser = Auth::user();
$flashMessages = Flash::pull();
$appName = (string) Config::get('app.name', 'SRMS');
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Student Record Management System">
    <title><?= e($pageTitle ?? 'Dashboard') ?> &middot; <?= e($appName) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?= e(url('assets/css/style.css')) ?>" rel="stylesheet">
</head>
<body>
<div class="app-shell">
    <div class="sidebar-backdrop" id="sidebarBackdrop"></div>

    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <div class="brand-mark"><i class="bi bi-mortarboard-fill"></i></div>
            <div class="brand-text">
                <span class="brand-name"><?= e($appName) ?></span>
                <span class="brand-sub">Student Records</span>
            </div>
        </div>

        <nav class="sidebar-nav">
            <span class="nav-label">Main menu</span>

            <a class="nav-link <?= is_active_path('/') ? 'active' : '' ?>" href="<?= e(url('/')) ?>">
                <i class="bi bi-grid-1x2-fill"></i>
                <span>Dashboard</span>
            </a>

            <a class="nav-link <?= is_active_path('/students') && !is_active_path('/students/create') ? 'active' : '' ?>"
               href="<?= e(url('students')) ?>">
                <i class="bi bi-people-fill"></i>
                <span>Students</span>
            </a>

            <a class="nav-link <?= is_active_path('/students/create') ? 'active' : '' ?>"
               href="<?= e(url('students/create')) ?>">
                <i class="bi bi-person-plus-fill"></i>
                <span>Add student</span>
            </a>

            <span class="nav-label mt-3">Monitoring</span>

            <a class="nav-link <?= is_active_path('/activity') ? 'active' : '' ?>" href="<?= e(url('activity')) ?>">
                <i class="bi bi-clock-history"></i>
                <span>Activity log</span>
            </a>
        </nav>

        <div class="sidebar-footer">
            <div class="sidebar-user">
                <span class="avatar avatar-sm"><?= e(name_initials((string) ($currentUser['full_name'] ?? 'User'))) ?></span>
                <span class="sidebar-user-text">
                    <strong><?= e($currentUser['full_name'] ?? 'User') ?></strong>
                    <small><?= e(ucfirst((string) ($currentUser['role'] ?? 'staff'))) ?></small>
                </span>
            </div>
            <form method="post" action="<?= e(url('logout')) ?>">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-sidebar-logout">
                    <i class="bi bi-box-arrow-left"></i> Sign out
                </button>
            </form>
            <p class="sidebar-copy">&copy; <?= date('Y') ?> <?= e($appName) ?></p>
        </div>
    </aside>

    <div class="app-main">
        <header class="topbar">
            <button class="btn-icon d-lg-none" type="button" id="sidebarToggle" aria-label="Toggle menu">
                <i class="bi bi-list"></i>
            </button>

            <div class="topbar-heading">
                <h1><?= e($pageTitle ?? 'Dashboard') ?></h1>
                <p><?= e($pageSubtitle ?? '') ?></p>
            </div>

            <div class="topbar-actions">
                <a class="btn btn-primary btn-sm d-none d-sm-inline-flex" href="<?= e(url('students/create')) ?>">
                    <i class="bi bi-plus-lg"></i> New student
                </a>

                <div class="dropdown">
                    <button class="user-chip dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <span class="avatar avatar-sm"><?= e(name_initials((string) ($currentUser['full_name'] ?? 'User'))) ?></span>
                        <span class="user-chip-text">
                            <strong><?= e($currentUser['full_name'] ?? 'User') ?></strong>
                            <small><?= e(ucfirst((string) ($currentUser['role'] ?? 'staff'))) ?></small>
                        </span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                        <li class="dropdown-header">
                            <div class="fw-semibold"><?= e($currentUser['full_name'] ?? 'User') ?></div>
                            <div class="small text-muted"><?= e($currentUser['email'] ?? '') ?></div>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item" href="<?= e(url('profile/password')) ?>">
                                <i class="bi bi-key me-2"></i>Change password
                            </a>
                        </li>
                        <li>
                            <form method="post" action="<?= e(url('logout')) ?>">
                                <?= csrf_field() ?>
                                <button class="dropdown-item text-danger" type="submit">
                                    <i class="bi bi-box-arrow-left me-2"></i>Sign out
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </header>

        <main class="content">
            <?php foreach ($flashMessages as $flash): ?>
                <?php
                $type = in_array($flash['type'], ['success', 'error', 'info', 'warning'], true)
                    ? $flash['type']
                    : 'info';
                $icons = [
                    'success' => 'bi-check-circle-fill',
                    'error'   => 'bi-exclamation-octagon-fill',
                    'info'    => 'bi-info-circle-fill',
                    'warning' => 'bi-exclamation-triangle-fill',
                ];
                ?>
                <div class="alert alert-<?= e($type) ?> alert-dismissible fade show app-alert" role="alert">
                    <i class="bi <?= e($icons[$type]) ?>"></i>
                    <span><?= e($flash['message']) ?></span>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endforeach; ?>

            <?= $content ?>
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    (function () {
        var sidebar = document.getElementById('sidebar');
        var toggle = document.getElementById('sidebarToggle');
        var backdrop = document.getElementById('sidebarBackdrop');

        function closeSidebar() {
            if (sidebar) sidebar.classList.remove('show');
            if (backdrop) backdrop.classList.remove('show');
        }

        if (toggle) {
            toggle.addEventListener('click', function () {
                sidebar.classList.toggle('show');
                backdrop.classList.toggle('show');
            });
        }

        if (backdrop) backdrop.addEventListener('click', closeSidebar);

        document.querySelectorAll('.sidebar a').forEach(function (link) {
            link.addEventListener('click', closeSidebar);
        });
    })();
</script>
</body>
</html>
