<?php
/**
 * Fix específico para el problema de SalasModel con Database class
 */

echo "=== FIX ESPECÍFICO PARA SALAS.AJAX.PHP ===\n";
echo "Fecha: " . date('Y-m-d H:i:s') . "\n\n";

// Verificar el problema
echo "🔍 Verificando problema actual...\n";

$archivos_problemáticos = [
    '/var/www/html/clinica/ajax/salas.ajax.php',
    '/var/www/html/clinica/model/SalasModel.php',
    '/var/www/html/clinica/config/config.php'
];

foreach ($archivos_problemáticos as $archivo) {
    if (file_exists($archivo)) {
        echo "✅ Encontrado: " . basename($archivo) . "\n";
    } else {
        echo "❌ No encontrado: " . basename($archivo) . "\n";
    }
}

// Función para agregar include de Database al inicio de archivos específicos
function agregarIncludeDatabase($archivo, $ruta_relativa_api = '../api/core/Database.php') {
    if (!file_exists($archivo)) {
        echo "❌ Archivo no encontrado: $archivo\n";
        return false;
    }
    
    $contenido = file_get_contents($archivo);
    
    // Verificar si ya tiene el include
    if (strpos($contenido, 'Database.php') !== false) {
        echo "⚡ Ya tiene include de Database: " . basename($archivo) . "\n";
        return false;
    }
    
    // Verificar si usa Api\Core\Database
    if (strpos($contenido, 'Api\Core\Database') === false) {
        echo "⚡ No usa Api\\Core\\Database: " . basename($archivo) . "\n";
        return false;
    }
    
    echo "🔧 Agregando include de Database a: " . basename($archivo) . "\n";
    
    // Buscar la primera línea después de <?php
    $lineas = explode("\n", $contenido);
    $nueva_linea_agregada = false;
    
    for ($i = 0; $i < count($lineas); $i++) {
        if (strpos($lineas[$i], '<?php') !== false && !$nueva_linea_agregada) {
            // Insertar después de <?php
            array_splice($lineas, $i + 1, 0, [
                "",
                "// Include Database class for non-API files",
                "if (!class_exists('Api\\Core\\Database')) {",
                "    require_once __DIR__ . '/$ruta_relativa_api';",
                "}"
            ]);
            $nueva_linea_agregada = true;
            break;
        }
    }
    
    if ($nueva_linea_agregada) {
        $contenido_nuevo = implode("\n", $lineas);
        file_put_contents($archivo, $contenido_nuevo);
        echo "✅ Include agregado exitosamente\n";
        return true;
    } else {
        echo "❌ No se pudo agregar include\n";
        return false;
    }
}

// Fix para config.php
echo "\n🔧 Corrigiendo config.php...\n";
$config_file = '/var/www/html/clinica/config/config.php';
if (file_exists($config_file)) {
    $contenido = file_get_contents($config_file);
    
    // Si config.php usa Api\Core\Database pero no tiene el include
    if (strpos($contenido, 'Api\Core\Database') !== false && strpos($contenido, 'Database.php') === false) {
        // Agregar include al inicio
        $include_code = "<?php\n\n// Include Database class for non-API files\nif (!class_exists('Api\\Core\\Database')) {\n    require_once __DIR__ . '/../api/core/Database.php';\n}\n\n";
        
        // Reemplazar el <?php inicial
        $contenido_nuevo = preg_replace('/^<\?php\s*/', $include_code, $contenido);
        
        file_put_contents($config_file, $contenido_nuevo);
        echo "✅ Config.php corregido\n";
    } else {
        echo "⚡ Config.php no necesita corrección\n";
    }
}

// Fix para SalasModel.php
echo "\n🔧 Corrigiendo SalasModel.php...\n";
agregarIncludeDatabase('/var/www/html/clinica/model/SalasModel.php', '../api/core/Database.php');

// Fix para salas.ajax.php
echo "\n🔧 Corrigiendo salas.ajax.php...\n";
agregarIncludeDatabase('/var/www/html/clinica/ajax/salas.ajax.php', '../api/core/Database.php');

// Verificar sintaxis PHP
echo "\n🧪 Verificando sintaxis PHP...\n";
$archivos_verificar = [
    '/var/www/html/clinica/config/config.php',
    '/var/www/html/clinica/model/SalasModel.php',
    '/var/www/html/clinica/ajax/salas.ajax.php'
];

$sintaxis_ok = true;
foreach ($archivos_verificar as $archivo) {
    if (file_exists($archivo)) {
        $output = shell_exec("php -l $archivo 2>&1");
        if (strpos($output, 'No syntax errors') !== false) {
            echo "✅ Sintaxis OK: " . basename($archivo) . "\n";
        } else {
            echo "❌ Error sintaxis: " . basename($archivo) . "\n";
            echo "   $output\n";
            $sintaxis_ok = false;
        }
    }
}

if ($sintaxis_ok) {
    echo "\n🎉 CORRECCIÓN COMPLETADA EXITOSAMENTE\n";
    echo "Todos los archivos tienen sintaxis correcta.\n";
    echo "\n📋 Próximos pasos:\n";
    echo "1. Probar: curl -X POST -d 'action=listarSalas' http://localhost:8888/ajax/salas.ajax.php\n";
    echo "2. Verificar aplicación web en el navegador\n";
} else {
    echo "\n❌ HAY ERRORES DE SINTAXIS\n";
    echo "Revisar los errores mostrados arriba.\n";
}

echo "\n=== FIN DEL FIX ===\n";
?>