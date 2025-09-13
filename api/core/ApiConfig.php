<?php
/**
 * API Configuration
 * Configuración centralizada para toda la API que funciona en local y producción
 */

namespace Api\Core;

class ApiConfig
{
    private static $initialized = false;
    private static $config = [];
    
    /**
     * Initialize API configuration
     */
    public static function initialize()
    {
        if (self::$initialized) {
            return;
        }
        
        // Cargar configuración del entorno
        if (!class_exists('EnvironmentSetup')) {
            require_once dirname(dirname(__DIR__)) . '/config/environment_setup.php';
            \EnvironmentSetup::initialize();
        }
        
        self::$config = [
            'log_path' => \EnvironmentSetup::getLogPath(),
            'upload_path' => \EnvironmentSetup::getUploadPath(),
            'temp_path' => \EnvironmentSetup::getTempPath(),
            'is_production' => \EnvironmentSetup::isProduction(),
            'is_local' => \EnvironmentSetup::isLocal(),
            'database_config' => \EnvironmentSetup::getDatabaseConfig(),
            'app_url' => $_ENV['APP_URL'] ?? 'http://localhost/clinica',
            'api_version' => $_ENV['API_VERSION'] ?? '1.0.0',
            'log_level' => $_ENV['LOG_LEVEL'] ?? 'debug',
            'debug' => $_ENV['APP_DEBUG'] ?? false
        ];
        
        self::$initialized = true;
    }
    
    /**
     * Get configuration value
     */
    public static function get($key, $default = null)
    {
        self::initialize();
        return self::$config[$key] ?? $default;
    }
    
    /**
     * Get log path for API
     */
    public static function getLogPath()
    {
        return self::get('log_path');
    }
    
    /**
     * Get API log file path
     */
    public static function getApiLogFile()
    {
        return self::getLogPath() . 'api.log';
    }
    
    /**
     * Get database log file path
     */
    public static function getDatabaseLogFile()
    {
        return self::getLogPath() . 'database.log';
    }
    
    /**
     * Get session log file path
     */
    public static function getSessionLogFile()
    {
        return self::getLogPath() . 'api_session.log';
    }
    
    /**
     * Check if running in production
     */
    public static function isProduction()
    {
        return self::get('is_production', false);
    }
    
    /**
     * Check if running in local environment
     */
    public static function isLocal()
    {
        return self::get('is_local', true);
    }
    
    /**
     * Get upload path
     */
    public static function getUploadPath()
    {
        return self::get('upload_path');
    }
    
    /**
     * Get temp path
     */
    public static function getTempPath()
    {
        return self::get('temp_path');
    }
    
    /**
     * Log API message with proper formatting
     */
    public static function log($message, $level = 'INFO', $file = 'api')
    {
        $timestamp = date('Y-m-d H:i:s');
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
        
        // Verificar que exista el backtrace y los índices necesarios
        $caller = 'unknown';
        if (isset($backtrace[1]) && isset($backtrace[1]['file']) && isset($backtrace[1]['line'])) {
            $caller = basename($backtrace[1]['file']) . ':' . $backtrace[1]['line'];
        }
        
        $logMessage = sprintf(
            "[%s] [%s] [%s] %s\n",
            $timestamp,
            strtoupper($level),
            $caller,
            $message
        );
        
        $logFile = self::getLogPath() . $file . '.log';
        error_log($logMessage, 3, $logFile);
    }
    
    /**
     * Log API session information
     */
    public static function logSession($message)
    {
        self::log($message, 'SESSION', 'api_session');
    }
    
    /**
     * Log database operations
     */
    public static function logDatabase($message, $isError = false)
    {
        $level = $isError ? 'ERROR' : 'INFO';
        self::log($message, $level, 'database');
    }
    
    /**
     * Get all configuration for debugging
     */
    public static function getAllConfig()
    {
        self::initialize();
        return self::$config;
    }
}

// Auto-initialize when this file is included
ApiConfig::initialize();