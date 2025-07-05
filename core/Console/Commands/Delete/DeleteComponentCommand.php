<?php
/**
 * @package     phast/core
 * @subpackage  Console/Commands/Delete
 * @file        DeleteComponentCommand
 * @author      Fernando Castillo <fdocst@gmail.com>
 * @date        2025-07-04
 * @version     1.0.0
 * @description Generic command to delete module components
 */

declare(strict_types=1);

namespace Phast\Core\Console\Commands\Delete;

use Phast\Core\Console\BaseCommand;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
   name: 'delete:component',
   description: 'Delete a module component (controller, service, repository, etc.)'
)]
class DeleteComponentCommand extends BaseCommand {
   private const COMPONENT_TYPES = [
      'controller' => ['path' => 'Controllers', 'suffix' => 'Controller'],
      'service' => ['path' => 'Services', 'suffix' => 'Service'],
      'repository' => ['path' => 'Models/Repositories', 'suffix' => 'Repository'],
      'entity' => ['path' => 'Models/Entities', 'suffix' => ''],
      'valueobject' => ['path' => 'Models/ValueObjects', 'suffix' => ''],
      'provider' => ['path' => 'Providers', 'suffix' => 'ServiceProvider'],
   ];

   protected function configure(): void {
      $this
         ->addArgument('type', InputArgument::REQUIRED, 'The type of component (controller, service, repository, entity, valueobject, provider)')
         ->addArgument('name', InputArgument::REQUIRED, 'The name of the component to delete')
         ->addOption('module', 'm', InputOption::VALUE_REQUIRED, 'The module name where the component is located')
         ->addOption('force', 'f', InputOption::VALUE_NONE, 'Force deletion without confirmation')
         ->setHelp('This command allows you to delete any module component...');
   }

   protected function execute(InputInterface $input, OutputInterface $output): int {
      $type = strtolower($input->getArgument('type'));
      $name = $input->getArgument('name');
      $module = $input->getOption('module');
      $force = $input->getOption('force');

      if (!$module) {
         $this->io->error('Module option is required. Use --module=ModuleName');
         return self::FAILURE;
      }

      if (!isset(self::COMPONENT_TYPES[$type])) {
         $this->io->error("Invalid component type '{$type}'. Valid types: " . implode(', ', array_keys(self::COMPONENT_TYPES)));
         return self::FAILURE;
      }

      $componentName = $this->studlyCase($name);
      $moduleName = $this->studlyCase($module);
      $modulePath = $this->getModulePath($moduleName);

      if (!$this->moduleExists($moduleName)) {
         $this->io->error("Module '{$moduleName}' does not exist!");
         return self::FAILURE;
      }

      $config = self::COMPONENT_TYPES[$type];
      $fileName = $componentName . $config['suffix'] . '.php';
      $componentPath = $modulePath . '/' . $config['path'] . '/' . $fileName;

      if (!file_exists($componentPath)) {
         $this->io->error("Component '{$componentName}' of type '{$type}' does not exist in module '{$moduleName}'!");
         return self::FAILURE;
      }

      if (!$force) {
         $confirmed = $this->io->confirm(
            "Are you sure you want to delete the {$type} '{$componentName}' from module '{$moduleName}'?",
            false
         );

         if (!$confirmed) {
            $this->io->note('Deletion cancelled.');
            return self::SUCCESS;
         }
      }

      unlink($componentPath);

      $this->io->success("Component '{$componentName}' ({$type}) deleted successfully from module '{$moduleName}'!");

      return self::SUCCESS;
   }
}
