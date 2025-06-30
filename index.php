<?php 
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
error_log("MAIN INDEX - Sesión ID: " . session_id() . ", Data: " . json_encode($_SESSION), 3, "c:/laragon/www/clinica/logs/session_debug.log");

//CONTROLLER
include "controller/consultas.controller.php";
include "controller/template.controller.php";
include "controller/archivos.controller.php";
include "controller/user.controller.php";
include "controller/register.controller.php";
include "controller/permisos.controller.php";

//MODEL
include "model/register.model.php";
include "model/archivos.model.php";
include "model/consultas.model.php";
include "model/personas.model.php";
include "model/permisos.model.php";
$template = new ControllerTemplate();
$template -> ctrTemplate();