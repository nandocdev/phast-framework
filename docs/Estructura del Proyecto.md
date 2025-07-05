# Estructura del Proyecto

> Organización de archivos y carpetas del framework Phast

## 📁 Vista General

```
phast-framework/
├── 📂 app/                     # Código de aplicación
│   ├── 📂 Modules/            # Módulos de la aplicación
│   ├── 📂 Http/               # HTTP específico
│   └── 📂 Providers/          # Service providers globales
├── 📂 config/                  # Archivos de configuración
├── 📂 core/                    # Núcleo del framework
│   ├── 📂 Application/        # Bootstrap y aplicación
│   ├── 📂 Console/            # Comandos CLI
│   ├── 📂 Http/               # Manejo HTTP
│   └── 📂 Database/           # Abstracción de BD
├── 📂 docs/                    # Documentación
├── 📂 migrations/              # Migraciones de BD
├── 📂 public/                  # Punto de entrada web
├── 📂 resources/               # Recursos no compilados
├── 📂 routes/                  # Archivos de rutas globales
├── 📂 storage/                 # Archivos generados
├── 📂 vendor/                  # Dependencias de Composer
├── 📄 .env                     # Variables de entorno
├── 📄 composer.json            # Dependencias PHP
└── 📄 phast                    # CLI ejecutable
```

## 🏗️ Directorio `app/`

### Código de Aplicación

```
app/
├── Modules/                    # [[Módulos]] de la aplicación
│   └── Blog/                  # Ejemplo: módulo Blog
│       ├── Controllers/       # [[Controladores]]
│       ├── Models/
│       │   ├── Entities/      # [[Entidades]]
│       │   ├── Repositories/  # [[Repositorios]]
│       │   └── ValueObjects/  # [[Value Objects]]
│       ├── Services/          # [[Servicios]]
│       ├── Providers/         # Service providers del módulo
│       └── routes.php         # [[Rutas]] del módulo
├── Http/
│   ├── Middleware/            # [[Middleware]] global
│   └── Kernel.php             # Kernel HTTP
└── Providers/
    ├── AppServiceProvider.php # Provider principal
    └── RouteServiceProvider.php # Provider de rutas
```

## ⚙️ Directorio `config/`

### Archivos de Configuración

```
config/
├── app.php                    # Configuración general
├── database.php               # [[Configuración]] de BD
├── cache.php                  # Configuración de caché
├── logging.php                # Configuración de logs
└── services.php               # [[Inyección de Dependencias]]
```

## 🔧 Directorio `core/`

### Núcleo del Framework

```
core/
├── Application/
│   ├── Bootstrap.php          # Inicialización
│   ├── Container.php          # DI Container
│   └── Kernel.php             # Kernel base
├── Console/
│   ├── Application.php        # CLI Application
│   ├── BaseCommand.php        # Comando base
│   ├── Commands/              # [[Comandos CLI]]
│   └── stubs/                 # Plantillas de código
├── Http/
│   ├── Controller.php         # Controlador base
│   ├── Request.php            # Objeto Request
│   ├── Response.php           # Objeto Response
│   ├── Router.php             # [[Rutas|Router]]
│   └── Middleware/            # Middleware del core
└── Database/
    ├── Connection.php         # Conexión BD
    ├── QueryBuilder.php       # Constructor consultas
    └── Migration.php          # Base para migraciones
```

## 📚 Directorio `docs/`

### Documentación

```
docs/
├── README.md                  # Índice principal
├── Instalación.md             # Guía de instalación
├── Primeros Pasos.md          # Tutorial inicial
├── Módulos.md                 # Sistema modular
├── Comandos CLI.md            # Comandos disponibles
└── API/                       # Documentación API
```

## 🗄️ Directorio `migrations/`

### Control de Versiones de BD

```
migrations/
├── 20250704000001_create_users_table.php
├── 20250704000002_create_blog_posts_table.php
└── 20250704000003_add_indexes_to_posts.php
```

## 🌐 Directorio `public/`

### Punto de Entrada Web

```
public/
├── index.php                  # Front controller
├── .htaccess                  # Configuración Apache
├── assets/                    # Assets estáticos
│   ├── css/
│   ├── js/
│   └── images/
└── uploads/                   # Archivos subidos
```

## 📦 Directorio `storage/`

### Archivos Generados

```
storage/
├── logs/                      # [[Logging|Logs]] de aplicación
│   ├── app.log
│   └── error.log
├── cache/                     # [[Cache]] de aplicación
├── sessions/                  # Sesiones (si se usa file)
└── framework/
    ├── cache/                 # Cache del framework
    └── views/                 # Vistas compiladas
```

## 🛣️ Directorio `routes/`

### Rutas Globales

```
routes/
├── web.php                    # Rutas web
├── api.php                    # Rutas API
└── console.php                # Rutas de comandos
```

## 🏷️ Convenciones de Nomenclatura

### Archivos y Clases

-  **Controladores**: `BlogController.php`
-  **Entidades**: `Blog.php`, `User.php`
-  **Repositorios**: `BlogRepository.php`
-  **Servicios**: `BlogService.php`
-  **Value Objects**: `BlogId.php`, `Email.php`
-  **Providers**: `BlogServiceProvider.php`
-  **Middleware**: `AuthMiddleware.php`
-  **Comandos**: `MakeBlogCommand.php`

### Namespaces

```php
// Módulos
Phast\App\Modules\Blog\Controllers\BlogController

// Core
Phast\Core\Http\Controller
Phast\Core\Console\BaseCommand

// Configuración
Phast\Config\DatabaseConfig
```

### Directorios

-  **PascalCase**: Nombres de clases y archivos
-  **kebab-case**: URLs y rutas
-  **snake_case**: Base de datos
-  **camelCase**: Variables y métodos

## 🔄 Flujo de Ejecución

### Request Web

1. `public/index.php` → Bootstrap
2. `core/Application/Bootstrap.php` → Inicialización
3. `app/Http/Kernel.php` → Kernel HTTP
4. `core/Http/Router.php` → Enrutamiento
5. `app/Modules/*/Controllers/*` → Controlador
6. `app/Modules/*/Services/*` → Lógica de negocio
7. `app/Modules/*/Repositories/*` → Acceso a datos
8. Response → Cliente

### Command CLI

1. `phast` → CLI Bootstrap
2. `core/Console/Application.php` → CLI Application
3. `core/Console/Commands/*` → Comando específico
4. Ejecución → Output

## 📋 Buenas Prácticas

### Organización de Módulos

-  Un módulo por dominio de negocio
-  Cada módulo es independiente
-  Comunicación entre módulos vía eventos o servicios compartidos

### Separación de Responsabilidades

-  **Controllers**: Solo reciben requests y devuelven responses
-  **Services**: Lógica de negocio
-  **Repositories**: Acceso a datos
-  **Entities**: Modelos de dominio
-  **Value Objects**: Valores inmutables

### Archivos de Configuración

-  Un archivo por tipo de configuración
-  Usar variables de entorno para valores sensibles
-  Documentar todas las opciones

---

#estructura #organización #convenciones #arquitectura #phast

[[README]] | [[Instalación]] | [[Clean Architecture]]
