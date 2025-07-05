# Comandos CLI

> Sistema completo de comandos de consola para automatizar el desarrollo

## 🚀 Introducción

Phast incluye un potente sistema CLI basado en Symfony Console que automatiza tareas comunes de desarrollo, desde la creación de módulos hasta la gestión de base de datos.

## 📋 Comandos Disponibles

### Ver todos los comandos

```bash
php phast list
```

## 🏗️ Comandos de Módulos

### Crear Módulo Completo

```bash
php phast make:module <nombre>
```

**Ejemplo:**

```bash
php phast make:module Blog
```

**Genera:**

-  📁 Estructura completa de carpetas
-  🎮 `BlogController.php`
-  🏛️ `Blog.php` (Entity)
-  🗄️ `BlogRepository.php`
-  ⚙️ `BlogService.php`
-  💎 `BlogId.php` (Value Object)
-  🔧 `BlogServiceProvider.php`
-  🛣️ `routes.php`
-  📖 `README.md`

### Crear Controlador

```bash
php phast make:controller <módulo> <nombre>
```

**Ejemplo:**

```bash
php phast make:controller Blog PostController
```

## 🔧 Generadores Individuales

### Entidades

```bash
php phast make:entity <nombre> --module=<módulo>
```

**Ejemplos:**

```bash
php phast make:entity Author --module=Blog
php phast make:entity Product --module=Ecommerce
php phast make:entity Category --module=Ecommerce
```

### Repositorios

```bash
php phast make:repository <nombre> --module=<módulo> [--entity=<entidad>]
```

**Ejemplos:**

```bash
php phast make:repository AuthorRepository --module=Blog --entity=Author
php phast make:repository ProductRepository --module=Ecommerce
```

### Servicios

```bash
php phast make:service <nombre> --module=<módulo> [--repository=<repositorio>]
```

**Ejemplos:**

```bash
php phast make:service EmailService --module=Blog --repository=EmailRepository
php phast make:service PaymentService --module=Ecommerce
php phast make:service SearchService --module=Blog
```

### Value Objects

```bash
php phast make:valueobject <nombre> --module=<módulo>
```

**Ejemplos:**

```bash
php phast make:valueobject EmailAddress --module=User
php phast make:valueobject Money --module=Ecommerce
php phast make:valueobject BlogSlug --module=Blog
```

### Service Providers

```bash
php phast make:provider <nombre> --module=<módulo>
```

**Ejemplo:**

```bash
php phast make:provider CustomServiceProvider --module=Blog
```

## 🗑️ Comandos de Eliminación

### Eliminar Entidad

```bash
php phast delete:entity <nombre> --module=<módulo> [--force]
```

**Ejemplo:**

```bash
php phast delete:entity Author --module=Blog
```

### Eliminar Módulo Completo

```bash
php phast delete:module <nombre> [--force]
```

**Ejemplo:**

```bash
# Con confirmación
php phast delete:module Blog

# Sin confirmación
php phast delete:module Blog --force
```

### Eliminar Componente Específico

```bash
php phast delete:component <tipo> <nombre> --module=<módulo> [--force]
```

**Tipos válidos:**

-  `controller`
-  `service`
-  `repository`
-  `entity`
-  `valueobject`
-  `provider`

**Ejemplos:**

```bash
php phast delete:component service EmailService --module=Blog
php phast delete:component entity Product --module=Ecommerce
php phast delete:component controller AdminController --module=User
```

## 🌐 Comandos de Servidor

### Servidor de Desarrollo

```bash
php phast serve [--host=<host>] [--port=<puerto>]
```

**Ejemplos:**

```bash
# Servidor por defecto (localhost:8000)
php phast serve

# Host y puerto específicos
php phast serve --host=0.0.0.0 --port=8080

# Solo puerto
php phast serve --port=3000
```

## 🛣️ Comandos de Rutas

### Listar Todas las Rutas

```bash
php phast routes:list
```

**Salida de ejemplo:**

```
+--------+------------------+------------------+-------------------+
| Method | URI              | Name             | Controller        |
+--------+------------------+------------------+-------------------+
| GET    | /                | home             | HomeController    |
| GET    | /blog            | blog.index       | BlogController    |
| POST   | /blog            | blog.store       | BlogController    |
| GET    | /blog/{id}       | blog.show        | BlogController    |
| PUT    | /blog/{id}       | blog.update      | BlogController    |
| DELETE | /blog/{id}       | blog.destroy     | BlogController    |
+--------+------------------+------------------+-------------------+
```

## 📊 Ejemplos de Flujo de Trabajo

### Crear un Sistema de Blog Completo

```bash
# 1. Crear módulo base
php phast make:module Blog

# 2. Agregar entidades adicionales
php phast make:entity Author --module=Blog
php phast make:entity Category --module=Blog
php phast make:entity Tag --module=Blog

# 3. Crear servicios especializados
php phast make:service AuthorService --module=Blog --repository=AuthorRepository
php phast make:service CategoryService --module=Blog
php phast make:service SearchService --module=Blog

# 4. Crear value objects
php phast make:valueobject BlogSlug --module=Blog
php phast make:valueobject AuthorEmail --module=Blog

# 5. Verificar rutas
php phast routes:list

# 6. Servir aplicación
php phast serve --port=8080
```

### Crear un E-commerce

```bash
# Módulo principal
php phast make:module Ecommerce

# Entidades del dominio
php phast make:entity Product --module=Ecommerce
php phast make:entity Category --module=Ecommerce
php phast make:entity Order --module=Ecommerce
php phast make:entity Customer --module=Ecommerce

# Servicios especializados
php phast make:service ProductService --module=Ecommerce
php phast make:service OrderService --module=Ecommerce
php phast make:service PaymentService --module=Ecommerce
php phast make:service InventoryService --module=Ecommerce

# Value objects
php phast make:valueobject Money --module=Ecommerce
php phast make:valueobject ProductSku --module=Ecommerce
php phast make:valueobject CustomerEmail --module=Ecommerce
```

### Limpieza y Mantenimiento

```bash
# Eliminar componentes específicos
php phast delete:component service OldService --module=Blog --force

# Eliminar entidades no utilizadas
php phast delete:entity TempEntity --module=Test

# Eliminar módulo de prueba completo
php phast delete:module Test --force
```

## ⚙️ Opciones Globales

### Opciones Disponibles

-  `--force` o `-f`: Ejecutar sin confirmación
-  `--module` o `-m`: Especificar módulo
-  `--entity` o `-e`: Especificar entidad relacionada
-  `--repository` o `-r`: Especificar repositorio relacionado
-  `--host`: Host del servidor
-  `--port`: Puerto del servidor

### Ejemplos con Opciones

```bash
# Crear con opciones específicas
php phast make:repository UserRepository -m=Auth -e=User

# Eliminar sin confirmación
php phast delete:module OldModule -f

# Servidor en IP específica
php phast serve --host=192.168.1.100 --port=8000
```

## 🎯 Tips y Trucos

### Nombres Inteligentes

El CLI es inteligente con los nombres:

```bash
# Estos comandos son equivalentes
php phast make:entity User --module=Auth
php phast make:entity user --module=auth
php phast make:entity USER --module=AUTH

# Automáticamente añade sufijos
php phast make:service Email --module=Blog  # Crea EmailService
php phast make:repository User --module=Auth  # Crea UserRepository
```

### Validaciones

```bash
# Error: Módulo no existe
php phast make:entity Product --module=NonExistent
# [ERROR] Module 'NonExistent' does not exist!

# Error: Componente ya existe
php phast make:entity Blog --module=Blog
# [ERROR] Entity 'Blog' already exists in module 'Blog'!
```

### Confirmaciones de Seguridad

```bash
# Eliminar con confirmación
php phast delete:module Blog
# Are you sure you want to delete the module 'Blog'? (yes/no) [no]:

# Saltar confirmación
php phast delete:module Blog --force
# [OK] Module 'Blog' deleted successfully!
```

## 🔮 Comandos Futuros (En Desarrollo)

### Base de Datos

```bash
php phast migrate                    # Ejecutar migraciones
php phast migrate:rollback          # Rollback migraciones
php phast migrate:status            # Estado migraciones
php phast make:migration <nombre>   # Crear migración
```

### Testing

```bash
php phast test                      # Ejecutar tests
php phast make:test <nombre>        # Crear test
```

### Cache y Optimización

```bash
php phast cache:clear              # Limpiar caché
php phast optimize                 # Optimizar aplicación
```

---

#cli #comandos #automatización #generadores #scaffolding #phast

[[README]] | [[Módulos]] | [[Generadores de Código]]
