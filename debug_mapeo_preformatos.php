<?php
// Prueba específica del mapeo y consulta de preformatos
require_once 'controller/preformatos.controller.php';

echo "<h2>Prueba específica del mapeo de tipos de formulario</h2>";

// Simular una petición de preformatos de consulta con form_type=informe_imagen
echo "<h3>Simulando petición: getPreformatosConsulta con tipo_formulario='informe_imagen'</h3>";

try {
    $resultado = ControllerPreformatos::ctrGetPreformatos('consulta', null, 'informe_imagen');
    
    echo "<h4>Resultado de ctrGetPreformatos('consulta', null, 'informe_imagen'):</h4>";
    echo "<pre>" . print_r($resultado, true) . "</pre>";
    
    echo "<h4>Total de preformatos encontrados: " . count($resultado) . "</h4>";
    
} catch (Exception $e) {
    echo "<h4>Error:</h4>";
    echo "<pre>" . $e->getMessage() . "</pre>";
}

// También probar con el ID directo
echo "<h3>Comparación: getPreformatosConsulta con tipo_formulario='4' (ID directo)</h3>";

try {
    $resultado = ControllerPreformatos::ctrGetPreformatos('consulta', null, '4');
    
    echo "<h4>Resultado de ctrGetPreformatos('consulta', null, '4'):</h4>";
    echo "<pre>" . print_r($resultado, true) . "</pre>";
    
    echo "<h4>Total de preformatos encontrados: " . count($resultado) . "</h4>";
    
} catch (Exception $e) {
    echo "<h4>Error:</h4>";
    echo "<pre>" . $e->getMessage() . "</pre>";
}

// Probar el mapeo directamente
echo "<h3>Prueba del mapeo directo:</h3>";
$mapeos = ['informe_imagen', 'anteojos', 'general', '4', '1', '2'];

echo "<table border='1'>";
echo "<tr><th>Código Original</th><th>Código Mapeado</th></tr>";
foreach ($mapeos as $codigo) {
    $mapeado = ControllerPreformatos::mapearTipoFormulario($codigo);
    echo "<tr><td>$codigo</td><td>$mapeado</td></tr>";
}
echo "</table>";
?>
