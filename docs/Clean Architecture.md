# Clean Architecture

> Principios arquitectónicos que guían el diseño del framework Phast

## 🎯 ¿Qué es Clean Architecture?

**Clean Architecture** es un enfoque de diseño que separa el software en capas concéntricas, donde las dependencias apuntan hacia adentro, hacia el núcleo del negocio. Phast implementa estos principios para crear aplicaciones mantenibles, testeable y flexibles.

## 🏗️ Capas de la Arquitectura

### Vista General

```
┌─────────────────────────────────────────┐
│          🌐 Frameworks & Drivers        │  ← Capa Externa
│  (Web, Database, UI, Devices)          │
├─────────────────────────────────────────┤
│        🎮 Interface Adapters           │  ← Controladores, Presentadores
│  (Controllers, Gateways, Presenters)   │
├─────────────────────────────────────────┤
│         ⚙️ Application Business         │  ← Casos de uso
│      (Use Cases, Services)             │
├─────────────────────────────────────────┤
│        🏛️ Enterprise Business          │  ← Entidades del dominio
│     (Entities, Value Objects)          │    (Núcleo)
└─────────────────────────────────────────┘
```

## 🏛️ Capa 1: Enterprise Business Rules

### Entidades de Dominio

El corazón de la aplicación. Contienen las reglas de negocio más críticas.

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
    private ?\DateTime $publishedAt = null;

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

    /**
     * Regla de negocio: Un blog solo puede publicarse si tiene contenido
     */
    public function publish(): void
    {
        if (empty($this->content)) {
            throw new \DomainException('Cannot publish blog without content');
        }

        if ($this->published) {
            throw new \DomainException('Blog is already published');
        }

        $this->published = true;
        $this->publishedAt = new \DateTime();
    }

    /**
     * Regla de negocio: Un blog publicado no puede ser despublicado
     */
    public function unpublish(): void
    {
        if (!$this->published) {
            throw new \DomainException('Blog is not published');
        }

        $this->published = false;
        $this->publishedAt = null;
    }

    public function isPublished(): bool
    {
        return $this->published;
    }

    // Getters y otros métodos...
}
```

### [[Value Objects]]

Objetos inmutables que representan valores del dominio:

```php
<?php
namespace Phast\App\Modules\Blog\Models\ValueObjects;

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
            throw new \InvalidArgumentException('Slug must contain only lowercase letters, numbers, and hyphens');
        }

        if (strlen($value) > 100) {
            throw new \InvalidArgumentException('Slug cannot exceed 100 characters');
        }
    }

    public function toString(): string
    {
        return $this->value;
    }

    public function equals(BlogSlug $other): bool
    {
        return $this->value === $other->value;
    }
}
```

## ⚙️ Capa 2: Application Business Rules

### [[Servicios]] de Aplicación

Orquestan casos de uso específicos:

```php
<?php
namespace Phast\App\Modules\Blog\Services;

use Phast\App\Modules\Blog\Models\Entities\Blog;
use Phast\App\Modules\Blog\Models\Repositories\BlogRepository;
use Phast\App\Modules\Blog\Models\ValueObjects\BlogSlug;

class BlogService
{
    public function __construct(
        private BlogRepository $blogRepository,
        private EventDispatcher $eventDispatcher
    ) {}

    /**
     * Caso de uso: Crear un nuevo blog
     */
    public function createBlog(CreateBlogRequest $request): Blog
    {
        // Validar datos de entrada
        $this->validateCreateRequest($request);

        // Crear slug automáticamente si no se proporciona
        $slug = $request->slug
            ? new BlogSlug($request->slug)
            : $this->generateSlugFromTitle($request->title);

        // Verificar que el slug sea único
        if ($this->blogRepository->existsBySlug($slug)) {
            throw new \DomainException('A blog with this slug already exists');
        }

        // Crear entidad
        $blog = new Blog(
            $request->title,
            $request->content,
            $slug
        );

        // Persistir
        $blog = $this->blogRepository->save($blog);

        // Disparar evento
        $this->eventDispatcher->dispatch(new BlogCreated($blog));

        return $blog;
    }

    /**
     * Caso de uso: Publicar un blog
     */
    public function publishBlog(int $blogId): Blog
    {
        $blog = $this->blogRepository->findById($blogId);

        if (!$blog) {
            throw new \DomainException('Blog not found');
        }

        // La lógica de publicación está en la entidad
        $blog->publish();

        // Persistir cambios
        $blog = $this->blogRepository->save($blog);

        // Disparar evento
        $this->eventDispatcher->dispatch(new BlogPublished($blog));

        return $blog;
    }

    private function generateSlugFromTitle(string $title): BlogSlug
    {
        $slug = strtolower(str_replace(' ', '-', $title));
        $slug = preg_replace('/[^a-z0-9-]/', '', $slug);

        return new BlogSlug($slug);
    }

    private function validateCreateRequest(CreateBlogRequest $request): void
    {
        if (empty($request->title)) {
            throw new \InvalidArgumentException('Title is required');
        }

        if (empty($request->content)) {
            throw new \InvalidArgumentException('Content is required');
        }
    }
}
```

### DTOs (Data Transfer Objects)

```php
<?php
namespace Phast\App\Modules\Blog\DTOs;

class CreateBlogRequest
{
    public function __construct(
        public readonly string $title,
        public readonly string $content,
        public readonly ?string $slug = null,
        public readonly ?int $authorId = null
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            title: $data['title'] ?? '',
            content: $data['content'] ?? '',
            slug: $data['slug'] ?? null,
            authorId: $data['author_id'] ?? null
        );
    }
}
```

## 🎮 Capa 3: Interface Adapters

### [[Controladores]]

Adaptan las peticiones HTTP a casos de uso:

```php
<?php
namespace Phast\App\Modules\Blog\Controllers;

use Phast\Core\Http\Controller;
use Phast\Core\Http\Request;
use Phast\Core\Http\Response;
use Phast\App\Modules\Blog\Services\BlogService;
use Phast\App\Modules\Blog\DTOs\CreateBlogRequest;

class BlogController extends Controller
{
    public function __construct(
        private BlogService $blogService
    ) {}

    public function store(Request $request): Response
    {
        try {
            // Convertir request HTTP a DTO
            $createRequest = CreateBlogRequest::fromArray($request->all());

            // Ejecutar caso de uso
            $blog = $this->blogService->createBlog($createRequest);

            // Convertir entidad a respuesta HTTP
            return response()->json([
                'message' => 'Blog created successfully',
                'data' => $this->transformBlogToArray($blog)
            ], 201);

        } catch (\DomainException $e) {
            return response()->json([
                'error' => 'Business rule violation',
                'message' => $e->getMessage()
            ], 422);

        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'error' => 'Validation error',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function publish(Request $request, int $id): Response
    {
        try {
            $blog = $this->blogService->publishBlog($id);

            return response()->json([
                'message' => 'Blog published successfully',
                'data' => $this->transformBlogToArray($blog)
            ]);

        } catch (\DomainException $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 422);
        }
    }

    private function transformBlogToArray(Blog $blog): array
    {
        return [
            'id' => $blog->getId()->toInt(),
            'title' => $blog->getTitle(),
            'content' => $blog->getContent(),
            'slug' => $blog->getSlug()->toString(),
            'published' => $blog->isPublished(),
            'created_at' => $blog->getCreatedAt()->format('Y-m-d H:i:s'),
            'published_at' => $blog->getPublishedAt()?->format('Y-m-d H:i:s'),
        ];
    }
}
```

### [[Repositorios]]

Adaptan el acceso a datos:

```php
<?php
namespace Phast\App\Modules\Blog\Models\Repositories;

use Phast\App\Modules\Blog\Models\Entities\Blog;
use Phast\App\Modules\Blog\Models\ValueObjects\BlogSlug;

class BlogRepository
{
    public function __construct(
        private DatabaseConnection $connection
    ) {}

    public function save(Blog $blog): Blog
    {
        if ($blog->getId()) {
            return $this->update($blog);
        }

        return $this->insert($blog);
    }

    public function findById(int $id): ?Blog
    {
        $data = $this->connection->fetchOne(
            'SELECT * FROM blogs WHERE id = ?',
            [$id]
        );

        return $data ? $this->hydrateBlog($data) : null;
    }

    public function existsBySlug(BlogSlug $slug): bool
    {
        $count = $this->connection->fetchColumn(
            'SELECT COUNT(*) FROM blogs WHERE slug = ?',
            [$slug->toString()]
        );

        return $count > 0;
    }

    public function findPublished(): array
    {
        $results = $this->connection->fetchAll(
            'SELECT * FROM blogs WHERE published = 1 ORDER BY published_at DESC'
        );

        return array_map([$this, 'hydrateBlog'], $results);
    }

    private function hydrateBlog(array $data): Blog
    {
        // Reconstruir entidad desde datos de BD
        $blog = new Blog(
            $data['title'],
            $data['content'],
            new BlogSlug($data['slug'])
        );

        // Establecer estado interno
        if ($data['published']) {
            $blog->publish();
        }

        return $blog;
    }

    private function insert(Blog $blog): Blog
    {
        // Implementar inserción
    }

    private function update(Blog $blog): Blog
    {
        // Implementar actualización
    }
}
```

## 🌐 Capa 4: Frameworks & Drivers

### Web Framework (HTTP)

```php
<?php
// public/index.php

use Phast\Core\Application\Bootstrap;

require_once __DIR__ . '/../vendor/autoload.php';

$bootstrap = new Bootstrap();
$application = $bootstrap->createWebApplication();

$response = $application->handle(
    \Phast\Core\Http\Request::fromGlobals()
);

$response->send();
```

### Base de Datos

```php
<?php
namespace Phast\Core\Database;

class DatabaseConnection
{
    private \PDO $pdo;

    public function __construct(array $config)
    {
        $this->pdo = new \PDO(
            $config['dsn'],
            $config['username'],
            $config['password'],
            $config['options'] ?? []
        );
    }

    public function fetchOne(string $sql, array $params = []): ?array
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    // Otros métodos...
}
```

## 🔗 Dependency Rule

### ✅ Dependencias Permitidas

```php
// ✅ Correcto: Capa exterior depende de capa interior
class BlogController  // Interface Adapter
{
    public function __construct(
        private BlogService $blogService  // Application Business Rules
    ) {}
}

class BlogService  // Application Business Rules
{
    public function __construct(
        private BlogRepository $repository  // Interface (hacia Enterprise)
    ) {}
}
```

### ❌ Dependencias NO Permitidas

```php
// ❌ Incorrecto: Capa interior NO debe depender de capa exterior
class Blog  // Enterprise Business Rules
{
    public function __construct(
        private HttpRequest $request  // ❌ Framework dependency
    ) {}
}

class BlogService  // Application Business Rules
{
    public function __construct(
        private BlogController $controller  // ❌ Interface Adapter dependency
    ) {}
}
```

## 🎯 Beneficios de Clean Architecture

### ✅ Ventajas

1. **Independencia de Frameworks**: Puedes cambiar el framework sin afectar la lógica de negocio
2. **Testeable**: Fácil testing de cada capa por separado
3. **Independencia de UI**: La lógica no depende de la interfaz
4. **Independencia de Database**: Puedes cambiar la BD sin afectar las reglas de negocio
5. **Independencia de Agentes Externos**: Las reglas de negocio no saben de APIs externas

### 🔄 Inversión de Dependencias

```php
// En lugar de esto (dependencia directa):
class BlogService
{
    private MySQLBlogRepository $repository;  // ❌ Dependencia concreta
}

// Usa esto (inversión de dependencia):
interface BlogRepositoryInterface
{
    public function save(Blog $blog): Blog;
    public function findById(int $id): ?Blog;
}

class BlogService
{
    private BlogRepositoryInterface $repository;  // ✅ Dependencia abstracta
}

class MySQLBlogRepository implements BlogRepositoryInterface
{
    // Implementación específica
}
```

## 🧪 Testing en Clean Architecture

### Test de Entidades (Núcleo)

```php
class BlogTest extends TestCase
{
    public function test_can_publish_blog_with_content(): void
    {
        $blog = new Blog(
            'Test Title',
            'Test content',
            new BlogSlug('test-slug')
        );

        $blog->publish();

        $this->assertTrue($blog->isPublished());
        $this->assertNotNull($blog->getPublishedAt());
    }

    public function test_cannot_publish_blog_without_content(): void
    {
        $blog = new Blog(
            'Test Title',
            '',  // Sin contenido
            new BlogSlug('test-slug')
        );

        $this->expectException(\DomainException::class);
        $blog->publish();
    }
}
```

### Test de Servicios (Casos de Uso)

```php
class BlogServiceTest extends TestCase
{
    public function test_can_create_blog(): void
    {
        $repository = $this->createMock(BlogRepository::class);
        $eventDispatcher = $this->createMock(EventDispatcher::class);

        $service = new BlogService($repository, $eventDispatcher);

        $request = new CreateBlogRequest(
            title: 'Test Blog',
            content: 'Test content'
        );

        $repository->expects($this->once())
                  ->method('save')
                  ->willReturn($this->createMock(Blog::class));

        $result = $service->createBlog($request);

        $this->assertInstanceOf(Blog::class, $result);
    }
}
```

## 📋 Checklist de Clean Architecture

### ✅ Verificaciones

-  [ ] Las entidades no dependen de frameworks externos
-  [ ] Los servicios orquestan casos de uso sin conocer detalles de implementación
-  [ ] Los controladores solo adaptan entre HTTP y casos de uso
-  [ ] Los repositorios implementan interfaces definidas en capas interiores
-  [ ] Las dependencias apuntan hacia adentro
-  [ ] Cada capa es testeable independientemente
-  [ ] Las reglas de negocio están en las entidades
-  [ ] Los casos de uso están en los servicios

---

#clean-architecture #arquitectura #solid #ddd #testing #separación-responsabilidades #phast

[[README]] | [[Módulos]] | [[Controladores]] | [[Servicios]] | [[Entidades]]
