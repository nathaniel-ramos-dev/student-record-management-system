<?php

declare(strict_types=1);

use App\Support\Config;

/** @var string $content */
$appName = (string) Config::get('app.name', 'SRMS');
$appFullName = (string) Config::get('app.full_name', 'Student Record Management System');
$school = (string) Config::get('app.school', '');
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle ?? 'Sign in') ?> &middot; <?= e($appName) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?= e(url('assets/css/style.css')) ?>" rel="stylesheet">
</head>
<body class="auth-body">
<div class="auth-shell">
    <section class="auth-visual">
        <div class="auth-visual-inner">
            <div class="auth-brand">
                <div class="brand-mark brand-mark-lg"><i class="bi bi-mortarboard-fill"></i></div>
                <div>
                    <span class="brand-name brand-name-light"><?= e($appName) ?></span>
                    <span class="brand-sub brand-sub-light"><?= e($appFullName) ?></span>
                </div>
            </div>

            <div class="auth-copy">
                <span class="auth-eyebrow"><?= e($school) ?></span>
                <h1>Every student record,<br>organised in one place.</h1>
                <p>Manage enrolment information, keep an audit trail of changes, and find any student in seconds.</p>

                <ul class="auth-features">
                    <li><i class="bi bi-shield-check"></i> Secure, session-based sign in</li>
                    <li><i class="bi bi-database-check"></i> MySQL-backed student records</li>
                    <li><i class="bi bi-file-earmark-spreadsheet"></i> One-click CSV export</li>
                </ul>
            </div>

            <p class="auth-visual-footer"><?= e($appName) ?> &middot; <?= date('Y') ?></p>
        </div>
    </section>

    <section class="auth-panel">
        <div class="auth-card">
            <?= $content ?>
        </div>
    </section>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.querySelectorAll('.password-toggle').forEach(function (button) {
        button.addEventListener('click', function () {
            var input = document.getElementById(button.dataset.target);
            if (!input) return;
            var isPassword = input.type === 'password';
            input.type = isPassword ? 'text' : 'password';
            button.querySelector('i').className = isPassword ? 'bi bi-eye-slash' : 'bi bi-eye';
        });
    });
</script>
</body>
</html>
