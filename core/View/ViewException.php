<?php
/**
 * @package     phast/core
 * @subpackage  View
 * @file        ViewException
 * @author      Fernando Castillo <fdocst@gmail.com>
 * @date        2025-07-04
 * @version     1.0.0
 * @description View-related exception class
 */

declare(strict_types=1);

namespace Phast\Core\View;

use Exception;

class ViewException extends Exception {
   /**
    * ViewException constructor
    *
    * @param string $message Exception message
    * @param int $code Exception code
    * @param Exception|null $previous Previous exception
    */
   public function __construct(string $message = "", int $code = 0, ?Exception $previous = null) {
      parent::__construct($message, $code, $previous);
   }
}
