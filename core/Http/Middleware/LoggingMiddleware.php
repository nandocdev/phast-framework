<?php
/**
 * @package     phast/core
 * @subpackage  Http/Middleware
 * @file        LoggingMiddleware
 * @author      Fernando Castillo <fdocst@gmail.com>
 * @date        2025-07-04
 * @version     1.0.0
 * @description Request logging middleware
 */

declare(strict_types=1);

namespace Phast\Core\Http\Middleware;

use Phast\Core\Http\Request;
use Phast\Core\Http\Response;
use Psr\Log\LoggerInterface;

class LoggingMiddleware implements MiddlewareInterface {
   private LoggerInterface $logger;

   public function __construct(LoggerInterface $logger) {
      $this->logger = $logger;
   }

   public function handle(Request $request, callable $next): Response {
      $startTime = microtime(true);

      // Log incoming request
      $this->logger->info('Incoming request', [
         'method' => $request->getMethod(),
         'uri' => $request->getUri(),
         'ip' => $request->getIp(),
         'user_agent' => $request->getHeader('user-agent'),
      ]);

      try {
         $response = $next($request);

         $endTime = microtime(true);
         $duration = round(($endTime - $startTime) * 1000, 2); // in milliseconds

         // Log response
         $this->logger->info('Request completed', [
            'method' => $request->getMethod(),
            'uri' => $request->getUri(),
            'status' => $response->getStatusCode(),
            'duration_ms' => $duration,
         ]);

         return $response;
      } catch (\Throwable $e) {
         $endTime = microtime(true);
         $duration = round(($endTime - $startTime) * 1000, 2);

         // Log error
         $this->logger->error('Request failed', [
            'method' => $request->getMethod(),
            'uri' => $request->getUri(),
            'duration_ms' => $duration,
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
         ]);

         throw $e;
      }
   }
}
