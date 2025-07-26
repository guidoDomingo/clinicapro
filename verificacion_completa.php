<?php
// Verificación paso a paso de todas las dependencias

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Verificación Paso a Paso - Error 500</h2>";

$pasos = [];

// Paso 1: Verificar archivos
echo "<h3>Paso 1: Verificando archivos</h3>";
$archivos = [
    'controller/preformatos.controller.php',
    'model/preformatos.model.php',
    'model/conexion.php'
];

foreach ($archivos as $archivo) {
    $existe = file_exists($archivo);
    $pasos["archivo_$archivo"] = $existe;
    echo "<p style='color: " . ($existe ? 'green' : 'red') . ";'>" . 
         ($existe ? '✓' : '✗') . " $archivo</p>";
}

// Paso 2: Verificar sintaxis de cada archivo
echo "<h3>Paso 2: Verificando sintaxis PHP</h3>";
foreach ($archivos as $archivo) {
    if (file_exists($archivo)) {
        $output = shell_exec("php -l $archivo 2>&1");
        $sintaxis_ok = strpos($output, 'No syntax errors') !== false;
        $pasos["sintaxis_$archivo"] = $sintaxis_ok;
        echo "<p style='color: " . ($sintaxis_ok ? 'green' : 'red') . ";'>" . 
             ($sintaxis_ok ? '✓' : '✗') . " $archivo: " . trim($output) . "</p>";
    }
}

// Paso 3: Incluir conexión
echo "<h3>Paso 3: Probando conexión</h3>";
try {
    require_once "model/conexion.php";
    $pasos["include_conexion"] = true;
    echo "<p style='color: green;'>✓ Conexion incluida</p>";
    
    $db = Conexion::conectar();
    $pasos["test_conexion"] = true;
    echo "<p style='color: green;'>✓ Conexión a BD exitosa</p>";
} catch (Exception $e) {
    $pasos["include_conexion"] = false;
    $pasos["test_conexion"] = false;
    echo "<p style='color: red;'>✗ Error conexión: " . $e->getMessage() . "</p>";
}

// Paso 4: Incluir modelo
echo "<h3>Paso 4: Probando modelo</h3>";
try {
    require_once "model/preformatos.model.php";
    $pasos["include_modelo"] = true;
    echo "<p style='color: green;'>✓ Modelo incluido</p>";
    
    // Test método estático
    if (class_exists('ModelPreformatos')) {
        $pasos["clase_modelo"] = true;
        echo "<p style='color: green;'>✓ Clase ModelPreformatos existe</p>";
    } else {
        $pasos["clase_modelo"] = false;
        echo "<p style='color: red;'>✗ Clase ModelPreformatos no existe</p>";
    }
} catch (Exception $e) {
    $pasos["include_modelo"] = false;
    echo "<p style='color: red;'>✗ Error modelo: " . $e->getMessage() . "</p>";
}

// Paso 5: Incluir controlador
echo "<h3>Paso 5: Probando controlador</h3>";
try {
    require_once "controller/preformatos.controller.php";
    $pasos["include_controller"] = true;
    echo "<p style='color: green;'>✓ Controlador incluido</p>";
    
    if (class_exists('ControllerPreformatos')) {
        $pasos["clase_controller"] = true;
        echo "<p style='color: green;'>✓ Clase ControllerPreformatos existe</p>";
    } else {
        $pasos["clase_controller"] = false;
        echo "<p style='color: red;'>✗ Clase ControllerPreformatos no existe</p>";
    }
} catch (Exception $e) {
    $pasos["include_controller"] = false;
    echo "<p style='color: red;'>✗ Error controlador: " . $e->getMessage() . "</p>";
}

// Paso 6: Test método específico que falla
echo "<h3>Paso 6: Probando método getDoctorByUserId</h3>";
try {
    $_POST['operacion'] = 'getDoctorByUserId';
    $_POST['user_id'] = '9';
    
    // Instanciar la clase AJAX manualmente
    ob_start();
    $preformatos = new PreformatosAjax();
    $preformatos->ajaxGetDoctorByUserId(9);
    $output = ob_get_clean();
    
    $pasos["metodo_getDoctorByUserId"] = true;
    echo "<p style='color: green;'>✓ Método ejecutado</p>";
    echo "<pre>Output: " . htmlspecialchars($output) . "</pre>";
    
} catch (Exception $e) {
    $pasos["metodo_getDoctorByUserId"] = false;
    echo "<p style='color: red;'>✗ Error en método: " . $e->getMessage() . "</p>";
    echo "<p>Archivo: " . $e->getFile() . " - Línea: " . $e->getLine() . "</p>";
}

echo "<h3>Resumen de Verificación</h3>";
echo "<pre>";
foreach ($pasos as $paso => $resultado) {
    echo "$paso: " . ($resultado ? 'OK' : 'FAIL') . "\n";
}
echo "</pre>";

// Limpiar
$_POST = [];
?>
