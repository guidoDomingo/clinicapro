<?php
/**
 * Test simple para verificar servidor
 */
header('Content-Type: application/json; charset=UTF-8');

echo json_encode([
    'success' => true,
    'message' => 'Servidor funcionando',
    'php_version' => PHP_VERSION,
    'server' => $_SERVER['SERVER_SOFTWARE'] ?? 'nginx',
    'timestamp' => date('Y-m-d H:i:s'),
    'extensions' => [
        'pdo' => extension_loaded('pdo'),
        'pdo_pgsql' => extension_loaded('pdo_pgsql'),
        'json' => extension_loaded('json'),
        'session' => extension_loaded('session')
    ]
], JSON_PRETTY_PRINT);
?>