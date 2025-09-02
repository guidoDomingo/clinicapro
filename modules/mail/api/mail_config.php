<?php
/**
 * API para configuración de correo electrónico
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

session_start();

// Verificar autenticación (ajustar según tu sistema)
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

require_once __DIR__ . '/../../../config/config.php';

// Cargar PHPMailer
require_once __DIR__ . '/../../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

try {
    $pdo = new PDO('pgsql:host=localhost;port=5432;dbname=clinica', 'postgres', 'admin');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $action = $_GET['action'] ?? ($_POST['action'] ?? null);
    $input = json_decode(file_get_contents('php://input'), true);
    
    if ($input && isset($input['action'])) {
        $action = $input['action'];
    }
    
    switch ($action) {
        case 'get':
            getMailConfig($pdo);
            break;
            
        case 'save':
            saveMailConfig($pdo, $input['config']);
            break;
            
        case 'test':
            testMailConnection($pdo);
            break;
            
        case 'logs':
            getMailLogs($pdo, $_GET['limit'] ?? 10);
            break;
            
        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Acción no válida']);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Error interno del servidor',
        'error' => $e->getMessage()
    ]);
}

/**
 * Obtener configuración de correo
 */
function getMailConfig($pdo) {
    $sql = "SELECT * FROM mail_config ORDER BY id DESC LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $config = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($config) {
        // No enviar la contraseña en la respuesta por seguridad
        $config['smtp_password'] = $config['smtp_password'] ? '••••••••' : '';
        echo json_encode(['success' => true, 'config' => $config]);
    } else {
        echo json_encode(['success' => true, 'config' => null]);
    }
}

/**
 * Guardar configuración de correo
 */
function saveMailConfig($pdo, $config) {
    // Validar datos requeridos
    $required = ['smtp_host', 'smtp_port', 'smtp_username', 'from_email', 'from_name'];
    foreach ($required as $field) {
        if (empty($config[$field])) {
            echo json_encode(['success' => false, 'message' => "Campo requerido: $field"]);
            return;
        }
    }
    
    // Validar email
    if (!filter_var($config['from_email'], FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Email del remitente no válido']);
        return;
    }
    
    if (!empty($config['reply_to_email']) && !filter_var($config['reply_to_email'], FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Email de respuesta no válido']);
        return;
    }
    
    // Verificar si ya existe configuración
    $existingConfig = $pdo->query("SELECT id, smtp_password FROM mail_config ORDER BY id DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
    
    // Si la contraseña viene como asteriscos, mantener la anterior
    if ($config['smtp_password'] === '••••••••' && $existingConfig) {
        $config['smtp_password'] = $existingConfig['smtp_password'];
    }
    
    // Si se está activando esta configuración, desactivar otras
    if ($config['is_active']) {
        $pdo->exec("UPDATE mail_config SET is_active = FALSE");
    }
    
    if ($existingConfig) {
        // Actualizar configuración existente
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
        
        $stmt = $pdo->prepare($sql);
        $config['id'] = $existingConfig['id'];
    } else {
        // Insertar nueva configuración
        $sql = "INSERT INTO mail_config (
                smtp_host, smtp_port, smtp_secure, smtp_auth,
                smtp_username, smtp_password, from_email, from_name,
                reply_to_email, reply_to_name, is_active
            ) VALUES (
                :smtp_host, :smtp_port, :smtp_secure, :smtp_auth,
                :smtp_username, :smtp_password, :from_email, :from_name,
                :reply_to_email, :reply_to_name, :is_active
            )";
        
        $stmt = $pdo->prepare($sql);
    }
    
    // Convertir valores booleanos
    $config['smtp_auth'] = $config['smtp_auth'] ? 'true' : 'false';
    $config['is_active'] = $config['is_active'] ? 'true' : 'false';
    
    // Limpiar valores nulos o vacíos
    $config['smtp_secure'] = empty($config['smtp_secure']) ? null : $config['smtp_secure'];
    $config['reply_to_email'] = empty($config['reply_to_email']) ? null : $config['reply_to_email'];
    $config['reply_to_name'] = empty($config['reply_to_name']) ? null : $config['reply_to_name'];
    
    if ($stmt->execute($config)) {
        echo json_encode(['success' => true, 'message' => 'Configuración guardada exitosamente']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al guardar la configuración']);
    }
}

/**
 * Probar conexión de correo
 */
function testMailConnection($pdo) {
    // Obtener configuración activa
    $sql = "SELECT * FROM mail_config WHERE is_active = TRUE ORDER BY id DESC LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $config = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$config) {
        echo json_encode(['success' => false, 'message' => 'No hay configuración activa']);
        return;
    }
    
    $mail = new PHPMailer(true);
    
    try {
        // Configurar servidor
        $mail->isSMTP();
        $mail->Host = $config['smtp_host'];
        $mail->Port = $config['smtp_port'];
        
        if ($config['smtp_auth'] === 'true' || $config['smtp_auth'] === true) {
            $mail->SMTPAuth = true;
            $mail->Username = $config['smtp_username'];
            $mail->Password = $config['smtp_password'];
        }
        
        if ($config['smtp_secure']) {
            $mail->SMTPSecure = $config['smtp_secure'];
        }
        
        // Configurar remitente
        $mail->setFrom($config['from_email'], $config['from_name']);
        
        // Email de prueba (enviar a sí mismo)
        $mail->addAddress($config['from_email'], 'Prueba de Conexión');
        
        if ($config['reply_to_email']) {
            $mail->addReplyTo($config['reply_to_email'], $config['reply_to_name']);
        }
        
        // Contenido del email
        $mail->isHTML(true);
        $mail->Subject = 'Prueba de Conexión - Sistema Clínica';
        $mail->Body = '
            <h2>✅ Prueba de Conexión Exitosa</h2>
            <p>Este es un email de prueba para verificar la configuración SMTP.</p>
            <p><strong>Fecha:</strong> ' . date('Y-m-d H:i:s') . '</p>
            <p><strong>Servidor:</strong> ' . $config['smtp_host'] . ':' . $config['smtp_port'] . '</p>
            <hr>
            <small>Sistema de Gestión Clínica</small>
        ';
        
        $mail->send();
        
        echo json_encode([
            'success' => true, 
            'message' => 'Email de prueba enviado exitosamente'
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false, 
            'message' => 'Error al enviar email: ' . $mail->ErrorInfo
        ]);
    }
}

/**
 * Obtener logs de correo
 */
function getMailLogs($pdo, $limit = 10) {
    $sql = "SELECT ml.*, c.id_consulta 
            FROM mail_logs ml 
            LEFT JOIN consultas c ON ml.consulta_id = c.id_consulta 
            ORDER BY ml.sent_at DESC 
            LIMIT :limit";
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
    $stmt->execute();
    
    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'logs' => $logs]);
}
?>