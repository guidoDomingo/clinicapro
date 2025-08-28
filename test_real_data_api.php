<?php
// Test de la API con datos reales
require_once 'config/config.php';

echo "🧪 Probando la API con datos reales...\n\n";

// Test 1: Búsqueda de pacientes
echo "📋 Test 1: Búsqueda de pacientes\n";
echo "=" . str_repeat("=", 40) . "\n";

$dsn = "pgsql:host=" . $_ENV['DB_HOST'] . ";port=" . $_ENV['DB_PORT'] . ";dbname=" . $_ENV['DB_DATABASE'];
$pdo = new PDO($dsn, $_ENV['DB_USERNAME'], $_ENV['DB_PASSWORD'], [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
]);

// Buscar algunos pacientes para test
$stmt = $pdo->query("SELECT person_id, first_name, last_name, document_number FROM rh_person WHERE is_active = true LIMIT 5");
$testPatients = $stmt->fetchAll();

echo "Pacientes disponibles para prueba:\n";
foreach ($testPatients as $patient) {
    echo "  - ID: {$patient['person_id']}, Nombre: {$patient['first_name']} {$patient['last_name']}, DNI: {$patient['document_number']}\n";
}

echo "\n";

// Test con búsqueda por nombre
if (!empty($testPatients)) {
    $firstPatient = $testPatients[0];
    $searchTerm = substr($firstPatient['first_name'], 0, 3); // Primeras 3 letras
    
    echo "🔍 Buscando pacientes con término: '$searchTerm'\n";
    
    // Simular llamada a la API
    $searchData = [
        'action' => 'validateField',
        'data' => [
            'property' => 'search_nombre',
            'value' => $searchTerm,
            'formType' => 'general'
        ]
    ];
    
    echo "Datos de búsqueda: " . json_encode($searchData) . "\n\n";
    
    // Test directo de la función de búsqueda
    include_once 'modules/consultas/api/livwire-crud.php';
    
    echo "📤 Enviando request a la API...\n";
    
    // Crear un request simulado
    $url = 'http://localhost/clinica/modules/consultas/api/livwire-crud.php';
    $context = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => "Content-Type: application/json\r\n",
            'content' => json_encode($searchData)
        ]
    ]);
    
    $result = @file_get_contents($url, false, $context);
    
    if ($result !== false) {
        echo "✅ Respuesta de la API:\n";
        $response = json_decode($result, true);
        if ($response) {
            echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
        } else {
            echo "❌ Error: Respuesta no es JSON válido\n";
            echo "Respuesta raw: " . substr($result, 0, 500) . "\n";
        }
    } else {
        echo "❌ Error: No se pudo conectar a la API\n";
    }
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "✅ Test completado\n";
echo "🎯 El sistema ahora está configurado para usar datos REALES de PostgreSQL\n";
echo "🚀 Puede probar el sistema en: http://localhost/clinica/servicios\n";
?>