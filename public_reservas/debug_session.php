<?php
/**
 * Diagnóstico de sesiones
 * Este archivo muestra información detallada sobre la sesión actual y las cookies
 */

// Iniciar sesión si no está iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Configurar para mostrar todos los errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Diagnóstico de Sesión</h1>";

echo "<h2>Información de PHP</h2>";
echo "<p>PHP Version: " . phpversion() . "</p>";
echo "<p>Session Name: " . session_name() . "</p>";
echo "<p>Session ID: " . session_id() . "</p>";
echo "<p>Session Save Path: " . session_save_path() . "</p>";

echo "<h2>Configuración de Session</h2>";
echo "<table border='1'>";
echo "<tr><th>Opción</th><th>Valor</th></tr>";
$sessionOptions = [
    'session.cookie_lifetime', 'session.cookie_path', 'session.cookie_domain',
    'session.cookie_secure', 'session.cookie_httponly', 'session.cookie_samesite',
    'session.use_strict_mode', 'session.use_cookies', 'session.use_only_cookies',
    'session.gc_maxlifetime', 'session.gc_probability', 'session.gc_divisor',
    'session.sid_length', 'session.sid_bits_per_character'
];
foreach ($sessionOptions as $option) {
    echo "<tr><td>$option</td><td>" . ini_get($option) . "</td></tr>";
}
echo "</table>";

echo "<h2>Variables de Sesión</h2>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

echo "<h2>Cookies</h2>";
echo "<pre>";
print_r($_COOKIE);
echo "</pre>";

echo "<h2>Headers</h2>";
echo "<pre>";
$headers = getallheaders();
foreach ($headers as $name => $value) {
    echo "$name: $value\n";
}
echo "</pre>";

echo "<h2>SERVER Variables</h2>";
echo "<table border='1'>";
echo "<tr><th>Variable</th><th>Valor</th></tr>";
$serverVars = ['HTTP_HOST', 'HTTP_REFERER', 'REQUEST_URI', 'SCRIPT_NAME', 'PHP_SELF', 'REQUEST_METHOD', 'REMOTE_ADDR', 'SERVER_NAME', 'SERVER_PORT'];
foreach ($serverVars as $var) {
    echo "<tr><td>$var</td><td>" . (isset($_SERVER[$var]) ? $_SERVER[$var] : 'No definido') . "</td></tr>";
}
echo "</table>";

echo "<h2>Verificación de Autenticación</h2>";
require_once "controller/AuthController.php";
echo "<p>isAuthenticated(): " . (AuthController::isAuthenticated() ? 'true' : 'false') . "</p>";

if (AuthController::isAuthenticated()) {
    $userData = AuthController::ctrGetUserData();
    echo "<p>Usuario actual: " . $userData['nombre'] . " (ID: " . $userData['id'] . ")</p>";
}

echo "<h2>Enlaces de prueba</h2>";
echo "<ul>";
echo "<li><a href='index.php'>Inicio</a></li>";
echo "<li><a href='index.php?view=login'>Login</a></li>";
echo "<li><a href='index.php?accion=reservar'>Reservar</a> (requiere autenticación)</li>";
echo "</ul>";

function debug_session_values() {
    return "Sesión actual: " . (session_status() == PHP_SESSION_ACTIVE ? 'activa' : 'inactiva') . 
           ", ID: " . session_id() . 
           ", Datos: " . json_encode($_SESSION);
}

// Hacer un log de esta información
error_log("Debug Session: " . debug_session_values(), 3, "c:/laragon/www/clinica/logs/session_debug.log");
?>
