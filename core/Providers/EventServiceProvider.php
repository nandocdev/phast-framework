<?php
/**
 * @package     phast/core
 * @subpackage  Providers
 * @file        EventServiceProvider
 * @author      Fernando Castillo <fdocst@gmail.com>
 * @date        2025-07-05
 * @version     1.0.0
 * @description Service provider for event system
 */

declare(strict_types=1);

namespace Phast\Core\Providers;

use Phast\Core\Contracts\ServiceProviderInterface;
use Phast\Core\Contracts\ContainerInterface;
use Phast\Core\Events\EventDispatcher;
use Phast\Core\Events\EventDispatcherInterface;
use Psr\Log\LoggerInterface;

class EventServiceProvider implements ServiceProviderInterface {
   public function register(ContainerInterface $container): void {
      $container->singleton(EventDispatcherInterface::class, function () use ($container) {
         $logger = $container->get(LoggerInterface::class);
         return new EventDispatcher($logger);
      });
   }

   public function boot(ContainerInterface $container): void {
      $eventDispatcher = $container->get(EventDispatcherInterface::class);

      // Register user event listeners if classes exist - skip registration for now to avoid dependencies
      // This would be better handled by the user module provider when it boots

      // For now, just log that the event system is ready
      if (method_exists($container, 'get')) {
         try {
            $logger = $container->get(\Psr\Log\LoggerInterface::class);
            $logger->info('Event system initialized and ready');
         } catch (\Exception $e) {
            // Logger not available, skip logging
         }
      }
   }
}
