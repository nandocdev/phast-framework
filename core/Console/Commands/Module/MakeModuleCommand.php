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

      // Create initial files for the module
      $this->createInitialFiles($modulePath, $moduleName);

      // Create README.md
      $this->createReadme($modulePath, $moduleName);

      $this->io->success("Module '{$moduleName}' created successfully!");
      $this->io->note([
         "Module created at: {$modulePath}",
         "Files generated:",
         "  • {$moduleName}Controller.php",
         "  • {$moduleName}.php (Entity)",
         "  • {$moduleName}Repository.php", 
         "  • {$moduleName}Service.php",
         "  • {$moduleName}Id.php (Value Object)",
         "  • {$moduleName}ServiceProvider.php",
         "  • routes.php",
         "  • README.md",
         "",
         "Don't forget to register the module provider in your bootstrap if needed."
      ]);

      return self::SUCCESS;
   }

   private function createRoutesFile(string $modulePath, string $moduleName): void {
      $content = $this->getStub('module.routes', [
         'MODULE_NAME' => $moduleName,
         'MODULE_SNAKE' => $this->snakeCase($moduleName),
         'MODULE_KEBAB' => $this->kebabCase($moduleName),
         'DATE' => date('Y-m-d'),
      ]);

      file_put_contents($modulePath . '/routes.php', $content);
   }

   private function createModuleProvider(string $modulePath, string $moduleName): void {
      $content = $this->getStub('module.provider', [
         'MODULE_NAME' => $moduleName,
         'MODULE_SNAKE' => $this->snakeCase($moduleName),
         'DATE' => date('Y-m-d'),
      ]);

      file_put_contents($modulePath . '/Providers/' . $moduleName . 'ServiceProvider.php', $content);
   }

   private function createReadme(string $modulePath, string $moduleName): void {
      $content = $this->getStub('module.readme', [
         'MODULE_NAME' => $moduleName,
         'DATE' => date('Y-m-d'),
      ]);

      file_put_contents($modulePath . '/README.md', $content);
   }

   private function createInitialFiles(string $modulePath, string $moduleName): void
   {
      // Generate consistent names
      $entityName = $moduleName;
      $entityNameLower = strtolower($moduleName);
      $entityNameCamel = lcfirst($moduleName);
      $controllerName = $moduleName . 'Controller';
      $repositoryName = $moduleName . 'Repository';
      $repositoryNameCamel = lcfirst($repositoryName);
      $serviceName = $moduleName . 'Service';
      $valueObjectName = $moduleName . 'Id';

      // Create Controller
      $this->createController($modulePath, $moduleName, $controllerName);

      // Create Entity
      $this->createEntity($modulePath, $moduleName, $entityName);

      // Create Repository
      $this->createRepository($modulePath, $moduleName, $repositoryName, $entityName, $entityNameLower, $entityNameCamel);

      // Create Service
      $this->createService($modulePath, $moduleName, $serviceName, $entityName, $entityNameLower, $entityNameCamel, $repositoryName, $repositoryNameCamel);

      // Create Value Object
      $this->createValueObject($modulePath, $moduleName, $valueObjectName);
   }

   private function createController(string $modulePath, string $moduleName, string $controllerName): void
   {
      $content = $this->getStub('module.controller', [
         'MODULE_NAME' => $moduleName,
         'MODULE_NAME_CAMEL' => lcfirst($moduleName),
         'CONTROLLER_NAME' => $controllerName,
         'CONTROLLER_BASE' => $moduleName,
         'DATE' => date('Y-m-d'),
      ]);

      file_put_contents($modulePath . '/Controllers/' . $controllerName . '.php', $content);
   }

   private function createEntity(string $modulePath, string $moduleName, string $entityName): void
   {
      $content = $this->getStub('module.entity', [
         'MODULE_NAME' => $moduleName,
         'ENTITY_NAME' => $entityName,
         'DATE' => date('Y-m-d'),
      ]);

      file_put_contents($modulePath . '/Models/Entities/' . $entityName . '.php', $content);
   }

   private function createRepository(string $modulePath, string $moduleName, string $repositoryName, string $entityName, string $entityNameLower, string $entityNameCamel): void
   {
      $content = $this->getStub('module.repository', [
         'MODULE_NAME' => $moduleName,
         'REPOSITORY_NAME' => $repositoryName,
         'ENTITY_NAME' => $entityName,
         'ENTITY_NAME_LOWER' => $entityNameLower,
         'ENTITY_NAME_CAMEL' => $entityNameCamel,
         'DATE' => date('Y-m-d'),
      ]);

      file_put_contents($modulePath . '/Models/Repositories/' . $repositoryName . '.php', $content);
   }

   private function createService(string $modulePath, string $moduleName, string $serviceName, string $entityName, string $entityNameLower, string $entityNameCamel, string $repositoryName, string $repositoryNameCamel): void
   {
      $content = $this->getStub('module.service', [
         'MODULE_NAME' => $moduleName,
         'SERVICE_NAME' => $serviceName,
         'ENTITY_NAME' => $entityName,
         'ENTITY_NAME_LOWER' => $entityNameLower,
         'ENTITY_NAME_CAMEL' => $entityNameCamel,
         'REPOSITORY_NAME' => $repositoryName,
         'REPOSITORY_NAME_CAMEL' => $repositoryNameCamel,
         'DATE' => date('Y-m-d'),
      ]);

      file_put_contents($modulePath . '/Services/' . $serviceName . '.php', $content);
   }

   private function createValueObject(string $modulePath, string $moduleName, string $valueObjectName): void
   {
      $content = $this->getStub('module.valueobject', [
         'MODULE_NAME' => $moduleName,
         'VALUE_OBJECT_NAME' => $valueObjectName,
         'DATE' => date('Y-m-d'),
      ]);

      file_put_contents($modulePath . '/Models/ValueObjects/' . $valueObjectName . '.php', $content);
   }
}
