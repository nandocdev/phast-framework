<?php
/**
 * @package     phast/core
 * @subpackage  Console/Commands/Route
 * @file        RouteListCommand
 * @author      Fernando Castillo <fdocst@gmail.com>
 * @date        2025-07-04
 * @version     1.0.0
 * @description Command to list all registered routes
 */

declare(strict_types=1);

namespace Phast\Core\Console\Commands\Route;

use Phast\Core\Console\BaseCommand;
use Phast\Core\Routing\Router;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\Table;

#[AsCommand(
   name: 'routes:list',
   description: 'List all registered routes'
)]
class RouteListCommand extends BaseCommand {
   private Router $router;

   public function __construct(Router $router) {
      parent::__construct();
      $this->router = $router;
   }

   protected function execute(InputInterface $input, OutputInterface $output): int {
      // Create a temporary bootstrap to load routes
      $bootstrap = new \Phast\Core\Application\Bootstrap();
      $app = $bootstrap; // Variable expected by routes.php

      // Load routes
      require_once $this->basePath . '/app/routes.php';

      $routes = $bootstrap->getRouter()->getRoutes();

      if (empty($routes)) {
         $this->io->warning('No routes registered!');
         return self::SUCCESS;
      }

      $this->io->title('Registered Routes');

      $table = new Table($output);
      $table->setHeaders(['Method', 'URI', 'Handler', 'Middleware', 'Name']);

      foreach ($routes as $route) {
         $handler = $this->formatHandler($route['handler']);
         $middleware = $this->formatMiddleware($route['middleware'] ?? []);
         $name = $route['name'] ?? '';

         $table->addRow([
            $route['method'],
            $route['uri'],
            $handler,
            $middleware,
            $name
         ]);
      }

      $table->render();

      $this->io->note("Total routes: " . count($routes));

      return self::SUCCESS;
   }

   private function formatHandler($handler): string {
      if (is_string($handler)) {
         return $handler;
      }

      if (is_callable($handler)) {
         return 'Closure';
      }

      return 'Unknown';
   }

   private function formatMiddleware(array $middleware): string {
      if (empty($middleware)) {
         return '';
      }

      $formatted = array_map(function ($item) {
         if (is_string($item)) {
            $parts = explode('\\', $item);
            return end($parts);
         }
         return 'Closure';
      }, $middleware);

      return implode(', ', $formatted);
   }
}
