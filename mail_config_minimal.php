<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');

// Incluir configuración del entorno
require_once __DIR__ . '/config/environment_setup.php';
use Config\EnvironmentSetup;

try {
    // Obtener configuración de base de datos dinámicamente
    $dbConfig = EnvironmentSetup::getDatabaseConfig();
    $dsn = "pgsql:host={$dbConfig['host']};port={$dbConfig['port']};dbname={$dbConfig['dbname']}";
    $pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $action = $_GET['action'] ?? 'test';
    
    if ($action === 'get') {
        $stmt = $pdo->query("SELECT * FROM mail_config ORDER BY id DESC LIMIT 1");
        $config = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($config && $config['smtp_password']) {
            $config['smtp_password'] = '••••••••';
        }
        echo json_encode(['success' => true, 'config' => $config]);
    } elseif ($action === 'logs') {
        $limit = (int)($_GET['limit'] ?? 5);
        $stmt = $pdo->prepare("SELECT * FROM mail_logs ORDER BY sent_at DESC LIMIT ?");
        $stmt->execute([$limit]);
        $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'logs' => $logs]);
    } else {
        echo json_encode(['success' => true, 'message' => 'API funcionando']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>