<?php
/**
 * Test simple de API - para verificar que las rutas básicas funcionan
 */

// Habilitar errores para debug
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>🧪 Test de Rutas API</h1>";

$baseUrl = "http://181.122.125.143:8888/api";

$routes = [
    'departments' => "$baseUrl/departments",
    'especialidades' => "$baseUrl/especialidades", 
    'persons' => "$baseUrl/persons?page=1&per_page=5"
];

foreach ($routes as $name => $url) {
    echo "<h2>Testing: $name</h2>";
    echo "URL: $url<br>";
    
    try {
        $context = stream_context_create([
            'http' => [
                'timeout' => 10,
                'method' => 'GET',
                'header' => [
                    'Accept: application/json',
                    'User-Agent: API-Test-Script'
                ]
            ]
        ]);
        
        $result = file_get_contents($url, false, $context);
        
        if ($result === false) {
            echo "❌ Error: No se pudo conectar<br>";
            if (isset($http_response_header)) {
                echo "Headers: " . implode(', ', $http_response_header) . "<br>";
            }
        } else {
            $json = json_decode($result, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                echo "✅ Respuesta JSON válida<br>";
                echo "Status: " . (isset($json['status']) ? $json['status'] : 'N/A') . "<br>";
                if (isset($json['data'])) {
                    echo "Datos: " . count($json['data']) . " elementos<br>";
                }
            } else {
                echo "❌ Respuesta no es JSON válido<br>";
                echo "Contenido: " . substr($result, 0, 200) . "...<br>";
            }
        }
    } catch (Exception $e) {
        echo "❌ Exception: " . $e->getMessage() . "<br>";
    }
    
    echo "<hr>";
}

echo "<h2>🔍 Info del servidor</h2>";
echo "PHP Version: " . phpversion() . "<br>";
echo "Server: " . $_SERVER['SERVER_SOFTWARE'] . "<br>";
echo "Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "<br>";

?>