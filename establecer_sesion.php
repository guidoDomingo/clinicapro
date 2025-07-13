<?php
// Script para establecer sesión manualmente para testing
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== ESTABLECER SESIÓN PARA TESTING ===\n";

// Iniciar sesión
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

echo "Session ID antes: " . session_id() . "\n";

// Establecer datos de sesión manuales para testing
$_SESSION["iniciarSesion"] = "ok";
$_SESSION["validarSesion"] = "ok";
$_SESSION["user_id"] = 1;
$_SESSION["nombre"] = "Usuario Test";
$_SESSION["usuario"] = "test";
$_SESSION["rol"] = "admin";
$_SESSION["roles"] = ["admin"];
$_SESSION["email"] = "test@test.com";
$_SESSION["profile_complete"] = true;

echo "✅ Sesión establecida manualmente\n";
echo "Session ID después: " . session_id() . "\n";

echo "\n=== CONTENIDO DE LA SESIÓN ===\n";
foreach ($_SESSION as $key => $value) {
    if (is_array($value)) {
        echo "  $key: " . print_r($value, true);
    } else {
        echo "  $key: $value\n";
    }
}

echo "\n=== VERIFICANDO ACCESO A CONSULTAS ===\n";

// Simular los headers que se enviarían
header('Content-Type: text/html; charset=utf-8');

echo "\n✅ Sesión configurada. Ahora intenta acceder a:\n";
echo "http://localhost:8000/index.php?ruta=consultas&form_type=general&id_consulta=27&skip_modal=1\n";

// Generar enlace directo
echo "\n<a href='index.php?ruta=consultas&form_type=general&id_consulta=27&skip_modal=1' target='_blank'>Clic aquí para probar</a>\n";

?>
