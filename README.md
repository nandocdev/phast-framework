<div align="center">
  <img src="https://raw.githubusercontent.com/nandocdev/phast-framework/main/public/assets/images/phast_icon1.svg" alt="Phast Framework Logo" width="200">
  <h1>Phast Framework</h1>
  <p><strong>Un framework PHP diseñado para la velocidad, la estructura y la elegancia.</strong></p>
  <p>Construido sobre principios de Arquitectura Limpia, SOLID y DDD para ofrecer una experiencia de desarrollo excepcional.</p>
  
  <p>
    <a href="#"><img src="https://img.shields.io/badge/php-8.2+-blue.svg" alt="PHP Version"></a>
    <a href="#"><img src="https://img.shields.io/badge/license-MIT-green.svg" alt="License"></a>
    <a href="#"><img src="https://img.shields.io/badge/build-passing-brightgreen.svg" alt="Build Status"></a>
    <a href="#"><img src="https://img.shields.io/badge/contributions-welcome-orange.svg" alt="Contributions Welcome"></a>
  </p>
</div>

---

**Phast** no es solo otro framework PHP. Es una filosofía de desarrollo que prioriza la claridad, la mantenibilidad y la productividad. Diseñado para desarrolladores que aprecian el código limpio y una estructura modular, Phast te permite construir aplicaciones robustas y escalables, desde APIs RESTful hasta aplicaciones web completas.

Nuestra misión es proporcionar una base sólida y bien organizada que te libere para centrarte en lo que realmente importa: la lógica de negocio de tu aplicación.

## Principios Fundamentales

Phast se construye sobre una base de principios de ingeniería de software probados en la industria:

-  🏛️ **Arquitectura Limpia**: Una estricta separación de capas (Dominio, Aplicación, Infraestructura) que garantiza un bajo acoplamiento y una alta cohesión.
-  SOLID: Cada componente del framework y de tu aplicación está diseñado siguiendo los cinco principios SOLID.
-  🧩 **Modularidad**: Organiza tu aplicación en módulos de dominio autocontenidos. Cada módulo tiene sus propios controladores, servicios, entidades y rutas, fomentando la reutilización y el desarrollo en equipo.
-  🚀 **Experiencia de Desarrollador (DX)**: Un potente sistema de **comandos CLI** para generar módulos, controladores, entidades, servicios y más, automatizando las tareas repetitivas.

## Características Principales

| Característica            | Descripción                                                                 |
| ------------------------- | --------------------------------------------------------------------------- |
| CLI Robusta               | Genera y elimina módulos y componentes (`make:module`, `delete:entity`...). |
| Inyección de Dependencias | Contenedor DI para una gestión automática de dependencias.                  |
| Routing Flexible          | Sistema de rutas basado en `FastRoute` con grupos, middleware y nombres.    |
| ORM y Base de Datos       | Integración con `Doctrine` y migraciones con `Phinx`.                       |
| Capa de Servicio          | Lógica de negocio encapsulada y reutilizable.                               |
| Patrón Repositorio        | Abstracción completa de la capa de acceso a datos.                          |
| Middlewares               | Pipeline de procesamiento de peticiones HTTP (CORS, Auth, Rate Limiting).   |
| Motor de Vistas           | Sistema de plantillas potente y extensible con `League/Plates`.             |
| Validación                | Reglas de validación integradas para datos de entrada.                      |
| Logging                   | Sistema de logs flexible con `Monolog`.                                     |

## Primeros Pasos

### Requisitos

-  PHP 8.2+
-  Composer

### Instalación en 5 Minutos

1. **Crear el proyecto**

   ```bash
   composer create-project your-username/phast-project
   cd phast-project
   ```

2. **Configurar el entorno**

   ```bash
   cp .env.example .env
   # Edita .env con la configuración de tu base de datos
   ```

3. **Ejecutar las migraciones**

   ```bash
   composer migrate
   ```

4. **Iniciar el servidor**
   ```bash
   composer serve
   ```

¡Tu aplicación Phast estará disponible en `http://localhost:8000`!

## La Experiencia de Desarrollo Phast

Creemos que un desarrollador feliz es un desarrollador productivo. Por eso, hemos puesto un gran énfasis en la **Experiencia de Desarrollador (DX)**.

### Generación de Código con la CLI

Olvídate de crear archivos y estructuras de directorios manualmente. Usa nuestros comandos para acelerar tu flujo de trabajo.

**Crear un módulo completo con un solo comando:**

```bash
php phast make:module Blog
```

Este comando generará toda la estructura del módulo `Blog`, incluyendo:

-  `app/Modules/Blog/`
-  `Controllers/BlogController.php`
-  `Services/BlogService.php`
-  `Models/Entities/BlogEntity.php`
-  `Models/Repositories/BlogRepository.php`
-  `Providers/BlogServiceProvider.php`
-  `routes.php`

**Generar componentes individuales:**

```bash
# Crear un nuevo controlador
php phast make:controller PostController --module=Blog

# Crear una nueva entidad y su repositorio
php phast make:entity Post --module=Blog
php phast make:repository PostRepository --module=Blog
```

### Arquitectura Modular

La estructura de `app/Modules` te permite encapsular la lógica de cada dominio de tu aplicación. Esto no solo mantiene el código organizado, sino que también facilita:

-  **Navegación**: Encuentra rápidamente el código relacionado con una funcionalidad.
-  **Reutilización**: Mueve módulos entre proyectos con mínimas modificaciones.
-  **Colaboración**: Los equipos pueden trabajar en diferentes módulos de forma independiente.

```text
app/
└── Modules/
    ├── Users/
    │   ├── Controllers/
    │   ├── Models/
    │   ├── Services/
    │   └── routes.php
    └── Orders/
        ├── Controllers/
        ├── Models/
        ├── Services/
        └── routes.php
```

## Documentación Completa

Para una guía detallada sobre cada componente del framework, desde la arquitectura hasta tutoriales prácticos, visita nuestra **documentación oficial**.

➡️ **[Leer la documentación completa](./docs/README.md)**

## Contribuciones

Las contribuciones son bienvenidas. Si quieres mejorar Phast, por favor, sigue estos pasos:

1. Haz un Fork del proyecto.
2. Crea una nueva rama (`git checkout -b feature/nueva-caracteristica`).
3. Realiza tus cambios y haz commit (`git commit -m 'feat: Agrega nueva característica'`).
4. Haz push a tu rama (`git push origin feature/nueva-caracteristica`).
5. Abre un Pull Request.

## Licencia

Phast Framework es un software de código abierto licenciado bajo la [Licencia MIT](LICENSE).
