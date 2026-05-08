<?php
/** @var \App\Core\Router $router */

// Auth
$router->any ('/',          'AuthController@login');
$router->get ('/login',     'AuthController@login');
$router->post('/login',     'AuthController@login');
$router->get ('/logout',    'AuthController@logout');
$router->get ('/activate',  'AuthController@activate');

// Setup wizard
$router->any('/setup', 'SetupController@index');

// Dashboard / profile
$router->any('/dashboard', 'DashboardController@index');
$router->any('/profile',   'ProfileController@index');

// Notifications
$router->any ('/notifications',         'NotificationController@index');
$router->post('/notifications/read',    'NotificationController@markRead');
$router->get ('/notifications/count',   'NotificationController@unreadCount');

// My orders
$router->get('/my-orders', 'OrderController@mine');

// Order flow
$router->any ('/order-products',     'OrderController@products');
$router->any ('/cart',               'OrderController@cart');
$router->any ('/order-confirmation', 'OrderController@confirmation');
$router->any ('/finalize-order',     'OrderController@finalize');
$router->any ('/order-history',      'OrderController@history');
$router->any ('/order-details',      'OrderController@details');
$router->post('/order-email',        'OrderController@sendEmail');
$router->get ('/order-pdf',          'OrderController@pdf');

// Products
$router->any('/products',         'ProductController@index');
$router->any('/products/details', 'ProductController@details');
$router->any('/products/upload',  'ProductController@upload');

// Categories
$router->any('/categories', 'CategoryController@index');

// Clients
$router->any('/clients',         'ClientController@index');
$router->any('/clients/details', 'ClientController@details');

// Users
$router->any('/users', 'UserController@index');

// Settings
$router->any('/settings', 'SettingsController@index');

// JSON helpers retained from legacy
$router->get('/api/client', 'ClientController@apiDetails');
$router->get('/api/settings', 'SettingsController@api');
