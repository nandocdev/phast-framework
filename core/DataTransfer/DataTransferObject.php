<?php
/**
 * @package     phast/core
 * @subpackage  DataTransfer
 * @file        DataTransferObject
 * @author      Fernando Castillo <fdocst@gmail.com>
 * @date        2025-07-04
 * @version     1.0.0
 * @description Base class for Data Transfer Objects
 */

declare(strict_types=1);

namespace Phast\Core\DataTransfer;

use Phast\Core\Exceptions\Domain\ValidationException;
use Phast\Core\Validation\ValidationResult;

/**
 * Base class for all DTOs with validation capabilities
 */
abstract class DataTransferObject
{
    /**
     * Create DTO from array data with validation
     */
    public static function fromArray(array $data): static
    {
        $instance = new static();
        $validationResult = $instance->validate($data);
        
        if ($validationResult->hasErrors()) {
            throw ValidationException::withErrors($validationResult->getErrors());
        }
        
        return $instance->populate($validationResult->getValidatedData());
    }
    
    /**
     * Create DTO without validation (use with caution)
     */
    public static function fromValidatedArray(array $data): static
    {
        $instance = new static();
        return $instance->populate($data);
    }
    
    /**
     * Validate input data
     */
    abstract protected function validate(array $data): ValidationResult;
    
    /**
     * Populate DTO properties from validated data
     */
    abstract protected function populate(array $data): static;
    
    /**
     * Convert DTO to array
     */
    abstract public function toArray(): array;
    
    /**
     * Get validation rules for this DTO
     */
    abstract protected function getRules(): array;
    
    /**
     * Get only the fields that have been set/modified
     */
    public function getModifiedFields(): array
    {
        return $this->toArray();
    }
}
