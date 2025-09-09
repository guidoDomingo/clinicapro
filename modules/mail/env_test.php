<?php
echo "<h1>Test Variables de Entorno</h1>";

// Test 1: Variables básicas
echo "<h2>Variables del Sistema:</h2>";
echo "PHP Version: " . PHP_VERSION . "<br>";
echo "Server Software: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'No disponible') . "<br>";
echo "Document Root: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'No disponible') . "<br>";

// Test 2: Directorio actual
echo "<h2>Rutas:</h2>";
echo "Directorio actual: " . __DIR__ . "<br>";
echo "Archivo actual: " . __FILE__ . "<br>";

// Test 3: Buscar archivo .env
$possible_env_paths = [
    __DIR__ . '/../../../.env',
    '/var/www/html/clinica/.env',
    dirname(dirname(dirname(__DIR__))) . '/.env'
];

echo "<h2>Búsqueda de archivo .env:</h2>";
foreach ($possible_env_paths as $path) {
    $exists = file_exists($path);
    echo "- $path: " . ($exists ? "✅ Existe" : "❌ No existe") . "<br>";
    
    if ($exists) {
        echo "  Contenido:<br><pre>";
        echo htmlspecialchars(file_get_contents($path));
        echo "</pre><br>";
        break;
    }
}

// Test 4: Verificar si Composer está instalado
$vendor_paths = [
    __DIR__ . '/../../../vendor/autoload.php',
    '/var/www/html/clinica/vendor/autoload.php'
];

echo "<h2>Verificación de Composer:</h2>";
foreach ($vendor_paths as $path) {
    $exists = file_exists($path);
    echo "- $path: " . ($exists ? "✅ Existe" : "❌ No existe") . "<br>";
}

// Test 5: Extensiones PHP requeridas
echo "<h2>Extensiones PHP:</h2>";
$extensions = ['pgsql', 'pdo', 'pdo_pgsql', 'json', 'curl', 'openssl'];
foreach ($extensions as $ext) {
    $loaded = extension_loaded($ext);
    echo "- $ext: " . ($loaded ? "✅ Cargada" : "❌ No disponible") . "<br>";
}
?>