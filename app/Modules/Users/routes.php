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

/** @var \Phast\Core\Routing\Router $router */

$router->group(['prefix' => '/users'], function ($router) {
   $router->get('/', 'Phast\App\Modules\Users\Controllers\UserController@index');
   $router->get('/{id}', 'Phast\App\Modules\Users\Controllers\UserController@show');
   $router->post('/', 'Phast\App\Modules\Users\Controllers\UserController@store');
   $router->put('/{id}', 'Phast\App\Modules\Users\Controllers\UserController@update');
   $router->delete('/{id}', 'Phast\App\Modules\Users\Controllers\UserController@destroy');
});
