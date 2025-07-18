<?php
// Test rápido de permisos
session_start();

// Simular una sesión de administrador
if(!isset($_SESSION['usuario'])) {
    $_SESSION['usuario'] = 'admin';
    $_SESSION['id'] = 1;
    $_SESSION['rol_id'] = 1;
    echo "<p>Sesión simulada creada</p>";
}

echo "<h2>Test de Permisos Final</h2>";
echo "<p>Usuario: " . $_SESSION['usuario'] . "</p>";

// Incluir el helper de permisos
require_once 'view/helpers/permisos_helper.php';

// Test del permiso
$tienePermiso = tiene_permiso('administrar_turnos');
$claseMostrar = mostrar_si_tiene_permiso('administrar_turnos');

echo "<p>Permiso 'administrar_turnos': " . ($tienePermiso ? "✅ SÍ" : "❌ NO") . "</p>";
echo "<p>Clase CSS: '$claseMostrar'</p>";

if($tienePermiso) {
    echo "<p style='color: green;'><strong>✅ El usuario tiene permisos para acceder al módulo de turnos</strong></p>";
    echo "<p><a href='index.php?ruta=turnos' style='padding: 10px 20px; background-color: #007bff; color: white; text-decoration: none; border-radius: 5px;'>→ Ir al módulo de turnos</a></p>";
} else {
    echo "<p style='color: red;'><strong>❌ El usuario NO tiene permisos para acceder al módulo de turnos</strong></p>";
    echo "<p>Es necesario asignar el permiso 'administrar_turnos' al usuario/rol</p>";
}

// Listar todos los permisos del usuario
echo "<h3>Todos los permisos del usuario:</h3>";
try {
    $permisos = get_permisos_usuario();
    if(is_array($permisos) && count($permisos) > 0) {
        echo "<ul>";
        foreach($permisos as $permiso) {
            echo "<li>$permiso</li>";
        }
        echo "</ul>";
    } else {
        echo "<p>No se encontraron permisos</p>";
    }
} catch(Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>
