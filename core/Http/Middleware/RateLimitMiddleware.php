<?php
/**
 * @package     phast/core
 * @subpackage  Http/Middleware
 * @file        RateLimitMiddleware
 * @author      Fernando Castillo <fdocst@gmail.com>
 * @date        2025-07-04
 * @version     1.0.0
 * @description Rate limiting middleware
 */

declare(strict_types=1);

namespace Phast\Core\Http\Middleware;

use Phast\Core\Http\Request;
use Phast\Core\Http\Response;

class RateLimitMiddleware implements MiddlewareInterface {
   private int $maxRequests;
   private int $windowSeconds;
   private string $storageFile;

   public function __construct(int $maxRequests = 60, int $windowSeconds = 60) {
      $this->maxRequests = $maxRequests;
      $this->windowSeconds = $windowSeconds;
      $this->storageFile = PHAST_BASE_PATH . '/storage/cache/rate_limits.json';
   }

   public function handle(Request $request, callable $next): Response {
      $clientIp = $request->getIp() ?? 'unknown';
      $currentTime = time();

      $rateLimits = $this->getRateLimits();

      // Clean old entries
      $this->cleanOldEntries($rateLimits, $currentTime);

      // Check current client
      if (!isset($rateLimits[$clientIp])) {
         $rateLimits[$clientIp] = [];
      }

      // Count requests in current window
      $windowStart = $currentTime - $this->windowSeconds;
      $recentRequests = array_filter(
         $rateLimits[$clientIp],
         fn($timestamp) => $timestamp > $windowStart
      );

      if (count($recentRequests) >= $this->maxRequests) {
         $this->saveRateLimits($rateLimits);

         return new Response(
            json_encode(['error' => 'Rate limit exceeded']),
            429,
            [
               'Content-Type' => 'application/json',
               'Retry-After' => (string) $this->windowSeconds,
               'X-RateLimit-Limit' => (string) $this->maxRequests,
               'X-RateLimit-Remaining' => '0',
               'X-RateLimit-Reset' => (string) ($currentTime + $this->windowSeconds),
            ]
         );
      }

      // Add current request
      $rateLimits[$clientIp][] = $currentTime;
      $this->saveRateLimits($rateLimits);

      $response = $next($request);

      // Add rate limit headers
      $remaining = max(0, $this->maxRequests - count($recentRequests) - 1);
      $response->setHeader('X-RateLimit-Limit', (string) $this->maxRequests)
         ->setHeader('X-RateLimit-Remaining', (string) $remaining)
         ->setHeader('X-RateLimit-Reset', (string) ($currentTime + $this->windowSeconds));

      return $response;
   }

   private function getRateLimits(): array {
      if (!file_exists($this->storageFile)) {
         return [];
      }

      $content = file_get_contents($this->storageFile);
      return $content ? json_decode($content, true) ?? [] : [];
   }

   private function saveRateLimits(array $rateLimits): void {
      $dir = dirname($this->storageFile);
      if (!is_dir($dir)) {
         mkdir($dir, 0755, true);
      }

      file_put_contents($this->storageFile, json_encode($rateLimits));
   }

   private function cleanOldEntries(array &$rateLimits, int $currentTime): void {
      $windowStart = $currentTime - $this->windowSeconds;

      foreach ($rateLimits as $clientIp => &$timestamps) {
         $timestamps = array_filter(
            $timestamps,
            fn($timestamp) => $timestamp > $windowStart
         );

         if (empty($timestamps)) {
            unset($rateLimits[$clientIp]);
         }
      }
   }
}
