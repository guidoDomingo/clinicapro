<?php
// Script para validar y corregir la sintaxis JavaScript en consultas-new.php

$filename = 'c:\laragon\www\clinica\view\modules\consultas-new.php';
$content = file_get_contents($filename);

// Función para encontrar todos los bloques <script>
function findScriptBlocks($content) {
    $scripts = [];
    preg_match_all('/<script[^>]*>(.*?)<\/script>/s', $content, $matches, PREG_OFFSET_CAPTURE);
    
    foreach ($matches[0] as $index => $match) {
        $scripts[] = [
            'full_match' => $match[0],
            'offset' => $match[1],
            'javascript' => $matches[1][$index][0],
            'line' => substr_count(substr($content, 0, $match[1]), "\n") + 1
        ];
    }
    
    return $scripts;
}

// Función para validar JavaScript
function validateJavaScript($js, $lineStart) {
    $errors = [];
    
    // Contar llaves
    $openBraces = substr_count($js, '{');
    $closeBraces = substr_count($js, '}');
    if ($openBraces !== $closeBraces) {
        $errors[] = "Línea ~{$lineStart}: Llaves desbalanceadas ({$openBraces} abiertas, {$closeBraces} cerradas)";
    }
    
    // Contar paréntesis
    $openParens = substr_count($js, '(');
    $closeParens = substr_count($js, ')');
    if ($openParens !== $closeParens) {
        $errors[] = "Línea ~{$lineStart}: Paréntesis desbalanceados ({$openParens} abiertos, {$closeParens} cerrados)";
    }
    
    // Buscar try sin catch
    if (preg_match('/try\s*{[^}]*}(?!\s*catch)/s', $js)) {
        $errors[] = "Línea ~{$lineStart}: try sin catch correspondiente";
    }
    
    // Buscar funciones sin cerrar
    if (preg_match('/function\s+\w+\([^)]*\)\s*{[^}]*$/s', $js)) {
        $errors[] = "Línea ~{$lineStart}: Función sin cerrar correctamente";
    }
    
    return $errors;
}

echo "🔍 Analizando bloques JavaScript en consultas-new.php...\n";

$scripts = findScriptBlocks($content);
echo "📄 Encontrados " . count($scripts) . " bloques <script>\n\n";

$totalErrors = 0;
foreach ($scripts as $index => $script) {
    echo "🔧 Bloque " . ($index + 1) . " (línea {$script['line']}):\n";
    
    $errors = validateJavaScript($script['javascript'], $script['line']);
    if (empty($errors)) {
        echo "  ✅ Sin errores detectados\n";
    } else {
        foreach ($errors as $error) {
            echo "  ❌ $error\n";
            $totalErrors++;
        }
    }
    echo "\n";
}

echo "📊 Total de errores encontrados: $totalErrors\n";

if ($totalErrors > 0) {
    echo "\n🛠️ Para corregir estos errores, revisa:\n";
    echo "1. Balancear llaves { }\n";
    echo "2. Balancear paréntesis ( )\n";
    echo "3. Agregar catch a todos los try\n";
    echo "4. Cerrar todas las funciones correctamente\n";
}
?>