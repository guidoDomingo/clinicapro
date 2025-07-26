<?php
// Test directo del endpoint
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Test AJAX Endpoint - getTiposFormularios</h2>";

// Simular POST
$_POST['operacion'] = 'getTiposFormularios';

try {
    // Incluir el archivo AJAX directamente
    include_once 'ajax/preformatos.ajax.php';
    
} catch (Exception $e) {
    echo "<h3 style='color: red;'>Exception:</h3>";
    echo "<pre>" . $e->getMessage() . "</pre>";
} catch (Error $e) {
    echo "<h3 style='color: red;'>Fatal Error:</h3>";
    echo "<pre>" . $e->getMessage() . "</pre>";
}
?>
