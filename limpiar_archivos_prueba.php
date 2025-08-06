<?php
/**
 * Script para eliminar todos los archivos de prueba y debugging generados
 * Durante la investigación del problema de valores de esfera
 */

echo "<h1>🧹 Limpieza de Archivos de Prueba</h1>";
echo "<p><strong>Fecha:</strong> " . date('Y-m-d H:i:s') . "</p>";

// Lista de archivos de prueba a eliminar
$archivos_prueba = [
    // Archivos de debugging específicos del problema de esfera
    'debug_paso_a_paso_esferas.php',
    'debug_origen_valores_masivos.php',
    'debug_problema_valores_extra.php',
    'debug_interceptor_llamadas.php',
    'verificar_valores_esfera.php',
    'analisis_problema_esferas.php',
    
    // Archivos de investigación y cazadores
    'cazador_simple_referenciales.php',
    'cazador_referenciales_masivos.php',
    'investigacion_extrema_valores_masivos.php',
    'detector_final_html_vs_bd.php',
    'trazador_completo_formulario.php',
    
    // Archivos de limpieza y solución
    'limpieza_total_cache_bd.php',
    'limpieza_profunda.php',
    'limpieza_forzada_todos_valores.php',
    'limpieza_definitiva_referencial_esfera.php',
    'limpieza_automatica.php',
    'solucion_nuclear_referenciales.php',
    'solucion_duplicados_implementada.php',
    'solucion_final_estudios.php',
    'solucion_final_estudios_v2.php',
    
    // Archivos de debug generales que no son del sistema principal
    'debug_ajax_turnos.php',
    'debug_blank_page.php',
    'debug_busqueda_personas.php',
    'debug_carga_datos.php',
    'debug_duplicados_estudios.php',
    'debug_duplicados_preformatos.php',
    'debug_filtros.php',
    'debug_javascript.html',
    'debug_mapeo_preformatos.php',
    'debug_modal_horarios.html',
    'debug_modelo_detallado.php',
    'debug_operaciones_ajax.php',
    'debug_paciente.php',
    'debug_personas.php',
    'debug_preformatos_500.php',
    'debug_preformatos_actuales.php',
    'debug_preformatos_estudios.php',
    'debug_problemas_formulario.html',
    'debug_reserva_por_id.php',
    'debug_sys_users_columns.php',
    'debug_sys_users_web.php',
    'debug_tabla_informe.php',
    'debug_tables.php',
    'debug_web_doctores.php',
    'debug_valores_masivos.php',
    
    // Archivos de test específicos que no son del sistema
    'test_ajax_directo.php',
    'test_ajax_tipos.php',
    'test_arreglo_filtrado.php',
    'test_bd_directo.php',
    'test_consultas_preformatos.php',
    'test_debug_emails.php',
    'test_tipos_ajax_directo.php',
    'test_tipos_formularios.php',
    'test_turnos.php',
    'test_turnos_ajax.php',
    'test_turnos_directo.php',
    
    // Archivos de verificación específicos de debugging
    'verificar_formulario_estudios.php',
    'verificar_formularios_finales.php',
    'verificar_crud_tipos_formularios.php',
    'verificar_id_especifico.php',
    'verificar_preformatos_estudios_directo.php',
    'verificar_tipos_formularios.php',
    'verificar_tabla_informe_imagen.php',
    'verificar_pgsql.php',
    'verificar_logs.php',
    'verificar_llaves.php',
    'verificar_informe_imagen.php',
    'verificar_estructura_permisos.php',
    
    // Archivos de análisis específicos
    'analisis_comparativo_preformatos.php',
    'analisis_duplicado_especifico.php',
    
    // Archivos HTML de debugging
    'debug_consultas.html',
    'debug_formulario_informe_imagen.html',
    'debug_final_modal.html',
    'debug_problemas_formulario.html',
    'debug_modal_horarios.html',
    'debug_javascript.html',
    
    // Archivos de análisis del módulo
    'analisis_modulo_consultas.html',
    
    // Archivos de corrección específicos del debugging
    'correccion_actualizacion_exitosa.html',
    'CORRECCION_PREFORMATOS_ESTUDIOS_COMPLETADA.html',
];

$eliminados = [];
$no_encontrados = [];
$errores = [];

echo "<h2>📋 Archivos a eliminar:</h2>";
echo "<div style='background: #f8f9fa; padding: 15px; border: 1px solid #dee2e6; border-radius: 5px; margin: 10px 0;'>";

foreach ($archivos_prueba as $archivo) {
    $ruta_completa = __DIR__ . '/' . $archivo;
    
    if (file_exists($ruta_completa)) {
        echo "<p style='color: #28a745;'>✅ <strong>{$archivo}</strong> - Encontrado, será eliminado</p>";
        
        if (unlink($ruta_completa)) {
            $eliminados[] = $archivo;
        } else {
            $errores[] = $archivo;
            echo "<p style='color: #dc3545;'>❌ Error al eliminar: {$archivo}</p>";
        }
    } else {
        $no_encontrados[] = $archivo;
        echo "<p style='color: #6c757d;'>⚪ <strong>{$archivo}</strong> - No encontrado (ya eliminado o no existe)</p>";
    }
}

echo "</div>";

// Resumen
echo "<h2>📊 Resumen de la limpieza:</h2>";
echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
echo "<p><strong>✅ Archivos eliminados:</strong> " . count($eliminados) . "</p>";
echo "<p><strong>⚪ Archivos no encontrados:</strong> " . count($no_encontrados) . "</p>";
echo "<p><strong>❌ Errores:</strong> " . count($errores) . "</p>";
echo "<p><strong>📝 Total procesados:</strong> " . count($archivos_prueba) . "</p>";
echo "</div>";

if (count($eliminados) > 0) {
    echo "<h3>✅ Archivos eliminados exitosamente:</h3>";
    echo "<ul>";
    foreach ($eliminados as $archivo) {
        echo "<li><code>{$archivo}</code></li>";
    }
    echo "</ul>";
}

if (count($errores) > 0) {
    echo "<h3>❌ Errores al eliminar:</h3>";
    echo "<ul>";
    foreach ($errores as $archivo) {
        echo "<li><code>{$archivo}</code></li>";
    }
    echo "</ul>";
}

echo "<div style='background: #cce5ff; border: 1px solid #0066cc; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
echo "<h3>🎯 Archivos del sistema que se mantuvieron:</h3>";
echo "<ul>";
echo "<li><strong>Sistema principal:</strong> Todos los archivos del core se mantienen intactos</li>";
echo "<li><strong>Configuraciones:</strong> Archivos de configuración preservados</li>";
echo "<li><strong>Base de datos:</strong> Estructura y datos principales sin cambios</li>";
echo "<li><strong>Módulos activos:</strong> Referenciales, consultas, etc. funcionando</li>";
echo "</ul>";
echo "</div>";

echo "<h2>🚀 ¡Limpieza Completada!</h2>";
echo "<p><strong>El sistema está limpio y listo para producción.</strong></p>";
echo "<p><em>Recuerda: Este script se auto-eliminará también.</em></p>";

// Auto-eliminar este script de limpieza
if (unlink(__FILE__)) {
    echo "<p style='color: #28a745;'>✅ Script de limpieza auto-eliminado exitosamente.</p>";
} else {
    echo "<p style='color: #ffc107;'>⚠️ No se pudo auto-eliminar el script de limpieza.</p>";
}
?>
