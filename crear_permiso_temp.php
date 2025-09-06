<?php
require_once 'model/conexion.php';

try {
    $db = Conexion::conectar();
    
    // Insertar el nuevo permiso si no existe
    $sql = "INSERT INTO sys_permissions (perm_name, perm_description) 
            SELECT 'ver_todas_consultas', 'Permite ver todas las consultas de todos los doctores'
            WHERE NOT EXISTS (
                SELECT 1 FROM sys_permissions WHERE perm_name = 'ver_todas_consultas'
            )";
    
    $result = $db->exec($sql);
    
    if ($result > 0) {
        echo "✅ Permiso 'ver_todas_consultas' creado correctamente\n";
    } else {
        echo "ℹ️  El permiso 'ver_todas_consultas' ya existe\n";
    }
    
    // Verificar que el permiso existe
    $stmt = $db->prepare("SELECT * FROM sys_permissions WHERE perm_name = 'ver_todas_consultas'");
    $stmt->execute();
    $permiso = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($permiso) {
        echo "✅ Verificación: Permiso encontrado con ID: " . $permiso['perm_id'] . "\n";
        echo "   Descripción: " . $permiso['perm_description'] . "\n";
    } else {
        echo "❌ Error: No se pudo encontrar el permiso creado\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>