<?php
/**
 * Debug para verificar qué está pasando con el HTML de consultas-new.php
 */

echo "<h2>🔍 Diagnóstico de HTML Output</h2>";

ob_start();
include 'view/modules/consultas-new.php';
$content = ob_get_clean();

echo "<h3>📏 Tamaño del contenido:</h3>";
echo "<p>Contenido generado: " . strlen($content) . " bytes</p>";

echo "<h3>🏁 Primeros 1000 caracteres:</h3>";
echo "<pre style='background: #f5f5f5; padding: 10px; border: 1px solid #ccc;'>";
echo htmlspecialchars(substr($content, 0, 1000));
echo "</pre>";

echo "<h3>🏁 Últimos 500 caracteres:</h3>";
echo "<pre style='background: #f5f5f5; padding: 10px; border: 1px solid #ccc;'>";
echo htmlspecialchars(substr($content, -500));
echo "</pre>";

echo "<h3>🔍 Buscar elementos principales:</h3>";
$elements_to_find = [
    '<body' => 'Body tag',
    'class="consultas-app"' => 'Consultas App class',
    'id="main-content"' => 'Main content div',
    'alejandro visconte' => 'Texto alejandro visconte'
];

foreach ($elements_to_find as $search => $description) {
    $found = strpos($content, $search) !== false;
    echo "<p>✅ " . ($found ? "✅" : "❌") . " $description: " . ($found ? "ENCONTRADO" : "NO ENCONTRADO") . "</p>";
}

echo "<h3>💭 Headers HTTP:</h3>";
foreach (headers_list() as $header) {
    echo "<p>$header</p>";
}
?>