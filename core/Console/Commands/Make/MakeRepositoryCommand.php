<?php
/**
 * @package     phast/core
 * @subpackage  Console/Commands/Make
 * @file        MakeRepositoryCommand
 * @author      Fernando Castillo <fdocst@gmail.com>
 * @date        2025-07-04
 * @version     1.0.0
 * @description Command to create a new repository
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
   name: 'make:repository',
   description: 'Create a new repository class'
)]
class MakeRepositoryCommand extends BaseCommand {
   protected function configure(): void {
      $this
         ->addArgument('name', InputArgument::REQUIRED, 'The name of the repository')
         ->addOption('module', 'm', InputOption::VALUE_REQUIRED, 'The module name where the repository will be created')
         ->addOption('entity', 'e', InputOption::VALUE_OPTIONAL, 'The entity name for the repository')
         ->setHelp('This command allows you to create a new repository class in a specific module...');
   }

   protected function execute(InputInterface $input, OutputInterface $output): int {
      $name = $input->getArgument('name');
      $module = $input->getOption('module');
      $entity = $input->getOption('entity');

      if (!$module) {
         $this->io->error('Module option is required. Use --module=ModuleName');
         return self::FAILURE;
      }

      $repositoryName = $this->studlyCase($name);
      $moduleName = $this->studlyCase($module);
      $entityName = $entity ? $this->studlyCase($entity) : str_replace('Repository', '', $repositoryName);

      $modulePath = $this->getModulePath($moduleName);

      if (!$this->moduleExists($moduleName)) {
         $this->io->error("Module '{$moduleName}' does not exist!");
         return self::FAILURE;
      }

      $repositoryPath = $modulePath . '/Models/Repositories/' . $repositoryName . '.php';

      if (file_exists($repositoryPath)) {
         $this->io->error("Repository '{$repositoryName}' already exists in module '{$moduleName}'!");
         return self::FAILURE;
      }

      $this->ensureDirectoryExists($modulePath . '/Models/Repositories');

      $content = $this->getStub('module.repository', [
         'MODULE_NAME' => $moduleName,
         'REPOSITORY_NAME' => $repositoryName,
         'ENTITY_NAME' => $entityName,
         'ENTITY_NAME_LOWER' => strtolower($entityName),
         'ENTITY_NAME_CAMEL' => lcfirst($entityName),
         'DATE' => date('Y-m-d'),
      ]);

      file_put_contents($repositoryPath, $content);

      $this->io->success("Repository '{$repositoryName}' created successfully in module '{$moduleName}'!");
      $this->io->note("Repository created at: {$repositoryPath}");

      return self::SUCCESS;
   }
}
