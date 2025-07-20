<?php
/**
 * AJAX para verificar el estado de la sesión
 * Retorna si la sesión está activa o no
 */

// Iniciar sesión si no está iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Verificar si la sesión está activa
$sesion_activa = isset($_SESSION["iniciarSesion"]) && $_SESSION["iniciarSesion"] == "ok" && isset($_SESSION["user_id"]);

// Respuesta JSON
header('Content-Type: application/json');
echo json_encode([
    'sesion_activa' => $sesion_activa,
    'user_id' => $sesion_activa ? $_SESSION["user_id"] : null
]);
?>
