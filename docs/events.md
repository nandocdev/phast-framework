# Event System Documentation

El framework Phast incluye un sistema de eventos robusto que permite desacoplar componentes y manejar eventos de manera eficiente.

## Arquitectura

### Componentes Principales

-  **EventInterface**: Define la interfaz para eventos
-  **Event**: Clase base abstracta para eventos
-  **SimpleEvent**: Implementación simple para eventos genéricos
-  **EventDispatcherInterface**: Interfaz para el despachador de eventos
-  **EventDispatcher**: Implementación del despachador de eventos
-  **ListenerInterface**: Interfaz para escuchadores de eventos

### Estructura de Archivos

```
core/Events/
├── EventInterface.php
├── Event.php
├── SimpleEvent.php
├── EventDispatcherInterface.php
├── EventDispatcher.php
└── ListenerInterface.php
```

## Uso Básico

### 1. Crear un Evento

```php
<?php

use Phast\Core\Events\Event;

class UserRegistered extends Event
{
    public function getName(): string
    {
        return 'user.registered';
    }

    public function getUser(): array
    {
        return $this->get('user');
    }
}

// Crear el evento
$event = new UserRegistered(['user' => $userData]);
```

### 2. Usar SimpleEvent para Eventos Simples

```php
<?php

use Phast\Core\Events\SimpleEvent;

// Crear un evento simple
$event = new SimpleEvent('notification.sent', [
    'user_id' => 123,
    'message' => 'Welcome to our platform!'
]);
```

### 3. Crear un Listener

```php
<?php

use Phast\Core\Events\ListenerInterface;
use Phast\Core\Events\EventInterface;

class EmailNotificationListener implements ListenerInterface
{
    public function handle(EventInterface $event): void
    {
        if ($event->getName() === 'user.registered') {
            $user = $event->get('user');
            // Enviar email de bienvenida
            $this->sendWelcomeEmail($user);
        }
    }

    public function getPriority(): int
    {
        return 10; // Mayor prioridad = se ejecuta primero
    }

    private function sendWelcomeEmail(array $user): void
    {
        // Lógica para enviar email
    }
}
```

### 4. Registrar Listeners

```php
<?php

// En un service provider
public function boot(ContainerInterface $container): void
{
    $dispatcher = $container->get(EventDispatcherInterface::class);

    // Registrar listener con instancia
    $emailListener = new EmailNotificationListener();
    $dispatcher->listen('user.registered', $emailListener);

    // Registrar listener con callback
    $dispatcher->addListener('user.updated', function($event) {
        // Lógica del listener
    }, 5);
}
```

### 5. Disparar Eventos

```php
<?php

// Usando el helper global
$event = new UserRegistered(['user' => $userData]);
event($event);

// Usando el container
$dispatcher = $container->get(EventDispatcherInterface::class);
$dispatcher->dispatch($event);
```

## Ejemplos Avanzados

### Event Payload y Métodos Útiles

```php
<?php

class OrderCreated extends Event
{
    public function getName(): string
    {
        return 'order.created';
    }

    public function getOrder(): array
    {
        return $this->get('order');
    }

    public function getCustomer(): array
    {
        return $this->get('customer');
    }

    public function getTotal(): float
    {
        return $this->get('total', 0.0);
    }
}

// Uso
$event = new OrderCreated([
    'order' => $orderData,
    'customer' => $customerData,
    'total' => 99.99
]);

event($event);
```

### Listeners con Diferentes Prioridades

```php
<?php

// Listener de alta prioridad (se ejecuta primero)
class ValidateOrderListener implements ListenerInterface
{
    public function getPriority(): int
    {
        return 100;
    }

    public function handle(EventInterface $event): void
    {
        // Validar orden antes de procesarla
        if (!$this->isValid($event->get('order'))) {
            $event->stopPropagation(); // Detener otros listeners
        }
    }
}

// Listener de prioridad normal
class ProcessOrderListener implements ListenerInterface
{
    public function getPriority(): int
    {
        return 10;
    }

    public function handle(EventInterface $event): void
    {
        // Procesar la orden
    }
}

// Listener de baja prioridad (se ejecuta al final)
class SendOrderConfirmationListener implements ListenerInterface
{
    public function getPriority(): int
    {
        return 1;
    }

    public function handle(EventInterface $event): void
    {
        // Enviar confirmación
    }
}
```

### Detener Propagación de Eventos

```php
<?php

class SecurityListener implements ListenerInterface
{
    public function handle(EventInterface $event): void
    {
        if ($this->isSecurityThreat($event)) {
            $event->stopPropagation();
            $this->logSecurityEvent($event);
        }
    }

    public function getPriority(): int
    {
        return 1000; // Muy alta prioridad
    }
}
```

## Integración con Módulos

### En UserServiceProvider

```php
<?php

class UserServiceProvider implements ServiceProviderInterface
{
    public function boot(ContainerInterface $container): void
    {
        $dispatcher = $container->get(EventDispatcherInterface::class);

        // Registrar listeners específicos del módulo Users
        $dispatcher->listen('user.created', new SendWelcomeEmailListener());
        $dispatcher->listen('user.created', new LogUserActivityListener());
        $dispatcher->listen('user.updated', new LogUserActivityListener());
        $dispatcher->listen('user.deleted', new CleanupUserDataListener());
    }
}
```

## Helper Functions

### Función Global `event()`

```php
<?php

// Disparar un evento usando el helper global
$result = event(new UserRegistered(['user' => $userData]));

// El evento se devuelve para poder inspeccionar su estado
if ($result->isPropagationStopped()) {
    // El evento fue detenido por algún listener
}
```

## Configuración Avanzada

### Logging de Eventos

El EventDispatcher registra automáticamente eventos en el log cuando se disparan y cuando se registran listeners:

```
[2025-07-05 00:22:19] phast.DEBUG: Event listener registered {"event":"user.created","priority":10}
[2025-07-05 00:22:19] phast.INFO: Event dispatched {"event":"user.created","listeners_count":2}
```

### Performance

-  Los listeners se ordenan automáticamente por prioridad
-  Los eventos con propagación detenida no ejecutan listeners restantes
-  El sistema es optimizado para manejar cientos de eventos por request

## Best Practices

1. **Nombres de Eventos**: Usar formato `module.action` (ej: `user.created`, `order.shipped`)
2. **Listeners Específicos**: Crear listeners específicos para cada acción
3. **Prioridades**: Usar prioridades lógicas (validación=100, procesamiento=10, notificación=1)
4. **Payload Limitado**: Incluir solo datos necesarios en el payload
5. **Error Handling**: Los listeners deben manejar sus propios errores
6. **Testing**: Testear eventos y listeners de manera independiente

## Troubleshooting

### Error: "Argument #2 ($listener) must be of type ListenerInterface"

```php
// ❌ Incorrecto
$dispatcher->listen('event.name', 'ClassName');

// ✅ Correcto
$dispatcher->listen('event.name', new ClassName());
```

### Event No Se Dispara

1. Verificar que el listener esté registrado correctamente
2. Revisar logs para mensajes de debug
3. Asegurar que el evento no esté siendo detenido por otro listener

### Performance Issues

1. Revisar número de listeners por evento
2. Optimizar listeners lentos
3. Considerar usar queues para tareas pesadas
