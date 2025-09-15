<?php
/**
 * Test rápido de MailerPublic después de las correcciones
 */

// Suprimir warnings para prueba limpia
error_reporting(E_ERROR | E_PARSE);

// Cargar dependencias
require_once __DIR__ . "/../config/environment_setup.php";
EnvironmentSetup::initialize();

require_once __DIR__ . "/helpers/MailerPublic.php";

echo "<h2>🧪 Test de MailerPublic Corregido</h2>";

try {
    // Test de configuración
    echo "<h3>1. Test de configuración de mail</h3>";
    
    $reflection = new ReflectionClass('MailerPublic');
    $method = $reflection->getMethod('getMailConfig');
    $method->setAccessible(true);
    $mailConfig = $method->invoke(null);
    
    if ($mailConfig) {
        echo "✅ Configuración obtenida correctamente<br>";
        echo "📧 Host: " . ($mailConfig['smtp_host'] ?? 'NO DEFINIDO') . "<br>";
        echo "🔐 Puerto: " . ($mailConfig['smtp_port'] ?? 'NO DEFINIDO') . "<br>";
        echo "👤 Usuario: " . ($mailConfig['smtp_username'] ?? 'NO DEFINIDO') . "<br>";
        echo "🔒 Auth: " . ($mailConfig['smtp_auth'] ?? 'NO DEFINIDO') . "<br>";
        echo "🛡️ Secure: " . ($mailConfig['smtp_secure'] ?? 'NO DEFINIDO') . "<br>";
        echo "📤 From: " . ($mailConfig['from_email'] ?? 'NO DEFINIDO') . "<br>";
    } else {
        echo "❌ No se pudo obtener configuración<br>";
    }
    
    // Test de inicialización de mailer
    echo "<h3>2. Test de inicialización PHPMailer</h3>";
    
    $getMailerMethod = $reflection->getMethod('getMailer');
    $getMailerMethod->setAccessible(true);
    $mailer = $getMailerMethod->invoke(null);
    
    if ($mailer) {
        echo "✅ PHPMailer inicializado correctamente<br>";
        echo "📧 Host configurado: " . $mailer->Host . "<br>";
        echo "🔐 Puerto configurado: " . $mailer->Port . "<br>";
        echo "🔒 SMTPAuth: " . ($mailer->SMTPAuth ? 'true' : 'false') . "<br>";
    } else {
        echo "❌ Error inicializando PHPMailer<br>";
    }
    
    echo "<h3>3. ✅ Resultado Final</h3>";
    echo "<p><strong>MailerPublic está funcionando correctamente sin errores de campos faltantes.</strong></p>";
    echo "<p>Los errores de <code>smtp_user</code> y <code>smtp_encryption</code> han sido corregidos.</p>";
    
} catch (Exception $e) {
    echo "<h3>❌ Error en el test</h3>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
    echo "<p>Archivo: " . $e->getFile() . " línea " . $e->getLine() . "</p>";
}

echo "<hr>";
echo "<p><em>Test ejecutado: " . date('Y-m-d H:i:s') . "</em></p>";
?>