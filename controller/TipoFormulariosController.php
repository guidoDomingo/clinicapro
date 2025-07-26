<?php

class TipoFormulariosController {
    
    /**
     * Obtiene todos los tipos de formularios
     */
    public static function listarTipos() {
        return TipoFormularios::obtenerTodos();
    }
    
    /**
     * Obtiene un tipo de formulario por ID
     */
    public static function obtenerPorId($id) {
        return TipoFormularios::obtenerPorId($id);
    }
    
    /**
     * Crea un nuevo tipo de formulario
     */
    public static function crear($datos) {
        return TipoFormularios::crear($datos);
    }
    
    /**
     * Actualiza un tipo de formulario
     */
    public static function actualizar($id, $datos) {
        return TipoFormularios::actualizar($id, $datos);
    }
    
    /**
     * Elimina un tipo de formulario
     */
    public static function eliminar($id) {
        return TipoFormularios::eliminar($id);
    }
    
    /**
     * Obtiene tipos de formularios activos para selectores
     */
    public static function obtenerActivos() {
        return TipoFormularios::obtenerActivos();
    }
    
    /**
     * Listar todos los tipos de formularios (alias para compatibilidad)
     */
    public static function listarTodos() {
        return TipoFormularios::obtenerTodos();
    }
    
    /**
     * Activar un tipo de formulario
     */
    public static function activar($id) {
        return TipoFormularios::actualizar($id, ['activo' => true]);
    }
    
    /**
     * Desactivar un tipo de formulario
     */
    public static function desactivar($id) {
        return TipoFormularios::actualizar($id, ['activo' => false]);
    }
}
?>
