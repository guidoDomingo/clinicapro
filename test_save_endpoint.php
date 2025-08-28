<?php
session_start();

// Test del endpoint de LivewireCRUD para guardado
echo "<h2>🧪 Test LivewireCRUD Save Endpoint</h2>";

// Datos de prueba
$testData = [
    'action' => 'save',
    'formType' => 'anteojos',
    'consultaId' => '161',  // ID de la consulta que estás editando
    'state' => [
        'txtmotivo' => 'Test motivo actualizado',
        'od_esf' => '-19.25',
        'od_cil' => '-5.25',
        'od_eje' => '56',
        'od_dnp' => '32',
        'od_add' => '1',
        'od_nota' => 'Nota de prueba',
        'oi_esf' => '-18.75',
        'oi_cil' => '-4.75',
        'oi_eje' => '123',
        'oi_dnp' => '29',
        'oi_add' => '1.25',
        'dist_interpupilar' => 'dfgfdg',
        'txtnota' => 'dfgfdgdfg',
        'proximaconsulta' => '2025-09-05',
        'consulta_textarea' => 'Consulta de anteojos de prueba actualizada',
        'id_persona' => '45'  // ID del paciente
    ]
];

$jsonData = json_encode($testData);

echo "<h3>📤 Datos a enviar:</h3>";
echo "<pre>" . htmlspecialchars($jsonData) . "</pre>";

// Test del endpoint principal
$endpoint = 'http://localhost/clinica/modules/consultas/api/livewire-crud.php';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $endpoint);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Content-Length: ' . strlen($jsonData)
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "<h3>📨 Respuesta del endpoint:</h3>";
echo "<p><strong>HTTP Code:</strong> $httpCode</p>";

if ($error) {
    echo "<p><strong>❌ Error:</strong> $error</p>";
}

echo "<p><strong>Response:</strong></p>";
echo "<textarea rows='15' cols='100' style='width: 100%; font-family: monospace;'>";
echo htmlspecialchars($response);
echo "</textarea>";

// Test directo en base de datos
echo "<h3>🗄️ Test Directo Base de Datos</h3>";

try {
    $dsn = "pgsql:host=localhost;port=5432;dbname=clinica;";
    $username = "postgres";
    $password = "admin";
    
    $pdo = new PDO($dsn, $username, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    
    // Verificar consulta existente
    $stmt = $pdo->prepare("SELECT * FROM consultas WHERE id = ?");
    $stmt->execute([161]);
    $consulta = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "<h4>📋 Consulta actual (ID 161):</h4>";
    echo "<pre>" . print_r($consulta, true) . "</pre>";
    
    // Verificar datos de anteojos
    $stmt = $pdo->prepare("SELECT * FROM consulta_anteojos WHERE id_consulta = ?");
    $stmt->execute([161]);
    $anteojos = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "<h4>👓 Datos anteojos actuales:</h4>";
    echo "<pre>" . print_r($anteojos, true) . "</pre>";
    
} catch (Exception $e) {
    echo "<p><strong>❌ Error BD:</strong> " . $e->getMessage() . "</p>";
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
h2 { color: #2c3e50; }
h3, h4 { color: #34495e; border-bottom: 1px solid #ecf0f1; padding-bottom: 5px; }
pre { background: #f8f9fa; padding: 10px; border-left: 4px solid #007bff; overflow-x: auto; }
textarea { background: #f8f9fa; border: 1px solid #dee2e6; padding: 10px; }
</style>