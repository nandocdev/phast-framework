<?php
/**
 * @package     phast/app
 * @subpackage  Modules/Users/Providers
 * @file        UserServiceProvider
 * @author      Fernando Castillo <fdocst@gmail.com>
 * @date        2025-07-04
 * @version     1.0.0
 * @description User module service provider
 */

declare(strict_types=1);

namespace Phast\App\Modules\Users\Providers;

use PDO;
use Phast\Core\Contracts\ContainerInterface;
use Phast\Core\Contracts\ServiceProviderInterface;
use Phast\App\Modules\Users\Models\Repositories\UserRepository;
use Phast\App\Modules\Users\Models\Repositories\UserRepositoryInterface;
use Phast\App\Modules\Users\Services\UserService;

class UserServiceProvider implements ServiceProviderInterface {
   public function register(ContainerInterface $container): void {
      // Register PDO connection
      $container->singleton(PDO::class, function () {
         $config = config('database.connections.mysql');

         $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            $config['host'],
            $config['port'],
            $config['database'],
            $config['charset']
         );

         $pdo = new PDO($dsn, $config['username'], $config['password'], $config['options']);

         return $pdo;
      });

      // Register User Repository
      $container->singleton(UserRepositoryInterface::class, UserRepository::class);

      // Register User Service
      $container->singleton(UserService::class, UserService::class);
   }

   public function boot(ContainerInterface $container): void {
      // Boot logic if needed
   }
}
