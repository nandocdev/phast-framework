<?php
/**
 * @package     phast/app
 * @subpackage  Modules/Users/Listeners
 * @file        LogUserActivityListener
 * @author      Fernando Castillo <fdocst@gmail.com>
 * @date        2025-07-05
 * @version     1.0.0
 * @description Listener to log user activity
 */

declare(strict_types=1);

namespace Phast\App\Modules\Users\Listeners;

use Phast\Core\Events\EventInterface;
use Phast\Core\Events\ListenerInterface;
use Phast\App\Modules\Users\Events\UserCreated;
use Phast\App\Modules\Users\Events\UserUpdated;
use Phast\App\Modules\Users\Events\UserDeleted;
use Psr\Log\LoggerInterface;

/**
 * Logs user activity for audit purposes
 */
class LogUserActivityListener implements ListenerInterface {
   private LoggerInterface $logger;

   public function __construct(LoggerInterface $logger) {
      $this->logger = $logger;
   }

   public function handle(EventInterface $event): void {
      match (get_class($event)) {
         UserCreated::class => $this->logUserCreated($event),
         UserUpdated::class => $this->logUserUpdated($event),
         UserDeleted::class => $this->logUserDeleted($event),
         default => null,
      };
   }

   public function getPriority(): int {
      return 5; // Medium priority
   }

   private function logUserCreated(EventInterface $event): void {
      if (!$event instanceof UserCreated) {
         return;
      }

      $this->logger->info('User created', [
         'action' => 'user_created',
         'user_id' => $event->getUserId(),
         'user_email' => $event->getUserEmail(),
         'timestamp' => $event->getTimestamp()->format('Y-m-d H:i:s'),
      ]);
   }

   private function logUserUpdated(EventInterface $event): void {
      if (!$event instanceof UserUpdated) {
         return;
      }

      $this->logger->info('User updated', [
         'action' => 'user_updated',
         'user_id' => $event->getUser()->getId(),
         'changes' => $event->getChanges(),
         'timestamp' => $event->getTimestamp()->format('Y-m-d H:i:s'),
      ]);
   }

   private function logUserDeleted(EventInterface $event): void {
      if (!$event instanceof UserDeleted) {
         return;
      }

      $this->logger->warning('User deleted', [
         'action' => 'user_deleted',
         'user_id' => $event->getUserId(),
         'user_email' => $event->getUserEmail(),
         'timestamp' => $event->getTimestamp()->format('Y-m-d H:i:s'),
      ]);
   }
}
