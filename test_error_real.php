<?php
// Test directo del endpoint que está fallando

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Test Directo - Endpoint que Falla</h2>";

// Simular la petición exacta que está fallando
$_POST['operacion'] = 'getDoctorByUserId';
$_POST['user_id'] = '9'; // El ID que está siendo usado en la aplicación real

echo "<h3>Simulando petición real:</h3>";
echo "<pre>";
print_r($_POST);
echo "</pre>";

echo "<h3>Ejecutando endpoint...</h3>";

try {
    // Incluir directamente el archivo AJAX sin buffer para ver errores
    include 'ajax/preformatos.ajax.php';
} catch (ParseError $e) {
    echo "<p style='color: red;'><strong>Error de sintaxis:</strong></p>";
    echo "<p>Mensaje: " . $e->getMessage() . "</p>";
    echo "<p>Archivo: " . $e->getFile() . "</p>";
    echo "<p>Línea: " . $e->getLine() . "</p>";
} catch (Error $e) {
    echo "<p style='color: red;'><strong>Error fatal:</strong></p>";
    echo "<p>Mensaje: " . $e->getMessage() . "</p>";
    echo "<p>Archivo: " . $e->getFile() . "</p>";
    echo "<p>Línea: " . $e->getLine() . "</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'><strong>Excepción:</strong></p>";
    echo "<p>Mensaje: " . $e->getMessage() . "</p>";
    echo "<p>Archivo: " . $e->getFile() . "</p>";
    echo "<p>Línea: " . $e->getLine() . "</p>";
}

echo "<h3>Verificando archivos requeridos:</h3>";
$archivos = [
    'controller/preformatos.controller.php',
    'model/preformatos.model.php', 
    'model/conexion.php'
];

foreach ($archivos as $archivo) {
    if (file_exists($archivo)) {
        echo "<p style='color: green;'>✓ $archivo existe</p>";
    } else {
        echo "<p style='color: red;'>✗ $archivo NO existe</p>";
    }
}

// Limpiar $_POST
$_POST = [];
?>
