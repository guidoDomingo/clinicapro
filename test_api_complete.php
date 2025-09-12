<?php
/**
 * Test API Endpoints
 * Prueba que los endpoints de la API funcionen correctamente en local y producción
 */

// Incluir configuración
require_once __DIR__ . '/config/environment_setup.php';
EnvironmentSetup::initialize();

echo "<h1>Test de API - Sistema Clínica</h1>";
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

// Configurar la URL base para las pruebas
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$baseUrl = "$protocol://$host/clinica/api";

echo "<h2>1. Configuración de la API</h2>";
echo "<table>";
echo "<tr><th>Variable</th><th>Valor</th></tr>";
echo "<tr><td>URL Base API</td><td>$baseUrl</td></tr>";
echo "<tr><td>Entorno</td><td>" . ($_ENV['APP_ENV'] ?? 'no definido') . "</td></tr>";
echo "<tr><td>Logs Path</td><td>" . EnvironmentSetup::getLogPath() . "</td></tr>";
echo "<tr><td>Debug Mode</td><td>" . ($_ENV['APP_DEBUG'] ?? 'false') . "</td></tr>";
echo "</table>";

// Función para probar endpoints
function testEndpoint($url, $method = 'GET', $data = null) {
    $ch = curl_init();
    
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Content-Length: ' . strlen(json_encode($data))
            ]);
        }
    }
    
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

echo "<h2>2. Test de Endpoints</h2>";
echo "<table>";
echo "<tr><th>Endpoint</th><th>Método</th><th>Status</th><th>Respuesta</th></tr>";

// Lista de endpoints para probar
$endpoints = [
    ['url' => $baseUrl . '/test.php', 'method' => 'GET', 'description' => 'Test API básico'],
    ['url' => $baseUrl . '/people', 'method' => 'GET', 'description' => 'Listar personas'],
    ['url' => $baseUrl . '/departments', 'method' => 'GET', 'description' => 'Listar departamentos'],
    ['url' => $baseUrl . '/specialties', 'method' => 'GET', 'description' => 'Listar especialidades'],
    ['url' => $baseUrl . '/cities', 'method' => 'GET', 'description' => 'Listar ciudades']
];

foreach ($endpoints as $endpoint) {
    $result = testEndpoint($endpoint['url'], $endpoint['method']);
    
    $statusClass = 'error';
    $statusText = 'ERROR';
    
    if ($result['http_code'] === 200) {
        $statusClass = 'success';
        $statusText = 'OK';
    } elseif ($result['http_code'] >= 400 && $result['http_code'] < 500) {
        $statusClass = 'warning';
        $statusText = 'CLIENT ERROR';
    }
    
    echo "<tr>";
    echo "<td>{$endpoint['description']}</td>";
    echo "<td>{$endpoint['method']}</td>";
    echo "<td class='$statusClass'>HTTP {$result['http_code']} - $statusText</td>";
    
    // Mostrar respuesta (limitada)
    $response = $result['response'];
    if (strlen($response) > 200) {
        $response = substr($response, 0, 200) . '...';
    }
    echo "<td><pre>" . htmlspecialchars($response) . "</pre></td>";
    echo "</tr>";
}

echo "</table>";

echo "<h2>3. Test de Logs</h2>";

// Verificar que se puedan crear logs
$logPath = EnvironmentSetup::getLogPath();
$testLogFile = $logPath . 'api_test.log';

try {
    $testMessage = "[" . date('Y-m-d H:i:s') . "] API Test - Sistema funcionando correctamente\n";
    $result = error_log($testMessage, 3, $testLogFile);
    
    if ($result && file_exists($testLogFile)) {
        echo "<p class='success'>✅ Sistema de logs funcionando correctamente</p>";
        echo "<p class='info'>📝 Log de prueba creado en: $testLogFile</p>";
        
        // Leer las últimas líneas del log
        if (file_exists($testLogFile)) {
            $logContent = file_get_contents($testLogFile);
            echo "<h3>Contenido del log de prueba:</h3>";
            echo "<pre>" . htmlspecialchars(substr($logContent, -500)) . "</pre>";
        }
    } else {
        echo "<p class='error'>❌ Error: No se pudo crear el archivo de log</p>";
    }
} catch (Exception $e) {
    echo "<p class='error'>❌ Error en sistema de logs: " . $e->getMessage() . "</p>";
}

echo "<h2>4. Test de Configuración de API</h2>";

// Probar que la configuración de API funcione
try {
    require_once __DIR__ . '/api/core/ApiConfig.php';
    $config = \Api\Core\ApiConfig::getAllConfig();
    
    echo "<table>";
    echo "<tr><th>Configuración</th><th>Valor</th></tr>";
    foreach ($config as $key => $value) {
        if (is_array($value)) {
            $value = json_encode($value, JSON_PRETTY_PRINT);
        } elseif (is_bool($value)) {
            $value = $value ? 'true' : 'false';
        }
        echo "<tr><td>$key</td><td><pre>" . htmlspecialchars($value) . "</pre></td></tr>";
    }
    echo "</table>";
    
    echo "<p class='success'>✅ Configuración de API cargada correctamente</p>";
    
} catch (Exception $e) {
    echo "<p class='error'>❌ Error en configuración de API: " . $e->getMessage() . "</p>";
}

echo "<h2>5. Test de Conexión a Base de Datos desde API</h2>";

try {
    require_once __DIR__ . '/model/conexion.php';
    
    // Probar conexión usando la clase existente
    $db = Conexion::conectar();
    
    if ($db !== null) {
        // Probar una consulta simple
        $stmt = $db->prepare("SELECT version() as version");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo "<p class='success'>✅ Conexión a base de datos desde API exitosa</p>";
        echo "<p class='info'>Versión PostgreSQL: " . $result['version'] . "</p>";
    } else {
        echo "<p class='error'>❌ Error: No se pudo conectar a la base de datos</p>";
    }
    
} catch (Exception $e) {
    echo "<p class='error'>❌ Error en conexión BD desde API: " . $e->getMessage() . "</p>";
}

echo "<h2>🎯 Resumen de Pruebas</h2>";
echo "<p>Pruebas completadas el " . date('Y-m-d H:i:s') . "</p>";
echo "<p><em>Si hay errores en rojo, revisa los logs correspondientes en: " . EnvironmentSetup::getLogPath() . "</em></p>";

// Mostrar información de archivos de log disponibles
echo "<h3>📂 Archivos de Log Disponibles:</h3>";
echo "<ul>";
$logFiles = glob($logPath . '*.log');
foreach ($logFiles as $logFile) {
    $size = file_exists($logFile) ? filesize($logFile) : 0;
    $filename = basename($logFile);
    echo "<li><strong>$filename</strong> - " . number_format($size) . " bytes</li>";
}
echo "</ul>";
?>