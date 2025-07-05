<?php
/**
 * @package     phast/core
 * @subpackage  Exceptions
 * @file        PhastException
 * @author      Fernando Castillo <fdocst@gmail.com>
 * @date        2025-07-04
 * @version     1.0.0
 * @description Base exception for all Phast framework exceptions
 */

declare(strict_types=1);

namespace Phast\Core\Exceptions;

use Exception;

/**
 * Base exception class for all Phast framework exceptions
 */
abstract class PhastException extends Exception {
   protected array $context = [];

   public function __construct(
      string $message = '',
      int $code = 0,
      ?Exception $previous = null,
      array $context = []
   ) {
      parent::__construct($message, $code, $previous);
      $this->context = $context;
   }

   public function getContext(): array {
      return $this->context;
   }

   public function setContext(array $context): self {
      $this->context = $context;
      return $this;
   }

   /**
    * Get exception type for logging/categorization
    */
   abstract public function getType(): string;

   /**
    * Get user-friendly message
    */
   public function getUserMessage(): string {
      return $this->getMessage();
   }

   /**
    * Get error details for debugging
    */
   public function getErrorDetails(): array {
      return [
         'type' => $this->getType(),
         'message' => $this->getMessage(),
         'code' => $this->getCode(),
         'file' => $this->getFile(),
         'line' => $this->getLine(),
         'context' => $this->getContext(),
      ];
   }
}
