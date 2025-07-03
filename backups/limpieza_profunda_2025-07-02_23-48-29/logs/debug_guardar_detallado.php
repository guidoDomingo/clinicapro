<?php
/**
 * Script mejorado de depuración para rastrear paso a paso el proceso de guardado de consultas
 * Este archivo es más detallado que debug_guardar.php y añade contexto para solucionar problemas.
 */

// Función mejorada para guardar logs de depuración con contexto adicional
function debug_detallado($etapa, $mensaje, $variables = [], $nivel = 'info') {
    $log_dir = __DIR__; // Directorio actual de logs
    $fecha = date('Y-m-d H:i:s');
    $ruta_log = "$log_dir/debug_consultas_detallado.log";
    
    // Definir colores para diferentes niveles de log (solo para legibilidad en el archivo)
    $prefijos = [
        'info' => '[INFO]',
        'warning' => '[ADVERTENCIA]',
        'error' => '[ERROR]',
        'success' => '[ÉXITO]',
        'debug' => '[DEBUG]'
    ];
    
    $prefijo = isset($prefijos[$nivel]) ? $prefijos[$nivel] : '[INFO]';
    
    // Capturar backtrace para saber desde dónde se llamó esta función
    $bt = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
    $caller = isset($bt[1]) ? basename($bt[1]['file']) . ':' . $bt[1]['line'] : 'desconocido';
    
    // Incluir información de la conexión PDO si está disponible
    $conexion_info = "";
    if (class_exists('PDO')) {
        $conexion_info = "\n[PDO] Extensión cargada";
        if (in_array('pdo_pgsql', get_loaded_extensions())) {
            $conexion_info .= "\n[PDO_PGSQL] Extensión cargada";
        } else {
            $conexion_info .= "\n[PDO_PGSQL] Extensión NO cargada";
        }
    }
    
    // Formatear variables para el log
    $vars_text = "";
    foreach ($variables as $nombre => $valor) {
        if (is_array($valor) || is_object($valor)) {
            // Para arrays y objetos, usamos print_r con formato más legible
            $vars_text .= "\n[$nombre] => " . print_r($valor, true);
        } else {
            $vars_text .= "\n[$nombre] => $valor";
        }
    }
    
    // Escribir en el log con formato y contexto
    $log_message = "[$fecha]$prefijo [$etapa] ($caller) $mensaje$conexion_info $vars_text\n" . 
                   "-----------------------------------------------------\n";
    
    // Asegurarse de que el directorio de logs existe
    if (!is_dir($log_dir)) {
        mkdir($log_dir, 0777, true);
    }
    
    // Escribir al archivo de log
    error_log($log_message, 3, $ruta_log);
    
    return true;
}

// Función para capturar y registrar la estructura de las tablas de BD
function debug_estructura_tabla($tabla) {
    if (!class_exists('Conexion')) {
        debug_detallado('ESTRUCTURA', "Clase Conexion no disponible", [], 'error');
        return false;
    }
    
    try {
        // Intentar conectar a la BD usando la clase Conexion
        $metodo = new ReflectionMethod('Conexion', 'conectar');
        $db = $metodo->invoke(null);
        
        if (!$db) {
            debug_detallado('ESTRUCTURA', "No se pudo conectar a la BD", [], 'error');
            return false;
        }
        
        // Consulta para obtener la estructura de la tabla
        $consulta = "
            SELECT column_name, data_type, character_maximum_length, is_nullable 
            FROM information_schema.columns 
            WHERE table_name = :tabla
            ORDER BY ordinal_position";
        
        $stmt = $db->prepare($consulta);
        $stmt->bindParam(':tabla', $tabla, PDO::PARAM_STR);
        $stmt->execute();
        
        $columnas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($columnas)) {
            debug_detallado('ESTRUCTURA', "No se encontraron columnas para la tabla $tabla", [], 'warning');
            return false;
        }
        
        debug_detallado('ESTRUCTURA', "Estructura de la tabla $tabla", ['columnas' => $columnas], 'info');
        return true;
        
    } catch (Exception $e) {
        debug_detallado('ESTRUCTURA', "Error al obtener estructura de tabla $tabla", ['error' => $e->getMessage()], 'error');
        return false;
    }
}
