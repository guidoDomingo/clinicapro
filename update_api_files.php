<?php
/**
 * Script para actualizar archivos de API en el servidor
 * Ejecutar este archivo desde el navegador: http://181.122.125.143/update_api_files.php
 */

echo "<h1>🔧 Actualizando archivos de API</h1>";
echo "<p><strong>Fecha:</strong> " . date('Y-m-d H:i:s') . "</p>";
echo "<hr>";

// 1. Actualizar api/index.php
echo "<h2>📝 Actualizando api/index.php</h2>";

$newIndexContent = '<?php
/**
 * API Router
 * 
 * This file serves as the main entry point for the API
 * It handles routing and dispatches requests to the appropriate controllers
 */

// Asegurarse de que no haya salida antes de establecer encabezados
ob_start();

// Configurar la visualización de errores para desarrollo (solo si no es producción)
if (strpos($_SERVER[\'SERVER_NAME\'], \'.local\') !== false || 
    strpos($_SERVER[\'SERVER_NAME\'], \'.test\') !== false ||
    $_SERVER[\'SERVER_NAME\'] === \'localhost\') {
    ini_set(\'display_errors\', 1);
    ini_set(\'display_startup_errors\', 1);
    error_reporting(E_ALL);
} else {
    error_reporting(E_ERROR | E_PARSE);
}

// Definir la ruta base del proyecto
define(\'BASE_DIR\', dirname(dirname(__FILE__)));
define(\'API_DIR\', dirname(__FILE__));

// Incluir archivo de funciones comunes
require_once BASE_DIR . "/api/core/Logger.php";

// Iniciar sesión para todas las solicitudes a la API
if (session_status() === PHP_SESSION_NONE) {
    // Configurar las sesiones para compartir entre dominios
    session_set_cookie_params([
        \'lifetime\' => 3600,
        \'path\' => \'/\',
        \'domain\' => \'.clinica.test\',
        \'secure\' => false,
        \'httponly\' => true,
        \'samesite\' => \'Lax\'
    ]);
    
    session_start();
}

// Registrar información de depuración
error_log("API Request - URI: " . $_SERVER[\'REQUEST_URI\']);
error_log("API Request - Method: " . $_SERVER[\'REQUEST_METHOD\']);
error_log("API Request - Content-Type: " . ($_SERVER[\'CONTENT_TYPE\'] ?? \'none\'));
error_log("API Request - Session ID: " . session_id() . ", Data: " . json_encode($_SESSION), 3, "/var/log/clinica/api_session.log");

// Set headers for API responses
header(\'Content-Type: application/json\');
// Permitir solicitudes desde el mismo origen o desde dominios específicos
$allowedOrigins = [
    \'http://localhost\',
    \'http://localhost:80\',
    \'http://clinica.test\',
    \'http://clinica.local\',
    \'http://181.122.125.143\',
    \'http://181.122.125.143:8888\'
];
$origin = isset($_SERVER[\'HTTP_ORIGIN\']) ? $_SERVER[\'HTTP_ORIGIN\'] : \'\';

if (in_array($origin, $allowedOrigins)) {
    header(\'Access-Control-Allow-Origin: \' . $origin);
} else {
    header(\'Access-Control-Allow-Origin: *\'); // Fallback para desarrollo
}

header(\'Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS\');
header(\'Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-CSRF-TOKEN\');
header(\'Access-Control-Allow-Credentials: true\');

// Handle preflight OPTIONS requests
if ($_SERVER[\'REQUEST_METHOD\'] === \'OPTIONS\') {
    http_response_code(200);
    exit;
}

// Include necessary files
require_once __DIR__ . \'/core/Router.php\';
require_once __DIR__ . \'/core/Response.php\';
require_once __DIR__ . \'/core/Database.php\';

// Include configuration after core classes are loaded
require_once __DIR__ . \'/../config/config.php\';

// Initialize the Router
$router = new \\Api\\Core\\Router();

// Include routes
require_once __DIR__ . \'/routes/api.php\';

// Get the request URI and method
$requestUri = $_SERVER[\'REQUEST_URI\'];
// Remover el prefijo /api/ de la URI
if (strpos($requestUri, \'/api/\') === 0) {
    $requestUri = substr($requestUri, 5); // Remover "/api/"
}
// Remover query string si existe
if (($pos = strpos($requestUri, \'?\')) !== false) {
    $requestUri = substr($requestUri, 0, $pos);
}
// Usar como fallback el parámetro route
if (empty($requestUri)) {
    $requestUri = isset($_GET[\'route\']) ? $_GET[\'route\'] : \'\';
}

$requestMethod = $_SERVER[\'REQUEST_METHOD\'];


// Process the request
try {
    // Capturar cualquier salida de buffer para evitar que rompa el JSON
    ob_start();
    $router->dispatch($requestUri, $requestMethod);
    // Si llegamos aquí sin enviar una respuesta, descartamos cualquier salida y enviamos un error
    $output = ob_get_clean();
    if (!headers_sent()) {
        // Si no se han enviado encabezados, significa que el controlador no respondió correctamente
        \\Api\\Core\\Response::error([\'message\' => \'No response from controller\'], 500);
    }
} catch (Exception $e) {
    // Limpiar cualquier salida buffer
    ob_clean();
    // Registrar el error
    error_log("API Error: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());
    // Enviar respuesta de error
    \\Api\\Core\\Response::error([\'message\' => $e->getMessage()], 500);
}
';

if (file_put_contents('/var/www/html/clinica/api/index.php', $newIndexContent)) {
    echo "<p>✅ api/index.php actualizado correctamente</p>";
} else {
    echo "<p>❌ Error al actualizar api/index.php</p>";
}

// 2. Verificar controladores faltantes
echo "<h2>📂 Verificando controladores</h2>";

$controllersNeeded = [
    'RhPersonController.php',
    'LocationController.php',
    'EspecialidadController.php',
    'SysBusinessController.php'
];

foreach ($controllersNeeded as $controller) {
    $controllerPath = "/var/www/html/clinica/api/controllers/$controller";
    if (file_exists($controllerPath)) {
        echo "<p>✅ $controller existe</p>";
    } else {
        echo "<p>❌ $controller NO existe</p>";
        
        // Crear controlador básico si no existe
        if ($controller === 'RhPersonController.php') {
            $content = createRhPersonController();
            if (file_put_contents($controllerPath, $content)) {
                echo "<p>&nbsp;&nbsp;&nbsp;✅ $controller creado</p>";
            }
        }
    }
}

// 3. Verificar Router.php
echo "<h2>🔀 Verificando Router</h2>";
$routerPath = '/var/www/html/clinica/api/core/Router.php';
if (file_exists($routerPath)) {
    echo "<p>✅ Router.php existe</p>";
} else {
    echo "<p>❌ Router.php NO existe - creando...</p>";
    
    $routerContent = createRouterClass();
    if (file_put_contents($routerPath, $routerContent)) {
        echo "<p>✅ Router.php creado</p>";
    }
}

// 4. Probar APIs
echo "<h2>🧪 Probando APIs</h2>";

$tests = [
    'http://181.122.125.143:8888/api/departments',
    'http://181.122.125.143:8888/api/especialidades'
];

foreach ($tests as $url) {
    echo "<p>🔬 Probando: <a href=\'$url\' target=\'_blank\'>$url</a></p>";
    
    $context = stream_context_create([
        'http' => [
            'timeout' => 5
        ]
    ]);
    
    $response = @file_get_contents($url, false, $context);
    if ($response !== false) {
        $data = json_decode($response, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            echo "<p>&nbsp;&nbsp;&nbsp;✅ Respuesta JSON válida</p>";
            if (isset($data['status'])) {
                echo "<p>&nbsp;&nbsp;&nbsp;📊 Status: " . $data['status'] . "</p>";
            }
        } else {
            echo "<p>&nbsp;&nbsp;&nbsp;❌ JSON inválido</p>";
        }
    } else {
        echo "<p>&nbsp;&nbsp;&nbsp;❌ Sin respuesta</p>";
    }
}

echo "<hr>";
echo "<h3>✅ Actualización completada</h3>";
echo "<p>Ahora ejecuta en el servidor:</p>";
echo "<pre>sudo systemctl reload nginx</pre>";

function createRhPersonController() {
    return \'<?php
namespace Api\\Controllers;

use Api\\Core\\Response;
use Api\\Core\\Database;

class RhPersonController
{
    private $db;
    
    public function __construct()
    {
        $this->db = Database::getConnection();
    }
    
    public function index()
    {
        try {
            $page = isset($_GET["page"]) ? (int)$_GET["page"] : 1;
            $perPage = isset($_GET["per_page"]) ? (int)$_GET["per_page"] : 20;
            $search = isset($_GET["search"]) ? $_GET["search"] : "";
            
            $offset = ($page - 1) * $perPage;
            
            $whereClause = "";
            $params = [];
            
            if (!empty($search)) {
                $whereClause = "WHERE LOWER(CONCAT(name, \' \', lastname)) LIKE LOWER(?) 
                               OR LOWER(identity_card) LIKE LOWER(?)";
                $params = ["%$search%", "%$search%"];
            }
            
            $countQuery = "SELECT COUNT(*) FROM rh_person $whereClause";
            $stmt = $this->db->prepare($countQuery);
            $stmt->execute($params);
            $total = $stmt->fetchColumn();
            
            $query = "SELECT person_id, name, lastname, identity_card, email, phone, 
                             profession, created_at 
                      FROM rh_person 
                      $whereClause 
                      ORDER BY person_id DESC 
                      LIMIT $perPage OFFSET $offset";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            $persons = $stmt->fetchAll();
            
            Response::success([
                \\\'data\\\' => $persons,
                \\\'recordsTotal\\\' => $total,
                \\\'recordsFiltered\\\' => $total,
                \\\'draw\\\' => isset($_GET[\\\'draw\\\']) ? (int)$_GET[\\\'draw\\\'] : 1
            ]);
            
        } catch (Exception $e) {
            Response::error([\\\'message\\\' => $e->getMessage()], 500);
        }
    }
    
    public function show()
    {
        // Implementar show
        Response::success([\\\'message\\\' => \\\'Show method\\\']);
    }
    
    public function store()
    {
        // Implementar store
        Response::success([\\\'message\\\' => \\\'Store method\\\']);
    }
    
    public function update()
    {
        // Implementar update
        Response::success([\\\'message\\\' => \\\'Update method\\\']);
    }
    
    public function destroy()
    {
        // Implementar destroy
        Response::success([\\\'message\\\' => \\\'Destroy method\\\']);
    }
}\';
}

function createRouterClass() {
    return \'<?php
namespace Api\\Core;

class Router
{
    private $routes = [];
    
    public function get($path, $controller, $method)
    {
        $this->addRoute(\\\'GET\\\', $path, $controller, $method);
    }
    
    public function post($path, $controller, $method)
    {
        $this->addRoute(\\\'POST\\\', $path, $controller, $method);
    }
    
    public function put($path, $controller, $method)
    {
        $this->addRoute(\\\'PUT\\\', $path, $controller, $method);
    }
    
    public function delete($path, $controller, $method)
    {
        $this->addRoute(\\\'DELETE\\\', $path, $controller, $method);
    }
    
    private function addRoute($httpMethod, $path, $controller, $method)
    {
        $this->routes[] = [
            \\\'method\\\' => $httpMethod,
            \\\'path\\\' => $path,
            \\\'controller\\\' => $controller,
            \\\'action\\\' => $method
        ];
    }
    
    public function dispatch($requestUri, $requestMethod)
    {
        foreach ($this->routes as $route) {
            if ($route[\\\'method\\\'] === $requestMethod && $this->matchPath($route[\\\'path\\\'], $requestUri)) {
                $this->callController($route[\\\'controller\\\'], $route[\\\'action\\\']);
                return;
            }
        }
        
        Response::error([\\\'message\\\' => [\\\'Route not found\\\', $requestUri, $requestMethod]], 404);
    }
    
    private function matchPath($routePath, $requestUri)
    {
        // Remover slash inicial si existe
        $routePath = ltrim($routePath, \\\'/\\\');
        $requestUri = ltrim($requestUri, \\\'/\\\');
        
        return $routePath === $requestUri;
    }
    
    private function callController($controllerClass, $method)
    {
        try {
            if (class_exists($controllerClass)) {
                $controller = new $controllerClass();
                if (method_exists($controller, $method)) {
                    $controller->$method();
                } else {
                    Response::error([\\\'message\\\' => \\\"Method $method not found in $controllerClass\\\"], 500);
                }
            } else {
                // Intentar cargar el controlador
                $controllerFile = str_replace(\\\'Api\\\\Controllers\\\\\\\', \\\'\\\', $controllerClass);
                $controllerPath = __DIR__ . \\\'/../controllers/\\\' . $controllerFile . \\\'.php\\\';
                
                if (file_exists($controllerPath)) {
                    require_once $controllerPath;
                    
                    if (class_exists($controllerClass)) {
                        $controller = new $controllerClass();
                        if (method_exists($controller, $method)) {
                            $controller->$method();
                        } else {
                            Response::error([\\\'message\\\' => \\\"Method $method not found\\\"], 500);
                        }
                    } else {
                        Response::error([\\\'message\\\' => \\\"Class $controllerClass not found after loading file\\\"], 500);
                    }
                } else {
                    Response::error([\\\'message\\\' => [\\\"Controller $controllerClass not found\\\", $requestUri, $requestMethod]], 500);
                }
            }
        } catch (Exception $e) {
            Response::error([\\\'message\\\' => $e->getMessage()], 500);
        }
    }
}\';
}
?>