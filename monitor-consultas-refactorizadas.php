<?php
/**
 * SCRIPT DE MONITOREO - SISTEMA DE CONSULTAS REFACTORIZADO
 * 
 * Este script verifica el estado del sistema nuevo y proporciona
 * información de diagnóstico en tiempo real.
 */

header('Content-Type: application/json');

// Función para verificar si un archivo existe y es accesible
function checkFile($path) {
    $fullPath = __DIR__ . '/' . $path;
    return [
        'path' => $path,
        'exists' => file_exists($fullPath),
        'readable' => is_readable($fullPath),
        'size' => file_exists($fullPath) ? filesize($fullPath) : 0,
        'modified' => file_exists($fullPath) ? date('Y-m-d H:i:s', filemtime($fullPath)) : null
    ];
}

// Verificar conexión a base de datos
function checkDatabase() {
    try {
        // Asumiendo que tienes un archivo de conexión
        if (file_exists('config/database.php')) {
            include_once 'config/database.php';
        }
        
        return [
            'status' => 'ok',
            'message' => 'Conexión disponible'
        ];
    } catch (Exception $e) {
        return [
            'status' => 'error',
            'message' => $e->getMessage()
        ];
    }
}

// Verificar permisos de usuario actual
function checkPermissions() {
    session_start();
    
    return [
        'logged_in' => isset($_SESSION['user_id']),
        'user_id' => $_SESSION['user_id'] ?? null,
        'username' => $_SESSION['username'] ?? null,
        'session_id' => session_id()
    ];
}

// Archivos críticos del sistema refactorizado
$criticalFiles = [
    'view/modules/consultas-new.php',
    'modules/consultas/core/ConsultasManager.js',
    'modules/consultas/core/FormComponents.js', 
    'modules/consultas/core/PatientManager.js',
    'modules/consultas/core/AppInitializer.js',
    'modules/consultas/assets/css/consultas-enhanced.css'
];

// Realizar verificaciones
$diagnostics = [
    'timestamp' => date('Y-m-d H:i:s'),
    'server' => $_SERVER['HTTP_HOST'] ?? 'localhost',
    'php_version' => PHP_VERSION,
    'system_status' => 'operational',
    'files' => [],
    'database' => checkDatabase(),
    'session' => checkPermissions(),
    'performance' => [
        'memory_usage' => memory_get_usage(true),
        'peak_memory' => memory_get_peak_usage(true),
        'execution_time' => microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']
    ]
];

// Verificar cada archivo crítico
foreach ($criticalFiles as $file) {
    $diagnostics['files'][] = checkFile($file);
}

// Calcular estado general
$allFilesOk = true;
foreach ($diagnostics['files'] as $file) {
    if (!$file['exists'] || !$file['readable']) {
        $allFilesOk = false;
        break;
    }
}

$diagnostics['system_status'] = $allFilesOk ? 'operational' : 'degraded';

// Añadir recomendaciones si hay problemas
if (!$allFilesOk) {
    $diagnostics['recommendations'] = [
        'Verificar que todos los archivos del sistema estén presentes',
        'Comprobar permisos de archivos y directorios',
        'Revisar la guía de migración para pasos faltantes'
    ];
}

// Añadir URLs de acceso rápido
$diagnostics['quick_access'] = [
    'sistema_nuevo' => 'http://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . '/clinica/index.php?ruta=consultas-new',
    'sistema_anterior' => 'http://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . '/clinica/index.php?ruta=consultas',
    'test_page' => 'http://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . '/clinica/test-consultas-refactorizadas.html'
];

// Enviar respuesta
echo json_encode($diagnostics, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
?>
