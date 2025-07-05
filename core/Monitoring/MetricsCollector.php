<?php

declare(strict_types=1);

namespace Phast\Core\Monitoring;

/**
 * Sistema de métricas simple para monitoring
 */
class MetricsCollector {
   private array $metrics = [];
   private array $timers = [];

   /**
    * Incrementa un contador
    */
   public function increment(string $metric, array $tags = []): void {
      $key = $this->buildKey($metric, $tags);
      $this->metrics[$key] = ($this->metrics[$key] ?? 0) + 1;
   }

   /**
    * Establece un gauge (valor instantáneo)
    */
   public function gauge(string $metric, float $value, array $tags = []): void {
      $key = $this->buildKey($metric, $tags);
      $this->metrics[$key] = $value;
   }

   /**
    * Inicia un timer
    */
   public function startTimer(string $metric): string {
      $timerId = uniqid($metric . '_', true);
      $this->timers[$timerId] = [
         'metric' => $metric,
         'start' => microtime(true)
      ];

      return $timerId;
   }

   /**
    * Finaliza un timer y registra la duración
    */
   public function endTimer(string $timerId, array $tags = []): float {
      if (!isset($this->timers[$timerId])) {
         throw new \InvalidArgumentException("Timer '{$timerId}' not found");
      }

      $timer = $this->timers[$timerId];
      $duration = microtime(true) - $timer['start'];

      $this->gauge($timer['metric'] . '.duration', $duration, $tags);
      unset($this->timers[$timerId]);

      return $duration;
   }

   /**
    * Mide el tiempo de ejecución de una función
    */
   public function time(string $metric, callable $callback, array $tags = []): mixed {
      $timerId = $this->startTimer($metric);

      try {
         $result = $callback();
         $this->endTimer($timerId, $tags);

         return $result;
      } catch (\Throwable $e) {
         $this->endTimer($timerId, array_merge($tags, ['status' => 'error']));
         throw $e;
      }
   }

   /**
    * Obtiene todas las métricas
    */
   public function getMetrics(): array {
      return $this->metrics;
   }

   /**
    * Resetea todas las métricas
    */
   public function reset(): void {
      $this->metrics = [];
      $this->timers = [];
   }

   private function buildKey(string $metric, array $tags): string {
      if (empty($tags)) {
         return $metric;
      }

      $tagStrings = [];
      foreach ($tags as $key => $value) {
         $tagStrings[] = "{$key}:{$value}";
      }

      return $metric . '{' . implode(',', $tagStrings) . '}';
   }
}
