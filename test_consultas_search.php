<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== TEST DE BÚSQUEDA DE CONSULTAS ===\n";

$url = "http://localhost/clinica/modules/consultas/api/livewire-system.php";

// Test 1: Buscar consultas con "alejandro"
echo "\n📤 Test 1: Buscar consultas con 'alejandro'\n";
$data1 = [
    "action" => "search",
    "table" => "consultas", 
    "search" => "alejandro",
    "limit" => 10
];

$response1 = makeRequest($url, $data1);
echo "Resultado: " . (isset($response1['data']) ? count($response1['data']) : 0) . " consultas encontradas\n";

// Test 2: Buscar consultas con "visconte" 
echo "\n📤 Test 2: Buscar consultas con 'visconte'\n";
$data2 = [
    "action" => "search",
    "table" => "consultas",
    "search" => "visconte", 
    "limit" => 10
];

$response2 = makeRequest($url, $data2);
echo "Resultado: " . (isset($response2['data']) ? count($response2['data']) : 0) . " consultas encontradas\n";

// Test 3: Buscar con id_persona:45
echo "\n📤 Test 3: Buscar con 'id_persona:45'\n";
$data3 = [
    "action" => "search",
    "table" => "consultas",
    "search" => "id_persona:45",
    "limit" => 10
];

$response3 = makeRequest($url, $data3);
echo "Resultado: " . (isset($response3['data']) ? count($response3['data']) : 0) . " consultas encontradas\n";

// Test 4: Buscar con person_id:45
echo "\n📤 Test 4: Buscar con 'person_id:45'\n";
$data4 = [
    "action" => "search",
    "table" => "consultas", 
    "search" => "person_id:45",
    "limit" => 10
];

$response4 = makeRequest($url, $data4);
echo "Resultado: " . (isset($response4['data']) ? count($response4['data']) : 0) . " consultas encontradas\n";

// Test 5: Buscar sin filtros
echo "\n📤 Test 5: Buscar todas las consultas (sin filtros)\n";
$data5 = [
    "action" => "search",
    "table" => "consultas",
    "search" => "",
    "limit" => 5
];

$response5 = makeRequest($url, $data5);
echo "Resultado: " . (isset($response5['data']) ? count($response5['data']) : 0) . " consultas encontradas\n";

if (isset($response5['data']) && count($response5['data']) > 0) {
    echo "\n📋 Estructura de la primera consulta:\n";
    $primera = $response5['data'][0];
    foreach ($primera as $key => $value) {
        if (in_array($key, ['id_consulta', 'id_persona', 'person_id', 'first_name', 'last_name', 'fecha_registro'])) {
            echo "  $key: " . (is_null($value) ? 'null' : $value) . "\n";
        }
    }
}

function makeRequest($url, $data) {
    $json = json_encode($data);
    
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $json);
    curl_setopt($curl, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Content-Length: ' . strlen($json)
    ]);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_TIMEOUT, 30);
    
    $response = curl_exec($curl);
    
    if (curl_error($curl)) {
        echo "❌ Error cURL: " . curl_error($curl) . "\n";
        curl_close($curl);
        return null;
    }
    
    curl_close($curl);
    
    $decoded = json_decode($response, true);
    return $decoded;
}

echo "\n=== FIN DEL TEST ===\n";
?>