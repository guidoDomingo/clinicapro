<?php
/**
 * Script para encontrar comentarios HTML o PHP mal formateados
 */

$file = 'view/modules/consultas-new.php';
$content = file_get_contents($file);
$lines = explode("\n", $content);

echo "<h2>🔍 Análisis de comentarios en " . basename($file) . "</h2>";

// Buscar comentarios HTML sin cerrar
$html_comment_opens = 0;
$problematic_lines = [];

for ($i = 0; $i < count($lines); $i++) {
    $line = $lines[$i];
    $line_num = $i + 1;
    
    // Contar apertura de comentarios HTML
    preg_match_all('/<!--/', $line, $opens);
    preg_match_all('/-->/', $line, $closes);
    
    $opens_count = count($opens[0]);
    $closes_count = count($closes[0]);
    
    $html_comment_opens += $opens_count - $closes_count;
    
    if ($html_comment_opens > 0 && $line_num > 5) {
        $problematic_lines[] = [
            'line' => $line_num,
            'content' => trim($line),
            'open_count' => $html_comment_opens
        ];
    }
}

echo "<h3>📊 Comentarios HTML sin cerrar:</h3>";
if (empty($problematic_lines)) {
    echo "<p>✅ No se encontraron comentarios HTML sin cerrar</p>";
} else {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Línea</th><th>Contenido</th><th>Comentarios abiertos</th></tr>";
    foreach (array_slice($problematic_lines, 0, 20) as $problem) {
        echo "<tr>";
        echo "<td>{$problem['line']}</td>";
        echo "<td>" . htmlspecialchars($problem['content']) . "</td>";
        echo "<td>{$problem['open_count']}</td>";
        echo "</tr>";
    }
    echo "</table>";
}

// Buscar comentarios PHP sin cerrar
echo "<h3>📊 Análisis de comentarios PHP:</h3>";
$php_comment_pattern = '/\/\*(?!.*\*\/)/';
preg_match_all($php_comment_pattern, $content, $matches, PREG_OFFSET_CAPTURE);

if (empty($matches[0])) {
    echo "<p>✅ No se encontraron comentarios PHP sin cerrar</p>";
} else {
    echo "<p>⚠️ Se encontraron " . count($matches[0]) . " comentarios PHP potencialmente sin cerrar:</p>";
    foreach ($matches[0] as $match) {
        $offset = $match[1];
        $line_num = substr_count(substr($content, 0, $offset), "\n") + 1;
        $context = substr($content, max(0, $offset - 100), 200);
        echo "<div style='background: #f5f5f5; margin: 10px 0; padding: 10px; border: 1px solid #ccc;'>";
        echo "<strong>Línea $line_num:</strong><br>";
        echo "<pre>" . htmlspecialchars($context) . "</pre>";
        echo "</div>";
    }
}

// Verificar balance general
$all_php_opens = substr_count($content, '/*');
$all_php_closes = substr_count($content, '*/');
$all_html_opens = substr_count($content, '<!--');
$all_html_closes = substr_count($content, '-->');

echo "<h3>📊 Balance general de comentarios:</h3>";
echo "<table border='1' style='border-collapse: collapse;'>";
echo "<tr><th>Tipo</th><th>Aperturas</th><th>Cierres</th><th>Balance</th></tr>";
echo "<tr><td>PHP /*</td><td>$all_php_opens</td><td>$all_php_closes</td><td>" . ($all_php_opens - $all_php_closes) . "</td></tr>";
echo "<tr><td>HTML <!--</td><td>$all_html_opens</td><td>$all_html_closes</td><td>" . ($all_html_opens - $all_html_closes) . "</td></tr>";
echo "</table>";

if ($all_php_opens != $all_php_closes) {
    echo "<p>❌ PROBLEMA: Los comentarios PHP no están balanceados</p>";
}
if ($all_html_opens != $all_html_closes) {
    echo "<p>❌ PROBLEMA: Los comentarios HTML no están balanceados</p>";
}
?>