<?php
// Test directo del endpoint AJAX de tipos de formularios
echo "<h2>Test directo del endpoint AJAX</h2>";
echo "<style>body { font-family: Arial, sans-serif; margin: 20px; }</style>";

// Simular la petición POST
$_POST['operacion'] = 'getTiposFormularios';

echo "<p><strong>Simulando petición POST con operacion='getTiposFormularios'</strong></p>";

// Incluir el archivo AJAX
try {
    // Capturar la salida
    ob_start();
    include 'ajax/preformatos.ajax.php';
    $output = ob_get_contents();
    ob_end_clean();
    
    echo "<h3>Respuesta del endpoint:</h3>";
    echo "<pre>" . htmlspecialchars($output) . "</pre>";
    
    // Intentar decodificar como JSON
    $json = json_decode($output, true);
    if ($json) {
        echo "<h3>JSON decodificado:</h3>";
        echo "<pre>" . print_r($json, true) . "</pre>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>
