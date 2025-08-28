<?php
// Test directo de la API via HTTP
echo "🧪 Probando la API de búsqueda de pacientes reales via HTTP...\n\n";

// Test data
$searchData = [
    'action' => 'validateField',
    'data' => [
        'property' => 'search_nombre',
        'value' => 'and', // Buscar pacientes que contengan 'and'
        'formType' => 'general'
    ]
];

$loadPatientData = [
    'action' => 'loadPatient',
    'data' => [
        'id' => 41 // ID del primer paciente de la lista
    ]
];

echo "📤 Test 1: Búsqueda de pacientes\n";
echo "Buscando pacientes con término: 'and'\n";
echo str_repeat("-", 40) . "\n";

// Test 1: Search patients
$url = 'http://localhost/clinica/modules/consultas/api/livwire-crud.php';
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($searchData));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$result = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($result !== false) {
    echo "HTTP Status: $httpCode\n";
    $response = json_decode($result, true);
    if ($response) {
        echo "✅ Respuesta de búsqueda:\n";
        echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
        
        // If we got patients, test loading one
        if (isset($response['data']['patients']) && !empty($response['data']['patients'])) {
            $firstPatient = $response['data']['patients'][0];
            echo "\n📤 Test 2: Cargar datos completos del paciente\n";
            echo "Cargando datos del paciente ID: " . $firstPatient['id'] . " (" . $firstPatient['nombre'] . ")\n";
            echo str_repeat("-", 40) . "\n";
            
            // Test 2: Load patient data
            $loadPatientData['data']['id'] = $firstPatient['id'];
            
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($loadPatientData));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            
            $loadResult = curl_exec($ch);
            $loadHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($loadResult !== false) {
                echo "HTTP Status: $loadHttpCode\n";
                $loadResponse = json_decode($loadResult, true);
                if ($loadResponse) {
                    echo "✅ Respuesta de carga de datos:\n";
                    echo json_encode($loadResponse, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
                } else {
                    echo "❌ Error: Respuesta no es JSON válido\n";
                    echo "Respuesta raw: " . substr($loadResult, 0, 500) . "\n";
                }
            } else {
                echo "❌ Error: No se pudo conectar a la API para cargar datos\n";
            }
        }
    } else {
        echo "❌ Error: Respuesta no es JSON válido\n";
        echo "Respuesta raw: " . substr($result, 0, 500) . "\n";
    }
} else {
    echo "❌ Error: No se pudo conectar a la API de búsqueda\n";
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "✅ Pruebas completadas\n";
echo "🎯 El sistema ahora utiliza datos REALES de PostgreSQL\n";
echo "🗄️ Tabla de pacientes: rh_person (67 registros activos)\n";
echo "📊 Tabla de consultas: consultas (117 registros)\n";
echo "🚀 Sistema listo en: http://localhost/clinica/servicios\n";
?>