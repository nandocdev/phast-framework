<?php
/**
 * @package     phast/core
 * @subpackage  Cache
 * @file        MemoryCache
 * @author      Fernando Castillo <fdocst@gmail.com>
 * @date        2025-07-05
 * @version     1.0.0
 * @description Memory-based cache implementation (for testing/development)
 */

declare(strict_types=1);

namespace Phast\Core\Cache;

/**
 * Memory-based cache implementation - data is lost when script ends
 */
class MemoryCache implements CacheInterface {
   private array $data = [];
   private array $expires = [];
   private array $stats = [
      'hits' => 0,
      'misses' => 0,
      'writes' => 0,
      'deletes' => 0,
   ];

   public function get(string $key, $default = null) {
      if (!$this->has($key)) {
         $this->stats['misses']++;
         return $default;
      }

      $this->stats['hits']++;
      return $this->data[$key];
   }

   public function set(string $key, $value, int|\DateInterval $ttl = null): bool {
      $this->data[$key] = $value;

      if ($ttl !== null) {
         if ($ttl instanceof \DateInterval) {
            $this->expires[$key] = (new \DateTime())->add($ttl)->getTimestamp();
         } else {
            $this->expires[$key] = time() + $ttl;
         }
      } else {
         unset($this->expires[$key]);
      }

      $this->stats['writes']++;
      return true;
   }

   public function delete(string $key): bool {
      unset($this->data[$key]);
      unset($this->expires[$key]);
      $this->stats['deletes']++;
      return true;
   }

   public function clear(): bool {
      $this->data = [];
      $this->expires = [];
      $this->stats = ['hits' => 0, 'misses' => 0, 'writes' => 0, 'deletes' => 0];
      return true;
   }

   public function getMultiple(iterable $keys, $default = null): iterable {
      $results = [];

      foreach ($keys as $key) {
         $results[$key] = $this->get($key, $default);
      }

      return $results;
   }

   public function setMultiple(iterable $values, int|\DateInterval $ttl = null): bool {
      foreach ($values as $key => $value) {
         $this->set($key, $value, $ttl);
      }

      return true;
   }

   public function deleteMultiple(iterable $keys): bool {
      foreach ($keys as $key) {
         $this->delete($key);
      }

      return true;
   }

   public function has(string $key): bool {
      if (!isset($this->data[$key])) {
         return false;
      }

      // Check expiration
      if (isset($this->expires[$key]) && $this->expires[$key] < time()) {
         $this->delete($key);
         return false;
      }

      return true;
   }

   public function remember(string $key, callable $callback, int|\DateInterval $ttl = null) {
      if ($this->has($key)) {
         return $this->get($key);
      }

      $value = $callback();
      $this->set($key, $value, $ttl);

      return $value;
   }

   public function getStats(): array {
      return [
         'hits' => $this->stats['hits'],
         'misses' => $this->stats['misses'],
         'writes' => $this->stats['writes'],
         'deletes' => $this->stats['deletes'],
         'hit_ratio' => $this->stats['hits'] + $this->stats['misses'] > 0
            ? round($this->stats['hits'] / ($this->stats['hits'] + $this->stats['misses']) * 100, 2)
            : 0,
         'keys_count' => count($this->data),
         'memory_usage' => memory_get_usage(true),
      ];
   }
}
