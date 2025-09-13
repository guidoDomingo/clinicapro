<?php
// Diagnóstico específico para el problema de mail_config.php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Incluir configuración del entorno
require_once __DIR__ . '/../../../config/environment_setup.php';
use Config\EnvironmentSetup;

// Capturar todos los errores
ob_start();

try {
    // Test 1: Headers básicos
    header('Content-Type: application/json; charset=UTF-8');
    header('Access-Control-Allow-Origin: *');
    
    $debug = [];
    $debug['step'] = 'inicio';
    $debug['php_version'] = PHP_VERSION;
    $debug['extensions'] = [
        'pdo' => extension_loaded('pdo'),
        'pdo_pgsql' => extension_loaded('pdo_pgsql')
    ];
    
    // Test 2: PDO disponible
    if (!extension_loaded('pdo_pgsql')) {
        throw new Exception('Extension pdo_pgsql no está disponible');
    }
    $debug['step'] = 'extensiones_ok';
    
    // Test 3: Conexión
    // Obtener configuración de base de datos dinámicamente
    $dbConfig = EnvironmentSetup::getDatabaseConfig();
    $dsn = "pgsql:host={$dbConfig['host']};port={$dbConfig['port']};dbname={$dbConfig['dbname']}";
    $debug['dsn'] = $dsn;
    
    $pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_TIMEOUT => 5
    ]);
    $debug['step'] = 'conexion_ok';
    
    // Test 4: Verificar tabla
    $pdo->query("SELECT 1 FROM mail_config LIMIT 1");
    $debug['step'] = 'tabla_ok';
    
    // Test 5: Consulta real
    $action = $_GET['action'] ?? 'test';
    $debug['action'] = $action;
    
    if ($action === 'get') {
        $sql = "SELECT * FROM mail_config ORDER BY id DESC LIMIT 1";
        $stmt = $pdo->query($sql);
        $config = $stmt->fetch(PDO::FETCH_ASSOC);
        $debug['step'] = 'consulta_ok';
        $debug['config_found'] = $config ? true : false;
        
        if ($config) {
            $config['smtp_password'] = '••••••••';
        }
        
        // Limpiar buffer de salida
        ob_clean();
        echo json_encode(['success' => true, 'config' => $config, 'debug' => $debug]);
        exit;
    }
    
    if ($action === 'logs') {
        $limit = (int)($_GET['limit'] ?? 10);
        $sql = "SELECT * FROM mail_logs ORDER BY sent_at DESC LIMIT " . $limit;
        $stmt = $pdo->query($sql);
        $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $debug['step'] = 'logs_ok';
        $debug['logs_count'] = count($logs);
        
        // Limpiar buffer de salida
        ob_clean();
        echo json_encode(['success' => true, 'logs' => $logs, 'debug' => $debug]);
        exit;
    }
    
    // Respuesta por defecto
    $debug['step'] = 'default_ok';
    ob_clean();
    echo json_encode(['success' => true, 'message' => 'API funcionando', 'action' => $action, 'debug' => $debug]);
    
} catch (Exception $e) {
    // Capturar cualquier salida previa
    $output = ob_get_clean();
    
    // Respuesta de error
    echo json_encode([
        'success' => false, 
        'error' => $e->getMessage(),
        'file' => basename($e->getFile()),
        'line' => $e->getLine(),
        'debug' => $debug ?? [],
        'output_buffer' => $output
    ]);
} catch (Error $e) {
    // Errores fatales
    $output = ob_get_clean();
    
    echo json_encode([
        'success' => false, 
        'error' => 'Fatal error: ' . $e->getMessage(),
        'file' => basename($e->getFile()),
        'line' => $e->getLine(),
        'debug' => $debug ?? [],
        'output_buffer' => $output
    ]);
}
?>