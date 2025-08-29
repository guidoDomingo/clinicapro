<?php
echo "=== TEST DIRECTO DE API DE BÚSQUEDA ===" . PHP_EOL;

// Simular una llamada directa a la API usando cURL
$testData = [
    'action' => 'search',
    'table' => 'rh_person',
    'search' => 'visconte',
    'limit' => 10
];

echo "📤 Datos de prueba: " . json_encode($testData, JSON_PRETTY_PRINT) . PHP_EOL . PHP_EOL;

$url = 'http://localhost/clinica/modules/consultas/api/livewire-system.php';
$jsonData = json_encode($testData);

echo "🔄 Enviando request a: $url" . PHP_EOL;
echo "📦 JSON: $jsonData" . PHP_EOL . PHP_EOL;

// Usar stream context en lugar de cURL para evitar problemas
$context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => 'Content-Type: application/json',
        'content' => $jsonData
    ]
]);

try {
    $response = file_get_contents($url, false, $context);
    
    echo "📨 Respuesta HTTP:" . PHP_EOL;
    echo $response . PHP_EOL . PHP_EOL;
    
    // Parsear respuesta
    $result = json_decode($response, true);
    
    if (json_last_error() === JSON_ERROR_NONE) {
        echo "✅ JSON válido parseado:" . PHP_EOL;
        echo "- Success: " . ($result['success'] ? 'true' : 'false') . PHP_EOL;
        echo "- Message: " . ($result['message'] ?? 'N/A') . PHP_EOL;
        
        if (isset($result['data'])) {
            if (is_array($result['data'])) {
                echo "- Records found: " . count($result['data']) . PHP_EOL;
                foreach ($result['data'] as $i => $record) {
                    echo "  Record $i: " . json_encode($record, JSON_PRETTY_PRINT) . PHP_EOL;
                }
            } else {
                echo "- Data: " . json_encode($result['data'], JSON_PRETTY_PRINT) . PHP_EOL;
            }
        }
        
        if (isset($result['debug'])) {
            echo "- Debug info: " . json_encode($result['debug'], JSON_PRETTY_PRINT) . PHP_EOL;
        }
        
    } else {
        echo "❌ JSON inválido. Error: " . json_last_error_msg() . PHP_EOL;
        echo "Raw response: " . substr($response, 0, 500) . PHP_EOL;
    }
    
} catch (Exception $e) {
    echo "❌ Error en request: " . $e->getMessage() . PHP_EOL;
}

// También probar con otros términos de búsqueda
echo PHP_EOL . "🔍 Probando otras búsquedas..." . PHP_EOL;

$otherSearches = ['ale', 'maria', 'jose', 'test'];

foreach ($otherSearches as $search) {
    $testData['search'] = $search;
    $jsonData = json_encode($testData);
    
    $context = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => 'Content-Type: application/json',
            'content' => $jsonData
        ]
    ]);
    
    try {
        $response = file_get_contents($url, false, $context);
        $result = json_decode($response, true);
        
        $count = 0;
        if (isset($result['data']) && is_array($result['data'])) {
            $count = count($result['data']);
        }
        
        echo "- Búsqueda '$search': $count resultados" . PHP_EOL;
        
    } catch (Exception $e) {
        echo "- Búsqueda '$search': Error - " . $e->getMessage() . PHP_EOL;
    }
}
?>