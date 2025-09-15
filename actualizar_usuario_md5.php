<?php
/**
 * Actualizar usuario existente para usar MD5 del documento
 */

require_once __DIR__ . "/config/environment_setup.php";
EnvironmentSetup::initialize();

$email = "ruizbenitezguido11@gmail.com";

echo "<h2>🔄 Actualizar Usuario a Sistema MD5: $email</h2>";

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
            sr.reg_lastname,
            su.user_pass as current_pass
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
        echo "<p>Hash actual: " . substr($usuario['current_pass'], 0, 20) . "...</p>";
        
        // Calcular MD5 del documento
        $documento = $usuario['reg_document'];
        $passwordMD5 = md5($documento);
        
        echo "<h3>🔧 Conversión a MD5:</h3>";
        echo "<p>Documento: <strong>{$documento}</strong></p>";
        echo "<p>MD5 del documento: <strong>{$passwordMD5}</strong></p>";
        
        // Actualizar contraseña a MD5 del documento
        $updateStmt = $pdo->prepare(
            "UPDATE sys_users 
             SET user_pass = :password, user_is_active = true
             WHERE user_id = :user_id"
        );
        
        $updateStmt->bindParam(":password", $passwordMD5, PDO::PARAM_STR);
        $updateStmt->bindParam(":user_id", $usuario['user_id'], PDO::PARAM_INT);
        
        if ($updateStmt->execute()) {
            echo "<h3>✅ Usuario Actualizado</h3>";
            echo "<p><strong style='color: green;'>Ahora puedes hacer login con:</strong></p>";
            echo "<ul>";
            echo "<li><strong>Email:</strong> {$email}</li>";
            echo "<li><strong>Contraseña:</strong> {$documento}</li>";
            echo "</ul>";
            
            // Verificar que la nueva contraseña funciona con MD5
            $testMD5 = md5($documento);
            echo "<p><strong>Verificación MD5:</strong> " . ($testMD5 === $passwordMD5 ? '✅ CORRECTO' : '❌ ERROR') . "</p>";
            
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
echo "<p><strong>Credenciales para login:</strong></p>";
echo "<ul>";
echo "<li>Email: ruizbenitezguido11@gmail.com</li>";
echo "<li>Contraseña: 9996665544</li>";
echo "</ul>";
?>