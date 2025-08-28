<?php
// Test simple para verificar acceso directo al API
echo "🧪 Test directo del API desde la raíz del proyecto\n";

// Verificar si existe el archivo API
$apiPath = './modules/consultas/api/livwire-crud.php';
if (file_exists($apiPath)) {
    echo "✅ Archivo API existe: $apiPath\n";
} else {
    echo "❌ Archivo API NO existe: $apiPath\n";
}

// Hacer request interno
try {
    $url = 'http://localhost' . $_SERVER['REQUEST_URI'] . 'modules/consultas/api/livwire-crud.php';
    echo "🌐 Probando URL: $url\n";
    
    $testData = json_encode([
        'action' => 'validateField',
        'data' => [
            'property' => 'search_nombre',
            'value' => 'visconte',
            'formType' => 'general'
        ]
    ]);
    
    $context = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => "Content-type: application/json\r\n",
            'content' => $testData
        ]
    ]);
    
    $result = @file_get_contents($url, false, $context);
    
    if ($result !== false) {
        echo "✅ API responde correctamente\n";
        $response = json_decode($result, true);
        if ($response && isset($response['success']) && $response['success']) {
            echo "✅ Respuesta válida con " . count($response['data']['patients'] ?? []) . " pacientes\n";
        } else {
            echo "⚠️ Respuesta inválida: " . substr($result, 0, 100) . "\n";
        }
    } else {
        echo "❌ No se pudo acceder al API desde $url\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n📍 Información del servidor:\n";
echo "REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? 'N/A') . "\n";
echo "DOCUMENT_ROOT: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'N/A') . "\n";
echo "PHP_SELF: " . ($_SERVER['PHP_SELF'] ?? 'N/A') . "\n";
?>