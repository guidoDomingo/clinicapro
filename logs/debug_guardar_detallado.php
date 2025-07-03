<?php
/**
 * Archivo de funciones para depuración detallada de operaciones de guardado
 * Creado automáticamente el 2 de julio de 2025
 */

if (!function_exists('debug_detallado')) {
    /**
     * Función para registrar información detallada de depuración
     *
     * @param string $seccion La sección o componente que genera el mensaje
     * @param string $mensaje El mensaje a registrar
     * @param array $datos Datos adicionales para incluir en el log
     * @param string $nivel Nivel de log (info, debug, warning, error, success)
     * @return void
     */
    function debug_detallado($seccion, $mensaje, $datos = [], $nivel = 'debug') {
        $log_dir = __DIR__;
        $fecha = date('Y-m-d H:i:s');
        
        // Preparar el prefijo según el nivel
        $prefijo = match($nivel) {
            'info' => '[INFO]',
            'warning' => '[ADVERTENCIA]',
            'error' => '[ERROR]',
            'success' => '[EXITO]',
            default => '[DEBUG]'
        };
        
        // Preparar mensaje
        $log_mensaje = "$fecha $prefijo [$seccion] $mensaje";
        
        if (!empty($datos)) {
            // Limitar profundidad y tamaño de datos
            $datos_seguros = array();
            foreach ($datos as $key => $value) {
                if (is_array($value)) {
                    // Limitar arrays muy grandes
                    if (count($value) > 20) {
                        $datos_seguros[$key] = array_slice($value, 0, 20) + ['...' => '(datos truncados)'];
                    } else {
                        $datos_seguros[$key] = $value;
                    }
                } else if (is_string($value) && strlen($value) > 500) {
                    // Truncar strings muy largos
                    $datos_seguros[$key] = substr($value, 0, 500) . '... (truncado)';
                } else {
                    $datos_seguros[$key] = $value;
                }
            }
            
            $log_mensaje .= " - " . json_encode($datos_seguros, 
                JSON_UNESCAPED_UNICODE | 
                JSON_UNESCAPED_SLASHES | 
                JSON_PARTIAL_OUTPUT_ON_ERROR
            );
        }
        
        error_log($log_mensaje . PHP_EOL, 3, "$log_dir/debug_consultas_detallado.log");
        
        // Para errores críticos, también registrar en el log general
        if ($nivel === 'error') {
            error_log("$fecha ERROR en $seccion: $mensaje", 3, "$log_dir/application.log");
        }
    }
}
?>
