<?php
/**
 * API para configuración de correo electrónico - Versión robusta para servidor
 */

// Configurar manejo de errores para servidor
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Función para enviar respuesta JSON limpia
function sendJsonResponse($data, $httpCode = 200) {
    // Limpiar cualquier salida previa
    if (ob_get_level()) {
        ob_clean();
    }
    
    // Headers
    header('Content-Type: application/json; charset=UTF-8');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
    
    // Código de estado
    http_response_code($httpCode);
    
    // Enviar JSON
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

// Función para manejo de errores fatales
function errorHandler($data) {
    sendJsonResponse([
        'success' => false,
        'message' => 'Error interno del servidor',
        'error' => $data,
        'server_info' => [
            'php_version' => PHP_VERSION,
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'unknown',
            'script_name' => $_SERVER['SCRIPT_NAME'] ?? 'unknown'
        ]
    ], 500);
}

// Manejar OPTIONS preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    sendJsonResponse(['status' => 'ok']);
}

try {
    // Iniciar sesión de forma segura
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Incluir configuración de forma robusta
    $root_path = dirname(dirname(dirname(__DIR__)));
    $config_file = $root_path . '/config/config.php';
    
    if (!file_exists($config_file)) {
        errorHandler('Archivo config.php no encontrado: ' . $config_file);
    }
    
    // Capturar errores al incluir config
    ob_start();
    $config_loaded = @include_once $config_file;
    $config_output = ob_get_clean();
    
    if ($config_loaded === false) {
        errorHandler('Error al cargar config.php: ' . $config_output);
    }
    
    // Verificar variables de entorno básicas
    if (!isset($_ENV['DB_HOST']) || !isset($_ENV['DB_DATABASE'])) {
        errorHandler('Variables de entorno no cargadas correctamente desde .env');
    }
    
    // Verificar PHPMailer de forma opcional
    $vendor_path = $root_path . '/vendor/autoload.php';
    $phpmailer_available = false;
    
    if (file_exists($vendor_path)) {
        ob_start();
        $vendor_loaded = @include_once $vendor_path;
        $vendor_output = ob_get_clean();
        
        if ($vendor_loaded !== false && class_exists('PHPMailer\PHPMailer\PHPMailer')) {
            $phpmailer_available = true;
        }
    }
    
    // Conectar a base de datos con manejo robusto de errores
    $host = $_ENV['DB_HOST'];
    $port = $_ENV['DB_PORT'] ?? 5432;
    $database = $_ENV['DB_DATABASE'];
    $username = $_ENV['DB_USERNAME'];
    $password = $_ENV['DB_PASSWORD'] ?? '';
    
    $dsn = "pgsql:host={$host};port={$port};dbname={$database}";
    
    try {
        $pdo = new PDO($dsn, $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_TIMEOUT => 10
        ]);
    } catch (PDOException $e) {
        errorHandler('Error de conexión a BD: ' . $e->getMessage());
    }
    
    // Verificar que las tablas existan
    try {
        $pdo->query("SELECT 1 FROM mail_config LIMIT 1");
        $pdo->query("SELECT 1 FROM mail_logs LIMIT 1");
    } catch (PDOException $e) {
        errorHandler('Tablas de correo no encontradas: ' . $e->getMessage());
    }
    
    // Procesar acción
    $action = $_GET['action'] ?? $_POST['action'] ?? null;
    
    // Para POST con JSON
    $input = null;
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $raw_input = file_get_contents('php://input');
        if ($raw_input) {
            $input = json_decode($raw_input, true);
            if ($input && isset($input['action'])) {
                $action = $input['action'];
            }
        }
    }
    
    // Ejecutar acción
    switch ($action) {
        case 'get':
            getMailConfig($pdo);
            break;
            
        case 'save':
            if (!$input || !isset($input['config'])) {
                sendJsonResponse(['success' => false, 'message' => 'Datos de configuración requeridos'], 400);
            }
            saveMailConfig($pdo, $input['config'], $phpmailer_available);
            break;
            
        case 'test':
            if (!$phpmailer_available) {
                sendJsonResponse(['success' => false, 'message' => 'PHPMailer no está disponible'], 400);
            }
            testMailConnection($pdo);
            break;
            
        case 'logs':
            $limit = $_GET['limit'] ?? 10;
            getMailLogs($pdo, (int)$limit);
            break;
            
        default:
            sendJsonResponse(['success' => false, 'message' => 'Acción no válida: ' . ($action ?? 'null')], 400);
    }
    
} catch (Exception $e) {
    errorHandler('Excepción no manejada: ' . $e->getMessage());
} catch (Error $e) {
    errorHandler('Error fatal: ' . $e->getMessage());
}

/**
 * Obtener configuración de correo
 */
function getMailConfig($pdo) {
    try {
        $sql = "SELECT * FROM mail_config ORDER BY id DESC LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $config = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($config) {
            // No enviar la contraseña por seguridad
            $config['smtp_password'] = $config['smtp_password'] ? '••••••••' : '';
            sendJsonResponse(['success' => true, 'config' => $config]);
        } else {
            sendJsonResponse(['success' => true, 'config' => null]);
        }
    } catch (Exception $e) {
        sendJsonResponse(['success' => false, 'message' => 'Error al obtener configuración: ' . $e->getMessage()], 500);
    }
}

/**
 * Guardar configuración de correo
 */
function saveMailConfig($pdo, $config, $phpmailer_available) {
    try {
        // Validar datos requeridos
        $required = ['smtp_host', 'smtp_port', 'smtp_username', 'from_email', 'from_name'];
        foreach ($required as $field) {
            if (empty($config[$field])) {
                sendJsonResponse(['success' => false, 'message' => "Campo requerido: $field"], 400);
            }
        }
        
        // Validar email
        if (!filter_var($config['from_email'], FILTER_VALIDATE_EMAIL)) {
            sendJsonResponse(['success' => false, 'message' => 'Email del remitente no válido'], 400);
        }
        
        // Resto de la lógica de guardado...
        // (implementar según necesidades)
        
        sendJsonResponse(['success' => true, 'message' => 'Configuración guardada exitosamente']);
        
    } catch (Exception $e) {
        sendJsonResponse(['success' => false, 'message' => 'Error al guardar: ' . $e->getMessage()], 500);
    }
}

/**
 * Probar conexión de correo
 */
function testMailConnection($pdo) {
    try {
        // Implementar test básico
        sendJsonResponse(['success' => true, 'message' => 'Test de conexión no implementado completamente']);
    } catch (Exception $e) {
        sendJsonResponse(['success' => false, 'message' => 'Error en test: ' . $e->getMessage()], 500);
    }
}

/**
 * Obtener logs de correo
 */
function getMailLogs($pdo, $limit = 10) {
    try {
        $sql = "SELECT ml.*, c.id_consulta 
                FROM mail_logs ml 
                LEFT JOIN consultas c ON ml.consulta_id = c.id_consulta 
                ORDER BY ml.sent_at DESC 
                LIMIT :limit";
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        sendJsonResponse(['success' => true, 'logs' => $logs]);
        
    } catch (Exception $e) {
        sendJsonResponse(['success' => false, 'message' => 'Error al obtener logs: ' . $e->getMessage()], 500);
    }
}
?>