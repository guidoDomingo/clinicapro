<?php
// Test básico de PHP
echo "PHP está funcionando correctamente<br>";
echo "Versión de PHP: " . PHP_VERSION . "<br>";
echo "Directorio actual: " . __DIR__ . "<br>";
echo "Timestamp: " . date('Y-m-d H:i:s') . "<br>";

// Test de extensiones requeridas
echo "<h3>Extensiones PHP:</h3>";
$required_extensions = ['pgsql', 'pdo', 'pdo_pgsql', 'json', 'openssl'];
foreach ($required_extensions as $ext) {
    $status = extension_loaded($ext) ? "✅ Disponible" : "❌ No disponible";
    echo "- $ext: $status<br>";
}

// Test de archivo .env
echo "<h3>Archivo .env:</h3>";
$env_file = dirname(__DIR__, 3) . '/.env';
echo "Ruta .env: $env_file<br>";
echo "Existe: " . (file_exists($env_file) ? "✅ Sí" : "❌ No") . "<br>";

if (file_exists($env_file)) {
    echo "Contenido:<br><pre>";
    echo htmlspecialchars(file_get_contents($env_file));
    echo "</pre>";
}

// Test de config.php
echo "<h3>Config.php:</h3>";
$config_file = dirname(__DIR__, 3) . '/config/config.php';
echo "Ruta config: $config_file<br>";
echo "Existe: " . (file_exists($config_file) ? "✅ Sí" : "❌ No") . "<br>";

try {
    if (file_exists($config_file)) {
        require_once $config_file;
        echo "✅ Config cargado correctamente<br>";
        
        if (isset($_ENV['DB_HOST'])) {
            echo "Variables de entorno cargadas:<br>";
            echo "- DB_HOST: " . $_ENV['DB_HOST'] . "<br>";
            echo "- DB_PORT: " . ($_ENV['DB_PORT'] ?? 'no definido') . "<br>";
            echo "- DB_DATABASE: " . $_ENV['DB_DATABASE'] . "<br>";
            echo "- DB_USERNAME: " . $_ENV['DB_USERNAME'] . "<br>";
        } else {
            echo "❌ Variables de entorno no cargadas<br>";
        }
    }
} catch (Exception $e) {
    echo "❌ Error cargando config: " . $e->getMessage() . "<br>";
}

echo "<h3>Test completado</h3>";
?>