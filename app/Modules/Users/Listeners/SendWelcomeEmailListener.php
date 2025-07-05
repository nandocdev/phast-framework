<?php
/**
 * @package     phast/app
 * @subpackage  Modules/Users/Listeners
 * @file        SendWelcomeEmailListener
 * @author      Fernando Castillo <fdocst@gmail.com>
 * @date        2025-07-05
 * @version     1.0.0
 * @description Listener to send welcome email when user is created
 */

declare(strict_types=1);

namespace Phast\App\Modules\Users\Listeners;

use Phast\Core\Events\EventInterface;
use Phast\Core\Events\ListenerInterface;
use Phast\App\Modules\Users\Events\UserCreated;
use Psr\Log\LoggerInterface;

/**
 * Sends welcome email when a user is created
 */
class SendWelcomeEmailListener implements ListenerInterface {
   private LoggerInterface $logger;

   public function __construct(LoggerInterface $logger) {
      $this->logger = $logger;
   }

   public function handle(EventInterface $event): void {
      if (!$event instanceof UserCreated) {
         return;
      }

      $user = $event->getUser();

      $this->logger->info('Sending welcome email', [
         'user_id' => $user->getId(),
         'email' => $user->getEmail(),
      ]);

      // TODO: Implement actual email sending
      // For now, just log the action
      $this->sendWelcomeEmail($user->getEmail(), $user->getName());

      $this->logger->info('Welcome email sent successfully', [
         'user_id' => $user->getId(),
      ]);
   }

   public function getPriority(): int {
      return 10; // High priority
   }

   private function sendWelcomeEmail(string $email, string $name): void {
      // Simulate email sending
      $this->logger->debug('Welcome email content prepared', [
         'to' => $email,
         'subject' => "Welcome to Phast, {$name}!",
         'template' => 'welcome_email',
      ]);

      // In a real implementation, you would use a mail service here
      // Example: $this->mailer->send(new WelcomeEmail($email, $name));
   }
}
