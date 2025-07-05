# Rate Limiting Documentation

El framework Phast incluye un sistema de rate limiting robusto basado en el algoritmo Token Bucket para proteger APIs y recursos contra abuso y ataques de fuerza bruta.

## Arquitectura

### Componentes Principales

-  **RateLimiterInterface**: Interfaz estándar para implementaciones de rate limiting
-  **TokenBucketRateLimiter**: Implementación usando algoritmo Token Bucket
-  **RateLimitException**: Excepción específica para errores de rate limiting
-  **RateLimitMiddleware**: Middleware para aplicar rate limiting automáticamente
-  **RateLimitServiceProvider**: Proveedor de servicios para configurar rate limiting

### Estructura de Archivos

```
core/RateLimit/
├── RateLimiterInterface.php
├── TokenBucketRateLimiter.php
├── RateLimitException.php
└── RateLimitServiceProvider.php

core/Http/Middleware/
└── RateLimitMiddleware.php
```

## Configuración

### Variables de Entorno

```env
# Número máximo de intentos por ventana de tiempo
RATE_LIMIT_MAX_ATTEMPTS=60

# Ventana de tiempo en minutos para la regeneración de tokens
RATE_LIMIT_DECAY_MINUTES=1

# Driver de cache para almacenar contadores (file, memory)
CACHE_DRIVER=file
```

### Registro en Service Provider

```php
<?php

// El RateLimitServiceProvider se registra automáticamente
$container->singleton(RateLimiterInterface::class, function () use ($container) {
    $cache = $container->get(CacheInterface::class);

    $maxAttempts = (int) env('RATE_LIMIT_MAX_ATTEMPTS', 60);
    $decayMinutes = (int) env('RATE_LIMIT_DECAY_MINUTES', 1);

    return new TokenBucketRateLimiter($cache, $maxAttempts, $decayMinutes);
});
```

## Algoritmo Token Bucket

### Conceptos Básicos

El algoritmo Token Bucket funciona como un balde que:

1. **Contiene tokens** (cada token = 1 petición permitida)
2. **Capacidad máxima** definida por `maxAttempts`
3. **Se rellena** automáticamente a una tasa constante
4. **Consume tokens** con cada petición

### Ventajas

-  ✅ **Permite ráfagas**: Si hay tokens disponibles, permite múltiples requests rápidos
-  ✅ **Suave degradación**: No bloquea completamente, solo limita la tasa
-  ✅ **Recuperación automática**: Los tokens se regeneran automáticamente
-  ✅ **Flexible**: Diferentes límites por identificador

## Uso Básico

### 1. Usando Helpers Globales

```php
<?php

// Verificar si se permite un request
$identifier = 'user:123';

if (rate_limit($identifier)) {
    // Request permitido
    echo "Request procesado\n";
} else {
    // Rate limit excedido
    echo "Demasiados requests. Intenta más tarde.\n";
}

// Obtener información del rate limit
$info = rate_limit_info($identifier);
echo "Intentos restantes: " . $info['remaining_attempts'] . "\n";
echo "Tiempo para reset: " . $info['reset_time'] . " segundos\n";
```

### 2. Inyección de Dependencias

```php
<?php

use Phast\Core\RateLimit\RateLimiterInterface;
use Phast\Core\RateLimit\RateLimitException;

class ApiController
{
    private RateLimiterInterface $rateLimiter;

    public function __construct(RateLimiterInterface $rateLimiter)
    {
        $this->rateLimiter = $rateLimiter;
    }

    public function handleRequest(Request $request): Response
    {
        $identifier = $this->getIdentifier($request);

        try {
            if (!$this->rateLimiter->attempt($identifier)) {
                $info = $this->rateLimiter->getInfo($identifier);

                return new Response(json_encode([
                    'error' => 'Rate limit exceeded',
                    'retry_after' => $info['reset_time']
                ]), 429);
            }

            // Procesar request normal
            return $this->processRequest($request);

        } catch (RateLimitException $e) {
            return new Response(json_encode([
                'error' => 'Rate limiting error: ' . $e->getMessage()
            ]), 500);
        }
    }

    private function getIdentifier(Request $request): string
    {
        // Usar IP + User ID si está autenticado
        $ip = $request->getIp();
        $userId = $request->getUserId();

        return $userId ? "user:{$userId}" : "ip:{$ip}";
    }
}
```

### 3. Middleware Automático

```php
<?php

// El RateLimitMiddleware se aplica automáticamente
// Configuración en routes o aplicación global

$router->group(['middleware' => ['rate_limit']], function($router) {
    $router->get('/api/users', 'UserController@index');
    $router->post('/api/users', 'UserController@store');
});
```

## Métodos de la Interfaz

### isAllowed(string $identifier): bool

```php
<?php

// Verificar si se permite un request SIN consumir un token
$identifier = 'user:123';

if ($rateLimiter->isAllowed($identifier)) {
    echo "Request sería permitido\n";
} else {
    echo "Rate limit alcanzado\n";
}
```

### attempt(string $identifier): bool

```php
<?php

// Intentar consumir un token
$identifier = 'user:123';

if ($rateLimiter->attempt($identifier)) {
    echo "Request permitido y token consumido\n";
    // Procesar el request
} else {
    echo "Rate limit excedido\n";
    // Rechazar el request
}
```

### getInfo(string $identifier): array

```php
<?php

// Obtener información detallada del rate limit
$info = $rateLimiter->getInfo($identifier);

/*
Array (
    [identifier] => user:123
    [max_attempts] => 60
    [remaining_attempts] => 45
    [reset_time] => 30
    [decay_minutes] => 1
    [last_refill] => 1751692972
)
*/

echo "Identificador: " . $info['identifier'] . "\n";
echo "Máximo permitido: " . $info['max_attempts'] . "\n";
echo "Intentos restantes: " . $info['remaining_attempts'] . "\n";
echo "Tiempo para reset: " . $info['reset_time'] . " segundos\n";
echo "Ventana de tiempo: " . $info['decay_minutes'] . " minutos\n";
echo "Último relleno: " . date('H:i:s', $info['last_refill']) . "\n";
```

### reset(string $identifier): bool

```php
<?php

// Resetear el bucket para un identificador específico
$success = $rateLimiter->reset('user:123');

if ($success) {
    echo "Rate limit reseteado para user:123\n";
}
```

### clear(): bool

```php
<?php

// Limpiar TODOS los rate limits (usar con cuidado)
$success = $rateLimiter->clear();

if ($success) {
    echo "Todos los rate limits han sido limpiados\n";
}
```

## Estrategias de Identificación

### 1. Por IP Address

```php
<?php

class IpRateLimiter
{
    public function checkRequest(Request $request): bool
    {
        $ip = $request->getIp();
        return rate_limit("ip:{$ip}");
    }
}
```

### 2. Por Usuario Autenticado

```php
<?php

class UserRateLimiter
{
    public function checkRequest(Request $request): bool
    {
        $userId = $request->getAuthenticatedUserId();

        if ($userId) {
            return rate_limit("user:{$userId}");
        }

        // Fallback a IP para usuarios no autenticados
        $ip = $request->getIp();
        return rate_limit("ip:{$ip}");
    }
}
```

### 3. Por API Key

```php
<?php

class ApiKeyRateLimiter
{
    public function checkRequest(Request $request): bool
    {
        $apiKey = $request->getHeader('X-API-Key');

        if ($apiKey) {
            return rate_limit("api_key:{$apiKey}");
        }

        throw new UnauthorizedException('API Key required');
    }
}
```

### 4. Combinación de Factores

```php
<?php

class AdvancedRateLimiter
{
    public function checkRequest(Request $request): bool
    {
        $factors = [];

        // Factor 1: IP Address
        if ($ip = $request->getIp()) {
            $factors[] = "ip:{$ip}";
        }

        // Factor 2: User ID
        if ($userId = $request->getAuthenticatedUserId()) {
            $factors[] = "user:{$userId}";
        }

        // Factor 3: Endpoint específico
        $endpoint = $request->getPath();
        $factors[] = "endpoint:{$endpoint}";

        // Crear identificador compuesto
        $identifier = implode('|', $factors);

        return rate_limit($identifier);
    }
}
```

## Rate Limiting Avanzado

### 1. Diferentes Límites por Endpoint

```php
<?php

class EndpointRateLimiter
{
    private array $limits = [
        '/api/auth/login' => ['max' => 5, 'window' => 15], // 5 intentos por 15 min
        '/api/users' => ['max' => 100, 'window' => 1],     // 100 por minuto
        '/api/search' => ['max' => 20, 'window' => 1],     // 20 por minuto
    ];

    public function checkEndpoint(Request $request): bool
    {
        $endpoint = $request->getPath();
        $identifier = $request->getIp();

        if (!isset($this->limits[$endpoint])) {
            return true; // Sin límite específico
        }

        $limit = $this->limits[$endpoint];
        $key = "endpoint:{$endpoint}:ip:{$identifier}";

        // Usar rate limiter personalizado para este endpoint
        $rateLimiter = new TokenBucketRateLimiter(
            cache(),
            $limit['max'],
            $limit['window']
        );

        return $rateLimiter->attempt($key);
    }
}
```

### 2. Rate Limiting por Niveles de Usuario

```php
<?php

class TieredRateLimiter
{
    private array $userTiers = [
        'free' => ['max' => 100, 'window' => 60],     // 100 por hora
        'premium' => ['max' => 1000, 'window' => 60], // 1000 por hora
        'enterprise' => ['max' => 10000, 'window' => 60], // 10k por hora
    ];

    public function checkUserTier(Request $request): bool
    {
        $userId = $request->getAuthenticatedUserId();
        $userTier = $this->getUserTier($userId);

        $limits = $this->userTiers[$userTier] ?? $this->userTiers['free'];

        $rateLimiter = new TokenBucketRateLimiter(
            cache(),
            $limits['max'],
            $limits['window']
        );

        return $rateLimiter->attempt("user_tier:{$userTier}:user:{$userId}");
    }

    private function getUserTier(int $userId): string
    {
        // Lógica para determinar el tier del usuario
        return 'free'; // Ejemplo
    }
}
```

### 3. Rate Limiting con Burst Allowance

```php
<?php

class BurstRateLimiter
{
    public function checkWithBurst(string $identifier): bool
    {
        // Rate limiter normal: 60 requests por minuto
        $normalLimit = rate_limit("normal:{$identifier}");

        if ($normalLimit) {
            return true;
        }

        // Rate limiter de burst: 10 requests adicionales por hora
        $burstRateLimiter = new TokenBucketRateLimiter(
            cache(),
            10,    // 10 tokens de burst
            60     // se recargan cada hora
        );

        return $burstRateLimiter->attempt("burst:{$identifier}");
    }
}
```

## Middleware Integration

### RateLimitMiddleware Personalizado

```php
<?php

class CustomRateLimitMiddleware
{
    private RateLimiterInterface $rateLimiter;

    public function __construct(RateLimiterInterface $rateLimiter)
    {
        $this->rateLimiter = $rateLimiter;
    }

    public function handle(Request $request, callable $next): Response
    {
        $identifier = $this->getIdentifier($request);

        // Verificar rate limit
        if (!$this->rateLimiter->attempt($identifier)) {
            return $this->rateLimitResponse($identifier);
        }

        // Continuar con el request
        $response = $next($request);

        // Agregar headers informativos
        $this->addRateLimitHeaders($response, $identifier);

        return $response;
    }

    private function getIdentifier(Request $request): string
    {
        // Estrategia de identificación personalizada
        $userId = $request->getAuthenticatedUserId();
        $ip = $request->getIp();

        return $userId ? "user:{$userId}" : "ip:{$ip}";
    }

    private function rateLimitResponse(string $identifier): Response
    {
        $info = $this->rateLimiter->getInfo($identifier);

        return new Response(
            json_encode([
                'error' => 'Rate limit exceeded',
                'message' => 'Too many requests. Please try again later.',
                'retry_after' => $info['reset_time'],
                'limit' => $info['max_attempts'],
                'remaining' => $info['remaining_attempts']
            ]),
            429,
            [
                'Content-Type' => 'application/json',
                'X-RateLimit-Limit' => $info['max_attempts'],
                'X-RateLimit-Remaining' => $info['remaining_attempts'],
                'X-RateLimit-Reset' => time() + $info['reset_time'],
                'Retry-After' => $info['reset_time']
            ]
        );
    }

    private function addRateLimitHeaders(Response $response, string $identifier): void
    {
        $info = $this->rateLimiter->getInfo($identifier);

        $response->header('X-RateLimit-Limit', $info['max_attempts']);
        $response->header('X-RateLimit-Remaining', $info['remaining_attempts']);
        $response->header('X-RateLimit-Reset', time() + $info['reset_time']);
    }
}
```

## Headers HTTP Estándar

### Headers de Response

```http
HTTP/1.1 200 OK
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 59
X-RateLimit-Reset: 1751693632
```

### Headers de Rate Limit Excedido

```http
HTTP/1.1 429 Too Many Requests
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 0
X-RateLimit-Reset: 1751693632
Retry-After: 60
Content-Type: application/json

{
    "error": "Rate limit exceeded",
    "message": "Too many requests. Please try again later.",
    "retry_after": 60,
    "limit": 60,
    "remaining": 0
}
```

## Monitoring y Alertas

### 1. Logging de Rate Limits

```php
<?php

class LoggingRateLimiter implements RateLimiterInterface
{
    private RateLimiterInterface $rateLimiter;
    private LoggerInterface $logger;

    public function __construct(RateLimiterInterface $rateLimiter, LoggerInterface $logger)
    {
        $this->rateLimiter = $rateLimiter;
        $this->logger = $logger;
    }

    public function attempt(string $identifier): bool
    {
        $allowed = $this->rateLimiter->attempt($identifier);

        if (!$allowed) {
            $info = $this->rateLimiter->getInfo($identifier);

            $this->logger->warning('Rate limit exceeded', [
                'identifier' => $identifier,
                'max_attempts' => $info['max_attempts'],
                'remaining_attempts' => $info['remaining_attempts'],
                'reset_time' => $info['reset_time']
            ]);
        }

        return $allowed;
    }

    // ... otros métodos delegados
}
```

### 2. Métricas de Rate Limiting

```php
<?php

class RateLimitMetrics
{
    public function recordAttempt(string $identifier, bool $allowed): void
    {
        $metric = $allowed ? 'rate_limit.allowed' : 'rate_limit.blocked';

        // Registrar en sistema de métricas (Prometheus, StatsD, etc.)
        $this->metrics->increment($metric, [
            'identifier_type' => $this->getIdentifierType($identifier)
        ]);
    }

    public function getStats(): array
    {
        return [
            'total_attempts' => $this->metrics->get('rate_limit.total'),
            'allowed_attempts' => $this->metrics->get('rate_limit.allowed'),
            'blocked_attempts' => $this->metrics->get('rate_limit.blocked'),
            'block_rate' => $this->calculateBlockRate()
        ];
    }

    private function getIdentifierType(string $identifier): string
    {
        if (str_starts_with($identifier, 'user:')) return 'user';
        if (str_starts_with($identifier, 'ip:')) return 'ip';
        if (str_starts_with($identifier, 'api_key:')) return 'api_key';

        return 'unknown';
    }
}
```

### 3. Alertas Automáticas

```php
<?php

class RateLimitAlerting
{
    private float $alertThreshold = 0.8; // 80% de rate limits excedidos

    public function checkAlerts(): void
    {
        $stats = $this->getRateLimitStats();
        $blockRate = $stats['blocked'] / ($stats['total'] ?: 1);

        if ($blockRate > $this->alertThreshold) {
            $this->sendAlert([
                'type' => 'rate_limit_high_block_rate',
                'block_rate' => $blockRate,
                'threshold' => $this->alertThreshold,
                'total_requests' => $stats['total'],
                'blocked_requests' => $stats['blocked']
            ]);
        }
    }

    private function sendAlert(array $data): void
    {
        // Enviar alerta (email, Slack, webhook, etc.)
        $this->notificationService->send('high_rate_limit_blocks', $data);
    }
}
```

## Testing

### 1. Unit Tests

```php
<?php

class TokenBucketRateLimiterTest extends TestCase
{
    public function testAllowsRequestsWithinLimit(): void
    {
        $cache = new MemoryCache();
        $rateLimiter = new TokenBucketRateLimiter($cache, 5, 1);

        // Debe permitir 5 requests
        for ($i = 0; $i < 5; $i++) {
            $this->assertTrue($rateLimiter->attempt('test'));
        }

        // El 6to debe ser rechazado
        $this->assertFalse($rateLimiter->attempt('test'));
    }

    public function testTokenRefill(): void
    {
        $cache = new MemoryCache();
        $rateLimiter = new TokenBucketRateLimiter($cache, 5, 1);

        // Consumir todos los tokens
        for ($i = 0; $i < 5; $i++) {
            $rateLimiter->attempt('test');
        }

        // Simular paso del tiempo (esto requeriría mock del tiempo)
        // En test real, usarías time mocking

        // Después del tiempo de decay, debe permitir nuevos requests
        $this->assertTrue($rateLimiter->attempt('test'));
    }
}
```

### 2. Integration Tests

```php
<?php

class RateLimitMiddlewareTest extends TestCase
{
    public function testRateLimitMiddleware(): void
    {
        // Setup
        $app = $this->createApplication();

        // Hacer múltiples requests
        for ($i = 0; $i < 60; $i++) {
            $response = $app->request('GET', '/api/test');
            $this->assertEquals(200, $response->getStatusCode());
        }

        // El siguiente debe ser rechazado
        $response = $app->request('GET', '/api/test');
        $this->assertEquals(429, $response->getStatusCode());

        // Verificar headers
        $this->assertArrayHasKey('X-RateLimit-Limit', $response->getHeaders());
        $this->assertArrayHasKey('Retry-After', $response->getHeaders());
    }
}
```

## Best Practices

### 1. Identificadores Únicos

```php
<?php

// ✅ Buenos identificadores
rate_limit('user:123');
rate_limit('ip:192.168.1.1');
rate_limit('api_key:abc123def456');
rate_limit('endpoint:/api/search:user:123');

// ❌ Evitar identificadores genéricos
rate_limit('requests');
rate_limit('api');
```

### 2. Límites Apropiados

```php
<?php

// Diferentes límites según el contexto
$limits = [
    'auth' => ['max' => 5, 'window' => 15],      // Login: 5 por 15 min
    'api_read' => ['max' => 1000, 'window' => 1], // Lectura: 1000 por min
    'api_write' => ['max' => 100, 'window' => 1], // Escritura: 100 por min
    'search' => ['max' => 20, 'window' => 1],     // Búsqueda: 20 por min
];
```

### 3. Graceful Degradation

```php
<?php

class GracefulRateLimiter
{
    public function handleRequest(Request $request): Response
    {
        $identifier = $this->getIdentifier($request);

        if (!rate_limit($identifier)) {
            // En lugar de rechazar completamente,
            // ofrecer funcionalidad limitada
            return $this->handleLimitedRequest($request);
        }

        return $this->handleFullRequest($request);
    }

    private function handleLimitedRequest(Request $request): Response
    {
        // Servir desde cache, datos limitados, etc.
        return new Response(json_encode([
            'data' => $this->getCachedData(),
            'limited' => true,
            'message' => 'Serving cached data due to rate limiting'
        ]));
    }
}
```

### 4. Documentación para Desarrolladores

```php
<?php

/**
 * API Rate Limits
 *
 * Default limits:
 * - 1000 requests per hour for authenticated users
 * - 100 requests per hour for unauthenticated users
 * - 5 login attempts per 15 minutes per IP
 *
 * Headers returned:
 * - X-RateLimit-Limit: Maximum requests allowed
 * - X-RateLimit-Remaining: Requests remaining in current window
 * - X-RateLimit-Reset: Unix timestamp when limit resets
 *
 * When limit exceeded:
 * - HTTP 429 Too Many Requests
 * - Retry-After header with seconds to wait
 */
```

## Troubleshooting

### Problema: Rate Limits No Funcionan

```php
<?php

// 1. Verificar que el cache esté funcionando
$testKey = 'rate_limit_test_' . time();
cache($testKey, 'test_value', 60);
$retrieved = cache($testKey);

if ($retrieved !== 'test_value') {
    throw new \Exception('Cache no está funcionando correctamente');
}

// 2. Verificar configuración
$rateLimiter = app(RateLimiterInterface::class);
$info = $rateLimiter->getInfo('test_identifier');

if ($info['max_attempts'] !== 60) {
    throw new \Exception('Configuración de rate limit incorrecta');
}
```

### Problema: Rate Limits Demasiado Estrictos

```php
<?php

// Verificar configuración
$maxAttempts = env('RATE_LIMIT_MAX_ATTEMPTS', 60);
$decayMinutes = env('RATE_LIMIT_DECAY_MINUTES', 1);

echo "Max attempts: {$maxAttempts}\n";
echo "Decay minutes: {$decayMinutes}\n";

// Ajustar según necesidades
// Para desarrollo: límites más altos
// Para producción: límites apropiados para la carga esperada
```

### Problema: Memory/Performance Issues

```php
<?php

// 1. Usar FileCache en lugar de MemoryCache para persistencia
// 2. Limpiar entradas expiradas regularmente
// 3. Monitorear uso de cache

$cacheStats = [
    'total_keys' => $this->countCacheKeys(),
    'memory_usage' => memory_get_usage(true),
    'cache_size' => $this->getCacheSize()
];

if ($cacheStats['cache_size'] > 100 * 1024 * 1024) { // 100MB
    // Limpiar cache de rate limiting
    $rateLimiter->clear();
}
```
