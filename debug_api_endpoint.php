<?php
/**
 * DEBUG DEL API ENDPOINT - VERIFICAR ERROR 500
 */

// Habilitar reporte de errores
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

echo "<h1>🔍 Debug del API Endpoint</h1>";

try {
    echo "<h2>1. Verificando archivos necesarios...</h2>";
    
    // Verificar archivo de conexión
    $conexionFile = __DIR__ . '/model/conexion.php';
    if (file_exists($conexionFile)) {
        echo "✅ Archivo de conexión encontrado: $conexionFile<br>";
    } else {
        echo "❌ Archivo de conexión NO encontrado: $conexionFile<br>";
        echo "📁 Directorio actual: " . __DIR__ . "<br>";
        echo "📁 Listado de archivos en model/:<br>";
        $modelDir = __DIR__ . '/model/';
        if (is_dir($modelDir)) {
            $files = scandir($modelDir);
            foreach ($files as $file) {
                if ($file !== '.' && $file !== '..') {
                    echo "&nbsp;&nbsp;- $file<br>";
                }
            }
        } else {
            echo "&nbsp;&nbsp;❌ Directorio model/ no existe<br>";
        }
    }
    
    echo "<h2>2. Probando inclusión de archivos...</h2>";
    
    // Intentar incluir conexión
    try {
        require_once __DIR__ . '/model/conexion.php';
        echo "✅ Archivo conexion.php incluido correctamente<br>";
        
        // Verificar clase
        if (class_exists('Conexion')) {
            echo "✅ Clase 'Conexion' encontrada<br>";
            
            // Intentar conectar
            try {
                $conexion = Conexion::conectar();
                if ($conexion) {
                    echo "✅ Conexión a BD exitosa<br>";
                    echo "&nbsp;&nbsp;Driver: " . $conexion->getAttribute(PDO::ATTR_DRIVER_NAME) . "<br>";
                } else {
                    echo "❌ Conexión a BD falló<br>";
                }
            } catch (Exception $e) {
                echo "❌ Error conectando a BD: " . $e->getMessage() . "<br>";
            }
            
        } else {
            echo "❌ Clase 'Conexion' no encontrada<br>";
        }
        
    } catch (Exception $e) {
        echo "❌ Error incluyendo conexion.php: " . $e->getMessage() . "<br>";
    }
    
    echo "<h2>3. Simulando llamada al API...</h2>";
    
    // Simular sesión
    session_start();
    $_SESSION['user_id'] = 1;
    
    // Simular parámetros GET
    $_GET['action'] = 'getMotivosComunes';
    
    echo "📋 Acción: " . $_GET['action'] . "<br>";
    echo "👤 Usuario ID: " . $_SESSION['user_id'] . "<br>";
    
    echo "<h3>Ejecutando lógica del API...</h3>";
    
    // Headers que estarían en el API
    header('Content-Type: application/json');
    
    // Verificar que la función getMotivosComunes existe
    if (function_exists('getMotivosComunes')) {
        echo "✅ Función getMotivosComunes existe<br>";
        
        try {
            $result = getMotivosComunes();
            echo "✅ Función ejecutada exitosamente<br>";
            echo "<pre>" . print_r($result, true) . "</pre>";
        } catch (Exception $e) {
            echo "❌ Error ejecutando función: " . $e->getMessage() . "<br>";
        }
    } else {
        echo "❌ Función getMotivosComunes NO existe<br>";
        echo "📋 Funciones disponibles:<br>";
        $functions = get_defined_functions()['user'];
        foreach ($functions as $func) {
            if (strpos($func, 'motivos') !== false || strpos($func, 'Motivos') !== false) {
                echo "&nbsp;&nbsp;- $func<br>";
            }
        }
    }
    
} catch (Exception $e) {
    echo "❌ Error general: " . $e->getMessage() . "<br>";
    echo "📍 Archivo: " . $e->getFile() . " línea " . $e->getLine() . "<br>";
}

echo "<hr>";
echo "<h2>🔗 Enlaces de prueba:</h2>";
echo "<a href='/clinica/modules/consultas/api/consultas-api.php?action=getMotivosComunes'>Probar API directamente</a><br>";
echo "<a href='/clinica/test_conexion_db.php'>Test de conexión DB</a>";
?>
