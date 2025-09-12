<?php 
require_once __DIR__ . '/../vendor/autoload.php';
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

class Conexion{

    static public function conectar(){
        // Cargar .env una sola vez
        if (!isset($GLOBALS['_dotenv_loaded'])) {
            $dotenv = Dotenv::createImmutable(__DIR__ . '/../');
            $dotenv->load();
            $GLOBALS['_dotenv_loaded'] = true;
        }
        $contrasena = $_ENV['DB_PASSWORD'] ?? '';
        $usuario = $_ENV['DB_USERNAME'] ?? '';
        $nombreBaseDeDatos = $_ENV['DB_DATABASE'] ?? '';
        $rutaServidor = $_ENV['DB_HOST'] ?? '';
        $puerto = $_ENV['DB_PORT'] ?? '';
        
        try {
            // Check if PostgreSQL extension is available
            if (!extension_loaded('pdo_pgsql')) {
                // Determinar directorio de logs basado en el entorno
                if (isset($_ENV['LOG_PATH']) && !empty($_ENV['LOG_PATH'])) {
                    $logDir = $_ENV['LOG_PATH'];
                } else {
                    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                        $logDir = __DIR__ . "/../logs";
                    } else {
                        $logDir = "/var/log/clinica";
                    }
                }
                
                // Asegurar que exista el directorio de logs
                if (!file_exists($logDir)) {
                    mkdir($logDir, 0777, true);
                }
                
                error_log("[" . date('Y-m-d H:i:s') . "] Error: PDO PostgreSQL extension not loaded", 
                          3, "$logDir/database.log");
                
                // Registrar información adicional para diagnóstico
                $extensiones_cargadas = implode(', ', get_loaded_extensions());
                error_log("[" . date('Y-m-d H:i:s') . "] Extensiones cargadas: $extensiones_cargadas", 
                          3, "$logDir/database.log");
                
                // Si estamos en un entorno web, ofrecer soluciones
                if (php_sapi_name() !== 'cli' && !isset($GLOBALS['_conexion_error_displayed'])) {
                    $GLOBALS['_conexion_error_displayed'] = true;
                    // Solo mostramos el mensaje una vez por solicitud
                    if (!headers_sent()) {
                        header("HTTP/1.1 503 Service Unavailable");
                    }
                    
                    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
                    // No redireccionamos si ya estamos en la página de verificación
                    if (strpos($scriptName, 'check_and_enable_pgsql.php') === false) {
                        // Sólo mostramos el mensaje si no es una solicitud AJAX
                        $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
                        
                        if (!$isAjax) {
                            echo '<div style="margin: 20px; padding: 20px; border: 2px solid #dc3545; border-radius: 5px; background-color: #f8d7da; color: #721c24; font-family: Arial, sans-serif;">';
                            echo '<h2>Error de Conexión a la Base de Datos</h2>';
                            echo '<p>La extensión PostgreSQL para PHP (pdo_pgsql) no está habilitada.</p>';
                            echo '<p><a href="/clinica/check_and_enable_pgsql.php" style="display: inline-block; padding: 10px 20px; background-color: #007bff; color: white; text-decoration: none; border-radius: 5px;">Verificar y Solucionar</a></p>';
                            echo '</div>';
                        }
                    }
                }
                
                return null;
            }
            
            $link = new PDO("pgsql:host=$rutaServidor;port=$puerto;dbname=$nombreBaseDeDatos", $usuario, $contrasena);
            $link->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $link;
        } catch (Exception $e) {
            // Log the error with timestamp and details
            $errorMessage = "[" . date('Y-m-d H:i:s') . "] Database Error: " . $e->getMessage() . 
                          "\nFile: " . $e->getFile() . 
                          "\nLine: " . $e->getLine() . 
                          "\nTrace: " . $e->getTraceAsString();
            
            // Determinar directorio de logs basado en el entorno
            if (isset($_ENV['LOG_PATH']) && !empty($_ENV['LOG_PATH'])) {
                $logDir = $_ENV['LOG_PATH'];
            } else {
                if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                    $logDir = __DIR__ . "/../logs";
                } else {
                    $logDir = "/var/log/clinica";
                }
            }
            
            // Asegurar que exista el directorio de logs
            if (!file_exists($logDir)) {
                mkdir($logDir, 0777, true);
            }
            
            error_log($errorMessage, 3, "$logDir/database.log");
            
            // En lugar de lanzar una excepción que podría generar HTML,
            // devolvemos null y manejamos el error en el modelo
            return null;
        }
    }
}