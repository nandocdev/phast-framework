<?php
/**
 * @package     phast/core
 * @subpackage  Routing
 * @file        Route
 * @author      Fernando Castillo <fdocst@gmail.com>
 * @date        2025-07-04
 * @version     1.0.0
 * @description Route definition class
 */

declare(strict_types=1);

namespace Phast\Core\Routing;

class Route {
   private string $method;
   private string $uri;
   private mixed $handler;
   private array $middleware = [];
   private ?string $name = null;

   public function __construct(string $method, string $uri, mixed $handler) {
      $this->method = $method;
      $this->uri = $uri;
      $this->handler = $handler;
   }

   public function middleware(string|array $middleware): self {
      $this->middleware = array_merge($this->middleware, (array) $middleware);
      return $this;
   }

   public function name(string $name): self {
      $this->name = $name;
      return $this;
   }

   public function getMethod(): string {
      return $this->method;
   }

   public function getUri(): string {
      return $this->uri;
   }

   public function getHandler(): mixed {
      return $this->handler;
   }

   public function getMiddleware(): array {
      return $this->middleware;
   }

   public function getName(): ?string {
      return $this->name;
   }

   /**
    * Check if route matches given method and URI
    */
   public function matches(string $method, string $uri): bool {
      return $this->method === $method && $this->uri === $uri;
   }

   /**
    * Get route data as array
    */
   public function toArray(): array {
      return [
         'method' => $this->method,
         'uri' => $this->uri,
         'handler' => $this->handler,
         'middleware' => $this->middleware,
         'name' => $this->name,
      ];
   }
}
