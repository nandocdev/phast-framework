<?php
/**
 * @package     phast/core
 * @subpackage  Exceptions/Infrastructure
 * @file        DatabaseException
 * @author      Fernando Castillo <fdocst@gmail.com>
 * @date        2025-07-04
 * @version     1.0.0
 * @description Exception for database-related errors
 */

declare(strict_types=1);

namespace Phast\Core\Exceptions\Infrastructure;

use PDOException;

/**
 * Thrown when database operations fail
 */
class DatabaseException extends InfrastructureException {
   public static function fromPDOException(PDOException $e, string $operation = ''): self {
      $message = $operation ? "Database error during {$operation}: " : 'Database error: ';
      $message .= $e->getMessage();

      return new self(
         message: $message,
         code: (int) $e->getCode(),
         previous: $e,
         context: [
            'operation' => $operation,
            'sql_state' => $e->getCode(),
         ]
      );
   }

   public static function connectionFailed(string $details = ''): self {
      $message = 'Failed to connect to database';
      if ($details) {
         $message .= ": {$details}";
      }

      return new self(
         message: $message,
         context: ['connection_details' => $details]
      );
   }
}
