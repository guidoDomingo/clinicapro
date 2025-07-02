<?php
// Este script se utilizará para diagnosticar por qué no se reciben correctamente los datos del formulario
session_start();

// Crear el archivo de log si no existe
$logFile = dirname(__DIR__) . "/logs/diagnostico_password.log";
file_put_contents($logFile, date('Y-m-d H:i:s') . " - Iniciando diagnóstico de formulario cambio contraseña\n", FILE_APPEND);

// Registrar información del método de solicitud
$requestMethod = $_SERVER['REQUEST_METHOD'];
file_put_contents($logFile, date('Y-m-d H:i:s') . " - Método de solicitud: $requestMethod\n", FILE_APPEND);

// Registrar información de la sesión
if (isset($_SESSION['user_id'])) {
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - Usuario en sesión: " . $_SESSION['user_id'] . "\n", FILE_APPEND);
} else {
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - No hay usuario en sesión\n", FILE_APPEND);
}

// Registrar los datos recibidos por POST
if ($requestMethod === 'POST') {
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - Datos POST recibidos: " . print_r($_POST, true) . "\n", FILE_APPEND);
    
    if (isset($_POST['action']) && $_POST['action'] === 'changePassword') {
        file_put_contents($logFile, date('Y-m-d H:i:s') . " - Acción de cambio de contraseña detectada\n", FILE_APPEND);
        
        $currentPassword = $_POST['current_password'] ?? 'no proporcionada';
        $newPassword = $_POST['new_password'] ?? 'no proporcionada';
        
        file_put_contents($logFile, date('Y-m-d H:i:s') . " - Contraseña actual: " . (empty($currentPassword) ? 'VACÍA' : 'PROPORCIONADA') . "\n", FILE_APPEND);
        file_put_contents($logFile, date('Y-m-d H:i:s') . " - Contraseña nueva: " . (empty($newPassword) ? 'VACÍA' : 'PROPORCIONADA') . "\n", FILE_APPEND);
    } else {
        file_put_contents($logFile, date('Y-m-d H:i:s') . " - No se encontró la acción 'changePassword' en los datos POST\n", FILE_APPEND);
    }
} else {
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - No se recibieron datos por POST\n", FILE_APPEND);
}

// Registrar los encabezados HTTP
$headers = apache_request_headers();
file_put_contents($logFile, date('Y-m-d H:i:s') . " - Encabezados recibidos: " . print_r($headers, true) . "\n", FILE_APPEND);

// Registrar información de la solicitud AJAX (si la hay)
$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
file_put_contents($logFile, date('Y-m-d H:i:s') . " - Es solicitud AJAX: " . ($isAjax ? 'SÍ' : 'NO') . "\n", FILE_APPEND);

// Responder con información de diagnóstico
header('Content-Type: application/json');
echo json_encode([
    'status' => 'diagnostic',
    'timestamp' => date('Y-m-d H:i:s'),
    'request_method' => $requestMethod,
    'is_ajax' => $isAjax,
    'post_data' => $_POST,
    'session_data' => [
        'user_id' => $_SESSION['user_id'] ?? 'no disponible'
    ],
    'message' => 'Este es un mensaje de diagnóstico. Revisa los logs para más detalles.'
]);
?>
