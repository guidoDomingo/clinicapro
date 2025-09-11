<?php
/**
 * Script para corregir rutas de Windows a Linux en todos los archivos PHP
 * Ejecutar en el servidor después de hacer git pull
 */

echo "=== CORRECCIÓN MASIVA DE RUTAS WINDOWS A LINUX ===\n";
echo "Fecha: " . date('Y-m-d H:i:s') . "\n\n";

// Configuración de rutas
$rutas_windows = [
    'c:/laragon/www/clinica/logs/',
    'c:\\laragon\\www\\clinica\\logs\\',
    'C:/laragon/www/clinica/logs/',
    'C:\\laragon\\www\\clinica\\logs\\',
];

$ruta_linux = '/var/log/clinica/';

// Directorios a procesar
$directorios = [
    '/var/www/html/clinica/ajax/',
    '/var/www/html/clinica/controller/',
    '/var/www/html/clinica/model/',
    '/var/www/html/clinica/api/',
    '/var/www/html/clinica/config/',
    '/var/www/html/clinica/includes/',
];

$archivos_corregidos = 0;
$total_reemplazos = 0;

function procesarDirectorio($directorio) {
    global $rutas_windows, $ruta_linux, $archivos_corregidos, $total_reemplazos;
    
    if (!is_dir($directorio)) {
        echo "⚠️  Directorio no encontrado: $directorio\n";
        return;
    }
    
    echo "📁 Procesando directorio: $directorio\n";
    
    $archivos = glob($directorio . "*.php");
    foreach ($archivos as $archivo) {
        $contenido_original = file_get_contents($archivo);
        $contenido_nuevo = $contenido_original;
        $reemplazos_archivo = 0;
        
        // Aplicar todos los reemplazos
        foreach ($rutas_windows as $ruta_windows) {
            $antes = $contenido_nuevo;
            $contenido_nuevo = str_replace($ruta_windows, $ruta_linux, $contenido_nuevo);
            $reemplazos = substr_count($antes, $ruta_windows);
            $reemplazos_archivo += $reemplazos;
        }
        
        // Si hubo cambios, guardar el archivo
        if ($contenido_nuevo !== $contenido_original) {
            file_put_contents($archivo, $contenido_nuevo);
            echo "  ✅ Corregido: " . basename($archivo) . " ($reemplazos_archivo reemplazos)\n";
            $archivos_corregidos++;
            $total_reemplazos += $reemplazos_archivo;
        }
    }
    
    // Procesar subdirectorios recursivamente
    $subdirectorios = glob($directorio . "*", GLOB_ONLYDIR);
    foreach ($subdirectorios as $subdir) {
        procesarDirectorio($subdir . "/");
    }
}

// Crear directorio de logs si no existe
if (!is_dir('/var/log/clinica')) {
    mkdir('/var/log/clinica', 0755, true);
    chown('/var/log/clinica', 'www-data');
    chgrp('/var/log/clinica', 'www-data');
    echo "📁 Directorio de logs creado: /var/log/clinica\n";
}

// Procesar todos los directorios
foreach ($directorios as $directorio) {
    procesarDirectorio($directorio);
}

// Resumen
echo "\n=== RESUMEN DE CORRECCIONES ===\n";
echo "Archivos corregidos: $archivos_corregidos\n";
echo "Total de reemplazos: $total_reemplazos\n";

if ($archivos_corregidos > 0) {
    echo "\n✅ CORRECCIÓN COMPLETADA EXITOSAMENTE\n";
    echo "Se han corregido todas las rutas de Windows a Linux.\n";
} else {
    echo "\n✅ NO HAY CORRECCIONES NECESARIAS\n";
    echo "Todos los archivos ya tienen rutas correctas.\n";
}

echo "\n=== FIN DEL SCRIPT ===\n";
?>