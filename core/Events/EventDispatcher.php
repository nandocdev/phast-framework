<?php
/**
 * @package     phast/core
 * @subpackage  Events
 * @file        EventDispatcher
 * @author      Fernando Castillo <fdocst@gmail.com>
 * @date        2025-07-05
 * @version     1.0.0
 * @description Event dispatcher implementation
 */

declare(strict_types=1);

namespace Phast\Core\Events;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Event dispatcher implementation
 */
class EventDispatcher implements EventDispatcherInterface {
   private array $listeners = [];
   private LoggerInterface $logger;

   public function __construct(LoggerInterface $logger = null) {
      $this->logger = $logger ?? new NullLogger();
   }

   public function listen(string $eventName, ListenerInterface $listener): void {
      $this->addListener($eventName, [$listener, 'handle'], $listener->getPriority());
   }

   public function addListener(string $eventName, callable $listener, int $priority = 0): void {
      $this->listeners[$eventName][] = [
         'listener' => $listener,
         'priority' => $priority,
      ];

      // Sort by priority (higher first)
      uasort($this->listeners[$eventName], function ($a, $b) {
         return $b['priority'] <=> $a['priority'];
      });

      $this->logger->debug('Event listener registered', [
         'event' => $eventName,
         'priority' => $priority,
      ]);
   }

   public function dispatch(EventInterface $event): EventInterface {
      $eventName = $event->getName();

      $this->logger->info('Dispatching event', [
         'event' => $eventName,
         'payload' => $event->getPayload(),
      ]);

      if (!$this->hasListeners($eventName)) {
         $this->logger->debug('No listeners found for event', ['event' => $eventName]);
         return $event;
      }

      $listeners = $this->getListeners($eventName);
      $executedListeners = 0;

      foreach ($listeners as $listenerData) {
         if ($event->isPropagationStopped()) {
            $this->logger->debug('Event propagation stopped', [
               'event' => $eventName,
               'executed_listeners' => $executedListeners,
            ]);
            break;
         }

         try {
            $listenerData['listener']($event);
            $executedListeners++;
         } catch (\Throwable $e) {
            $this->logger->error('Error executing event listener', [
               'event' => $eventName,
               'error' => $e->getMessage(),
               'file' => $e->getFile(),
               'line' => $e->getLine(),
            ]);

            // Continue with other listeners unless it's a critical error
            if ($e instanceof \Error) {
               throw $e;
            }
         }
      }

      $this->logger->info('Event processing completed', [
         'event' => $eventName,
         'executed_listeners' => $executedListeners,
         'total_listeners' => count($listeners),
      ]);

      return $event;
   }

   public function forget(string $eventName): void {
      unset($this->listeners[$eventName]);
      $this->logger->debug('Event listeners removed', ['event' => $eventName]);
   }

   public function getListeners(string $eventName): array {
      return $this->listeners[$eventName] ?? [];
   }

   public function hasListeners(string $eventName): bool {
      return !empty($this->listeners[$eventName]);
   }

   /**
    * Get all registered events
    */
   public function getEvents(): array {
      return array_keys($this->listeners);
   }

   /**
    * Get statistics about registered listeners
    */
   public function getStats(): array {
      $stats = [
         'total_events' => count($this->listeners),
         'total_listeners' => 0,
         'events' => [],
      ];

      foreach ($this->listeners as $eventName => $listeners) {
         $listenerCount = count($listeners);
         $stats['total_listeners'] += $listenerCount;
         $stats['events'][$eventName] = $listenerCount;
      }

      return $stats;
   }
}
