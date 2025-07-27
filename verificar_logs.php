<?php
/**
 * Verificar logs de depuración del sistema
 */

echo "<h2>📋 Logs de Depuración del Sistema</h2>";

// Verificar si existen archivos de log
$logFiles = [
    'logs/debug_guardar.php',
    'logs/debug_guardar_detallado.php', 
    'logs/public_reservas.log',
    'logs/error.log',
    'error.log'
];

foreach ($logFiles as $logFile) {
    if (file_exists($logFile)) {
        echo "<h3>📁 $logFile</h3>";
        $content = file_get_contents($logFile);
        if (strlen($content) > 5000) {
            // Si el archivo es muy grande, mostrar solo las últimas líneas
            $lines = explode("\n", $content);
            $lastLines = array_slice($lines, -50);
            echo "<pre>" . htmlspecialchars(implode("\n", $lastLines)) . "</pre>";
            echo "<p><em>Mostrando las últimas 50 líneas del archivo</em></p>";
        } else {
            echo "<pre>" . htmlspecialchars($content) . "</pre>";
        }
        echo "<hr>";
    } else {
        echo "<p>📄 $logFile - No existe</p>";
    }
}

// Verificar errores de PHP
echo "<h3>🔍 Últimos errores de PHP</h3>";
$phpErrors = error_get_last();
if ($phpErrors) {
    echo "<pre>";
    print_r($phpErrors);
    echo "</pre>";
} else {
    echo "<p>✅ No hay errores recientes de PHP</p>";
}

// Verificar si las funciones de debug están definidas
echo "<h3>🛠️ Funciones de Debug</h3>";
echo "<p>debug_log: " . (function_exists('debug_log') ? '✅ Disponible' : '❌ No disponible') . "</p>";
echo "<p>debug_detallado: " . (function_exists('debug_detallado') ? '✅ Disponible' : '❌ No disponible') . "</p>";

// Mostrar información del sistema
echo "<h3>⚙️ Información del Sistema</h3>";
echo "<p>PHP Version: " . phpversion() . "</p>";
echo "<p>Server: " . $_SERVER['SERVER_SOFTWARE'] . "</p>";
echo "<p>Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "</p>";
?>
