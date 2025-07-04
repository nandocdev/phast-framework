<?php
/**
 * @package     phast/core
 * @subpackage  Application
 * @file        Bootstrap
 * @author      Fernando Castillo <fdocst@gmail.com>
 * @date        2025-07-04 14:41:22
 * @version     1.0.0
 * @description Application bootstrap
 */

declare(strict_types=1);

namespace Phast\Core\Application;

use Phast\Core\Config\Config;
use Phast\Core\Config\ConfigInterface;
use Phast\Core\Http\Request;
use Phast\Core\Http\Response;
use Phast\Core\Routing\Router;
use Phast\Core\Validation\Validator;
use Phast\Core\Validation\ValidatorInterface;
use Psr\Log\LoggerInterface;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class Bootstrap {
   private Container $container;
   private Router $router;

   public function __construct() {
      $this->container = Container::getInstance();
      $this->router = new Router();
      $this->registerCoreServices();
      $this->loadConfiguration();
   }

   public function getContainer(): Container {
      return $this->container;
   }

   public function getRouter(): Router {
      return $this->router;
   }

   public function run(): void {
      try {
         $request = $this->container->get(Request::class);
         $response = $this->router->dispatch($request);
         $response->send();
      } catch (\Throwable $e) {
         $this->handleError($e);
      }
   }

   private function registerCoreServices(): void {
      // Configuration
      $this->container->singleton(ConfigInterface::class, Config::class);

      // HTTP
      $this->container->singleton(Request::class, function () {
         return new Request();
      });

      // Validation
      $this->container->singleton(ValidatorInterface::class, Validator::class);

      // Logger
      $this->container->singleton(LoggerInterface::class, function () {
         $logger = new Logger('phast');
         $logger->pushHandler(new StreamHandler(
            PHAST_BASE_PATH . '/storage/logs/app.log',
            Logger::DEBUG
         ));
         return $logger;
      });

      // Register core middlewares
      $this->registerMiddlewares();

      // Register module service providers
      $this->registerModuleProviders();
   }

   private function registerMiddlewares(): void {
      // Register core middlewares
      $this->container->singleton(\Phast\Core\Http\Middleware\CorsMiddleware::class);
      $this->container->singleton(\Phast\Core\Http\Middleware\AuthMiddleware::class);
      $this->container->singleton(\Phast\Core\Http\Middleware\RateLimitMiddleware::class);
      $this->container->singleton(\Phast\Core\Http\Middleware\LoggingMiddleware::class);
   }

   private function registerModuleProviders(): void {
      // Register Users module
      $this->container->register(new \Phast\App\Modules\Users\Providers\UserServiceProvider());

      // Boot all providers
      $this->container->boot();
   }

   private function loadConfiguration(): void {
      $config = $this->container->get(ConfigInterface::class);

      // Load configuration files
      $configPath = PHAST_BASE_PATH . '/config';
      if (is_dir($configPath)) {
         foreach (glob($configPath . '/*.php') as $file) {
            $key = basename($file, '.php');
            $values = require $file;
            if (is_array($values)) {
               foreach ($values as $subKey => $value) {
                  $config->set("{$key}.{$subKey}", $value);
               }
            }
         }
      }
   }

   private function handleError(\Throwable $e): void {
      $logger = $this->container->get(LoggerInterface::class);
      $logger->error('Application error: ' . $e->getMessage(), [
         'exception' => $e,
         'file' => $e->getFile(),
         'line' => $e->getLine(),
      ]);

      $response = new Response('500 Internal Server Error', 500);

      if (env('APP_DEBUG', false)) {
         $response->setContent($this->formatDebugError($e));
      }

      $response->send();
   }

   private function formatDebugError(\Throwable $e): string {
      return sprintf(
         "<h1>Error: %s</h1><p><strong>File:</strong> %s<br><strong>Line:</strong> %d</p><pre>%s</pre>",
         $e->getMessage(),
         $e->getFile(),
         $e->getLine(),
         $e->getTraceAsString()
      );
   }
}