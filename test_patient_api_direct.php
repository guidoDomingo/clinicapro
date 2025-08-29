<?php
// Test directo de la API
header('Content-Type: application/json');

// Simular una petición POST real
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST['action'] = 'search';
$_POST['table'] = 'rh_person';
$_POST['search'] = json_encode(['first_name' => 'alejandro']);

// Capturar toda la salida
ob_start();
include 'modules/consultas/api/livewire-system.php';
$output = ob_get_clean();

echo $output;
?>