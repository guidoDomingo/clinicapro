<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>🔍 Diagnóstico Error 500 - API Moderna</h2>";

// Simular exactamente la llamada que está fallando
$_GET['action'] = 'get_consulta';
$_GET['id'] = 107;

echo "<h3>📋 Datos de la petición:</h3>";
echo "<p>Action: " . ($_GET['action'] ?? 'N/A') . "</p>";
echo "<p>ID: " . ($_GET['id'] ?? 'N/A') . "</p>";
echo "<p>Method: " . ($_SERVER['REQUEST_METHOD'] ?? 'N/A') . "</p>";

try {
    echo "<h3>🔧 Paso 1: Verificando archivos...</h3>";
    
    $mapperFile = __DIR__ . '/modules/consultas/core/DatabaseMapper.php';
    $apiFile = __DIR__ . '/modules/consultas/api/modern-api.php';
    
    echo "<p>DatabaseMapper existe: " . (file_exists($mapperFile) ? '✅' : '❌') . "</p>";
    echo "<p>Modern API existe: " . (file_exists($apiFile) ? '✅' : '❌') . "</p>";
    
    echo "<h3>🔧 Paso 2: Incluyendo DatabaseMapper...</h3>";
    require_once 'modules/consultas/core/DatabaseMapper.php';
    echo "<p>✅ DatabaseMapper incluido sin errores</p>";
    
    echo "<h3>🔧 Paso 3: Probando constructor...</h3>";
    $mapper = new DatabaseMapper();
    echo "<p>✅ DatabaseMapper instanciado sin errores</p>";
    
    echo "<h3>🔧 Paso 4: Probando getConsulta()...</h3>";
    $result = $mapper->getConsulta(107);
    
    if ($result && isset($result['success'])) {
        if ($result['success']) {
            echo "<p>✅ getConsulta() exitoso</p>";
            echo "<p>📊 Datos obtenidos: " . count($result['data']) . " elementos</p>";
        } else {
            echo "<p>❌ getConsulta() falló: " . ($result['message'] ?? 'Sin mensaje') . "</p>";
        }
    } else {
        echo "<p>❌ getConsulta() devolvió formato inválido</p>";
        echo "<pre>" . print_r($result, true) . "</pre>";
    }
    
    echo "<h3>🔧 Paso 5: Probando getArchivosConsulta()...</h3>";
    $archivos = $mapper->getArchivosConsulta(107);
    echo "<p>✅ getArchivosConsulta() ejecutado</p>";
    echo "<p>📁 Archivos encontrados: " . json_encode($archivos) . "</p>";
    
} catch (Exception $e) {
    echo "<h3>❌ ERROR CAPTURADO:</h3>";
    echo "<p><strong>Mensaje:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>Archivo:</strong> " . $e->getFile() . "</p>";
    echo "<p><strong>Línea:</strong> " . $e->getLine() . "</p>";
    echo "<p><strong>Stack trace:</strong></p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
} catch (Error $e) {
    echo "<h3>❌ ERROR FATAL CAPTURADO:</h3>";
    echo "<p><strong>Mensaje:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>Archivo:</strong> " . $e->getFile() . "</p>";
    echo "<p><strong>Línea:</strong> " . $e->getLine() . "</p>";
    echo "<p><strong>Stack trace:</strong></p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<h3>🔧 Paso 6: Probando conexión PDO directa...</h3>";
try {
    $pdo = new PDO('pgsql:host=localhost;dbname=clinica', 'postgres', '12345678');
    echo "<p>✅ Conexión PDO directa exitosa</p>";
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM consultas WHERE id_consulta = 107");
    $count = $stmt->fetch();
    echo "<p>📊 Consulta 107 existe: " . ($count['count'] > 0 ? 'Sí' : 'No') . "</p>";
    
} catch (Exception $e) {
    echo "<p>❌ Error PDO: " . $e->getMessage() . "</p>";
}
?>