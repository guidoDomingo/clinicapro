<?php
$content = file_get_contents('view/modules/consultas-new.php');

// Buscar bloques no cerrados
$open_tags = [
    '{' => '}',
    '(' => ')',
    '[' => ']',
    '<script' => '</script>',
    '<style' => '</style>',
    '/*' => '*/'
];

echo "<h2>🔍 Diagnóstico de Balance de Bloques</h2>";

foreach ($open_tags as $open => $close) {
    $opens = substr_count($content, $open);
    $closes = substr_count($content, $close);
    $balance = $opens - $closes;
    
    $status = $balance == 0 ? "✅" : "❌";
    echo "<p>$status <strong>$open / $close</strong>: $opens aperturas, $closes cierres, balance: $balance</p>";
}

// Análisis más detallado de llaves
echo "<h3>📊 Análisis de JavaScript/CSS:</h3>";
preg_match_all('/<script[^>]*>(.*?)<\/script>/s', $content, $scripts);
preg_match_all('/<style[^>]*>(.*?)<\/style>/s', $content, $styles);

echo "<p>Scripts encontrados: " . count($scripts[0]) . "</p>";
echo "<p>Estilos encontrados: " . count($styles[0]) . "</p>";

// Verificar si hay script o style sin cerrar
$last_script_open = strrpos($content, '<script');
$last_script_close = strrpos($content, '</script>');
$last_style_open = strrpos($content, '<style');
$last_style_close = strrpos($content, '</style>');

echo "<p>Último script abre en posición: $last_script_open</p>";
echo "<p>Último script cierra en posición: $last_script_close</p>";
echo "<p>Último style abre en posición: $last_style_open</p>";
echo "<p>Último style cierra en posición: $last_style_close</p>";

if ($last_script_open > $last_script_close) {
    echo "<p>❌ PROBLEMA: Hay un &lt;script&gt; sin cerrar</p>";
}
if ($last_style_open > $last_style_close) {
    echo "<p>❌ PROBLEMA: Hay un &lt;style&gt; sin cerrar</p>";
}

// Mostrar las últimas 10 líneas del archivo
$lines = explode("\n", $content);
echo "<h3>📄 Últimas 10 líneas del archivo:</h3>";
echo "<pre>";
for ($i = max(0, count($lines) - 10); $i < count($lines); $i++) {
    $line_num = $i + 1;
    echo sprintf("%4d: %s\n", $line_num, htmlspecialchars($lines[$i]));
}
echo "</pre>";
?>