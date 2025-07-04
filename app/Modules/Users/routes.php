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
   // Public routes
   $router->get('/', 'Phast\App\Modules\Users\Controllers\UserController@index')
      ->name('users.index');

   $router->get('/{id}', 'Phast\App\Modules\Users\Controllers\UserController@show')
      ->name('users.show');

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