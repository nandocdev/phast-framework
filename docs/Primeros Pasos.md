# Primeros Pasos

> Crea tu primera aplicación con Phast Framework en 10 minutos

## 🎯 Objetivo

Vamos a crear un sistema básico de blog con:
- Módulo `Blog`
- Gestión de posts
- API REST completa

## 🚀 Paso 1: Crear el Módulo

```bash
php phast make:module Blog
```

Esto creará la estructura completa:

```
app/Modules/Blog/
├── Controllers/
│   └── BlogController.php
├── Models/
│   ├── Entities/
│   │   └── Blog.php
│   ├── Repositories/
│   │   └── BlogRepository.php
│   └── ValueObjects/
│       └── BlogId.php
├── Services/
│   └── BlogService.php
├── Providers/
│   └── BlogServiceProvider.php
├── routes.php
└── README.md
```

## 📝 Paso 2: Personalizar la Entidad

Edita `app/Modules/Blog/Models/Entities/Blog.php`:

```php
<?php
namespace Phast\App\Modules\Blog\Models\Entities;

class Blog
{
    private int $id;
    private string $title;
    private string $content;
    private string $slug;
    private \DateTime $createdAt;
    private ?\DateTime $updatedAt = null;

    public function __construct(
        string $title, 
        string $content, 
        string $slug
    ) {
        $this->title = $title;
        $this->content = $content;
        $this->slug = $slug;
        $this->createdAt = new \DateTime();
    }

    // Getters
    public function getId(): int { return $this->id; }
    public function getTitle(): string { return $this->title; }
    public function getContent(): string { return $this->content; }
    public function getSlug(): string { return $this->slug; }
    public function getCreatedAt(): \DateTime { return $this->createdAt; }
    public function getUpdatedAt(): ?\DateTime { return $this->updatedAt; }

    // Setters
    public function setTitle(string $title): void { 
        $this->title = $title; 
        $this->touch();
    }
    
    public function setContent(string $content): void { 
        $this->content = $content; 
        $this->touch();
    }

    private function touch(): void {
        $this->updatedAt = new \DateTime();
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'content' => $this->content,
            'slug' => $this->slug,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }
}
```

## 🗄️ Paso 3: Crear Migración

```bash
php phast make:migration create_blog_posts_table
```

Edita la migración creada:

```php
<?php
use Phinx\Migration\AbstractMigration;

class CreateBlogPostsTable extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('blog_posts');
        $table->addColumn('title', 'string', ['limit' => 255])
              ->addColumn('content', 'text')
              ->addColumn('slug', 'string', ['limit' => 255])
              ->addColumn('created_at', 'datetime')
              ->addColumn('updated_at', 'datetime', ['null' => true])
              ->addIndex(['slug'], ['unique' => true])
              ->create();
    }
}
```

Ejecutar migración:

```bash
php phast migrate
```

## 🎮 Paso 4: Implementar el Controlador

El controlador ya viene con métodos CRUD básicos. Puedes personalizarlo según tus necesidades.

## 🛣️ Paso 5: Registrar las Rutas

Las rutas se generan automáticamente en `routes.php`. Puedes verificarlas:

```bash
php phast routes:list
```

## 🧪 Paso 6: Probar la API

### Iniciar servidor

```bash
php phast serve
```

### Crear un post

```bash
curl -X POST http://localhost:8000/blog \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Mi primer post",
    "content": "Este es el contenido de mi primer post con Phast",
    "slug": "mi-primer-post"
  }'
```

### Obtener todos los posts

```bash
curl http://localhost:8000/blog
```

### Obtener un post específico

```bash
curl http://localhost:8000/blog/1
```

## 🔧 Paso 7: Personalizar el Servicio

Edita `app/Modules/Blog/Services/BlogService.php` para agregar lógica de negocio específica:

```php
public function createPost(array $data): Blog
{
    // Validar datos
    $this->validatePostData($data);
    
    // Generar slug automáticamente si no se proporciona
    if (empty($data['slug'])) {
        $data['slug'] = $this->generateSlug($data['title']);
    }
    
    // Crear entidad
    $blog = new Blog(
        $data['title'],
        $data['content'],
        $data['slug']
    );
    
    // Guardar en repositorio
    return $this->blogRepository->save($blog);
}

private function generateSlug(string $title): string
{
    return strtolower(str_replace(' ', '-', $title));
}
```

## 📚 Próximos Pasos

¡Felicidades! Ya tienes tu primera aplicación funcionando. Ahora puedes:

1. **Aprender más sobre [[Módulos]]** - Organización avanzada
2. **Explorar [[Middleware]]** - Interceptores de peticiones  
3. **Configurar [[Testing]]** - Pruebas automatizadas
4. **Implementar [[Validación]]** - Validación de datos
5. **Usar [[CLI]]** - Comandos personalizados

## 🎓 Conceptos Aprendidos

- ✅ Creación de módulos con [[Comandos CLI]]
- ✅ Estructura de [[Clean Architecture]]
- ✅ [[Controladores]] REST
- ✅ [[Entidades]] de dominio
- ✅ [[Servicios]] de aplicación
- ✅ [[Repositorios]] de datos
- ✅ [[Rutas]] automáticas
- ✅ [[Migraciones]] de base de datos

---

#tutorial #primeros-pasos #blog #crud #api-rest #phast

[[Instalación]] | [[README]] | [[Módulos]]
