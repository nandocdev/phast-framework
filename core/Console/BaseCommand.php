<?php
/**
 * @package     phast/core
 * @subpackage  Console
 * @file        BaseCommand
 * @author      Fernando Castillo <fdocst@gmail.com>
 * @date        2025-07-04
 * @version     1.0.0
 * @description Base class for console commands
 */

declare(strict_types=1);

namespace Phast\Core\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class BaseCommand extends Command {
   protected SymfonyStyle $io;
   protected string $basePath;

   protected function initialize(InputInterface $input, OutputInterface $output): void {
      $this->io = new SymfonyStyle($input, $output);
      $this->basePath = defined('PHAST_BASE_PATH') ? PHAST_BASE_PATH : getcwd();
   }

   /**
    * Get the stub file content and replace placeholders
    */
   protected function getStub(string $stubName, array $replacements = []): string {
      $stubPath = $this->basePath . '/core/Console/stubs/' . $stubName . '.stub';

      if (!file_exists($stubPath)) {
         throw new \RuntimeException("Stub file not found: {$stubPath}");
      }

      $content = file_get_contents($stubPath);

      // Add default replacements
      $defaultReplacements = [
         'DATE' => date('Y-m-d'),
         'DATETIME' => date('Y-m-d H:i:s'),
      ];

      $replacements = array_merge($defaultReplacements, $replacements);

      foreach ($replacements as $search => $replace) {
         $content = str_replace('{{' . $search . '}}', $replace, $content);
      }

      return $content;
   }

   /**
    * Create directory if it doesn't exist
    */
   protected function ensureDirectoryExists(string $path): void {
      if (!is_dir($path)) {
         mkdir($path, 0755, true);
      }
   }

   /**
    * Convert string to StudlyCase
    */
   protected function studlyCase(string $string): string {
      return str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $string)));
   }

   /**
    * Convert string to snake_case
    */
   protected function snakeCase(string $string): string {
      return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $string));
   }

   /**
    * Convert string to kebab-case
    */
   protected function kebabCase(string $string): string {
      return strtolower(preg_replace('/(?<!^)[A-Z]/', '-$0', $string));
   }

   /**
    * Get module path
    */
   protected function getModulePath(string $module): string {
      return $this->basePath . '/app/Modules/' . $this->studlyCase($module);
   }

   /**
    * Check if module exists
    */
   protected function moduleExists(string $module): bool {
      return is_dir($this->getModulePath($module));
   }
}
