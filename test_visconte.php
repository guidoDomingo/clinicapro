<?php
// Test rápido para verificar la API
echo "🧪 Probando búsqueda con 'visconte'...\n";

// Test data
$testData = json_encode([
    'action' => 'validateField',
    'data' => [
        'property' => 'search_nombre',
        'value' => 'visconte',
        'formType' => 'general'
    ]
]);

echo "📤 Enviando: $testData\n\n";

// Usar file_get_contents para simular POST
$context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => "Content-type: application/json\r\n",
        'content' => $testData
    ]
]);

$result = file_get_contents('http://localhost/clinica/modules/consultas/api/livwire-crud.php', false, $context);

if ($result !== false) {
    echo "✅ Respuesta recibida:\n";
    $response = json_decode($result, true);
    if ($response) {
        echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    } else {
        echo "Raw response: $result\n";
    }
} else {
    echo "❌ Error: No se pudo conectar a la API\n";
}
?>