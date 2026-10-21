<?php

namespace App\Core;

use Exception;

class View
{
    private static string $viewsPath = __DIR__ . '/../../views/';

    public static function render(string $view, array $data = [], ?string $layout = 'layouts/main'): string
    {
        $viewFile = self::$viewsPath . str_replace('.', '/', $view) . '.php';
        if (!file_exists($viewFile)) {
            throw new Exception("View template not found: {$view} (at {$viewFile})");
        }

        // Extract variables for template scope
        extract($data, EXTR_SKIP);

        // Capture view content
        ob_start();
        include $viewFile;
        $content = ob_get_clean();

        if ($layout === null) {
            return $content;
        }

        $layoutFile = self::$viewsPath . str_replace('.', '/', $layout) . '.php';
        if (!file_exists($layoutFile)) {
            throw new Exception("Layout file not found: {$layout} (at {$layoutFile})");
        }

        ob_start();
        include $layoutFile;
        return ob_get_clean();
    }
}
