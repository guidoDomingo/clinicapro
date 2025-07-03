<?php
/**
 * Archivo de funciones para depuración de operaciones de guardado
 * Creado automáticamente el 2 de julio de 2025
 */

if (!function_exists('debug_log')) {
    /**
     * Función para registrar información de depuración
     *
     * @param string $mensaje El mensaje a registrar
     * @param array $datos Datos adicionales para incluir en el log
     * @param string $prefijo Prefijo para el mensaje
     * @return void
     */
    function debug_log($mensaje, $datos = [], $prefijo = "[DEBUG]") {
        $log_dir = __DIR__;
        $fecha = date('Y-m-d H:i:s');
        $log_mensaje = "$fecha $prefijo $mensaje";
        
        if (!empty($datos)) {
            // Limitar la profundidad de los datos para evitar logs demasiado extensos
            $datos_limitados = array_map(function($item) {
                if (is_array($item) && count($item) > 10) {
                    return array_slice($item, 0, 10) + ['...' => '(datos truncados)'];
                }
                return $item;
            }, $datos);
            
            $log_mensaje .= " - " . json_encode($datos_limitados, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }
        
        error_log($log_mensaje . PHP_EOL, 3, "$log_dir/debug_consultas.log");
    }
}

if (!function_exists('debug_estructura_tabla')) {
    /**
     * Función para registrar la estructura de una tabla para depuración
     *
     * @param string $tabla Nombre de la tabla a verificar
     * @return void
     */
    function debug_estructura_tabla($tabla) {
        try {
            // Verificar si existe el archivo de conexión e importarlo si es necesario
            if (!class_exists('Conexion')) {
                if (file_exists(__DIR__ . '/../model/conexion.php')) {
                    require_once __DIR__ . '/../model/conexion.php';
                    debug_log("Archivo de conexión cargado correctamente", [], "[ESTRUCTURA]");
                } else {
                    debug_log("No se encontró el archivo de conexión", ['ruta_buscada' => __DIR__ . '/../model/conexion.php'], "[ERROR]");
                    return;
                }
            }
            
            // Utilizar el método estático para conectar
            $conexion = Conexion::conectar();
            
            if ($conexion) {
                // Para PostgreSQL, usamos información_schema en lugar de DESCRIBE
                $stmt = $conexion->prepare("
                    SELECT column_name, data_type, character_maximum_length, is_nullable
                    FROM information_schema.columns 
                    WHERE table_name = :tabla
                ");
                $stmt->bindParam(':tabla', $tabla, PDO::PARAM_STR);
                $stmt->execute();
                $estructura = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                if (count($estructura) > 0) {
                    debug_log("Estructura de tabla $tabla verificada", ['columnas' => array_column($estructura, 'column_name')], "[ESTRUCTURA]");
                } else {
                    debug_log("La tabla $tabla no existe o no tiene columnas", [], "[ADVERTENCIA]");
                }
            } else {
                debug_log("No se pudo establecer conexión a la base de datos para verificar tabla $tabla", [], "[ERROR]");
            }
        } catch (Exception $e) {
            debug_log("Error al verificar estructura de tabla $tabla", ['error' => $e->getMessage()], "[ERROR]");
        }
    }
}
?>
