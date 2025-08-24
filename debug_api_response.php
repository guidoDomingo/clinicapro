<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>🔍 Diagnóstico de Respuesta API</h2>";

// Test directo de la API
$url = "http://localhost/clinica/modules/consultas/api/modern-api.php?action=get_consulta&id=107";

echo "<h3>📡 Llamando API: $url</h3>";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_NOBODY, false);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "<h4>📊 Código HTTP: $httpCode</h4>";

// Separar headers y body
$parts = explode("\r\n\r\n", $response, 2);
$headers = $parts[0];
$body = $parts[1] ?? '';

echo "<h4>📋 Headers:</h4>";
echo "<pre>$headers</pre>";

echo "<h4>📄 Body Response:</h4>";
echo "<pre>" . htmlspecialchars($body) . "</pre>";

echo "<h4>🧪 Test JSON Parse:</h4>";
$jsonData = json_decode($body, true);
if (json_last_error() === JSON_ERROR_NONE) {
    echo "✅ JSON válido<br>";
    echo "<pre>" . print_r($jsonData, true) . "</pre>";
} else {
    echo "❌ JSON inválido: " . json_last_error_msg() . "<br>";
    echo "Primeros 500 caracteres del response:<br>";
    echo "<code>" . htmlspecialchars(substr($body, 0, 500)) . "</code>";
}

echo "<hr>";

// Test directo del DatabaseMapper
echo "<h3>🔧 Test directo DatabaseMapper</h3>";

try {
    require_once 'modules/consultas/core/DatabaseMapper.php';
    
    echo "✅ DatabaseMapper cargado exitosamente<br>";
    
    $mapper = new DatabaseMapper();
    echo "✅ DatabaseMapper instanciado<br>";
    
    $result = $mapper->getConsulta(107);
    echo "✅ getConsulta ejecutado<br>";
    
    echo "<h4>📊 Resultado:</h4>";
    echo "<pre>" . print_r($result, true) . "</pre>";
    
} catch (Exception $e) {
    echo "❌ Error en DatabaseMapper: " . $e->getMessage() . "<br>";
    echo "Stack trace:<br>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>