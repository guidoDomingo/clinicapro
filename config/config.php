<?php
/**
 * Configuration File
 * 
 * This file contains all the configuration settings for the application
 */

// Require composer autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Database configuration
$dbConfig = [
    'driver' => 'pgsql',
    'host' => '181.122.125.143',
    'port' => '5454',
    'database' => 'clinica',
    'username' => 'acmeuser',
    'password' => 'wjstks'
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