<?php
/**
 * Configuración portable para el sistema de reservas públicas
 * Este archivo permite que el sistema funcione en cualquier servidor sin modificaciones
 */

class ConfigPortable {
    
    /**
     * Obtiene la ruta base del proyecto de forma portable
     */
    public static function getBasePath() {
        return dirname(__DIR__, 2);
    }
    
    /**
     * Obtiene la ruta de logs de forma portable
     */
    public static function getLogPath() {
        $basePath = self::getBasePath();
        $logDir = $basePath . '/logs';
        
        // Crear directorio de logs si no existe
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        return $logDir;
    }
    
    /**
     * Genera una ruta de log específica
     */
    public static function getLogFile($fileName) {
        return self::getLogPath() . '/' . $fileName;
    }
    
    /**
     * Registra un mensaje en un archivo de log de forma portable
     */
    public static function log($message, $fileName = 'public_reservas.log') {
        $logFile = self::getLogFile($fileName);
        error_log(date('[Y-m-d H:i:s] ') . $message . PHP_EOL, 3, $logFile);
    }
    
    /**
     * Configuración de sesiones portable
     */
    public static function configurarSesiones() {
        if (session_status() == PHP_SESSION_NONE) {
            session_set_cookie_params([
                'lifetime' => 3600,
                'path' => '/',
                'domain' => '', // Sin dominio específico - funciona en cualquier servidor
                'secure' => false,
                'httponly' => true,
                'samesite' => 'Lax'
            ]);
            
            session_start();
        }
    }
    
    /**
     * Obtiene la URL base del proyecto de forma automática
     */
    public static function getBaseUrl() {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'];
        $path = dirname($_SERVER['SCRIPT_NAME']);
        
        return $protocol . '://' . $host . $path;
    }
    
    /**
     * Genera URLs relativas para redirecciones
     */
    public static function getUrl($path = '') {
        return 'index.php' . ($path ? '?' . $path : '');
    }
}
?>