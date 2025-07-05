<?php
/**
 * @package     phast/core
 * @subpackage  Validation
 * @file        ValidationResult
 * @author      Fernando Castillo <fdocst@gmail.com>
 * @date        2025-07-04
 * @version     1.0.0
 * @description Result of validation process
 */

declare(strict_types=1);

namespace Phast\Core\Validation;

/**
 * Represents the result of a validation process
 */
class ValidationResult {
   private bool $isValid;
   private array $errors;
   private array $validatedData;

   public function __construct(bool $isValid, array $errors = [], array $validatedData = []) {
      $this->isValid = $isValid;
      $this->errors = $errors;
      $this->validatedData = $validatedData;
   }

   public static function success(array $validatedData = []): self {
      return new self(true, [], $validatedData);
   }

   public static function failure(array $errors): self {
      return new self(false, $errors, []);
   }

   public function isValid(): bool {
      return $this->isValid;
   }

   public function hasErrors(): bool {
      return !$this->isValid;
   }

   public function getErrors(): array {
      return $this->errors;
   }

   public function getErrorsForField(string $field): array {
      return $this->errors[$field] ?? [];
   }

   public function getValidatedData(): array {
      return $this->validatedData;
   }

   public function getFirstError(): ?string {
      if (empty($this->errors)) {
         return null;
      }

      $firstField = array_key_first($this->errors);
      $fieldErrors = $this->errors[$firstField];

      return is_array($fieldErrors) ? $fieldErrors[0] : $fieldErrors;
   }
}
