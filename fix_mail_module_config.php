<?php
/**
 * Script para actualizar configuraciones hardcodeadas en el módulo de correo
 * Convierte todas las conexiones hardcodeadas para usar el sistema multi-entorno
 */

echo "<h1>Actualizador del Módulo de Correo</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .success { color: green; font-weight: bold; }
    .error { color: red; font-weight: bold; }
    .warning { color: orange; font-weight: bold; }
    .info { color: blue; }
    pre { background: #f5f5f5; padding: 10px; border-radius: 5px; overflow: auto; }
    .file { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
</style>";

// Archivos a actualizar
$filesToUpdate = [
    __DIR__ . '/modules/mail/db_test.php',
    __DIR__ . '/modules/mail/api/mail_config_debug_detailed.php',
    __DIR__ . '/modules/mail/api/test_step_by_step.php'
];

$initCode = "
// Inicializar configuración del entorno
require_once dirname(dirname(dirname(__DIR__))) . '/config/environment_setup.php';
EnvironmentSetup::initialize();
\$dbConfig = EnvironmentSetup::getDatabaseConfig();
";

$connectionCode = "\$dsn = \"{\$dbConfig['driver']}:host={\$dbConfig['host']};port={\$dbConfig['port']};dbname={\$dbConfig['database']}\";
\$pdo = new PDO(\$dsn, \$dbConfig['username'], \$dbConfig['password'], [";

echo "<h2>Archivos a Actualizar</h2>";

foreach ($filesToUpdate as $file) {
    echo "<div class='file'>";
    echo "<h3>" . basename($file) . "</h3>";
    
    if (!file_exists($file)) {
        echo "<p class='warning'>⚠️ Archivo no encontrado: $file</p>";
        continue;
    }
    
    $content = file_get_contents($file);
    $originalContent = $content;
    
    // Buscar y reemplazar configuraciones hardcodeadas
    $patterns = [
        // Patrón para producción
        '/\$dsn\s*=\s*["\']pgsql:host=181\.122\.125\.143;port=5454;dbname=clinica["\'];?\s*\n?\s*\$pdo\s*=\s*new\s+PDO\(\$dsn,\s*["\']acmeuser["\'],\s*["\']wjstks["\']/',
        // Patrón para local
        '/\$dsn\s*=\s*["\']pgsql:host=localhost;port=5432;dbname=clinica["\'];?\s*\n?\s*\$pdo\s*=\s*new\s+PDO\(\$dsn,\s*["\']postgres["\'],\s*["\'][^"\']*["\']/',
        // Patrón simple de array de configuración
        '/["\']host["\']\s*=>\s*["\']181\.122\.125\.143["\']/',
        '/["\']port["\']\s*=>\s*5454/',
        '/["\']username["\']\s*=>\s*["\']acmeuser["\']/',
        '/["\']password["\']\s*=>\s*["\']wjstks["\']/'
    ];
    
    $replacements = [
        $initCode . $connectionCode,
        $initCode . $connectionCode,
        "'host' => \$dbConfig['host']",
        "'port' => \$dbConfig['port']",
        "'username' => \$dbConfig['username']",
        "'password' => \$dbConfig['password']"
    ];
    
    $updated = false;
    foreach ($patterns as $i => $pattern) {
        if (preg_match($pattern, $content)) {
            $content = preg_replace($pattern, $replacements[$i], $content);
            $updated = true;
            echo "<p class='info'>✓ Patrón " . ($i + 1) . " encontrado y reemplazado</p>";
        }
    }
    
    // Verificar cambios específicos por archivo
    $filename = basename($file);
    switch ($filename) {
        case 'db_test.php':
            // Agregar require al inicio si no existe
            if (!strpos($content, 'environment_setup.php')) {
                $content = str_replace(
                    '<?php',
                    "<?php\n// Inicializar configuración del entorno\nrequire_once dirname(dirname(__DIR__)) . '/config/environment_setup.php';\nEnvironmentSetup::initialize();",
                    $content
                );
                $updated = true;
                echo "<p class='info'>✓ Agregado require de environment_setup</p>";
            }
            break;
            
        case 'mail_config_debug_detailed.php':
        case 'test_step_by_step.php':
            // Buscar patrones específicos
            if (strpos($content, '181.122.125.143') !== false) {
                $content = str_replace('181.122.125.143', "' . \$dbConfig['host'] . '", $content);
                $content = str_replace('5454', "' . \$dbConfig['port'] . '", $content);
                $content = str_replace('acmeuser', "' . \$dbConfig['username'] . '", $content);
                $content = str_replace('wjstks', "' . \$dbConfig['password'] . '", $content);
                $updated = true;
                echo "<p class='info'>✓ Reemplazadas configuraciones hardcodeadas</p>";
            }
            break;
    }
    
    if ($updated) {
        if (file_put_contents($file, $content)) {
            echo "<p class='success'>✅ Archivo actualizado exitosamente</p>";
        } else {
            echo "<p class='error'>❌ Error al escribir archivo</p>";
        }
    } else {
        echo "<p class='info'>ℹ️ No se encontraron patrones para actualizar</p>";
    }
    
    echo "</div>";
}

echo "<h2>🎯 Resumen</h2>";
echo "<p>Proceso completado. Todos los archivos del módulo de correo han sido revisados y actualizados para usar el sistema de configuración multi-entorno.</p>";

echo "<h3>Para verificar:</h3>";
echo "<ol>";
echo "<li>Ejecuta: <code>http://tu-dominio.com/clinica/test_mail_module.php</code></li>";
echo "<li>Prueba el endpoint: <code>http://tu-dominio.com/modules/mail/api/send_pdf.php</code></li>";
echo "<li>Verifica la configuración de correo en el sistema</li>";
echo "</ol>";

echo "<p><em>Actualización completada el " . date('Y-m-d H:i:s') . "</em></p>";
?>