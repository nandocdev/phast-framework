<?php
/**
 * @package     phast/core
 * @subpackage  Events
 * @file        ListenerInterface
 * @author      Fernando Castillo <fdocst@gmail.com>
 * @date        2025-07-05
 * @version     1.0.0
 * @description Interface for event listeners
 */

declare(strict_types=1);

namespace Phast\Core\Events;

/**
 * Interface for event listeners
 */
interface ListenerInterface {
   /**
    * Handle the event
    */
   public function handle(EventInterface $event): void;

   /**
    * Get the priority of this listener (higher = executed first)
    */
   public function getPriority(): int;
}
