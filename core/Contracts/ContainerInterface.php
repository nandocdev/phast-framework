<?php
/**
 * @package     phast/core
 * @subpackage  Contracts
 * @file        ContainerInterface
 * @author      Fernando Castillo <fdocst@gmail.com>
 * @date        2025-07-04
 * @version     1.0.0
 * @description Container interface for dependency injection
 */

declare(strict_types=1);

namespace Phast\Core\Contracts;

interface ContainerInterface {
   /**
    * Bind a class or interface to the container
    */
   public function bind(string $abstract, mixed $concrete = null, bool $shared = false): void;

   /**
    * Bind a singleton to the container
    */
   public function singleton(string $abstract, mixed $concrete = null): void;

   /**
    * Get an instance from the container
    */
   public function get(string $abstract): mixed;

   /**
    * Check if binding exists
    */
   public function has(string $abstract): bool;

   /**
    * Register a service provider
    */
   public function register(ServiceProviderInterface $provider): void;
}
