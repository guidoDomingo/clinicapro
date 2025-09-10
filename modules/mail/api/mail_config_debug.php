<?php
/**
 * Diagnóstico simple para mail_config.php
 */

// Headers básicos
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');

// Log de inicio
error_log('DEBUG: Iniciando mail_config.php');

try {
    error_log('DEBUG: Punto 1 - Headers enviados');
    
    // Test 1: Respuesta básica
    $action = $_GET['action'] ?? 'test';
    error_log('DEBUG: Punto 2 - Acción: ' . $action);
    
    if ($action === 'basic') {
        echo json_encode(['success' => true, 'message' => 'Respuesta básica funcionando']);
        exit;
    }
    
    // Test 2: Cargar config
    $root_path = dirname(dirname(dirname(__DIR__)));
    error_log('DEBUG: Punto 3 - Root path: ' . $root_path);
    
    $config_file = $root_path . '/config/config.php';
    error_log('DEBUG: Punto 4 - Config file: ' . $config_file);
    
    if (!file_exists($config_file)) {
        echo json_encode(['success' => false, 'error' => 'Config file no existe']);
        exit;
    }
    
    error_log('DEBUG: Punto 5 - Cargando config');
    require_once $config_file;
    error_log('DEBUG: Punto 6 - Config cargado');
    
    // Test 3: Variables de entorno
    $host = $_ENV['DB_HOST'] ?? null;
    $database = $_ENV['DB_DATABASE'] ?? null;
    
    if (!$host || !$database) {
        echo json_encode(['success' => false, 'error' => 'Variables ENV no disponibles', 'host' => $host, 'db' => $database]);
        exit;
    }
    
    error_log('DEBUG: Punto 7 - ENV vars OK');
    
    // Test 4: Conexión BD
    $port = $_ENV['DB_PORT'] ?? 5432;
    $username = $_ENV['DB_USERNAME'] ?? 'postgres';
    $password = $_ENV['DB_PASSWORD'] ?? '';
    
    $dsn = "pgsql:host={$host};port={$port};dbname={$database}";
    error_log('DEBUG: Punto 8 - Conectando BD: ' . $dsn);
    
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_TIMEOUT => 5
    ]);
    
    error_log('DEBUG: Punto 9 - BD conectada');
    
    // Test 5: Verificar tablas
    $pdo->query("SELECT 1 FROM mail_config LIMIT 1");
    error_log('DEBUG: Punto 10 - Tabla mail_config OK');
    
    $pdo->query("SELECT 1 FROM mail_logs LIMIT 1");
    error_log('DEBUG: Punto 11 - Tabla mail_logs OK');
    
    // Test 6: Procesar acciones
    switch ($action) {
        case 'get':
            error_log('DEBUG: Punto 12 - Procesando GET');
            $sql = "SELECT * FROM mail_config ORDER BY id DESC LIMIT 1";
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $config = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($config) {
                $config['smtp_password'] = '••••••••';
            }
            
            echo json_encode(['success' => true, 'config' => $config, 'debug' => 'GET completado']);
            break;
            
        case 'logs':
            error_log('DEBUG: Punto 13 - Procesando LOGS');
            $limit = $_GET['limit'] ?? 10;
            $sql = "SELECT * FROM mail_logs ORDER BY sent_at DESC LIMIT :limit";
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->execute();
            $logs = $stmt->fetchAll();
            
            echo json_encode(['success' => true, 'logs' => $logs, 'debug' => 'LOGS completado']);
            break;
            
        default:
            echo json_encode(['success' => true, 'message' => 'Diagnóstico completado', 'action' => $action]);
    }
    
    error_log('DEBUG: Punto 14 - Proceso completado');
    
} catch (Exception $e) {
    error_log('DEBUG: ERROR en punto - ' . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
} catch (Error $e) {
    error_log('DEBUG: FATAL ERROR - ' . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'error' => 'Fatal error: ' . $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
}
?>