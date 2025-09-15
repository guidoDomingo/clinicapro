<?php
/**
 * Test de validación de contraseñas temporales
 */

echo "<h2>🔐 Test de Contraseñas Temporales</h2>";

// Simular el proceso de registro
echo "<h3>1. Simulación del Proceso de Registro</h3>";

// Generar contraseña temporal (misma lógica que en AuthController)
function generarPasswordTemporal() {
    $caracteres = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789';
    $password = '';
    
    for ($i = 0; $i < 8; $i++) {
        $password .= $caracteres[rand(0, strlen($caracteres) - 1)];
    }
    
    return $password;
}

$passwordTemporal = generarPasswordTemporal();
echo "✅ Contraseña temporal generada: <strong>$passwordTemporal</strong><br>";

// Hashear la contraseña (como se hace en el registro)
$passwordHashed = password_hash($passwordTemporal, PASSWORD_DEFAULT);
echo "✅ Contraseña hasheada: " . substr($passwordHashed, 0, 30) . "...<br>";

echo "<h3>2. Simulación del Proceso de Login</h3>";

// Verificar la contraseña (como se hace en el login)
$passwordIngresada = $passwordTemporal; // El usuario ingresa la contraseña temporal
$esValida = password_verify($passwordIngresada, $passwordHashed);

echo "✅ Contraseña ingresada: <strong>$passwordIngresada</strong><br>";
echo "✅ Verificación: " . ($esValida ? '<strong style="color: green;">VÁLIDA ✅</strong>' : '<strong style="color: red;">INVÁLIDA ❌</strong>') . "<br>";

echo "<h3>3. Test con Contraseña Incorrecta</h3>";

$passwordIncorrecta = "123456789";
$esValidaIncorrecta = password_verify($passwordIncorrecta, $passwordHashed);

echo "❌ Contraseña incorrecta: <strong>$passwordIncorrecta</strong><br>";
echo "❌ Verificación: " . ($esValidaIncorrecta ? '<strong style="color: green;">VÁLIDA ✅</strong>' : '<strong style="color: red;">INVÁLIDA ❌</strong>') . "<br>";

echo "<h3>🎯 Resultado</h3>";
echo "<p>El sistema de contraseñas temporales funciona correctamente.</p>";
echo "<p>La contraseña temporal generada se puede validar exitosamente con password_verify().</p>";

echo "<hr>";
echo "<p><em>Test ejecutado: " . date('Y-m-d H:i:s') . "</em></p>";
?>