<?php
/**
 * @package     phast/core
 * @subpackage  Console/Commands/Make
 * @file        MakeServiceCommand
 * @author      Fernando Castillo <fdocst@gmail.com>
 * @date        2025-07-04
 * @version     1.0.0
 * @description Command to create a new service
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
   name: 'make:service',
   description: 'Create a new service class'
)]
class MakeServiceCommand extends BaseCommand {
   protected function configure(): void {
      $this
         ->addArgument('name', InputArgument::REQUIRED, 'The name of the service')
         ->addOption('module', 'm', InputOption::VALUE_REQUIRED, 'The module name where the service will be created')
         ->addOption('repository', 'r', InputOption::VALUE_OPTIONAL, 'The repository name for the service')
         ->setHelp('This command allows you to create a new service class in a specific module...');
   }

   protected function execute(InputInterface $input, OutputInterface $output): int {
      $name = $input->getArgument('name');
      $module = $input->getOption('module');
      $repository = $input->getOption('repository');

      if (!$module) {
         $this->io->error('Module option is required. Use --module=ModuleName');
         return self::FAILURE;
      }

      $serviceName = $this->studlyCase($name);
      $moduleName = $this->studlyCase($module);
      $repositoryName = $repository ? $this->studlyCase($repository) : str_replace('Service', 'Repository', $serviceName);
      $entityName = str_replace(['Service', 'Repository'], '', $serviceName);

      $modulePath = $this->getModulePath($moduleName);

      if (!$this->moduleExists($moduleName)) {
         $this->io->error("Module '{$moduleName}' does not exist!");
         return self::FAILURE;
      }

      $servicePath = $modulePath . '/Services/' . $serviceName . '.php';

      if (file_exists($servicePath)) {
         $this->io->error("Service '{$serviceName}' already exists in module '{$moduleName}'!");
         return self::FAILURE;
      }

      $this->ensureDirectoryExists($modulePath . '/Services');

      $content = $this->getStub('module.service', [
         'MODULE_NAME' => $moduleName,
         'SERVICE_NAME' => $serviceName,
         'ENTITY_NAME' => $entityName,
         'ENTITY_NAME_LOWER' => strtolower($entityName),
         'ENTITY_NAME_CAMEL' => lcfirst($entityName),
         'REPOSITORY_NAME' => $repositoryName,
         'REPOSITORY_NAME_CAMEL' => lcfirst($repositoryName),
         'DATE' => date('Y-m-d'),
      ]);

      file_put_contents($servicePath, $content);

      $this->io->success("Service '{$serviceName}' created successfully in module '{$moduleName}'!");
      $this->io->note("Service created at: {$servicePath}");

      return self::SUCCESS;
   }
}
