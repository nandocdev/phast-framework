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
$router->get('/', 'Phast\App\Controllers\WebController@home')->name('home');

// Web routes for views
$router->get('/web', 'Phast\App\Controllers\WebController@home')->name('web.home');
$router->get('/web/users', 'Phast\App\Controllers\WebController@users')->name('web.users');

// Legacy JSON API route (for backward compatibility)
$router->get('/api-info', function () {
   return response()->json([
      'message' => 'Welcome to Phast Framework',
      'version' => '1.0.0',
      'timestamp' => date('Y-m-d H:i:s')
   ]);
})->name('api.info');

// API routes with rate limiting
$router->group([
   'prefix' => '/api',
   'middleware' => [\Phast\Core\Http\Middleware\RateLimitMiddleware::class]
], function ($router) {
   // Public API routes
   $router->get('/health', function () {
      return response()->json(['status' => 'healthy']);
   })->name('api.health');

   // Mock users route for testing (no DB required)
   $router->get('/users-mock', function () {
      return response()->json([
         'users' => [
            ['id' => 1, 'name' => 'Juan Pérez', 'email' => 'juan@example.com'],
            ['id' => 2, 'name' => 'María García', 'email' => 'maria@example.com'],
            ['id' => 3, 'name' => 'Carlos López', 'email' => 'carlos@example.com']
         ],
         'total' => 3
      ]);
   })->name('api.users.mock');

   // Include module routes within API context
   $moduleRoutesPath = __DIR__ . '/Modules/*/routes.php';
   $pathRoute = glob($moduleRoutesPath) ?: [];

   foreach ($pathRoute as $routeFile) {
      if (file_exists($routeFile)) {
         error_log("Loading route file: " . $routeFile);
         require_once $routeFile;
         error_log("Route file loaded successfully");
      }
   }
});