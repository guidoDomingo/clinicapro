<?php
require_once 'model/conexion.php';

try {
    $db = Conexion::conectar();
    
    // Obtener el ID del permiso
    $stmt = $db->prepare("SELECT perm_id FROM sys_permissions WHERE perm_name = 'ver_todas_consultas'");
    $stmt->execute();
    $permiso = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$permiso) {
        echo "❌ Error: No se encontró el permiso 'ver_todas_consultas'\n";
        exit;
    }
    
    $permisoId = $permiso['perm_id'];
    echo "📋 Permiso ID: $permisoId\n";
    
    // Obtener todos los roles de admin
    $stmt = $db->prepare("SELECT role_id, role_name FROM sys_roles WHERE role_name IN ('admin', 'administrator', 'administrador')");
    $stmt->execute();
    $adminRoles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "👥 Roles de admin encontrados: " . count($adminRoles) . "\n";
    
    foreach ($adminRoles as $role) {
        echo "   - " . $role['role_name'] . " (ID: " . $role['role_id'] . ")\n";
        
        // Asignar el permiso al rol si no lo tiene ya
        $checkStmt = $db->prepare("SELECT 1 FROM sys_role_permissions WHERE role_id = ? AND perm_id = ?");
        $checkStmt->execute([$role['role_id'], $permisoId]);
        
        if (!$checkStmt->fetch()) {
            $insertStmt = $db->prepare("INSERT INTO sys_role_permissions (role_id, perm_id) VALUES (?, ?)");
            $insertStmt->execute([$role['role_id'], $permisoId]);
            echo "     ✅ Permiso asignado al rol: " . $role['role_name'] . "\n";
        } else {
            echo "     ℹ️  El rol ya tiene el permiso: " . $role['role_name'] . "\n";
        }
    }
    
    // También asignar directamente al usuario con ID 1 (normalmente admin)
    $stmt = $db->prepare("SELECT user_id, user_email FROM sys_users WHERE user_id = 1 OR user_email LIKE '%admin%' LIMIT 5");
    $stmt->execute();
    $adminUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "\n👤 Usuarios admin encontrados: " . count($adminUsers) . "\n";
    
    foreach ($adminUsers as $user) {
        echo "   - " . $user['user_email'] . " (ID: " . $user['user_id'] . ")\n";
        
        // Verificar si el usuario ya tiene el permiso a través de roles
        $checkStmt = $db->prepare("
            SELECT 1 FROM sys_user_roles ur 
            JOIN sys_role_permissions rp ON ur.role_id = rp.role_id 
            WHERE ur.user_id = ? AND rp.perm_id = ?
        ");
        $checkStmt->execute([$user['user_id'], $permisoId]);
        
        if ($checkStmt->fetch()) {
            echo "     ✅ Usuario ya tiene el permiso a través de roles\n";
        } else {
            echo "     ⚠️  Usuario no tiene el permiso. Asignar rol admin o permiso directo.\n";
        }
    }
    
    echo "\n✅ Proceso completado. Verificar que los usuarios admin tengan el permiso 'ver_todas_consultas'.\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>