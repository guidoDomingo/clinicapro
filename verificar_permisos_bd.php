<?php
// Verificar permisos directamente en la base de datos
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include_once "config/bd.php";

echo "<!DOCTYPE html><html><head><title>DB Permisos</title></head><body>";
echo "<h1>Verificación de Permisos en BD</h1>";

try {
    $pdo = Conexion::conectar();
    
    // Verificar usuario actual
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
        echo "<h2>Usuario ID: $user_id</h2>";
        
        // Obtener roles del usuario
        $stmt = $pdo->prepare("SELECT r.nombre as rol_nombre FROM user_roles ur 
                              JOIN roles r ON ur.role_id = r.id 
                              WHERE ur.user_id = :user_id");
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h3>Roles:</h3>";
        foreach ($roles as $rol) {
            echo "- " . $rol['rol_nombre'] . "<br>";
        }
        
        // Obtener permisos del usuario
        $stmt = $pdo->prepare("SELECT DISTINCT p.nombre as permiso_nombre 
                              FROM user_roles ur 
                              JOIN role_permissions rp ON ur.role_id = rp.role_id 
                              JOIN permissions p ON rp.permission_id = p.id 
                              WHERE ur.user_id = :user_id");
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        $permisos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h3>Permisos:</h3>";
        $tiene_ver_consultas = false;
        foreach ($permisos as $permiso) {
            echo "- " . $permiso['permiso_nombre'] . "<br>";
            if ($permiso['permiso_nombre'] == 'ver_consultas') {
                $tiene_ver_consultas = true;
            }
        }
        
        echo "<h3>Resultado:</h3>";
        echo "Permiso 'ver_consultas': " . ($tiene_ver_consultas ? '✅ SÍ' : '❌ NO') . "<br>";
        
    } else {
        echo "<h2>❌ No hay usuario en sesión</h2>";
    }
    
} catch (Exception $e) {
    echo "<h2>❌ Error: " . $e->getMessage() . "</h2>";
}

echo "</body></html>";
?>
