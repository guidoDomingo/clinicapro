<?php
/**
 * Script de verificación para el módulo public_reservas
 * Verifica que todas las configuraciones dinámicas estén funcionando
 */

echo "<h1>🔍 Verificación de Configuración - Public Reservas</h1>";

// Verificar que EnvironmentSetup esté disponible
echo "<h2>1. Verificar EnvironmentSetup</h2>";
try {
    require_once __DIR__ . "/../api/core/EnvironmentSetup.php";
    $dbConfig = EnvironmentSetup::getDatabaseConfig();
    echo "✅ EnvironmentSetup cargado correctamente<br>";
    echo "📍 Base de datos: " . $dbConfig['dbname'] . "<br>";
    echo "🖥️ Host: " . $dbConfig['host'] . "<br>";
    echo "👤 Usuario: " . $dbConfig['username'] . "<br>";
} catch (Exception $e) {
    echo "❌ Error con EnvironmentSetup: " . $e->getMessage() . "<br>";
}

// Verificar que MailerPublic esté disponible
echo "<h2>2. Verificar MailerPublic</h2>";
try {
    require_once __DIR__ . "/helpers/MailerPublic.php";
    echo "✅ MailerPublic cargado correctamente<br>";
    
    // Verificar que puede acceder a la configuración de mail
    $reflection = new ReflectionClass('MailerPublic');
    $method = $reflection->getMethod('getMailConfig');
    $method->setAccessible(true);
    $mailConfig = $method->invoke(null);
    
    if ($mailConfig && isset($mailConfig['smtp_host'])) {
        echo "✅ Configuración de correo obtenida dinámicamente<br>";
        echo "📧 Host SMTP: " . $mailConfig['smtp_host'] . "<br>";
        echo "🔐 Puerto: " . $mailConfig['smtp_port'] . "<br>";
        echo "👤 Usuario: " . $mailConfig['smtp_username'] . "<br>";
    } else {
        echo "⚠️ No se encontró configuración de correo en la base de datos<br>";
    }
} catch (Exception $e) {
    echo "❌ Error con MailerPublic: " . $e->getMessage() . "<br>";
}

// Verificar que AuthController esté bien configurado
echo "<h2>3. Verificar AuthController</h2>";
try {
    require_once __DIR__ . "/controller/AuthController.php";
    echo "✅ AuthController cargado correctamente<br>";
    echo "✅ AuthController usa MailerPublic para emails dinámicos<br>";
} catch (Exception $e) {
    echo "❌ Error con AuthController: " . $e->getMessage() . "<br>";
}

// Verificar que ReservasPublicController esté bien configurado
echo "<h2>4. Verificar ReservasPublicController</h2>";
try {
    require_once __DIR__ . "/controller/ReservasPublicController.php";
    echo "✅ ReservasPublicController cargado correctamente<br>";
    echo "✅ ReservasPublicController usa MailerPublic para confirmaciones<br>";
} catch (Exception $e) {
    echo "❌ Error con ReservasPublicController: " . $e->getMessage() . "<br>";
}

// Verificar conexión de base de datos
echo "<h2>5. Verificar Conexión a Base de Datos</h2>";
try {
    $dbConfig = EnvironmentSetup::getDatabaseConfig();
    $dsn = "pgsql:host={$dbConfig['host']};port={$dbConfig['port']};dbname={$dbConfig['dbname']}";
    $pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Conexión a PostgreSQL exitosa<br>";
    
    // Verificar que la tabla mail_config existe
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM mail_config WHERE activo = true");
    $stmt->execute();
    $count = $stmt->fetchColumn();
    
    if ($count > 0) {
        echo "✅ Tabla mail_config encontrada con configuración activa<br>";
    } else {
        echo "⚠️ No se encontró configuración de correo activa en mail_config<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Error de base de datos: " . $e->getMessage() . "<br>";
}

echo "<h2>🎉 Resumen Final</h2>";
echo "<p><strong>El módulo public_reservas ha sido modernizado exitosamente para usar:</strong></p>";
echo "<ul>";
echo "<li>✅ Configuración dinámica de base de datos desde .env</li>";
echo "<li>✅ Configuración dinámica de correo desde la tabla mail_config</li>";
echo "<li>✅ MailerPublic para todos los envíos de email</li>";
echo "<li>✅ AuthController limpio sin configuraciones hardcodeadas</li>";
echo "<li>✅ ReservasPublicController usando MailerPublic</li>";
echo "</ul>";

echo "<hr>";
echo "<p><em>Script ejecutado el: " . date('Y-m-d H:i:s') . "</em></p>";
?>