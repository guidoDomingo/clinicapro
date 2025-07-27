<?php
// No cargar el modelo aquí, se carga en el archivo ajax que incluye este controlador

class ControllerConsulta {
    public static function ctrSetConsulta($datos) {
        // Verificar si existe el archivo de logs detallado
        $log_function_exists = function_exists('debug_detallado');
        
        // Si existe la función, usar el log detallado
        if ($log_function_exists) {
            debug_detallado('CONTROLLER', "Iniciando ctrSetConsulta en el controlador", [
                'datos_recibidos' => array_keys($datos)
            ], 'info');
        }
        
        // Validar datos mínimos necesarios
        if (!isset($datos['idPersona']) || empty($datos['idPersona'])) {
            $mensaje = "Error: No se ha proporcionado el ID de la persona";
            
            if ($log_function_exists) {
                debug_detallado('CONTROLLER', $mensaje, [], 'error');
            }
            
            return $mensaje;
        }
        
        // Llamar al modelo para guardar la consulta
        try {
            if ($log_function_exists) {
                debug_detallado('CONTROLLER', "Llamando al modelo mdlSetConsulta", [], 'info');
            }
            
            $resultado = ModelConsulta::mdlSetConsulta($datos);
            
            if ($log_function_exists) {
                debug_detallado('CONTROLLER', "Resultado del modelo", ['resultado' => $resultado], 'info');
            }
            
            return $resultado;
        } catch (Exception $e) {
            $mensaje = "Error en el controlador: " . $e->getMessage();
            
            if ($log_function_exists) {
                debug_detallado('CONTROLLER', $mensaje, [
                    'trace' => $e->getTraceAsString()
                ], 'error');
            }
            
            return $mensaje;
        }
    }

    public static function ctrEliminarConsulta($id) {
        return ModelConsulta::mdlEliminarConsulta($id);
    }
    
    public static function ctrGetAllConsultas() {
        return ModelConsulta::mdlGetAllConsultas();
    }
}
?>