<?php
/**
 * @package     phast/core
 * @subpackage  Validation
 * @file        ValidationException
 * @author      Fernando Castillo <fdocst@gmail.com>
 * @date        2025-07-04
 * @version     1.0.0
 * @description Validation exception
 */

declare(strict_types=1);

namespace Phast\Core\Validation;

class ValidationException extends \Exception
{
    private array $errors;

    public function __construct(array $errors, string $message = 'Validation failed')
    {
        $this->errors = $errors;
        parent::__construct($message);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
