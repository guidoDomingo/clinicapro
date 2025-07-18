<?php
// Script para agregar el permiso administrar_turnos

require_once 'model/conexion.php';

try {
    $conexion = Conexion::conectar();
    echo "<h2>Agregando permiso 'administrar_turnos'</h2>";
    
    // Primero verificar la estructura de la tabla permisos
    $estructura = $conexion->prepare("
        SELECT column_name FROM information_schema.columns 
        WHERE table_name = 'permisos' 
        ORDER BY ordinal_position
    ");
    $estructura->execute();
    $columnas = $estructura->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<p>Columnas en tabla permisos: " . implode(', ', $columnas) . "</p>";
    
    // Determinar el nombre correcto de la columna
    $columna_nombre = in_array('nombre', $columnas) ? 'nombre' : 'permiso_nombre';
    $columna_descripcion = in_array('descripcion', $columnas) ? 'descripcion' : 'permiso_descripcion';
    
    echo "<p>Usando columna: $columna_nombre para el nombre del permiso</p>";
    
    // Verificar si ya existe
    $verificar = $conexion->prepare("SELECT * FROM permisos WHERE $columna_nombre = 'administrar_turnos'");
    $verificar->execute();
    $existe = $verificar->fetch(PDO::FETCH_ASSOC);
    
    if($existe) {
        echo "<p style='color: green;'>✅ El permiso ya existe:</p>";
        var_dump($existe);
    } else {
        echo "<p>El permiso no existe. Insertando...</p>";
        
        // Insertar el permiso
        $insert = $conexion->prepare("
            INSERT INTO permisos ($columna_nombre, $columna_descripcion) 
            VALUES ('administrar_turnos', 'Administrar gestión de turnos')
        ");
        
        if($insert->execute()) {
            echo "<p style='color: green;'>✅ Permiso insertado correctamente</p>";
            
            // Obtener el ID del permiso recién insertado
            $permiso_id = $conexion->lastInsertId();
            echo "<p>ID del permiso: $permiso_id</p>";
            
            // Verificar si existe la tabla roles_permisos
            $tablas = $conexion->prepare("
                SELECT table_name FROM information_schema.tables 
                WHERE table_name IN ('roles_permisos', 'rol_permisos', 'usuario_permisos')
            ");
            $tablas->execute();
            $tabla_relacion = $tablas->fetchAll(PDO::FETCH_COLUMN);
            
            echo "<p>Tablas de relación encontradas: " . implode(', ', $tabla_relacion) . "</p>";
            
            if(in_array('roles_permisos', $tabla_relacion)) {
                // Asignar al rol de administrador (generalmente ID 1)
                $asignar = $conexion->prepare("
                    INSERT INTO roles_permisos (rol_id, permiso_id)
                    SELECT 1, :permiso_id
                    WHERE NOT EXISTS (
                        SELECT 1 FROM roles_permisos 
                        WHERE rol_id = 1 AND permiso_id = :permiso_id
                    )
                ");
                $asignar->bindParam(':permiso_id', $permiso_id);
                
                if($asignar->execute()) {
                    echo "<p style='color: green;'>✅ Permiso asignado al rol administrador</p>";
                } else {
                    echo "<p style='color: orange;'>⚠️ No se pudo asignar al rol (puede que ya exista)</p>";
                }
            }
            
        } else {
            echo "<p style='color: red;'>❌ Error al insertar permiso</p>";
        }
    }
    
    // Verificar los permisos del usuario actual (si hay sesión)
    session_start();
    if(isset($_SESSION['id'])) {
        echo "<h3>Permisos del usuario actual:</h3>";
        
        // Buscar qué tabla se usa para usuarios-permisos
        $tablas_usuario = $conexion->prepare("
            SELECT table_name FROM information_schema.tables 
            WHERE table_name LIKE '%usuario%permiso%' OR table_name LIKE '%user%permiso%'
        ");
        $tablas_usuario->execute();
        $tablas_rel_usuario = $tablas_usuario->fetchAll(PDO::FETCH_COLUMN);
        
        echo "<p>Tablas usuario-permiso: " . implode(', ', $tablas_rel_usuario) . "</p>";
        
        // También verificar roles del usuario
        $roles_usuario = $conexion->prepare("
            SELECT table_name FROM information_schema.tables 
            WHERE table_name LIKE '%usuario%rol%' OR table_name LIKE '%user%rol%'
        ");
        $roles_usuario->execute();
        $tablas_rol_usuario = $roles_usuario->fetchAll(PDO::FETCH_COLUMN);
        
        echo "<p>Tablas usuario-rol: " . implode(', ', $tablas_rol_usuario) . "</p>";
    }
    
} catch(Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}

echo "<p><a href='index.php?ruta=turnos'>→ Probar acceso al módulo de turnos</a></p>";
?>
