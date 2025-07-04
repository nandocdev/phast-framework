<?php
/**
 * @package     phast/core
 * @subpackage  Application
 * @file        Container
 * @author      Fernando Castillo <fdocst@gmail.com>
 * @date        2025-07-04
 * @version     1.0.0
 * @description Dependency injection container
 */

declare(strict_types=1);

namespace Phast\Core\Application;

use Phast\Core\Contracts\ContainerInterface;
use Phast\Core\Contracts\ServiceProviderInterface;
use ReflectionClass;
use ReflectionParameter;

class Container implements ContainerInterface {
   private static ?self $instance = null;
   private array $bindings = [];
   private array $instances = [];
   private array $shared = [];
   private array $providers = [];

   private function __construct() {
      // Singleton pattern
   }

   public static function getInstance(): self {
      if (self::$instance === null) {
         self::$instance = new self();
      }

      return self::$instance;
   }

   public function bind(string $abstract, mixed $concrete = null, bool $shared = false): void {
      $this->bindings[$abstract] = [
         'concrete' => $concrete ?? $abstract,
         'shared' => $shared
      ];

      if ($shared) {
         $this->shared[$abstract] = true;
      }

      // Remove existing instance if rebinding
      unset($this->instances[$abstract]);
   }

   public function singleton(string $abstract, mixed $concrete = null): void {
      $this->bind($abstract, $concrete, true);
   }

   public function get(string $abstract): mixed {
      // Return existing singleton instance
      if (isset($this->instances[$abstract])) {
         return $this->instances[$abstract];
      }

      $concrete = $this->getConcrete($abstract);
      $object = $this->resolve($concrete);

      // Store singleton instance
      if ($this->isShared($abstract)) {
         $this->instances[$abstract] = $object;
      }

      return $object;
   }

   public function has(string $abstract): bool {
      return isset($this->bindings[$abstract]) ||
         isset($this->instances[$abstract]) ||
         class_exists($abstract);
   }

   public function register(ServiceProviderInterface $provider): void {
      $this->providers[] = $provider;
      $provider->register($this);
   }

   public function boot(): void {
      foreach ($this->providers as $provider) {
         $provider->boot($this);
      }
   }

   private function getConcrete(string $abstract): mixed {
      if (!isset($this->bindings[$abstract])) {
         return $abstract;
      }

      return $this->bindings[$abstract]['concrete'];
   }

   private function isShared(string $abstract): bool {
      return isset($this->shared[$abstract]) ||
         (isset($this->bindings[$abstract]) && $this->bindings[$abstract]['shared']);
   }

   private function resolve(mixed $concrete): mixed {
      if (is_callable($concrete)) {
         return $concrete($this);
      }

      if (!is_string($concrete)) {
         return $concrete;
      }

      return $this->build($concrete);
   }

   private function build(string $concrete): object {
      $reflector = new ReflectionClass($concrete);

      if (!$reflector->isInstantiable()) {
         throw new \InvalidArgumentException("Class {$concrete} is not instantiable");
      }

      $constructor = $reflector->getConstructor();

      if (is_null($constructor)) {
         return new $concrete;
      }

      $dependencies = $this->resolveDependencies($constructor->getParameters());

      return $reflector->newInstanceArgs($dependencies);
   }

   private function resolveDependencies(array $parameters): array {
      $dependencies = [];

      foreach ($parameters as $parameter) {
         $dependency = $this->resolveDependency($parameter);
         $dependencies[] = $dependency;
      }

      return $dependencies;
   }

   private function resolveDependency(ReflectionParameter $parameter): mixed {
      $type = $parameter->getType();

      if (!$type || $type->isBuiltin()) {
         if ($parameter->isDefaultValueAvailable()) {
            return $parameter->getDefaultValue();
         }

         throw new \InvalidArgumentException(
            "Cannot resolve dependency {$parameter->getName()}"
         );
      }

      $name = $type->getName();

      return $this->get($name);
   }
}
