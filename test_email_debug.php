<?php
/**
 * Script de prueba para verificar el envío de emails
 */

// Debug: Mostrar errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<!DOCTYPE html>";
echo "<html><body>";
echo "<h2>Prueba de Envío de PDF por Email</h2>";

try {
    echo "<p>1. Verificando estructura de datos...</p>";
    
    // Simular datos de consulta
    $consultaData = [
        'id_consulta' => 203,
        'first_name' => 'Gustavo',
        'last_name' => 'Alfaro',
        'txtmotivo' => '<p>Ojo rojo que puede deberse a conjuntivitis</p>',
        'doctor_first_name' => 'Angel',
        'doctor_last_name' => 'Isnardi',
        'fecha_registro' => '2025-09-01 21:48:24'
    ];
    
    echo "<p>✅ Datos de consulta preparados</p>";
    
    // Simular recipients
    $recipients = [
        [
            'email' => 'test@example.com',
            'name' => 'Usuario Prueba',
            'type' => 'prueba'
        ]
    ];
    
    echo "<p>2. Preparando llamada al API...</p>";
    
    // Crear datos para enviar
    $postData = [
        'action' => 'send_pdf',
        'consulta_id' => 203,
        'recipients' => $recipients,
        'subject' => 'PDF Consulta Médica #203 - Gustavo Alfaro',
        'message' => 'Mensaje de prueba',
        'consulta_data' => $consultaData
    ];
    
    echo "<p>3. Datos preparados:</p>";
    echo "<pre>" . htmlspecialchars(json_encode($postData, JSON_PRETTY_PRINT)) . "</pre>";
    
    // Hacer petición local al API
    $url = 'http://localhost/clinica/modules/mail/api/send_pdf.php';
    $ch = curl_init();
    
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Content-Length: ' . strlen(json_encode($postData))
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    echo "<p>4. Enviando petición...</p>";
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    
    curl_close($ch);
    
    echo "<p>5. Respuesta recibida:</p>";
    echo "<p><strong>HTTP Code:</strong> $httpCode</p>";
    
    if ($error) {
        echo "<p><strong>CURL Error:</strong> $error</p>";
    }
    
    echo "<p><strong>Response:</strong></p>";
    echo "<pre>" . htmlspecialchars($response) . "</pre>";
    
    // Intentar decodificar como JSON
    $jsonResponse = json_decode($response, true);
    if ($jsonResponse) {
        echo "<p>✅ Respuesta JSON válida</p>";
        echo "<pre>" . htmlspecialchars(json_encode($jsonResponse, JSON_PRETTY_PRINT)) . "</pre>";
    } else {
        echo "<p>❌ La respuesta no es JSON válido</p>";
        echo "<p><strong>Tipo de contenido detectado:</strong> " . 
             (strpos($response, '%PDF') === 0 ? 'PDF' : 'Texto/HTML') . "</p>";
    }
    
} catch (Exception $e) {
    echo "<p>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "</body></html>";
?>