<?php
/**
 * @package     phast/app
 * @subpackage  Modules/Users/Models/ValueObjects
 * @file        Email
 * @author      Fernando Castillo <fdocst@gmail.com>
 * @date        2025-07-04
 * @version     1.0.0
 * @description Email value object
 */

declare(strict_types=1);

namespace Phast\App\Modules\Users\Models\ValueObjects;

class Email {
   private string $value;

   public function __construct(string $email) {
      $this->validate($email);
      $this->value = strtolower(trim($email));
   }

   public function getValue(): string {
      return $this->value;
   }

   public function getDomain(): string {
      return substr($this->value, strpos($this->value, '@') + 1);
   }

   public function getLocal(): string {
      return substr($this->value, 0, strpos($this->value, '@'));
   }

   public function equals(Email $other): bool {
      return $this->value === $other->value;
   }

   public function __toString(): string {
      return $this->value;
   }

   private function validate(string $email): void {
      if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
         throw new \InvalidArgumentException("Invalid email format: {$email}");
      }
   }
}
