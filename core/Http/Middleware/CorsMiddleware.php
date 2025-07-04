<?php
/**
 * @package     phast/core
 * @subpackage  Http/Middleware
 * @file        CorsMiddleware
 * @author      Fernando Castillo <fdocst@gmail.com>
 * @date        2025-07-04
 * @version     1.0.0
 * @description CORS middleware for handling cross-origin requests
 */

declare(strict_types=1);

namespace Phast\Core\Http\Middleware;

use Phast\Core\Http\Request;
use Phast\Core\Http\Response;

class CorsMiddleware implements MiddlewareInterface {
   private array $config;

   public function __construct(array $config = []) {
      $this->config = array_merge([
         'allowed_origins' => ['*'],
         'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'OPTIONS'],
         'allowed_headers' => ['Content-Type', 'Authorization', 'X-Requested-With'],
         'max_age' => 86400,
         'allow_credentials' => false,
      ], $config);
   }

   public function handle(Request $request, callable $next): Response {
      // Handle preflight requests
      if ($request->isMethod('OPTIONS')) {
         $response = new Response('', 200);
      } else {
         $response = $next($request);
      }

      // Add CORS headers
      $origin = $request->getHeader('origin');

      if ($this->isAllowedOrigin($origin)) {
         $response->setHeader('Access-Control-Allow-Origin', $origin ?: '*');
      }

      $response->setHeader('Access-Control-Allow-Methods', implode(', ', $this->config['allowed_methods']))
         ->setHeader('Access-Control-Allow-Headers', implode(', ', $this->config['allowed_headers']))
         ->setHeader('Access-Control-Max-Age', (string) $this->config['max_age']);

      if ($this->config['allow_credentials']) {
         $response->setHeader('Access-Control-Allow-Credentials', 'true');
      }

      return $response;
   }

   private function isAllowedOrigin(?string $origin): bool {
      if (!$origin) {
         return false;
      }

      if (in_array('*', $this->config['allowed_origins'])) {
         return true;
      }

      return in_array($origin, $this->config['allowed_origins']);
   }
}
