<?php
/**
 * @package     phast/core
 * @subpackage  RateLimit
 * @file        RateLimitException
 * @author      Fernando Castillo <fdocst@gmail.com>
 * @date        2025-07-05
 * @version     1.0.0
 * @description Exception thrown when rate limit is exceeded
 */

declare(strict_types=1);

namespace Phast\Core\RateLimit;

use Phast\Core\Exceptions\PhastException;

/**
 * Exception thrown when rate limit is exceeded
 */
class RateLimitException extends PhastException {
   private array $rateLimitInfo;

   public function __construct(array $rateLimitInfo, string $message = 'Rate limit exceeded') {
      $this->rateLimitInfo = $rateLimitInfo;

      parent::__construct(
         message: $message,
         code: 429,
         context: $rateLimitInfo
      );
   }

   public function getType(): string {
      return 'rate_limit';
   }

   public function getUserMessage(): string {
      $resetTime = $this->rateLimitInfo['reset_time'] ?? 0;

      if ($resetTime > 0) {
         return "Too many requests. Please try again in {$resetTime} seconds.";
      }

      return 'Too many requests. Please try again later.';
   }

   public function getRateLimitInfo(): array {
      return $this->rateLimitInfo;
   }

   public function getRemainingAttempts(): int {
      return $this->rateLimitInfo['remaining_attempts'] ?? 0;
   }

   public function getResetTime(): int {
      return $this->rateLimitInfo['reset_time'] ?? 0;
   }
}
