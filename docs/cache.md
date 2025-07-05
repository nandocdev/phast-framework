# Cache System Documentation

El framework Phast incluye un sistema de cache flexible que soporta múltiples drivers para optimizar el rendimiento de la aplicación.

## Arquitectura

### Componentes Principales

-  **CacheInterface**: Interfaz estándar para implementaciones de cache
-  **FileCache**: Implementación de cache basada en archivos
-  **MemoryCache**: Implementación de cache en memoria (para el request actual)
-  **CacheServiceProvider**: Proveedor de servicios para configurar el cache

### Estructura de Archivos

```
core/Cache/
├── CacheInterface.php
├── FileCache.php
└── MemoryCache.php
```

## Configuración

### Variables de Entorno

```env
# Tipo de driver de cache (file, memory)
CACHE_DRIVER=file

# Ruta para cache de archivos (opcional, por defecto: storage/cache)
CACHE_PATH=/path/to/cache

# TTL por defecto en segundos (opcional, por defecto: 3600)
CACHE_TTL=3600
```

### Registro en Service Provider

```php
<?php

// El CacheServiceProvider se registra automáticamente
// Configura el driver según CACHE_DRIVER
$container->singleton(CacheInterface::class, function () {
    $cacheType = env('CACHE_DRIVER', 'file');

    return match ($cacheType) {
        'memory' => new MemoryCache(),
        'file' => new FileCache(PHAST_BASE_PATH . '/storage/cache'),
        default => new FileCache(PHAST_BASE_PATH . '/storage/cache')
    };
});
```

## Uso Básico

### 1. Usando el Helper Global

```php
<?php

// Obtener instancia del cache
$cache = cache();

// Obtener un valor
$value = cache('key');

// Almacenar un valor (TTL por defecto)
cache('key', 'value');

// Almacenar con TTL específico (segundos)
cache('key', 'value', 3600);

// Almacenar con TTL usando DateInterval
cache('key', 'value', new DateInterval('PT1H'));
```

### 2. Usando Inyección de Dependencias

```php
<?php

use Phast\Core\Cache\CacheInterface;

class ProductService
{
    private CacheInterface $cache;

    public function __construct(CacheInterface $cache)
    {
        $this->cache = $cache;
    }

    public function getProduct(int $id): ?Product
    {
        $key = "product.{$id}";

        // Intentar obtener del cache
        $product = $this->cache->get($key);

        if ($product === null) {
            // Cargar desde base de datos
            $product = $this->loadProductFromDatabase($id);

            // Almacenar en cache por 1 hora
            $this->cache->set($key, $product, 3600);
        }

        return $product;
    }
}
```

### 3. Cache Remember Pattern

```php
<?php

// Usando el helper cache_remember
$products = cache_remember('products.featured', function() {
    return $this->database->query('SELECT * FROM products WHERE featured = 1');
}, 1800); // 30 minutos

// Usando la interfaz directamente
$products = $cache->remember('products.featured', function() {
    return $this->database->query('SELECT * FROM products WHERE featured = 1');
}, 1800);
```

## Implementaciones

### FileCache

Cache basado en archivos del sistema, ideal para aplicaciones con múltiples procesos.

```php
<?php

use Phast\Core\Cache\FileCache;

$cache = new FileCache('/path/to/cache/directory');

// Características:
// - Persistente entre requests
// - Compartido entre procesos
// - Limpieza automática de archivos expirados
// - Serialización automática de datos complejos
```

#### Ventajas:

-  ✅ Persistente
-  ✅ Compartido entre procesos
-  ✅ No requiere servicios externos
-  ✅ Limpieza automática

#### Desventajas:

-  ❌ Más lento que memoria
-  ❌ Requiere espacio en disco
-  ❌ I/O intensivo

### MemoryCache

Cache en memoria para el request actual, muy rápido pero no persistente.

```php
<?php

use Phast\Core\Cache\MemoryCache;

$cache = new MemoryCache();

// Características:
// - Muy rápido
// - Solo durante el request actual
// - Ideal para datos que se acceden múltiples veces en un request
```

#### Ventajas:

-  ✅ Extremadamente rápido
-  ✅ Sin I/O
-  ✅ Sin configuración adicional

#### Desventajas:

-  ❌ No persistente
-  ❌ Limitado por memoria del proceso
-  ❌ Se pierde al finalizar el request

## Métodos de la Interfaz

### get(string $key, mixed $default = null)

```php
<?php

// Obtener valor con default
$value = $cache->get('user.123', []);

// Verificar si existe
if ($cache->get('session.token') !== null) {
    // Token existe
}
```

### set(string $key, mixed $value, int|\DateInterval $ttl = null)

```php
<?php

// TTL en segundos
$cache->set('user.123', $userData, 3600);

// TTL con DateInterval
$cache->set('user.123', $userData, new DateInterval('PT1H'));

// TTL por defecto (definido en la implementación)
$cache->set('user.123', $userData);
```

### delete(string $key): bool

```php
<?php

// Eliminar entrada específica
$deleted = $cache->delete('user.123');

if ($deleted) {
    echo "Entrada eliminada correctamente";
}
```

### clear(): bool

```php
<?php

// Limpiar todo el cache
$cleared = $cache->clear();

if ($cleared) {
    echo "Cache completamente limpiado";
}
```

### getMultiple(iterable $keys, mixed $default = null): iterable

```php
<?php

// Obtener múltiples valores
$keys = ['user.123', 'user.456', 'user.789'];
$users = $cache->getMultiple($keys, []);

foreach ($users as $key => $user) {
    echo "Usuario {$key}: " . json_encode($user) . "\n";
}
```

### setMultiple(iterable $values, int|\DateInterval $ttl = null): bool

```php
<?php

// Almacenar múltiples valores
$values = [
    'user.123' => $userData1,
    'user.456' => $userData2,
    'user.789' => $userData3
];

$success = $cache->setMultiple($values, 3600);
```

### deleteMultiple(iterable $keys): bool

```php
<?php

// Eliminar múltiples entradas
$keys = ['user.123', 'user.456', 'user.789'];
$deleted = $cache->deleteMultiple($keys);
```

### has(string $key): bool

```php
<?php

// Verificar si existe una clave
if ($cache->has('user.123')) {
    echo "Usuario encontrado en cache";
}
```

### remember(string $key, callable $callback, int|\DateInterval $ttl = null)

```php
<?php

// Patrón Cache Remember
$expensiveData = $cache->remember('expensive.calculation', function() {
    // Cálculo costoso
    return $this->performExpensiveCalculation();
}, 3600);
```

## Patrones de Uso

### 1. Cache-Aside (Manual)

```php
<?php

class UserRepository
{
    public function findById(int $id): ?User
    {
        $key = "user.{$id}";

        // 1. Verificar cache
        $user = cache($key);

        if ($user === null) {
            // 2. Cargar desde fuente de datos
            $user = $this->database->find($id);

            if ($user) {
                // 3. Almacenar en cache
                cache($key, $user, 3600);
            }
        }

        return $user;
    }

    public function update(User $user): void
    {
        // 1. Actualizar base de datos
        $this->database->update($user);

        // 2. Invalidar cache
        cache()->delete("user.{$user->getId()}");
    }
}
```

### 2. Cache-Through (Automático)

```php
<?php

class CachedUserRepository
{
    public function findById(int $id): ?User
    {
        return cache_remember("user.{$id}", function() use ($id) {
            return $this->database->find($id);
        }, 3600);
    }
}
```

### 3. Write-Through

```php
<?php

class UserService
{
    public function updateUser(int $id, array $data): User
    {
        // 1. Actualizar base de datos
        $user = $this->repository->update($id, $data);

        // 2. Actualizar cache inmediatamente
        cache("user.{$id}", $user, 3600);

        return $user;
    }
}
```

### 4. Cache Tagging (Simulado)

```php
<?php

class ProductService
{
    public function getProductsByCategory(int $categoryId): array
    {
        return cache_remember("products.category.{$categoryId}", function() use ($categoryId) {
            return $this->repository->findByCategory($categoryId);
        }, 1800);
    }

    public function invalidateCategoryCache(int $categoryId): void
    {
        // Invalidar cache de categoría específica
        cache()->delete("products.category.{$categoryId}");

        // También invalidar cache relacionado
        cache()->delete("categories.{$categoryId}");
        cache()->delete('categories.all');
    }
}
```

## Estrategias de TTL

### TTL Fijo

```php
<?php

// Siempre 1 hora
cache('data', $value, 3600);
```

### TTL Basado en Contenido

```php
<?php

class NewsService
{
    public function getArticle(int $id): Article
    {
        return cache_remember("article.{$id}", function() use ($id) {
            return $this->repository->find($id);
        }, $this->calculateTTL($id));
    }

    private function calculateTTL(int $articleId): int
    {
        $article = $this->repository->find($articleId);

        // Artículos recientes: cache corto
        if ($article->isRecent()) {
            return 300; // 5 minutos
        }

        // Artículos antiguos: cache largo
        return 86400; // 24 horas
    }
}
```

### TTL Aleatorio (Cache Stampede Prevention)

```php
<?php

function randomTTL(int $baseTTL, float $jitter = 0.1): int
{
    $variance = $baseTTL * $jitter;
    return $baseTTL + random_int(-$variance, $variance);
}

// Uso
cache('key', $value, randomTTL(3600)); // 3600 ± 360 segundos
```

## Namespacing

### Por Módulo

```php
<?php

class UserCacheService
{
    private const PREFIX = 'users:';

    public function cacheUser(User $user): void
    {
        cache(self::PREFIX . "user.{$user->getId()}", $user, 3600);
    }

    public function getCachedUser(int $id): ?User
    {
        return cache(self::PREFIX . "user.{$id}");
    }
}
```

### Por Versión

```php
<?php

class ApiCacheService
{
    private function getVersionedKey(string $key): string
    {
        $version = config('api.version', 'v1');
        return "api:{$version}:{$key}";
    }

    public function cacheResponse(string $endpoint, array $data): void
    {
        $key = $this->getVersionedKey("response.{$endpoint}");
        cache($key, $data, 1800);
    }
}
```

## Monitoring y Debugging

### Cache Stats (FileCache)

```php
<?php

// En FileCache, puedes revisar archivos manualmente
$cacheDir = PHAST_BASE_PATH . '/storage/cache';
$files = glob($cacheDir . '/*.cache');

echo "Archivos en cache: " . count($files) . "\n";

foreach ($files as $file) {
    $key = basename($file, '.cache');
    $size = filesize($file);
    $modified = date('Y-m-d H:i:s', filemtime($file));

    echo "Key: {$key}, Size: {$size} bytes, Modified: {$modified}\n";
}
```

### Logging de Cache Operations

```php
<?php

class LoggingCacheDecorator implements CacheInterface
{
    private CacheInterface $cache;
    private LoggerInterface $logger;

    public function __construct(CacheInterface $cache, LoggerInterface $logger)
    {
        $this->cache = $cache;
        $this->logger = $logger;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $value = $this->cache->get($key, $default);

        $this->logger->debug('Cache get', [
            'key' => $key,
            'hit' => $value !== $default
        ]);

        return $value;
    }

    public function set(string $key, mixed $value, int|\DateInterval $ttl = null): bool
    {
        $result = $this->cache->set($key, $value, $ttl);

        $this->logger->debug('Cache set', [
            'key' => $key,
            'ttl' => $ttl,
            'success' => $result
        ]);

        return $result;
    }

    // ... otros métodos
}
```

## Best Practices

### 1. Naming Conventions

```php
<?php

// ✅ Buenas prácticas
cache('users:profile:123', $profile);
cache('products:category:electronics:page:1', $products);
cache('api:v2:weather:london', $weather);

// ❌ Evitar
cache('user123', $profile);
cache('products', $products);
cache('weather', $weather);
```

### 2. TTL Apropiados

```php
<?php

// Datos que cambian frecuentemente: TTL corto
cache('live_prices', $prices, 30); // 30 segundos

// Datos estáticos: TTL largo
cache('country_list', $countries, 86400); // 24 horas

// Datos de sesión: TTL medio
cache('user_preferences:123', $prefs, 3600); // 1 hora
```

### 3. Invalidación Proactiva

```php
<?php

class ProductService
{
    public function updateProduct(Product $product): void
    {
        // 1. Actualizar base de datos
        $this->repository->update($product);

        // 2. Invalidar caches relacionados
        $this->invalidateProductCache($product->getId());
        $this->invalidateCategoryCache($product->getCategoryId());
        $this->invalidateSearchCache();
    }

    private function invalidateProductCache(int $productId): void
    {
        cache()->delete("product:{$productId}");
        cache()->delete("product:details:{$productId}");
        cache()->delete("product:reviews:{$productId}");
    }
}
```

### 4. Serialización Segura

```php
<?php

// ✅ Para FileCache, los objetos se serializan automáticamente
cache('user:123', $userObject);

// ✅ Para APIs, considera JSON
cache('api:response:weather', json_encode($weatherData));

// ⚠️ Cuidado con objetos que contienen recursos
// (conexiones DB, file handles, etc.)
```

### 5. Error Handling

```php
<?php

class SafeCacheService
{
    public function getCached(string $key, callable $fallback, int $ttl = 3600)
    {
        try {
            $value = cache($key);

            if ($value === null) {
                $value = $fallback();
                cache($key, $value, $ttl);
            }

            return $value;
        } catch (\Exception $e) {
            // Log error pero no fallar
            logger()->warning('Cache error', [
                'key' => $key,
                'error' => $e->getMessage()
            ]);

            // Ejecutar fallback sin cache
            return $fallback();
        }
    }
}
```

## Performance Tips

1. **Use MemoryCache** para datos accedidos múltiples veces en un request
2. **FileCache** para datos compartidos entre requests/procesos
3. **Cache Remember** para simplificar el código
4. **TTL Apropiados** para evitar datos obsoletos o limpieza excesiva
5. **Namespacing** para evitar colisiones de claves
6. **Invalidación Proactiva** para mantener consistencia
7. **Monitoring** para optimizar hit rates
