<?php
// Debug: Simular llamada HTTP exacta del frontend
echo "=== SIMULACIÓN HTTP CALL FRONTEND PARA CONSULTA 203 ===\n";

// Configurar URL base
$base_url = 'http://localhost/clinica';
$api_url = $base_url . '/modules/consultas/api/livewire-system.php';

// Datos exactos que envía el frontend
$post_data = [
    'action' => 'get',
    'table' => 'consultas',
    'id' => 203,
    'id_consulta' => 203
];

echo "URL API: $api_url\n";
echo "Datos POST: " . json_encode($post_data, JSON_PRETTY_PRINT) . "\n\n";

// Configurar cURL para simular la llamada
$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, $api_url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

// Simular cookies de sesión si es necesario
$cookie_file = tempnam(sys_get_temp_dir(), 'curl_cookies');
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);

curl_close($ch);

// Limpiar archivo temporal de cookies
if (file_exists($cookie_file)) {
    unlink($cookie_file);
}

echo "=== RESPUESTA HTTP ===\n";
echo "Código HTTP: $http_code\n";

if ($error) {
    echo "❌ Error cURL: $error\n";
} else {
    echo "✅ Respuesta recibida\n";
    echo "Tamaño respuesta: " . strlen($response) . " bytes\n\n";
    
    // Intentar decodificar JSON
    $json_data = json_decode($response, true);
    
    if ($json_data === null) {
        echo "❌ ERROR: Respuesta no es JSON válido\n";
        echo "Respuesta raw (primeros 500 chars):\n";
        echo substr($response, 0, 500) . "\n";
    } else {
        echo "✅ JSON válido decodificado\n";
        
        if (isset($json_data['success']) && $json_data['success']) {
            echo "\n=== DATOS DEL DOCTOR EN RESPUESTA ===\n";
            $data = $json_data['data'] ?? [];
            
            $doctor_fields = [
                'doctor_first_name',
                'doctor_last_name',
                'doctor_email', 
                'doctor_document',
                'doctor_phone'
            ];
            
            $doctor_present = false;
            foreach ($doctor_fields as $field) {
                $value = $data[$field] ?? 'NO PRESENTE';
                echo "$field: $value\n";
                if (!empty($data[$field])) {
                    $doctor_present = true;
                }
            }
            
            if ($doctor_present) {
                echo "\n🎉 DATOS DEL DOCTOR ENCONTRADOS EN API\n";
                
                // Simular extracción JavaScript
                $doctor_nombre = trim(($data['doctor_first_name'] ?? '') . ' ' . ($data['doctor_last_name'] ?? ''));
                echo "Nombre completo: '$doctor_nombre'\n";
                echo "Email: '{$data['doctor_email']}'\n";
                echo "Documento: '{$data['doctor_document']}'\n";
            } else {
                echo "\n❌ DATOS DEL DOCTOR NO ENCONTRADOS\n";
            }
            
            // Verificar otros campos importantes
            echo "\n=== CAMPOS ADICIONALES ===\n";
            echo "ID Consulta: " . ($data['id_consulta'] ?? 'N/A') . "\n";
            echo "ID Usuario: " . ($data['id_user'] ?? 'N/A') . "\n";
            echo "Paciente: " . trim(($data['first_name'] ?? '') . ' ' . ($data['last_name'] ?? '')) . "\n";
            
        } else {
            echo "❌ ERROR EN API:\n";
            echo "Mensaje: " . ($json_data['message'] ?? 'Error desconocido') . "\n";
        }
    }
}

echo "\n=== FIN SIMULACIÓN ===\n";
?>