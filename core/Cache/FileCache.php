<?php
/**
 * @package     phast/core
 * @subpackage  Cache
 * @file        FileCache
 * @author      Fernando Castillo <fdocst@gmail.com>
 * @date        2025-07-05
 * @version     1.0.0
 * @description File-based cache implementation
 */

declare(strict_types=1);

namespace Phast\Core\Cache;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * File-based cache implementation
 */
class FileCache implements CacheInterface {
   private string $cacheDir;
   private int $defaultTtl;
   private LoggerInterface $logger;
   private array $stats = [
      'hits' => 0,
      'misses' => 0,
      'writes' => 0,
      'deletes' => 0,
   ];

   public function __construct(
      string $cacheDir = '',
      int $defaultTtl = 3600,
      LoggerInterface $logger = null
   ) {
      $this->cacheDir = $cacheDir ?: sys_get_temp_dir() . '/phast_cache';
      $this->defaultTtl = $defaultTtl;
      $this->logger = $logger ?? new NullLogger();

      $this->ensureCacheDirectoryExists();
   }

   public function get(string $key, $default = null) {
      $filePath = $this->getFilePath($key);

      if (!file_exists($filePath)) {
         $this->stats['misses']++;
         $this->logger->debug('Cache miss', ['key' => $key]);
         return $default;
      }

      $content = file_get_contents($filePath);
      if ($content === false) {
         $this->stats['misses']++;
         return $default;
      }

      $data = unserialize($content);

      // Check if expired
      if ($data['expires'] !== null && $data['expires'] < time()) {
         $this->delete($key);
         $this->stats['misses']++;
         $this->logger->debug('Cache expired', ['key' => $key]);
         return $default;
      }

      $this->stats['hits']++;
      $this->logger->debug('Cache hit', ['key' => $key]);
      return $data['value'];
   }

   public function set(string $key, $value, int|\DateInterval $ttl = null): bool {
      $filePath = $this->getFilePath($key);

      $expires = null;
      if ($ttl !== null) {
         if ($ttl instanceof \DateInterval) {
            $expires = (new \DateTime())->add($ttl)->getTimestamp();
         } else {
            $expires = time() + $ttl;
         }
      } elseif ($this->defaultTtl > 0) {
         $expires = time() + $this->defaultTtl;
      }

      $data = [
         'value' => $value,
         'expires' => $expires,
         'created_at' => time(),
      ];

      $result = file_put_contents($filePath, serialize($data), LOCK_EX) !== false;

      if ($result) {
         $this->stats['writes']++;
         $this->logger->debug('Cache write', ['key' => $key, 'expires' => $expires]);
      } else {
         $this->logger->error('Failed to write cache', ['key' => $key]);
      }

      return $result;
   }

   public function delete(string $key): bool {
      $filePath = $this->getFilePath($key);

      if (!file_exists($filePath)) {
         return true;
      }

      $result = unlink($filePath);

      if ($result) {
         $this->stats['deletes']++;
         $this->logger->debug('Cache delete', ['key' => $key]);
      }

      return $result;
   }

   public function clear(): bool {
      $files = glob($this->cacheDir . '/*');

      foreach ($files as $file) {
         if (is_file($file)) {
            unlink($file);
         }
      }

      $this->stats = ['hits' => 0, 'misses' => 0, 'writes' => 0, 'deletes' => 0];
      $this->logger->info('Cache cleared');

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
      $success = true;

      foreach ($values as $key => $value) {
         if (!$this->set($key, $value, $ttl)) {
            $success = false;
         }
      }

      return $success;
   }

   public function deleteMultiple(iterable $keys): bool {
      $success = true;

      foreach ($keys as $key) {
         if (!$this->delete($key)) {
            $success = false;
         }
      }

      return $success;
   }

   public function has(string $key): bool {
      return $this->get($key, '__NOT_FOUND__') !== '__NOT_FOUND__';
   }

   public function remember(string $key, callable $callback, int|\DateInterval $ttl = null) {
      $value = $this->get($key, '__NOT_FOUND__');

      if ($value !== '__NOT_FOUND__') {
         return $value;
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
         'cache_dir' => $this->cacheDir,
         'default_ttl' => $this->defaultTtl,
      ];
   }

   private function getFilePath(string $key): string {
      $safeKey = preg_replace('/[^a-zA-Z0-9_-]/', '_', $key);
      return $this->cacheDir . '/' . $safeKey . '.cache';
   }

   private function ensureCacheDirectoryExists(): void {
      if (!is_dir($this->cacheDir)) {
         mkdir($this->cacheDir, 0755, true);
      }
   }
}
