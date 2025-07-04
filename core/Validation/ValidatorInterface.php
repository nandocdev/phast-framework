<?php
/**
 * @package     phast/core
 * @subpackage  Validation
 * @file        ValidatorInterface
 * @author      Fernando Castillo <fdocst@gmail.com>
 * @date        2025-07-04
 * @version     1.0.0
 * @description Validator interface
 */

declare(strict_types=1);

namespace Phast\Core\Validation;

interface ValidatorInterface
{
    /**
     * Validate data against rules
     * 
     * @param array $data Data to validate
     * @param array $rules Validation rules
     * @return array Validated data
     * @throws ValidationException
     */
    public function validate(array $data, array $rules): array;

    /**
     * Check if validation passes
     */
    public function passes(array $data, array $rules): bool;

    /**
     * Get validation errors
     */
    public function getErrors(): array;
}
