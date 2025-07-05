<?php

require_once __DIR__ . '/vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

return
    [
        'paths' => [
            'migrations' => '%%PHINX_CONFIG_DIR%%/database/migrations',
            'seeds' => '%%PHINX_CONFIG_DIR%%/database/seeds'
        ],
        'environments' => [
            'default_migration_table' => 'migrations',
            'default_environment' => 'development',
            'production' => [
                'adapter' => 'mysql',
                'host' => $_ENV['DB_HOST'],
                'name' => $_ENV['DB_NAME'],
                'user' => $_ENV['DB_USER'],
                'pass' => $_ENV['DB_PASS'],
                'port' => $_ENV['DB_PORT'] ?? '3306',
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
            ],
            'development' => [
                'adapter' => 'mysql',
                'host' => $_ENV['DB_HOST'],
                'name' => $_ENV['DB_NAME'],
                'user' => $_ENV['DB_USER'],
                'pass' => $_ENV['DB_PASS'],
                'port' => $_ENV['DB_PORT'] ?? '3306',
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
            ],
            'testing' => [
                'adapter' => 'mysql',
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
