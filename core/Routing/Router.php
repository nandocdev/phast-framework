<?php
/**
 * @package     phast/core
 * @subpackage  Routing
 * @file        Router
 * @author      Fernando Castillo <fdocst@gmail.com>
 * @date        2025-07-04
 * @version     1.0.0
 * @description Simple router implementation
 */

declare(strict_types=1);

namespace Phast\Core\Routing;

use Phast\Core\Http\Request;
use Phast\Core\Http\Response;
use FastRoute\Dispatcher;
use FastRoute\RouteCollector;

class Router {
   private array $routes = [];
   private array $middlewares = [];
   private ?Dispatcher $dispatcher = null;

   public function get(string $uri, mixed $handler): Route {
      return $this->addRoute('GET', $uri, $handler);
   }

   public function post(string $uri, mixed $handler): Route {
      return $this->addRoute('POST', $uri, $handler);
   }

   public function put(string $uri, mixed $handler): Route {
      return $this->addRoute('PUT', $uri, $handler);
   }

   public function delete(string $uri, mixed $handler): Route {
      return $this->addRoute('DELETE', $uri, $handler);
   }

   public function patch(string $uri, mixed $handler): Route {
      return $this->addRoute('PATCH', $uri, $handler);
   }

   public function options(string $uri, mixed $handler): Route {
      return $this->addRoute('OPTIONS', $uri, $handler);
   }

   public function any(string $uri, mixed $handler): Route {
      $methods = ['GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'OPTIONS'];
      $route = null;

      foreach ($methods as $method) {
         $route = $this->addRoute($method, $uri, $handler);
      }

      return $route;
   }

   public function group(array $attributes, callable $callback): void {
      $prefix = $attributes['prefix'] ?? '';
      $middleware = $attributes['middleware'] ?? [];

      $originalMiddlewares = $this->middlewares;
      $this->middlewares = array_merge($this->middlewares, (array) $middleware);

      $router = new self();
      $callback($router);

      foreach ($router->routes as $route) {
         $route['uri'] = $prefix . $route['uri'];
         $route['middleware'] = array_merge($route['middleware'], $this->middlewares);
         $this->routes[] = $route;
      }

      $this->middlewares = $originalMiddlewares;
   }

   public function middleware(string|array $middleware): self {
      $this->middlewares = array_merge($this->middlewares, (array) $middleware);
      return $this;
   }

   public function dispatch(Request $request): Response {
      $this->buildDispatcher();

      $routeInfo = $this->dispatcher->dispatch(
         $request->getMethod(),
         $request->getPath()
      );

      switch ($routeInfo[0]) {
         case Dispatcher::NOT_FOUND:
            return new Response('404 Not Found', 404);

         case Dispatcher::METHOD_NOT_ALLOWED:
            return new Response('405 Method Not Allowed', 405);

         case Dispatcher::FOUND:
            $handler = $routeInfo[1]['handler'];
            $vars = $routeInfo[2];
            $middleware = $routeInfo[1]['middleware'] ?? [];

            return $this->handleRequest($request, $handler, $vars, $middleware);

         default:
            return new Response('500 Internal Server Error', 500);
      }
   }

   private function addRoute(string $method, string $uri, mixed $handler): Route {
      $route = new Route($method, $uri, $handler);
      $route->middleware($this->middlewares);

      $this->routes[] = [
         'method' => $method,
         'uri' => $uri,
         'handler' => $handler,
         'middleware' => $this->middlewares
      ];

      $this->dispatcher = null; // Reset dispatcher

      return $route;
   }

   private function buildDispatcher(): void {
      if ($this->dispatcher !== null) {
         return;
      }

      $this->dispatcher = \FastRoute\simpleDispatcher(function (RouteCollector $r) {
         foreach ($this->routes as $route) {
            $r->addRoute($route['method'], $route['uri'], $route);
         }
      });
   }

   private function handleRequest(Request $request, mixed $handler, array $vars, array $middleware): Response {
      // Add route parameters to request
      $request->setRouteParams($vars);

      // Execute middleware chain
      $pipeline = array_reduce(
         array_reverse($middleware),
         $this->carry(),
         $this->prepareDestination($handler)
      );

      return $pipeline($request);
   }

   private function carry(): callable {
      return function ($stack, $pipe) {
         return function ($passable) use ($stack, $pipe) {
            if (is_string($pipe)) {
               $pipe = app($pipe);
            }

            return $pipe->handle($passable, $stack);
         };
      };
   }

   private function prepareDestination(mixed $handler): callable {
      return function (Request $request) use ($handler) {
         if (is_string($handler) && str_contains($handler, '@')) {
            [$controller, $method] = explode('@', $handler);
            $controller = app($controller);
            return $controller->$method($request);
         }

         if (is_callable($handler)) {
            return $handler($request);
         }

         if (is_string($handler)) {
            $controller = app($handler);
            return $controller($request);
         }

         throw new \InvalidArgumentException('Invalid route handler');
      };
   }
}
