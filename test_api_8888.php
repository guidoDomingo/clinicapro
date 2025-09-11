<?php
echo "=== PRUEBA API CLÍNICA - PUERTO 8888 ===\n";
echo "Servidor: http://181.122.125.143:8888\n";
echo "Fecha: " . date('Y-m-d H:i:s') . "\n\n";

// Configuración de prueba
$base_url = "http://181.122.125.143:8888";
$endpoints = [
    '/api/departments' => 'Departamentos',
    '/api/cities' => 'Ciudades', 
    '/api/especialidades' => 'Especialidades',
    '/api/persons' => 'Personas',
    '/api/test' => 'Test de conexión'
];

function test_endpoint($url, $name) {
    echo "🔍 Probando: $name\n";
    echo "URL: $url\n";
    
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'timeout' => 10,
            'header' => [
                'Content-Type: application/json',
                'Accept: application/json'
            ]
        ]
    ]);
    
    $start_time = microtime(true);
    $response = @file_get_contents($url, false, $context);
    $end_time = microtime(true);
    $response_time = round(($end_time - $start_time) * 1000, 2);
    
    if ($response === false) {
        echo "❌ ERROR: No se pudo conectar\n";
        $error = error_get_last();
        echo "   Detalles: " . ($error['message'] ?? 'Error desconocido') . "\n";
        return false;
    }
    
    // Verificar headers de respuesta
    $headers = $http_response_header ?? [];
    $status_line = $headers[0] ?? 'HTTP/1.1 200 OK';
    echo "📊 Status: $status_line\n";
    echo "⏱️  Tiempo: {$response_time}ms\n";
    
    // Analizar contenido
    echo "📄 Contenido (" . strlen($response) . " bytes):\n";
    
    // Verificar si es JSON válido
    $json_data = json_decode($response, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        echo "✅ JSON VÁLIDO\n";
        if (is_array($json_data)) {
            echo "   Estructura: Array con " . count($json_data) . " elemento(s)\n";
            if (!empty($json_data)) {
                echo "   Primer elemento: " . json_encode(array_slice($json_data, 0, 1), JSON_PRETTY_PRINT) . "\n";
            }
        } else {
            echo "   Estructura: " . gettype($json_data) . "\n";
            echo "   Contenido: " . json_encode($json_data, JSON_PRETTY_PRINT) . "\n";
        }
        return true;
    } else {
        echo "❌ NO ES JSON VÁLIDO\n";
        echo "   Error JSON: " . json_last_error_msg() . "\n";
        
        // Mostrar inicio del contenido para diagnóstico
        $preview = substr($response, 0, 200);
        echo "   Vista previa: " . $preview . "\n";
        
        // Verificar si es HTML
        if (strpos($response, '<!DOCTYPE') !== false || strpos($response, '<html') !== false) {
            echo "   ⚠️  Respuesta parece ser HTML en lugar de JSON\n";
        }
        return false;
    }
}

echo "==========================================\n";
echo "INICIANDO PRUEBAS DE API\n";
echo "==========================================\n\n";

$success_count = 0;
$total_count = count($endpoints);

foreach ($endpoints as $endpoint => $name) {
    $url = $base_url . $endpoint;
    $result = test_endpoint($url, $name);
    
    if ($result) {
        $success_count++;
        echo "✅ ÉXITO\n";
    } else {
        echo "❌ FALLO\n";
    }
    
    echo "\n" . str_repeat("-", 50) . "\n\n";
}

echo "==========================================\n";
echo "RESUMEN DE PRUEBAS\n";
echo "==========================================\n";
echo "Total de endpoints: $total_count\n";
echo "Exitosos: $success_count\n";
echo "Fallidos: " . ($total_count - $success_count) . "\n";
echo "Porcentaje de éxito: " . round(($success_count / $total_count) * 100, 1) . "%\n";

if ($success_count === $total_count) {
    echo "\n🎉 TODAS LAS PRUEBAS PASARON - API FUNCIONANDO CORRECTAMENTE\n";
} else {
    echo "\n⚠️  ALGUNAS PRUEBAS FALLARON - REVISAR CONFIGURACIÓN\n";
}

echo "\n=== FIN DE PRUEBAS ===\n";
?>