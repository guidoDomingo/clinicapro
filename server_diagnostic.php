<?php
/**
 * Diagnóstico de servidor - Archivo en raíz
 */

header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');

$diagnostics = [];

try {
    // Información básica
    <?php
/**
 * Diagnóstico básico del servidor
 */

// Incluir configuración del entorno
require_once __DIR__ . '/config/environment_setup.php';
use Config\EnvironmentSetup;

$diagnostics = [];
$diagnostics['php_version'] = PHP_VERSION;
    $diagnostics['server'] = $_SERVER['SERVER_SOFTWARE'] ?? 'unknown';
    $diagnostics['document_root'] = $_SERVER['DOCUMENT_ROOT'] ?? 'unknown';
    $diagnostics['script_name'] = $_SERVER['SCRIPT_NAME'] ?? 'unknown';
    $diagnostics['current_dir'] = __DIR__;
    
    // Verificar estructura de carpetas
    $diagnostics['folders'] = [
        'modules' => is_dir(__DIR__ . '/modules'),
        'modules/mail' => is_dir(__DIR__ . '/modules/mail'),
        'modules/mail/api' => is_dir(__DIR__ . '/modules/mail/api'),
        'config' => is_dir(__DIR__ . '/config'),
        'vendor' => is_dir(__DIR__ . '/vendor')
    ];
    
    // Verificar archivos clave
    $diagnostics['files'] = [
        'config.php' => file_exists(__DIR__ . '/config/config.php'),
        '.env' => file_exists(__DIR__ . '/.env'),
        'mail_config.php' => file_exists(__DIR__ . '/modules/mail/api/mail_config.php'),
        'composer.json' => file_exists(__DIR__ . '/composer.json')
    ];
    
    // Verificar extensiones críticas
    $required_extensions = ['pdo', 'pdo_pgsql', 'json', 'session'];
    $diagnostics['extensions'] = [];
    $missing = [];
    
    foreach ($required_extensions as $ext) {
        $loaded = extension_loaded($ext);
        $diagnostics['extensions'][$ext] = $loaded;
        if (!$loaded) $missing[] = $ext;
    }
    
    // Test de PDO drivers
    if (extension_loaded('pdo')) {
        $diagnostics['pdo_drivers'] = PDO::getAvailableDrivers();
    }
    
    // Test de conexión básica
    if (extension_loaded('pdo_pgsql')) {
        try {
            // Obtener configuración de base de datos dinámicamente
            $dbConfig = EnvironmentSetup::getDatabaseConfig();
            $dsn = "pgsql:host={$dbConfig['host']};port={$dbConfig['port']};dbname={$dbConfig['dbname']}";
            $pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password'], [PDO::ATTR_TIMEOUT => 5]);
            $diagnostics['db_connection'] = 'success';
        } catch (Exception $e) {
            $diagnostics['db_connection'] = 'failed: ' . $e->getMessage();
        }
    } else {
        $diagnostics['db_connection'] = 'pdo_pgsql not available';
    }
    
    // Determinar problema principal
    $status = 'ok';
    $issues = [];
    
    if (!empty($missing)) {
        $status = 'error';
        $issues[] = 'Extensiones faltantes: ' . implode(', ', $missing);
    }
    
    if (!$diagnostics['folders']['modules/mail/api']) {
        $status = 'error';
        $issues[] = 'Carpeta modules/mail/api no existe';
    }
    
    if (!$diagnostics['files']['mail_config.php']) {
        $status = 'error';
        $issues[] = 'Archivo mail_config.php no existe';
    }
    
    echo json_encode([
        'success' => $status === 'ok',
        'status' => $status,
        'issues' => $issues,
        'diagnostics' => $diagnostics,
        'next_steps' => [
            '1. Verificar que la carpeta modules/mail/api existe',
            '2. Subir archivos faltantes al servidor',
            '3. Instalar extensiones PHP faltantes',
            '4. Verificar permisos de archivos'
        ]
    ], JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'file' => basename($e->getFile()),
        'line' => $e->getLine()
    ]);
}
?>