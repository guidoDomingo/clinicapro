<?php
session_start();

// Simular una sesión para pruebas
$_SESSION['user_id'] = 1;
$_SESSION['user_name'] = 'test';

// Configurar parámetros de prueba
$_GET['action'] = 'search';
$_GET['table'] = 'rh_person';
$_GET['search'] = 'visconte';
$_GET['limit'] = 10;

// Incluir el archivo de la API
include 'modules/consultas/api/livewire-system.php';
?>