<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');

try {
    $pdo = new PDO("pgsql:host=181.122.125.143;port=5454;dbname=clinica", 'acmeuser', 'wjstks');
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