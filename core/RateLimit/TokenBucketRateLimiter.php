<?php
/**
 * @package     phast/core
 * @subpackage  RateLimit
 * @file        TokenBucketRateLimiter
 * @author      Fernando Castillo <fdocst@gmail.com>
 * @date        2025-07-05
 * @version     1.0.0
 * @description Token bucket rate limiter implementation
 */

declare(strict_types=1);

namespace Phast\Core\RateLimit;

use Phast\Core\Cache\CacheInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Token bucket rate limiter implementation
 */
class TokenBucketRateLimiter implements RateLimiterInterface {
   private CacheInterface $cache;
   private LoggerInterface $logger;
   private int $maxAttempts;
   private int $decayMinutes;

   public function __construct(
      CacheInterface $cache,
      int $maxAttempts = 60,
      int $decayMinutes = 1,
      LoggerInterface $logger = null
   ) {
      $this->cache = $cache;
      $this->maxAttempts = $maxAttempts;
      $this->decayMinutes = $decayMinutes;
      $this->logger = $logger ?? new NullLogger();
   }

   public function isAllowed(string $identifier): bool {
      $bucket = $this->getBucket($identifier);
      return $bucket['tokens'] > 0;
   }

   public function attempt(string $identifier): bool {
      $bucket = $this->getBucket($identifier);

      if ($bucket['tokens'] <= 0) {
         $this->logger->warning('Rate limit exceeded', [
            'identifier' => $identifier,
            'max_attempts' => $this->maxAttempts,
            'decay_minutes' => $this->decayMinutes,
         ]);
         return false;
      }

      // Consume a token
      $bucket['tokens']--;
      $this->setBucket($identifier, $bucket);

      $this->logger->debug('Rate limit attempt', [
         'identifier' => $identifier,
         'remaining_tokens' => $bucket['tokens'],
      ]);

      return true;
   }

   public function getRemainingAttempts(string $identifier): int {
      $bucket = $this->getBucket($identifier);
      return max(0, $bucket['tokens']);
   }

   public function getResetTime(string $identifier): int {
      $bucket = $this->getBucket($identifier);

      if ($bucket['tokens'] >= $this->maxAttempts) {
         return 0; // No reset needed
      }

      $secondsUntilRefill = $this->decayMinutes * 60;
      $timeElapsed = time() - $bucket['last_refill'];

      return max(0, $secondsUntilRefill - $timeElapsed);
   }

   public function reset(string $identifier): void {
      $this->cache->delete($this->getCacheKey($identifier));
      $this->logger->info('Rate limit reset', ['identifier' => $identifier]);
   }

   public function getInfo(string $identifier): array {
      $bucket = $this->getBucket($identifier);

      return [
         'identifier' => $identifier,
         'max_attempts' => $this->maxAttempts,
         'remaining_attempts' => $bucket['tokens'],
         'reset_time' => $this->getResetTime($identifier),
         'decay_minutes' => $this->decayMinutes,
         'last_refill' => $bucket['last_refill'],
      ];
   }

   /**
    * Get the bucket data for an identifier
    */
   private function getBucket(string $identifier): array {
      $cacheKey = $this->getCacheKey($identifier);
      $bucket = $this->cache->get($cacheKey);

      if ($bucket === null) {
         $bucket = [
            'tokens' => $this->maxAttempts,
            'last_refill' => time(),
         ];
      } else {
         // Refill tokens based on time elapsed
         $bucket = $this->refillBucket($bucket);
      }

      return $bucket;
   }

   /**
    * Set the bucket data for an identifier
    */
   private function setBucket(string $identifier, array $bucket): void {
      $cacheKey = $this->getCacheKey($identifier);
      $ttl = ($this->decayMinutes + 1) * 60; // Cache TTL should be longer than decay time

      $this->cache->set($cacheKey, $bucket, $ttl);
   }

   /**
    * Refill tokens in the bucket based on elapsed time
    */
   private function refillBucket(array $bucket): array {
      $now = time();
      $timeElapsed = $now - $bucket['last_refill'];
      $decaySeconds = $this->decayMinutes * 60;

      if ($timeElapsed >= $decaySeconds) {
         // Full refill
         $bucket['tokens'] = $this->maxAttempts;
         $bucket['last_refill'] = $now;
      } else {
         // Partial refill based on time elapsed
         $refillRate = $this->maxAttempts / $decaySeconds;
         $tokensToAdd = floor($timeElapsed * $refillRate);

         if ($tokensToAdd > 0) {
            $bucket['tokens'] = min($this->maxAttempts, $bucket['tokens'] + $tokensToAdd);
            $bucket['last_refill'] = $now;
         }
      }

      return $bucket;
   }

   /**
    * Get the cache key for an identifier
    */
   private function getCacheKey(string $identifier): string {
      return 'rate_limit:' . md5($identifier);
   }
}
