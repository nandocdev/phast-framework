<?php
/**
 * @package     phast/app
 * @subpackage  Modules/Users/Events
 * @file        UserDeleted
 * @author      Fernando Castillo <fdocst@gmail.com>
 * @date        2025-07-05
 * @version     1.0.0
 * @description Event fired when a user is deleted
 */

declare(strict_types=1);

namespace Phast\App\Modules\Users\Events;

use Phast\Core\Events\Event;

/**
 * Event fired when a user is deleted
 */
class UserDeleted extends Event {
   public function __construct(int $userId, string $userEmail) {
      parent::__construct([
         'user_id' => $userId,
         'user_email' => $userEmail,
      ]);
   }

   public function getName(): string {
      return 'user.deleted';
   }

   public function getUserId(): int {
      return $this->get('user_id');
   }

   public function getUserEmail(): string {
      return $this->get('user_email');
   }
}
