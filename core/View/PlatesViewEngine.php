<?php
/**
 * @package     phast/core
 * @subpackage  View
 * @file        PlatesViewEngine
 * @author      Fernando Castillo <fdocst@gmail.com>
 * @date        2025-07-04
 * @version     1.0.0
 * @description Plates template engine implementation
 */

declare(strict_types=1);

namespace Phast\Core\View;

use League\Plates\Engine;
use League\Plates\Extension\ExtensionInterface;
use Psr\Log\LoggerInterface;

class PlatesViewEngine implements ViewInterface {
   private Engine $plates;
   private LoggerInterface $logger;
   private array $globalData = [];
   private array $composers = [];
   private string $defaultLayout;
   private array $config;

   public function __construct(
      LoggerInterface $logger,
      array $config = []
   ) {
      $this->logger = $logger;
      $this->config = array_merge([
         'views_path' => PHAST_BASE_PATH . '/resources/views',
         'templates_path' => PHAST_BASE_PATH . '/resources/templates',
         'file_extension' => 'phtml',
         'cache_enabled' => false,
         'cache_path' => PHAST_BASE_PATH . '/storage/cache/views',
      ], $config);

      $this->defaultLayout = $config['default_layout'] ?? 'default';
      $this->initializePlates();
      $this->registerHelpers();
   }

   public function render(string $template, array $data = [], string $layout = ''): string {
      try {
         // Execute view composers
         $this->executeComposers($template, $data);

         // Merge global data
         $data = array_merge($this->globalData, $data);

         // Add layout data
         $data['_layout'] = $layout;
         $data['_template'] = $template;

         // Log rendering attempt
         $this->logger->debug('Rendering template', [
            'template' => $template,
            'layout' => $layout,
            'data_keys' => array_keys($data)
         ]);

         // Check if template exists in views
         $templatePath = $this->resolveTemplatePath($template);

         if (!$this->plates->exists($templatePath)) {
            throw new ViewException("Template not found: {$template}");
         }

         // Render the template
         $content = $this->plates->render($templatePath, $data);

         // Note: Plates handles layouts automatically when $this->layout() is called in templates
         // So we don't need to manually wrap in layout if the template already defines one
         // This prevents double-rendering of layouts

         return $content;

      } catch (\Throwable $e) {
         $this->logger->error('View rendering failed', [
            'template' => $template,
            'layout' => $layout,
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
         ]);

         if (config('app.debug', false)) {
            throw new ViewException(
               "View rendering failed: {$e->getMessage()}",
               0,
               $e
            );
         }

         throw new ViewException('View rendering failed');
      }
   }

   public function exists(string $template): bool {
      return $this->plates->exists($this->resolveTemplatePath($template));
   }

   public function addGlobalData(string $key, mixed $value): void {
      $this->globalData[$key] = $value;
      $this->plates->addData([$key => $value]);
   }

   public function addGlobalDataArray(array $data): void {
      $this->globalData = array_merge($this->globalData, $data);
      $this->plates->addData($data);
   }

   public function composer(string|array $templates, callable $callback): void {
      $templates = (array) $templates;

      foreach ($templates as $template) {
         if (!isset($this->composers[$template])) {
            $this->composers[$template] = [];
         }
         $this->composers[$template][] = $callback;
      }
   }

   /**
    * Get the Plates engine instance for advanced usage
    */
   public function getEngine(): Engine {
      return $this->plates;
   }

   /**
    * Add a Plates extension
    */
   public function addExtension(ExtensionInterface $extension): void {
      $this->plates->loadExtension($extension);
   }

   private function initializePlates(): void {
      $this->plates = new Engine();

      // Set file extension
      $this->plates->setFileExtension($this->config['file_extension']);

      // Add template folders
      $this->plates->addFolder('views', $this->config['views_path']);
      $this->plates->addFolder('layouts', $this->config['templates_path'] . '/layouts');
      $this->plates->addFolder('partials', $this->config['templates_path'] . '/partials');

      // Set views as default directory (not templates)
      $this->plates->setDirectory($this->config['views_path']);
   }

   private function registerHelpers(): void {
      // Add common helper functions
      $this->plates->registerFunction('url', function (string $path = ''): string {
         $baseUrl = config('app.url', 'http://localhost:8000');
         return rtrim($baseUrl, '/') . '/' . ltrim($path, '/');
      });

      $this->plates->registerFunction('asset', function (string $path): string {
         $baseUrl = config('app.url', 'http://localhost:8000');
         return rtrim($baseUrl, '/') . '/assets/' . ltrim($path, '/');
      });

      $this->plates->registerFunction('route', function (string $name, array $params = []): string {
         // This would need a route URL generator - simplified for now
         return '#route:' . $name;
      });

      $this->plates->registerFunction('old', function (string $key, mixed $default = ''): mixed {
         // Get old input data from session - simplified for now
         return $default;
      });

      $this->plates->registerFunction('csrf_token', function (): string {
         // Generate CSRF token - simplified for now
         return bin2hex(random_bytes(32));
      });

      $this->plates->registerFunction('config', function (string $key, mixed $default = null): mixed {
         return config($key, $default);
      });

      $this->plates->registerFunction('json', function (mixed $data): string {
         return json_encode($data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
      });
   }

   private function resolveTemplatePath(string $template): string {
      // If template contains namespace, use as-is
      if (str_contains($template, '::')) {
         return $template;
      }

      // Otherwise, assume it's in views folder
      return "views::{$template}";
   }

   private function executeComposers(string $template, array &$data): void {
      $composersToExecute = [];

      // Check for exact template match
      if (isset($this->composers[$template])) {
         $composersToExecute = array_merge($composersToExecute, $this->composers[$template]);
      }

      // Check for wildcard matches
      foreach ($this->composers as $pattern => $callbacks) {
         if ($pattern !== $template && $this->matchesPattern($template, $pattern)) {
            $composersToExecute = array_merge($composersToExecute, $callbacks);
         }
      }

      // Execute all matching composers
      foreach ($composersToExecute as $callback) {
         try {
            $result = $callback($data, $template);
            if (is_array($result)) {
               $data = array_merge($data, $result);
            }
         } catch (\Throwable $e) {
            $this->logger->warning('View composer failed', [
               'template' => $template,
               'error' => $e->getMessage()
            ]);
         }
      }
   }

   private function matchesPattern(string $template, string $pattern): bool {
      // Convert wildcard pattern to regex
      $regex = str_replace(['*', '?'], ['.*', '.'], $pattern);
      return preg_match("/^{$regex}$/", $template) === 1;
   }
}
