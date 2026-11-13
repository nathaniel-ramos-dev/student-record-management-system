<?php

declare(strict_types=1);
?>
<div class="error-code">500</div>
<h1 class="error-title">Something went wrong</h1>
<p class="error-text">
    The server could not complete your request. If you are the administrator, check the PHP error log
    and make sure the database credentials in <code>config/config.php</code> are correct.
</p>
<a class="btn btn-primary" href="<?= e(url('/')) ?>"><i class="bi bi-arrow-clockwise"></i> Try again</a>
