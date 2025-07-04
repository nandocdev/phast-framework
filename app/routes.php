<?php
/**
 * @package     phast/app
 * @file        routes
 * @author      Fernando Castillo <fdocst@gmail.com>
 * @date        2025-07-04
 * @version     1.0.0
 * @description Application routes
 */

declare(strict_types=1);

use Phast\Core\Application\Bootstrap;

/** @var Bootstrap $app */
$router = $app->getRouter();

// Home route
$router->get('/', function () {
    return response()->json([
        'message' => 'Welcome to Phast Framework',
        'version' => '1.0.0',
        'timestamp' => date('Y-m-d H:i:s')
    ]);
});

// API routes
$router->group(['prefix' => '/api'], function ($router) {
    // Users module routes
    require_once PHAST_BASE_PATH . '/app/Modules/Users/routes.php';
    
    // Add more module routes here...
});
