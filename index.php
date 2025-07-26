<?php 
// Iniciar sesión si no está iniciada
if (session_status() == PHP_SESSION_NONE) {
    // Detectar el dominio actual para configurar cookies correctamente
    $domain = '';
    if (isset($_SERVER['HTTP_HOST'])) {
        $host = $_SERVER['HTTP_HOST'];
        if (strpos($host, 'clinica.test') !== false) {
            $domain = '.clinica.test';
        } elseif (strpos($host, 'localhost') !== false) {
            $domain = ''; // Para localhost no especificar dominio
        }
    }
    
    // Configurar las sesiones
    $sessionParams = [
        'lifetime' => 3600,
        'path' => '/',
        'secure' => false,
        'httponly' => true,
        'samesite' => 'Lax'
    ];
    
    // Solo agregar dominio si no es localhost
    if (!empty($domain)) {
        $sessionParams['domain'] = $domain;
    }
    
    session_set_cookie_params($sessionParams);
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
include "controller/TipoFormulariosController.php";
include "controller/preformatos.controller.php";

//MODEL
include "model/register.model.php";
include "model/archivos.model.php";
include "model/consultas.model.php";
require_once "model/personas.model.php";
require_once "model/permisos.model.php";
require_once "model/TipoFormularios.php";
require_once "model/preformatos.model.php";
$template = new ControllerTemplate();
$template -> ctrTemplate();