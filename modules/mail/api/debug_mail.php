<?php
/**
 * API simplificada para configuración de correo (para debugging)
 */

// Mostrar errores en pantalla para debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Función para output JSON seguro
function safe_json_output($data) {
    while (ob_get_level()) {
        ob_end_clean();
    }
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode($data, JSON_PRETTY_PRINT);
    exit;
}

// Función para manejo de errores
function handle_error($message, $details = []) {
    safe_json_output([
        'success' => false,
        'message' => $message,
        'debug' => $details,
        'timestamp' => date('Y-m-d H:i:s'),
        'php_version' => PHP_VERSION
    ]);
}

try {
    // Test básico
    if (!function_exists('json_encode')) {
        handle_error('JSON extension not available');
    }

    // Verificar directorio
    $root_path = dirname(dirname(dirname(__DIR__)));
    $debug_info = [
        'current_dir' => __DIR__,
        'root_path' => $root_path,
        'script_name' => $_SERVER['SCRIPT_NAME'] ?? 'unknown'
    ];

    // Verificar config
    $config_file = $root_path . '/config/config.php';
    if (!file_exists($config_file)) {
        handle_error('Config file not found', array_merge($debug_info, [
            'config_file' => $config_file,
            'files_in_root' => is_dir($root_path) ? scandir($root_path) : 'directory not accessible'
        ]));
    }

    // Cargar configuración
    require_once $config_file;

    // Verificar variables de entorno
    if (!isset($_ENV['DB_HOST'])) {
        handle_error('Environment variables not loaded', array_merge($debug_info, [
            'env_file' => $root_path . '/.env',
            'env_exists' => file_exists($root_path . '/.env'),
            '$_ENV keys' => array_keys($_ENV ?? [])
        ]));
    }

    $action = $_GET['action'] ?? 'test';

    switch ($action) {
        case 'test':
            safe_json_output([
                'success' => true,
                'message' => 'Basic test passed',
                'config' => [
                    'host' => $_ENV['DB_HOST'],
                    'port' => $_ENV['DB_PORT'] ?? 5432,
                    'database' => $_ENV['DB_DATABASE'],
                    'username' => $_ENV['DB_USERNAME']
                ],
                'php_version' => PHP_VERSION,
                'timestamp' => date('Y-m-d H:i:s'),
                'debug' => $debug_info
            ]);
            break;

        case 'db_test':
            // Test de conexión a base de datos
            $host = $_ENV['DB_HOST'];
            $port = $_ENV['DB_PORT'] ?? 5432;
            $database = $_ENV['DB_DATABASE'];
            $username = $_ENV['DB_USERNAME'];
            $password = $_ENV['DB_PASSWORD'];
            
            $dsn = "pgsql:host={$host};port={$port};dbname={$database}";
            
            try {
                $pdo = new PDO($dsn, $username, $password, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_TIMEOUT => 5
                ]);
                
                // Test simple query
                $stmt = $pdo->query("SELECT version()");
                $version = $stmt->fetchColumn();
                
                safe_json_output([
                    'success' => true,
                    'message' => 'Database connection successful',
                    'database_version' => $version,
                    'dsn' => $dsn
                ]);
                
            } catch (PDOException $e) {
                handle_error('Database connection failed', [
                    'dsn' => $dsn,
                    'error' => $e->getMessage(),
                    'code' => $e->getCode()
                ]);
            }
            break;

        case 'table_test':
            // Test de tabla mail_config
            $host = $_ENV['DB_HOST'];
            $port = $_ENV['DB_PORT'] ?? 5432;
            $database = $_ENV['DB_DATABASE'];
            $username = $_ENV['DB_USERNAME'];
            $password = $_ENV['DB_PASSWORD'];
            
            $dsn = "pgsql:host={$host};port={$port};dbname={$database}";
            
            try {
                $pdo = new PDO($dsn, $username, $password, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
                ]);
                
                // Verificar si existe la tabla
                $stmt = $pdo->query("SELECT COUNT(*) FROM mail_config");
                $count = $stmt->fetchColumn();
                
                safe_json_output([
                    'success' => true,
                    'message' => 'Table mail_config exists',
                    'record_count' => $count
                ]);
                
            } catch (PDOException $e) {
                handle_error('Table test failed', [
                    'error' => $e->getMessage(),
                    'code' => $e->getCode()
                ]);
            }
            break;

        default:
            safe_json_output([
                'success' => false,
                'message' => 'Invalid action',
                'available_actions' => ['test', 'db_test', 'table_test']
            ]);
    }

} catch (Throwable $e) {
    handle_error('Fatal error: ' . $e->getMessage(), [
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString()
    ]);
}
?>