<?php

namespace App\Core;

class View
{
    private static string $viewsPath = __DIR__ . '/../Views';
    private static ?string $layout = null;
    private static array  $layoutData = [];

    public static function setLayout(?string $layout, array $data = []): void
    {
        self::$layout     = $layout;
        self::$layoutData = $data;
    }

    public static function render(string $view, array $data = []): void
    {
        $content = self::capture($view, $data);

        if (self::$layout === null) {
            echo $content;
            return;
        }

        $layoutPath = self::resolve('layouts/' . self::$layout);
        $layoutData = array_merge(self::$layoutData, ['content' => $content]);
        echo self::renderFile($layoutPath, $layoutData);
    }

    public static function partial(string $view, array $data = []): string
    {
        return self::capture($view, $data);
    }

    private static function capture(string $view, array $data): string
    {
        $path = self::resolve($view);
        return self::renderFile($path, $data);
    }

    private static function renderFile(string $path, array $data): string
    {
        extract($data, EXTR_SKIP);
        ob_start();
        include $path;
        return (string) ob_get_clean();
    }

    private static function resolve(string $view): string
    {
        $relative = ltrim(str_replace('.', '/', $view), '/') . '.php';
        $path = realpath(self::$viewsPath) . DIRECTORY_SEPARATOR . $relative;
        if (!is_file($path)) {
            throw new \RuntimeException("View not found: $view");
        }
        return $path;
    }
}
