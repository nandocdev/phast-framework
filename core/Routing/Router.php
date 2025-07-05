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
use Phast\Core\Http\Middleware\MiddlewareInterface;
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
   }   /**
    * Register a group of routes with shared attributes
    * 
    * VERSIÓN CORREGIDA: Trabaja directamente con referencias y aplica
    * los atributos del grupo correctamente a todas las rutas registradas.
    * Maneja correctamente los separadores de rutas para grupos anidados.
    * 
    * @param array $attributes Array with 'prefix', 'middleware', 'name' keys
    * @param callable $callback Callback that receives the router instance
    */
   public function group(array $attributes, callable $callback): void {
      $prefix = $attributes['prefix'] ?? '';
      $middleware = $attributes['middleware'] ?? [];
      $name = $attributes['name'] ?? '';

      // Normalize prefix - ensure it starts with / and doesn't end with /
      if (!empty($prefix)) {
         $prefix = '/' . trim($prefix, '/');
      }

      // Guardar estado original de middlewares
      $originalMiddlewares = $this->middlewares;
      
      // Aplicar middlewares del grupo temporalmente
      $this->middlewares = array_merge($this->middlewares, (array) $middleware);

      // Crear router temporal para capturar rutas del grupo
      $groupRouter = new self();
      
      // Ejecutar callback con el router temporal
      $callback($groupRouter);

      // Procesar todas las rutas registradas en el grupo
      foreach ($groupRouter->routes as $routeData) {
         // Normalize route URI
         $routeUri = '/' . trim($routeData['uri'], '/');
         
         // Crear nueva ruta con atributos del grupo aplicados
         $processedRoute = [
            'method' => $routeData['method'],
            'uri' => $prefix . $routeUri, // Aplicar prefijo con separadores correctos
            'handler' => $routeData['handler'],
            'middleware' => array_merge($routeData['middleware'], $this->middlewares), // Combinar middlewares
            'name' => $this->buildRouteName($name, $routeData['name'] ?? '') // Aplicar prefijo de nombre
         ];

         // Agregar la ruta procesada al router principal
         $this->routes[] = $processedRoute;
      }

      // Restaurar middlewares originales
      $this->middlewares = $originalMiddlewares;
   }

   /**
    * Build route name with group prefix
    */
   private function buildRouteName(string $groupName, string $routeName): string {
      if (empty($groupName)) {
         return $routeName;
      }

      if (empty($routeName)) {
         return $groupName;
      }

      return $groupName . '.' . $routeName;
   }


   public function middleware(string|array $middleware): self {
      $this->middlewares = array_merge($this->middlewares, (array) $middleware);
      return $this;
   }

   /**
    * Add global middleware to all routes
    */
   public function globalMiddleware(string|array $middleware): self {
      $this->middlewares = array_merge($this->middlewares, (array) $middleware);
      return $this;
   }

   /**
    * Set global middlewares (replaces existing)
    */
   public function setGlobalMiddlewares(array $middlewares): self {
      $this->middlewares = $middlewares;
      return $this;
   }

   /**
    * Get all registered routes
    */
   public function getRoutes(): array {
      return $this->routes;
   }

   /**
    * Register a named route
    */
   public function name(string $name): Route {
      if (empty($this->routes)) {
         throw new \InvalidArgumentException('No route to name');
      }

      $lastRouteIndex = count($this->routes) - 1;
      $this->routes[$lastRouteIndex]['name'] = $name;

      return new Route(
         $this->routes[$lastRouteIndex]['method'],
         $this->routes[$lastRouteIndex]['uri'],
         $this->routes[$lastRouteIndex]['handler']
      );
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
      return function (callable $stack, mixed $pipe) {
         return function (Request $passable) use ($stack, $pipe): Response {
            try {
               $middleware = $this->resolveMiddleware($pipe);
               return $middleware->handle($passable, $stack);
            } catch (\Throwable $e) {
               logger()->error('Middleware error', [
                  'middleware' => is_object($pipe) ? get_class($pipe) : (string) $pipe,
                  'error' => $e->getMessage(),
                  'file' => $e->getFile(),
                  'line' => $e->getLine(),
               ]);

               throw $e;
            }
         };
      };
   }

   private function resolveMiddleware(mixed $pipe): MiddlewareInterface {
      if (is_string($pipe)) {
         // If it's a string, resolve from container
         return app($pipe);
      }

      if (is_callable($pipe)) {
         // If it's a callable, create anonymous middleware
         return new class ($pipe) implements MiddlewareInterface {
            private $callable;

            public function __construct(callable $callable) {
               $this->callable = $callable;
            }

            public function handle(Request $request, callable $next): Response {
               return ($this->callable)($request, $next);
            }
         };
      }

      if ($pipe instanceof MiddlewareInterface) {
         return $pipe;
      }

      throw new \InvalidArgumentException(
         'Middleware must implement MiddlewareInterface or be a callable/string'
      );
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
