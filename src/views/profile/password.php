<?php

declare(strict_types=1);

/**
 * @var array<string, string> $errors
 */
?>
<div class="page-actions">
    <a href="<?= e(url('/')) ?>" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Back to dashboard
    </a>
</div>

<?php if ($errors !== []): ?>
    <div class="alert alert-danger app-alert" role="alert">
        <i class="bi bi-exclamation-octagon-fill"></i>
        <span>Please review the highlighted fields and try again.</span>
    </div>
<?php endif; ?>

<div class="row g-3">
    <div class="col-xl-7">
        <div class="card panel">
            <div class="panel-header">
                <div>
                    <h2>Account password</h2>
                    <p>Use a unique password that you do not use on any other site</p>
                </div>
                <i class="bi bi-shield-lock panel-icon"></i>
            </div>
            <div class="panel-body">
                <form method="post" action="<?= e(url('profile/password')) ?>" novalidate>
                    <?= csrf_field() ?>

                    <div class="mb-3">
                        <label for="current_password" class="form-label">Current password <span class="required">*</span></label>
                        <div class="input-group">
                            <input type="password" class="form-control<?= field_class($errors, 'current_password') ?>"
                                   id="current_password" name="current_password"
                                   autocomplete="current-password" required autofocus>
                            <button class="btn btn-outline-secondary password-toggle" type="button"
                                    data-target="current_password" aria-label="Show password">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                        <?= field_error($errors, 'current_password') ?>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">New password <span class="required">*</span></label>
                        <div class="input-group">
                            <input type="password" class="form-control<?= field_class($errors, 'password') ?>"
                                   id="password" name="password" minlength="10"
                                   autocomplete="new-password" required>
                            <button class="btn btn-outline-secondary password-toggle" type="button"
                                    data-target="password" aria-label="Show password">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                        <?= field_error($errors, 'password') ?>
                        <div class="form-text">At least 10 characters, including one letter and one number.</div>
                    </div>

                    <div class="mb-4">
                        <label for="password_confirmation" class="form-label">Confirm new password <span class="required">*</span></label>
                        <div class="input-group">
                            <input type="password" class="form-control<?= field_class($errors, 'password_confirmation') ?>"
                                   id="password_confirmation" name="password_confirmation" minlength="10"
                                   autocomplete="new-password" required>
                            <button class="btn btn-outline-secondary password-toggle" type="button"
                                    data-target="password_confirmation" aria-label="Show password">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                        <?= field_error($errors, 'password_confirmation') ?>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check2-circle"></i> Update password
                        </button>
                        <a href="<?= e(url('/')) ?>" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-xl-5">
        <div class="card panel panel-muted">
            <div class="panel-header">
                <div>
                    <h2>Security tips</h2>
                    <p>Small habits that protect student data</p>
                </div>
                <i class="bi bi-info-circle panel-icon"></i>
            </div>
            <div class="panel-body">
                <ul class="security-list">
                    <li><i class="bi bi-check-circle"></i> Change the seeded demo passwords immediately after deployment.</li>
                    <li><i class="bi bi-check-circle"></i> Never reuse your hosting control panel or e-mail password.</li>
                    <li><i class="bi bi-check-circle"></i> Sign out when using a shared or public computer.</li>
                    <li><i class="bi bi-check-circle"></i> Keep the database backup in a private location.</li>
                </ul>
            </div>
        </div>
    </div>
</div>

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
