<?php
/**
 * Verificación de configuración de correo
 * Archivo para diagnosticar problemas de configuración de correo
 */

header('Content-Type: application/json; charset=UTF-8');

$checks = [];
$allPassed = true;

// 1. Verificar que PHPMailer esté instalado
$checks['phpmailer'] = [
    'name' => 'PHPMailer',
    'status' => false,
    'message' => ''
];

$vendor_path = dirname(dirname(dirname(__DIR__))) . '/vendor/autoload.php';
if (file_exists($vendor_path)) {
    require_once $vendor_path;
    
    if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
        $checks['phpmailer']['status'] = true;
        $checks['phpmailer']['message'] = 'PHPMailer está instalado correctamente';
    } else {
        $checks['phpmailer']['message'] = 'PHPMailer no se puede cargar';
        $allPassed = false;
    }
} else {
    $checks['phpmailer']['message'] = 'Vendor autoload no encontrado. Ejecute: composer install';
    $allPassed = false;
}

// 2. Verificar configuración de entorno
$checks['env_config'] = [
    'name' => 'Configuración de Entorno',
    'status' => false,
    'message' => ''
];

$config_path = dirname(dirname(dirname(__DIR__))) . '/config/config.php';
if (file_exists($config_path)) {
    require_once $config_path;
    
    if (isset($_ENV['DB_HOST']) && isset($_ENV['DB_DATABASE'])) {
        $checks['env_config']['status'] = true;
        $checks['env_config']['message'] = 'Variables de entorno cargadas correctamente';
    } else {
        $checks['env_config']['message'] = 'Variables de entorno no están configuradas';
        $allPassed = false;
    }
} else {
    $checks['env_config']['message'] = 'Archivo config.php no encontrado';
    $allPassed = false;
}

// 3. Verificar conexión a base de datos
$checks['database'] = [
    'name' => 'Conexión a Base de Datos',
    'status' => false,
    'message' => ''
];

try {
    $host = $_ENV['DB_HOST'] ?? 'localhost';
    $port = $_ENV['DB_PORT'] ?? 5432;
    $database = $_ENV['DB_DATABASE'] ?? 'clinica';
    $username = $_ENV['DB_USERNAME'] ?? 'postgres';
    $password = $_ENV['DB_PASSWORD'] ?? 'admin';
    
    $dsn = "pgsql:host={$host};port={$port};dbname={$database}";
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $checks['database']['status'] = true;
    $checks['database']['message'] = 'Conexión a base de datos exitosa';
} catch (Exception $e) {
    $checks['database']['message'] = 'Error de conexión: ' . $e->getMessage();
    $allPassed = false;
}

// 4. Verificar tabla mail_config
$checks['mail_table'] = [
    'name' => 'Tabla mail_config',
    'status' => false,
    'message' => ''
];

if ($checks['database']['status']) {
    try {
        $stmt = $pdo->query("SELECT COUNT(*) FROM mail_config");
        $count = $stmt->fetchColumn();
        
        $checks['mail_table']['status'] = true;
        $checks['mail_table']['message'] = "Tabla existe con {$count} configuraciones";
    } catch (Exception $e) {
        $checks['mail_table']['message'] = 'Tabla mail_config no existe: ' . $e->getMessage();
        $allPassed = false;
    }
}

// 5. Verificar extensiones PHP necesarias
$checks['php_extensions'] = [
    'name' => 'Extensiones PHP',
    'status' => false,
    'message' => ''
];

$required_extensions = ['openssl', 'sockets'];
$missing_extensions = [];

foreach ($required_extensions as $ext) {
    if (!extension_loaded($ext)) {
        $missing_extensions[] = $ext;
    }
}

if (empty($missing_extensions)) {
    $checks['php_extensions']['status'] = true;
    $checks['php_extensions']['message'] = 'Todas las extensiones requeridas están disponibles';
} else {
    $checks['php_extensions']['message'] = 'Extensiones faltantes: ' . implode(', ', $missing_extensions);
    $allPassed = false;
}

// 6. Verificar permisos de directorio
$checks['permissions'] = [
    'name' => 'Permisos de Archivos',
    'status' => false,
    'message' => ''
];

$logs_dir = dirname(dirname(dirname(__DIR__))) . '/logs';
if (!is_dir($logs_dir)) {
    @mkdir($logs_dir, 0755, true);
}

if (is_writable($logs_dir)) {
    $checks['permissions']['status'] = true;
    $checks['permissions']['message'] = 'Directorio de logs es escribible';
} else {
    $checks['permissions']['message'] = 'Directorio de logs no es escribible: ' . $logs_dir;
    $allPassed = false;
}

// Respuesta final
echo json_encode([
    'success' => $allPassed,
    'message' => $allPassed ? 'Todas las verificaciones pasaron' : 'Algunas verificaciones fallaron',
    'checks' => $checks,
    'summary' => [
        'total' => count($checks),
        'passed' => array_sum(array_column($checks, 'status')),
        'failed' => count($checks) - array_sum(array_column($checks, 'status'))
    ]
]);
?>