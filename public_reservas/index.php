<?php
/**
 * Sistema de Reservas Públicas
 * Permite a usuarios externos agendar citas sin necesidad de iniciar sesión
 */

// Incluir el controlador principal
require_once "controller/ReservasPublicController.php";

// Arrancar la aplicación
$reservas = new ReservasPublicController();
$reservas->iniciarAplicacion();
?>
