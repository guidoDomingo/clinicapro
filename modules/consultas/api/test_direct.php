<?php
// Test directo del API
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Simular parámetros
$_GET['action'] = 'getMotivosComunes';
session_start();
$_SESSION['user_id'] = 1;

echo "Ejecutando API directamente...\n";

// Incluir el API
include __DIR__ . '/consultas-api.php';
?>
