<?php
/**
 * @package     phast/core
 * @subpackage  Events
 * @file        EventInterface
 * @author      Fernando Castillo <fdocst@gmail.com>
 * @date        2025-07-05
 * @version     1.0.0
 * @description Interface for all events
 */

declare(strict_types=1);

namespace Phast\Core\Events;

/**
 * Interface for all events in the system
 */
interface EventInterface {
   /**
    * Get the event name
    */
   public function getName(): string;

   /**
    * Get event payload data
    */
   public function getPayload(): array;

   /**
    * Get the timestamp when the event occurred
    */
   public function getTimestamp(): \DateTimeImmutable;

   /**
    * Check if event propagation should be stopped
    */
   public function isPropagationStopped(): bool;

   /**
    * Stop event propagation
    */
   public function stopPropagation(): void;
}
