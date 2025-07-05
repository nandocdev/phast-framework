# Controladores

> Capa de presentación que maneja peticiones HTTP y coordina respuestas

## 🎯 ¿Qué es un Controlador?

Los **controladores** en Phast son clases responsables de manejar peticiones HTTP entrantes, coordinar la ejecución de lógica de negocio a través de [[Servicios]], y retornar respuestas apropiadas. Siguen el patrón [[Clean Architecture]] actuando como adaptadores entre el protocolo HTTP y los casos de uso de la aplicación.

## 📝 Anatomía de un Controlador

### Controlador Básico

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

    /**
     * GET /blog
     * Listar todos los blogs
     */
    public function index(Request $request): Response
    {
        try {
            $blogs = $this->blogService->getAllPublished();

            return response()->json([
                'message' => 'Blogs retrieved successfully',
                'data' => array_map(fn($blog) => $blog->toArray(), $blogs),
                'total' => count($blogs)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to retrieve blogs',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * POST /blog
     * Crear un nuevo blog
     */
    public function store(Request $request): Response
    {
        try {
            $blog = $this->blogService->create($request->all());

            return response()->json([
                'message' => 'Blog created successfully',
                'data' => $blog->toArray()
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

    /**
     * GET /blog/{id}
     * Mostrar un blog específico
     */
    public function show(Request $request, int $id): Response
    {
        try {
            $blog = $this->blogService->findById($id);

            if (!$blog) {
                return response()->json([
                    'error' => 'Blog not found'
                ], 404);
            }

            return response()->json([
                'message' => 'Blog retrieved successfully',
                'data' => $blog->toArray()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to retrieve blog',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * PUT /blog/{id}
     * Actualizar un blog existente
     */
    public function update(Request $request, int $id): Response
    {
        try {
            $blog = $this->blogService->update($id, $request->all());

            return response()->json([
                'message' => 'Blog updated successfully',
                'data' => $blog->toArray()
            ]);

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

    /**
     * DELETE /blog/{id}
     * Eliminar un blog
     */
    public function destroy(Request $request, int $id): Response
    {
        try {
            $this->blogService->delete($id);

            return response()->json([
                'message' => 'Blog deleted successfully'
            ]);

        } catch (\DomainException $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 422);
        }
    }
}
```

## 🏗️ Crear Controladores

### Con CLI

```bash
# Controlador en módulo existente
php phast make:controller Blog PostController

# Controlador al crear módulo
php phast make:module Blog  # Crea BlogController automáticamente
```

### Manualmente

```php
<?php
namespace Phast\App\Modules\Ecommerce\Controllers;

use Phast\Core\Http\Controller;
use Phast\Core\Http\Request;
use Phast\Core\Http\Response;

class ProductController extends Controller
{
    public function __construct(
        private ProductService $productService
    ) {}

    // Métodos del controlador...
}
```

## 🎮 Métodos HTTP y Acciones

### Convenciones RESTful

| Método HTTP | Acción      | Propósito                  | Ejemplo URL      |
| ----------- | ----------- | -------------------------- | ---------------- |
| GET         | `index()`   | Listar recursos            | `GET /blog`      |
| GET         | `show()`    | Mostrar recurso específico | `GET /blog/1`    |
| POST        | `store()`   | Crear nuevo recurso        | `POST /blog`     |
| PUT/PATCH   | `update()`  | Actualizar recurso         | `PUT /blog/1`    |
| DELETE      | `destroy()` | Eliminar recurso           | `DELETE /blog/1` |

### Acciones Personalizadas

```php
class BlogController extends Controller
{
    /**
     * POST /blog/{id}/publish
     * Publicar un blog
     */
    public function publish(Request $request, int $id): Response
    {
        try {
            $blog = $this->blogService->publish($id);

            return response()->json([
                'message' => 'Blog published successfully',
                'data' => $blog->toArray()
            ]);

        } catch (\DomainException $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * GET /blog/search
     * Buscar blogs
     */
    public function search(Request $request): Response
    {
        $query = $request->get('q', '');
        $results = $this->blogService->search($query);

        return response()->json([
            'message' => 'Search completed',
            'data' => $results,
            'query' => $query
        ]);
    }

    /**
     * GET /blog/stats
     * Estadísticas de blogs
     */
    public function stats(Request $request): Response
    {
        $stats = $this->blogService->getStatistics();

        return response()->json([
            'message' => 'Statistics retrieved',
            'data' => $stats
        ]);
    }
}
```

## 📥 Manejo de Request

### Acceso a Datos

```php
class BlogController extends Controller
{
    public function store(Request $request): Response
    {
        // Todos los datos
        $allData = $request->all();

        // Datos específicos
        $title = $request->get('title');
        $content = $request->get('content');

        // Con valor por defecto
        $published = $request->get('published', false);

        // Solo campos específicos
        $blogData = $request->only(['title', 'content', 'slug']);

        // Excluir campos
        $safeData = $request->except(['_token', 'password']);

        // Verificar existencia
        if ($request->has('title')) {
            // Campo existe
        }

        // Verificar si está lleno
        if ($request->filled('content')) {
            // Campo existe y no está vacío
        }

        // JSON data
        $jsonData = $request->json();

        // Headers
        $authHeader = $request->header('Authorization');

        // Query parameters
        $page = $request->query('page', 1);

        // Files
        $file = $request->file('upload');

        return response()->json(['received' => $allData]);
    }
}
```

### Validación en Controlador

```php
class BlogController extends Controller
{
    public function store(Request $request): Response
    {
        // Validación básica
        $this->validateRequest($request, [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'slug' => 'nullable|string|max:100',
            'published' => 'boolean'
        ]);

        // Lógica del controlador
        $blog = $this->blogService->create($request->all());

        return response()->json([
            'message' => 'Blog created successfully',
            'data' => $blog->toArray()
        ], 201);
    }

    private function validateRequest(Request $request, array $rules): void
    {
        foreach ($rules as $field => $rule) {
            $this->validateField($request, $field, $rule);
        }
    }

    private function validateField(Request $request, string $field, string $rules): void
    {
        $value = $request->get($field);
        $ruleArray = explode('|', $rules);

        foreach ($ruleArray as $rule) {
            if ($rule === 'required' && empty($value)) {
                throw new \InvalidArgumentException("Field {$field} is required");
            }

            if (str_starts_with($rule, 'max:')) {
                $max = intval(str_replace('max:', '', $rule));
                if (strlen($value) > $max) {
                    throw new \InvalidArgumentException("Field {$field} exceeds maximum length of {$max}");
                }
            }

            // Más validaciones...
        }
    }
}
```

## 📤 Respuestas HTTP

### JSON Responses

```php
class BlogController extends Controller
{
    public function success(): Response
    {
        // Respuesta exitosa básica
        return response()->json([
            'message' => 'Operation successful'
        ]);
    }

    public function withData(): Response
    {
        // Con datos
        return response()->json([
            'message' => 'Data retrieved',
            'data' => ['id' => 1, 'title' => 'Blog Title']
        ]);
    }

    public function withStatus(): Response
    {
        // Con código de estado específico
        return response()->json([
            'message' => 'Resource created'
        ], 201);
    }

    public function withHeaders(): Response
    {
        // Con headers personalizados
        return response()->json([
            'message' => 'Success'
        ])->withHeaders([
            'X-Custom-Header' => 'value',
            'Cache-Control' => 'no-cache'
        ]);
    }

    public function errorResponse(): Response
    {
        // Respuesta de error
        return response()->json([
            'error' => 'Something went wrong',
            'code' => 'BLOG_001',
            'details' => 'Additional error information'
        ], 500);
    }
}
```

### Otros Tipos de Respuesta

```php
class BlogController extends Controller
{
    public function downloadFile(): Response
    {
        // Descarga de archivo
        return response()->download('/path/to/file.pdf');
    }

    public function redirect(): Response
    {
        // Redirección
        return response()->redirect('/blog');
    }

    public function view(): Response
    {
        // Renderizar vista (si se usa template engine)
        return response()->view('blog.index', [
            'blogs' => $this->blogService->getAll()
        ]);
    }

    public function raw(): Response
    {
        // Respuesta raw
        return response('Plain text content', 200, [
            'Content-Type' => 'text/plain'
        ]);
    }
}
```

## 🔒 Autenticación y Autorización

### Con Middleware

```php
// En routes.php
$router->group([
    'middleware' => [AuthMiddleware::class]
], function ($router) {
    $router->post('/blog', 'BlogController@store');
    $router->put('/blog/{id}', 'BlogController@update');
    $router->delete('/blog/{id}', 'BlogController@destroy');
});
```

### En el Controlador

```php
class BlogController extends Controller
{
    public function store(Request $request): Response
    {
        // Verificar autenticación
        $user = $this->getCurrentUser($request);
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Verificar autorización
        if (!$this->canCreateBlog($user)) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        // Proceder con la lógica
        $blog = $this->blogService->create($request->all(), $user);

        return response()->json([
            'message' => 'Blog created successfully',
            'data' => $blog->toArray()
        ], 201);
    }

    private function getCurrentUser(Request $request): ?User
    {
        // Obtener usuario del token, sesión, etc.
        $token = $request->header('Authorization');
        return $this->authService->getUserFromToken($token);
    }

    private function canCreateBlog(User $user): bool
    {
        // Verificar permisos
        return $user->hasPermission('blog.create');
    }
}
```

## 🎭 Controladores Especializados

### API Controller

```php
<?php
namespace Phast\App\Modules\Blog\Controllers;

class ApiBlogController extends Controller
{
    public function __construct(
        private BlogService $blogService
    ) {}

    public function index(Request $request): Response
    {
        $page = $request->query('page', 1);
        $limit = $request->query('limit', 10);

        $blogs = $this->blogService->getPaginated($page, $limit);

        return response()->json([
            'data' => $blogs['data'],
            'pagination' => [
                'current_page' => $page,
                'per_page' => $limit,
                'total' => $blogs['total'],
                'total_pages' => ceil($blogs['total'] / $limit)
            ]
        ]);
    }

    public function store(Request $request): Response
    {
        $this->validateApiRequest($request);

        $blog = $this->blogService->create($request->all());

        return response()->json($blog->toArray(), 201)
            ->withHeaders([
                'Location' => "/api/blog/{$blog->getId()}"
            ]);
    }

    private function validateApiRequest(Request $request): void
    {
        if (!$request->isJson()) {
            throw new \InvalidArgumentException('Content-Type must be application/json');
        }

        // Validaciones específicas de API
    }
}
```

### Admin Controller

```php
<?php
namespace Phast\App\Modules\Blog\Controllers;

class AdminBlogController extends Controller
{
    public function __construct(
        private BlogService $blogService,
        private AdminAuthService $adminAuth
    ) {}

    public function dashboard(Request $request): Response
    {
        $this->requireAdminAccess($request);

        $stats = $this->blogService->getAdminStatistics();

        return response()->json([
            'stats' => $stats,
            'recent_blogs' => $this->blogService->getRecent(5),
            'pending_reviews' => $this->blogService->getPendingReview()
        ]);
    }

    public function moderate(Request $request, int $id): Response
    {
        $this->requireAdminAccess($request);

        $action = $request->get('action'); // approve, reject, delete
        $result = $this->blogService->moderate($id, $action);

        return response()->json([
            'message' => "Blog {$action}d successfully",
            'data' => $result
        ]);
    }

    private function requireAdminAccess(Request $request): void
    {
        if (!$this->adminAuth->isAdmin($request)) {
            throw new \UnauthorizedException('Admin access required');
        }
    }
}
```

## 🧪 Testing de Controladores

### Test Unitario

```php
<?php
namespace Tests\Unit\Controllers;

use PHPUnit\Framework\TestCase;
use Phast\App\Modules\Blog\Controllers\BlogController;
use Phast\App\Modules\Blog\Services\BlogService;
use Phast\Core\Http\Request;

class BlogControllerTest extends TestCase
{
    private BlogController $controller;
    private BlogService $blogService;

    protected function setUp(): void
    {
        $this->blogService = $this->createMock(BlogService::class);
        $this->controller = new BlogController($this->blogService);
    }

    public function test_index_returns_blogs(): void
    {
        $mockBlogs = [
            $this->createMockBlog(1, 'Blog 1'),
            $this->createMockBlog(2, 'Blog 2')
        ];

        $this->blogService
            ->expects($this->once())
            ->method('getAllPublished')
            ->willReturn($mockBlogs);

        $request = new Request();
        $response = $this->controller->index($request);

        $this->assertEquals(200, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('data', $data);
        $this->assertCount(2, $data['data']);
    }

    public function test_store_creates_blog(): void
    {
        $requestData = [
            'title' => 'New Blog',
            'content' => 'Blog content'
        ];

        $mockBlog = $this->createMockBlog(1, 'New Blog');

        $this->blogService
            ->expects($this->once())
            ->method('create')
            ->with($requestData)
            ->willReturn($mockBlog);

        $request = new Request($requestData);
        $response = $this->controller->store($request);

        $this->assertEquals(201, $response->getStatusCode());
    }

    private function createMockBlog(int $id, string $title): object
    {
        $mock = $this->createMock(\Phast\App\Modules\Blog\Models\Entities\Blog::class);

        $mock->method('toArray')->willReturn([
            'id' => $id,
            'title' => $title,
            'content' => 'Content',
            'slug' => strtolower(str_replace(' ', '-', $title))
        ]);

        return $mock;
    }
}
```

## 📋 Mejores Prácticas

### ✅ Hacer

-  **Mantener controladores delgados**: Solo coordinación, no lógica de negocio
-  **Usar inyección de dependencias**: Constructor injection para servicios
-  **Manejar excepciones**: Capturar y transformar excepciones a respuestas HTTP
-  **Validar entrada**: Validar datos de request antes de pasarlos a servicios
-  **Retornar respuestas consistentes**: Formato JSON estándar
-  **Documentar endpoints**: Comentarios claros sobre propósito y parámetros

### ❌ Evitar

-  **Lógica de negocio en controladores**: Usar servicios para eso
-  **Acceso directo a base de datos**: Usar repositorios
-  **Controladores gordos**: Métodos largos y complejos
-  **Dependencias de framework en servicios**: Mantener separación
-  **Respuestas inconsistentes**: Diferentes formatos para endpoints similares

---

#controladores #http #api #rest #mvc #clean-architecture #phast

[[README]] | [[Módulos]] | [[Servicios]] | [[Rutas]] | [[Middleware]]
