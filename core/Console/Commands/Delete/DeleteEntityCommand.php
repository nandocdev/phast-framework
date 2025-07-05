<?php
/**
 * @package     phast/core
 * @subpackage  Console/Commands/Delete
 * @file        DeleteEntityCommand
 * @author      Fernando Castillo <fdocst@gmail.com>
 * @date        2025-07-04
 * @version     1.0.0
 * @description Command to delete an entity
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
   name: 'delete:entity',
   description: 'Delete an entity class'
)]
class DeleteEntityCommand extends BaseCommand {
   protected function configure(): void {
      $this
         ->addArgument('name', InputArgument::REQUIRED, 'The name of the entity to delete')
         ->addOption('module', 'm', InputOption::VALUE_REQUIRED, 'The module name where the entity is located')
         ->addOption('force', 'f', InputOption::VALUE_NONE, 'Force deletion without confirmation')
         ->setHelp('This command allows you to delete an entity class from a specific module...');
   }

   protected function execute(InputInterface $input, OutputInterface $output): int {
      $name = $input->getArgument('name');
      $module = $input->getOption('module');
      $force = $input->getOption('force');

      if (!$module) {
         $this->io->error('Module option is required. Use --module=ModuleName');
         return self::FAILURE;
      }

      $entityName = $this->studlyCase($name);
      $moduleName = $this->studlyCase($module);
      $modulePath = $this->getModulePath($moduleName);

      if (!$this->moduleExists($moduleName)) {
         $this->io->error("Module '{$moduleName}' does not exist!");
         return self::FAILURE;
      }

      $entityPath = $modulePath . '/Models/Entities/' . $entityName . '.php';

      if (!file_exists($entityPath)) {
         $this->io->error("Entity '{$entityName}' does not exist in module '{$moduleName}'!");
         return self::FAILURE;
      }

      if (!$force) {
         $confirmed = $this->io->confirm(
            "Are you sure you want to delete the entity '{$entityName}' from module '{$moduleName}'?",
            false
         );

         if (!$confirmed) {
            $this->io->note('Deletion cancelled.');
            return self::SUCCESS;
         }
      }

      unlink($entityPath);

      $this->io->success("Entity '{$entityName}' deleted successfully from module '{$moduleName}'!");

      return self::SUCCESS;
   }
}
