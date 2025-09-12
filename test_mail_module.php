<?php
/**
 * Test del módulo de correo
 * Verifica que el módulo de configuración de correo funcione correctamente
 */

// Incluir configuración del entorno
require_once __DIR__ . '/config/environment_setup.php';
EnvironmentSetup::initialize();

echo "<h1>Test del Módulo de Correo</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .success { color: green; font-weight: bold; }
    .error { color: red; font-weight: bold; }
    .warning { color: orange; font-weight: bold; }
    .info { color: blue; }
    pre { background: #f5f5f5; padding: 10px; border-radius: 5px; overflow: auto; }
    table { border-collapse: collapse; width: 100%; margin: 20px 0; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    th { background-color: #f2f2f2; }
</style>";

// Configurar URL base
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';

// Detectar si estamos en dominio virtual
if (strpos($host, '.test') !== false || strpos($host, '.local') !== false) {
    $baseUrl = "$protocol://$host";
    $mailApiUrl = "$baseUrl/modules/mail/api/mail_config.php";
} else {
    $baseUrl = "$protocol://$host/clinica";
    $mailApiUrl = "$baseUrl/modules/mail/api/mail_config.php";
}

echo "<h2>1. Configuración Detectada</h2>";
echo "<table>";
echo "<tr><th>Variable</th><th>Valor</th></tr>";
echo "<tr><td>Host</td><td>$host</td></tr>";
echo "<tr><td>Base URL</td><td>$baseUrl</td></tr>";
echo "<tr><td>Mail API URL</td><td>$mailApiUrl</td></tr>";
echo "<tr><td>Entorno</td><td>" . ($_ENV['APP_ENV'] ?? 'no definido') . "</td></tr>";
echo "</table>";

echo "<h2>2. Verificación de Archivos</h2>";
$mailConfigFile = __DIR__ . '/modules/mail/api/mail_config.php';
$exists = file_exists($mailConfigFile);
$readable = $exists ? is_readable($mailConfigFile) : false;

echo "<table>";
echo "<tr><th>Archivo</th><th>Existe</th><th>Legible</th><th>Tamaño</th></tr>";
echo "<tr>";
echo "<td>mail_config.php</td>";
echo "<td class='" . ($exists ? 'success' : 'error') . "'>" . ($exists ? 'Sí' : 'NO') . "</td>";
echo "<td class='" . ($readable ? 'success' : 'error') . "'>" . ($readable ? 'Sí' : 'NO') . "</td>";
echo "<td>" . ($exists ? filesize($mailConfigFile) . ' bytes' : 'N/A') . "</td>";
echo "</tr>";
echo "</table>";

echo "<h2>3. Test de Conectividad</h2>";

// Función para probar endpoints
function testEndpoint($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    return [
        'response' => $response,
        'http_code' => $httpCode,
        'error' => $error
    ];
}

// Probar diferentes URLs
$testUrls = [
    $mailApiUrl . '?action=get',
    $mailApiUrl . '?action=logs&limit=5'
];

echo "<table>";
echo "<tr><th>URL</th><th>Status HTTP</th><th>Respuesta</th></tr>";

foreach ($testUrls as $url) {
    $result = testEndpoint($url);
    
    $statusClass = $result['http_code'] === 200 ? 'success' : 'error';
    $response = $result['response'];
    
    // Limitar respuesta para mostrar
    if (strlen($response) > 200) {
        $response = substr($response, 0, 200) . '...';
    }
    
    echo "<tr>";
    echo "<td><a href='$url' target='_blank'>" . basename($url) . "</a></td>";
    echo "<td class='$statusClass'>HTTP {$result['http_code']}</td>";
    echo "<td><pre>" . htmlspecialchars($response) . "</pre></td>";
    echo "</tr>";
}

echo "</table>";

echo "<h2>4. Test de Base de Datos</h2>";

try {
    // Probar conexión a BD
    $dbConfig = EnvironmentSetup::getDatabaseConfig();
    $dsn = "{$dbConfig['driver']}:host={$dbConfig['host']};port={$dbConfig['port']};dbname={$dbConfig['database']}";
    $pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_TIMEOUT => 10
    ]);
    
    echo "<p class='success'>✅ Conexión a base de datos exitosa</p>";
    
    // Verificar si existe la tabla mail_config
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM information_schema.tables WHERE table_name = 'mail_config'");
    $stmt->execute();
    $tableExists = $stmt->fetchColumn() > 0;
    
    echo "<p class='" . ($tableExists ? 'success' : 'warning') . "'>";
    echo $tableExists ? "✅ Tabla mail_config existe" : "⚠️ Tabla mail_config no existe";
    echo "</p>";
    
    if ($tableExists) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM mail_config");
        $stmt->execute();
        $recordCount = $stmt->fetchColumn();
        echo "<p class='info'>📊 Registros en mail_config: $recordCount</p>";
    }
    
} catch (Exception $e) {
    echo "<p class='error'>❌ Error en base de datos: " . $e->getMessage() . "</p>";
}

echo "<h2>5. URLs de Prueba Directa</h2>";
echo "<ul>";
echo "<li><a href='$mailApiUrl?action=get' target='_blank'>Obtener configuración</a></li>";
echo "<li><a href='$mailApiUrl?action=logs&limit=5' target='_blank'>Obtener logs recientes</a></li>";
echo "<li><a href='" . dirname($mailApiUrl) . "/debug_mail.php' target='_blank'>Debug del módulo</a></li>";
echo "</ul>";

echo "<h2>🎯 Solución de Problemas</h2>";
echo "<p>Si hay errores:</p>";
echo "<ol>";
echo "<li>Verifica que la base de datos esté conectada</li>";
echo "<li>Asegúrate de que la tabla mail_config exista</li>";
echo "<li>Revisa los permisos de archivos</li>";
echo "<li>Verifica la configuración de rutas en tu navegador</li>";
echo "</ol>";

echo "<p><em>Test completado el " . date('Y-m-d H:i:s') . "</em></p>";
?>