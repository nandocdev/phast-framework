<?php
/**
 * @package     projects/phast
 * @subpackage  public
 * @file        index
 * @author      Fernando Castillo <fdocst@gmail.com>
 * @date        2025-07-04 13:25:52
 * @version     1.0.0
 * @description Application entry point
 */

declare(strict_types=1);

define('PHAST_BASE_PATH', dirname(__DIR__));

// 1. Registrar el autoloader de Composer
require_once PHAST_BASE_PATH . '/vendor/autoload.php';

// 2. Configurar el entorno
$dotenv = Dotenv\Dotenv::createImmutable(PHAST_BASE_PATH);
$dotenv->load();
$dotenv->required(['DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASS'])->notEmpty();

// 3. Iniciar la aplicación
$app = new \Phast\Core\Application\Bootstrap();

// 4. Registrar rutas
require_once PHAST_BASE_PATH . '/app/routes.php';

// 5. Ejecutar la aplicación
$app->run();