<?php
/**
 * @package     phast/app/Controllers
 * @file        WebController
 * @author      Fernando Castillo <fdocst@gmail.com>
 * @date        2025-07-04
 * @version     1.0.0
 * @description Web controller for view rendering
 */

declare(strict_types=1);

namespace Phast\App\Controllers;

use Phast\Core\Http\Controller;
use Phast\Core\Http\Request;
use Phast\Core\Http\Response;

class WebController extends Controller {
   public function home(Request $request): Response {
      $data = [
         'title' => 'Bienvenido a Phast Framework',
         'description' => 'Un framework PHP moderno y limpio basado en principios de arquitectura limpia y código SOLID.',
         'features' => [
               'Arquitectura Limpia',
               'Inyección de Dependencias',
               'Sistema de Routing',
               'Middlewares',
               'Validación',
               'Motor de Vistas',
               'ORM con Doctrine',
               'Sistema de Logs',
            ],
         'middlewares_count' => 4,
         'stats' => [
            'php_version' => PHP_VERSION,
            'framework_version' => '1.0.0',
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
         ]
      ];

      return $this->view('welcome', $data);
   }

   public function users(Request $request): Response {
      // Simulamos algunos usuarios para la demo
      $users = [
         [
            'id' => 1,
            'name' => 'Juan Pérez',
            'email' => 'juan@example.com',
            'status' => 'active',
            'created_at' => '2025-01-01 10:00:00'
         ],
         [
            'id' => 2,
            'name' => 'María García',
            'email' => 'maria@example.com',
            'status' => 'active',
            'created_at' => '2025-01-02 11:30:00'
         ],
         [
            'id' => 3,
            'name' => 'Carlos López',
            'email' => 'carlos@example.com',
            'status' => 'inactive',
            'created_at' => '2025-01-03 09:15:00'
         ],
      ];

      $data = [
         'title' => 'Gestión de Usuarios',
         'description' => 'Lista de usuarios registrados en el sistema',
         'users' => $users,
         'total_users' => count($users),
         'active_users' => count(array_filter($users, fn($u) => $u['status'] === 'active')),
      ];

      return $this->view('users/index', $data);
   }

   public function notFound(Request $request): Response {
      $data = [
         'title' => 'Página no encontrada - Error 404',
         'description' => 'La página solicitada no existe o ha sido movida',
         'requested_url' => $request->getUri(),
         'method' => $request->getMethod(),
         'timestamp' => date('Y-m-d H:i:s'),
      ];

      return $this->view('errors/404', $data)->setStatusCode(404);
   }
}
