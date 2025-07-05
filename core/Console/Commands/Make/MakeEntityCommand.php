<?php
/**
 * @package     phast/core
 * @subpackage  Console/Commands/Make
 * @file        MakeEntityCommand
 * @author      Fernando Castillo <fdocst@gmail.com>
 * @date        2025-07-04
 * @version     1.0.0
 * @description Command to create a new entity
 */

declare(strict_types=1);

namespace Phast\Core\Console\Commands\Make;

use Phast\Core\Console\BaseCommand;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
   name: 'make:entity',
   description: 'Create a new entity class'
)]
class MakeEntityCommand extends BaseCommand {
   protected function configure(): void {
      $this
         ->addArgument('name', InputArgument::REQUIRED, 'The name of the entity')
         ->addOption('module', 'm', InputOption::VALUE_REQUIRED, 'The module name where the entity will be created')
         ->setHelp('This command allows you to create a new entity class in a specific module...');
   }

   protected function execute(InputInterface $input, OutputInterface $output): int {
      $name = $input->getArgument('name');
      $module = $input->getOption('module');

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

      if (file_exists($entityPath)) {
         $this->io->error("Entity '{$entityName}' already exists in module '{$moduleName}'!");
         return self::FAILURE;
      }

      $this->ensureDirectoryExists($modulePath . '/Models/Entities');

      $content = $this->getStub('module.entity', [
         'MODULE_NAME' => $moduleName,
         'ENTITY_NAME' => $entityName,
         'DATE' => date('Y-m-d'),
      ]);

      file_put_contents($entityPath, $content);

      $this->io->success("Entity '{$entityName}' created successfully in module '{$moduleName}'!");
      $this->io->note("Entity created at: {$entityPath}");

      return self::SUCCESS;
   }
}
