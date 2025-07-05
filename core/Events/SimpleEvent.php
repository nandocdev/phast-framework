<?php
/**
 * @package     phast/core
 * @subpackage  Events
 * @file        SimpleEvent
 * @author      Fernando Castillo <fdocst@gmail.com>
 * @date        2025-07-05
 * @version     1.0.0
 * @description Simple event implementation for testing
 */

declare(strict_types=1);

namespace Phast\Core\Events;

/**
 * Simple event implementation for testing and general use
 */
class SimpleEvent extends Event {
   private string $name;

   public function __construct(string $name, array $payload = []) {
      $this->name = $name;
      parent::__construct($payload);
   }

   public function getName(): string {
      return $this->name;
   }
}
