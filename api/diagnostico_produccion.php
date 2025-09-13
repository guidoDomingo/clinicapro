<?php
/**
 * Script de diagnóstico para producción
 * Verificar qué está causando los errores 500
 */

// Habilitar display de errores temporalmente
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>🔍 Diagnóstico de API en Producción</h1>";

try {
    echo "<h2>1. Verificar archivos de configuración</h2>";
    
    // Verificar .env
    if (file_exists(__DIR__ . '/../.env')) {
        echo "✅ Archivo .env existe<br>";
    } else {
        echo "❌ Archivo .env NO existe<br>";
    }
    
    // Verificar vendor/autoload.php
    if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
        echo "✅ vendor/autoload.php existe<br>";
    } else {
        echo "❌ vendor/autoload.php NO existe (ejecutar: composer install)<br>";
    }
    
    echo "<h2>2. Verificar configuración de base de datos</h2>";
    
    // Intentar cargar configuración
    require_once __DIR__ . '/../config/environment_setup.php';
    \EnvironmentSetup::initialize();
    $dbConfig = \EnvironmentSetup::getDatabaseConfig();
    
    echo "✅ Configuración de BD cargada:<br>";
    echo "<pre>Host: {$dbConfig['host']}:{$dbConfig['port']}</pre>";
    echo "<pre>Database: {$dbConfig['database']}</pre>";
    echo "<pre>Username: {$dbConfig['username']}</pre>";
    
    echo "<h2>3. Probar conexión a base de datos</h2>";
    
    $dsn = "pgsql:host={$dbConfig['host']};port={$dbConfig['port']};dbname={$dbConfig['database']}";
    $pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Conexión a BD exitosa<br>";
    
    echo "<h2>4. Verificar rutas de logs</h2>";
    
    $logPath = \EnvironmentSetup::getLogPath();
    echo "Ruta de logs: $logPath<br>";
    
    if (is_dir($logPath)) {
        echo "✅ Directorio de logs existe<br>";
        if (is_writable($logPath)) {
            echo "✅ Directorio de logs es escribible<br>";
        } else {
            echo "❌ Directorio de logs NO es escribible<br>";
        }
    } else {
        echo "❌ Directorio de logs NO existe<br>";
        echo "Intentando crear...<br>";
        if (mkdir($logPath, 0755, true)) {
            echo "✅ Directorio de logs creado<br>";
        } else {
            echo "❌ No se pudo crear directorio de logs<br>";
        }
    }
    
    echo "<h2>5. Probar API básica</h2>";
    
    // Incluir clases necesarias
    require_once __DIR__ . '/core/ApiInitializer.php';
    \Api\Core\ApiInitializer::initialize();
    
    echo "✅ API inicializada correctamente<br>";
    
    echo "<h2>6. Verificar controladores</h2>";
    
    $controllers = [
        'LocationController' => __DIR__ . '/controllers/LocationController.php',
        'EspecialidadController' => __DIR__ . '/controllers/EspecialidadController.php',
        'RhPersonController' => __DIR__ . '/controllers/RhPersonController.php'
    ];
    
    foreach ($controllers as $name => $path) {
        if (file_exists($path)) {
            echo "✅ $name existe<br>";
        } else {
            echo "❌ $name NO existe<br>";
        }
    }
    
    echo "<h2>✅ Diagnóstico completado</h2>";
    
} catch (Exception $e) {
    echo "<h2>❌ Error encontrado:</h2>";
    echo "<p><strong>Mensaje:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>Archivo:</strong> " . $e->getFile() . "</p>";
    echo "<p><strong>Línea:</strong> " . $e->getLine() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>