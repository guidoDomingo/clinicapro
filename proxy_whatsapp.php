<?php
/**
 * Proxy para enviar PDFs por WhatsApp
 * Este script actúa como intermediario para evitar problemas de CORS
 */

// Establecer tipo de contenido como JSON
header('Content-Type: application/json');

// Permitir solo solicitudes POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

// Verificar parámetros necesarios
if (empty($_POST['telefono']) || empty($_POST['mediaUrl'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Faltan parámetros requeridos: telefono y mediaUrl']);
    exit;
}

// Obtener los parámetros
$telefono = $_POST['telefono'];
$mediaUrl = $_POST['mediaUrl'];

// Registrar la solicitud en log
error_log("Enviando PDF a WhatsApp - Teléfono: $telefono, URL: $mediaUrl");

// Comprobar si cURL está disponible
if (!function_exists('curl_init')) {
    error_log("cURL no está disponible. Usando método alternativo.");
}

// Función para realizar la solicitud al servidor de WhatsApp
function enviarSolicitudWhatsapp($telefono, $mediaUrl) {
    // Credenciales para Basic Auth
    $username = 'admin';
    $password = '1234';
    
    // URL de la API
    $apiUrl = 'http://aventisdev.com:8082/media.php';
    
    // Configurar datos de la solicitud
    $data = [
        'telefono' => $telefono,
        'mediaUrl' => $mediaUrl
    ];
    
    // Si cURL está disponible, usarlo (es más completo y flexible)
    if (function_exists('curl_init')) {
        // Configuración de la solicitud cURL
        $ch = curl_init($apiUrl);
        
        // Configurar opciones de cURL
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        // Opcional: si necesitas ignorar errores de SSL
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        // Ejecutar la solicitud y obtener la respuesta
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        $errorNo = curl_errno($ch);
        
        // Cerrar la conexión cURL
        curl_close($ch);
        
        // Registro de la respuesta
        error_log("Respuesta de WhatsApp API (cURL) - HTTP Code: $httpCode, Respuesta: $response, Error: $error ($errorNo)");
        
        // Devolver la información de la solicitud
        return [
            'response' => $response,
            'httpCode' => $httpCode,
            'error' => $error,
            'errorNo' => $errorNo
        ];
    } 
    // Si cURL no está disponible, usar file_get_contents con flujos HTTP
    else {
        // Preparar los datos para POST
        $postData = http_build_query($data);
        
        // Configurar las opciones del contexto
        $options = [
            'http' => [
                'method' => 'POST',
                'header' => "Content-Type: application/x-www-form-urlencoded\r\n" .
                            "Authorization: Basic " . base64_encode("$username:$password") . "\r\n" .
                            "Content-Length: " . strlen($postData) . "\r\n",
                'content' => $postData,
                'timeout' => 30
            ],
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false
            ]
        ];
        
        // Crear el contexto
        $context = stream_context_create($options);
        
        // Intentar realizar la solicitud
        $result = null;
        $httpCode = 200;
        $errorMessage = '';
        $errorNo = 0;
        
        try {
            // Intentar realizar la solicitud
            $result = @file_get_contents($apiUrl, false, $context);
            
            // Verificar si hubo error
            if ($result === false) {
                $httpCode = 500;
                $errorMessage = 'Error al realizar la solicitud con file_get_contents';
                $errorNo = 1;
            }
            
            // Obtener el código de respuesta HTTP si está disponible
            if (isset($http_response_header)) {
                foreach ($http_response_header as $header) {
                    if (preg_match('#HTTP/[0-9\.]+\s+([0-9]+)#', $header, $matches)) {
                        $httpCode = intval($matches[1]);
                        break;
                    }
                }
            }
        } catch (Exception $e) {
            $errorMessage = 'Excepción: ' . $e->getMessage();
            $errorNo = $e->getCode();
            $httpCode = 500;
        }
        
        // Registro de la respuesta
        error_log("Respuesta de WhatsApp API (stream) - HTTP Code: $httpCode, Respuesta: $result, Error: $errorMessage ($errorNo)");
        
        // Devolver la información de la solicitud
        return [
            'response' => $result,
            'httpCode' => $httpCode,
            'error' => $errorMessage,
            'errorNo' => $errorNo
        ];
    }
}

// Enviar la solicitud al servidor de WhatsApp
$result = enviarSolicitudWhatsapp($telefono, $mediaUrl);

// Verificar si hubo un error en la solicitud al servidor de WhatsApp
if ($result['error']) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error en la comunicación con el servidor de WhatsApp: ' . $result['error'],
        'error_code' => $result['errorNo'],
        'method_used' => function_exists('curl_init') ? 'curl' : 'file_get_contents',
        'php_info' => [
            'php_version' => phpversion(),
            'curl_available' => function_exists('curl_init'),
            'allow_url_fopen' => ini_get('allow_url_fopen')
        ]
    ]);
    exit;
}

// Intentar decodificar la respuesta como JSON
$responseData = json_decode($result['response'], true);

// Si la respuesta es un JSON válido, devolverlo tal cual con información adicional
if (json_last_error() === JSON_ERROR_NONE) {
    // Establecer el código HTTP basado en la respuesta del servidor de WhatsApp
    http_response_code($result['httpCode']);
    
    // Añadir información adicional a la respuesta JSON
    $responseData['_proxy_info'] = [
        'method_used' => function_exists('curl_init') ? 'curl' : 'file_get_contents',
        'time' => date('Y-m-d H:i:s')
    ];
    
    // Devolver la respuesta mejorada
    echo json_encode($responseData);
    exit;
}

// Si no es un JSON válido, envolver la respuesta en un formato JSON
http_response_code($result['httpCode'] >= 200 && $result['httpCode'] < 300 ? 200 : $result['httpCode']);
echo json_encode([
    'success' => $result['httpCode'] >= 200 && $result['httpCode'] < 300,
    'message' => $result['response'],
    'http_code' => $result['httpCode'],
    'method_used' => function_exists('curl_init') ? 'curl' : 'file_get_contents',
    'time' => date('Y-m-d H:i:s')
]);
