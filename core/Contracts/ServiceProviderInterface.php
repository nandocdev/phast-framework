<?php
/**
 * @package     phast/core
 * @subpackage  Contracts
 * @file        ServiceProviderInterface
 * @author      Fernando Castillo <fdocst@gmail.com>
 * @date        2025-07-04
 * @version     1.0.0
 * @description Service provider interface
 */

declare(strict_types=1);

namespace Phast\Core\Contracts;

interface ServiceProviderInterface
{
    /**
     * Register services in the container
     */
    public function register(ContainerInterface $container): void;

    /**
     * Boot services after all providers are registered
     */
    public function boot(ContainerInterface $container): void;
}
