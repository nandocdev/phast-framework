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

      // Register user event listeners if classes exist
      if (class_exists(\Phast\App\Modules\Users\Events\UserCreated::class)) {
         $eventDispatcher->listen(
            \Phast\App\Modules\Users\Events\UserCreated::class,
            \Phast\App\Modules\Users\Listeners\SendWelcomeEmailListener::class
         );

         $eventDispatcher->listen(
            \Phast\App\Modules\Users\Events\UserCreated::class,
            \Phast\App\Modules\Users\Listeners\LogUserActivityListener::class
         );
      }

      if (class_exists(\Phast\App\Modules\Users\Events\UserUpdated::class)) {
         $eventDispatcher->listen(
            \Phast\App\Modules\Users\Events\UserUpdated::class,
            \Phast\App\Modules\Users\Listeners\LogUserActivityListener::class
         );
      }

      if (class_exists(\Phast\App\Modules\Users\Events\UserDeleted::class)) {
         $eventDispatcher->listen(
            \Phast\App\Modules\Users\Events\UserDeleted::class,
            \Phast\App\Modules\Users\Listeners\LogUserActivityListener::class
         );
      }
   }
}
