<?php
/**
 * @package     phast/app
 * @subpackage  Modules/Users/Events
 * @file        UserUpdated
 * @author      Fernando Castillo <fdocst@gmail.com>
 * @date        2025-07-05
 * @version     1.0.0
 * @description Event fired when a user is updated
 */

declare(strict_types=1);

namespace Phast\App\Modules\Users\Events;

use Phast\Core\Events\Event;
use Phast\App\Modules\Users\Models\Entities\UserEntity;

/**
 * Event fired when a user is updated
 */
class UserUpdated extends Event {
   public function __construct(UserEntity $user, array $changes = []) {
      parent::__construct([
         'user_id' => $user->getId(),
         'user_email' => $user->getEmail(),
         'user_name' => $user->getName(),
         'user' => $user,
         'changes' => $changes,
      ]);
   }

   public function getName(): string {
      return 'user.updated';
   }

   public function getUser(): UserEntity {
      return $this->get('user');
   }

   public function getChanges(): array {
      return $this->get('changes', []);
   }
}
