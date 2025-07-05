<?php
/**
 * @package     phast/app
 * @subpackage  Modules/Users/Events
 * @file        UserCreated
 * @author      Fernando Castillo <fdocst@gmail.com>
 * @date        2025-07-05
 * @version     1.0.0
 * @description Event fired when a user is created
 */

declare(strict_types=1);

namespace Phast\App\Modules\Users\Events;

use Phast\Core\Events\Event;
use Phast\App\Modules\Users\Models\Entities\UserEntity;

/**
 * Event fired when a user is created
 */
class UserCreated extends Event {
   public function __construct(UserEntity $user) {
      parent::__construct([
         'user_id' => $user->getId(),
         'user_email' => $user->getEmail(),
         'user_name' => $user->getName(),
         'user' => $user,
      ]);
   }

   public function getName(): string {
      return 'user.created';
   }

   public function getUser(): UserEntity {
      return $this->get('user');
   }

   public function getUserId(): int {
      return $this->get('user_id');
   }

   public function getUserEmail(): string {
      return $this->get('user_email');
   }
}
