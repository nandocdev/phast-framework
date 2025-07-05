<?php
/**
 * @package     phast/core
 * @subpackage  Exceptions/Infrastructure
 * @file        InfrastructureException
 * @author      Fernando Castillo <fdocst@gmail.com>
 * @date        2025-07-04
 * @version     1.0.0
 * @description Base exception for infrastructure-related errors
 */

declare(strict_types=1);

namespace Phast\Core\Exceptions\Infrastructure;

use Phast\Core\Exceptions\PhastException;

/**
 * Base class for all infrastructure-related exceptions
 */
abstract class InfrastructureException extends PhastException
{
    public function getType(): string
    {
        return 'infrastructure';
    }
    
    public function getUserMessage(): string
    {
        return 'A system error occurred. Please try again later.';
    }
}
