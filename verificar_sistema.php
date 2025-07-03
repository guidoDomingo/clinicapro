<?php
/**
 * Script para verificación exhaustiva del sistema
 * Verifica la integridad y salud del proyecto después de la limpieza
 * 
 * Fecha: <?php echo date('Y-m-d H:i:s'); ?>
 */

// Archivo de log
$logFile = "logs/system_check_" . date('Y-m-d') . ".log";

// Función para registrar mensajes
function log_message($message, $level = 'INFO') {
    global $logFile;
    $timestamp = date('Y-m-d H:i:s');
    $line = "[$timestamp] [$level] $message\n";
    file_put_contents($logFile, $line, FILE_APPEND);
    
    // Colorear mensajes en la consola
    switch ($level) {
        case 'ERROR':
            echo "\033[31m$message\033[0m\n";  // Rojo
            break;
        case 'WARNING':
            echo "\033[33m$message\033[0m\n";  // Amarillo
            break;
        case 'SUCCESS':
            echo "\033[32m$message\033[0m\n";  // Verde
            break;
        default:
            echo "$message\n";  // Sin color
    }
}

// Función para verificar que un directorio exista y tenga permisos adecuados
function check_directory($dir) {
    if (!file_exists($dir)) {
        log_message("El directorio $dir no existe", 'ERROR');
        return false;
    }
    
    if (!is_dir($dir)) {
        log_message("$dir no es un directorio", 'ERROR');
        return false;
    }
    
    if (!is_writable($dir)) {
        log_message("El directorio $dir no tiene permisos de escritura", 'WARNING');
        return false;
    }
    
    log_message("Directorio $dir: OK", 'SUCCESS');
    return true;
}

// Función para verificar la existencia de archivos críticos
function check_critical_file($file) {
    if (!file_exists($file)) {
        log_message("Archivo crítico no encontrado: $file", 'ERROR');
        return false;
    }
    
    if (!is_readable($file)) {
        log_message("Archivo crítico no legible: $file", 'ERROR');
        return false;
    }
    
    log_message("Archivo crítico $file: OK", 'SUCCESS');
    return true;
}

// Iniciar verificación
log_message("=== INICIANDO VERIFICACIÓN EXHAUSTIVA DEL SISTEMA ===");
log_message("Fecha y hora: " . date('Y-m-d H:i:s'));
log_message("PHP versión: " . phpversion());
log_message("Sistema operativo: " . PHP_OS);

// 1. Verificar directorios críticos
log_message("\n=== VERIFICACIÓN DE DIRECTORIOS CRÍTICOS ===");
$directorios_criticos = [
    'config',
    'controller',
    'model',
    'view',
    'uploads',
    'pdf_temp',
    'logs',
    'ajax',
    'api',
    'cache'
];

$directorios_ok = 0;
foreach ($directorios_criticos as $dir) {
    if (check_directory($dir)) {
        $directorios_ok++;
    }
}

// 2. Verificar archivos críticos
log_message("\n=== VERIFICACIÓN DE ARCHIVOS CRÍTICOS ===");
$archivos_criticos = [
    'index.php',
    'config/config.php',
    'view/template.php',
    'controller/template.controller.php',
    'ajax/servicios.ajax.php',
    'ajax/reservas.ajax.php',
    'ajax/consultas.ajax.php'
];

$archivos_ok = 0;
foreach ($archivos_criticos as $archivo) {
    if (check_critical_file($archivo)) {
        $archivos_ok++;
    }
}

// 3. Verificar permisos de archivos de configuración
log_message("\n=== VERIFICACIÓN DE PERMISOS DE ARCHIVOS DE CONFIGURACIÓN ===");
$config_files = glob('config/*.php');
$config_permisos_ok = 0;

foreach ($config_files as $config) {
    $perms = fileperms($config);
    $perms_octal = substr(sprintf('%o', $perms), -4);
    
    // Idealmente los archivos de configuración deberían ser 0644 o más restrictivos
    if (($perms_octal[1] > 6) || ($perms_octal[2] > 4) || ($perms_octal[3] > 4)) {
        log_message("El archivo $config tiene permisos muy permisivos: $perms_octal", 'WARNING');
    } else {
        log_message("Permisos de $config: $perms_octal - OK", 'SUCCESS');
        $config_permisos_ok++;
    }
}

// 4. Verificar archivos .htaccess en directorios sensibles
log_message("\n=== VERIFICACIÓN DE PROTECCIÓN DE DIRECTORIOS SENSIBLES ===");
$directorios_sensibles = [
    'config',
    'pdf_temp',
    'uploads',
    'logs',
    'backups'
];

$htaccess_ok = 0;
foreach ($directorios_sensibles as $dir) {
    $htaccess = "$dir/.htaccess";
    if (file_exists($htaccess)) {
        log_message("Directorio sensible $dir protegido con .htaccess: OK", 'SUCCESS');
        $htaccess_ok++;
    } else {
        log_message("Directorio sensible $dir no tiene archivo .htaccess", 'WARNING');
        // Crear archivo .htaccess si no existe
        file_put_contents($htaccess, "Options -Indexes\nDeny from all");
        log_message("Creado archivo .htaccess para el directorio $dir", 'SUCCESS');
        $htaccess_ok++;
    }
}

// 5. Verificar errores de sintaxis en archivos PHP
log_message("\n=== VERIFICACIÓN DE SINTAXIS PHP ===");
$php_ok = 0;
$php_error = 0;

// Función recursiva para verificar errores de sintaxis
function check_syntax_recursive($dir) {
    global $php_ok, $php_error;
    
    $files = scandir($dir);
    foreach ($files as $file) {
        if ($file == '.' || $file == '..') continue;
        
        $path = $dir . DIRECTORY_SEPARATOR . $file;
        if (is_dir($path)) {
            // No verificar ciertos directorios
            if (in_array($file, ['backups', 'vendor', 'node_modules'])) continue;
            check_syntax_recursive($path);
        } elseif (pathinfo($path, PATHINFO_EXTENSION) == 'php') {
            // Verificar sintaxis de archivos PHP
            $output = [];
            $return_var = 0;
            exec("php -l \"$path\"", $output, $return_var);
            
            if ($return_var === 0) {
                $php_ok++;
            } else {
                log_message("Error de sintaxis en $path: " . implode("\n", $output), 'ERROR');
                $php_error++;
            }
        }
    }
}

// Verificar sintaxis en un conjunto limitado de directorios importantes
$dirs_to_check = ['controller', 'model', 'view', 'ajax'];
foreach ($dirs_to_check as $check_dir) {
    if (is_dir($check_dir)) {
        check_syntax_recursive($check_dir);
    }
}

log_message("Archivos PHP verificados sin errores: $php_ok", 'SUCCESS');
if ($php_error > 0) {
    log_message("Archivos PHP con errores de sintaxis: $php_error", 'ERROR');
}

// 6. Resumen
log_message("\n=== RESUMEN DE LA VERIFICACIÓN ===");
log_message("Directorios críticos verificados: $directorios_ok de " . count($directorios_criticos));
log_message("Archivos críticos verificados: $archivos_ok de " . count($archivos_criticos));
log_message("Archivos de configuración con permisos correctos: $config_permisos_ok de " . count($config_files));
log_message("Directorios sensibles protegidos: $htaccess_ok de " . count($directorios_sensibles));
log_message("Archivos PHP sin errores de sintaxis: $php_ok");
log_message("Archivos PHP con errores de sintaxis: $php_error");

// Resultado final
if ($directorios_ok == count($directorios_criticos) && 
    $archivos_ok == count($archivos_criticos) && 
    $php_error == 0) {
    log_message("\n¡VERIFICACIÓN COMPLETA: SISTEMA EN BUEN ESTADO!", 'SUCCESS');
} else {
    log_message("\nVERIFICACIÓN COMPLETA: SE ENCONTRARON PROBLEMAS QUE REQUIEREN ATENCIÓN", 'WARNING');
}

log_message("\nProceso de verificación completado. Revise el log para más detalles: $logFile");
