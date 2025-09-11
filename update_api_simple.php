<?php
/**
 * Script para actualizar archivos de API en el servidor
 */

echo "<h1>🔧 Actualizando archivos de API</h1>";
echo "<p><strong>Fecha:</strong> " . date('Y-m-d H:i:s') . "</p>";
echo "<hr>";

// 1. Actualizar configuración Nginx
echo "<h2>📋 Instrucciones para el servidor</h2>";
echo "<p>Ejecutar estos comandos en el servidor:</p>";
echo "<pre>";
echo "# 1. Actualizar configuración Nginx\n";
echo "sudo cp nginx_config_update.conf /etc/nginx/sites-available/clinica\n";
echo "sudo nginx -t\n";
echo "sudo systemctl reload nginx\n\n";

echo "# 2. Verificar archivos de API\n";
echo "ls -la /var/www/html/clinica/api/controllers/\n\n";

echo "# 3. Probar API directamente\n";
echo "curl -v http://181.122.125.143:8888/api/departments\n";
echo "curl -v http://181.122.125.143/api/departments\n";
echo "</pre>";

// 2. Verificar estado actual
echo "<h2>🔍 Estado actual</h2>";

$checkFiles = [
    '/var/www/html/clinica/api/index.php',
    '/var/www/html/clinica/api/core/Database.php',
    '/var/www/html/clinica/api/core/Router.php',
    '/var/www/html/clinica/api/controllers/LocationController.php',
    '/var/www/html/clinica/config/config.php'
];

foreach ($checkFiles as $file) {
    if (file_exists($file)) {
        echo "<p>✅ " . basename($file) . " existe</p>";
    } else {
        echo "<p>❌ " . basename($file) . " NO existe</p>";
    }
}

// 3. Probar conexión a base de datos
echo "<h2>🗄️ Prueba de conexión a BD</h2>";

try {
    $dsn = "pgsql:host=181.122.125.143;port=5454;dbname=clinica";
    $pdo = new PDO($dsn, 'acmeuser', 'wjstks', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_TIMEOUT => 5
    ]);
    
    echo "<p>✅ Conexión a BD exitosa</p>";
    
    // Verificar tablas necesarias
    $tables = ['departments', 'especialidades', 'rh_person'];
    foreach ($tables as $table) {
        try {
            $stmt = $pdo->query("SELECT COUNT(*) FROM $table");
            $count = $stmt->fetchColumn();
            echo "<p>✅ Tabla '$table': $count registros</p>";
        } catch (Exception $e) {
            echo "<p>❌ Tabla '$table': Error - " . $e->getMessage() . "</p>";
        }
    }
    
} catch (Exception $e) {
    echo "<p>❌ Error de conexión: " . $e->getMessage() . "</p>";
}

// 4. Información de depuración
echo "<h2>🐛 Información de depuración</h2>";
echo "<p><strong>REQUEST_URI:</strong> " . $_SERVER['REQUEST_URI'] . "</p>";
echo "<p><strong>SERVER_NAME:</strong> " . $_SERVER['SERVER_NAME'] . "</p>";
echo "<p><strong>SERVER_PORT:</strong> " . $_SERVER['SERVER_PORT'] . "</p>";
echo "<p><strong>HTTP_HOST:</strong> " . $_SERVER['HTTP_HOST'] . "</p>";

// 5. Enlaces de prueba
echo "<h2>🔗 Enlaces de prueba</h2>";
echo "<ul>";
echo "<li><a href='http://181.122.125.143/setup_ubicaciones.php' target='_blank'>Configurar ubicaciones</a></li>";
echo "<li><a href='http://181.122.125.143:8888/api/departments' target='_blank'>API Departamentos (puerto 8888)</a></li>";
echo "<li><a href='http://181.122.125.143/api/departments' target='_blank'>API Departamentos (puerto 80)</a></li>";
echo "<li><a href='http://181.122.125.143/test_api_quick.php' target='_blank'>Prueba rápida API</a></li>";
echo "</ul>";

echo "<hr>";
echo "<h3>📋 Próximos pasos:</h3>";
echo "<ol>";
echo "<li>Aplicar configuración Nginx actualizada</li>";
echo "<li>Verificar que las tablas de BD existen</li>";
echo "<li>Probar APIs desde diferentes puertos</li>";
echo "<li>Revisar logs de errores de Nginx</li>";
echo "</ol>";
?>