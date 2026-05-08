<?php

// 1. Autoloading
require_once __DIR__ . '/app/Core/Env.php';
require_once __DIR__ . '/app/Core/Autoloader.php';
\App\Core\Autoloader::register('App', __DIR__ . '/app');

// Composer autoload (vendor packages)
if (is_file(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

// Function helpers
require_once __DIR__ . '/app/helpers.php';

// 2. Environment
\App\Core\Env::load(__DIR__ . '/.env');

// 3. Database & Mailjet config (define legacy constants from env, with config.php fallback)
$envHas = static fn(string $k) => (\App\Core\Env::get($k, null) !== null && \App\Core\Env::get($k, '') !== '');

if ($envHas('DB_HOST')) {
    if (!defined('DB_HOST'))     define('DB_HOST',     \App\Core\Env::get('DB_HOST'));
    if (!defined('DB_NAME'))     define('DB_NAME',     \App\Core\Env::get('DB_NAME'));
    if (!defined('DB_USER'))     define('DB_USER',     \App\Core\Env::get('DB_USER'));
    if (!defined('DB_PASSWORD')) define('DB_PASSWORD', \App\Core\Env::get('DB_PASSWORD', ''));
    if (!defined('MAILJET_API_KEY'))    define('MAILJET_API_KEY',    \App\Core\Env::get('MAILJET_API_KEY', ''));
    if (!defined('MAILJET_API_SECRET')) define('MAILJET_API_SECRET', \App\Core\Env::get('MAILJET_API_SECRET', ''));
} elseif (is_file(__DIR__ . '/config/config.php')) {
    require_once __DIR__ . '/config/config.php';
}

// 4. Security bootstrap (sessions + headers)
require_once __DIR__ . '/config/security.php';
