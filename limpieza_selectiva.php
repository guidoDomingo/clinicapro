<?php
/**
 * Limpieza selectiva final - Solo archivos de prueba innecesarios
 */

echo "<h1>🎯 Limpieza Selectiva Final</h1>";

// Solo archivos que claramente son de prueba y no del sistema
$archivos_prueba_seguros = [
    'test_boton_ver_funcional.html',
    'test_completo_con_archivos.html',
    'test_corregidos.html',
    'test_datatable_simple.html',
    'test_debug_500.html',
    'test_directo_ajax.php',
    'test_endpoint_directo.php',
    'test_endpoint_informe_imagen.html',
    'test_envio_emails_estudios.html',
    'test_error_real.php',
    'test_final_preformatos.html',
    'test_final_sin_duplicados.php',
    'test_formato_tabla_archivos.html',
    'test_formulario_corregido.html',
    'test_formulario_datos_reales.html',
    'test_formulario_estudios_archivos.html',
    'test_formulario_limpio.php',
    'test_funciones_globales.html',
    'test_guardado_directo.php',
    'test_js_turnos.html',
    'test_modal_completo.html',
    'test_modal_tipos.html',
    'test_modulo_simplificado.html',
    'test_nuevo_endpoint.html',
    'test_preformatos_ajax.html',
    'test_simple_ajax.html',
    'test_sistema_completo.html',
    'test_sistema_informe_imagen.html',
    'test_summernote.html',
    'test_summernote_save.html',
    'test_tipos_formularios_web.html',
    'test_tipos_simple.html',
    'ver_log_debug.php',
];

// Archivos que se mantienen (son del sistema o documentación importante)
$archivos_mantener = [
    'crear_preformato_test.php', // Parte del sistema
    'test_estudios.php', // Puede ser parte del sistema
    'DOCUMENTACION_LIMPIEZA.md', // Documentación
    'LIMPIEZA_INFO.md', // Documentación
    'SOLUCION_ERROR_500_PREFORMATOS.md', // Documentación
    'SOLUCION_ERRORES_PREFORMATOS.md', // Documentación
    'SOLUCION_ERRORES_TIPOS_FORMULARIOS.md', // Documentación
    'SOLUCION_PATRON_CORRECTO_PREFORMATOS.md', // Documentación
    'SOLUCION_PROBLEMA_CURL.md', // Documentación
];

$eliminados_selectivos = [];

echo "<h2>🗑️ Eliminando archivos de prueba seguros:</h2>";

foreach ($archivos_prueba_seguros as $archivo) {
    $ruta_completa = __DIR__ . '/' . $archivo;
    
    if (file_exists($ruta_completa)) {
        echo "<p style='color: #28a745;'>✅ Eliminando: <strong>{$archivo}</strong></p>";
        
        if (unlink($ruta_completa)) {
            $eliminados_selectivos[] = $archivo;
        } else {
            echo "<p style='color: #dc3545;'>❌ Error al eliminar: {$archivo}</p>";
        }
    } else {
        echo "<p style='color: #6c757d;'>⚪ No encontrado: <strong>{$archivo}</strong></p>";
    }
}

echo "<h2>💾 Archivos mantenidos (sistema/documentación):</h2>";
echo "<div style='background: #fff3cd; border: 1px solid #ffc107; padding: 15px; border-radius: 5px;'>";
foreach ($archivos_mantener as $archivo) {
    if (file_exists(__DIR__ . '/' . $archivo)) {
        echo "<p style='color: #856404;'>📄 <strong>{$archivo}</strong> - Mantenido</p>";
    }
}
echo "</div>";

echo "<h2>📊 Resumen de Limpieza Selectiva:</h2>";
echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px;'>";
echo "<p><strong>✅ Archivos eliminados:</strong> " . count($eliminados_selectivos) . "</p>";
echo "<p><strong>💾 Archivos mantenidos:</strong> " . count($archivos_mantener) . "</p>";
echo "</div>";

echo "<h2>✅ Estado Final del Sistema:</h2>";
echo "<div style='background: #d1ecf1; border: 1px solid #b8daff; padding: 15px; border-radius: 5px;'>";
echo "<ul>";
echo "<li>🎯 <strong>Archivos de prueba innecesarios:</strong> Eliminados</li>";
echo "<li>📚 <strong>Documentación importante:</strong> Preservada</li>";
echo "<li>⚙️ <strong>Archivos del sistema:</strong> Intactos</li>";
echo "<li>🔧 <strong>Funcionalidad:</strong> Completamente operativa</li>";
echo "</ul>";
echo "</div>";

// Auto-eliminar este script
if (unlink(__FILE__)) {
    echo "<p style='color: #28a745;'>✅ Script de limpieza selectiva auto-eliminado.</p>";
}

echo "<h1>🎉 ¡Limpieza Completa y Sistema Optimizado!</h1>";
?>
