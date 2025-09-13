<?php
/**
 * Diagnóstico de extensiones PHP para el servidor
 */

header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');

$diagnostics = [];

try {
    // Información básica de PHP
    $diagnostics['php_version'] = PHP_VERSION;
    $diagnostics['server_software'] = $_SERVER['SERVER_SOFTWARE'] ?? 'unknown';
    $diagnostics['sapi_name'] = php_sapi_name();
    
    // Verificar extensiones críticas
    $required_extensions = [
        'pdo' => 'PDO (PHP Data Objects)',
        'pdo_pgsql' => 'PostgreSQL PDO Driver',
        'pgsql' => 'PostgreSQL Native Driver',
        'json' => 'JSON Processing',
        'session' => 'Session Management',
        'curl' => 'cURL (for HTTP requests)',
        'openssl' => 'OpenSSL (for HTTPS)',
        'mbstring' => 'Multibyte String',
        'fileinfo' => 'File Information',
        'hash' => 'Hash Functions'
    ];
    
    $diagnostics['extensions'] = [];
    $missing_extensions = [];
    
    foreach ($required_extensions as $ext => $description) {
        $loaded = extension_loaded($ext);
        $diagnostics['extensions'][$ext] = [
            'loaded' => $loaded,
            'description' => $description
        ];
        
        if (!$loaded) {
            $missing_extensions[] = $ext;
        }
    }
    
    // Verificar funciones críticas
    $required_functions = [
        'json_encode' => 'JSON Encoding',
        'json_decode' => 'JSON Decoding', 
        'session_start' => 'Session Start',
        'file_get_contents' => 'File Reading',
        'header' => 'HTTP Headers'
    ];
    
    $diagnostics['functions'] = [];
    $missing_functions = [];
    
    foreach ($required_functions as $func => $description) {
        $exists = function_exists($func);
        $diagnostics['functions'][$func] = [
            'exists' => $exists,
            'description' => $description
        ];
        
        if (!$exists) {
            $missing_functions[] = $func;
        }
    }
    
    // Test de PDO específico
    $diagnostics['pdo_drivers'] = [];
    if (extension_loaded('pdo')) {
        $diagnostics['pdo_drivers'] = PDO::getAvailableDrivers();
    }
    
    // Test de conexión básica
    $diagnostics['connection_test'] = 'not_tested';
    
    if (extension_loaded('pdo') && extension_loaded('pdo_pgsql')) {
        try {
            // Usar configuración dinámica
            require_once dirname(dirname(dirname(__DIR__))) . '/config/environment_setup.php';
            EnvironmentSetup::initialize();
            $dbConfig = EnvironmentSetup::getDatabaseConfig();
            
            $dsn = "{$dbConfig['driver']}:host={$dbConfig['host']};port={$dbConfig['port']};dbname={$dbConfig['database']}";
            $pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_TIMEOUT => 5
            ]);
            
            $diagnostics['connection_test'] = 'success';
            $diagnostics['db_version'] = $pdo->getAttribute(PDO::ATTR_SERVER_VERSION);
            
        } catch (Exception $e) {
            $diagnostics['connection_test'] = 'failed';
            $diagnostics['connection_error'] = $e->getMessage();
        }
    }
    
    // Determinar estado general
    $status = 'ok';
    $messages = [];
    
    if (!empty($missing_extensions)) {
        $status = 'error';
        $messages[] = 'Extensiones faltantes: ' . implode(', ', $missing_extensions);
    }
    
    if (!empty($missing_functions)) {
        $status = 'error';
        $messages[] = 'Funciones faltantes: ' . implode(', ', $missing_functions);
    }
    
    if (!in_array('pgsql', $diagnostics['pdo_drivers'] ?? [])) {
        $status = 'error';
        $messages[] = 'Driver PostgreSQL no disponible en PDO';
    }
    
    if ($diagnostics['connection_test'] === 'failed') {
        $status = 'warning';
        $messages[] = 'No se pudo conectar a la base de datos';
    }
    
    // Respuesta final
    echo json_encode([
        'success' => $status === 'ok',
        'status' => $status,
        'messages' => $messages,
        'diagnostics' => $diagnostics,
        'recommendations' => [
            'Si faltan extensiones, contactar al administrador del servidor',
            'Extensiones críticas: pdo, pdo_pgsql, json, session',
            'Verificar que PostgreSQL esté disponible',
            'Comprobar permisos de archivo y carpeta'
        ]
    ], JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Error en diagnóstico: ' . $e->getMessage(),
        'line' => $e->getLine(),
        'file' => basename($e->getFile())
    ], JSON_PRETTY_PRINT);
}
?>