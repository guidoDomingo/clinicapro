<?php
/**
 * Script para limpieza profunda del sistema
 * Elimina archivos temporales, logs innecesarios, y código de depuración
 * 
 * Este script debe ser ejecutado solo cuando el sistema esté en mantenimiento
 * Fecha: <?php echo date('Y-m-d H:i:s'); ?>
 */

// Configuración
$backupFolder = 'backups/limpieza_profunda_' . date('Y-m-d_H-i-s');
$logFile = 'logs/limpieza_profunda_' . date('Y-m-d') . '.log';
$MAX_LOG_SIZE = 5 * 1024 * 1024; // 5 MB
$MAX_LOG_AGE = 7 * 24 * 60 * 60;  // 7 días en segundos

// Función para registrar mensajes
function log_message($message) {
    global $logFile;
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND);
    echo "$message\n";
}

// Función para crear un respaldo de un archivo antes de eliminarlo
function backup_and_delete($file) {
    global $backupFolder;
    
    $relativePath = str_replace($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR, '', $file);
    $backupPath = $backupFolder . DIRECTORY_SEPARATOR . $relativePath;
    $backupDir = dirname($backupPath);
    
    // Crear directorio de respaldo si no existe
    if (!is_dir($backupDir)) {
        mkdir($backupDir, 0777, true);
    }
    
    // Respaldar y eliminar
    if (copy($file, $backupPath)) {
        log_message("Respaldado: $file -> $backupPath");
        
        if (unlink($file)) {
            log_message("Eliminado: $file");
            return true;
        } else {
            log_message("ERROR: No se pudo eliminar: $file");
            return false;
        }
    } else {
        log_message("ERROR: No se pudo respaldar: $file");
        return false;
    }
}

// Función para truncar un archivo de log
function truncate_log($file) {
    $size = filesize($file);
    log_message("Truncando archivo de log grande: $file ($size bytes)");
    
    // Leer las primeras y últimas líneas
    $lines = file($file);
    $totalLines = count($lines);
    
    if ($totalLines < 10) {
        log_message("  El archivo tiene pocas líneas, no se truncará");
        return false;
    }
    
    // Mantener primeras 5 líneas y últimas 5 líneas
    $header = array_slice($lines, 0, 5);
    $footer = array_slice($lines, -5);
    
    // Crear mensaje de truncado
    $truncateMsg = "[" . date('Y-m-d H:i:s') . "] --- LOG TRUNCADO: Se eliminaron " . 
                   ($totalLines - 10) . " líneas para ahorrar espacio ---\n";
    
    // Crear nuevo contenido
    $newContent = implode('', $header) . $truncateMsg . implode('', $footer);
    
    // Guardar archivo truncado
    if (file_put_contents($file, $newContent)) {
        log_message("  Log truncado exitosamente: " . $file);
        return true;
    } else {
        log_message("  ERROR: No se pudo truncar el log: " . $file);
        return false;
    }
}

// Crear carpeta de respaldo
if (!file_exists($backupFolder)) {
    if (!mkdir($backupFolder, 0777, true)) {
        die("Error: No se pudo crear la carpeta de respaldo: $backupFolder");
    }
    log_message("Creada carpeta de respaldo: $backupFolder");
}

// 1. LIMPIEZA DE LOGS
log_message("=== INICIANDO LIMPIEZA DE LOGS ===");
$truncatedLogs = 0;
$deletedOldLogs = 0;

$logFiles = glob('logs/*.log');
foreach ($logFiles as $log) {
    $size = filesize($log);
    $lastModified = filemtime($log);
    $ageInDays = (time() - $lastModified) / 86400;
    
    // Verificar si es muy grande
    if ($size > $MAX_LOG_SIZE) {
        if (truncate_log($log)) {
            $truncatedLogs++;
        }
    }
    
    // Verificar si es muy viejo (excepto para logs importantes)
    $importantLogs = ['application.log', 'database.log', 'consultas.log', 'auth.log'];
    $logName = basename($log);
    
    if (!in_array($logName, $importantLogs) && (time() - $lastModified) > $MAX_LOG_AGE) {
        log_message("Respaldando y eliminando log antiguo: $log (Edad: $ageInDays días)");
        if (backup_and_delete($log)) {
            $deletedOldLogs++;
        }
    }
}

// 2. LIMPIEZA DE ARCHIVOS TEMPORALES
log_message("\n=== INICIANDO LIMPIEZA DE ARCHIVOS TEMPORALES ===");
$tempDirs = [
    'temp',
    'pdf_temp',
    'uploads/temp',
    'ajax/temp'
];

$tempFiles = 0;
foreach ($tempDirs as $dir) {
    if (!is_dir($dir)) {
        log_message("Directorio no encontrado: $dir (omitiendo)");
        continue;
    }
    
    log_message("Limpiando directorio temporal: $dir");
    
    // Mantener estructura pero eliminar contenido (excepto archivos de protección)
    $files = glob("$dir/*");
    foreach ($files as $file) {
        $filename = basename($file);
        // No eliminar archivos de protección o directorios
        if (in_array($filename, ['.htaccess', 'index.html', 'index.php', '.gitkeep']) || is_dir($file)) {
            continue;
        }
        
        if (unlink($file)) {
            log_message("  Eliminado archivo temporal: $file");
            $tempFiles++;
        } else {
            log_message("  ERROR: No se pudo eliminar archivo temporal: $file");
        }
    }
}

// 3. ELIMINAR ARCHIVOS DE DEPURACIÓN Y PRUEBAS
log_message("\n=== ELIMINANDO ARCHIVOS DE DEPURACIÓN Y PRUEBAS ===");
$debugFiles = [
    'logs/debug_guardar.php',
    'logs/debug_guardar_detallado.php',
];

$debugFilesRemoved = 0;
foreach ($debugFiles as $file) {
    if (file_exists($file)) {
        log_message("Eliminando archivo de depuración: $file");
        if (backup_and_delete($file)) {
            $debugFilesRemoved++;
        }
    }
}

// 4. LIMPIEZA DE ARCHIVOS DE RESPALDO Y TEMPORALES EN LA RAÍZ
log_message("\n=== LIMPIANDO ARCHIVOS DE RESPALDO Y TEMPORALES DE LA RAÍZ ===");
$rootCleanupFiles = [
    'limpieza_archivos.php',    // Ya no es necesario
    'limpieza_avanzada.php',    // Ya no es necesario
    'limpieza_final.php'        // Ya no es necesario
];

$rootFilesRemoved = 0;
foreach ($rootCleanupFiles as $file) {
    if (file_exists($file)) {
        log_message("Eliminando archivo de limpieza: $file");
        if (backup_and_delete($file)) {
            $rootFilesRemoved++;
        }
    }
}

// 5. ELIMINAR ARCHIVOS PHP VACÍOS
log_message("\n=== ELIMINANDO ARCHIVOS PHP VACÍOS ===");
$emptyPhpFiles = 0;
$phpFiles = array_merge(
    glob('*.php'),
    glob('public_reservas/*.php')
);

foreach ($phpFiles as $file) {
    // Verificar si es un archivo importante (nunca eliminar estos)
    $important = [
        'index.php', 'config.php', 'template.php', 'enviar_pdf_consulta.php',
        'enviar_pdf_whatsapp.php', 'generar_pdf_consulta.php', 'generar_pdf_reserva.php'
    ];
    
    if (in_array(basename($file), $important)) {
        continue;
    }
    
    // Verificar si está vacío o casi vacío
    $content = file_get_contents($file);
    $contentStripped = trim(preg_replace('/<\?php|\?>|\s+/s', '', $content));
    
    if (strlen($contentStripped) < 5) {
        log_message("Encontrado archivo PHP prácticamente vacío: $file (tamaño efectivo: " . strlen($contentStripped) . " bytes)");
        if (backup_and_delete($file)) {
            $emptyPhpFiles++;
        }
    }
}

// Crear README en carpeta de respaldo
$readmeContent = "# Respaldo de limpieza profunda\n\n";
$readmeContent .= "Fecha: " . date('Y-m-d H:i:s') . "\n\n";
$readmeContent .= "Este directorio contiene archivos respaldados durante el proceso de limpieza profunda del sistema.\n\n";
$readmeContent .= "## Resumen de acciones:\n\n";
$readmeContent .= "- Logs truncados: $truncatedLogs\n";
$readmeContent .= "- Logs antiguos eliminados: $deletedOldLogs\n";
$readmeContent .= "- Archivos temporales eliminados: $tempFiles\n";
$readmeContent .= "- Archivos de depuración eliminados: $debugFilesRemoved\n";
$readmeContent .= "- Scripts de limpieza eliminados: $rootFilesRemoved\n";
$readmeContent .= "- Archivos PHP vacíos eliminados: $emptyPhpFiles\n";

file_put_contents("$backupFolder/README.md", $readmeContent);
log_message("\nCreado archivo README.md en el directorio de respaldo");

// Resumen
log_message("\n=== RESUMEN DE LA LIMPIEZA PROFUNDA ===");
log_message("Logs truncados: $truncatedLogs");
log_message("Logs antiguos eliminados: $deletedOldLogs");
log_message("Archivos temporales eliminados: $tempFiles");
log_message("Archivos de depuración eliminados: $debugFilesRemoved");
log_message("Scripts de limpieza eliminados: $rootFilesRemoved");
log_message("Archivos PHP vacíos eliminados: $emptyPhpFiles");
log_message("\nLimpieza profunda completada exitosamente");
log_message("Los archivos respaldados se encuentran en: $backupFolder");
log_message("Revise el archivo de registro para más detalles: $logFile");

// Auto-eliminar este script después de la ejecución
log_message("\nEste script se eliminará a sí mismo en la próxima ejecución...");
