<?php
/**
 * @package     phast/core
 * @subpackage  Console/Commands/Module
 * @file        MakeModuleCommand
 * @author      Fernando Castillo <fdocst@gmail.com>
 * @date        2025-07-04
 * @version     1.0.0
 * @description Command to create a new module with complete structure
 */

declare(strict_types=1);

namespace Phast\Core\Console\Commands\Module;

use Phast\Core\Console\BaseCommand;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
   name: 'make:module',
   description: 'Create a new module with complete directory structure'
)]
class MakeModuleCommand extends BaseCommand {
   protected function configure(): void {
      $this
         ->addArgument('name', InputArgument::REQUIRED, 'The name of the module')
         ->setHelp('This command allows you to create a new module with the complete directory structure...');
   }

   protected function execute(InputInterface $input, OutputInterface $output): int {
      $name = $input->getArgument('name');
      $moduleName = $this->studlyCase($name);
      $modulePath = $this->getModulePath($moduleName);

      if ($this->moduleExists($moduleName)) {
         $this->io->error("Module '{$moduleName}' already exists!");
         return self::FAILURE;
      }

      $this->io->info("Creating module: {$moduleName}");

      // Create module directory structure
      $directories = [
         $modulePath,
         $modulePath . '/Controllers',
         $modulePath . '/Models',
         $modulePath . '/Models/Entities',
         $modulePath . '/Models/Repositories',
         $modulePath . '/Models/ValueObjects',
         $modulePath . '/Providers',
         $modulePath . '/Services',
      ];

      foreach ($directories as $directory) {
         $this->ensureDirectoryExists($directory);
      }

      // Create routes.php file
      $this->createRoutesFile($modulePath, $moduleName);

      // Create module provider
      $this->createModuleProvider($modulePath, $moduleName);

      // Create README.md
      $this->createReadme($modulePath, $moduleName);

      $this->io->success("Module '{$moduleName}' created successfully!");
      $this->io->note([
         "Module created at: {$modulePath}",
         "Don't forget to register the module provider in your bootstrap if needed."
      ]);

      return self::SUCCESS;
   }

   private function createRoutesFile(string $modulePath, string $moduleName): void {
      $content = $this->getStub('module.routes', [
         'MODULE_NAME' => $moduleName,
         'MODULE_SNAKE' => $this->snakeCase($moduleName),
         'MODULE_KEBAB' => $this->kebabCase($moduleName),
      ]);

      file_put_contents($modulePath . '/routes.php', $content);
   }

   private function createModuleProvider(string $modulePath, string $moduleName): void {
      $content = $this->getStub('module.provider', [
         'MODULE_NAME' => $moduleName,
         'MODULE_SNAKE' => $this->snakeCase($moduleName),
      ]);

      file_put_contents($modulePath . '/Providers/' . $moduleName . 'ServiceProvider.php', $content);
   }

   private function createReadme(string $modulePath, string $moduleName): void {
      $content = $this->getStub('module.readme', [
         'MODULE_NAME' => $moduleName,
      ]);

      file_put_contents($modulePath . '/README.md', $content);
   }
}
