<?php
// Test de conexión básica y verificación de tablas
ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');

try {
    $debug = ['step' => 'inicio'];
    
    // Test 1: Extensiones
    $debug['extensions'] = [
        'pdo' => extension_loaded('pdo'),
        'pdo_pgsql' => extension_loaded('pdo_pgsql')
    ];
    
    if (!extension_loaded('pdo_pgsql')) {
        throw new Exception('PDO PostgreSQL extension no disponible');
    }
    
    // Test 2: Conexión
    $dsn = "pgsql:host=181.122.125.143;port=5454;dbname=clinica";
    $pdo = new PDO($dsn, 'acmeuser', 'wjstks', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_TIMEOUT => 5
    ]);
    $debug['conexion'] = 'ok';
    
    // Test 3: Verificar si existen las tablas
    $tables = ['mail_config', 'mail_logs'];
    $debug['tablas'] = [];
    
    foreach ($tables as $table) {
        try {
            $stmt = $pdo->query("SELECT COUNT(*) FROM $table");
            $count = $stmt->fetchColumn();
            $debug['tablas'][$table] = ['existe' => true, 'registros' => $count];
        } catch (Exception $e) {
            $debug['tablas'][$table] = ['existe' => false, 'error' => $e->getMessage()];
        }
    }
    
    // Test 4: Estructura de mail_config si existe
    if ($debug['tablas']['mail_config']['existe']) {
        try {
            $stmt = $pdo->query("SELECT column_name FROM information_schema.columns WHERE table_name = 'mail_config'");
            $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
            $debug['estructura_mail_config'] = $columns;
        } catch (Exception $e) {
            $debug['estructura_error'] = $e->getMessage();
        }
    }
    
    echo json_encode(['success' => true, 'debug' => $debug]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'debug' => $debug ?? []
    ]);
}
?>