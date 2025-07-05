<?php
/**
 * @package     phast/core
 * @subpackage  Providers
 * @file        CacheServiceProvider
 * @author      Fernando Castillo <fdocst@gmail.com>
 * @date        2025-07-05
 * @version     1.0.0
 * @description Service provider for cache system
 */

declare(strict_types=1);

namespace Phast\Core\Providers;

use Phast\Core\Contracts\ServiceProviderInterface;
use Phast\Core\Contracts\ContainerInterface;
use Phast\Core\Cache\CacheInterface;
use Phast\Core\Cache\FileCache;
use Phast\Core\Cache\MemoryCache;

class CacheServiceProvider implements ServiceProviderInterface {
   public function register(ContainerInterface $container): void {
      // Register default cache implementation
      $container->singleton(CacheInterface::class, function () {
         $cacheType = env('CACHE_DRIVER', 'file');
         $cacheDir = PHAST_BASE_PATH . '/storage/cache';

         return match ($cacheType) {
            'memory' => new MemoryCache(),
            'file' => new FileCache($cacheDir),
            default => new FileCache($cacheDir)
         };
      });

      // Register specific implementations
      $container->bind('cache.file', function () {
         return new FileCache(PHAST_BASE_PATH . '/storage/cache');
      });

      $container->bind('cache.memory', function () {
         return new MemoryCache();
      });
   }

   public function boot(ContainerInterface $container): void {
      // Boot cache system - create directories if needed
      $cacheDir = PHAST_BASE_PATH . '/storage/cache';
      if (!is_dir($cacheDir)) {
         mkdir($cacheDir, 0755, true);
      }
   }
}
