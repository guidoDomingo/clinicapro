<?php
/**
 * API simplificada para configuración de correo (para debugging)
 */

// Configurar manejo de errores
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Limpiar buffer de salida
while (ob_get_level()) {
    ob_end_clean();
}

header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

try {
    // Incluir configuración
    $root_path = dirname(dirname(dirname(__DIR__)));
    $config_file = $root_path . '/config/config.php';
    
    if (!file_exists($config_file)) {
        throw new Exception("Config file not found: $config_file");
    }
    
    require_once $config_file;

    // Verificar variables de entorno
    if (!isset($_ENV['DB_HOST'])) {
        throw new Exception('Environment variables not loaded');
    }

    // Conectar a base de datos
    $host = $_ENV['DB_HOST'];
    $port = $_ENV['DB_PORT'] ?? 5432;
    $database = $_ENV['DB_DATABASE'];
    $username = $_ENV['DB_USERNAME'];
    $password = $_ENV['DB_PASSWORD'];
    
    $dsn = "pgsql:host={$host};port={$port};dbname={$database}";
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_TIMEOUT => 10
    ]);

    $action = $_GET['action'] ?? 'test';

    switch ($action) {
        case 'test':
            echo json_encode([
                'success' => true,
                'message' => 'Conexión exitosa',
                'config' => [
                    'host' => $host,
                    'port' => $port,
                    'database' => $database,
                    'username' => $username
                ],
                'php_version' => PHP_VERSION,
                'timestamp' => date('Y-m-d H:i:s')
            ]);
            break;

        case 'get':
            $sql = "SELECT * FROM mail_config ORDER BY id DESC LIMIT 1";
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $config = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($config && isset($config['smtp_password'])) {
                $config['smtp_password'] = '••••••••';
            }
            
            echo json_encode([
                'success' => true,
                'config' => $config,
                'table_exists' => true
            ]);
            break;

        case 'logs':
            $limit = $_GET['limit'] ?? 5;
            $sql = "SELECT * FROM mail_logs ORDER BY sent_at DESC LIMIT :limit";
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->execute();
            $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'logs' => $logs
            ]);
            break;

        default:
            echo json_encode([
                'success' => false,
                'message' => 'Invalid action'
            ]);
    }

} catch (PDOException $e) {
    error_log('Database Error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database connection error',
        'error' => $e->getMessage(),
        'debug' => [
            'dsn' => isset($dsn) ? $dsn : 'not set',
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]
    ]);
} catch (Exception $e) {
    error_log('General Error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'debug' => [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'root_path' => isset($root_path) ? $root_path : 'not set',
            'config_file' => isset($config_file) ? $config_file : 'not set'
        ]
    ]);
}
?>