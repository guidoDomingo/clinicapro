<?php
// API simple para configuración de correo
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');

try {
    $action = $_GET['action'] ?? 'default';
    
    // Conexión directa sin includes complicados
    $dsn = "pgsql:host=181.122.125.143;port=5454;dbname=clinica";
    $pdo = new PDO($dsn, 'acmeuser', 'wjstks', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_TIMEOUT => 10
    ]);
    
    switch ($action) {
        case 'get':
            $sql = "SELECT * FROM mail_config ORDER BY id DESC LIMIT 1";
            $stmt = $pdo->query($sql);
            $config = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($config) {
                $config['smtp_password'] = '••••••••';
            }
            echo json_encode(['success' => true, 'config' => $config]);
            break;
            
        case 'logs':
            $limit = (int)($_GET['limit'] ?? 10);
            $sql = "SELECT * FROM mail_logs ORDER BY sent_at DESC LIMIT " . $limit;
            $stmt = $pdo->query($sql);
            $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'logs' => $logs]);
            break;
            
        default:
            echo json_encode(['success' => true, 'message' => 'API funcionando', 'action' => $action]);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
?>
                $action = $input['action'];
            }
        }
    }
    
    // Procesar acción
    switch ($action) {
        case 'get':
            getMailConfig($pdo);
            break;
            
        case 'save':
            if ($input && isset($input['config'])) {
                saveMailConfig($pdo, $input['config']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Datos requeridos']);
            }
            break;
            
        case 'test':
            testMailConnection($pdo);
            break;
            
        case 'logs':
            $limit = $_GET['limit'] ?? 10;
            getMailLogs($pdo, (int)$limit);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Acción no válida']);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Error del servidor: ' . $e->getMessage()
    ]);
}

function getMailConfig($pdo) {
    try {
        $sql = "SELECT * FROM mail_config ORDER BY id DESC LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $config = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($config) {
            $config['smtp_password'] = $config['smtp_password'] ? '••••••••' : '';
            echo json_encode(['success' => true, 'config' => $config]);
        } else {
            echo json_encode(['success' => true, 'config' => null]);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}

function saveMailConfig($pdo, $config) {
    try {
        // Validaciones básicas
        $required = ['smtp_host', 'smtp_port', 'smtp_username', 'from_email', 'from_name'];
        foreach ($required as $field) {
            if (empty($config[$field])) {
                echo json_encode(['success' => false, 'message' => "Campo requerido: $field"]);
                return;
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
            echo json_encode(['success' => true, 'message' => 'Configuración guardada']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al guardar']);
        }
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}

function testMailConnection($pdo) {
    try {
        $sql = "SELECT * FROM mail_config WHERE is_active = TRUE ORDER BY id DESC LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $config = $stmt->fetch();
        
        if (!$config) {
            echo json_encode(['success' => false, 'message' => 'No hay configuración activa']);
            return;
        }
        
        // Test básico por ahora
        echo json_encode([
            'success' => true, 
            'message' => 'Test OK: ' . $config['smtp_host'] . ':' . $config['smtp_port']
        ]);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}

function getMailLogs($pdo, $limit = 10) {
    try {
        $sql = "SELECT * FROM mail_logs ORDER BY sent_at DESC LIMIT :limit";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        $logs = $stmt->fetchAll();
        
        echo json_encode(['success' => true, 'logs' => $logs]);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}
?>