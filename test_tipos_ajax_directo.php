<?php
echo "<h2>Test directo del endpoint AJAX para tipos de formularios</h2>";
echo "Simulando petición POST con operacion='getTiposFormularios'<br><br>";

// Simular la petición POST
$_POST['operacion'] = 'getTiposFormularios';

// Capturar cualquier salida
ob_start();

try {
    // Incluir el archivo AJAX
    include 'ajax/preformatos.ajax.php';
    
    // Crear instancia y ejecutar
    $ajax = new PreformatosAjax();
    
    // Llamar directamente al método
    $resultado = $ajax->ajaxGetTiposFormularios();
    
    echo "<h3>Resultado:</h3>";
    echo "<pre>";
    var_dump($resultado);
    echo "</pre>";
    
} catch (Exception $e) {
    echo "<h3 style='color: red;'>Error capturado:</h3>";
    echo "<pre style='color: red;'>";
    echo $e->getMessage();
    echo "\nStack trace:\n";
    echo $e->getTraceAsString();
    echo "</pre>";
} catch (Error $e) {
    echo "<h3 style='color: red;'>Fatal Error capturado:</h3>";
    echo "<pre style='color: red;'>";
    echo $e->getMessage();
    echo "\nStack trace:\n";
    echo $e->getTraceAsString();
    echo "</pre>";
}

// Obtener cualquier salida capturada
$output = ob_get_clean();
if ($output) {
    echo "<h3>Salida capturada:</h3>";
    echo "<pre>";
    echo htmlspecialchars($output);
    echo "</pre>";
}
?>
