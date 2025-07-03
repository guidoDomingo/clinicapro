<?php
/**
 * Script de mantenimiento programado
 * Este script limpia archivos temporales y logs antiguos
 * 
 * Puede programarse para ejecutarse periódicamente mediante el Programador de Tareas de Windows
 * o mediante cron en sistemas Linux.
 */

// Configuración
$logFile = 'logs/mantenimiento_' . date('Y-m-d') . '.log';
$diasAntiguedadLogs = 30; // Eliminar logs con más de 30 días
$diasAntiguedadTemp = 7;  // Eliminar archivos temporales con más de 7 días
$maxBackups = 10;         // Número máximo de carpetas de respaldo a mantener

// Función para registrar mensajes
function log_message($message) {
    global $logFile;
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND);
    echo "$message\n";
}

// Función para eliminar archivos antiguos
function eliminar_archivos_antiguos($directorio, $diasAntiguedad) {
    $cont = 0;
    $tiempoLimite = time() - ($diasAntiguedad * 86400);
    
    if (!file_exists($directorio)) return $cont;
    
    $archivos = glob($directorio . '/*');
    foreach ($archivos as $archivo) {
        if (is_file($archivo)) {
            if (filemtime($archivo) < $tiempoLimite) {
                unlink($archivo);
                log_message("Eliminado archivo antiguo: $archivo");
                $cont++;
            }
        }
    }
    
    return $cont;
}

log_message("Iniciando proceso de mantenimiento programado...");

// 1. Limpiar archivos temporales
$tempDirs = [
    'temp',
    'pdf_temp',
    'cache',
    'ajax/temp'
];

$totalTemp = 0;
foreach ($tempDirs as $dir) {
    log_message("Limpiando directorio: $dir");
    $totalTemp += eliminar_archivos_antiguos($dir, $diasAntiguedadTemp);
}

// 2. Limpiar logs antiguos
log_message("Limpiando logs antiguos...");
$totalLogs = eliminar_archivos_antiguos('logs', $diasAntiguedadLogs);

// 3. Limpiar carpetas de respaldo antiguas manteniendo las N más recientes
log_message("Gestionando carpetas de respaldo...");
$backupDirs = glob('backups/limpieza_*');
$totalBackups = count($backupDirs);

if ($totalBackups > $maxBackups) {
    // Ordenar por fecha (más antiguo primero)
    usort($backupDirs, function($a, $b) {
        return filemtime($a) - filemtime($b);
    });
    
    // Eliminar las carpetas más antiguas
    $eliminar = $totalBackups - $maxBackups;
    for ($i = 0; $i < $eliminar; $i++) {
        $dir = $backupDirs[$i];
        if (is_dir($dir)) {
            // Eliminar archivos dentro de la carpeta
            array_map('unlink', glob("$dir/*"));
            // Eliminar la carpeta
            rmdir($dir);
            log_message("Eliminada carpeta de respaldo antigua: $dir");
        }
    }
}

// Mostrar resumen
log_message("=== RESUMEN DEL MANTENIMIENTO ===");
log_message("Archivos temporales eliminados: $totalTemp");
log_message("Logs antiguos eliminados: $totalLogs");
log_message("Mantenimiento de carpetas de respaldo completado");
log_message("Proceso finalizado correctamente");
