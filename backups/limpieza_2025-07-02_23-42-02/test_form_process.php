<?php
// Archivo de procesamiento para el formulario de prueba

// Iniciar sesión si no está iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Simular que el usuario está autenticado
$_SESSION['paciente_id'] = 1;
$_SESSION['paciente_nombre'] = 'Usuario de Prueba';
$_SESSION['paciente_email'] = 'test@example.com';

// Registrar los datos recibidos
error_log("test_form_process.php: Recibiendo datos del formulario. Método: " . $_SERVER['REQUEST_METHOD'], 
    3, "c:/laragon/www/clinica/logs/test_form.log");

if (!empty($_POST)) {
    error_log("test_form_process.php: Contenido de POST: " . json_encode($_POST), 
        3, "c:/laragon/www/clinica/logs/test_form.log");
        
    // Verificar si se recibió el parámetro guardarReserva
    if (isset($_POST['guardarReserva'])) {
        error_log("test_form_process.php: Se recibió el parámetro guardarReserva", 
            3, "c:/laragon/www/clinica/logs/test_form.log");
    } else {
        error_log("test_form_process.php: NO se recibió el parámetro guardarReserva", 
            3, "c:/laragon/www/clinica/logs/test_form.log");
    }
    
    // Incluir el controlador de reservas
    require_once 'controller/ReservasPublicController.php';
    
    // Procesar la reserva
    $reservasController = new ReservasPublicController();
    $resultadoReserva = $reservasController->ctrProcesarReserva();
    
    // Registrar el resultado
    error_log("test_form_process.php: Resultado del procesamiento: " . json_encode($resultadoReserva), 
        3, "c:/laragon/www/clinica/logs/test_form.log");
    
    // Mostrar el resultado
    echo '<!DOCTYPE html>
    <html>
    <head>
        <title>Resultado del Test</title>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
    </head>
    <body>
        <h1>Resultado del Formulario de Prueba</h1>
        
        <pre>' . print_r($resultadoReserva, true) . '</pre>
        
        <a href="test_form.php">Volver al formulario</a>
    </body>
    </html>';
} else {
    echo 'No se recibieron datos del formulario.';
}
?>
