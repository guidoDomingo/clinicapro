<?php
/**
 * Diagnóstico completo del sistema en el servidor
 */

header('Content-Type: text/html; charset=UTF-8');

echo "<h1>🔍 Diagnóstico del Sistema Clínica</h1>";
echo "<p><strong>Fecha:</strong> " . date('Y-m-d H:i:s') . "</p>";
echo "<hr>";

// 1. Información del servidor
echo "<h2>📋 Información del Servidor</h2>";
echo "<ul>";
echo "<li><strong>Sistema Operativo:</strong> " . php_uname() . "</li>";
echo "<li><strong>Versión PHP:</strong> " . phpversion() . "</li>";
echo "<li><strong>Servidor Web:</strong> " . $_SERVER['SERVER_SOFTWARE'] . "</li>";
echo "<li><strong>Host:</strong> " . $_SERVER['HTTP_HOST'] . "</li>";
echo "<li><strong>REQUEST_URI:</strong> " . $_SERVER['REQUEST_URI'] . "</li>";
echo "<li><strong>DOCUMENT_ROOT:</strong> " . $_SERVER['DOCUMENT_ROOT'] . "</li>";
echo "</ul>";

// 2. Verificar estructura de archivos
echo "<h2>📁 Estructura de Archivos</h2>";
$baseDir = $_SERVER['DOCUMENT_ROOT'];
$criticalFiles = [
    'index.php',
    'modules/mail/api/mail_config.php',
    'modules/mail/api/check_mail_setup.php',
    'view/modules/mail/configuracion-correo.php',
    '.htaccess'
];

echo "<ul>";
foreach ($criticalFiles as $file) {
    $fullPath = $baseDir . '/' . $file;
    if (file_exists($fullPath)) {
        $perms = substr(sprintf('%o', fileperms($fullPath)), -4);
        echo "<li>✅ <strong>$file</strong> - Permisos: $perms</li>";
    } else {
        echo "<li>❌ <strong>$file</strong> - NO EXISTE</li>";
    }
}
echo "</ul>";

// 3. Prueba de conexión a BD
echo "<h2>🗄️ Conexión a Base de Datos</h2>";
try {
    $dsn = "pgsql:host=181.122.125.143;port=5454;dbname=clinica";
    $pdo = new PDO($dsn, 'acmeuser', 'wjstks', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_TIMEOUT => 5
    ]);
    echo "✅ <strong>Conexión exitosa</strong><br>";
    
    // Verificar tablas de mail
    $stmt = $pdo->query("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public' AND table_name LIKE 'mail_%'");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (count($tables) > 0) {
        echo "✅ <strong>Tablas de correo encontradas:</strong> " . implode(', ', $tables) . "<br>";
    } else {
        echo "⚠️ <strong>No se encontraron tablas de correo</strong><br>";
    }
    
} catch (Exception $e) {
    echo "❌ <strong>Error de conexión:</strong> " . $e->getMessage() . "<br>";
}

// 4. Probar acceso directo a API
echo "<h2>🔗 Prueba de API</h2>";
$apiUrl = "http://181.122.125.143/modules/mail/api/mail_config.php?action=get";
echo "<p>Probando: <a href=\"$apiUrl\" target=\"_blank\">$apiUrl</a></p>";

$context = stream_context_create([
    'http' => [
        'timeout' => 10
    ]
]);

try {
    $response = file_get_contents($apiUrl, false, $context);
    if ($response !== false) {
        echo "✅ <strong>API responde correctamente</strong><br>";
        echo "<details><summary>Ver respuesta</summary><pre>" . htmlspecialchars($response) . "</pre></details>";
    } else {
        echo "❌ <strong>API no responde</strong><br>";
    }
} catch (Exception $e) {
    echo "❌ <strong>Error al probar API:</strong> " . $e->getMessage() . "<br>";
}

// 5. Variables de entorno y configuración
echo "<h2>⚙️ Configuración PHP</h2>";
echo "<ul>";
echo "<li><strong>max_execution_time:</strong> " . ini_get('max_execution_time') . "</li>";
echo "<li><strong>memory_limit:</strong> " . ini_get('memory_limit') . "</li>";
echo "<li><strong>post_max_size:</strong> " . ini_get('post_max_size') . "</li>";
echo "<li><strong>upload_max_filesize:</strong> " . ini_get('upload_max_filesize') . "</li>";
echo "<li><strong>error_reporting:</strong> " . error_reporting() . "</li>";
echo "<li><strong>display_errors:</strong> " . (ini_get('display_errors') ? 'On' : 'Off') . "</li>";
echo "</ul>";

// 6. Extensiones necesarias
echo "<h2>🧩 Extensiones PHP</h2>";
$requiredExtensions = ['pdo', 'pdo_pgsql', 'curl', 'json', 'mbstring'];
echo "<ul>";
foreach ($requiredExtensions as $ext) {
    if (extension_loaded($ext)) {
        echo "<li>✅ <strong>$ext</strong></li>";
    } else {
        echo "<li>❌ <strong>$ext</strong> - NO CARGADA</li>";
    }
}
echo "</ul>";

// 7. Información sobre la sesión
echo "<h2>🔐 Información de Sesión</h2>";
session_start();
echo "<ul>";
echo "<li><strong>Session ID:</strong> " . session_id() . "</li>";
echo "<li><strong>Session Name:</strong> " . session_name() . "</li>";
echo "<li><strong>Session Path:</strong> " . session_save_path() . "</li>";
echo "</ul>";

echo "<hr>";
echo "<p><strong>Diagnóstico completado</strong> - " . date('Y-m-d H:i:s') . "</p>";
?>