<?php
// Test ultra simple - paso a paso
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Incluir configuración del entorno
require_once __DIR__ . '/../../../config/environment_setup.php';
use Config\EnvironmentSetup;

// Función para responder y salir
function respond($data) {
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode($data);
    exit;
}

// Test 1: PHP básico funciona
if (!isset($_GET['step'])) {
    respond(['success' => true, 'message' => 'PHP básico funcionando', 'step' => 0]);
}

$step = (int)$_GET['step'];

// Test 2: Extensiones
if ($step === 1) {
    $extensions = [
        'pdo' => extension_loaded('pdo'),
        'pdo_pgsql' => extension_loaded('pdo_pgsql'),
        'json' => extension_loaded('json')
    ];
    respond(['success' => true, 'extensions' => $extensions, 'step' => 1]);
}

// Test 3: Conexión BD
if ($step === 2) {
    try {
        // Obtener configuración de base de datos dinámicamente
        $dbConfig = EnvironmentSetup::getDatabaseConfig();
        $dsn = "pgsql:host={$dbConfig['host']};port={$dbConfig['port']};dbname={$dbConfig['dbname']}";
        $pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password'], [PDO::ATTR_TIMEOUT => 5]);
        respond(['success' => true, 'message' => 'BD conectada', 'step' => 2]);
    } catch (Exception $e) {
        respond(['success' => false, 'error' => $e->getMessage(), 'step' => 2]);
    }
}

// Test 4: Consulta BD
if ($step === 3) {
    try {
        // Obtener configuración de base de datos dinámicamente
        $dbConfig = EnvironmentSetup::getDatabaseConfig();
        $dsn = "pgsql:host={$dbConfig['host']};port={$dbConfig['port']};dbname={$dbConfig['dbname']}";
        $pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password'], [PDO::ATTR_TIMEOUT => 5]);
        $result = $pdo->query("SELECT COUNT(*) as count FROM mail_config")->fetch();
        respond(['success' => true, 'count' => $result['count'], 'step' => 3]);
    } catch (Exception $e) {
        respond(['success' => false, 'error' => $e->getMessage(), 'step' => 3]);
    }
}

// Test 5: Acción GET
if ($step === 4) {
    try {
        // Obtener configuración de base de datos dinámicamente
        $dbConfig = EnvironmentSetup::getDatabaseConfig();
        $dsn = "pgsql:host={$dbConfig['host']};port={$dbConfig['port']};dbname={$dbConfig['dbname']}";
        $pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password'], [PDO::ATTR_TIMEOUT => 5]);
        $sql = "SELECT * FROM mail_config ORDER BY id DESC LIMIT 1";
        $stmt = $pdo->query($sql);
        $config = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($config) {
            $config['smtp_password'] = '••••••••';
        }
        respond(['success' => true, 'config' => $config, 'step' => 4]);
    } catch (Exception $e) {
        respond(['success' => false, 'error' => $e->getMessage(), 'step' => 4]);
    }
}

respond(['success' => false, 'error' => 'Step no válido', 'step' => $step]);
?>