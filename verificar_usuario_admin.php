<?php
session_start();
require_once 'model/conexion.php';

try {
    $db = Conexion::conectar();
    
    echo "👤 Usuario actual en sesión:\n";
    echo "   - ID: " . ($_SESSION['user_id'] ?? 'No definido') . "\n";
    echo "   - Email: " . ($_SESSION['usuario'] ?? 'No definido') . "\n";
    
    if (!isset($_SESSION['user_id'])) {
        echo "❌ No hay usuario logueado\n";
        exit;
    }
    
    $userId = $_SESSION['user_id'];
    
    // Obtener información del usuario
    $stmt = $db->prepare("SELECT user_id, user_email FROM sys_users WHERE user_id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        echo "❌ Usuario no encontrado en base de datos\n";
        exit;
    }
    
    echo "📋 Usuario en BD: " . $user['user_email'] . " (ID: " . $user['user_id'] . ")\n";
    
    // Obtener el role ID de admin
    $stmt = $db->prepare("SELECT role_id FROM sys_roles WHERE role_name = 'admin'");
    $stmt->execute();
    $adminRole = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$adminRole) {
        echo "❌ Rol admin no encontrado\n";
        exit;
    }
    
    $adminRoleId = $adminRole['role_id'];
    echo "👑 Rol admin ID: $adminRoleId\n";
    
    // Verificar si el usuario ya tiene el rol admin
    $stmt = $db->prepare("SELECT 1 FROM sys_user_roles WHERE user_id = ? AND role_id = ?");
    $stmt->execute([$userId, $adminRoleId]);
    
    if ($stmt->fetch()) {
        echo "✅ El usuario ya tiene el rol admin\n";
    } else {
        // Asignar rol admin al usuario
        $stmt = $db->prepare("INSERT INTO sys_user_roles (user_id, role_id) VALUES (?, ?)");
        $stmt->execute([$userId, $adminRoleId]);
        echo "✅ Rol admin asignado al usuario\n";
    }
    
    // Verificar permisos finales
    echo "\n🔍 Verificando permisos del usuario:\n";
    $stmt = $db->prepare("
        SELECT p.perm_name, p.perm_description 
        FROM sys_permissions p
        JOIN sys_role_permissions rp ON p.perm_id = rp.perm_id
        JOIN sys_user_roles ur ON rp.role_id = ur.role_id
        WHERE ur.user_id = ? AND p.perm_name = 'ver_todas_consultas'
    ");
    $stmt->execute([$userId]);
    $permisos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($permisos) > 0) {
        echo "✅ El usuario tiene el permiso 'ver_todas_consultas'\n";
        foreach ($permisos as $permiso) {
            echo "   - " . $permiso['perm_name'] . ": " . $permiso['perm_description'] . "\n";
        }
    } else {
        echo "❌ El usuario NO tiene el permiso 'ver_todas_consultas'\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>