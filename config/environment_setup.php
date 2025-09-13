<?php
/**
 * Environment Setup Script
 * Configura automáticamente el entorno para local y producción
 */

// Cargar variables de entorno
require_once __DIR__ . '/../vendor/autoload.php';
use Dotenv\Dotenv;

class EnvironmentSetup
{
    private static $isWindows;
    private static $envLoaded = false;
    
    public static function initialize()
    {
        self::$isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
        self::loadEnvironmentVariables();
        self::setupDirectories();
        self::validateDatabaseConnection();
    }
    
    private static function loadEnvironmentVariables()
    {
        if (!self::$envLoaded) {
            try {
                $dotenv = Dotenv::createImmutable(dirname(__DIR__));
                $dotenv->load();
                self::$envLoaded = true;
            } catch (Exception $e) {
                error_log("Error loading .env file: " . $e->getMessage());
                return false;
            }
        }
        return true;
    }
    
    private static function setupDirectories()
    {
        // Configurar directorio de logs (solo si realmente se va a usar)
        $logPath = self::getLogPath();
        if (!file_exists($logPath)) {
            // Suprimir warnings y usar @ para operación silenciosa
            if (!@mkdir($logPath, 0777, true)) {
                // No es crítico si falla - los logs pueden ir a otro lugar
                error_log("Warning: Could not create log directory: $logPath");
            }
        }
        
        // Configurar directorio de uploads (solo si realmente se necesita)
        $uploadPath = self::getUploadPath();
        if (!file_exists($uploadPath)) {
            if (!@mkdir($uploadPath, 0777, true)) {
                // No es crítico para API básico
                error_log("Warning: Could not create upload directory: $uploadPath");
            }
        }
        
        // Configurar directorio temporal (solo si realmente se necesita)
        $tempPath = self::getTempPath();
        if (!file_exists($tempPath)) {
            if (!@mkdir($tempPath, 0777, true)) {
                // Usar directorio temporal del sistema como fallback
                error_log("Warning: Could not create temp directory: $tempPath");
            }
        }
        
        // Siempre retorna true - los errores de directorio no son críticos para API
        return true;
        
        return true;
    }
    
    public static function getLogPath()
    {
        // Siempre priorizar la configuración explícita del .env
        if (isset($_ENV['LOG_PATH']) && !empty($_ENV['LOG_PATH'])) {
            return $_ENV['LOG_PATH'];
        }
        
        // Si no hay configuración en .env, detectar automáticamente por SO
        // (no por APP_ENV, sino por el sistema operativo real)
        if (PHP_OS_FAMILY === 'Windows') {
            return dirname(__DIR__) . '/logs/';
        } else {
            return '/var/log/clinica/';
        }
    }
    
    public static function getUploadPath()
    {
        // Siempre priorizar la configuración explícita del .env
        if (isset($_ENV['UPLOAD_PATH']) && !empty($_ENV['UPLOAD_PATH'])) {
            return $_ENV['UPLOAD_PATH'];
        }
        
        // Detectar automáticamente por SO real
        if (PHP_OS_FAMILY === 'Windows') {
            return dirname(__DIR__) . '/uploads/';
        } else {
            return '/var/www/clinica/uploads/';
        }
    }
    
    public static function getTempPath()
    {
        // Siempre priorizar la configuración explícita del .env
        if (isset($_ENV['TEMP_PATH']) && !empty($_ENV['TEMP_PATH'])) {
            return $_ENV['TEMP_PATH'];
        }
        
        // Detectar automáticamente por SO real
        if (PHP_OS_FAMILY === 'Windows') {
            return dirname(__DIR__) . '/temp/';
        } else {
            return '/tmp/clinica/';
        }
    }
    
    public static function isProduction()
    {
        return isset($_ENV['APP_ENV']) && $_ENV['APP_ENV'] === 'production';
    }
    
    public static function isLocal()
    {
        return isset($_ENV['APP_ENV']) && $_ENV['APP_ENV'] === 'local';
    }
    
    private static function validateDatabaseConnection()
    {
        try {
            require_once dirname(__DIR__) . '/model/conexion.php';
            $db = Conexion::conectar();
            
            if ($db === null) {
                $logPath = self::getLogPath();
                $message = "[" . date('Y-m-d H:i:s') . "] Database connection failed during environment setup\n";
                error_log($message, 3, $logPath . 'setup.log');
                return false;
            }
            
            // Test the connection
            $stmt = $db->prepare("SELECT 1");
            $stmt->execute();
            
            $logPath = self::getLogPath();
            $message = "[" . date('Y-m-d H:i:s') . "] Database connection successful\n";
            error_log($message, 3, $logPath . 'setup.log');
            
            return true;
            
        } catch (Exception $e) {
            $logPath = self::getLogPath();
            $message = "[" . date('Y-m-d H:i:s') . "] Database connection error: " . $e->getMessage() . "\n";
            error_log($message, 3, $logPath . 'setup.log');
            return false;
        }
    }
    
    public static function getDatabaseConfig()
    {
        // Asegurar que las variables de entorno estén cargadas
        self::loadEnvironmentVariables();
        
        return [
            'host' => $_ENV['DB_HOST'] ?? 'localhost',
            'port' => $_ENV['DB_PORT'] ?? '5432',
            'database' => $_ENV['DB_DATABASE'] ?? 'clinica',
            'username' => $_ENV['DB_USERNAME'] ?? 'postgres',
            'password' => $_ENV['DB_PASSWORD'] ?? '',
            'driver' => $_ENV['DB_DRIVER'] ?? 'pgsql'
        ];
    }
    
    public static function logEnvironmentInfo()
    {
        $logPath = self::getLogPath();
        $info = [
            'timestamp' => date('Y-m-d H:i:s'),
            'php_version' => PHP_VERSION,
            'os' => PHP_OS,
            'is_windows' => self::$isWindows,
            'environment' => $_ENV['APP_ENV'] ?? 'unknown',
            'log_path' => $logPath,
            'upload_path' => self::getUploadPath(),
            'temp_path' => self::getTempPath(),
            'database_config' => self::getDatabaseConfig()
        ];
        
        $message = "[" . date('Y-m-d H:i:s') . "] Environment Info: " . json_encode($info, JSON_PRETTY_PRINT) . "\n";
        error_log($message, 3, $logPath . 'environment.log');
    }
}

// Auto-inicializar si el archivo es llamado directamente
if (basename(__FILE__) === basename($_SERVER['SCRIPT_NAME'])) {
    EnvironmentSetup::initialize();
    EnvironmentSetup::logEnvironmentInfo();
    
    echo "Environment setup completed successfully!\n";
    echo "Log path: " . EnvironmentSetup::getLogPath() . "\n";
    echo "Upload path: " . EnvironmentSetup::getUploadPath() . "\n";
    echo "Temp path: " . EnvironmentSetup::getTempPath() . "\n";
}