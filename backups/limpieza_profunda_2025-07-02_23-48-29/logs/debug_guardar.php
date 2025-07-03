<?php
/**
 * Script de depuración para registrar variables del entorno
 * Al incluir este archivo en puntos críticos del proceso,
 * nos permite diagnosticar problemas.
 */

// Función para guardar logs de depuración
function debug_log($mensaje, $variables = [], $prefijo = "") {
    $log_dir = __DIR__; // Directorio actual de logs
    $fecha = date('Y-m-d H:i:s');
    
    // Formatear variables para el log
    $vars_text = "";
    foreach ($variables as $nombre => $valor) {
        if (is_array($valor) || is_object($valor)) {
            $vars_text .= "\n[$nombre] => " . print_r($valor, true);
        } else {
            $vars_text .= "\n[$nombre] => $valor";
        }
    }
    
    // Escribir en el log
    $log_message = "[$fecha]$prefijo $mensaje $vars_text\n";
    error_log($log_message, 3, "$log_dir/debug_consultas.log");
    
    return true;
}
