<?php
/**
 * DIAGNÓSTICO DE CONTENEDORES HTML
 * 
 * Verifica que todos los contenedores necesarios estén presentes
 * en la página de consultas para el correcto funcionamiento del historial y timeline
 */

echo "<!DOCTYPE html>\n<html lang='es'>\n<head>\n";
echo "<meta charset='UTF-8'>\n<title>Diagnóstico de Contenedores</title>\n";
echo "<style>body{font-family:Arial,sans-serif;padding:20px;background:#f4f4f4} .container{background:white;padding:20px;border-radius:8px;box-shadow:0 2px 10px rgba(0,0,0,0.1);margin-bottom:20px} .ok{color:green;font-weight:bold} .error{color:red;font-weight:bold} .info{color:blue} pre{background:#f8f8f8;padding:10px;border-radius:4px;overflow-x:auto}</style>\n";
echo "</head>\n<body>\n";

echo "<h1>🔍 Diagnóstico de Contenedores HTML</h1>\n";

// Leer el archivo de consultas-new.php
$archivo = 'view/modules/consultas-new.php';
if (!file_exists($archivo)) {
    echo "<div class='container'><p class='error'>❌ Archivo no encontrado: $archivo</p></div>\n";
    echo "</body></html>";
    exit;
}

$contenido = file_get_contents($archivo);

echo "<div class='container'>\n";
echo "<h2>📁 Análisis del Archivo: $archivo</h2>\n";
echo "<p class='info'>Verificando elementos necesarios para el historial, timeline y archivos...</p>\n";

// Lista de elementos a verificar
$elementos = [
    'tabla-consultas' => 'Tabla principal de historial',
    'timeline-container' => 'Contenedor principal del timeline', 
    'timeline' => 'Área de contenido del timeline',
    'archivos-container' => 'Contenedor de archivos del paciente',
    'txtCantConsulta' => 'Campo contador de consultas',
    'cuota-valor' => 'Campo valor de cuota MB'
];

echo "<h3>🎯 Elementos Requeridos:</h3>\n<ul>\n";

foreach ($elementos as $id => $descripcion) {
    if (strpos($contenido, "id=\"$id\"") !== false) {
        echo "<li class='ok'>✅ $descripcion (id=\"$id\") - ENCONTRADO</li>\n";
    } else {
        echo "<li class='error'>❌ $descripcion (id=\"$id\") - NO ENCONTRADO</li>\n";
    }
}
echo "</ul>\n</div>\n";

// Verificar JavaScript
echo "<div class='container'>\n";
echo "<h2>📜 Verificación de JavaScript</h2>\n";

$js_file = 'simple_search.js';
if (file_exists($js_file)) {
    echo "<p class='ok'>✅ Archivo JavaScript encontrado: $js_file</p>\n";
    
    $js_content = file_get_contents($js_file);
    
    // Buscar funciones críticas
    $funciones = [
        'actualizarHistorial' => 'Función para actualizar historial',
        'actualizarTimeline' => 'Función para actualizar timeline',
        'actualizarArchivos' => 'Función para actualizar archivos',
        'buscarPacienteSimple' => 'Función de búsqueda principal'
    ];
    
    echo "<h3>🔧 Funciones JavaScript:</h3>\n<ul>\n";
    foreach ($funciones as $func => $desc) {
        if (strpos($js_content, "function $func") !== false || strpos($js_content, "$func(") !== false) {
            echo "<li class='ok'>✅ $desc - ENCONTRADA</li>\n";
        } else {
            echo "<li class='error'>❌ $desc - NO ENCONTRADA</li>\n";
        }
    }
    echo "</ul>\n";
} else {
    echo "<p class='error'>❌ Archivo JavaScript no encontrado: $js_file</p>\n";
}
echo "</div>\n";

// Test JavaScript inline para debuggear
echo "<div class='container'>\n";
echo "<h2>🧪 Test en Vivo</h2>\n";
echo "<p>Este script intentará simular la búsqueda para verificar la funcionalidad:</p>\n";
echo "<button id='testBtn' onclick='testearSistema()' style='background:#007bff;color:white;padding:10px 20px;border:none;border-radius:4px;cursor:pointer'>🚀 Probar Sistema</button>\n";
echo "<div id='testResult' style='margin-top:15px;padding:10px;background:#f8f9fa;border-radius:4px;display:none'></div>\n";

echo "<script>\n";
echo "function testearSistema() {\n";
echo "    const resultDiv = document.getElementById('testResult');\n";
echo "    resultDiv.style.display = 'block';\n";
echo "    let resultado = '<h4>🔍 Resultados del Test:</h4>';\n";
echo "    \n";
echo "    // Verificar que existan los elementos\n";
echo "    const elementos = {\n";
echo "        'tabla-consultas': 'Tabla de historial',\n";
echo "        'timeline-container': 'Contenedor timeline',\n";
echo "        'timeline': 'Timeline',\n";
echo "        'archivos-container': 'Container archivos',\n";
echo "        'txtCantConsulta': 'Contador consultas',\n";
echo "        'cuota-valor': 'Valor cuota'\n";
echo "    };\n";
echo "    \n";
echo "    let todosEncontrados = true;\n";
echo "    for (const [id, desc] of Object.entries(elementos)) {\n";
echo "        const elemento = document.getElementById(id);\n";
echo "        if (elemento) {\n";
echo "            resultado += '<p style=\"color:green\">✅ ' + desc + ' (encontrado)</p>';\n";
echo "        } else {\n";
echo "            resultado += '<p style=\"color:red\">❌ ' + desc + ' (NO encontrado)</p>';\n";
echo "            todosEncontrados = false;\n";
echo "        }\n";
echo "    }\n";
echo "    \n";
echo "    if (todosEncontrados) {\n";
echo "        resultado += '<div style=\"background:#d4edda;border:1px solid #c3e6cb;color:#155724;padding:10px;border-radius:4px;margin-top:10px\"><strong>🎉 ¡Todos los elementos están presentes!</strong><br>El sistema debería funcionar correctamente.</div>';\n";
echo "    } else {\n";
echo "        resultado += '<div style=\"background:#f8d7da;border:1px solid #f5c6cb;color:#721c24;padding:10px;border-radius:4px;margin-top:10px\"><strong>⚠️ Faltan elementos críticos</strong><br>El sistema no funcionará hasta corregir estos problemas.</div>';\n";
echo "    }\n";
echo "    \n";
echo "    resultDiv.innerHTML = resultado;\n";
echo "}\n";
echo "</script>\n";
echo "</div>\n";

// URL del módulo
echo "<div class='container'>\n";
echo "<h2>🔗 Enlaces Rápidos</h2>\n";
echo "<ul>\n";
echo "<li><a href='view/modules/consultas-new.php' target='_blank'>🏥 Ir al Módulo de Consultas</a></li>\n";
echo "<li><a href='patient_history_api.php?action=buscar&query=alejandro' target='_blank'>📊 Test API - Búsqueda</a></li>\n";
echo "<li><a href='verificacion_final.php' target='_blank'>✅ Verificación Completa</a></li>\n";
echo "</ul>\n";
echo "</div>\n";

echo "<br><hr><p><em>Diagnóstico realizado: " . date('Y-m-d H:i:s') . "</em></p>\n";
echo "</body></html>";
?>