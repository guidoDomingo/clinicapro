<?php
/**
 * Script para probar la conexión con el servidor de WhatsApp y verificar posibles problemas CORS
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Si es una solicitud OPTIONS (preflight), responder solo con los encabezados CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit;
}

echo json_encode([
    'status' => 'success',
    'message' => 'Conexión con el proxy establecida correctamente',
    'timestamp' => date('Y-m-d H:i:s'),
    'testing_api' => 'http://aventisdev.com:8082/media.php'
]);

// Si se proporciona un parámetro para probar la conexión con el API de WhatsApp
if (isset($_GET['test_api']) && $_GET['test_api'] === 'true') {
    // Configuración de la solicitud cURL
    $ch = curl_init('http://aventisdev.com:8082/media.php');
    
    // Configurar datos de la solicitud - solo para verificar conectividad, no envía realmente un mensaje
    $data = [
        'telefono' => '123456789',
        'mediaUrl' => 'http://example.com/test.pdf',
        'test' => 'true'
    ];
    
    // Configurar opciones de cURL
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($ch, CURLOPT_USERPWD, 'admin:1234');
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    // Ejecutar la solicitud y obtener la respuesta
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    $errorNo = curl_errno($ch);
    
    // Cerrar la conexión cURL
    curl_close($ch);
    
    // Añadir información de la prueba a la respuesta
    echo json_encode([
        'api_test' => [
            'status' => $error ? 'error' : 'success',
            'http_code' => $httpCode,
            'response' => $response,
            'error' => $error,
            'error_code' => $errorNo
        ]
    ]);
}
