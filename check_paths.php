<?php
/**
 * Utility script to check paths for critical files
 * Run this to diagnose file path issues
 */

// Descomentar para mostrar errores en la página
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo '<h1>Checking Critical Paths</h1>';

// Guardar resultados
$results = [];

// Función para verificar archivos
function checkFile($path) {
    if (file_exists($path)) {
        return [
            'status' => 'OK', 
            'message' => 'File exists',
            'real_path' => realpath($path)
        ];
    } else {
        return [
            'status' => 'ERROR', 
            'message' => 'File not found'
        ];
    }
}

// Rutas críticas
$criticalFiles = [
    'Controller User' => __DIR__ . '/controller/user.controller.php',
    'Controller Profile' => __DIR__ . '/controller/profile.controller.php',
    'Model Conexion' => __DIR__ . '/model/conexion.php',
    'Logger' => __DIR__ . '/api/core/Logger.php',
    'Router' => __DIR__ . '/api/core/Router.php',
    'API Index' => __DIR__ . '/api/index.php',
    'API Routes' => __DIR__ . '/api/routes/api.php'
];

// Verificar cada archivo
echo '<table border="1" cellpadding="5" style="border-collapse: collapse;">';
echo '<tr><th>Component</th><th>Path</th><th>Status</th><th>Message</th><th>Real Path</th></tr>';

foreach ($criticalFiles as $name => $path) {
    $result = checkFile($path);
    $statusColor = $result['status'] === 'OK' ? 'green' : 'red';
    
    echo '<tr>';
    echo "<td>$name</td>";
    echo "<td>$path</td>";
    echo "<td style='color: $statusColor; font-weight: bold;'>{$result['status']}</td>";
    echo "<td>{$result['message']}</td>";
    echo "<td>" . ($result['real_path'] ?? 'N/A') . "</td>";
    echo '</tr>';
    
    $results[$name] = $result;
}

echo '</table>';

// Comprobar dirname en diferentes niveles
echo '<h2>Directory Structure</h2>';
echo '<table border="1" cellpadding="5" style="border-collapse: collapse;">';
echo '<tr><th>Level</th><th>Path</th><th>Real Path</th></tr>';

$levels = [
    'Current Directory' => __DIR__,
    'dirname(__DIR__)' => dirname(__DIR__),
    'dirname(dirname(__DIR__))' => dirname(dirname(__DIR__)),
    'dirname(__FILE__)' => dirname(__FILE__),
    'dirname(dirname(__FILE__))' => dirname(dirname(__FILE__)),
];

foreach ($levels as $name => $path) {
    echo '<tr>';
    echo "<td>$name</td>";
    echo "<td>$path</td>";
    echo "<td>" . realpath($path) . "</td>";
    echo '</tr>';
}

echo '</table>';

// Información del servidor
echo '<h2>Server Information</h2>';
echo '<pre>';
echo 'SCRIPT_NAME: ' . $_SERVER['SCRIPT_NAME'] . "\n";
echo 'PHP_SELF: ' . $_SERVER['PHP_SELF'] . "\n";
echo 'DOCUMENT_ROOT: ' . $_SERVER['DOCUMENT_ROOT'] . "\n";
echo 'SERVER_NAME: ' . $_SERVER['SERVER_NAME'] . "\n";
echo 'REQUEST_URI: ' . $_SERVER['REQUEST_URI'] . "\n";
echo '</pre>';

// Sugerencias para arreglar problemas
echo '<h2>Suggestions</h2>';
if (count(array_filter($results, function($r) { return $r['status'] === 'ERROR'; })) > 0) {
    echo '<div style="color: red; border: 2px solid red; padding: 10px;">';
    echo '<p><strong>Found issues with file paths. Try the following:</strong></p>';
    echo '<ol>';
    echo '<li>Verify the directory structure matches the expected paths</li>';
    echo '<li>Check file permissions</li>';
    echo '<li>Consider using absolute paths in includes</li>';
    echo '<li>Make sure all required files are properly named and in the correct locations</li>';
    echo '</ol>';
    echo '</div>';
} else {
    echo '<div style="color: green; border: 2px solid green; padding: 10px;">';
    echo '<p><strong>All critical files found! If you\'re still experiencing issues, check:</strong></p>';
    echo '<ol>';
    echo '<li>File permissions</li>';
    echo '<li>PHP version compatibility</li>';
    echo '<li>Class naming and namespaces</li>';
    echo '<li>Server configuration</li>';
    echo '</ol>';
    echo '</div>';
}
