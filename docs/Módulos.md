# Módulos

> Sistema modular de organización de código siguiendo Domain-Driven Design

## 🎯 ¿Qué es un Módulo?

Un **módulo** en Phast es una unidad funcional independiente que encapsula un dominio específico de tu aplicación. Cada módulo sigue los principios de [[Clean Architecture]] y contiene todos los componentes necesarios para funcionar de manera autónoma.

## 📂 Estructura de un Módulo

```
app/Modules/Blog/
├── Controllers/               # Capa de presentación
│   ├── BlogController.php    # Controlador principal
│   └── AdminBlogController.php # Controlador específico
├── Models/
│   ├── Entities/             # [[Entidades]] de dominio
│   │   ├── Blog.php
│   │   └── Comment.php
│   ├── Repositories/         # [[Repositorios]] de datos
│   │   ├── BlogRepository.php
│   │   └── CommentRepository.php
│   └── ValueObjects/         # [[Value Objects]]
│       ├── BlogId.php
│       ├── BlogSlug.php
│       └── PublishDate.php
├── Services/                 # [[Servicios]] de aplicación
│   ├── BlogService.php      # Servicio principal
│   ├── CommentService.php   # Servicio específico
│   └── BlogSearchService.php
├── Providers/               # Inyección de dependencias
│   └── BlogServiceProvider.php
├── Events/                  # Eventos del dominio (opcional)
│   ├── BlogCreated.php
│   └── BlogPublished.php
├── Listeners/               # Listeners de eventos (opcional)
│   └── SendBlogNotification.php
├── routes.php              # [[Rutas]] del módulo
└── README.md               # Documentación del módulo
```

## 🚀 Crear un Módulo

### Comando Básico

```bash
php phast make:module Blog
```

### Con Componentes Específicos

```bash
# Crear módulo completo
php phast make:module Ecommerce

# Agregar componentes adicionales
php phast make:entity Product --module=Ecommerce
php phast make:entity Category --module=Ecommerce
php phast make:service PaymentService --module=Ecommerce
php phast make:valueobject Money --module=Ecommerce
```

## 🔧 Componentes de un Módulo

### [[Controladores]]

Manejan las peticiones HTTP y coordinan la ejecución:

```php
<?php
namespace Phast\App\Modules\Blog\Controllers;

use Phast\Core\Http\Controller;
use Phast\Core\Http\Request;
use Phast\Core\Http\Response;
use Phast\App\Modules\Blog\Services\BlogService;

class BlogController extends Controller
{
    public function __construct(
        private BlogService $blogService
    ) {}

    public function index(Request $request): Response
    {
        $blogs = $this->blogService->getAllPublished();
        return response()->json($blogs);
    }

    public function store(Request $request): Response
    {
        $blog = $this->blogService->create($request->all());
        return response()->json($blog, 201);
    }
}
```

### [[Entidades]]

Representan conceptos del dominio:

```php
<?php
namespace Phast\App\Modules\Blog\Models\Entities;

use Phast\App\Modules\Blog\Models\ValueObjects\BlogId;
use Phast\App\Modules\Blog\Models\ValueObjects\BlogSlug;

class Blog
{
    private BlogId $id;
    private string $title;
    private string $content;
    private BlogSlug $slug;
    private bool $published = false;
    private \DateTime $createdAt;

    public function __construct(
        string $title,
        string $content,
        BlogSlug $slug
    ) {
        $this->id = BlogId::generate();
        $this->title = $title;
        $this->content = $content;
        $this->slug = $slug;
        $this->createdAt = new \DateTime();
    }

    public function publish(): void
    {
        $this->published = true;
        // Disparar evento BlogPublished
    }

    public function isPublished(): bool
    {
        return $this->published;
    }

    // Getters y otros métodos...
}
```

### [[Servicios]]

Contienen la lógica de negocio:

```php
<?php
namespace Phast\App\Modules\Blog\Services;

use Phast\App\Modules\Blog\Models\Entities\Blog;
use Phast\App\Modules\Blog\Models\Repositories\BlogRepository;
use Phast\App\Modules\Blog\Models\ValueObjects\BlogSlug;

class BlogService
{
    public function __construct(
        private BlogRepository $blogRepository
    ) {}

    public function create(array $data): Blog
    {
        $this->validateBlogData($data);
        
        $slug = new BlogSlug($data['slug'] ?? $this->generateSlug($data['title']));
        
        if ($this->blogRepository->existsBySlug($slug)) {
            throw new \DomainException('Blog with this slug already exists');
        }

        $blog = new Blog(
            $data['title'],
            $data['content'],
            $slug
        );

        return $this->blogRepository->save($blog);
    }

    public function getAllPublished(): array
    {
        return $this->blogRepository->findPublished();
    }

    private function generateSlug(string $title): string
    {
        return strtolower(str_replace(' ', '-', $title));
    }

    private function validateBlogData(array $data): void
    {
        if (empty($data['title'])) {
            throw new \InvalidArgumentException('Title is required');
        }
        
        if (empty($data['content'])) {
            throw new \InvalidArgumentException('Content is required');
        }
    }
}
```

### [[Repositorios]]

Manejan la persistencia de datos:

```php
<?php
namespace Phast\App\Modules\Blog\Models\Repositories;

use Phast\App\Modules\Blog\Models\Entities\Blog;
use Phast\App\Modules\Blog\Models\ValueObjects\BlogSlug;

class BlogRepository
{
    public function save(Blog $blog): Blog
    {
        // Implementación de persistencia
        // Puede usar Doctrine, Eloquent, o SQL puro
    }

    public function findPublished(): array
    {
        // Buscar blogs publicados
    }

    public function existsBySlug(BlogSlug $slug): bool
    {
        // Verificar si existe blog con ese slug
    }

    public function findById(int $id): ?Blog
    {
        // Buscar por ID
    }
}
```

### Service Providers

Configuran la inyección de dependencias:

```php
<?php
namespace Phast\App\Modules\Blog\Providers;

use League\Container\ServiceProvider\AbstractServiceProvider;
use Phast\App\Modules\Blog\Models\Repositories\BlogRepository;
use Phast\App\Modules\Blog\Services\BlogService;

class BlogServiceProvider extends AbstractServiceProvider
{
    protected $provides = [
        BlogRepository::class,
        BlogService::class,
    ];

    public function register(): void
    {
        $this->container->add(BlogRepository::class);
        
        $this->container->add(BlogService::class)
            ->addArgument(BlogRepository::class);
    }
}
```

## 🛣️ Rutas del Módulo

Cada módulo tiene su archivo de rutas:

```php
<?php
// app/Modules/Blog/routes.php

$router->group(['prefix' => 'blog'], function ($router) {
    // Rutas públicas
    $router->get('/', 'BlogController@index')->name('blog.index');
    $router->get('/{slug}', 'BlogController@show')->name('blog.show');

    // Rutas protegidas
    $router->group([
        'middleware' => [AuthMiddleware::class]
    ], function ($router) {
        $router->post('/', 'BlogController@store')->name('blog.store');
        $router->put('/{id}', 'BlogController@update')->name('blog.update');
        $router->delete('/{id}', 'BlogController@destroy')->name('blog.destroy');
    });
});
```

## 🔄 Comunicación Entre Módulos

### Eventos y Listeners

```php
// Disparar evento desde Blog
event(new BlogPublished($blog));

// Listener en módulo Notification
class SendBlogNotificationListener
{
    public function handle(BlogPublished $event): void
    {
        // Enviar notificación
    }
}
```

### Servicios Compartidos

```php
// Inyectar servicio de otro módulo
class BlogService
{
    public function __construct(
        private BlogRepository $blogRepository,
        private NotificationService $notificationService // De otro módulo
    ) {}
}
```

## 📋 Comandos de Módulos

### Crear Componentes

```bash
# Entidades
php phast make:entity Post --module=Blog
php phast make:entity Comment --module=Blog

# Servicios
php phast make:service CommentService --module=Blog
php phast make:service BlogSearchService --module=Blog

# Repositorios
php phast make:repository CommentRepository --module=Blog --entity=Comment

# Value Objects
php phast make:valueobject BlogSlug --module=Blog
php phast make:valueobject PublishDate --module=Blog

# Providers
php phast make:provider CustomServiceProvider --module=Blog
```

### Eliminar Componentes

```bash
# Eliminar componente específico
php phast delete:component entity Comment --module=Blog

# Eliminar módulo completo
php phast delete:module Blog --force
```

## 🎨 Patrones de Diseño

### Repository Pattern

Abstrae el acceso a datos:

```php
interface BlogRepositoryInterface
{
    public function save(Blog $blog): Blog;
    public function findById(int $id): ?Blog;
    public function findPublished(): array;
}

class BlogRepository implements BlogRepositoryInterface
{
    // Implementación específica
}
```

### Service Layer Pattern

Encapsula lógica de negocio:

```php
class BlogService
{
    // Operaciones de dominio
    public function publishBlog(int $blogId): void
    public function scheduleBlog(int $blogId, \DateTime $publishDate): void
    public function archiveBlog(int $blogId): void
}
```

### Value Object Pattern

Representa valores inmutables:

```php
class BlogSlug
{
    private string $value;

    public function __construct(string $value)
    {
        $this->validate($value);
        $this->value = $value;
    }

    private function validate(string $value): void
    {
        if (empty($value)) {
            throw new \InvalidArgumentException('Slug cannot be empty');
        }

        if (!preg_match('/^[a-z0-9-]+$/', $value)) {
            throw new \InvalidArgumentException('Invalid slug format');
        }
    }

    public function toString(): string
    {
        return $this->value;
    }
}
```

## 🏷️ Ventajas del Sistema Modular

### ✅ Beneficios

- **Separación de responsabilidades**: Cada módulo maneja un dominio específico
- **Reutilización**: Módulos pueden reutilizarse en otros proyectos
- **Mantenibilidad**: Código organizado y fácil de mantener
- **Escalabilidad**: Fácil agregar nuevas funcionalidades
- **Testing**: Tests focalizados por dominio
- **Equipos**: Diferentes equipos pueden trabajar en módulos distintos

### ⚠️ Consideraciones

- **Comunicación**: Definir bien las interfaces entre módulos
- **Dependencias**: Evitar dependencias circulares
- **Granularidad**: No crear módulos demasiado pequeños o grandes
- **Consistencia**: Mantener patrones consistentes entre módulos

## 📚 Ejemplos de Módulos Comunes

### Blog
- Posts, categorías, comentarios, tags

### Ecommerce
- Productos, categorías, carritos, órdenes, pagos

### User Management
- Usuarios, roles, permisos, autenticación

### Notification
- Emails, SMS, push notifications, templates

### Analytics
- Métricas, reportes, dashboards, estadísticas

---

#módulos #arquitectura #ddd #clean-architecture #organización #phast

[[README]] | [[Clean Architecture]] | [[Comandos CLI]] | [[Controladores]]
