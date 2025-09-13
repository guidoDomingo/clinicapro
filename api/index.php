<?php
/**
 * API Router
 * 
 * This file serves as the main entry point for the API
 * It handles routing and dispatches requests to the appropriate controllers
 */

// Asegurarse de que no haya salida antes de establecer encabezados
ob_start();

// Configurar la visualización de errores para desarrollo (solo si no es producción)
if (strpos($_SERVER['SERVER_NAME'], '.local') !== false || 
    strpos($_SERVER['SERVER_NAME'], '.test') !== false ||
    $_SERVER['SERVER_NAME'] === 'localhost') {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    error_reporting(E_ERROR | E_PARSE);
}

// Definir la ruta base del proyecto
define('BASE_DIR', dirname(dirname(__FILE__)));
define('API_DIR', dirname(__FILE__));

// Inicializar todos los componentes de la API
require_once API_DIR . '/core/ApiInitializer.php';
use Api\Core\ApiInitializer;
use Api\Core\ApiConfig;

// Incluir clases necesarias
require_once BASE_DIR . "/api/core/Logger.php";
require_once BASE_DIR . "/api/core/Response.php";

// Iniciar sesión para todas las solicitudes a la API
if (session_status() === PHP_SESSION_NONE) {
    // Configurar las sesiones para compartir entre dominios
    session_set_cookie_params([
        'lifetime' => 3600,
        'path' => '/',
        'domain' => '.clinica.test',
        'secure' => false,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    
    session_start();
}

// Registrar información de depuración usando el sistema dinámico de logs
ApiConfig::log("API Request - URI: " . $_SERVER['REQUEST_URI']);
ApiConfig::log("API Request - Method: " . $_SERVER['REQUEST_METHOD']);
ApiConfig::log("API Request - Content-Type: " . ($_SERVER['CONTENT_TYPE'] ?? 'none'));
ApiConfig::logSession("Session ID: " . session_id() . ", Data: " . json_encode($_SESSION));

// Set headers for API responses
header('Content-Type: application/json');
// Permitir solicitudes desde el mismo origen o desde dominios específicos
$allowedOrigins = [
    'http://localhost',
    'http://localhost:80',
    'http://clinica.test',
    'http://clinica.local'
];
$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';

if (in_array($origin, $allowedOrigins)) {
    header('Access-Control-Allow-Origin: ' . $origin);
} else {
    header('Access-Control-Allow-Origin: *'); // Fallback para desarrollo
}

header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-CSRF-TOKEN');
header('Access-Control-Allow-Credentials: true');

// Handle preflight OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Include necessary files
require_once __DIR__ . '/core/Router.php';
require_once __DIR__ . '/core/Response.php';
require_once __DIR__ . '/core/Database.php';
require_once __DIR__ . '/core/Model.php';

// Include all controllers
require_once __DIR__ . '/controllers/LocationController.php';
require_once __DIR__ . '/controllers/RhPersonController.php';
require_once __DIR__ . '/controllers/EspecialidadController.php';
require_once __DIR__ . '/controllers/SysRegisterController.php';
require_once __DIR__ . '/controllers/SysUserController.php';
require_once __DIR__ . '/controllers/SysRoleController.php';
require_once __DIR__ . '/controllers/SysPermissionController.php';

// Include all models
require_once __DIR__ . '/models/RhPerson.php';
require_once __DIR__ . '/models/Especialidad.php';
require_once __DIR__ . '/models/SysRegister.php';
require_once __DIR__ . '/models/SysUser.php';
require_once __DIR__ . '/models/SysRole.php';
require_once __DIR__ . '/models/SysPermission.php';

// Include configuration after core classes are loaded
require_once __DIR__ . '/../config/config.php';

// Initialize the Router
$router = new \Api\Core\Router();

// Include routes
require_once __DIR__ . '/routes/api.php';

// Get the request URI and method
$requestUri = $_SERVER['REQUEST_URI'];

// Log the original request URI for debugging
ApiConfig::log("Original REQUEST_URI: " . $requestUri);

// Remover el prefijo completo del proyecto de la URI
$scriptName = dirname($_SERVER['SCRIPT_NAME']); // /clinica/api
$basePath = dirname($scriptName); // /clinica
ApiConfig::log("Script name: " . $_SERVER['SCRIPT_NAME'] . ", Base path: " . $basePath);

// Si la URI contiene el basePath, removerlo
if (strpos($requestUri, $basePath . '/api/') === 0) {
    $requestUri = substr($requestUri, strlen($basePath . '/api/'));
} elseif (strpos($requestUri, '/api/') === 0) {
    $requestUri = substr($requestUri, 5); // Remover "/api/"
}

// Remover leading slash si existe
$requestUri = ltrim($requestUri, '/');

// Remover query string si existe
if (($pos = strpos($requestUri, '?')) !== false) {
    $requestUri = substr($requestUri, 0, $pos);
}

// Usar como fallback el parámetro route
if (empty($requestUri)) {
    $requestUri = isset($_GET['route']) ? $_GET['route'] : '';
}

ApiConfig::log("Processed REQUEST_URI: " . $requestUri);

$requestMethod = $_SERVER['REQUEST_METHOD'];


// Process the request
try {
    // Capturar cualquier salida de buffer para evitar que rompa el JSON
    ob_start();
    $router->dispatch($requestUri, $requestMethod);
    // Si llegamos aquí sin enviar una respuesta, descartamos cualquier salida y enviamos un error
    $output = ob_get_clean();
    if (!headers_sent()) {
        // Si no se han enviado encabezados, significa que el controlador no respondió correctamente
        ApiConfig::log("Controller did not send response. Output: " . $output, 'WARNING');
        \Api\Core\Response::error([
            'message' => ['No se recibió respuesta del controlador', $requestUri, $requestMethod],
            'debug_output' => substr($output, 0, 500), // Incluir parte de la salida para depuración
            'codes' => 500
        ], 500);
    }
} catch (\Exception $e) {
    // Capturar cualquier salida para evitar que rompa el JSON
    ob_end_clean();
    ApiConfig::log("Exception: " . $e->getMessage(), 'ERROR');
    \Api\Core\Response::error([
        'message' => [$e->getMessage(), $requestUri, $requestMethod],
        'codes' => $e->getCode() ?: 500
    ], $e->getCode() ?: 500);
}