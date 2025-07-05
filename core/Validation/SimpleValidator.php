<?php
/**
 * @package     phast/core
 * @subpackage  Validation
 * @file        SimpleValidator
 * @author      Fernando Castillo <fdocst@gmail.com>
 * @date        2025-07-04
 * @version     1.0.0
 * @description Simple validation implementation
 */

declare(strict_types=1);

namespace Phast\Core\Validation;

use Phast\Core\Exceptions\Domain\ValidationException;

/**
 * Simple validator implementation
 */
class SimpleValidator implements ValidatorInterface {
   private array $lastErrors = [];

   public function validate(array $data, array $rules): array {
      $this->lastErrors = [];
      $validatedData = [];

      foreach ($rules as $field => $fieldRules) {
         $value = $data[$field] ?? null;
         $fieldErrors = $this->validateField($field, $value, $fieldRules, $data);

         if (!empty($fieldErrors)) {
            $this->lastErrors[$field] = $fieldErrors;
         } else {
            $validatedData[$field] = $this->sanitizeValue($value, $fieldRules);
         }
      }

      if (!empty($this->lastErrors)) {
         throw ValidationException::withErrors($this->lastErrors);
      }

      return $validatedData;
   }

   public function passes(array $data, array $rules): bool {
      try {
         $this->validate($data, $rules);
         return true;
      } catch (ValidationException $e) {
         $this->lastErrors = $e->getErrors();
         return false;
      }
   }

   public function getErrors(): array {
      return $this->lastErrors;
   }

   /**
    * Validate data and return ValidationResult (convenience method)
    */
   public function validateWithResult(array $data, array $rules): ValidationResult {
      $this->lastErrors = [];
      $validatedData = [];

      foreach ($rules as $field => $fieldRules) {
         $value = $data[$field] ?? null;
         $fieldErrors = $this->validateField($field, $value, $fieldRules, $data);

         if (!empty($fieldErrors)) {
            $this->lastErrors[$field] = $fieldErrors;
         } else {
            $validatedData[$field] = $this->sanitizeValue($value, $fieldRules);
         }
      }

      return empty($this->lastErrors)
         ? ValidationResult::success($validatedData)
         : ValidationResult::failure($this->lastErrors);
   }

   private function validateField(string $field, $value, array $rules, array $allData): array {
      $errors = [];

      foreach ($rules as $rule) {
         if (is_string($rule)) {
            $error = $this->applyRule($field, $value, $rule, $allData);
            if ($error) {
               $errors[] = $error;
            }
         }
      }

      return $errors;
   }

   private function applyRule(string $field, $value, string $rule, array $allData): ?string {
      $parts = explode(':', $rule, 2);
      $ruleName = $parts[0];
      $parameters = isset($parts[1]) ? explode(',', $parts[1]) : [];

      return match ($ruleName) {
         'required' => $this->validateRequired($field, $value),
         'email' => $this->validateEmail($field, $value),
         'min' => $this->validateMin($field, $value, (int) $parameters[0]),
         'max' => $this->validateMax($field, $value, (int) $parameters[0]),
         'string' => $this->validateString($field, $value),
         'integer' => $this->validateInteger($field, $value),
         'numeric' => $this->validateNumeric($field, $value),
         'boolean' => $this->validateBoolean($field, $value),
         'unique' => $this->validateUnique($field, $value, $parameters[0] ?? '', $allData),
         default => null
      };
   }

   private function validateRequired(string $field, $value): ?string {
      if ($value === null || $value === '' || (is_array($value) && empty($value))) {
         return "The {$field} field is required.";
      }
      return null;
   }

   private function validateEmail(string $field, $value): ?string {
      if ($value !== null && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
         return "The {$field} must be a valid email address.";
      }
      return null;
   }

   private function validateMin(string $field, $value, int $min): ?string {
      if ($value !== null) {
         $length = is_string($value) ? strlen($value) : (is_numeric($value) ? $value : 0);
         if ($length < $min) {
            return "The {$field} must be at least {$min} characters.";
         }
      }
      return null;
   }

   private function validateMax(string $field, $value, int $max): ?string {
      if ($value !== null) {
         $length = is_string($value) ? strlen($value) : (is_numeric($value) ? $value : 0);
         if ($length > $max) {
            return "The {$field} may not be greater than {$max} characters.";
         }
      }
      return null;
   }

   private function validateString(string $field, $value): ?string {
      if ($value !== null && !is_string($value)) {
         return "The {$field} must be a string.";
      }
      return null;
   }

   private function validateInteger(string $field, $value): ?string {
      if ($value !== null && !is_int($value) && !ctype_digit((string) $value)) {
         return "The {$field} must be an integer.";
      }
      return null;
   }

   private function validateNumeric(string $field, $value): ?string {
      if ($value !== null && !is_numeric($value)) {
         return "The {$field} must be a number.";
      }
      return null;
   }

   private function validateBoolean(string $field, $value): ?string {
      if ($value !== null && !is_bool($value) && !in_array($value, [0, 1, '0', '1', 'true', 'false'], true)) {
         return "The {$field} must be true or false.";
      }
      return null;
   }

   private function validateUnique(string $field, $value, string $table, array $allData): ?string {
      // Placeholder for unique validation - would need repository injection
      // For now, just return null (no error)
      return null;
   }

   private function sanitizeValue($value, array $rules) {
      // Apply type conversion based on rules
      foreach ($rules as $rule) {
         if ($rule === 'integer' && $value !== null) {
            return (int) $value;
         }
         if ($rule === 'boolean' && $value !== null) {
            return filter_var($value, FILTER_VALIDATE_BOOLEAN);
         }
         if ($rule === 'string' && $value !== null) {
            return (string) $value;
         }
      }

      return $value;
   }
}
