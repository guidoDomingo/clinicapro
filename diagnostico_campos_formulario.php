<?php
/**
 * DIAGNÓSTICO DE CAMPOS DE FORMULARIOS
 * ===================================
 * Verifica que los IDs de campos coincidan entre HTML y FormComponents.js
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Configurar respuesta JSON
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

echo "<h1>🔍 DIAGNÓSTICO DE CAMPOS DE FORMULARIOS</h1>";

// Función para extraer IDs de campos del HTML
function extractFieldIds($content, $formType) {
    $pattern = '/id\s*=\s*["\']([^"\']*' . preg_quote($formType, '/') . '[^"\']*)["\'][^>]*(?:name\s*=\s*["\']txtmotivo["\']|placeholder\s*=\s*["\'][^"\']*[Mm]otivo[^"\']*["\'])/';
    preg_match_all($pattern, $content, $matches);
    return $matches[1];
}

// Leer el archivo de consultas-new.php
$consultasNewFile = __DIR__ . '/view/modules/consultas-new.php';
if (!file_exists($consultasNewFile)) {
    echo "❌ Archivo consultas-new.php no encontrado\n";
    exit;
}

$htmlContent = file_get_contents($consultasNewFile);

// Leer FormComponents.js
$formComponentsFile = __DIR__ . '/modules/consultas/core/FormComponents.js';
if (!file_exists($formComponentsFile)) {
    echo "❌ Archivo FormComponents.js no encontrado\n";
    exit;
}

$jsContent = file_get_contents($formComponentsFile);

echo "<h2>📋 ANÁLISIS DE CAMPOS TXTMOTIVO POR FORMULARIO</h2>";

// Tipos de formulario
$formTypes = ['general' => 'txtmotivo', 'anteojos' => 'txtmotivo-anteojos', 'estudios' => 'txtmotivo-estudios', 'informe-imagen' => 'txtmotivo-informe-imagen'];

foreach ($formTypes as $formType => $expectedId) {
    echo "<h3>🔍 Formulario: <strong>$formType</strong></h3>";
    
    // Buscar en HTML
    if ($formType === 'general') {
        $pattern = '/id\s*=\s*["\']txtmotivo["\'][^>]*(?!-)/';
    } else {
        $pattern = '/id\s*=\s*["\']' . preg_quote($expectedId, '/') . '["\'][^>]*/';
    }
    
    preg_match_all($pattern, $htmlContent, $htmlMatches);
    
    echo "HTML encontrado: ";
    if (!empty($htmlMatches[0])) {
        foreach ($htmlMatches[0] as $match) {
            echo "<div style='margin: 5px; padding: 5px; background: #e8f5e8;'><code>$match</code></div>";
        }
    } else {
        echo "<span style='color: red;'>❌ No encontrado</span>";
    }
    
    // Buscar en JavaScript
    $jsPattern = '/getBasicFields\(\)\s*\{[^}]*[\'"]' . preg_quote($expectedId, '/') . '[\'"][^}]*\}/s';
    preg_match($jsPattern, $jsContent, $jsMatches);
    
    echo "<br>JavaScript (getBasicFields): ";
    if (!empty($jsMatches)) {
        echo "<div style='margin: 5px; padding: 5px; background: #e8f5e8;'><code>" . htmlspecialchars($jsMatches[0]) . "</code></div>";
    } else {
        echo "<span style='color: red;'>❌ No encontrado</span>";
    }
    
    echo "<hr>";
}

echo "<h2>🧪 SIMULACIÓN DE BÚSQUEDA DE CAMPOS</h2>";

// Simular lo que hace FormComponents.js
$testFields = [
    'general' => ['txtmotivo', 'visionod', 'visionoi'],
    'anteojos' => ['txtmotivo-anteojos', 'visionod', 'visionoi'],
    'estudios' => ['txtmotivo-estudios', 'visionod', 'visionoi'],
    'informe_imagen' => ['txtmotivo-informe-imagen', 'visionod', 'visionoi']
];

foreach ($testFields as $formType => $fields) {
    echo "<h3>🎯 Prueba para formulario: <strong>$formType</strong></h3>";
    
    foreach ($fields as $fieldId) {
        $found = false;
        
        // Buscar exactamente como lo hace el JavaScript
        $searchPatterns = [
            '/id\s*=\s*["\']' . preg_quote($fieldId, '/') . '["\']/',
            '/name\s*=\s*["\']' . preg_quote($fieldId, '/') . '["\']/'
        ];
        
        foreach ($searchPatterns as $pattern) {
            if (preg_match($pattern, $htmlContent)) {
                $found = true;
                break;
            }
        }
        
        if ($found) {
            echo "✅ Campo <strong>$fieldId</strong> encontrado<br>";
        } else {
            echo "❌ Campo <strong>$fieldId</strong> NO encontrado<br>";
        }
    }
    
    echo "<hr>";
}

echo "<h2>✅ DIAGNÓSTICO COMPLETADO</h2>";
echo "<p>Revisa los resultados arriba para identificar discrepancias entre HTML y JavaScript.</p>";
?>