<?php
// Debug específico para errores 500 en preformatos.ajax.php

echo "<h2>Debug de errores 500 en preformatos.ajax.php</h2>";

// Activar reporte de errores
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', 'logs/debug_preformatos_500.log');

echo "<h3>1. Verificando archivos de dependencias</h3>";

// Verificar archivos requeridos
$archivos_requeridos = [
    'controller/preformatos.controller.php',
    'model/preformatos.model.php',
    'model/conexion.php'
];

foreach ($archivos_requeridos as $archivo) {
    if (file_exists($archivo)) {
        echo "<span style='color: green'>✓ $archivo existe</span><br>";
    } else {
        echo "<span style='color: red'>✗ $archivo NO existe</span><br>";
    }
}

echo "<h3>2. Intentando cargar dependencias</h3>";

try {
    require_once "controller/preformatos.controller.php";
    echo "<span style='color: green'>✓ Controller cargado exitosamente</span><br>";
} catch (Exception $e) {
    echo "<span style='color: red'>✗ Error al cargar controller: " . $e->getMessage() . "</span><br>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<h3>3. Verificando conexión a base de datos</h3>";

try {
    require_once "model/conexion.php";
    $db = Conexion::conectar();
    echo "<span style='color: green'>✓ Conexión a base de datos exitosa</span><br>";
    echo "Tipo de conexión: " . get_class($db) . "<br>";
} catch (Exception $e) {
    echo "<span style='color: red'>✗ Error de conexión: " . $e->getMessage() . "</span><br>";
}

echo "<h3>4. Verificando extensiones PHP</h3>";

$extensiones_necesarias = ['pdo', 'pdo_pgsql', 'json'];
foreach ($extensiones_necesarias as $ext) {
    if (extension_loaded($ext)) {
        echo "<span style='color: green'>✓ $ext cargada</span><br>";
    } else {
        echo "<span style='color: red'>✗ $ext NO cargada</span><br>";
    }
}

echo "<h3>5. Probando métodos específicos</h3>";

try {
    // Verificar que podemos instanciar la clase Ajax
    if (class_exists('PreformatosAjax')) {
        echo "<span style='color: green'>✓ Clase PreformatosAjax disponible</span><br>";
    } else {
        echo "<span style='color: red'>✗ Clase PreformatosAjax NO disponible</span><br>";
        
        // Intentar incluir manualmente el archivo ajax
        ob_start();
        include 'ajax/preformatos.ajax.php';
        $output = ob_get_clean();
        
        if (class_exists('PreformatosAjax')) {
            echo "<span style='color: green'>✓ Clase PreformatosAjax cargada después de include</span><br>";
        } else {
            echo "<span style='color: red'>✗ Clase PreformatosAjax aún no disponible</span><br>";
            echo "Output del include: <pre>" . htmlspecialchars($output) . "</pre>";
        }
    }
} catch (Exception $e) {
    echo "<span style='color: red'>✗ Error en verificación de clases: " . $e->getMessage() . "</span><br>";
}

echo "<h3>6. Simulando petición AJAX específica</h3>";

try {
    // Simular petición de getDoctorByUserId que es la que está fallando
    $_POST['operacion'] = 'getDoctorByUserId';
    $_POST['user_id'] = '1'; // ID de prueba
    
    ob_start();
    
    // Capturar errores
    $error_handler = function($errno, $errstr, $errfile, $errline) {
        throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
    };
    set_error_handler($error_handler);
    
    include 'ajax/preformatos.ajax.php';
    
    $ajax_output = ob_get_clean();
    restore_error_handler();
    
    echo "<span style='color: green'>✓ AJAX ejecutado sin errores fatales</span><br>";
    echo "Output del AJAX: <pre>" . htmlspecialchars($ajax_output) . "</pre>";
    
    // Verificar si es JSON válido
    $json_data = json_decode($ajax_output, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        echo "<span style='color: green'>✓ Output es JSON válido</span><br>";
        echo "Datos decodificados: <pre>" . print_r($json_data, true) . "</pre>";
    } else {
        echo "<span style='color: red'>✗ Output NO es JSON válido</span><br>";
        echo "Error JSON: " . json_last_error_msg() . "<br>";
    }
    
} catch (Exception $e) {
    ob_end_clean();
    echo "<span style='color: red'>✗ Error en simulación AJAX: " . $e->getMessage() . "</span><br>";
    echo "Archivo: " . $e->getFile() . " Línea: " . $e->getLine() . "<br>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<h3>7. Verificando logs</h3>";

$archivos_log = [
    'logs/preformatos_ajax.log',
    'logs/database.log',
    'logs/debug_preformatos_500.log'
];

foreach ($archivos_log as $log_file) {
    if (file_exists($log_file)) {
        $contenido = file_get_contents($log_file);
        if (!empty($contenido)) {
            echo "<h4>$log_file:</h4>";
            echo "<pre style='background: #f5f5f5; padding: 10px; max-height: 200px; overflow-y: auto;'>";
            echo htmlspecialchars(tail($contenido, 20)); // Mostrar últimas 20 líneas
            echo "</pre>";
        } else {
            echo "<span style='color: orange'>⚠ $log_file existe pero está vacío</span><br>";
        }
    } else {
        echo "<span style='color: orange'>⚠ $log_file no existe</span><br>";
    }
}

function tail($string, $lines = 10) {
    $array = explode("\n", $string);
    return implode("\n", array_slice($array, -$lines));
}

echo "<h3>8. Información del servidor</h3>";
echo "PHP Version: " . phpversion() . "<br>";
echo "Server Software: " . $_SERVER['SERVER_SOFTWARE'] . "<br>";
echo "Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "<br>";
echo "Script Name: " . $_SERVER['SCRIPT_NAME'] . "<br>";

// Reiniciar $_POST para no interferir con otras operaciones
$_POST = [];
?>
