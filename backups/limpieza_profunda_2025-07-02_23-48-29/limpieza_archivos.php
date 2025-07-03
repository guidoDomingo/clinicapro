<?php
/**
 * Script para limpiar archivos de prueba y diagnóstico innecesarios
 * Este script realiza un respaldo de los archivos antes de eliminarlos
 * 
 * Fecha: <?php echo date('Y-m-d H:i:s'); ?>
 */

// Configuración
$backupFolder = 'backups/limpieza_' . date('Y-m-d_H-i-s');
$logFile = 'logs/limpieza_' . date('Y-m-d') . '.log';

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

// Lista de archivos a eliminar
$archivosTest = [
    // Archivos de prueba en la raíz
    'test_api_powershell.ps1',
    'test_api_ps.ps1',
    'test_icd.js',
    
    // Archivos de prueba en public_reservas
    'public_reservas/test_form.php',
    'public_reservas/test_reserva.php',
    'public_reservas/test_password.php',
    'public_reservas/test_integration.php',
    'public_reservas/test_form_process.php',
    'public_reservas/direct_test.php',
    'public_reservas/simular_login.php',
];

// Contadores
$movedCount = 0;
$errorCount = 0;

// Procesar cada archivo
log_message("Iniciando proceso de limpieza de archivos de prueba...");
log_message("Los archivos serán respaldados en: $backupFolder");
echo "\n";

foreach ($archivosTest as $archivo) {
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
$readmeContent = "# Respaldo de archivos de prueba\n\n";
$readmeContent .= "Fecha de respaldo: " . date('Y-m-d H:i:s') . "\n\n";
$readmeContent .= "Estos archivos fueron respaldados antes de ser eliminados del proyecto por considerarse archivos de prueba no necesarios para producción.\n\n";
$readmeContent .= "## Lista de archivos\n";

foreach ($archivosTest as $archivo) {
    if (file_exists($backupFolder . '/' . basename($archivo))) {
        $readmeContent .= "- `" . basename($archivo) . "`\n";
    }
}

file_put_contents($backupFolder . '/README.md', $readmeContent);
log_message("Se ha creado un README.md en la carpeta de respaldo con información detallada.");
