<?php
// Test directo del endpoint AJAX para identificar error 500

// Activar reporte de errores para debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Test directo - AJAX Preformatos</h2>";

// Simular datos POST
$_POST['operacion'] = 'getDoctorByUserId';
$_POST['user_id'] = '1';

echo "<h3>Datos POST simulados:</h3>";
echo "<pre>" . print_r($_POST, true) . "</pre>";

echo "<h3>Intentando ejecutar ajax/preformatos.ajax.php:</h3>";

try {
    // Capturar toda la salida
    ob_start();
    
    // Incluir el archivo AJAX
    include 'ajax/preformatos.ajax.php';
    
    $output = ob_get_clean();
    
    echo "<h4>Salida del script:</h4>";
    echo "<pre style='background: #f0f0f0; padding: 10px;'>" . htmlspecialchars($output) . "</pre>";
    
    // Verificar si es JSON válido
    $json = json_decode($output, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        echo "<p style='color: green;'><strong>✓ JSON válido</strong></p>";
        echo "<h4>Datos decodificados:</h4>";
        echo "<pre>" . print_r($json, true) . "</pre>";
    } else {
        echo "<p style='color: red;'><strong>✗ JSON inválido</strong></p>";
        echo "<p>Error: " . json_last_error_msg() . "</p>";
    }
    
} catch (ParseError $e) {
    echo "<p style='color: red;'><strong>Error de sintaxis PHP:</strong></p>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "<p>Archivo: " . $e->getFile() . "</p>";
    echo "<p>Línea: " . $e->getLine() . "</p>";
} catch (Error $e) {
    echo "<p style='color: red;'><strong>Error fatal PHP:</strong></p>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "<p>Archivo: " . $e->getFile() . "</p>";
    echo "<p>Línea: " . $e->getLine() . "</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'><strong>Excepción:</strong></p>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "<p>Archivo: " . $e->getFile() . "</p>";
    echo "<p>Línea: " . $e->getLine() . "</p>";
}

// Limpiar $_POST
$_POST = [];
?>
