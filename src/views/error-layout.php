<?php

declare(strict_types=1);

use App\Support\Config;

/** @var string $content */
$appName = (string) Config::get('app.name', 'SRMS');
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle ?? 'Error') ?> &middot; <?= e($appName) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?= e(url('assets/css/style.css')) ?>" rel="stylesheet">
</head>
<body class="error-body">
<div class="error-card">
    <div class="error-brand">
        <div class="brand-mark"><i class="bi bi-mortarboard-fill"></i></div>
        <span class="brand-name text-dark"><?= e($appName) ?></span>
    </div>
    <?= $content ?>
</div>
</body>
</html>
