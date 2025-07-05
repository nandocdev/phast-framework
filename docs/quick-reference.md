# Quick Reference - Phast Framework

Esta es una guía de referencia rápida para las funcionalidades principales del framework Phast.

## 🚀 Quick Start

```bash
# Crear un módulo completo
php phast make:module Blog

# Crear componentes individuales
php phast make:controller PostController --module=Blog
php phast make:entity Post --module=Blog
php phast make:service PostService --module=Blog
php phast make:repository PostRepository --module=Blog
php phast make:dto CreatePostDTO --module=Blog

# Ejecutar servidor de desarrollo
composer serve
```

## 📦 Helper Functions

### Sistema de Eventos

```php
// Disparar un evento
$event = new UserCreated(['user' => $userData]);
event($event);

// Crear eventos simples
$event = new SimpleEvent('notification.sent', ['user_id' => 123]);
event($event);
```

### Sistema de Cache

```php
// Obtener instancia del cache
$cache = cache();

// Operaciones básicas
cache('key', 'value', 3600);  // Almacenar (TTL en segundos)
$value = cache('key');         // Obtener
cache()->delete('key');        // Eliminar
cache()->clear();             // Limpiar todo

// Cache remember pattern
$data = cache_remember('expensive_key', function() {
    return performExpensiveOperation();
}, 1800);
```

### Rate Limiting

```php
// Verificar rate limit
if (rate_limit('user:123')) {
    // Request permitido
    processRequest();
} else {
    // Rate limit excedido
    return errorResponse(429, 'Too many requests');
}

// Obtener información del rate limit
$info = rate_limit_info('user:123');
// $info contiene: max_attempts, remaining_attempts, reset_time, etc.
```

### Helpers de Entorno y Path

```php
// Variables de entorno
$dbHost = env('DB_HOST', 'localhost');
$debug = env('APP_DEBUG', false);

// Paths de la aplicación
$appPath = app_path('Modules/Users');
$configPath = config_path('database.php');
$storagePath = storage_path('logs/app.log');
$publicPath = public_path('assets/css');
```

## 🏗️ Arquitectura de Módulos

### Estructura de Directorio

```
app/Modules/Users/
├── Controllers/
│   └── UserController.php
├── Services/
│   └── UserService.php
├── Models/
│   ├── Entities/
│   │   └── UserEntity.php
│   └── Repositories/
│       ├── UserRepository.php
│       └── UserRepositoryInterface.php
├── DataTransfer/
│   ├── CreateUserDTO.php
│   └── UpdateUserDTO.php
├── Events/
│   ├── UserCreated.php
│   ├── UserUpdated.php
│   └── UserDeleted.php
├── Listeners/
│   ├── SendWelcomeEmailListener.php
│   └── LogUserActivityListener.php
├── Providers/
│   └── UserServiceProvider.php
└── routes.php
```

## 🎯 Patrones de Uso Común

### Controller con Service y DTOs

```php
class UserController
{
    public function __construct(private UserService $userService) {}

    public function store(Request $request): Response
    {
        $dto = new CreateUserDTO($request->getInput());
        $user = $this->userService->createUser($dto);

        return new Response(json_encode(['user' => $user]), 201);
    }

    public function update(Request $request): Response
    {
        $id = $request->getRouteParam('id');
        $dto = new UpdateUserDTO($request->getInput());
        $user = $this->userService->updateUser($id, $dto);

        return new Response(json_encode(['user' => $user]));
    }
}
```

### Service con Repository y Eventos

```php
class UserService
{
    public function __construct(
        private UserRepositoryInterface $repository,
        private ValidatorInterface $validator
    ) {}

    public function createUser(CreateUserDTO $dto): UserEntity
    {
        // Validar datos
        $result = $this->validator->validate($dto->toArray(), [
            'email' => 'required|email',
            'name' => 'required|min:2'
        ]);

        if (!$result->isValid()) {
            throw new ValidationException($result->getErrors());
        }

        // Crear usuario
        $user = $this->repository->create($dto->toArray());

        // Disparar evento
        event(new UserCreated($user));

        return $user;
    }
}
```

### Repository con Excepciones

```php
class UserRepository implements UserRepositoryInterface
{
    public function findById(int $id): UserEntity
    {
        $user = $this->entityManager
            ->getRepository(UserEntity::class)
            ->find($id);

        if (!$user) {
            throw new EntityNotFoundException("User with ID {$id} not found");
        }

        return $user;
    }

    public function create(array $data): UserEntity
    {
        try {
            $user = new UserEntity();
            // ... set properties

            $this->entityManager->persist($user);
            $this->entityManager->flush();

            return $user;
        } catch (\Exception $e) {
            throw new DatabaseException("Failed to create user: " . $e->getMessage());
        }
    }
}
```

## 🛡️ Excepciones Tipadas

```php
// Jerarquía de excepciones
try {
    $user = $userService->createUser($dto);
} catch (ValidationException $e) {
    return new Response(json_encode(['errors' => $e->getErrors()]), 400);
} catch (DuplicateEntityException $e) {
    return new Response(json_encode(['error' => 'User already exists']), 409);
} catch (DatabaseException $e) {
    logger()->error('Database error', ['error' => $e->getMessage()]);
    return new Response(json_encode(['error' => 'Internal server error']), 500);
} catch (DomainException $e) {
    return new Response(json_encode(['error' => $e->getMessage()]), 400);
} catch (InfrastructureException $e) {
    logger()->error('Infrastructure error', ['error' => $e->getMessage()]);
    return new Response(json_encode(['error' => 'Service unavailable']), 503);
}
```

## 🔧 Configuración de Entorno

```env
# Aplicación
APP_NAME=MyApp
APP_ENV=production
APP_DEBUG=false

# Base de datos
DB_HOST=localhost
DB_PORT=3306
DB_NAME=myapp
DB_USER=root
DB_PASS=password

# Cache
CACHE_DRIVER=file
CACHE_TTL=3600

# Rate Limiting
RATE_LIMIT_MAX_ATTEMPTS=60
RATE_LIMIT_DECAY_MINUTES=1

# Logging
LOG_LEVEL=info
LOG_PATH=storage/logs/app.log
```

## 🚦 Middleware y Rutas

```php
// Aplicar middleware globalmente
$router->globalMiddleware([
    \Phast\Core\Http\Middleware\CorsMiddleware::class,
    \Phast\Core\Http\Middleware\RateLimitMiddleware::class,
]);

// Rutas con middleware específico
$router->group(['middleware' => ['auth', 'rate_limit']], function($router) {
    $router->get('/api/users', 'UserController@index');
    $router->post('/api/users', 'UserController@store');
});

// Rutas con rate limiting personalizado
$router->get('/api/search', 'SearchController@search')
    ->middleware(['rate_limit:20,1']); // 20 requests por minuto
```

## 🎉 Event Listeners

```php
// Registrar listeners en ServiceProvider
class UserServiceProvider implements ServiceProviderInterface
{
    public function boot(ContainerInterface $container): void
    {
        $dispatcher = $container->get(EventDispatcherInterface::class);

        // Listener para enviar email de bienvenida
        $dispatcher->listen(UserCreated::class, new SendWelcomeEmailListener());

        // Listener para logging
        $dispatcher->listen(UserCreated::class, new LogUserActivityListener());
        $dispatcher->listen(UserUpdated::class, new LogUserActivityListener());
        $dispatcher->listen(UserDeleted::class, new LogUserActivityListener());
    }
}

// Implementar un listener
class SendWelcomeEmailListener implements ListenerInterface
{
    public function handle(EventInterface $event): void
    {
        if ($event instanceof UserCreated) {
            $user = $event->getUser();
            $this->mailService->sendWelcomeEmail($user);
        }
    }

    public function getPriority(): int
    {
        return 10; // Mayor prioridad = se ejecuta primero
    }
}
```

## 🗄️ Cache Strategies

```php
// Cache-Aside Pattern
$user = cache("user:{$id}");
if (!$user) {
    $user = $repository->find($id);
    cache("user:{$id}", $user, 3600);
}

// Cache-Through Pattern
$user = cache_remember("user:{$id}", function() use ($id, $repository) {
    return $repository->find($id);
}, 3600);

// Cache con namespacing
cache("users:profile:{$id}", $profile, 3600);
cache("products:category:{$categoryId}", $products, 1800);

// Invalidación proactiva
public function updateUser(int $id, array $data): void
{
    $this->repository->update($id, $data);
    cache()->delete("user:{$id}");
    cache()->delete("users:profile:{$id}");
}
```

## 🔒 Rate Limiting Strategies

```php
// Por IP
if (!rate_limit("ip:" . $request->getIp())) {
    return errorResponse(429, 'Rate limit exceeded');
}

// Por usuario autenticado
$userId = auth()->user()->getId();
if (!rate_limit("user:{$userId}")) {
    return errorResponse(429, 'User rate limit exceeded');
}

// Por endpoint específico
$endpoint = $request->getPath();
if (!rate_limit("endpoint:{$endpoint}:ip:" . $request->getIp())) {
    return errorResponse(429, 'Endpoint rate limit exceeded');
}

// Rate limiting con información detallada
$identifier = "api_key:" . $request->getApiKey();
if (!rate_limit($identifier)) {
    $info = rate_limit_info($identifier);

    return new Response(json_encode([
        'error' => 'Rate limit exceeded',
        'retry_after' => $info['reset_time'],
        'limit' => $info['max_attempts'],
        'remaining' => $info['remaining_attempts']
    ]), 429, [
        'X-RateLimit-Limit' => $info['max_attempts'],
        'X-RateLimit-Remaining' => $info['remaining_attempts'],
        'Retry-After' => $info['reset_time']
    ]);
}
```

## 📊 Logging y Debugging

```php
// Diferentes niveles de logging
logger()->emergency('System is unusable');
logger()->alert('Action must be taken immediately');
logger()->critical('Critical conditions');
logger()->error('Error conditions');
logger()->warning('Warning conditions');
logger()->notice('Normal but significant condition');
logger()->info('Informational messages');
logger()->debug('Debug-level messages');

// Logging con contexto
logger()->info('User created', [
    'user_id' => $user->getId(),
    'email' => $user->getEmail(),
    'ip' => $request->getIp()
]);

// Logging de performance
$start = microtime(true);
$result = $heavyOperation();
$duration = microtime(true) - $start;

logger()->info('Heavy operation completed', [
    'duration' => $duration,
    'memory_peak' => memory_get_peak_usage(true)
]);
```

## 🧪 Testing Patterns

```php
// Test de servicio con mocks
class UserServiceTest extends TestCase
{
    public function testCreateUser(): void
    {
        $repository = $this->createMock(UserRepositoryInterface::class);
        $validator = $this->createMock(ValidatorInterface::class);

        $validator->expects($this->once())
            ->method('validate')
            ->willReturn(new ValidationResult(true, []));

        $repository->expects($this->once())
            ->method('create')
            ->willReturn(new UserEntity());

        $service = new UserService($repository, $validator);
        $dto = new CreateUserDTO(['name' => 'Test', 'email' => 'test@test.com']);

        $user = $service->createUser($dto);

        $this->assertInstanceOf(UserEntity::class, $user);
    }
}

// Test de rate limiting
class RateLimitTest extends TestCase
{
    public function testRateLimit(): void
    {
        $cache = new MemoryCache();
        $rateLimiter = new TokenBucketRateLimiter($cache, 5, 1);

        // Primeros 5 requests deben pasar
        for ($i = 0; $i < 5; $i++) {
            $this->assertTrue($rateLimiter->attempt('test'));
        }

        // El 6to debe fallar
        $this->assertFalse($rateLimiter->attempt('test'));
    }
}
```

## 📚 Comandos CLI Útiles

```bash
# Generar módulo completo
php phast make:module Blog

# Generar componentes
php phast make:controller PostController --module=Blog
php phast make:service PostService --module=Blog
php phast make:entity Post --module=Blog
php phast make:repository PostRepository --module=Blog
php phast make:dto CreatePostDTO --module=Blog
php phast make:dto UpdatePostDTO --module=Blog

# Eliminar componentes
php phast delete:controller PostController --module=Blog
php phast delete:module Blog

# Base de datos
composer migrate
composer migrate:rollback
composer migrate:reset

# Servidor de desarrollo
composer serve

# Testing
composer test
```

Esta guía cubre las funcionalidades más utilizadas del framework Phast. Para documentación completa, consulta los archivos en `/docs/`.
