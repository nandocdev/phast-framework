<?php
/**
 * @package     phast/core
 * @subpackage  Exceptions/Domain
 * @file        DuplicateEntityException
 * @author      Fernando Castillo <fdocst@gmail.com>
 * @date        2025-07-04
 * @version     1.0.0
 * @description Exception for duplicate entity violations
 */

declare(strict_types=1);

namespace Phast\Core\Exceptions\Domain;

/**
 * Thrown when trying to create an entity that already exists
 */
class DuplicateEntityException extends DomainException {
   public static function withField(string $entityType, string $field, string $value): self {
      return new self(
         message: "Entity '{$entityType}' with {$field} '{$value}' already exists",
         context: [
            'entity_type' => $entityType,
            'duplicate_field' => $field,
            'duplicate_value' => $value,
         ]
      );
   }

   public function getUserMessage(): string {
      $field = $this->context['duplicate_field'] ?? 'field';
      $value = $this->context['duplicate_value'] ?? 'value';

      return "A record with {$field} '{$value}' already exists.";
   }
}
