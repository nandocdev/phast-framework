<?php
/**
 * @package     phast/core
 * @subpackage  Events
 * @file        EventDispatcherInterface
 * @author      Fernando Castillo <fdocst@gmail.com>
 * @date        2025-07-05
 * @version     1.0.0
 * @description Interface for event dispatcher
 */

declare(strict_types=1);

namespace Phast\Core\Events;

/**
 * Interface for event dispatchers
 */
interface EventDispatcherInterface {
   /**
    * Register an event listener
    */
   public function listen(string $eventName, ListenerInterface $listener): void;

   /**
    * Register an event listener using a callable
    */
   public function addListener(string $eventName, callable $listener, int $priority = 0): void;

   /**
    * Dispatch an event
    */
   public function dispatch(EventInterface $event): EventInterface;

   /**
    * Remove all listeners for an event
    */
   public function forget(string $eventName): void;

   /**
    * Get all listeners for an event
    */
   public function getListeners(string $eventName): array;

   /**
    * Check if an event has listeners
    */
   public function hasListeners(string $eventName): bool;
}
