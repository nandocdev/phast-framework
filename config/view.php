<?php
/**
 * @package     phast/config
 * @file        view
 * @author      Fernando Castillo <fdocst@gmail.com>
 * @date        2025-07-04
 * @version     1.0.0
 * @description View configuration
 */

return [
   // View Paths
   'views_path' => PHAST_BASE_PATH . '/resources/views',
   'templates_path' => PHAST_BASE_PATH . '/resources/templates',
   'path' => PHAST_BASE_PATH . '/resources/views', // Legacy compatibility

   // File Extension - The default file extension for view templates
   'file_extension' => 'phtml',

   // Default Layout - The default layout to use when rendering views
   'default_layout' => 'default',

   // View Caching - Enable or disable view caching for better performance
   'cache_enabled' => env('VIEW_CACHE_ENABLED', false),
   'cache_path' => PHAST_BASE_PATH . '/storage/cache/views',
   'cache' => PHAST_BASE_PATH . '/storage/cache/views', // Legacy compatibility
   'compiled' => PHAST_BASE_PATH . '/storage/compiled', // Legacy compatibility

   // Global View Data - Data that will be available to all views
   'global_data' => [
      'app_name' => env('APP_NAME', 'Phast Application'),
      'app_version' => '1.0.0',
   ],

   // View Composers - Automatically inject data into specific views
   'composers' => [
      // 'layouts/*' => App\View\Composers\LayoutComposer::class,
      // 'auth/*' => App\View\Composers\AuthComposer::class,
   ],
];
