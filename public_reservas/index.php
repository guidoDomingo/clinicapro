<?php
/**
 * Sistema de Reservas Públicas
 * Ahora requiere autenticación para agendar citas
 */

// Inicializar configuración del entorno
require_once __DIR__ . '/../config/environment_setup.php';
EnvironmentSetup::initialize();

// Incluir configuración portable
require_once __DIR__ . '/config/ConfigPortable.php';

// Configurar sesiones de forma portable
ConfigPortable::configurarSesiones();

// Debug: Registrar información de la sesión de forma portable
ConfigPortable::log("INDEX - Sesión ID: " . session_id() . ", Data: " . json_encode($_SESSION), 'session_debug.log');

// Incluir controladores necesarios
require_once "controller/ReservasPublicController.php";
require_once "controller/AuthController.php";

// Procesar acción de logout ANTES de enviar cualquier contenido
if (isset($_GET['accion']) && $_GET['accion'] === 'logout') {
    AuthController::ctrLogout();
    // La función logout hace redirect y exit, no se ejecutará código posterior
}

// Verificar token de "recordarme" si está habilitado
if (!isset($_SESSION['paciente_id']) && AuthController::ctrVerificarTokenRecordarme()) {
    // El usuario ha sido autenticado por token, continuar
    ConfigPortable::log("Usuario autenticado por token de recordar", 'auth.log');
}

// Procesar acciones de login/registro si se envió el formulario
$resultadoAuth = null;
if (isset($_POST['action'])) {
    // Verificar si es una petición AJAX
    $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    
    // Para peticiones AJAX, suprimir warnings y configurar headers apropiados
    if ($isAjax) {
        error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR);
        ob_clean(); // Limpiar cualquier output previo
        header('Content-Type: application/json');
    }
    
    // Debug: Ver contenido completo del POST
    $postDataClean = $_POST;
    if (isset($postDataClean['password'])) $postDataClean['password'] = '******';
    if (isset($postDataClean['regPassword'])) $postDataClean['regPassword'] = '******';
    if (isset($postDataClean['regConfirmPassword'])) $postDataClean['regConfirmPassword'] = '******';
    
    ConfigPortable::log("POST data: " . json_encode($postDataClean), 'auth.log');
    
    if ($_POST['action'] === 'login') {
        $resultadoAuth = AuthController::ctrLoginUser();
        error_log("Resultado de login en index.php: " . json_encode($resultadoAuth), 3, "c:/laragon/www/clinica/logs/auth.log");
        
        // Si es una petición AJAX, devolver JSON
        if ($isAjax) {
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
            echo json_encode(['existe' => $existe]);
            exit;
        }
    } elseif ($_POST['action'] === 'verificar_documento') {
        // Verificar si un documento ya existe
        $documento = $_POST['documento'] ?? '';
        $existe = ReservasPublicModel::mdlVerificarDocumentoExistente($documento);
        
        if ($isAjax) {
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
