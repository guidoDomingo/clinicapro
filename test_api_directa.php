<?php
// Probar API directamente
$testData = [
    'id_persona' => 45,
    'tipo_formulario' => 'anteojos',
    'txtmotivo' => 'Prueba directa anteojos',
    'od_esf' => '-1.50',
    'od_cil' => '-0.50'
];

echo "🧪 Prueba directa de API para anteojos\n";
echo "=====================================\n";

$apiUrl = "http://localhost/clinica/modules/consultas/api/modern-api.php?action=create_consulta";

$context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => 'Content-Type: application/json',
        'content' => json_encode($testData)
    ]
]);

echo "📤 Enviando datos: " . json_encode($testData) . "\n\n";

$result = file_get_contents($apiUrl, false, $context);

echo "📨 Respuesta raw: " . $result . "\n\n";

if ($result) {
    $response = json_decode($result, true);
    if ($response) {
        echo "📊 Respuesta decodificada:\n";
        print_r($response);
    } else {
        echo "❌ Error decodificando JSON\n";
        echo "JSON Error: " . json_last_error_msg() . "\n";
    }
} else {
    echo "❌ No se obtuvo respuesta de la API\n";
}
?>