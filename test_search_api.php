<?php
// Test de la búsqueda con tabla rh_person
$postData = [
    'action' => 'search',
    'table' => 'rh_person',
    'nombres' => 'guido',
    'apellidos' => 'guido', 
    'ci' => 'guido'
];

$postString = http_build_query($postData);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost/clinica/modules/consultas/api/livewire-system.php');
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postString);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/x-www-form-urlencoded',
    'Content-Length: ' . strlen($postString)
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "=== TEST BÚSQUEDA INTELIGENTE ===\n";
echo "HTTP Code: $httpCode\n";
echo "Response:\n";
echo $response . "\n";

// Decodificar JSON para análisis
$data = json_decode($response, true);

if ($data && isset($data['success']) && $data['success']) {
    echo "\n=== PACIENTES ENCONTRADOS ===\n";
    if (isset($data['data']) && is_array($data['data'])) {
        foreach ($data['data'] as $i => $patient) {
            $fullName = ($patient['first_name'] ?? 'N/A') . ' ' . ($patient['last_name'] ?? 'N/A');
            $ci = $patient['document_number'] ?? 'Sin CI';
            echo ($i + 1) . ". $fullName (CI: $ci)\n";
        }
    } else {
        echo "No se encontraron pacientes.\n";
    }
} else {
    echo "Error en la búsqueda: " . ($data['message'] ?? 'Respuesta inválida') . "\n";
}
?>