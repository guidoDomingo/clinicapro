<?php
$content = file_get_contents('view/modules/consultas-new.php');
$lines = explode("\n", $content);

echo "<h2>🔍 Análisis detallado de llaves { }</h2>";

$brace_stack = [];
$line_info = [];

for ($i = 0; $i < count($lines); $i++) {
    $line = $lines[$i];
    $line_num = $i + 1;
    
    // Contar llaves abiertas y cerradas
    $open_braces = substr_count($line, '{');
    $close_braces = substr_count($line, '}');
    
    if ($open_braces > 0 || $close_braces > 0) {
        $line_info[] = [
            'line' => $line_num,
            'open' => $open_braces,
            'close' => $close_braces,
            'content' => trim($line)
        ];
        
        // Agregar al stack
        for ($j = 0; $j < $open_braces; $j++) {
            $brace_stack[] = $line_num;
        }
        
        // Remover del stack
        for ($j = 0; $j < $close_braces; $j++) {
            if (!empty($brace_stack)) {
                array_pop($brace_stack);
            }
        }
    }
}

echo "<h3>📊 Llaves sin cerrar (últimas 15 líneas con llaves):</h3>";
echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr><th>Línea</th><th>Abre</th><th>Cierra</th><th>Contenido</th></tr>";

$recent_lines = array_slice($line_info, -15);
foreach ($recent_lines as $info) {
    $balance_indicator = '';
    if ($info['open'] > $info['close']) {
        $balance_indicator = '↗️ +' . ($info['open'] - $info['close']);
    } elseif ($info['close'] > $info['open']) {
        $balance_indicator = '↘️ -' . ($info['close'] - $info['open']);
    }
    
    echo "<tr>";
    echo "<td>{$info['line']}</td>";
    echo "<td>{$info['open']}</td>";
    echo "<td>{$info['close']}</td>";
    echo "<td>" . htmlspecialchars(substr($info['content'], 0, 80)) . " $balance_indicator</td>";
    echo "</tr>";
}
echo "</table>";

echo "<h3>🔍 Líneas con llaves abiertas sin cerrar:</h3>";
if (!empty($brace_stack)) {
    echo "<p>❌ Se encontraron " . count($brace_stack) . " llaves sin cerrar:</p>";
    echo "<ul>";
    foreach (array_slice($brace_stack, -10) as $line_num) {
        $line_content = isset($lines[$line_num - 1]) ? trim($lines[$line_num - 1]) : '';
        echo "<li>Línea $line_num: " . htmlspecialchars(substr($line_content, 0, 100)) . "</li>";
    }
    echo "</ul>";
} else {
    echo "<p>✅ Todas las llaves están cerradas (esto no debería suceder según el diagnóstico anterior)</p>";
}

// Revisar específicamente scripts y estilos
echo "<h3>🔍 Verificación de scripts y estilos:</h3>";
$script_pattern = '/<script[^>]*>(.*?)<\/script>/s';
$style_pattern = '/<style[^>]*>(.*?)<\/style>/s';

preg_match_all($script_pattern, $content, $scripts, PREG_OFFSET_CAPTURE);
preg_match_all($style_pattern, $content, $styles, PREG_OFFSET_CAPTURE);

echo "<p>Scripts completos encontrados: " . count($scripts[0]) . "</p>";
echo "<p>Estilos completos encontrados: " . count($styles[0]) . "</p>";

// Verificar scripts/estilos sin cerrar
$unclosed_scripts = substr_count($content, '<script') - substr_count($content, '</script>');
$unclosed_styles = substr_count($content, '<style') - substr_count($content, '</style>');

if ($unclosed_scripts > 0) {
    echo "<p>❌ Scripts sin cerrar: $unclosed_scripts</p>";
}
if ($unclosed_styles > 0) {
    echo "<p>❌ Estilos sin cerrar: $unclosed_styles</p>";
}

// Mostrar contexto alrededor del final del archivo
echo "<h3>📄 Final del archivo (últimas 20 líneas):</h3>";
echo "<pre>";
for ($i = max(0, count($lines) - 20); $i < count($lines); $i++) {
    $line_num = $i + 1;
    $open_count = substr_count($lines[$i], '{');
    $close_count = substr_count($lines[$i], '}');
    $indicator = '';
    if ($open_count > 0) $indicator .= " +$open_count{";
    if ($close_count > 0) $indicator .= " -$close_count}";
    
    echo sprintf("%4d: %s%s\n", $line_num, htmlspecialchars($lines[$i]), $indicator);
}
echo "</pre>";
?>