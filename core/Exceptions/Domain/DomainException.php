<?php
/**
 * @package     phast/core
 * @subpackage  Exceptions/Domain
 * @file        DomainException
 * @author      Fernando Castillo <fdocst@gmail.com>
 * @date        2025-07-04
 * @version     1.0.0
 * @description Base exception for domain-related errors
 */

declare(strict_types=1);

namespace Phast\Core\Exceptions\Domain;

use Phast\Core\Exceptions\PhastException;

/**
 * Base class for all domain-related exceptions
 */
abstract class DomainException extends PhastException {
   public function getType(): string {
      return 'domain';
   }
}
