<?php
session_start();

// Simular usuario autenticado
$_SESSION['iniciarSesion'] = 'ok';
$_SESSION['id'] = 1;

// Simular POST data
$_POST['idConsulta'] = 161;

echo "=== TEST ENDPOINT OBTENER CONSULTA 161 ===\n";
echo "Session data: " . print_r($_SESSION, true) . "\n";
echo "POST data: " . print_r($_POST, true) . "\n";

// Cambiar el directorio de trabajo
chdir('ajax');

// Incluir el endpoint
include 'obtener-consulta.php';
?>
?>
?>
?>