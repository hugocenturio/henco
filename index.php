<?php
/**
 * Henco — front controller.
 *
 * All HTTP requests reach this file via .htaccess rewrites; routing is
 * defined in config/routes.php and dispatched by App\Core\Router.
 */

require __DIR__ . '/bootstrap.php';

use App\Core\Request;
use App\Core\Router;

$router  = new Router();
require __DIR__ . '/config/routes.php';
$router->dispatch(new Request());
