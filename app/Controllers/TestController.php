<?php
/**
 * @package     phast/app
 * @subpackage  Controllers
 * @file        TestController
 * @author      Fernando Castillo <fdocst@gmail.com>
 * @date        2025-07-05
 * @version     1.0.0
 * @description Test controller for system integration
 */

declare(strict_types=1);

namespace Phast\App\Controllers;

use Phast\Core\Http\Request;
use Phast\Core\Http\Response;
use Phast\Core\Events\SimpleEvent;

class TestController {
   public function testEvents(Request $request): Response {
      try {
         // Test event system with a generic event
         $testEvent = new SimpleEvent('test.event', ['message' => 'Test event data', 'timestamp' => time()]);
         event($testEvent);

         return new Response(json_encode([
            'success' => true,
            'message' => 'Event dispatched successfully',
            'event' => get_class($testEvent),
            'event_name' => $testEvent->getName()
         ]), 200, ['Content-Type' => 'application/json']);

      } catch (\Exception $e) {
         return new Response(json_encode([
            'success' => false,
            'error' => $e->getMessage()
         ]), 500, ['Content-Type' => 'application/json']);
      }
   }

   public function testCache(Request $request): Response {
      try {
         // Test cache system
         $key = 'test_cache_key';
         $value = ['timestamp' => time(), 'data' => 'This is test data'];

         // Store in cache
         cache($key, $value, 300); // 5 minutes

         // Retrieve from cache
         $cached = cache($key);

         return new Response(json_encode([
            'success' => true,
            'message' => 'Cache working correctly',
            'cached_data' => $cached
         ]), 200, ['Content-Type' => 'application/json']);

      } catch (\Exception $e) {
         return new Response(json_encode([
            'success' => false,
            'error' => $e->getMessage()
         ]), 500, ['Content-Type' => 'application/json']);
      }
   }

   public function testRateLimit(Request $request): Response {
      try {
         // Test rate limiting
         $identifier = $request->getIp() ?? 'test_user';
         $allowed = rate_limit($identifier);
         $info = rate_limit_info($identifier);

         return new Response(json_encode([
            'success' => true,
            'rate_limit_allowed' => $allowed,
            'rate_limit_info' => $info
         ]), 200, ['Content-Type' => 'application/json']);

      } catch (\Exception $e) {
         return new Response(json_encode([
            'success' => false,
            'error' => $e->getMessage()
         ]), 500, ['Content-Type' => 'application/json']);
      }
   }
}
