# Phast Framework

Un framework PHP moderno y limpio basado en principios de arquitectura limpia y código SOLID.

## Características

-  **Arquitectura Limpia**: Separación clara de responsabilidades
-  **Inyección de Dependencias**: Container DI personalizado
-  **Routing**: Sistema de rutas flexible con FastRoute
-  **Validación**: Sistema de validación robusto
-  **ORM**: Implementación con Doctrine ORM
-  **Logs**: Sistema de logging con Monolog
-  **Migraciones**: Control de versiones de base de datos con Phinx

## Estructura del Proyecto

```
├── app/                    # Código de la aplicación
│   ├── Modules/           # Módulos de la aplicación
│   │   └── Users/         # Módulo de usuarios (ejemplo)
│   │       ├── Controllers/
│   │       ├── Models/
│   │       │   ├── Entities/
│   │       │   ├── Repositories/
│   │       │   └── ValueObjects/
│   │       ├── Services/
│   │       └── routes.php
│   └── routes.php         # Rutas principales
├── config/                # Archivos de configuración
├── core/                  # Core del framework
│   ├── Application/       # Bootstrap y Container
│   ├── Config/           # Sistema de configuración
│   ├── Contracts/        # Interfaces
│   ├── Http/             # Request, Response, Controller
│   ├── Routing/          # Sistema de rutas
│   └── Validation/       # Sistema de validación
├── migrations/           # Migraciones de base de datos
├── public/              # Punto de entrada web
├── resources/           # Recursos (views, assets)
├── storage/             # Almacenamiento (logs, cache)
└── vendor/              # Dependencias de Composer
```

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

-  `GET /api/users` - Obtener todos los usuarios
-  `GET /api/users/{id}` - Obtener usuario por ID
-  `POST /api/users` - Crear nuevo usuario
-  `PUT /api/users/{id}` - Actualizar usuario
-  `DELETE /api/users/{id}` - Eliminar usuario

### Ejemplo de uso:

```bash
# Crear usuario
curl -X POST http://localhost:8000/api/users \
  -H "Content-Type: application/json" \
  -d '{"name": "Juan Pérez", "email": "juan@example.com", "password": "123456"}'

# Obtener usuarios
curl http://localhost:8000/api/users

# Obtener usuario específico
curl http://localhost:8000/api/users/1
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
# Migraciones
./vendor/bin/phinx create CreateTableName
./vendor/bin/phinx migrate
./vendor/bin/phinx rollback

# Tests
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
