<?php
/**
 * @package     phast/core
 * @subpackage  RateLimit
 * @file        RateLimiterInterface
 * @author      Fernando Castillo <fdocst@gmail.com>
 * @date        2025-07-05
 * @version     1.0.0
 * @description Rate limiter interface
 */

declare(strict_types=1);

namespace Phast\Core\RateLimit;

/**
 * Interface for rate limiters
 */
interface RateLimiterInterface {
   /**
    * Check if the identifier is allowed to make a request
    */
   public function isAllowed(string $identifier): bool;

   /**
    * Attempt to make a request (returns true if allowed, false if rate limited)
    */
   public function attempt(string $identifier): bool;

   /**
    * Get remaining attempts for an identifier
    */
   public function getRemainingAttempts(string $identifier): int;

   /**
    * Get the time until the rate limit resets (in seconds)
    */
   public function getResetTime(string $identifier): int;

   /**
    * Reset the rate limit for an identifier
    */
   public function reset(string $identifier): void;

   /**
    * Get rate limit information
    */
   public function getInfo(string $identifier): array;
}
