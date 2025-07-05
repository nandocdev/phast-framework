<?php
/**
 * @package     phast/core
 * @subpackage  Console/Commands/Make
 * @file        MakeValueObjectCommand
 * @author      Fernando Castillo <fdocst@gmail.com>
 * @date        2025-07-04
 * @version     1.0.0
 * @description Command to create a new value object
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
   name: 'make:valueobject',
   description: 'Create a new value object class'
)]
class MakeValueObjectCommand extends BaseCommand {
   protected function configure(): void {
      $this
         ->addArgument('name', InputArgument::REQUIRED, 'The name of the value object')
         ->addOption('module', 'm', InputOption::VALUE_REQUIRED, 'The module name where the value object will be created')
         ->setHelp('This command allows you to create a new value object class in a specific module...');
   }

   protected function execute(InputInterface $input, OutputInterface $output): int {
      $name = $input->getArgument('name');
      $module = $input->getOption('module');

      if (!$module) {
         $this->io->error('Module option is required. Use --module=ModuleName');
         return self::FAILURE;
      }

      $valueObjectName = $this->studlyCase($name);
      $moduleName = $this->studlyCase($module);
      $modulePath = $this->getModulePath($moduleName);

      if (!$this->moduleExists($moduleName)) {
         $this->io->error("Module '{$moduleName}' does not exist!");
         return self::FAILURE;
      }

      $valueObjectPath = $modulePath . '/Models/ValueObjects/' . $valueObjectName . '.php';

      if (file_exists($valueObjectPath)) {
         $this->io->error("Value Object '{$valueObjectName}' already exists in module '{$moduleName}'!");
         return self::FAILURE;
      }

      $this->ensureDirectoryExists($modulePath . '/Models/ValueObjects');

      $content = $this->getStub('module.valueobject', [
         'MODULE_NAME' => $moduleName,
         'VALUE_OBJECT_NAME' => $valueObjectName,
         'DATE' => date('Y-m-d'),
      ]);

      file_put_contents($valueObjectPath, $content);

      $this->io->success("Value Object '{$valueObjectName}' created successfully in module '{$moduleName}'!");
      $this->io->note("Value Object created at: {$valueObjectPath}");

      return self::SUCCESS;
   }
}
