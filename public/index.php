<?php
/**
 * Entry point aplikasi CMS
 */

// Definisikan konstanta untuk path
define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH . '/app');
define('CORE_PATH', ROOT_PATH . '/core');

// Load autoloader
require_once CORE_PATH . '/Autoloader.php';

// Inisialisasi aplikasi
$app = new \Core\Application();
$app->run(); 