<?php
/**
 * @package     phast/config
 * @file        app
 * @author      Fernando Castillo <fdocst@gmail.com>
 * @date        2025-07-04
 * @version     1.0.0
 * @description Application configuration
 */

return [
   'name' => env('APP_NAME', 'Phast Framework'),
   'env' => env('APP_ENV', 'production'),
   'debug' => env('APP_DEBUG', false),
   'url' => env('APP_URL', 'http://localhost'),
   'timezone' => env('APP_TIMEZONE', 'UTC'),
   'locale' => env('APP_LOCALE', 'en'),
   'fallback_locale' => env('APP_FALLBACK_LOCALE', 'en'),
   'key' => env('APP_KEY'),
   'cipher' => 'AES-256-CBC',
];
