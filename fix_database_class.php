<?php
/**
 * Script para corregir el problema de "Class Api\Core\Database not found"
 * en archivos que no están en la carpeta /api/
 */

echo "=== CORRECCIÓN DE PROBLEMAS DE CLASE DATABASE ===\n";
echo "Fecha: " . date('Y-m-d H:i:s') . "\n\n";

$archivos_procesados = 0;
$archivos_corregidos = 0;

// Función para corregir includes de Database en config.php
function corregirConfigDatabase() {
    global $archivos_procesados, $archivos_corregidos;
    
    $archivo = '/var/www/html/clinica/config/config.php';
    if (!file_exists($archivo)) {
        echo "❌ No se encontró: $archivo\n";
        return;
    }
    
    echo "🔧 Procesando: $archivo\n";
    $archivos_procesados++;
    
    $contenido = file_get_contents($archivo);
    $contenido_original = $contenido;
    
    // Buscar la línea problemática donde se instancia Api\Core\Database
    if (strpos($contenido, 'new Api\Core\Database') !== false) {
        echo "  🔍 Encontrado uso de Api\\Core\\Database\n";
        
        // Verificar si ya incluye Database.php
        if (strpos($contenido, 'require_once') === false || strpos($contenido, 'Database.php') === false) {
            // Agregar include de Database.php al inicio del archivo
            $include_database = "<?php\n// Include Database class for non-API files\nif (!class_exists('Api\\Core\\Database')) {\n    require_once __DIR__ . '/../api/core/Database.php';\n}\n\n";
            
            // Reemplazar el <?php inicial
            $contenido = preg_replace('/^<\?php\s*/', $include_database, $contenido);
            
            echo "  ✅ Agregado include de Database.php\n";
            $archivos_corregidos++;
        }
    }
    
    // Guardar si hubo cambios
    if ($contenido !== $contenido_original) {
        file_put_contents($archivo, $contenido);
        echo "  💾 Archivo guardado\n";
    } else {
        echo "  ⚡ No necesita cambios\n";
    }
}

// Función para corregir modelos que usan Database
function corregirModelos() {
    global $archivos_procesados, $archivos_corregidos;
    
    $directorios = [
        '/var/www/html/clinica/model/',
    ];
    
    foreach ($directorios as $directorio) {
        if (!is_dir($directorio)) {
            echo "⚠️  Directorio no encontrado: $directorio\n";
            continue;
        }
        
        echo "📁 Procesando modelos en: $directorio\n";
        
        $archivos = glob($directorio . "*.php");
        foreach ($archivos as $archivo) {
            echo "  🔧 Procesando: " . basename($archivo) . "\n";
            $archivos_procesados++;
            
            $contenido = file_get_contents($archivo);
            $contenido_original = $contenido;
            
            // Si el archivo usa Api\Core\Database pero no lo incluye
            if (strpos($contenido, 'Api\Core\Database') !== false) {
                if (strpos($contenido, 'Database.php') === false) {
                    // Buscar la primera línea después de <?php
                    $lineas = explode("\n", $contenido);
                    $nueva_linea_agregada = false;
                    
                    for ($i = 0; $i < count($lineas); $i++) {
                        if (strpos($lineas[$i], '<?php') !== false && !$nueva_linea_agregada) {
                            // Insertar después de <?php
                            array_splice($lineas, $i + 1, 0, [
                                "// Include Database class for non-API files",
                                "if (!class_exists('Api\\Core\\Database')) {",
                                "    require_once __DIR__ . '/../api/core/Database.php';",
                                "}"
                            ]);
                            $nueva_linea_agregada = true;
                            echo "    ✅ Agregado include de Database.php\n";
                            $archivos_corregidos++;
                            break;
                        }
                    }
                    
                    if ($nueva_linea_agregada) {
                        $contenido = implode("\n", $lineas);
                    }
                }
            }
            
            // Guardar si hubo cambios
            if ($contenido !== $contenido_original) {
                file_put_contents($archivo, $contenido);
                echo "    💾 Archivo guardado\n";
            }
        }
    }
}

// Ejecutar correcciones
echo "🔧 Corrigiendo config.php...\n";
corregirConfigDatabase();

echo "\n🔧 Corrigiendo modelos...\n";
corregirModelos();

// Resumen
echo "\n=== RESUMEN DE CORRECCIONES ===\n";
echo "Archivos procesados: $archivos_procesados\n";
echo "Archivos corregidos: $archivos_corregidos\n";

if ($archivos_corregidos > 0) {
    echo "\n✅ CORRECCIÓN COMPLETADA EXITOSAMENTE\n";
    echo "Se han corregido los problemas de clase Database.\n";
} else {
    echo "\n✅ NO HAY CORRECCIONES NECESARIAS\n";
    echo "Todos los archivos ya incluyen Database correctamente.\n";
}

echo "\n=== FIN DEL SCRIPT ===\n";
?>