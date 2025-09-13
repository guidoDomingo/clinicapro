<?php
// Inicializar configuración del entorno
require_once dirname(dirname(__DIR__)) . '/config/environment_setup.php';
EnvironmentSetup::initialize();

echo "<h1>Test de Base de Datos - Multi-entorno</h1>";

// Usar configuración dinámica
$config = EnvironmentSetup::getDatabaseConfig();

echo "<h2>Configuración de DB (Entorno: " . ($_ENV['APP_ENV'] ?? 'unknown') . "):</h2>";
foreach ($config as $key => $value) {
    $display_value = $key === 'password' ? str_repeat('*', strlen($value)) : $value;
    echo "- $key: $display_value<br>";
}

// Test de extensión PostgreSQL
echo "<h2>Extensiones:</h2>";
$pgsql_available = extension_loaded('pgsql');
$pdo_available = extension_loaded('pdo');
$pdo_pgsql_available = extension_loaded('pdo_pgsql');

echo "- pgsql: " . ($pgsql_available ? "✅" : "❌") . "<br>";
echo "- pdo: " . ($pdo_available ? "✅" : "❌") . "<br>";
echo "- pdo_pgsql: " . ($pdo_pgsql_available ? "✅" : "❌") . "<br>";

if (!$pdo_pgsql_available) {
    echo "<p style='color: red;'>❌ PDO PostgreSQL no está disponible. No se puede conectar a la base de datos.</p>";
    exit;
}

// Test de conexión
echo "<h2>Test de Conexión:</h2>";
try {
    $dsn = "pgsql:host={$config['host']};port={$config['port']};dbname={$config['database']}";
    echo "DSN: $dsn<br>";
    
    $pdo = new PDO($dsn, $config['username'], $config['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_TIMEOUT => 5
    ]);
    
    echo "✅ Conexión exitosa<br>";
    
    // Test de consulta básica
    $stmt = $pdo->query("SELECT version()");
    $version = $stmt->fetchColumn();
    echo "Versión de PostgreSQL: $version<br>";
    
    // Test de tabla mail_config
    try {
        $stmt = $pdo->query("SELECT COUNT(*) FROM mail_config");
        $count = $stmt->fetchColumn();
        echo "✅ Tabla mail_config existe con $count registros<br>";
    } catch (Exception $e) {
        echo "❌ Tabla mail_config no existe: " . $e->getMessage() . "<br>";
    }
    
    // Test de tabla mail_logs
    try {
        $stmt = $pdo->query("SELECT COUNT(*) FROM mail_logs");
        $count = $stmt->fetchColumn();
        echo "✅ Tabla mail_logs existe con $count registros<br>";
    } catch (Exception $e) {
        echo "❌ Tabla mail_logs no existe: " . $e->getMessage() . "<br>";
    }
    
} catch (PDOException $e) {
    echo "❌ Error de conexión: " . $e->getMessage() . "<br>";
    echo "Código de error: " . $e->getCode() . "<br>";
} catch (Exception $e) {
    echo "❌ Error general: " . $e->getMessage() . "<br>";
}
?>