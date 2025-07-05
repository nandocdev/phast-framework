<?php
/**
 * @package     phast/core
 * @subpackage  Console/Commands/Delete
 * @file        DeleteModuleCommand
 * @author      Fernando Castillo <fdocst@gmail.com>
 * @date        2025-07-04
 * @version     1.0.0
 * @description Command to delete an entire module
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
   name: 'delete:module',
   description: 'Delete an entire module and all its components'
)]
class DeleteModuleCommand extends BaseCommand {
   protected function configure(): void {
      $this
         ->addArgument('name', InputArgument::REQUIRED, 'The name of the module to delete')
         ->addOption('force', 'f', InputOption::VALUE_NONE, 'Force deletion without confirmation')
         ->setHelp('This command allows you to delete an entire module and all its files...');
   }

   protected function execute(InputInterface $input, OutputInterface $output): int {
      $name = $input->getArgument('name');
      $force = $input->getOption('force');

      $moduleName = $this->studlyCase($name);
      $modulePath = $this->getModulePath($moduleName);

      if (!$this->moduleExists($moduleName)) {
         $this->io->error("Module '{$moduleName}' does not exist!");
         return self::FAILURE;
      }

      if (!$force) {
         $this->io->warning("This will delete the entire module '{$moduleName}' and all its files!");

         $confirmed = $this->io->confirm(
            "Are you sure you want to delete the module '{$moduleName}'? This action cannot be undone.",
            false
         );

         if (!$confirmed) {
            $this->io->note('Deletion cancelled.');
            return self::SUCCESS;
         }
      }

      $this->deleteDirectory($modulePath);

      $this->io->success("Module '{$moduleName}' deleted successfully!");

      return self::SUCCESS;
   }

   private function deleteDirectory(string $dir): void {
      if (!is_dir($dir)) {
         return;
      }

      $files = array_diff(scandir($dir), ['.', '..']);

      foreach ($files as $file) {
         $filePath = $dir . DIRECTORY_SEPARATOR . $file;

         if (is_dir($filePath)) {
            $this->deleteDirectory($filePath);
         } else {
            unlink($filePath);
         }
      }

      rmdir($dir);
   }
}
