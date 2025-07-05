<?php
/**
 * @package     phast/core
 * @subpackage  Events
 * @file        Event
 * @author      Fernando Castillo <fdocst@gmail.com>
 * @date        2025-07-05
 * @version     1.0.0
 * @description Base event class
 */

declare(strict_types=1);

namespace Phast\Core\Events;

/**
 * Base implementation for events
 */
abstract class Event implements EventInterface {
   protected array $payload;
   protected \DateTimeImmutable $timestamp;
   protected bool $propagationStopped = false;

   public function __construct(array $payload = []) {
      $this->payload = $payload;
      $this->timestamp = new \DateTimeImmutable();
   }

   public function getPayload(): array {
      return $this->payload;
   }

   public function getTimestamp(): \DateTimeImmutable {
      return $this->timestamp;
   }

   public function isPropagationStopped(): bool {
      return $this->propagationStopped;
   }

   public function stopPropagation(): void {
      $this->propagationStopped = true;
   }

   /**
    * Get a specific payload value
    */
   public function get(string $key, $default = null) {
      return $this->payload[$key] ?? $default;
   }

   /**
    * Set a payload value
    */
   public function set(string $key, $value): void {
      $this->payload[$key] = $value;
   }
}
