<?php
/**
 * @package     phast/core
 * @file        helpers
 * @author      Fernando Castillo <fdocst@gmail.com>
 * @date        2025-07-04
 * @version     1.0.0
 * @description Helper functions for the application
 */

declare(strict_types=1);

if (!function_exists('app')) {
    /**
     * Get the application container instance
     */
    function app(?string $abstract = null): mixed
    {
        $container = \Phast\Core\Application\Container::getInstance();
        
        if (is_null($abstract)) {
            return $container;
        }

        return $container->get($abstract);
    }
}

if (!function_exists('config')) {
    /**
     * Get configuration value
     */
    function config(string $key, mixed $default = null): mixed
    {
        return app(\Phast\Core\Config\ConfigInterface::class)->get($key, $default);
    }
}

if (!function_exists('env')) {
    /**
     * Get environment variable value
     */
    function env(string $key, mixed $default = null): mixed
    {
        return $_ENV[$key] ?? $default;
    }
}

if (!function_exists('logger')) {
    /**
     * Get logger instance
     */
    function logger(): \Psr\Log\LoggerInterface
    {
        return app(\Psr\Log\LoggerInterface::class);
    }
}

if (!function_exists('dd')) {
    /**
     * Dump and die (for debugging)
     */
    function dd(...$vars): void
    {
        foreach ($vars as $var) {
            \Kint\Kint::dump($var);
        }
        die(1);
    }
}

if (!function_exists('response')) {
    /**
     * Create a response
     */
    function response(mixed $data = '', int $status = 200, array $headers = []): \Phast\Core\Http\Response
    {
        return new \Phast\Core\Http\Response($data, $status, $headers);
    }
}

if (!function_exists('request')) {
    /**
     * Get current request
     */
    function request(): \Phast\Core\Http\Request
    {
        return app(\Phast\Core\Http\Request::class);
    }
}
