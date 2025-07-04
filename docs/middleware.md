# Middleware System Documentation

## Descripción

El sistema de middlewares de Phast Framework permite interceptar y procesar las peticiones HTTP antes de que lleguen al controlador, siguiendo el patrón Pipeline.

## Interfaz MiddlewareInterface

Todos los middlewares deben implementar la interfaz `MiddlewareInterface`:

```php
interface MiddlewareInterface
{
    public function handle(Request $request, callable $next): Response;
}
```

## Middlewares Incluidos

### 1. CorsMiddleware

Maneja las cabeceras CORS para peticiones cross-origin.

**Configuración:**

```php
$corsMiddleware = new CorsMiddleware([
    'allowed_origins' => ['*'],
    'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE'],
    'allowed_headers' => ['Content-Type', 'Authorization'],
    'max_age' => 86400,
    'allow_credentials' => false,
]);
```

### 2. AuthMiddleware

Valida la autenticación mediante tokens Bearer.

**Uso:**

```php
$router->group([
    'middleware' => [AuthMiddleware::class]
], function ($router) {
    // Rutas protegidas
});
```

### 3. RateLimitMiddleware

Limita el número de peticiones por IP.

**Configuración:**

```php
$rateLimitMiddleware = new RateLimitMiddleware(60, 60); // 60 requests per 60 seconds
```

### 4. LoggingMiddleware

Registra todas las peticiones HTTP y sus respuestas.

## Uso de Middlewares

### Middleware Global

Aplica a todas las rutas:

```php
$router->globalMiddleware([
    CorsMiddleware::class,
    LoggingMiddleware::class,
]);
```

### Middleware en Grupos

Aplica a un grupo de rutas:

```php
$router->group([
    'prefix' => '/api',
    'middleware' => [RateLimitMiddleware::class]
], function ($router) {
    // Rutas del API
});
```

### Middleware en Rutas Específicas

Aplica a una ruta individual:

```php
$router->get('/admin', 'AdminController@index')
       ->middleware([AuthMiddleware::class]);
```

### Middleware con Callable

También puedes usar closures como middleware:

```php
$router->get('/test', 'TestController@index')
       ->middleware([
           function (Request $request, callable $next) {
               // Lógica del middleware
               return $next($request);
           }
       ]);
```

## Crear Middleware Personalizado

```php
<?php

namespace App\Middleware;

use Phast\Core\Http\Middleware\MiddlewareInterface;
use Phast\Core\Http\Request;
use Phast\Core\Http\Response;

class CustomMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, callable $next): Response
    {
        // Lógica antes del controlador

        $response = $next($request);

        // Lógica después del controlador

        return $response;
    }
}
```

## Registro en el Container

```php
// En Bootstrap.php o ServiceProvider
$this->container->singleton(CustomMiddleware::class);
```

## Orden de Ejecución

Los middlewares se ejecutan en el siguiente orden:

1. **Middlewares Globales** (en orden de registro)
2. **Middlewares de Grupo** (del grupo más externo al más interno)
3. **Middlewares de Ruta** (en orden de registro)
4. **Controlador**
5. **Middlewares de Ruta** (en orden inverso, post-procesamiento)
6. **Middlewares de Grupo** (en orden inverso)
7. **Middlewares Globales** (en orden inverso)

## Ejemplo Completo

```php
// routes.php
$router->globalMiddleware([
    CorsMiddleware::class,      // 1º ejecutado
    LoggingMiddleware::class,   // 2º ejecutado
]);

$router->group([
    'prefix' => '/api',
    'middleware' => [RateLimitMiddleware::class] // 3º ejecutado
], function ($router) {

    $router->group([
        'middleware' => [AuthMiddleware::class] // 4º ejecutado
    ], function ($router) {

        $router->get('/users', 'UserController@index')
               ->middleware([CustomMiddleware::class]); // 5º ejecutado

    });
});
```

## Manejo de Errores

Los middlewares pueden capturar y manejar errores:

```php
public function handle(Request $request, callable $next): Response
{
    try {
        return $next($request);
    } catch (\Exception $e) {
        // Manejar error
        return new Response('Error occurred', 500);
    }
}
```

## Mejores Prácticas

1. **Mantén los middlewares pequeños y enfocados** en una sola responsabilidad
2. **Usa el container DI** para inyectar dependencias
3. **Maneja errores apropiadamente** sin interrumpir la cadena
4. **Documenta el comportamiento** de middlewares personalizados
5. **Considera el orden de ejecución** al registrar middlewares
6. **Usa tipos específicos** en lugar de mixed cuando sea posible
