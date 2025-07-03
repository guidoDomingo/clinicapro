<?php
/**
 * Script final de limpieza para los archivos de prueba en subdirectorios
 * Este script maneja correctamente los archivos en subdirectorios que no fueron
 * procesados correctamente en la ejecución anterior.
 */

// Definir la carpeta de respaldo
$backupFolder = 'backups/limpieza_ajax_' . date('Y-m-d_H-i-s');

// Crear la carpeta de respaldo si no existe
if (!is_dir($backupFolder)) {
    if (!mkdir($backupFolder, 0777, true)) {
        die("Error: No se pudo crear la carpeta de respaldo $backupFolder");
    }
}

// Lista de archivos en subdirectorios
$files = [
    'ajax/enviar_media.php.new',
    'ajax/guardar-consulta.ajax.php.bak',
    'ajax/send_pdf_test.php',
    'ajax/pdf_uploader.php',
    'ajax/subir_pdf.php',
    'ajax/upload_pdf.php',
];

$moved = 0;
$errors = [];

echo "Iniciando limpieza de archivos en subdirectorios...\n";
echo "Los archivos serán respaldados en: $backupFolder\n\n";

foreach ($files as $file) {
    if (file_exists($file)) {
        echo "Procesando: $file...";
        
        // Crear la estructura de directorios en la carpeta de respaldo
        $backupPath = "$backupFolder/" . dirname($file);
        if (!is_dir($backupPath)) {
            if (!mkdir($backupPath, 0777, true)) {
                echo " [ERROR: No se pudo crear el directorio $backupPath]\n";
                $errors[] = "No se pudo crear directorio: $backupPath";
                continue;
            }
        }
        
        // Respaldar el archivo
        $backupFile = "$backupFolder/$file";
        if (copy($file, $backupFile)) {
            // Eliminar el archivo original
            if (unlink($file)) {
                echo " [ELIMINADO]\n";
                $moved++;
            } else {
                echo " [ERROR: No se pudo eliminar]\n";
                $errors[] = "No se pudo eliminar: $file";
            }
        } else {
            echo " [ERROR: No se pudo respaldar]\n";
            $errors[] = "No se pudo respaldar: $file";
        }
    } else {
        echo "Archivo no encontrado: $file\n";
    }
}

// Mostrar resumen
echo "\n--- Resumen del proceso ---\n";
echo "Archivos respaldados y eliminados: $moved\n";
echo "Errores encontrados: " . count($errors) . "\n";

if (!empty($errors)) {
    echo "\nDetalle de errores:\n";
    foreach ($errors as $error) {
        echo "- $error\n";
    }
}

echo "\nProceso completado. Los archivos han sido respaldados en: $backupFolder\n";

// Crear un README en la carpeta de respaldo
$readmeContent = "# Respaldo de archivos eliminados\n\n";
$readmeContent .= "Fecha: " . date('Y-m-d H:i:s') . "\n\n";
$readmeContent .= "Esta carpeta contiene los archivos de prueba y temporales que fueron eliminados del proyecto.\n";
$readmeContent .= "Los archivos fueron respaldados antes de su eliminación para referencia futura.\n\n";
$readmeContent .= "## Archivos eliminados\n\n";
foreach ($files as $file) {
    $readmeContent .= "- `$file`\n";
}

file_put_contents("$backupFolder/README.md", $readmeContent);
echo "Se ha creado un archivo README.md en la carpeta de respaldo con información sobre los archivos.\n";

// Eliminar este script
echo "\nEliminando este script de limpieza final...\n";
if (unlink(__FILE__)) {
    echo "Script eliminado correctamente.\n";
} else {
    echo "No se pudo eliminar este script. Por favor, elimínelo manualmente.\n";
}
