<?php

if (is_file(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
}

require_once __DIR__ . '/../app/Core/Autoloader.php';
\App\Core\Autoloader::register('App',   __DIR__ . '/../app');
\App\Core\Autoloader::register('Tests', __DIR__);

require_once __DIR__ . '/../app/helpers.php';
