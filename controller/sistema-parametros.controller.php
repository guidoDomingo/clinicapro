<?php
/**
 * Controlador para Sistema de Parámetros
 * Maneja la lógica de negocio para los parámetros del sistema
 */
class ControllerSistemaParametros {
    
    /**
     * Obtener todos los parámetros
     * @param bool $soloActivos
     * @return array
     */
    static public function ctrObtenerTodosParametros($soloActivos = true) {
        try {
            $parametros = ModelSistemaParametros::mdlObtenerTodosParametros($soloActivos);
            
            // Formatear datos para la vista
            foreach ($parametros as &$parametro) {
                $parametro['estado_texto'] = $parametro['is_active'] ? 'Activo' : 'Inactivo';
                $parametro['fecha_creacion'] = date('d/m/Y H:i', strtotime($parametro['created_at']));
                $parametro['fecha_actualizacion'] = $parametro['updated_at'] ? 
                    date('d/m/Y H:i', strtotime($parametro['updated_at'])) : '-';
            }
            
            return $parametros;
            
        } catch (Exception $e) {
            error_log("Error en ctrObtenerTodosParametros: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtener un parámetro por ID
     * @param int $id
     * @return array|false
     */
    static public function ctrObtenerParametroPorId($id) {
        if (empty($id) || !is_numeric($id)) {
            return false;
        }
        
        return ModelSistemaParametros::mdlObtenerParametroPorId($id);
    }
    
    /**
     * Obtener valor de un parámetro por código
     * @param string $codigo
     * @return string|false
     */
    static public function ctrObtenerValorParametro($codigo) {
        if (empty($codigo)) {
            return false;
        }
        
        $parametro = ModelSistemaParametros::mdlObtenerParametroPorCodigo($codigo);
        return $parametro ? $parametro['parametro_valor'] : false;
    }
    
    /**
     * Crear nuevo parámetro
     * @param array $datos
     * @return array
     */
    static public function ctrCrearParametro($datos) {
        $respuesta = ['success' => false, 'message' => '', 'data' => null];
        
        try {
            // Validaciones
            $validacion = self::validarDatosParametro($datos);
            if (!$validacion['valido']) {
                $respuesta['message'] = $validacion['mensaje'];
                return $respuesta;
            }
            
            // Limpiar y preparar datos
            $datosLimpios = self::limpiarDatosParametro($datos);
            
            // Crear parámetro
            $nuevoId = ModelSistemaParametros::mdlCrearParametro($datosLimpios);
            
            if ($nuevoId) {
                $respuesta['success'] = true;
                $respuesta['message'] = 'Parámetro creado exitosamente';
                $respuesta['data'] = ['id' => $nuevoId];
            } else {
                $respuesta['message'] = 'Error al crear el parámetro';
            }
            
        } catch (Exception $e) {
            $respuesta['message'] = 'Error: ' . $e->getMessage();
            error_log("Error en ctrCrearParametro: " . $e->getMessage());
        }
        
        return $respuesta;
    }
    
    /**
     * Actualizar parámetro existente
     * @param array $datos
     * @return array
     */
    static public function ctrActualizarParametro($datos) {
        $respuesta = ['success' => false, 'message' => '', 'data' => null];
        
        try {
            // Validar que existe el ID
            if (empty($datos['parametro_id']) || !is_numeric($datos['parametro_id'])) {
                $respuesta['message'] = 'ID de parámetro inválido';
                return $respuesta;
            }
            
            // Validar que el parámetro existe
            $parametroExistente = ModelSistemaParametros::mdlObtenerParametroPorId($datos['parametro_id']);
            if (!$parametroExistente) {
                $respuesta['message'] = 'El parámetro no existe';
                return $respuesta;
            }
            
            // Validaciones
            $validacion = self::validarDatosParametro($datos, $datos['parametro_id']);
            if (!$validacion['valido']) {
                $respuesta['message'] = $validacion['mensaje'];
                return $respuesta;
            }
            
            // Limpiar y preparar datos
            $datosLimpios = self::limpiarDatosParametro($datos);
            $datosLimpios['parametro_id'] = $datos['parametro_id'];
            
            // Actualizar parámetro
            $actualizado = ModelSistemaParametros::mdlActualizarParametro($datosLimpios);
            
            if ($actualizado) {
                $respuesta['success'] = true;
                $respuesta['message'] = 'Parámetro actualizado exitosamente';
            } else {
                $respuesta['message'] = 'Error al actualizar el parámetro';
            }
            
        } catch (Exception $e) {
            $respuesta['message'] = 'Error: ' . $e->getMessage();
            error_log("Error en ctrActualizarParametro: " . $e->getMessage());
        }
        
        return $respuesta;
    }
    
    /**
     * Eliminar (desactivar) parámetro
     * @param int $id
     * @return array
     */
    static public function ctrEliminarParametro($id) {
        $respuesta = ['success' => false, 'message' => ''];
        
        try {
            if (empty($id) || !is_numeric($id)) {
                $respuesta['message'] = 'ID de parámetro inválido';
                return $respuesta;
            }
            
            // Verificar que el parámetro existe
            $parametro = ModelSistemaParametros::mdlObtenerParametroPorId($id);
            if (!$parametro) {
                $respuesta['message'] = 'El parámetro no existe';
                return $respuesta;
            }
            
            // Eliminar (desactivar) parámetro
            $eliminado = ModelSistemaParametros::mdlEliminarParametro($id);
            
            if ($eliminado) {
                $respuesta['success'] = true;
                $respuesta['message'] = 'Parámetro eliminado exitosamente';
            } else {
                $respuesta['message'] = 'Error al eliminar el parámetro';
            }
            
        } catch (Exception $e) {
            $respuesta['message'] = 'Error: ' . $e->getMessage();
            error_log("Error en ctrEliminarParametro: " . $e->getMessage());
        }
        
        return $respuesta;
    }
    
    /**
     * Obtener categorías disponibles
     * @return array
     */
    static public function ctrObtenerCategorias() {
        return ModelSistemaParametros::mdlObtenerCategorias();
    }
    
    /**
     * Obtener tipos disponibles
     * @return array
     */
    static public function ctrObtenerTipos() {
        return ModelSistemaParametros::mdlObtenerTipos();
    }
    
    /**
     * Buscar parámetros con filtros
     * @param array $filtros
     * @return array
     */
    static public function ctrBuscarParametros($filtros = []) {
        try {
            $parametros = ModelSistemaParametros::mdlBuscarParametros($filtros);
            
            // Formatear datos para la vista
            foreach ($parametros as &$parametro) {
                $parametro['estado_texto'] = $parametro['is_active'] ? 'Activo' : 'Inactivo';
                $parametro['fecha_creacion'] = date('d/m/Y H:i', strtotime($parametro['created_at']));
                $parametro['fecha_actualizacion'] = $parametro['updated_at'] ? 
                    date('d/m/Y H:i', strtotime($parametro['updated_at'])) : '-';
            }
            
            return $parametros;
            
        } catch (Exception $e) {
            error_log("Error en ctrBuscarParametros: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Validar datos del parámetro
     * @param array $datos
     * @param int $idActual - Para validaciones de actualización
     * @return array
     */
    private static function validarDatosParametro($datos, $idActual = null) {
        $resultado = ['valido' => true, 'mensaje' => ''];
        
        // Campos requeridos
        $camposRequeridos = [
            'parametro_codigo' => 'Código del parámetro',
            'parametro_nombre' => 'Nombre del parámetro',
            'parametro_valor' => 'Valor del parámetro'
        ];
        
        foreach ($camposRequeridos as $campo => $nombre) {
            if (empty($datos[$campo])) {
                $resultado['valido'] = false;
                $resultado['mensaje'] = "El campo '$nombre' es requerido";
                return $resultado;
            }
        }
        
        // Validar código (solo letras, números y guiones bajos)
        if (!preg_match('/^[A-Z0-9_]+$/', $datos['parametro_codigo'])) {
            $resultado['valido'] = false;
            $resultado['mensaje'] = 'El código debe contener solo letras mayúsculas, números y guiones bajos';
            return $resultado;
        }
        
        // Validar longitudes
        if (strlen($datos['parametro_codigo']) > 100) {
            $resultado['valido'] = false;
            $resultado['mensaje'] = 'El código no puede exceder 100 caracteres';
            return $resultado;
        }
        
        if (strlen($datos['parametro_nombre']) > 255) {
            $resultado['valido'] = false;
            $resultado['mensaje'] = 'El nombre no puede exceder 255 caracteres';
            return $resultado;
        }
        
        // Validar tipo si se especifica
        if (!empty($datos['parametro_tipo'])) {
            $tiposValidos = ['texto', 'numero', 'booleano', 'email', 'url', 'fecha', 'json'];
            if (!in_array(strtolower(trim($datos['parametro_tipo'])), $tiposValidos)) {
                $resultado['valido'] = false;
                $resultado['mensaje'] = 'Tipo de parámetro inválido. Tipos válidos: ' . implode(', ', $tiposValidos);
                return $resultado;
            }
        }
        
        return $resultado;
    }
    
    /**
     * Limpiar y preparar datos del parámetro
     * @param array $datos
     * @return array
     */
    private static function limpiarDatosParametro($datos) {
        return [
            'parametro_codigo' => strtoupper(trim($datos['parametro_codigo'])),
            'parametro_nombre' => trim($datos['parametro_nombre']),
            'parametro_valor' => trim($datos['parametro_valor']),
            'parametro_descripcion' => !empty($datos['parametro_descripcion']) ? 
                trim($datos['parametro_descripcion']) : null,
            'parametro_tipo' => !empty($datos['parametro_tipo']) ? 
                strtolower(trim($datos['parametro_tipo'])) : 'texto',
            'parametro_categoria' => !empty($datos['parametro_categoria']) ? 
                strtolower(trim($datos['parametro_categoria'])) : 'sistema',
            'is_active' => isset($datos['is_active']) ? 
                (bool)$datos['is_active'] : true
        ];
    }
    
    /**
     * Obtener tipos de parámetros disponibles para select
     * @return array
     */
    static public function ctrObtenerTiposDisponibles() {
        return [
            'texto' => 'Texto',
            'numero' => 'Número',
            'booleano' => 'Booleano (true/false)',
            'email' => 'Email',
            'url' => 'URL',
            'fecha' => 'Fecha',
            'json' => 'JSON'
        ];
    }
    
    /**
     * Obtener categorías sugeridas para select
     * @return array
     */
    static public function ctrObtenerCategoriasDisponibles() {
        return [
            'sistema' => 'Sistema',
            'interfaz' => 'Interfaz',
            'notificaciones' => 'Notificaciones',
            'seguridad' => 'Seguridad',
            'reportes' => 'Reportes',
            'integracion' => 'Integración'
        ];
    }
}
?>