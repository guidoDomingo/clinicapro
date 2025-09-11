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
    private static $logFile = '/var/log/clinica/application.log';

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
        if (!file_exists(self::$logFile)) {
            return;
        }

        if (filesize(self::$logFile) > self::$maxFileSize) {
            $backupFile = self::$logFile . '.' . date('Y-m-d-H-i-s') . '.backup';
            rename(self::$logFile, $backupFile);
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
        
        error_log($message, 3, self::$logFile);
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