<?php
/**
 * @package     phast/core
 * @subpackage  Console
 * @file        Application
 * @author      Fernando Castillo <fdocst@gmail.com>
 * @date        2025-07-04
 * @version     1.0.0
 * @description Console application for Phast Framework
 */

declare(strict_types=1);

namespace Phast\Core\Console;

use Symfony\Component\Console\Application as SymfonyApplication;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Phast\Core\Application\Bootstrap;

class Application extends SymfonyApplication {
   private Bootstrap $bootstrap;

   public function __construct(Bootstrap $bootstrap) {
      parent::__construct('Phast Framework', '1.0.0');

      $this->bootstrap = $bootstrap;
      $this->registerCommands();
   }

   private function registerCommands(): void {
      // Comandos de módulos
      $this->add(new Commands\Module\MakeModuleCommand());
      $this->add(new Commands\Module\MakeControllerCommand());

      // Comandos de creación individuales
      $this->add(new Commands\Make\MakeEntityCommand());
      $this->add(new Commands\Make\MakeRepositoryCommand());
      $this->add(new Commands\Make\MakeServiceCommand());
      $this->add(new Commands\Make\MakeValueObjectCommand());
      $this->add(new Commands\Make\MakeProviderCommand());

      // Comandos de eliminación
      $this->add(new Commands\Delete\DeleteEntityCommand());
      $this->add(new Commands\Delete\DeleteModuleCommand());
      $this->add(new Commands\Delete\DeleteComponentCommand());

      // Comandos de utilidades  
      $this->add(new Commands\Serve\ServeCommand());
      $this->add(new Commands\Route\RouteListCommand($this->bootstrap->getRouter()));
   }

   public function run(InputInterface $input = null, OutputInterface $output = null): int {
      return parent::run($input, $output);
   }

   public function getBootstrap(): Bootstrap {
      return $this->bootstrap;
   }
}
