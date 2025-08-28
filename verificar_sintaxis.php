<?php
/**
 * VERIFICACIÓN DE SINTAXIS PHP
 * 
 * Script para verificar si consultas-new.php tiene errores de sintaxis
 */

echo "<!DOCTYPE html>\n<html>\n<head>\n<meta charset='UTF-8'>\n<title>Verificación de Sintaxis</title>\n</head>\n<body>\n";
echo "<h1>🔍 Verificación de Sintaxis PHP</h1>\n";

$archivo = 'view/modules/consultas-new.php';

if (!file_exists($archivo)) {
    echo "<p style='color:red'>❌ Archivo no encontrado: $archivo</p>\n";
    echo "</body></html>";
    exit;
}

// Verificar sintaxis PHP
$output = [];
$return_code = 0;

exec("php -l \"$archivo\"", $output, $return_code);

echo "<h2>📋 Resultado de la Verificación:</h2>\n";

if ($return_code === 0) {
    echo "<div style='background:#d4edda;color:#155724;padding:15px;border-radius:8px;border:1px solid #c3e6cb'>\n";
    echo "<h3>✅ Sintaxis PHP Correcta</h3>\n";
    echo "<p>El archivo <strong>$archivo</strong> no tiene errores de sintaxis PHP.</p>\n";
    echo "<p><strong>Resultado del comando php -l:</strong></p>\n";
    echo "<pre>" . implode("\n", $output) . "</pre>\n";
    echo "</div>\n";
} else {
    echo "<div style='background:#f8d7da;color:#721c24;padding:15px;border-radius:8px;border:1px solid #f5c6cb'>\n";
    echo "<h3>❌ Errores de Sintaxis Encontrados</h3>\n";
    echo "<p>El archivo <strong>$archivo</strong> tiene errores de sintaxis:</p>\n";
    echo "<pre>" . implode("\n", $output) . "</pre>\n";
    echo "</div>\n";
}

echo "<hr>\n";

// Información adicional
$filesize = filesize($archivo);
$lines = count(file($archivo));

echo "<h2>📊 Información del Archivo:</h2>\n";
echo "<ul>\n";
echo "<li><strong>Tamaño:</strong> " . number_format($filesize / 1024, 2) . " KB</li>\n";
echo "<li><strong>Líneas:</strong> $lines</li>\n";
echo "<li><strong>Última modificación:</strong> " . date('Y-m-d H:i:s', filemtime($archivo)) . "</li>\n";
echo "</ul>\n";

// Test de carga básica
echo "<h2>🧪 Test de Carga Básica:</h2>\n";
try {
    // Intentar incluir el archivo sin ejecutar (usando output buffering)
    ob_start();
    $included = @include $archivo;
    $content = ob_get_contents();
    ob_end_clean();
    
    if ($included !== false) {
        echo "<p style='color:green'>✅ El archivo se puede incluir sin errores fatales</p>\n";
        echo "<p><strong>Tamaño del contenido generado:</strong> " . strlen($content) . " bytes</p>\n";
    } else {
        echo "<p style='color:orange'>⚠️ El archivo se incluyó pero retornó false</p>\n";
    }
} catch (ParseError $e) {
    echo "<p style='color:red'>❌ Error de sintaxis al incluir: " . $e->getMessage() . "</p>\n";
} catch (Exception $e) {
    echo "<p style='color:red'>❌ Error al incluir: " . $e->getMessage() . "</p>\n";
}

echo "<hr>\n";

// Verificar estructura de llaves/paréntesis básica
echo "<h2>🔧 Análisis de Estructura:</h2>\n";
$contenido = file_get_contents($archivo);

// Contar llaves
$abre_llaves = substr_count($contenido, '{');
$cierra_llaves = substr_count($contenido, '}');

// Contar paréntesis
$abre_parentesis = substr_count($contenido, '(');
$cierra_parentesis = substr_count($contenido, ')');

// Contar corchetes
$abre_corchetes = substr_count($contenido, '[');
$cierra_corchetes = substr_count($contenido, ']');

echo "<table border='1' style='border-collapse:collapse;'>\n";
echo "<tr><th>Símbolo</th><th>Abre</th><th>Cierra</th><th>Balance</th></tr>\n";
echo "<tr><td>Llaves {}</td><td>$abre_llaves</td><td>$cierra_llaves</td><td style='color:" . ($abre_llaves == $cierra_llaves ? "green'>✅ Balanceado" : "red'>❌ Desbalanceado") . "</td></tr>\n";
echo "<tr><td>Paréntesis ()</td><td>$abre_parentesis</td><td>$cierra_parentesis</td><td style='color:" . ($abre_parentesis == $cierra_parentesis ? "green'>✅ Balanceado" : "red'>❌ Desbalanceado") . "</td></tr>\n";
echo "<tr><td>Corchetes []</td><td>$abre_corchetes</td><td>$cierra_corchetes</td><td style='color:" . ($abre_corchetes == $cierra_corchetes ? "green'>✅ Balanceado" : "red'>❌ Desbalanceado") . "</td></tr>\n";
echo "</table>\n";

echo "<hr>\n";

// Buscar patrones problemáticos
echo "<h2>🚨 Búsqueda de Patrones Problemáticos:</h2>\n";

$problemas = [];

// Buscar wire: attributes mal formateados
if (preg_match_all('/wire:[a-zA-Z]+="[^"]*\'[^"]*"/', $contenido, $matches)) {
    $problemas[] = "Atributos wire: con comillas mal anidadas: " . count($matches[0]) . " encontrados";
}

// Buscar scripts sin cerrar
if (preg_match_all('/<script[^>]*>(?![^<]*<\/script>)/i', $contenido, $matches)) {
    $problemas[] = "Posibles tags <script> sin cerrar: " . count($matches[0]);
}

// Buscar PHP sin cerrar
if (preg_match_all('/<\?php(?![^<]*\?>)/', $contenido, $matches)) {
    $problemas[] = "Posibles tags <?php sin cerrar: " . count($matches[0]);
}

if (empty($problemas)) {
    echo "<p style='color:green'>✅ No se encontraron patrones problemáticos conocidos</p>\n";
} else {
    echo "<ul>\n";
    foreach ($problemas as $problema) {
        echo "<li style='color:red'>❌ $problema</li>\n";
    }
    echo "</ul>\n";
}

echo "<hr>\n";
echo "<h2>✅ Resumen:</h2>\n";

if ($return_code === 0 && $abre_llaves == $cierra_llaves && $abre_parentesis == $cierra_parentesis) {
    echo "<div style='background:#d4edda;color:#155724;padding:15px;border-radius:8px;border:1px solid #c3e6cb'>\n";
    echo "<h3>🎉 Todo Parece Estar Bien</h3>\n";
    echo "<p>El archivo <strong>$archivo</strong> parece estar sintácticamente correcto. El error reportado por el editor puede ser un falso positivo.</p>\n";
    echo "</div>\n";
} else {
    echo "<div style='background:#fff3cd;color:#856404;padding:15px;border-radius:8px;border:1px solid #ffeaa7'>\n";
    echo "<h3>⚠️ Posibles Problemas Detectados</h3>\n";
    echo "<p>Se encontraron algunos problemas que podrían necesitar atención.</p>\n";
    echo "</div>\n";
}

echo "<p><em>Verificación realizada: " . date('Y-m-d H:i:s') . "</em></p>\n";
echo "</body></html>";
?>