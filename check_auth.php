<?php
/**
 * Script de verificación de autenticación y cookies
 * Permite diagnosticar problemas con la sesión compartida entre sistemas
 */

// Configurar para mostrar errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Verificación de Autenticación</h1>";

echo "<h2>Información de PHP</h2>";
echo "<p>PHP Version: " . phpversion() . "</p>";
echo "<p>Server Name: " . $_SERVER['SERVER_NAME'] . "</p>";

// Si no hay sesión iniciada, configurarla y luego iniciarla
if (session_status() == PHP_SESSION_NONE) {
    echo "<p>Estado inicial: No hay sesión activa, se configurará e iniciará.</p>";
    
    // Mostrar la configuración que se va a utilizar
    echo "<h3>Configuración de sesiones que se utilizará:</h3>";
    echo "<pre>";
    echo "session.cookie_path = /" . PHP_EOL;
    echo "session.cookie_domain = .clinica.test" . PHP_EOL;
    echo "session.cookie_lifetime = 3600" . PHP_EOL;
    echo "session.cookie_secure = false" . PHP_EOL;
    echo "session.cookie_httponly = true" . PHP_EOL;
    echo "session.cookie_samesite = Lax" . PHP_EOL;
    echo "</pre>";
    
    // Configurar cookies de sesión antes de iniciar
    session_set_cookie_params([
        'lifetime' => 3600,
        'path' => '/',
        'domain' => '.clinica.test',
        'secure' => false,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    
    // Iniciar sesión
    session_start();
    
    echo "<p>Sesión iniciada con ID: " . session_id() . "</p>";
} else {
    echo "<p>Estado inicial: Ya hay una sesión activa con ID: " . session_id() . "</p>";
}

// Obtener y mostrar parámetros actuales de la cookie de sesión
echo "<h2>Parámetros Actuales de Cookie de Sesión</h2>";
$cookieParams = session_get_cookie_params();
echo "<pre>";
print_r($cookieParams);
echo "</pre>";

// Verificar si hay datos de usuario en la sesión
echo "<h2>Datos de Sesión</h2>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

// Mostrar todas las cookies
echo "<h2>Cookies Disponibles</h2>";
echo "<pre>";
print_r($_COOKIE);
echo "</pre>";

// Verificar autenticación usando el controlador
echo "<h2>Verificación con AuthController</h2>";

// Cargar el controlador de autenticación si no está cargado
if (!class_exists('AuthController')) {
    if (file_exists("controller/AuthController.php")) {
        require_once "controller/AuthController.php";
        echo "<p>AuthController cargado desde controller/AuthController.php</p>";
    } elseif (file_exists("public_reservas/controller/AuthController.php")) {
        require_once "public_reservas/controller/AuthController.php";
        echo "<p>AuthController cargado desde public_reservas/controller/AuthController.php</p>";
    } else {
        echo "<p class='error'>No se encontró AuthController.php</p>";
    }
}

if (class_exists('AuthController')) {
    echo "<p>isAuthenticated(): " . (AuthController::isAuthenticated() ? 'true' : 'false') . "</p>";
    
    if (method_exists('AuthController', 'ctrGetUserData')) {
        $userData = AuthController::ctrGetUserData();
        echo "<p>Datos del usuario: " . ($userData ? json_encode($userData) : 'No hay datos') . "</p>";
    } else {
        echo "<p class='error'>El método ctrGetUserData no existe en AuthController</p>";
    }
} else {
    echo "<p class='error'>La clase AuthController no está disponible</p>";
}

// Crear un formulario de prueba para establecer una sesión
echo "<h2>Prueba de Sesión</h2>";
echo "<form method='post'>";
echo "<input type='text' name='test_value' placeholder='Valor de prueba'>";
echo "<button type='submit' name='set_session'>Establecer en Sesión</button>";
echo "</form>";

// Procesar el formulario si se envió
if (isset($_POST['set_session']) && isset($_POST['test_value'])) {
    $_SESSION['test_value'] = $_POST['test_value'];
    echo "<p>Valor establecido en sesión: " . $_SESSION['test_value'] . "</p>";
    echo "<p>Recargue la página para ver si el valor persiste.</p>";
}

// Enlaces para probar en diferentes contextos
echo "<h2>Enlaces de Prueba</h2>";
echo "<ul>";
echo "<li><a href='http://clinica.test/' target='_blank'>Página Principal</a></li>";
echo "<li><a href='http://clinica.test/public_reservas/' target='_blank'>Sistema de Reservas</a></li>";
echo "<li><a href='http://clinica.test/public_reservas/debug_session.php' target='_blank'>Diagnóstico de Sesión</a></li>";
echo "<li><a href='http://clinica.test/check_auth.php' target='_blank'>Recargar esta página</a></li>";
echo "</ul>";
?>
