<?php
// Diagnóstico simple - solo responder si el archivo existe
header('Content-Type: text/plain');
echo "DIAGNOSTICO SIMPLE\n";
echo "==================\n";
echo "Fecha: " . date('Y-m-d H:i:s') . "\n";
echo "PHP Version: " . PHP_VERSION . "\n";
echo "Directorio: " . __DIR__ . "\n";

$mailConfigFile = __DIR__ . '/modules/mail/api/mail_config.php';
echo "Archivo buscado: " . $mailConfigFile . "\n";
echo "Existe: " . (file_exists($mailConfigFile) ? 'SI' : 'NO') . "\n";

if (file_exists($mailConfigFile)) {
    echo "Tamaño: " . filesize($mailConfigFile) . " bytes\n";
    echo "Modificado: " . date('Y-m-d H:i:s', filemtime($mailConfigFile)) . "\n";
    
    $content = file_get_contents($mailConfigFile);
    echo "Primeros 100 caracteres:\n";
    echo substr($content, 0, 100) . "\n";
    
    echo "¿Tiene problema duplicado? " . (strpos($content, '?>?>') !== false ? 'SI' : 'NO') . "\n";
    echo "¿Es versión limpia? " . (strpos($content, 'API limpia para configuración') !== false ? 'SI' : 'NO') . "\n";
}

echo "\nEXTENSIONES:\n";
echo "PDO: " . (extension_loaded('pdo') ? 'SI' : 'NO') . "\n";
echo "PDO_PGSQL: " . (extension_loaded('pdo_pgsql') ? 'SI' : 'NO') . "\n";

echo "\nTEST CONEXION BD:\n";
try {
    $dsn = "pgsql:host=181.122.125.143;port=5454;dbname=clinica";
    $pdo = new PDO($dsn, 'acmeuser', 'wjstks', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_TIMEOUT => 5
    ]);
    echo "Conexión BD: OK\n";
    
    $stmt = $pdo->query("SELECT COUNT(*) FROM mail_config");
    $count = $stmt->fetchColumn();
    echo "Registros mail_config: " . $count . "\n";
    
} catch (Exception $e) {
    echo "Error BD: " . $e->getMessage() . "\n";
}
?>