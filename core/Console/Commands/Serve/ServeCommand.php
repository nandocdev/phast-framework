<?php
/**
 * @package     phast/core
 * @subpackage  Console/Commands/Serve
 * @file        ServeCommand
 * @author      Fernando Castillo <fdocst@gmail.com>
 * @date        2025-07-04
 * @version     1.0.0
 * @description Command to start the development server
 */

declare(strict_types=1);

namespace Phast\Core\Console\Commands\Serve;

use Phast\Core\Console\BaseCommand;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'serve',
    description: 'Start the Phast development server'
)]
class ServeCommand extends BaseCommand
{
    protected function configure(): void
    {
        $this
            ->addOption('host', null, InputOption::VALUE_OPTIONAL, 'The host address to serve the application on', 'localhost')
            ->addOption('port', null, InputOption::VALUE_OPTIONAL, 'The port to serve the application on', '8000')
            ->addOption('public', null, InputOption::VALUE_OPTIONAL, 'The public directory', 'public')
            ->setHelp('This command starts the built-in PHP development server...');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $host = $input->getOption('host');
        $port = $input->getOption('port');
        $public = $input->getOption('public');
        
        $publicPath = $this->basePath . '/' . $public;
        
        if (!is_dir($publicPath)) {
            $this->io->error("Public directory '{$public}' does not exist!");
            return self::FAILURE;
        }

        $address = "{$host}:{$port}";
        $indexFile = $publicPath . '/index.php';

        if (!file_exists($indexFile)) {
            $this->io->error("Entry point 'index.php' not found in public directory!");
            return self::FAILURE;
        }

        $this->io->info("Starting Phast development server on http://{$address}");
        $this->io->note([
            "Document root: {$publicPath}",
            "Press Ctrl+C to stop the server"
        ]);

        $command = sprintf(
            '%s -S %s -t %s %s',
            PHP_BINARY,
            escapeshellarg($address),
            escapeshellarg($publicPath),
            escapeshellarg($indexFile)
        );

        $this->io->comment("Executing: {$command}");

        // Use exec to replace current process
        exec($command);

        return self::SUCCESS;
    }
}
