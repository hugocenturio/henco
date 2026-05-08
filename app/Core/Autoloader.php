<?php

namespace App\Core;

class Autoloader
{
    public static function register(string $rootNamespace, string $baseDir): void
    {
        spl_autoload_register(function (string $class) use ($rootNamespace, $baseDir) {
            if (!str_starts_with($class, $rootNamespace . '\\')) {
                return;
            }
            $relative = substr($class, strlen($rootNamespace) + 1);
            $file = rtrim($baseDir, '/\\') . DIRECTORY_SEPARATOR
                  . str_replace('\\', DIRECTORY_SEPARATOR, $relative) . '.php';
            if (is_file($file)) {
                require_once $file;
            }
        });
    }
}
