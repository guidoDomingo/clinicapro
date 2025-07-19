<?php
/**
 * Sistema de Reservas Públicas
 * Ahora requiere autenticación para agendar citas
 */

// Iniciar sesión si no está iniciada
if (session_status() == PHP_SESSION_NONE) {
    // Configurar las sesiones para compartir entre dominios
    session_set_cookie_params([
        'lifetime' => 3600,
        'path' => '/',
        'domain' => '.clinica.test',
        'secure' => false,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    
    session_start();
}

// Debug: Registrar información de la sesión
error_log("INDEX - Sesión ID: " . session_id() . ", Data: " . json_encode($_SESSION), 3, "c:/laragon/www/clinica/logs/session_debug.log");

// Incluir controladores necesarios
require_once "controller/ReservasPublicController.php";
require_once "controller/AuthController.php";

// Verificar token de "recordarme" si está habilitado
if (!isset($_SESSION['paciente_id']) && AuthController::ctrVerificarTokenRecordarme()) {
    // El usuario ha sido autenticado por token, continuar
    error_log("Usuario autenticado por token de recordar", 3, "c:/laragon/www/clinica/logs/auth.log");
}

// Procesar acciones de login/registro si se envió el formulario
$resultadoAuth = null;
if (isset($_POST['action'])) {
    // Debug: Ver contenido completo del POST
    $postDataClean = $_POST;
    if (isset($postDataClean['password'])) $postDataClean['password'] = '******';
    if (isset($postDataClean['regPassword'])) $postDataClean['regPassword'] = '******';
    if (isset($postDataClean['regConfirmPassword'])) $postDataClean['regConfirmPassword'] = '******';
    
    error_log("POST data: " . json_encode($postDataClean), 3, "c:/laragon/www/clinica/logs/auth.log");
    
    // Verificar si es una petición AJAX
    $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    
    if ($_POST['action'] === 'login') {
        $resultadoAuth = AuthController::ctrLoginUser();
        error_log("Resultado de login en index.php: " . json_encode($resultadoAuth), 3, "c:/laragon/www/clinica/logs/auth.log");
        
        // Si es una petición AJAX, devolver JSON
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode($resultadoAuth);
            exit;
        }
        
        // Redirección automática si el login es exitoso
        if ($resultadoAuth && isset($resultadoAuth['error']) && !$resultadoAuth['error'] && isset($resultadoAuth['redirect'])) {
            $_SESSION['auth_message'] = $resultadoAuth['mensaje'];
            error_log("Redireccionando después de login exitoso a: " . $resultadoAuth['redirect'], 3, "c:/laragon/www/clinica/logs/auth.log");
            header("Location: " . $resultadoAuth['redirect']);
            exit;
        }
    } elseif ($_POST['action'] === 'register') {
        $resultadoAuth = AuthController::ctrRegisterUser();
        error_log("Resultado de registro en index.php: " . json_encode($resultadoAuth), 3, "c:/laragon/www/clinica/logs/auth.log");
        
        // Si es una petición AJAX, devolver JSON
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode($resultadoAuth);
            exit;
        }
        
        // Redirección automática si el registro es exitoso
        if ($resultadoAuth && isset($resultadoAuth['error']) && !$resultadoAuth['error'] && isset($resultadoAuth['redirect'])) {
            $_SESSION['auth_message'] = $resultadoAuth['mensaje'];
            error_log("Redireccionando después de registro exitoso a: " . $resultadoAuth['redirect'], 3, "c:/laragon/www/clinica/logs/auth.log");
            header("Location: " . $resultadoAuth['redirect']);
            exit;
        }
    } elseif ($_POST['action'] === 'verificar_email') {
        // Verificar si un email ya existe
        $email = $_POST['email'] ?? '';
        $existe = ReservasPublicModel::mdlVerificarEmailExistente($email);
        
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['existe' => $existe]);
            exit;
        }
    } elseif ($_POST['action'] === 'verificar_documento') {
        // Verificar si un documento ya existe
        $documento = $_POST['documento'] ?? '';
        $existe = ReservasPublicModel::mdlVerificarDocumentoExistente($documento);
        
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['existe' => $existe]);
            exit;
        }
    }
    
    // Si llegamos aquí, hubo un error o resultadoAuth es null
    if (!$resultadoAuth) {
        error_log("Error: resultadoAuth es null después de procesar " . $_POST['action'], 3, "c:/laragon/www/clinica/logs/auth.log");
        $resultadoAuth = [
            'error' => true,
            'mensaje' => 'Error al procesar la solicitud. Por favor intente nuevamente.'
        ];
        
        // Si es una petición AJAX, devolver JSON
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode($resultadoAuth);
            exit;
        }
    }
}

// Arrancar la aplicación
$reservas = new ReservasPublicController();
$reservas->iniciarAplicacion($resultadoAuth);
?>
