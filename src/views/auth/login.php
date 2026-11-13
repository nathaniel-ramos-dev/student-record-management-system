<?php

declare(strict_types=1);

use App\Support\Flash;

$flashMessages = Flash::pull();
?>
<div class="auth-card-heading">
    <h2>Welcome back</h2>
    <p>Sign in with your staff account to continue.</p>
</div>

<?php foreach ($flashMessages as $flash): ?>
    <?php $type = $flash['type'] === 'error' ? 'danger' : $flash['type']; ?>
    <div class="alert alert-<?= e($type) ?> app-alert" role="alert">
        <i class="bi <?= e($flash['type'] === 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-circle-fill') ?>"></i>
        <span><?= e($flash['message']) ?></span>
    </div>
<?php endforeach; ?>

<form method="post" action="<?= e(url('login')) ?>" novalidate>
    <?= csrf_field() ?>

    <div class="mb-3">
        <label for="username" class="form-label">Username or e-mail</label>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-person"></i></span>
            <input type="text" class="form-control" id="username" name="username"
                   placeholder="admin" autocomplete="username" required autofocus>
        </div>
    </div>

    <div class="mb-4">
        <label for="password" class="form-label">Password</label>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-lock"></i></span>
            <input type="password" class="form-control" id="password" name="password"
                   placeholder="Enter your password" autocomplete="current-password" required>
            <button class="btn btn-outline-secondary password-toggle" type="button"
                    data-target="password" aria-label="Show password">
                <i class="bi bi-eye"></i>
            </button>
        </div>
    </div>

    <button type="submit" class="btn btn-primary btn-lg w-100">
        <i class="bi bi-box-arrow-in-right"></i> Sign in
    </button>
</form>

<div class="auth-demo">
    <div class="auth-demo-title"><i class="bi bi-info-circle"></i> Demo accounts</div>
    <div class="auth-demo-row"><span>Administrator</span><code>admin / Admin@12345</code></div>
    <div class="auth-demo-row"><span>Staff</span><code>staff / Staff@12345</code></div>
</div>

<p class="auth-credit">Local development build &middot; SRMS v1.0</p>
