<?php
/**
 * Script para limpieza avanzada del proyecto
 * Este script identifica y elimina archivos innecesarios en el proyecto
 * 
 * Fecha: <?php echo date('Y-m-d H:i:s'); ?>
 */

// Configuración
$backupFolder = 'backups/limpieza_avanzada_' . date('Y-m-d_H-i-s');
$logFile = 'logs/limpieza_avanzada_' . date('Y-m-d') . '.log';

// Función para registrar mensajes
function log_message($message) {
    global $logFile;
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND);
    echo "$message\n";
}

// Crear carpeta de respaldo si no existe
if (!file_exists($backupFolder)) {
    mkdir($backupFolder, 0777, true);
    log_message("Creada carpeta de respaldo: $backupFolder");
}

// Lista de archivos no esenciales que pueden ser respaldados y eliminados
$archivosInnecesarios = [
    // Archivos de configuración temporales
    'configurar_especialidades.bat',
    'configurar_limpieza_automatica.bat',
    'configurar_motivos_comunes.bat',
    'public_reservas/crear_tabla_verificacion.bat',
    
    // Archivos relacionados con diagnóstico en public_reservas
    'public_reservas/diagnostico_cambio_password.php',
    'public_reservas/diagnostico_password.php',
    'public_reservas/diagnostico_verificacion.php',
];

// Contadores
$movedCount = 0;
$errorCount = 0;

// Procesar cada archivo
log_message("Iniciando proceso de limpieza avanzada...");
log_message("Los archivos serán respaldados en: $backupFolder");
echo "\n";

foreach ($archivosInnecesarios as $archivo) {
    if (file_exists($archivo)) {
        $backupPath = $backupFolder . '/' . basename($archivo);
        $dirName = dirname($backupPath);
        
        // Crear directorio si no existe
        if (!file_exists($dirName)) {
            mkdir($dirName, 0777, true);
        }
        
        // Copiar archivo al backup
        if (copy($archivo, $backupPath)) {
            log_message("Respaldado: $archivo -> $backupPath");
            
            // Eliminar archivo original
            if (unlink($archivo)) {
                log_message("Eliminado: $archivo");
                $movedCount++;
            } else {
                log_message("ERROR: No se pudo eliminar: $archivo");
                $errorCount++;
            }
        } else {
            log_message("ERROR: No se pudo respaldar: $archivo");
            $errorCount++;
        }
    } else {
        log_message("No encontrado: $archivo (omitido)");
    }
}

// Mostrar resumen
echo "\n";
log_message("=== RESUMEN DE LA OPERACIÓN ===");
log_message("Archivos procesados exitosamente: $movedCount");
log_message("Errores: $errorCount");
log_message("Operación completada. Revisa el log para más detalles: $logFile");

// Crear un archivo README.md en la carpeta de respaldo
$readmeContent = "# Respaldo de archivos innecesarios\n\n";
$readmeContent .= "Fecha de respaldo: " . date('Y-m-d H:i:s') . "\n\n";
$readmeContent .= "Estos archivos fueron respaldados y eliminados porque ya no son necesarios para el funcionamiento del sistema en producción.\n\n";
$readmeContent .= "## Lista de archivos\n";

foreach ($archivosInnecesarios as $archivo) {
    if (file_exists($backupFolder . '/' . basename($archivo))) {
        $readmeContent .= "- `" . basename($archivo) . "`\n";
    }
}

file_put_contents($backupFolder . '/README.md', $readmeContent);
log_message("Se ha creado un README.md en la carpeta de respaldo con información detallada.");
