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

// Add global middlewares
$router->globalMiddleware([
   \Phast\Core\Http\Middleware\CorsMiddleware::class,
   \Phast\Core\Http\Middleware\LoggingMiddleware::class,
]);

// Home route
$router->get('/', function () {
   return response()->json([
      'message' => 'Welcome to Phast Framework',
      'version' => '1.0.0',
      'timestamp' => date('Y-m-d H:i:s')
   ]);
})->name('home');

// API routes with rate limiting
$router->group([
   'prefix' => '/api',
   'middleware' => [\Phast\Core\Http\Middleware\RateLimitMiddleware::class]
], function ($router) {
   // Public API routes
   $router->get('/health', function () {
      return response()->json(['status' => 'healthy']);
   })->name('api.health');

   // Users module routes
   require_once PHAST_BASE_PATH . '/app/Modules/Users/routes.php';

   // Protected routes with authentication
   $router->group([
      'middleware' => [\Phast\Core\Http\Middleware\AuthMiddleware::class]
   ], function ($router) {
      $router->get('/profile', function () {
         return response()->json(['message' => 'User profile']);
      })->name('api.profile');
   });
});
