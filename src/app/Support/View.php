<?php

declare(strict_types=1);

namespace App\Support;

use RuntimeException;

/**
 * Plain-PHP view renderer with a single level of layout wrapping.
 */
final class View
{
    /**
     * @param array<string, mixed> $data
     */
    public static function render(string $view, array $data = [], string $layout = 'layout'): void
    {
        $viewFile = base_path('views/' . $view . '.php');

        if (!is_file($viewFile)) {
            throw new RuntimeException(sprintf('View not found: %s', $view));
        }

        extract($data, EXTR_SKIP);

        ob_start();
        require $viewFile;
        $content = (string) ob_get_clean();

        $layoutFile = base_path('views/' . $layout . '.php');

        if (!is_file($layoutFile)) {
            echo $content;

            return;
        }

        require $layoutFile;
    }
}
