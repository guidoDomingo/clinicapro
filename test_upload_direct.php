<?php
echo "<h2>Test de Upload Directo - Debug</h2>";

// Verificar si el archivo existe
$archivo_origen = 'uploads/consultas/68b2564a82aa7_1756517962.docx';
if (!file_exists($archivo_origen)) {
    echo "❌ Archivo origen no existe: $archivo_origen<br>";
    exit;
}

echo "✅ Archivo origen encontrado<br>";

// Crear datos de prueba para POST
$test_data = [
    'action' => 'upload_archivo',
    'id_consulta' => '179',
    'id_persona' => '53'
];

echo "📋 Datos POST de prueba: " . json_encode($test_data, JSON_PRETTY_PRINT) . "<br>";

// Hacer petición CURL al endpoint
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost/clinica/modules/consultas/api/livewire-system.php');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, [
    'action' => 'upload_archivo',
    'id_consulta' => '179',
    'id_persona' => '53',
    'archivos' => new CurlFile($archivo_origen, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'test_document.docx')
]);

echo "📡 Enviando petición al endpoint...<br>";

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "📊 Código HTTP: $http_code<br>";
echo "📄 Respuesta del servidor:<br>";
echo "<pre>" . htmlspecialchars($response) . "</pre>";
?>