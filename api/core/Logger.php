<?php
namespace Api\Core;

/**
 * Logger Class
 * 
 * Handles application logging functionality
 */
class Logger
{
    /**
     * @var string The default log file path
     */
    private static $logFile = null;

    /**
     * Get the appropriate log file path based on environment
     */
    private static function getLogPath()
    {
        if (self::$logFile === null) {
            // Load environment variables if not already loaded
            if (!isset($_ENV['LOG_PATH'])) {
                try {
                    $dotenv = \Dotenv\Dotenv::createImmutable(dirname(__DIR__, 2));
                    $dotenv->load();
                } catch (\Exception $e) {
                    // Fallback if .env not found
                }
            }
            
            // Determine log path based on environment
            if (isset($_ENV['LOG_PATH']) && !empty($_ENV['LOG_PATH'])) {
                $logDir = $_ENV['LOG_PATH'];
            } else {
                // Auto-detect based on OS
                if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                    // Windows (local)
                    $logDir = dirname(__DIR__, 2) . '/logs/';
                } else {
                    // Linux (production)
                    $logDir = '/var/log/clinica/';
                }
            }
            
            // Create directory if it doesn't exist
            if (!file_exists($logDir)) {
                mkdir($logDir, 0777, true);
            }
            
            self::$logFile = $logDir . 'application.log';
        }
        
        return self::$logFile;
    }

    /**
     * @var int Maximum size of log file in bytes (5MB)
     */
    private static $maxFileSize = 5242880;

    /**
     * @var array Valid log levels
     */
    private static $validLevels = ['debug', 'info', 'warning', 'error', 'critical'];

    /**
     * Rotate log file if it exceeds maximum size
     */
    private static function rotateLogFile()
    {
        $logFile = self::getLogPath();
        
        if (!file_exists($logFile)) {
            return;
        }

        if (filesize($logFile) > self::$maxFileSize) {
            $backupFile = $logFile . '.' . date('Y-m-d-H-i-s') . '.backup';
            rename($logFile, $backupFile);
        }
    }

    /**
     * Log a message with context data
     * 
     * @param mixed $data The data to log
     * @param string $level The log level (debug, info, warning, error, critical)
     * @param string $context Additional context information
     * @return void
     */
    public static function log($data, $level = 'info', $context = '')
    {
        if (!in_array($level, self::$validLevels)) {
            $level = 'info';
        }

        self::rotateLogFile();

        $timestamp = date('Y-m-d H:i:s');
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
        $caller = isset($backtrace[1]) ? basename($backtrace[1]['file']) . ':' . $backtrace[1]['line'] : 'unknown';
        
        // Asegurar que el contexto sea una cadena
        if (is_array($context) || is_object($context)) {
            $context = json_encode($context, JSON_UNESCAPED_UNICODE);
        }
        
        // Asegurar que los datos sean una cadena
        if (is_array($data) || is_object($data)) {
            $logData = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        } else {
            $logData = (string)$data;
        }
        
        $message = sprintf(
            "[%s] [%s] [%s] %s\n",
            $timestamp,
            strtoupper($level),
            $caller,
            $context
        );
        $message .= "Data: {$logData}\n";
        $message .= "----------------------------------------\n";
        
        try {
            $logFile = self::getLogPath();
            error_log($message, 3, $logFile);
        } catch (\Exception $e) {
            // Fallback: log to PHP error log if custom logging fails
            error_log("Logger Error: " . $e->getMessage() . " | Original message: " . trim($message));
        }
    }
    
    /**
     * Log info level message
     * 
     * @param mixed $data The data to log
     * @param string $context Additional context information
     * @return void
     */
    public static function info($data, $context = '')
    {
        self::log($data, 'info', $context);
    }
    
    /**
     * Log error level message
     * 
     * @param mixed $data The data to log
     * @param string $context Additional context information
     * @return void
     */
    public static function error($data, $context = '')
    {
        self::log($data, 'error', $context);
    }
    
    /**
     * Log debug level message
     * 
     * @param mixed $data The data to log
     * @param string $context Additional context information
     * @return void
     */
    public static function debug($data, $context = '')
    {
        self::log($data, 'debug', $context);
    }
    
    /**
     * Log warning level message
     * 
     * @param mixed $data The data to log
     * @param string $context Additional context information
     * @return void
     */
    public static function warning($data, $context = '')
    {
        self::log($data, 'warning', $context);
    }
}