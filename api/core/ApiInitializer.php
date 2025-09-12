<?php
/**
 * API Initializer
 * Inicializa todos los componentes necesarios para que la API funcione
 */

namespace Api\Core;

class ApiInitializer
{
    private static $initialized = false;
    
    /**
     * Initialize all API components
     */
    public static function initialize()
    {
        if (self::$initialized) {
            return;
        }
        
        // 1. Cargar configuración del entorno
        if (!class_exists('EnvironmentSetup')) {
            require_once dirname(dirname(__DIR__)) . '/config/environment_setup.php';
            \EnvironmentSetup::initialize();
        }
        
        // 2. Inicializar configuración de API
        if (!class_exists('Api\\Core\\ApiConfig')) {
            require_once __DIR__ . '/ApiConfig.php';
        }
        ApiConfig::initialize();
        
        // 3. Configurar base de datos
        self::initializeDatabase();
        
        // 4. Configurar manejo de errores para API
        self::configureErrorHandling();
        
        self::$initialized = true;
        ApiConfig::log('API Initialized successfully', 'INFO');
    }
    
    /**
     * Initialize database connection with current environment config
     */
    private static function initializeDatabase()
    {
        try {
            $dbConfig = \EnvironmentSetup::getDatabaseConfig();
            
            // Inicializar la clase Database con la configuración
            Database::init($dbConfig);
            
            // Probar la conexión
            $connection = Database::getConnection();
            if ($connection) {
                ApiConfig::logDatabase('Database connection established successfully');
            }
            
        } catch (\Exception $e) {
            ApiConfig::logDatabase('Database initialization failed: ' . $e->getMessage(), true);
            throw $e;
        }
    }
    
    /**
     * Configure error handling for API
     */
    private static function configureErrorHandling()
    {
        // Configure error reporting based on environment
        if (ApiConfig::isProduction()) {
            error_reporting(E_ERROR | E_PARSE);
            ini_set('display_errors', 0);
        } else {
            error_reporting(E_ALL);
            ini_set('display_errors', 1);
        }
        
        // Set custom error handler for API
        set_error_handler([self::class, 'handleError']);
        set_exception_handler([self::class, 'handleException']);
    }
    
    /**
     * Custom error handler for API
     */
    public static function handleError($severity, $message, $file, $line)
    {
        $errorMessage = "Error [$severity]: $message in $file on line $line";
        ApiConfig::log($errorMessage, 'ERROR');
        
        // Don't stop execution for warnings and notices in production
        if (ApiConfig::isProduction() && ($severity === E_WARNING || $severity === E_NOTICE)) {
            return true;
        }
        
        return false; // Let PHP handle the error normally
    }
    
    /**
     * Custom exception handler for API
     */
    public static function handleException($exception)
    {
        $errorMessage = "Uncaught exception: " . $exception->getMessage() . " in " . 
                       $exception->getFile() . " on line " . $exception->getLine();
        ApiConfig::log($errorMessage, 'ERROR');
        
        // Send proper API error response
        if (!headers_sent()) {
            Response::error([
                'message' => ApiConfig::isProduction() ? 'Internal server error' : $exception->getMessage(),
                'code' => $exception->getCode() ?: 500
            ], 500);
        }
    }
    
    /**
     * Get initialization status
     */
    public static function isInitialized()
    {
        return self::$initialized;
    }
}

// Auto-initialize when this file is included
ApiInitializer::initialize();