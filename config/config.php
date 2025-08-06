<?php
/**
 * Configuration File
 * 
 * This file contains all the configuration settings for the application
 */

// Require composer autoloader
// require_once __DIR__ . '/../vendor/autoload.php';

// // Database configuration
///////////////////
require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

// Cargar las variables del archivo .env
$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

// Database configuration desde .env
$dbConfig = [
    'driver'   => $_ENV['DB_DRIVER'] ?? 'pgsql',
    'host'     => $_ENV['DB_HOST'] ?? 'localhost',
    'port'     => $_ENV['DB_PORT'] ?? '5432',
    'database' => $_ENV['DB_DATABASE'] ?? 'default_db',
    'username' => $_ENV['DB_USERNAME'] ?? 'default_user',
    'password' => $_ENV['DB_PASSWORD'] ?? ''
];





// Initialize the database connection
\Api\Core\Database::init($dbConfig);

// Set timezone
date_default_timezone_set('America/Asuncion');

// Define API version
define('API_VERSION', '1.0.0');

// Define base path
define('BASE_PATH', dirname(__DIR__));

// Error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);