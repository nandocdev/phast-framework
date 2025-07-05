<?php
/**
 * @package     phast/app
 * @subpackage  Modules/Users/DataTransfer
 * @file        UpdateUserDTO
 * @author      Fernando Castillo <fdocst@gmail.com>
 * @date        2025-07-04
 * @version     1.0.0
 * @description DTO for updating a user
 */

declare(strict_types=1);

namespace Phast\App\Modules\Users\DataTransfer;

use Phast\Core\DataTransfer\DataTransferObject;
use Phast\Core\Validation\ValidationResult;
use Phast\Core\Validation\SimpleValidator;

/**
 * Data Transfer Object for updating a user
 */
class UpdateUserDTO extends DataTransferObject {
   public readonly ?string $name;
   public readonly ?string $email;
   public readonly ?string $password;

   protected function validate(array $data): ValidationResult {
      // Remove null/empty values for partial updates
      $filteredData = array_filter($data, fn($value) => $value !== null && $value !== '');

      $validator = new SimpleValidator();
      return $validator->validateWithResult($filteredData, $this->getRules());
   }

   protected function populate(array $data): static {
      $instance = clone $this;
      $instance->name = $data['name'] ?? null;
      $instance->email = isset($data['email']) ? filter_var($data['email'], FILTER_SANITIZE_EMAIL) : null;
      $instance->password = $data['password'] ?? null;

      return $instance;
   }

   public function toArray(): array {
      return array_filter([
         'name' => $this->name,
         'email' => $this->email,
         'password' => $this->password,
      ], fn($value) => $value !== null);
   }

   public function getModifiedFields(): array {
      return $this->toArray();
   }

   protected function getRules(): array {
      return [
         'name' => ['string', 'min:2', 'max:100'],
         'email' => ['email', 'max:255'],
         'password' => ['string', 'min:6', 'max:255'],
      ];
   }

   /**
    * Get password hash if password is being updated
    */
   public function getHashedPassword(): ?string {
      return $this->password ? password_hash($this->password, PASSWORD_DEFAULT) : null;
   }

   /**
    * Check if any field is being updated
    */
   public function hasUpdates(): bool {
      return !empty($this->getModifiedFields());
   }
}
