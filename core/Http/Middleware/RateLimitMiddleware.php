<?php
/**
 * @package     phast/core
 * @subpackage  Http/Middleware
 * @file        RateLimitMiddleware
 * @author      Fernando Castillo <fdocst@gmail.com>
 * @date        2025-07-05
 * @version     1.0.0
 * @description Rate limiting middleware
 */

declare(strict_types=1);

namespace Phast\Core\Http\Middleware;

use Phast\Core\Http\Request;
use Phast\Core\Http\Response;
use Phast\Core\RateLimit\RateLimiterInterface;
use Phast\Core\RateLimit\RateLimitException;

class RateLimitMiddleware {
   private RateLimiterInterface $rateLimiter;

   public function __construct(RateLimiterInterface $rateLimiter) {
      $this->rateLimiter = $rateLimiter;
   }

   public function handle(Request $request, callable $next): Response {
      $identifier = $this->getIdentifier($request);

      try {
         if (!$this->rateLimiter->attempt($identifier)) {
            $info = $this->rateLimiter->getInfo($identifier);

            return new Response(
               json_encode([
                  'error' => 'Rate limit exceeded',
                  'retry_after' => $info['retry_after'] ?? 60
               ]),
               429,
               ['Content-Type' => 'application/json']
            );
         }
      } catch (RateLimitException $e) {
         return new Response(
            json_encode([
               'error' => 'Rate limit error: ' . $e->getMessage()
            ]),
            429,
            ['Content-Type' => 'application/json']
         );
      }

      return $next($request);
   }

   private function getIdentifier(Request $request): string {
      // Use IP address or user ID if authenticated
      return $request->getIp() ?? 'unknown';
   }
}
