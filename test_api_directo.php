<?php
/**
 * TEST DIRECTO DEL ENDPOINT API
 */
header('Content-Type: text/html; charset=UTF-8');

echo "<h1>🔗 Test Directo API Endpoint</h1>";
echo "<p>Fecha: " . date('Y-m-d H:i:s') . "</p>";

// Simular la llamada exacta que hace el frontend
$url = "http://localhost/clinica/modules/consultas/api/consultas-api.php";
$params = [
    'action' => 'get_preformatos_consulta',
    'tipo_formulario' => 'anteojos', 
    'tipo' => 'consulta',
    'usuario_id' => '9'
];

$fullUrl = $url . '?' . http_build_query($params);

echo "<h2>📤 Request Details</h2>";
echo "<p><strong>URL:</strong> $fullUrl</p>";
echo "<p><strong>Parámetros:</strong></p>";
echo "<ul>";
foreach ($params as $key => $value) {
    echo "<li><strong>$key:</strong> $value</li>";
}
echo "</ul>";

// Hacer la llamada
echo "<h2>📥 Response</h2>";

try {
    $context = stream_context_create([
        'http' => [
            'timeout' => 30,
            'user_agent' => 'Test Script'
        ]
    ]);
    
    $response = file_get_contents($fullUrl, false, $context);
    
    if ($response === false) {
        echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; border-radius: 5px;'>";
        echo "❌ Error: No se pudo obtener respuesta del API";
        echo "</div>";
    } else {
        echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px;'>";
        echo "<strong>✅ Respuesta recibida:</strong>";
        echo "</div>";
        
        // Intentar decodificar JSON
        $jsonData = json_decode($response, true);
        
        if ($jsonData !== null) {
            echo "<h3>📊 JSON Decodificado:</h3>";
            echo "<pre style='background: #f8f9fa; border: 1px solid #ddd; padding: 15px; border-radius: 5px; overflow-x: auto;'>";
            echo json_encode($jsonData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            echo "</pre>";
            
            // Analizar resultados
            if (isset($jsonData['success']) && $jsonData['success']) {
                if (isset($jsonData['data']) && is_array($jsonData['data'])) {
                    $count = count($jsonData['data']);
                    echo "<div style='background: #d1ecf1; border: 1px solid #bee5eb; padding: 15px; border-radius: 5px;'>";
                    echo "<strong>📈 Análisis:</strong><br>";
                    echo "• Success: Sí<br>";
                    echo "• Preformatos encontrados: $count<br>";
                    
                    if ($count > 0) {
                        echo "• Primer preformato: " . $jsonData['data'][0]['nombre'] . "<br>";
                        echo "• ID: " . $jsonData['data'][0]['id'] . "<br>";
                    }
                    echo "</div>";
                } else {
                    echo "<div style='background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px;'>";
                    echo "⚠️ API devolvió success=true pero sin datos";
                    echo "</div>";
                }
            } else {
                echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; border-radius: 5px;'>";
                echo "❌ API devolvió success=false<br>";
                if (isset($jsonData['message'])) {
                    echo "Mensaje: " . $jsonData['message'];
                }
                echo "</div>";
            }
            
        } else {
            echo "<h3>📄 Respuesta Raw:</h3>";
            echo "<pre style='background: #f8f9fa; border: 1px solid #ddd; padding: 15px; border-radius: 5px; overflow-x: auto;'>";
            echo htmlspecialchars($response);
            echo "</pre>";
        }
    }
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; border-radius: 5px;'>";
    echo "❌ Excepción: " . $e->getMessage();
    echo "</div>";
}

echo "<hr>";
echo "<p><a href='index.php?ruta=consultas-new' target='_blank' style='background: #007bff; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px;'>🔗 Ir al Formulario de Consultas</a></p>";

?>
