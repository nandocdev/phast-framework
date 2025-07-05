# Phast CLI Commands Documentation

## Comandos Implementados ✅

### Módulos

-  ✅ `php phast make:module <name>` - Crear estructura completa de módulo
-  ✅ `php phast make:controller <module> <name>` - Crear controlador en módulo

### Generadores Individuales

-  ✅ `php phast make:entity <name> --module=<module>` - Crear entidad
-  ✅ `php phast make:repository <name> --module=<module> [--entity=<entity>]` - Crear repositorio
-  ✅ `php phast make:service <name> --module=<module> [--repository=<repo>]` - Crear servicio
-  ✅ `php phast make:valueobject <name> --module=<module>` - Crear value object
-  ✅ `php phast make:provider <name> --module=<module>` - Crear service provider

### Comandos de Eliminación

-  ✅ `php phast delete:entity <name> --module=<module> [--force]` - Eliminar entidad
-  ✅ `php phast delete:module <name> [--force]` - Eliminar módulo completo
-  ✅ `php phast delete:component <type> <name> --module=<module> [--force]` - Eliminar componente específico

### Utilidades

-  ✅ `php phast serve [--host=localhost] [--port=8000]` - Servidor de desarrollo
-  ✅ `php phast routes:list` - Listar todas las rutas registradasommands Documentation

## Comandos Implementados ✅

### Módulos

-  `php phast make:module <name>` - Crear estructura completa de módulo
-  `php phast make:controller <module> <name>` - Crear controlador en módulo

### Utilidades

-  `php phast serve [--host=localhost] [--port=8000]` - Servidor de desarrollo
-  `php phast routes:list` - Listar todas las rutas registradas

## Comandos Sugeridos para Implementar 🚀

### Generadores de Código

```bash
# Módulos
php phast make:entity <module> <name>         # Crear entidad
php phast make:repository <module> <name>     # Crear repositorio
php phast make:service <module> <name>        # Crear servicio
php phast make:provider <module> <name>       # Crear service provider
php phast make:valueobject <module> <name>    # Crear value object

# Infraestructura
php phast make:middleware <name>              # Crear middleware
php phast make:command <name>                 # Crear comando CLI
php phast make:migration <name>               # Crear migración
php phast make:seeder <name>                  # Crear seeder
php phast make:factory <name>                 # Crear factory para testing

# Testing
php phast make:test <module> <name>           # Crear test
```

### Base de Datos

```bash
php phast migrate                             # Ejecutar migraciones
php phast migrate:rollback                    # Rollback migraciones
php phast migrate:status                      # Estado migraciones
php phast db:seed                             # Ejecutar seeders
php phast db:reset                            # Reset y recargar DB
```

### Utilidades

```bash
php phast cache:clear                         # Limpiar caché
php phast config:cache                        # Cachear configuración
php phast key:generate                        # Generar APP_KEY
php phast env:copy                            # Copiar .env.example a .env
php phast optimize                            # Optimizar aplicación
```

### Testing

```bash
php phast test                                # Ejecutar tests
php phast test:coverage                       # Tests con coverage
php phast test:unit                           # Solo tests unitarios
php phast test:integration                    # Solo tests integración
```

### Mantenimiento

```bash
php phast module:list                         # Listar módulos
php phast module:enable <name>                # Habilitar módulo
php phast module:disable <name>               # Deshabilitar módulo
php phast health:check                        # Health check completo
php phast debug:routes                        # Debug rutas detallado
```

## Estructura Generada

Cuando usas `php phast make:module Blog`, se crea:

```
app/Modules/Blog/
├── Controllers/
├── Models/
│   ├── Entities/
│   ├── Repositories/
│   └── ValueObjects/
├── Providers/
│   └── BlogServiceProvider.php
├── Services/
├── routes.php
└── README.md
```

## Ejemplos de Uso

### Crear un módulo completo de blog

```bash
php phast make:module Blog
php phast make:controller Blog PostController
php phast make:entity Blog Post
php phast make:repository Blog PostRepository
php phast make:service Blog PostService
```

### Verificar rutas y servir aplicación

```bash
php phast routes:list
php phast serve --port=8080
```

## Ventajas del Sistema CLI

1. **Productividad**: Generación rápida de código boilerplate
2. **Consistencia**: Estructura uniforme en todos los módulos
3. **Clean Architecture**: Separación clara de responsabilidades
4. **SOLID Principles**: Código generado siguiendo mejores prácticas
5. **Extensibilidad**: Fácil agregar nuevos comandos
6. **Convention over Configuration**: Convenciones claras y predecibles

## Próximos Pasos

1. Implementar comandos de Entity y Repository
2. Agregar sistema de migraciones
3. Comandos de testing automatizado
4. Cache y optimización
5. Herramientas de debug y profiling
