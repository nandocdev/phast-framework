<?php
/**
 * @package     phast/config
 * @file        database
 * @author      Fernando Castillo <fdocst@gmail.com>
 * @date        2025-07-04
 * @version     1.0.0
 * @description Database configuration
 */

return [
   'default' => env('DB_CONNECTION', 'mysql'),

   'connections' => [
      'mysql' => [
         'driver' => 'mysql',
         'host' => env('DB_HOST', '127.0.0.1'),
         'port' => env('DB_PORT', '3306'),
         'database' => env('DB_NAME'),
         'username' => env('DB_USER'),
         'password' => env('DB_PASS'),
         'charset' => env('DB_CHARSET', 'utf8mb4'),
         'collation' => env('DB_COLLATION', 'utf8mb4_unicode_ci'),
         'options' => [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
         ],
      ],
   ],
];
