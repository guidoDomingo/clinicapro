<?php
/**
 * Verificar contraseña guardada para usuario específico
 */

require_once __DIR__ . "/config/environment_setup.php";
EnvironmentSetup::initialize();

$email = "ruizbenitezguido11@gmail.com";

echo "<h2>🔍 Verificación de Usuario: $email</h2>";

try {
    $dbConfig = EnvironmentSetup::getDatabaseConfig();
    $dsn = "pgsql:host={$dbConfig['host']};port={$dbConfig['port']};dbname={$dbConfig['dbname']}";
    $pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Buscar usuario
    $stmt = $pdo->prepare(
        "SELECT 
            su.user_id,
            su.user_email,
            substr(su.user_pass, 1, 20) || '...' as password_hash_preview,
            su.user_is_active,
            sr.reg_name,
            sr.reg_lastname,
            sr.reg_document,
            su.user_created_at
        FROM 
            sys_users su
        INNER JOIN 
            sys_register sr ON su.reg_id = sr.reg_id
        WHERE 
            su.user_email = :email
        ORDER BY su.user_created_at DESC
        LIMIT 1"
    );
    
    $stmt->bindParam(":email", $email, PDO::PARAM_STR);
    $stmt->execute();
    
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($usuario) {
        echo "<h3>✅ Usuario encontrado:</h3>";
        echo "<table border='1' style='border-collapse: collapse;'>";
        foreach ($usuario as $key => $value) {
            echo "<tr><td><strong>$key</strong></td><td>$value</td></tr>";
        }
        echo "</table>";
        
        echo "<h3>🔑 Opciones para Recuperar Acceso:</h3>";
        echo "<p><strong>Opción 1:</strong> Usar el documento como contraseña: <strong>{$usuario['reg_document']}</strong></p>";
        echo "<p><strong>Opción 2:</strong> Restablecer contraseña a documento</p>";
        echo "<p><strong>Opción 3:</strong> Generar nueva contraseña temporal</p>";
        
        // Verificar si la contraseña es el documento
        $stmt2 = $pdo->prepare("SELECT user_pass FROM sys_users WHERE user_email = :email");
        $stmt2->bindParam(":email", $email, PDO::PARAM_STR);
        $stmt2->execute();
        $passHash = $stmt2->fetchColumn();
        
        $documento = $usuario['reg_document'];
        $esDocumento = password_verify($documento, $passHash);
        
        echo "<h3>🧪 Test de Contraseñas:</h3>";
        echo "<p>¿La contraseña es el documento ($documento)? " . ($esDocumento ? '<strong style="color: green;">SÍ ✅</strong>' : '<strong style="color: red;">NO ❌</strong>') . "</p>";
        
        if (!$esDocumento) {
            echo "<p><strong>⚠️ La contraseña NO es el documento. Probablemente es una contraseña temporal generada.</strong></p>";
        }
        
    } else {
        echo "❌ Usuario no encontrado con email: $email";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>