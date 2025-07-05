<?php
/**
 * @package     phast/core
 * @subpackage  Exceptions/Domain
 * @file        ValidationException
 * @author      Fernando Castillo <fdocst@gmail.com>
 * @date        2025-07-04
 * @version     1.0.0
 * @description Exception for validation errors
 */

declare(strict_types=1);

namespace Phast\Core\Exceptions\Domain;

/**
 * Thrown when validation fails
 */
class ValidationException extends DomainException
{
    private array $errors = [];
    
    public static function withErrors(array $errors): self
    {
        $exception = new self(
            message: 'Validation failed',
            context: ['validation_errors' => $errors]
        );
        
        $exception->errors = $errors;
        return $exception;
    }
    
    public static function withSingleError(string $field, string $message): self
    {
        return self::withErrors([$field => [$message]]);
    }
    
    public function getErrors(): array
    {
        return $this->errors;
    }
    
    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }
    
    public function getErrorsForField(string $field): array
    {
        return $this->errors[$field] ?? [];
    }
    
    public function getUserMessage(): string
    {
        if (empty($this->errors)) {
            return 'Validation failed';
        }
        
        $firstField = array_key_first($this->errors);
        $firstError = is_array($this->errors[$firstField]) 
            ? $this->errors[$firstField][0] 
            : $this->errors[$firstField];
            
        return $firstError;
    }
}
