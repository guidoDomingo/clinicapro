<?php
/**
 * Diagnóstico del Sistema
 * Verifica que la configuración esté funcionando correctamente
 */

require_once __DIR__ . '/config/environment_setup.php';

// Inicializar entorno
EnvironmentSetup::initialize();

echo "<h1>Diagnóstico del Sistema Clínica</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .success { color: green; font-weight: bold; }
    .error { color: red; font-weight: bold; }
    .warning { color: orange; font-weight: bold; }
    .info { color: blue; }
    table { border-collapse: collapse; width: 100%; margin: 20px 0; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    th { background-color: #f2f2f2; }
</style>";

// 1. Verificar entorno
echo "<h2>1. Información del Entorno</h2>";
echo "<table>";
echo "<tr><th>Variable</th><th>Valor</th><th>Estado</th></tr>";

$isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
echo "<tr><td>Sistema Operativo</td><td>" . PHP_OS . "</td><td class='info'>Windows: " . ($isWindows ? 'Sí' : 'No') . "</td></tr>";
echo "<tr><td>PHP Version</td><td>" . PHP_VERSION . "</td><td class='success'>OK</td></tr>";
echo "<tr><td>APP_ENV</td><td>" . ($_ENV['APP_ENV'] ?? 'no definido') . "</td><td class='" . (isset($_ENV['APP_ENV']) ? 'success' : 'error') . "'>" . (isset($_ENV['APP_ENV']) ? 'OK' : 'ERROR') . "</td></tr>";

// 2. Verificar extensiones PHP
echo "</table>";
echo "<h2>2. Extensiones PHP</h2>";
echo "<table>";
echo "<tr><th>Extensión</th><th>Estado</th></tr>";

$extensions = ['pdo', 'pdo_pgsql', 'json', 'mbstring', 'openssl'];
foreach ($extensions as $ext) {
    $loaded = extension_loaded($ext);
    echo "<tr><td>$ext</td><td class='" . ($loaded ? 'success' : 'error') . "'>" . ($loaded ? 'Cargada' : 'NO CARGADA') . "</td></tr>";
}

// 3. Verificar directorios
echo "</table>";
echo "<h2>3. Directorios del Sistema</h2>";
echo "<table>";
echo "<tr><th>Directorio</th><th>Ruta</th><th>Existe</th><th>Escribible</th></tr>";

$dirs = [
    'Logs' => EnvironmentSetup::getLogPath(),
    'Uploads' => EnvironmentSetup::getUploadPath(),
    'Temp' => EnvironmentSetup::getTempPath()
];

foreach ($dirs as $name => $path) {
    $exists = file_exists($path);
    $writable = $exists ? is_writable($path) : false;
    
    echo "<tr>";
    echo "<td>$name</td>";
    echo "<td>$path</td>";
    echo "<td class='" . ($exists ? 'success' : 'error') . "'>" . ($exists ? 'Sí' : 'NO') . "</td>";
    echo "<td class='" . ($writable ? 'success' : 'error') . "'>" . ($writable ? 'Sí' : 'NO') . "</td>";
    echo "</tr>";
}

// 4. Verificar conexión a base de datos
echo "</table>";
echo "<h2>4. Conexión a Base de Datos</h2>";

try {
    require_once __DIR__ . '/model/conexion.php';
    $db = Conexion::conectar();
    
    if ($db === null) {
        echo "<p class='error'>❌ Error: No se pudo conectar a la base de datos</p>";
        
        $config = EnvironmentSetup::getDatabaseConfig();
        echo "<h3>Configuración actual:</h3>";
        echo "<table>";
        foreach ($config as $key => $value) {
            if ($key === 'password') {
                $value = str_repeat('*', strlen($value));
            }
            echo "<tr><td>$key</td><td>$value</td></tr>";
        }
        echo "</table>";
        
    } else {
        echo "<p class='success'>✅ Conexión exitosa a la base de datos</p>";
        
        // Probar una consulta simple
        try {
            $stmt = $db->prepare("SELECT version()");
            $stmt->execute();
            $version = $stmt->fetchColumn();
            echo "<p class='info'>Versión PostgreSQL: $version</p>";
            
            // Verificar algunas tablas importantes
            $tables = ['sys_users', 'sys_register', 'sys_permissions'];
            echo "<h3>Tablas del sistema:</h3>";
            echo "<table>";
            echo "<tr><th>Tabla</th><th>Estado</th></tr>";
            
            foreach ($tables as $table) {
                try {
                    $stmt = $db->prepare("SELECT COUNT(*) FROM $table");
                    $stmt->execute();
                    $count = $stmt->fetchColumn();
                    echo "<tr><td>$table</td><td class='success'>OK ($count registros)</td></tr>";
                } catch (Exception $e) {
                    echo "<tr><td>$table</td><td class='error'>ERROR: " . $e->getMessage() . "</td></tr>";
                }
            }
            echo "</table>";
            
        } catch (Exception $e) {
            echo "<p class='warning'>⚠️ Conexión establecida pero error en consulta: " . $e->getMessage() . "</p>";
        }
    }
} catch (Exception $e) {
    echo "<p class='error'>❌ Error crítico: " . $e->getMessage() . "</p>";
}

// 5. Verificar Logger
echo "<h2>5. Sistema de Logs</h2>";
try {
    if (class_exists('\Api\Core\Logger')) {
        \Api\Core\Logger::info('Prueba de diagnóstico', 'Sistema de diagnóstico funcionando');
        echo "<p class='success'>✅ Logger funcionando correctamente</p>";
    } else {
        echo "<p class='error'>❌ Clase Logger no encontrada</p>";
    }
} catch (Exception $e) {
    echo "<p class='error'>❌ Error en Logger: " . $e->getMessage() . "</p>";
}

// 6. Verificar archivos de log
$logPath = EnvironmentSetup::getLogPath();
if (file_exists($logPath . 'application.log')) {
    $size = filesize($logPath . 'application.log');
    echo "<p class='info'>📝 Archivo de log existe (tamaño: " . number_format($size) . " bytes)</p>";
} else {
    echo "<p class='warning'>⚠️ Archivo de log no existe aún</p>";
}

// 7. Información de URLs
echo "<h2>6. URLs del Sistema</h2>";
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$baseUrl = "$protocol://$host/clinica";

echo "<ul>";
echo "<li><strong>URL Base:</strong> <a href='$baseUrl' target='_blank'>$baseUrl</a></li>";
echo "<li><strong>Admin:</strong> <a href='$baseUrl/admin' target='_blank'>$baseUrl/admin</a></li>";
echo "<li><strong>API:</strong> <a href='$baseUrl/api' target='_blank'>$baseUrl/api</a></li>";
echo "<li><strong>Reservas Públicas:</strong> <a href='$baseUrl/public_reservas' target='_blank'>$baseUrl/public_reservas</a></li>";
echo "</ul>";

echo "<h2>🎯 Resumen</h2>";
echo "<p>Diagnóstico completado el " . date('Y-m-d H:i:s') . "</p>";
echo "<p><em>Si hay errores en rojo, revisa la configuración correspondiente.</em></p>";