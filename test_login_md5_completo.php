<?php
/**
 * Test completo del sistema MD5 de login
 */

require_once __DIR__ . "/config/environment_setup.php";
EnvironmentSetup::initialize();

require_once __DIR__ . "/public_reservas/model/ReservasPublicModel.php";

echo "<h2>🧪 Test Completo - Sistema MD5 Login</h2>";

$email = "ruizbenitezguido11@gmail.com";
$password = "9996665544"; // El documento

echo "<h3>📋 Datos de prueba:</h3>";
echo "<p>Email: <strong>$email</strong></p>";
echo "<p>Contraseña: <strong>$password</strong></p>";

echo "<h3>1. Test MD5 Manual:</h3>";
$md5Password = md5($password);
echo "<p>MD5('$password') = <strong>$md5Password</strong></p>";

echo "<h3>2. Test de Verificación de Usuario:</h3>";
try {
    $resultado = ReservasPublicModel::mdlVerificarUsuario($email, $password);
    
    echo "<p><strong>Resultado:</strong></p>";
    echo "<pre>" . json_encode($resultado, JSON_PRETTY_PRINT) . "</pre>";
    
    if (isset($resultado['error']) && !$resultado['error']) {
        echo "<p style='color: green; font-size: 18px;'><strong>✅ LOGIN EXITOSO!</strong></p>";
        echo "<p>Usuario ID: {$resultado['paciente_id']}</p>";
        echo "<p>Nombre: {$resultado['nombre']}</p>";
    } else {
        echo "<p style='color: red; font-size: 18px;'><strong>❌ LOGIN FALLIDO</strong></p>";
        echo "<p>Error: " . ($resultado['mensaje'] ?? 'Error desconocido') . "</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'><strong>❌ Excepción:</strong> " . $e->getMessage() . "</p>";
}

echo "<h3>3. Verificar Hash en BD:</h3>";
try {
    $dbConfig = EnvironmentSetup::getDatabaseConfig();
    $dsn = "pgsql:host={$dbConfig['host']};port={$dbConfig['port']};dbname={$dbConfig['dbname']}";
    $pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $pdo->prepare("SELECT user_pass FROM sys_users WHERE user_email = :email");
    $stmt->bindParam(":email", $email, PDO::PARAM_STR);
    $stmt->execute();
    $hashBD = $stmt->fetchColumn();
    
    echo "<p>Hash en BD: <strong>$hashBD</strong></p>";
    echo "<p>MD5 calculado: <strong>$md5Password</strong></p>";
    echo "<p>¿Coinciden? " . ($hashBD === $md5Password ? '<strong style="color: green;">SÍ ✅</strong>' : '<strong style="color: red;">NO ❌</strong>') . "</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error verificando BD: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><strong>Si el test es exitoso, el login debería funcionar en la página principal.</strong></p>";
echo "<p><a href='http://localhost/clinica/public_reservas/'>🔗 Probar Login Real</a></p>";
?>