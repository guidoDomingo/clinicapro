<?php
// Script para configurar el permiso de turnos en la base de datos

require_once 'model/conexion.php';

try {
    $conexion = Conexion::conectar();
    echo "<h2>Configurando permiso 'administrar_turnos'</h2>";
    
    // 1. Verificar estructura de tablas
    echo "<h3>1. Verificando estructura de tablas...</h3>";
    
    $tablas = ['permisos', 'roles', 'roles_permisos', 'usuarios', 'usuarios_roles'];
    foreach($tablas as $tabla) {
        $stmt = $conexion->prepare("SELECT COUNT(*) FROM information_schema.tables WHERE table_name = ?");
        $stmt->execute([$tabla]);
        $existe = $stmt->fetchColumn();
        
        echo "<p>Tabla '$tabla': " . ($existe ? "✅ Existe" : "❌ No existe") . "</p>";
    }
    
    // 2. Insertar el permiso
    echo "<h3>2. Insertando permiso...</h3>";
    
    $insertPermiso = $conexion->prepare("
        INSERT INTO permisos (nombre, descripcion)
        SELECT 'administrar_turnos', 'Administrar gestión de turnos'
        WHERE NOT EXISTS (SELECT 1 FROM permisos WHERE nombre = 'administrar_turnos')
    ");
    
    if($insertPermiso->execute()) {
        echo "<p>✅ Permiso insertado/verificado correctamente</p>";
    } else {
        echo "<p>❌ Error al insertar permiso</p>";
    }
    
    // 3. Verificar que existe
    echo "<h3>3. Verificando permiso creado...</h3>";
    
    $verificar = $conexion->prepare("SELECT * FROM permisos WHERE nombre = 'administrar_turnos'");
    $verificar->execute();
    $permiso = $verificar->fetch(PDO::FETCH_ASSOC);
    
    if($permiso) {
        echo "<p>✅ Permiso encontrado:</p>";
        echo "<ul>";
        foreach($permiso as $key => $value) {
            echo "<li><strong>$key:</strong> $value</li>";
        }
        echo "</ul>";
        
        $permiso_id = $permiso['permiso_id'];
        
        // 4. Asignar al rol de administrador
        echo "<h3>4. Asignando al rol de administrador...</h3>";
        
        // Buscar el rol de administrador
        $buscarAdmin = $conexion->prepare("SELECT * FROM roles WHERE nombre ILIKE '%admin%' OR rol_id = 1 ORDER BY rol_id LIMIT 1");
        $buscarAdmin->execute();
        $rolAdmin = $buscarAdmin->fetch(PDO::FETCH_ASSOC);
        
        if($rolAdmin) {
            echo "<p>Rol de administrador encontrado: " . $rolAdmin['nombre'] . " (ID: " . $rolAdmin['rol_id'] . ")</p>";
            
            $asignarPermiso = $conexion->prepare("
                INSERT INTO roles_permisos (rol_id, permiso_id)
                SELECT ?, ?
                WHERE NOT EXISTS (
                    SELECT 1 FROM roles_permisos 
                    WHERE rol_id = ? AND permiso_id = ?
                )
            ");
            
            if($asignarPermiso->execute([$rolAdmin['rol_id'], $permiso_id, $rolAdmin['rol_id'], $permiso_id])) {
                echo "<p>✅ Permiso asignado al rol de administrador</p>";
            } else {
                echo "<p>❌ Error al asignar permiso al rol</p>";
            }
        } else {
            echo "<p>❌ No se encontró rol de administrador</p>";
        }
        
        // 5. Mostrar usuarios que tendrían acceso
        echo "<h3>5. Usuarios con acceso al módulo de turnos:</h3>";
        
        $usuariosConAcceso = $conexion->prepare("
            SELECT DISTINCT u.usuario_id, u.nombre, u.usuario, r.nombre as rol
            FROM usuarios u
            JOIN usuarios_roles ur ON ur.usuario_id = u.usuario_id
            JOIN roles r ON r.rol_id = ur.rol_id
            JOIN roles_permisos rp ON rp.rol_id = r.rol_id
            JOIN permisos p ON p.permiso_id = rp.permiso_id
            WHERE p.nombre = 'administrar_turnos'
        ");
        $usuariosConAcceso->execute();
        $usuarios = $usuariosConAcceso->fetchAll(PDO::FETCH_ASSOC);
        
        if(count($usuarios) > 0) {
            echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
            echo "<tr><th>ID</th><th>Nombre</th><th>Usuario</th><th>Rol</th></tr>";
            foreach($usuarios as $usuario) {
                echo "<tr>";
                echo "<td>" . $usuario['usuario_id'] . "</td>";
                echo "<td>" . $usuario['nombre'] . "</td>";
                echo "<td>" . $usuario['usuario'] . "</td>";
                echo "<td>" . $usuario['rol'] . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p>❌ No se encontraron usuarios con acceso al módulo</p>";
        }
        
    } else {
        echo "<p>❌ No se pudo crear/encontrar el permiso</p>";
    }
    
    echo "<h3>✅ Configuración completada</h3>";
    echo "<p><a href='index.php?ruta=turnos'>→ Probar acceso al módulo de turnos</a></p>";
    
} catch(Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?>
