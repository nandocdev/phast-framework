<?php
/**
 * @package     phast/app
 * @subpackage  Modules/Users
 * @file        routes
 * @author      Fernando Castillo <fdocst@gmail.com>
 * @date        2025-07-04
 * @version     1.0.0
 * @description Users module routes
 */

declare(strict_types=1);

// Note: $router is already available from the parent scope

$router->group(['prefix' => 'users'], function ($router) {

   // Public routes with mock responses to avoid DB dependency
   $router->get('/', function () {
      return response()->json([
         'users' => [
            ['id' => 1, 'name' => 'Juan Pérez', 'email' => 'juan@example.com'],
            ['id' => 2, 'name' => 'María García', 'email' => 'maria@example.com']
         ],
         'total' => 2,
         'source' => 'module_mock'
      ]);
   })->name('users.index');

   $router->get('/{id}', function ($request) {
      $id = $request->getRouteParam('id');
      return response()->json([
         'user' => ['id' => $id, 'name' => 'Usuario ' . $id, 'email' => 'user' . $id . '@example.com'],
         'source' => 'module_mock'
      ]);
   })->name('users.show');

   // Routes that require authentication
   $router->group([
      'middleware' => [\Phast\Core\Http\Middleware\AuthMiddleware::class]
   ], function ($router) {
      $router->post('/', 'Phast\App\Modules\Users\Controllers\UserController@store')
         ->name('users.store');

      $router->put('/{id}', 'Phast\App\Modules\Users\Controllers\UserController@update')
         ->name('users.update');

      $router->delete('/{id}', 'Phast\App\Modules\Users\Controllers\UserController@destroy')
         ->name('users.destroy');
   });
});