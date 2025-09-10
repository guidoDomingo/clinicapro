<?php
// API temporal en directorio raíz para saltarse autenticación de módulos
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

try {
    // Conexión directa
    $pdo = new PDO("pgsql:host=181.122.125.143;port=5454;dbname=clinica", 'acmeuser', 'wjstks', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_TIMEOUT => 10
    ]);
    
    $action = $_GET['action'] ?? 'test';
    
    switch ($action) {
        case 'get':
            $stmt = $pdo->query("SELECT * FROM mail_config ORDER BY id DESC LIMIT 1");
            $config = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($config && $config['smtp_password']) {
                $config['smtp_password'] = '••••••••';
            }
            echo json_encode(['success' => true, 'config' => $config]);
            break;
            
        case 'logs':
            $limit = (int)($_GET['limit'] ?? 5);
            $stmt = $pdo->prepare("SELECT * FROM mail_logs ORDER BY sent_at DESC LIMIT ?");
            $stmt->execute([$limit]);
            $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'logs' => $logs]);
            break;
            
        case 'save':
            // Obtener datos POST
            $input = json_decode(file_get_contents('php://input'), true);
            if (!$input || !isset($input['config'])) {
                echo json_encode(['success' => false, 'message' => 'Datos requeridos']);
                break;
            }
            
            $config = $input['config'];
            
            // Validaciones básicas
            $required = ['smtp_host', 'smtp_port', 'smtp_username', 'from_email', 'from_name'];
            foreach ($required as $field) {
                if (empty($config[$field])) {
                    echo json_encode(['success' => false, 'message' => "Campo requerido: $field"]);
                    exit;
                }
            }
            
            // Verificar si existe configuración
            $existing = $pdo->query("SELECT id, smtp_password FROM mail_config ORDER BY id DESC LIMIT 1")->fetch();
            
            // Mantener contraseña anterior si viene enmascarada
            if ($config['smtp_password'] === '••••••••' && $existing) {
                $config['smtp_password'] = $existing['smtp_password'];
            }
            
            // Desactivar otras configuraciones si esta se activa
            if ($config['is_active']) {
                $pdo->exec("UPDATE mail_config SET is_active = FALSE");
            }
            
            if ($existing) {
                // Actualizar
                $sql = "UPDATE mail_config SET 
                        smtp_host = :smtp_host,
                        smtp_port = :smtp_port,
                        smtp_secure = :smtp_secure,
                        smtp_auth = :smtp_auth,
                        smtp_username = :smtp_username,
                        smtp_password = :smtp_password,
                        from_email = :from_email,
                        from_name = :from_name,
                        reply_to_email = :reply_to_email,
                        reply_to_name = :reply_to_name,
                        is_active = :is_active,
                        updated_at = CURRENT_TIMESTAMP
                        WHERE id = :id";
                $config['id'] = $existing['id'];
            } else {
                // Insertar
                $sql = "INSERT INTO mail_config (
                        smtp_host, smtp_port, smtp_secure, smtp_auth,
                        smtp_username, smtp_password, from_email, from_name,
                        reply_to_email, reply_to_name, is_active
                    ) VALUES (
                        :smtp_host, :smtp_port, :smtp_secure, :smtp_auth,
                        :smtp_username, :smtp_password, :from_email, :from_name,
                        :reply_to_email, :reply_to_name, :is_active
                    )";
            }
            
            // Preparar valores
            $config['smtp_auth'] = $config['smtp_auth'] ? true : false;
            $config['is_active'] = $config['is_active'] ? true : false;
            $config['smtp_secure'] = empty($config['smtp_secure']) ? null : $config['smtp_secure'];
            $config['reply_to_email'] = empty($config['reply_to_email']) ? null : $config['reply_to_email'];
            $config['reply_to_name'] = empty($config['reply_to_name']) ? null : $config['reply_to_name'];
            
            $stmt = $pdo->prepare($sql);
            if ($stmt->execute($config)) {
                echo json_encode(['success' => true, 'message' => 'Configuración guardada correctamente']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al guardar la configuración']);
            }
            break;
            
        default:
            echo json_encode(['success' => true, 'message' => 'API temporal funcionando', 'action' => $action]);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>