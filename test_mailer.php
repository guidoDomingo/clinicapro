<?php
// Test del Mailer
require_once __DIR__ . '/config/environment_setup.php';
require_once __DIR__ . '/api/core/Mailer.php';

echo "<h1>🧪 Test del Mailer</h1>";

try {
    echo "<h2>1. Test de configuración de entorno</h2>";
    \EnvironmentSetup::initialize();
    $dbConfig = \EnvironmentSetup::getDatabaseConfig();
    echo "<p><strong>Config DB desde EnvironmentSetup:</strong></p>";
    echo "<pre>" . json_encode($dbConfig, JSON_PRETTY_PRINT) . "</pre>";
    
    echo "<h2>2. Test de obtención de configuración de mail</h2>";
    // Usar reflection para acceder al método privado
    $reflection = new ReflectionClass('Api\Core\Mailer');
    $method = $reflection->getMethod('getMailConfig');
    $method->setAccessible(true);
    
    $mailConfig = $method->invoke(null);
    echo "<p><strong>Config Mail obtenida:</strong></p>";
    if ($mailConfig) {
        echo "<pre>" . json_encode($mailConfig, JSON_PRETTY_PRINT) . "</pre>";
    } else {
        echo "<p>❌ No se pudo obtener configuración de mail</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'><strong>❌ Error:</strong></p>";
    echo "<p><strong>Mensaje:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>Archivo:</strong> " . $e->getFile() . "</p>";
    echo "<p><strong>Línea:</strong> " . $e->getLine() . "</p>";
    echo "<p><strong>Stack trace:</strong></p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>