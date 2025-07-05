<?php
/**
 * @package     phast/core
 * @subpackage  Helpers
 * @file        helpers
 * @author      Fernando Castillo <fdocst@gmail.com>
 * @date        2025-07-05
 * @version     1.0.0
 * @description Global helper functions for events, cache, and rate limiting
 */

declare(strict_types=1);

use Phast\Core\Events\EventDispatcherInterface;
use Phast\Core\Events\EventInterface;
use Phast\Core\Cache\CacheInterface;
use Phast\Core\RateLimit\RateLimiterInterface;

if (!function_exists('event')) {
   /**
    * Dispatch an event
    */
   function event(EventInterface $event): EventInterface {
      $container = \Phast\Core\Application\Container::getInstance();
      $dispatcher = $container->get(EventDispatcherInterface::class);

      return $dispatcher->dispatch($event);
   }
}

if (!function_exists('cache')) {
   /**
    * Get the cache instance or retrieve/store a value
    */
   function cache(string $key = null, $value = null, int|\DateInterval $ttl = null) {
      $container = \Phast\Core\Application\Container::getInstance();
      $cache = $container->get(CacheInterface::class);

      if ($key === null) {
         return $cache;
      }

      if ($value === null) {
         return $cache->get($key);
      }

      $cache->set($key, $value, $ttl);
      return $value;
   }
}

if (!function_exists('cache_remember')) {
   /**
    * Get from cache or execute callback and cache the result
    */
   function cache_remember(string $key, callable $callback, int|\DateInterval $ttl = null) {
      $container = \Phast\Core\Application\Container::getInstance();
      $cache = $container->get(CacheInterface::class);

      return $cache->remember($key, $callback, $ttl);
   }
}

if (!function_exists('rate_limit')) {
   /**
    * Check rate limit for an identifier
    */
   function rate_limit(string $identifier): bool {
      $container = \Phast\Core\Application\Container::getInstance();
      $rateLimiter = $container->get(RateLimiterInterface::class);

      return $rateLimiter->attempt($identifier);
   }
}

if (!function_exists('rate_limit_info')) {
   /**
    * Get rate limit information for an identifier
    */
   function rate_limit_info(string $identifier): array {
      $container = \Phast\Core\Application\Container::getInstance();
      $rateLimiter = $container->get(RateLimiterInterface::class);

      return $rateLimiter->getInfo($identifier);
   }
}
