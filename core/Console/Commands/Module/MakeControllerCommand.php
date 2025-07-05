<?php
/**
 * @package     phast/core
 * @subpackage  Console/Commands/Module
 * @file        MakeControllerCommand
 * @author      Fernando Castillo <fdocst@gmail.com>
 * @date        2025-07-04
 * @version     1.0.0
 * @description Command to create a controller in a module
 */

declare(strict_types=1);

namespace Phast\Core\Console\Commands\Module;

use Phast\Core\Console\BaseCommand;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'make:controller',
    description: 'Create a new controller in a module'
)]
class MakeControllerCommand extends BaseCommand
{
    protected function configure(): void
    {
        $this
            ->addArgument('module', InputArgument::REQUIRED, 'The name of the module')
            ->addArgument('name', InputArgument::REQUIRED, 'The name of the controller')
            ->setHelp('This command creates a new controller in the specified module...');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $module = $this->studlyCase($input->getArgument('module'));
        $name = $this->studlyCase($input->getArgument('name'));
        
        if (!str_ends_with($name, 'Controller')) {
            $name .= 'Controller';
        }

        if (!$this->moduleExists($module)) {
            $this->io->error("Module '{$module}' does not exist!");
            $this->io->note("Create it first with: php phast make:module {$module}");
            return self::FAILURE;
        }

        $modulePath = $this->getModulePath($module);
        $controllerPath = $modulePath . '/Controllers/' . $name . '.php';

        if (file_exists($controllerPath)) {
            $this->io->error("Controller '{$name}' already exists in module '{$module}'!");
            return self::FAILURE;
        }

        $this->ensureDirectoryExists($modulePath . '/Controllers');

        $content = $this->getStub('module.controller', [
            'MODULE_NAME' => $module,
            'CONTROLLER_NAME' => $name,
            'CONTROLLER_BASE' => str_replace('Controller', '', $name),
            'DATE' => date('Y-m-d'),
        ]);

        file_put_contents($controllerPath, $content);

        $this->io->success("Controller '{$name}' created successfully in module '{$module}'!");
        $this->io->note("Controller created at: {$controllerPath}");

        return self::SUCCESS;
    }
}
