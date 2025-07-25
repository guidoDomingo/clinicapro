<?php
header('Content-Type: text/html; charset=UTF-8');
echo "<!DOCTYPE html>";
echo "<html><head><meta charset='UTF-8'><title>Debug: Operaciones AJAX y Tipos</title>";
echo "<style>body{font-family:monospace;margin:20px;} .section{margin:20px 0; padding:15px; border:1px solid #ccc;} .error{color:red;} .success{color:green;} .info{color:blue;} .warning{color:orange;}</style>";
echo "</head><body>";

echo "<h1>🔍 Debug: Operaciones AJAX para cada tipo</h1>";

// Simular exactamente lo que hace el JavaScript
$simulaciones = [
    ['tipoFormulario' => 'anteojos', 'tipoPreformato' => 'consulta'],
    ['tipoFormulario' => 'anteojos', 'tipoPreformato' => 'receta'],
    ['tipoFormulario' => 'estudios', 'tipoPreformato' => 'consulta'],
    ['tipoFormulario' => 'estudios', 'tipoPreformato' => 'receta'],
    ['tipoFormulario' => 'general', 'tipoPreformato' => 'consulta'],
    ['tipoFormulario' => 'general', 'tipoPreformato' => 'receta'],
];

foreach ($simulaciones as $sim) {
    $tipoFormulario = $sim['tipoFormulario'];
    $tipoPreformato = $sim['tipoPreformato'];
    
    echo "<div class='section'>";
    echo "<h2>Test: {$tipoFormulario} - {$tipoPreformato}</h2>";
    
    // Replicar la lógica del JavaScript
    if ($tipoFormulario === 'anteojos') {
        $operacion = 'getPreformatosReceta';
    } else if ($tipoFormulario === 'estudios') {
        $operacion = 'getPreformatos' . ucfirst($tipoPreformato);
    } else {
        $operacion = 'getPreformatos' . ucfirst($tipoPreformato);
    }
    
    echo "<p><strong>Operación calculada por JS:</strong> <code>{$operacion}</code></p>";
    echo "<p><strong>Tipo formulario:</strong> <code>{$tipoFormulario}</code></p>";
    echo "<p><strong>Tipo preformato:</strong> <code>{$tipoPreformato}</code></p>";
    
    // Hacer la petición real
    $_POST = [
        'operacion' => $operacion,
        'tipo_formulario' => $tipoFormulario,
        'usuario_id' => '1'
    ];
    
    echo "<h3>Resultado de la petición AJAX:</h3>";
    echo "<pre>";
    
    ob_start();
    try {
        include 'ajax/preformatos.ajax.php';
        $resultado = ob_get_contents();
        ob_end_clean();
        
        echo htmlspecialchars($resultado);
        
        // Intentar decodificar JSON para mostrar de forma más clara
        $json = json_decode($resultado, true);
        if ($json) {
            echo "\n\n--- Decodificado ---\n";
            echo "Status: " . ($json['status'] ?? 'N/A') . "\n";
            if (isset($json['data'])) {
                echo "Cantidad de resultados: " . (is_array($json['data']) ? count($json['data']) : 'N/A') . "\n";
                if (is_array($json['data']) && count($json['data']) > 0) {
                    echo "Primer resultado: " . $json['data'][0]['nombre'] . "\n";
                }
            }
            if (isset($json['message'])) {
                echo "Mensaje: " . $json['message'] . "\n";
            }
        }
        
    } catch (Exception $e) {
        ob_end_clean();
        echo "ERROR: " . $e->getMessage();
    }
    
    echo "</pre>";
    echo "</div>";
    
    // Limpiar POST para la siguiente iteración
    $_POST = [];
}

echo "<div class='section'>";
echo "<h2>🎯 Análisis</h2>";
echo "<p>Este test muestra exactamente qué operación está calculando el JavaScript y qué respuesta está dando el servidor para cada combinación de tipo de formulario y tipo de preformato.</p>";
echo "</div>";

echo "</body></html>";
?>
