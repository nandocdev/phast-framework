<?php
/**
 * @package     projects/viex
 * @subpackage  config
 * @file        phinx
 * @author      Fernando Castillo <fdocst@gmail.com>
 * @date        2025-07-05 01:16:26
 * @version     1.0.0
 * @description
 */

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

defined('PHINX_CONFIG_DIR') || define('PHINX_CONFIG_DIR', __DIR__ . '/../');
// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

return
    [
        'paths' => [
            'migrations' => '%%PHINX_CONFIG_DIR%%/../database/migrations',
            'seeds' => '%%PHINX_CONFIG_DIR%%/../database/seeds'
        ],
        'environments' => [
            'default_migration_table' => 'migrations',
            'default_environment' => 'development',
            'production' => [
                'adapter' => $_ENV['DB_CONNECTION'] ?? 'mysql',
                'host' => $_ENV['DB_HOST'],
                'name' => $_ENV['DB_NAME'],
                'user' => $_ENV['DB_USER'],
                'pass' => $_ENV['DB_PASS'],
                'port' => $_ENV['DB_PORT'] ?? '3306',
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
            ],
            'development' => [
                'adapter' => $_ENV['DB_CONNECTION'] ?? 'mysql',
                'host' => $_ENV['DB_HOST'],
                'name' => $_ENV['DB_NAME'],
                'user' => $_ENV['DB_USER'],
                'pass' => $_ENV['DB_PASS'],
                'port' => $_ENV['DB_PORT'] ?? '3306',
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
            ],
            'testing' => [
                'adapter' => $_ENV['DB_CONNECTION'] ?? 'mysql',
                'host' => $_ENV['DB_HOST'],
                'name' => $_ENV['DB_NAME'] . '_test',
                'user' => $_ENV['DB_USER'],
                'pass' => $_ENV['DB_PASS'],
                'port' => $_ENV['DB_PORT'] ?? '3306',
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
            ]
        ],
        'version_order' => 'creation'
    ];