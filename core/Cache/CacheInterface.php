<?php
/**
 * @package     phast/core
 * @subpackage  Cache
 * @file        CacheInterface
 * @author      Fernando Castillo <fdocst@gmail.com>
 * @date        2025-07-05
 * @version     1.0.0
 * @description Cache interface
 */

declare(strict_types=1);

namespace Phast\Core\Cache;

/**
 * Cache interface following PSR-16 Simple Cache
 */
interface CacheInterface {
   /**
    * Get a value from cache
    */
   public function get(string $key, $default = null);

   /**
    * Set a value in cache
    */
   public function set(string $key, $value, int|\DateInterval $ttl = null): bool;

   /**
    * Delete a value from cache
    */
   public function delete(string $key): bool;

   /**
    * Clear all cache
    */
   public function clear(): bool;

   /**
    * Get multiple values from cache
    */
   public function getMultiple(iterable $keys, $default = null): iterable;

   /**
    * Set multiple values in cache
    */
   public function setMultiple(iterable $values, int|\DateInterval $ttl = null): bool;

   /**
    * Delete multiple values from cache
    */
   public function deleteMultiple(iterable $keys): bool;

   /**
    * Check if a key exists in cache
    */
   public function has(string $key): bool;

   /**
    * Remember a value - get from cache or execute callback and cache result
    */
   public function remember(string $key, callable $callback, int|\DateInterval $ttl = null);

   /**
    * Get cache statistics
    */
   public function getStats(): array;
}
