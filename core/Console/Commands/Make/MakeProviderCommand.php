<?php
/**
 * @package     phast/core
 * @subpackage  Console/Commands/Make
 * @file        MakeProviderCommand
 * @author      Fernando Castillo <fdocst@gmail.com>
 * @date        2025-07-04
 * @version     1.0.0
 * @description Command to create a new service provider
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
   name: 'make:provider',
   description: 'Create a new service provider'
)]
class MakeProviderCommand extends BaseCommand {
   protected function configure(): void {
      $this
         ->addArgument('name', InputArgument::REQUIRED, 'The name of the service provider')
         ->addOption('module', 'm', InputOption::VALUE_REQUIRED, 'The module name where the provider will be created')
         ->setHelp('This command allows you to create a new service provider in a specific module...');
   }

   protected function execute(InputInterface $input, OutputInterface $output): int {
      $name = $input->getArgument('name');
      $module = $input->getOption('module');

      if (!$module) {
         $this->io->error('Module option is required. Use --module=ModuleName');
         return self::FAILURE;
      }

      $providerName = $this->studlyCase($name);
      if (!str_ends_with($providerName, 'ServiceProvider')) {
         $providerName .= 'ServiceProvider';
      }

      $moduleName = $this->studlyCase($module);
      $modulePath = $this->getModulePath($moduleName);

      if (!$this->moduleExists($moduleName)) {
         $this->io->error("Module '{$moduleName}' does not exist!");
         return self::FAILURE;
      }

      $providerPath = $modulePath . '/Providers/' . $providerName . '.php';

      if (file_exists($providerPath)) {
         $this->io->error("Provider '{$providerName}' already exists in module '{$moduleName}'!");
         return self::FAILURE;
      }

      $this->ensureDirectoryExists($modulePath . '/Providers');

      $content = $this->getStub('module.provider', [
         'MODULE_NAME' => $moduleName,
         'MODULE_SNAKE' => $this->snakeCase($moduleName),
         'DATE' => date('Y-m-d'),
      ]);

      file_put_contents($providerPath, $content);

      $this->io->success("Provider '{$providerName}' created successfully in module '{$moduleName}'!");
      $this->io->note("Provider created at: {$providerPath}");

      return self::SUCCESS;
   }
}
