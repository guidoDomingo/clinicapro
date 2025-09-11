<?php
/**
 * Prueba rápida de API - verificar configuración básica
 */

echo "<h1>🧪 Prueba Rápida de API</h1>";
echo "<p><strong>Fecha:</strong> " . date('Y-m-d H:i:s') . "</p>";
echo "<hr>";

// 1. Verificar que las clases se pueden cargar
echo "<h2>1. 📦 Verificando clases</h2>";

$coreClasses = [
    '/var/www/html/clinica/api/core/Database.php',
    '/var/www/html/clinica/api/core/Router.php',
    '/var/www/html/clinica/api/core/Response.php',
    '/var/www/html/clinica/api/core/Logger.php'
];

foreach ($coreClasses as $classFile) {
    if (file_exists($classFile)) {
        echo "<p>✅ <strong>" . basename($classFile) . "</strong> existe</p>";
        try {
            require_once $classFile;
            echo "<p>&nbsp;&nbsp;&nbsp;✅ Incluido correctamente</p>";
        } catch (Exception $e) {
            echo "<p>&nbsp;&nbsp;&nbsp;❌ Error al incluir: " . $e->getMessage() . "</p>";
        }
    } else {
        echo "<p>❌ <strong>" . basename($classFile) . "</strong> NO existe</p>";
    }
}

// 2. Verificar configuración .env
echo "<h2>2. ⚙️ Verificando configuración</h2>";

if (file_exists('/var/www/html/clinica/.env')) {
    echo "<p>✅ Archivo .env existe</p>";
    
    // Cargar manualmente las variables
    $envFile = file_get_contents('/var/www/html/clinica/.env');
    $envLines = explode("\n", $envFile);
    $envVars = [];
    
    foreach ($envLines as $line) {
        $line = trim($line);
        if (!empty($line) && strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            list($key, $value) = explode('=', $line, 2);
            $envVars[trim($key)] = trim($value);
        }
    }
    
    $requiredVars = ['DB_HOST', 'DB_PORT', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD'];
    foreach ($requiredVars as $var) {
        if (isset($envVars[$var]) && !empty($envVars[$var])) {
            echo "<p>&nbsp;&nbsp;&nbsp;✅ <strong>$var:</strong> " . $envVars[$var] . "</p>";
        } else {
            echo "<p>&nbsp;&nbsp;&nbsp;❌ <strong>$var:</strong> NO CONFIGURADO</p>";
        }
    }
} else {
    echo "<p>❌ Archivo .env NO existe</p>";
}

// 3. Probar conexión a base de datos manualmente
echo "<h2>3. 🗄️ Probando conexión directa a BD</h2>";

try {
    $host = $envVars['DB_HOST'] ?? '181.122.125.143';
    $port = $envVars['DB_PORT'] ?? '5454';
    $database = $envVars['DB_DATABASE'] ?? 'clinica';
    $username = $envVars['DB_USERNAME'] ?? 'acmeuser';
    $password = $envVars['DB_PASSWORD'] ?? 'wjstks';
    
    $dsn = "pgsql:host=$host;port=$port;dbname=$database";
    echo "<p>📝 <strong>DSN:</strong> $dsn</p>";
    echo "<p>📝 <strong>Usuario:</strong> $username</p>";
    
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_TIMEOUT => 5
    ]);
    
    echo "<p>✅ Conexión exitosa</p>";
    
    // Probar consulta simple
    $stmt = $pdo->query("SELECT COUNT(*) FROM departments");
    $count = $stmt->fetchColumn();
    echo "<p>✅ Prueba de consulta: $count departamentos encontrados</p>";
    
} catch (Exception $e) {
    echo "<p>❌ Error de conexión: " . $e->getMessage() . "</p>";
}

// 4. Verificar permisos de logs
echo "<h2>4. 📋 Verificando logs</h2>";

$logDir = '/var/log/clinica';
if (is_dir($logDir)) {
    echo "<p>✅ Directorio de logs existe</p>";
    if (is_writable($logDir)) {
        echo "<p>✅ Directorio es escribible</p>";
    } else {
        echo "<p>❌ Directorio NO es escribible</p>";
    }
} else {
    echo "<p>❌ Directorio de logs NO existe</p>";
    echo "<p>📝 Ejecutar: <code>sudo mkdir -p $logDir && sudo chown www-data:www-data $logDir</code></p>";
}

// 5. Probar llamada directa a API
echo "<h2>5. 🌐 Probando API directamente</h2>";

$apiTests = [
    'http://181.122.125.143/api/departments',
    'http://181.122.125.143/api/especialidades'
];

foreach ($apiTests as $url) {
    echo "<p>🧪 Probando: <a href='$url' target='_blank'>$url</a></p>";
    
    $context = stream_context_create([
        'http' => [
            'timeout' => 10,
            'header' => 'Accept: application/json'
        ]
    ]);
    
    try {
        $response = file_get_contents($url, false, $context);
        if ($response !== false) {
            $data = json_decode($response, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                echo "<p>&nbsp;&nbsp;&nbsp;✅ JSON válido recibido</p>";
                if (isset($data['status'])) {
                    echo "<p>&nbsp;&nbsp;&nbsp;📊 Status: " . $data['status'] . "</p>";
                }
            } else {
                echo "<p>&nbsp;&nbsp;&nbsp;❌ Respuesta no es JSON válido</p>";
                echo "<p>&nbsp;&nbsp;&nbsp;📄 Primeros 100 caracteres: " . htmlspecialchars(substr($response, 0, 100)) . "</p>";
            }
        } else {
            echo "<p>&nbsp;&nbsp;&nbsp;❌ No se pudo obtener respuesta</p>";
        }
    } catch (Exception $e) {
        echo "<p>&nbsp;&nbsp;&nbsp;❌ Error: " . $e->getMessage() . "</p>";
    }
}

echo "<hr>";
echo "<h3>📋 Resumen de acciones sugeridas:</h3>";
echo "<ol>";
echo "<li>Aplicar la nueva configuración Nginx</li>";
echo "<li>Ejecutar setup_logs.sh para configurar logs</li>";
echo "<li>Recargar Nginx: <code>sudo systemctl reload nginx</code></li>";
echo "<li>Verificar permisos de archivos de la API</li>";
echo "</ol>";
?>