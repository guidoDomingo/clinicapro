<?php
/**
 * Restablecer contraseña de usuario específico
 */

require_once __DIR__ . "/config/environment_setup.php";
EnvironmentSetup::initialize();

$email = "ruizbenitezguido11@gmail.com";

echo "<h2>🔄 Restablecimiento de Contraseña: $email</h2>";

try {
    $dbConfig = EnvironmentSetup::getDatabaseConfig();
    $dsn = "pgsql:host={$dbConfig['host']};port={$dbConfig['port']};dbname={$dbConfig['dbname']}";
    $pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Obtener datos del usuario
    $stmt = $pdo->prepare(
        "SELECT 
            su.user_id,
            sr.reg_document,
            sr.reg_name,
            sr.reg_lastname
        FROM 
            sys_users su
        INNER JOIN 
            sys_register sr ON su.reg_id = sr.reg_id
        WHERE 
            su.user_email = :email
        LIMIT 1"
    );
    
    $stmt->bindParam(":email", $email, PDO::PARAM_STR);
    $stmt->execute();
    
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($usuario) {
        echo "<h3>👤 Usuario encontrado:</h3>";
        echo "<p>Nombre: {$usuario['reg_name']} {$usuario['reg_lastname']}</p>";
        echo "<p>Documento: {$usuario['reg_document']}</p>";
        
        // Usar el documento como nueva contraseña
        $nuevaPassword = $usuario['reg_document'];
        $passwordHash = password_hash($nuevaPassword, PASSWORD_DEFAULT);
        
        // Actualizar contraseña
        $updateStmt = $pdo->prepare(
            "UPDATE sys_users 
             SET user_pass = :password, user_last_login = NULL
             WHERE user_id = :user_id"
        );
        
        $updateStmt->bindParam(":password", $passwordHash, PDO::PARAM_STR);
        $updateStmt->bindParam(":user_id", $usuario['user_id'], PDO::PARAM_INT);
        
        if ($updateStmt->execute()) {
            echo "<h3>✅ Contraseña Restablecida</h3>";
            echo "<p><strong>Nueva contraseña:</strong> {$nuevaPassword}</p>";
            echo "<p><strong>Email de login:</strong> {$email}</p>";
            echo "<hr>";
            echo "<p><strong style='color: green;'>Ahora puedes hacer login con:</strong></p>";
            echo "<ul>";
            echo "<li><strong>Email:</strong> {$email}</li>";
            echo "<li><strong>Contraseña:</strong> {$nuevaPassword}</li>";
            echo "</ul>";
            
            // Verificar que la nueva contraseña funciona
            $testVerify = password_verify($nuevaPassword, $passwordHash);
            echo "<p><strong>Verificación:</strong> " . ($testVerify ? '✅ CORRECTO' : '❌ ERROR') . "</p>";
            
        } else {
            echo "❌ Error al actualizar contraseña";
        }
        
    } else {
        echo "❌ Usuario no encontrado con email: $email";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}

echo "<hr>";
echo "<p><a href='http://localhost/clinica/public_reservas/'>🔗 Ir a página de login</a></p>";
?>