<?php

declare(strict_types=1);
?>
<div class="error-code">403</div>
<h1 class="error-title">Request blocked</h1>
<p class="error-text">
    Your security token is missing or has expired. Go back, reload the page, and submit the form again.
</p>
<a class="btn btn-primary" href="<?= e(url('/')) ?>"><i class="bi bi-arrow-clockwise"></i> Reload the application</a>
