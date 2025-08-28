<?php
// Configuración de sesión para testing del sistema Livewire CRUD
session_start();

// Simular usuario autenticado
$_SESSION['user_id'] = 1;
$_SESSION['username'] = 'admin';
$_SESSION['debug'] = true;

// Redireccionar al sistema CRUD
header('Location: livewire-crud-system.html');
exit;
?>