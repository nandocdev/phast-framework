<?php
/**
 * @package     phast/core
 * @subpackage  Providers
 * @file        RateLimitServiceProvider
 * @author      Fernando Castillo <fdocst@gmail.com>
 * @date        2025-07-05
 * @version     1.0.0
 * @description Service provider for rate limiting system
 */

declare(strict_types=1);

namespace Phast\Core\Providers;

use Phast\Core\Contracts\ServiceProviderInterface;
use Phast\Core\Contracts\ContainerInterface;
use Phast\Core\RateLimit\RateLimiterInterface;
use Phast\Core\RateLimit\TokenBucketRateLimiter;
use Phast\Core\Cache\CacheInterface;

class RateLimitServiceProvider implements ServiceProviderInterface {
   public function register(ContainerInterface $container): void {
      $container->singleton(RateLimiterInterface::class, function () use ($container) {
         $cache = $container->get(CacheInterface::class);

         $maxAttempts = (int) (env('RATE_LIMIT_MAX_ATTEMPTS', 60));
         $decayMinutes = (int) (env('RATE_LIMIT_DECAY_MINUTES', 1));

         return new TokenBucketRateLimiter(
            $cache,
            $maxAttempts,
            $decayMinutes
         );
      });
   }

   public function boot(ContainerInterface $container): void {
      // Boot rate limiting - any initialization needed
   }
}
