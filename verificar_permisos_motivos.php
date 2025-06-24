<?php
/**
 * Script para verificar los permisos de Motivos Comunes
 */

// Incluir los archivos necesarios
require_once "config/config.php";
require_once "model/conexion.php";
require_once "controller/permisos.controller.php";

echo "<h1>Verificación de Permisos para Motivos Comunes</h1>";

// Verificar la conexión a la base de datos
try {
    $conexion = Conexion::conectar();
    echo "<p style='color:green'>✓ Conexión a la base de datos establecida.</p>";
} catch (Exception $e) {
    echo "<p style='color:red'>✗ Error de conexión: " . $e->getMessage() . "</p>";
    exit;
}

// Verificar el permiso de administrar_motivos
try {
    $stmt = $conexion->prepare("SELECT permiso_id, nombre, descripcion FROM permisos WHERE nombre = 'administrar_motivos'");
    $stmt->execute();
    $permiso = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($permiso) {
        echo "<p style='color:green'>✓ El permiso 'administrar_motivos' existe con ID: {$permiso['permiso_id']}</p>";
    } else {
        echo "<p style='color:red'>✗ El permiso 'administrar_motivos' no existe.</p>";
        echo "<p>Asegúrese de ejecutar el script SQL que crea este permiso.</p>";
    }
} catch (Exception $e) {
    echo "<p style='color:red'>✗ Error al verificar el permiso: " . $e->getMessage() . "</p>";
}

// Verificar qué roles tienen asignado este permiso
if (isset($permiso)) {
    try {
        $stmt = $conexion->prepare("
            SELECT r.rol_id, r.nombre as rol_nombre 
            FROM roles r 
            JOIN roles_permisos rp ON r.rol_id = rp.rol_id 
            WHERE rp.permiso_id = :permiso_id
        ");
        $stmt->bindParam(':permiso_id', $permiso['permiso_id'], PDO::PARAM_INT);
        $stmt->execute();
        $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($roles) > 0) {
            echo "<p style='color:green'>✓ El permiso está asignado a los siguientes roles:</p>";
            echo "<ul>";
            foreach ($roles as $rol) {
                echo "<li>ID: {$rol['rol_id']} - Nombre: {$rol['rol_nombre']}</li>";
            }
            echo "</ul>";
        } else {
            echo "<p style='color:orange'>⚠ El permiso no está asignado a ningún rol.</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color:red'>✗ Error al verificar roles con el permiso: " . $e->getMessage() . "</p>";
    }
}

// Agregar opción para asignar el permiso manualmente si no existe
if (!isset($permiso) || count($roles) === 0) {
    echo "<h2>Crear y asignar permiso</h2>";
    echo "<form method='post'>";
    echo "<input type='hidden' name='accion' value='crear_permiso'>";
    echo "<button type='submit'>Crear y asignar permiso a administrador</button>";
    echo "</form>";
    
    if (isset($_POST['accion']) && $_POST['accion'] === 'crear_permiso') {
        try {
            // Crear el permiso si no existe
            if (!isset($permiso)) {
                $stmt = $conexion->prepare("INSERT INTO permisos (nombre, descripcion) VALUES ('administrar_motivos', 'Permiso para gestionar los motivos comunes')");
                $stmt->execute();
                echo "<p style='color:green'>✓ Permiso creado correctamente.</p>";
                
                // Obtener el ID del permiso recién creado
                $stmt = $conexion->prepare("SELECT permiso_id FROM permisos WHERE nombre = 'administrar_motivos'");
                $stmt->execute();
                $permiso = $stmt->fetch(PDO::FETCH_ASSOC);
            }
            
            // Asignar el permiso al rol de administrador (asumiendo ID 1)
            $stmt = $conexion->prepare("INSERT INTO roles_permisos (rol_id, permiso_id) VALUES (1, :permiso_id)");
            $stmt->bindParam(':permiso_id', $permiso['permiso_id'], PDO::PARAM_INT);
            $stmt->execute();
            echo "<p style='color:green'>✓ Permiso asignado al rol de administrador.</p>";
            echo "<script>setTimeout(function() { window.location.reload(); }, 2000);</script>";
        } catch (Exception $e) {
            echo "<p style='color:red'>✗ Error al crear/asignar permiso: " . $e->getMessage() . "</p>";
        }
    }
}

echo "<p>Verificación completada. <a href='index.php?ruta=motivos'>Ir al módulo de Motivos Comunes</a></p>";
?>
