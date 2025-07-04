<?php
/**
 * @package     phast/core
 * @subpackage  Config
 * @file        ConfigInterface
 * @author      Fernando Castillo <fdocst@gmail.com>
 * @date        2025-07-04
 * @version     1.0.0
 * @description Configuration interface
 */

declare(strict_types=1);

namespace Phast\Core\Config;

interface ConfigInterface
{
    /**
     * Get configuration value
     */
    public function get(string $key, mixed $default = null): mixed;

    /**
     * Set configuration value
     */
    public function set(string $key, mixed $value): void;

    /**
     * Check if configuration key exists
     */
    public function has(string $key): bool;

    /**
     * Load configuration from file
     */
    public function load(string $path): void;

    /**
     * Get all configuration values
     */
    public function all(): array;
}
