# Phast Framework

Un framework PHP moderno y limpio basado en principios de arquitectura limpia y código SOLID.

## Características

-  **Arquitectura Limpia**: Separación clara de responsabilidades
-  **Inyección de Dependencias**: Container DI personalizado
-  **Routing**: Sistema de rutas flexible con FastRoute
-  **Middlewares**: Sistema de middlewares para interceptar requests
-  **Validación**: Sistema de validación robusto
-  **Vistas**: Motor de plantillas con League Plates
-  **ORM**: Implementación con Doctrine ORM
-  **Logs**: Sistema de logging con Monolog
-  **Migraciones**: Control de versiones de base de datos con Phinx### Patrones de Diseño

-  **Repository Pattern**: Abstracción del acceso a datos
-  **Service Layer**: Lógica de negocio
-  **Dependency Injection**: Inversión de control
-  **Value Objects**: Objetos inmutables con validación
-  **Factory Pattern**: Creación de objetos complejos
-  **Middleware Pattern**: Pipeline de procesamiento de requests

## Sistema de Middlewares

El framework incluye un sistema completo de middlewares que permite interceptar y procesar las peticiones HTTP antes de que lleguen al controlador.

### Middlewares Incluidos

-  **CorsMiddleware**: Manejo de CORS para peticiones cross-origin
-  **AuthMiddleware**: Autenticación JWT/Bearer token
-  **RateLimitMiddleware**: Limitación de peticiones por IP
-  **LoggingMiddleware**: Logging de requests y responses con métricas

### Uso de Middlewares

#### Middleware Global

Aplica a todas las rutas:

```php
$router->globalMiddleware([
    \Phast\Core\Http\Middleware\CorsMiddleware::class,
    \Phast\Core\Http\Middleware\LoggingMiddleware::class,
]);
```

#### Middleware en Grupos

Aplica a un grupo de rutas:

```php
$router->group([
    'prefix' => '/api',
    'middleware' => [\Phast\Core\Http\Middleware\RateLimitMiddleware::class]
], function ($router) {
    // Rutas del API con rate limiting
});
```

#### Middleware en Rutas Específicas

Aplica a una ruta individual:

```php
$router->get('/admin', 'AdminController@index')
       ->middleware([\Phast\Core\Http\Middleware\AuthMiddleware::class]);
```

#### Middleware con Nombres

Las rutas pueden tener nombres para referencia:

```php
$router->get('/users', 'UserController@index')
       ->name('users.index')
       ->middleware([AuthMiddleware::class]);
```

### Crear Middleware Personalizado

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

### Configuración de Middlewares

#### CORS Middleware

```php
$corsMiddleware = new CorsMiddleware([
    'allowed_origins' => ['*'],
    'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE'],
    'allowed_headers' => ['Content-Type', 'Authorization'],
    'max_age' => 86400,
    'allow_credentials' => false,
]);
```

#### Rate Limit Middleware

`````php
$rateLimitMiddleware = new RateLimitMiddleware(60, 60); // 60 requests per 60 seconds
```  **Routing**: Sistema de rutas flexible con FastRoute
-  **Validación**: Sistema de validación robusto
-  **ORM**: Implementación con Doctrine ORM
-  **Logs**: Sistema de logging con Monolog
-  **Migraciones**: Control de versiones de base de datos con Phinx

## Estructura del Proyecto

````text

├── app/ # Código de la aplicación
│ ├── Modules/ # Módulos de la aplicación
│ │ └── Users/ # Módulo de usuarios (ejemplo)
│ │ ├── Controllers/
│ │ ├── Models/
│ │ │ ├── Entities/
│ │ │ ├── Repositories/
│ │ │ └── ValueObjects/
│ │ ├── Services/
│ │ └── routes.php
│ └── routes.php # Rutas principales
├── config/ # Archivos de configuración
├── core/ # Core del framework
│ ├── Application/ # Bootstrap y Container
│ ├── Config/ # Sistema de configuración
│ ├── Contracts/ # Interfaces
│ ├── Http/ # Request, Response, Controller
│ │ └── Middleware/ # Sistema de middlewares
│ ├── Routing/ # Sistema de rutas
│ └── Validation/ # Sistema de validación
├── migrations/ # Migraciones de base de datos
├── public/ # Punto de entrada web
├── resources/ # Recursos (views, assets)
├── storage/ # Almacenamiento (logs, cache)
└── vendor/ # Dependencias de Composer

`````

## Instalación

1. **Clonar el repositorio**

```bash
git clone <repository-url> phast-project
cd phast-project
```

2. **Instalar dependencias**

```bash
composer install
```

3. **Configurar entorno**

```bash
cp .env.example .env
# Editar .env con tus configuraciones
```

4. **Ejecutar migraciones**

```bash
./vendor/bin/phinx migrate
```

5. **Iniciar servidor de desarrollo**

```bash
php -S localhost:8000 -t public
```

## Uso

### Crear un Módulo

Un módulo sigue esta estructura:

```
app/Modules/ModuleName/
├── Controllers/
│   └── ModuleController.php
├── Models/
│   ├── Entities/
│   │   └── ModuleEntity.php
│   ├── Repositories/
│   │   ├── ModuleRepositoryInterface.php
│   │   └── ModuleRepository.php
│   └── ValueObjects/
├── Services/
│   └── ModuleService.php
├── Providers/
│   └── ModuleServiceProvider.php
└── routes.php
```

### Definir Rutas

```php
// app/Modules/ModuleName/routes.php
$router->group(['prefix' => '/module'], function ($router) {
    $router->get('/', 'Controller@index');
    $router->post('/', 'Controller@store');
    $router->get('/{id}', 'Controller@show');
    $router->put('/{id}', 'Controller@update');
    $router->delete('/{id}', 'Controller@destroy');
});
```

### Crear Controller

```php
<?php

namespace Phast\App\Modules\ModuleName\Controllers;

use Phast\Core\Http\Controller;
use Phast\Core\Http\Request;
use Phast\Core\Http\Response;

class ModuleController extends Controller
{
    public function index(Request $request): Response
    {
        return $this->json(['message' => 'Hello World']);
    }
}
```

### Crear Entity

```php
<?php

namespace Phast\App\Modules\ModuleName\Models\Entities;

class ModuleEntity
{
    private ?int $id = null;
    private string $name;

    public function __construct(string $name, ?int $id = null)
    {
        $this->name = $name;
        $this->id = $id;
    }

    // Getters y setters...

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
        ];
    }
}
```

### Crear Repository

```php
<?php

namespace Phast\App\Modules\ModuleName\Models\Repositories;

interface ModuleRepositoryInterface
{
    public function findAll(): array;
    public function findById(int $id): ?ModuleEntity;
    public function save(ModuleEntity $entity): ModuleEntity;
    public function delete(int $id): bool;
}

class ModuleRepository implements ModuleRepositoryInterface
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    // Implementar métodos...
}
```

### Crear Service

```php
<?php

namespace Phast\App\Modules\ModuleName\Services;

class ModuleService
{
    private ModuleRepositoryInterface $repository;

    public function __construct(ModuleRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function getAllItems(): array
    {
        return $this->repository->findAll();
    }
}
```

## API Endpoints (Módulo Users)

-  `GET /api/users` - Obtener todos los usuarios (con rate limiting)
-  `GET /api/users/{id}` - Obtener usuario por ID (con rate limiting)
-  `POST /api/users` - Crear nuevo usuario (requiere autenticación)
-  `PUT /api/users/{id}` - Actualizar usuario (requiere autenticación)
-  `DELETE /api/users/{id}` - Eliminar usuario (requiere autenticación)

### Endpoints Adicionales

-  `GET /` - Página de inicio con información del framework
-  `GET /api/health` - Health check del API
-  `GET /api/profile` - Perfil del usuario autenticado (requiere autenticación)

### Ejemplo de uso:

```bash
# Crear usuario (requiere autenticación)
curl -X POST http://localhost:8000/api/users \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer your-token-here" \
  -d '{"name": "Juan Pérez", "email": "juan@example.com", "password": "123456"}'

# Obtener usuarios (con rate limiting)
curl http://localhost:8000/api/users

# Obtener usuario específico
curl http://localhost:8000/api/users/1

# Health check
curl http://localhost:8000/api/health
```

## Sistema de Vistas

El framework incluye un motor de plantillas basado en **League Plates** que proporciona una experiencia de desarrollo fluida y potente para la creación de vistas.

### Estructura de Directorios

```text
/resources
├── templates
│   ├── layouts     # Plantillas principales
│   └── partials    # Componentes reutilizables
└── views           # Vistas de la aplicación
```

### Uso Básico

#### Renderizar una Vista desde un Controller

```php
class HomeController extends Controller
{
    public function index(Request $request): Response
    {
        return $this->view('welcome', [
            'title' => 'Bienvenido',
            'user' => $request->user(),
            'features' => $this->getFeatures()
        ]);
    }

    public function users(Request $request): Response
    {
        return $this->view('users/index', [
            'users' => $this->userService->getAllUsers()
        ], 'admin'); // Usando layout personalizado
    }
}
```

#### Método view()

```php
protected function view(string $template, array $data = [], string $layout = 'default'): Response
```

-  **$template**: Nombre de la plantilla (sin extensión .phtml)
-  **$data**: Array de datos para pasar a la vista
-  **$layout**: Layout a utilizar (default: 'default')

### Características del Motor de Vistas

#### Layouts

Los layouts definen la estructura base de las páginas:

```php
// En la vista
<?php $this->layout('layouts::default', ['title' => 'Mi Página']) ?>

<div class="content">
    <!-- Contenido de la vista -->
</div>
```

#### Partials

Incluir componentes reutilizables:

```php
<!-- Incluir navegación -->
<?= $this->insert('partials::navigation') ?>

<!-- Incluir footer -->
<?= $this->insert('partials::footer') ?>
```

#### Secciones

Definir contenido dinámico en layouts:

```php
<!-- En la vista -->
<?php $this->start('styles') ?>
<link href="custom.css" rel="stylesheet">
<?php $this->stop() ?>

<!-- En el layout -->
<?= $this->section('styles') ?>
```

#### Escape de Datos

Protección automática contra XSS:

```php
<!-- Datos escapados automáticamente -->
<h1><?= $this->e($title) ?></h1>
<p><?= $this->e($user['name']) ?></p>

<!-- Contenido sin escapar (usar con precaución) -->
<div><?= $trustedHtml ?></div>
```

### Helpers Disponibles

#### Helper url()

```php
<a href="<?= $this->url('/users') ?>">Ver Usuarios</a>
<a href="<?= $this->url('/') ?>">Inicio</a>
```

#### Helper asset()

```php
<link href="<?= $this->asset('css/app.css') ?>" rel="stylesheet">
<script src="<?= $this->asset('js/app.js') ?>"></script>
<img src="<?= $this->asset('images/logo.png') ?>" alt="Logo">
```

#### Helper config()

```php
<title><?= $this->config('app.name', 'Mi App') ?></title>
<meta name="version" content="<?= $this->config('app.version') ?>">
```

#### Helper json()

```php
<script>
    const appData = <?= $this->json($data) ?>;
    const config = <?= $this->json(['api_url' => $this->url('/api')]) ?>;
</script>
```

### Configuración

La configuración de vistas se encuentra en `config/view.php`:

```php
return [
    'views_path' => PHAST_BASE_PATH . '/resources/views',
    'templates_path' => PHAST_BASE_PATH . '/resources/templates',
    'file_extension' => 'phtml',
    'default_layout' => 'default',
    'cache_enabled' => false,
    'global_data' => [
        'app_name' => env('APP_NAME', 'Phast Application'),
        'app_version' => '1.0.0',
    ],
];
```

### View Composers

Inyectar datos automáticamente en vistas específicas:

```php
// En un Service Provider
$viewEngine = app(ViewInterface::class);

$viewEngine->composer('layouts/*', function ($data) {
    $data['currentUser'] = auth()->user();
    $data['notifications'] = notifications()->unread();
    return $data;
});

$viewEngine->composer('users/*', function ($data) {
    $data['userStats'] = $this->userService->getStats();
    return $data;
});
```

### Plantillas de Ejemplo

#### Layout Principal (layouts/default.phtml)

```php
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?= $this->e($title ?? 'Phast Framework') ?></title>
    <link href="<?= $this->asset('css/app.css') ?>" rel="stylesheet">
    <?= $this->section('styles') ?>
</head>
<body>
    <?= $this->insert('partials::navigation') ?>

    <main>
        <?= $content ?>
    </main>

    <?= $this->insert('partials::footer') ?>

    <script src="<?= $this->asset('js/app.js') ?>"></script>
    <?= $this->section('scripts') ?>
</body>
</html>
```

#### Vista de Usuario (views/users/index.phtml)

```php
<?php $this->layout('layouts::default', ['title' => 'Usuarios']) ?>

<div class="container">
    <h1>Lista de Usuarios</h1>

    <?php if (!empty($users)): ?>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Email</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?= $this->e($user['id']) ?></td>
                            <td><?= $this->e($user['name']) ?></td>
                            <td><?= $this->e($user['email']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p>No hay usuarios registrados.</p>
    <?php endif; ?>
</div>

<?php $this->start('scripts') ?>
<script>
    console.log('Vista de usuarios cargada');
</script>
<?php $this->stop() ?>
```

## Principios Aplicados

### SOLID

-  **S** - Single Responsibility: Cada clase tiene una responsabilidad específica
-  **O** - Open/Closed: Abierto para extensión, cerrado para modificación
-  **L** - Liskov Substitution: Las implementaciones pueden sustituir interfaces
-  **I** - Interface Segregation: Interfaces específicas y cohesivas
-  **D** - Dependency Inversion: Depende de abstracciones, no de concreciones

### Clean Architecture

-  **Entities**: Objetos de negocio con reglas empresariales
-  **Use Cases**: Lógica de aplicación específica
-  **Interface Adapters**: Controllers, Presenters, Gateways
-  **Frameworks & Drivers**: Framework, Base de datos, Web

### Patrones de Diseño

-  **Repository Pattern**: Abstracción del acceso a datos
-  **Service Layer**: Lógica de negocio
-  **Dependency Injection**: Inversión de control
-  **Value Objects**: Objetos inmutables con validación
-  **Factory Pattern**: Creación de objetos complejos

## Comandos Útiles

```bash
# Servidor de desarrollo
composer serve
# O alternativamente:
php -S localhost:8000 -t public

# Migraciones
composer migrate
./vendor/bin/phinx create CreateTableName
./vendor/bin/phinx migrate
./vendor/bin/phinx rollback

# Tests
composer test
./vendor/bin/phpunit

# Análisis de código
./vendor/bin/phpstan analyse

# Formateo de código
./vendor/bin/php-cs-fixer fix
```

## Contribución

1. Fork el proyecto
2. Crea una rama para tu feature (`git checkout -b feature/AmazingFeature`)
3. Commit tus cambios (`git commit -m 'Add some AmazingFeature'`)
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abre un Pull Request

## Licencia

Este proyecto está licenciado bajo la Licencia MIT.
