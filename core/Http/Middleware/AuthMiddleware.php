<?php
/**
 * @package     phast/core
 * @subpackage  Http/Middleware
 * @file        AuthMiddleware
 * @author      Fernando Castillo <fdocst@gmail.com>
 * @date        2025-07-04
 * @version     1.0.0
 * @description Authentication middleware
 */

declare(strict_types=1);

namespace Phast\Core\Http\Middleware;

use Phast\Core\Http\Request;
use Phast\Core\Http\Response;

class AuthMiddleware implements MiddlewareInterface {
   public function handle(Request $request, callable $next): Response {
      $token = $this->extractToken($request);

      if (!$token) {
         return new Response(
            json_encode(['error' => 'Authentication required']),
            401,
            ['Content-Type' => 'application/json']
         );
      }

      if (!$this->validateToken($token)) {
         return new Response(
            json_encode(['error' => 'Invalid or expired token']),
            401,
            ['Content-Type' => 'application/json']
         );
      }

      // Add authenticated user to request if needed
      // $request->setAttribute('user', $this->getUserFromToken($token));

      return $next($request);
   }

   private function extractToken(Request $request): ?string {
      $authorization = $request->getHeader('authorization');

      if (!$authorization) {
         return null;
      }

      if (str_starts_with($authorization, 'Bearer ')) {
         return substr($authorization, 7);
      }

      return null;
   }

   private function validateToken(string $token): bool {
      // Implement your token validation logic here
      // This is a simple example - in production, validate JWT or check database
      return !empty($token) && strlen($token) > 10;
   }
}
