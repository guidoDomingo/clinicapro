<?php
/**
 * Controlador para gestión de tipos de formularios
 */
class ControladorTipoFormularios {
    public $modelo;
    
    public function __construct($conexion) {
        require_once __DIR__ . '/../model/TipoFormulariosInstance.php';
        $this->modelo = new TipoFormularios($conexion);
    }
    
    /**
     * Listar tipos de formularios
     */
    public function listar() {
        $tipos = $this->modelo->obtenerTodos(false); // Incluir inactivos para administración
        $html = '';
        
        foreach ($tipos as $tipo) {
            $estadoClass = $tipo['activo'] ? 'badge-success' : 'badge-secondary';
            $estadoTexto = $tipo['activo'] ? 'Activo' : 'Inactivo';
            $fechaCreacion = date('d/m/Y H:i', strtotime($tipo['fecha_creacion']));
            
            $html .= '<tr>';
            $html .= '<td>' . htmlspecialchars($tipo['id']) . '</td>';
            $html .= '<td><strong>' . htmlspecialchars($tipo['nombre']) . '</strong></td>';
            $html .= '<td><code>' . htmlspecialchars($tipo['codigo']) . '</code></td>';
            $html .= '<td>' . htmlspecialchars($tipo['descripcion']) . '</td>';
            $html .= '<td><span class="badge ' . $estadoClass . '">' . $estadoTexto . '</span></td>';
            $html .= '<td><small>' . $fechaCreacion . '</small></td>';
            $html .= '<td>';
            $html .= '<div class="btn-group">';
            $html .= '<button type="button" class="btn btn-sm btn-info" onclick="editarTipoFormulario(' . $tipo['id'] . ')" title="Editar">';
            $html .= '<i class="fas fa-edit"></i>';
            $html .= '</button>';
            
            if ($tipo['activo']) {
                $html .= '<button type="button" class="btn btn-sm btn-warning" onclick="cambiarEstadoTipoFormulario(' . $tipo['id'] . ', false)" title="Desactivar">';
                $html .= '<i class="fas fa-eye-slash"></i>';
                $html .= '</button>';
            } else {
                $html .= '<button type="button" class="btn btn-sm btn-success" onclick="cambiarEstadoTipoFormulario(' . $tipo['id'] . ', true)" title="Activar">';
                $html .= '<i class="fas fa-eye"></i>';
                $html .= '</button>';
            }
            
            $html .= '</div>';
            $html .= '</td>';
            $html .= '</tr>';
        }
        
        return $html;
    }
    
    /**
     * Obtener tipos de formularios para select
     */
    public function obtenerParaSelect() {
        $tipos = $this->modelo->obtenerTodos(true);
        $opciones = '<option value="">Seleccionar tipo de formulario...</option>';
        
        foreach ($tipos as $tipo) {
            $opciones .= '<option value="' . htmlspecialchars($tipo['codigo']) . '">' . htmlspecialchars($tipo['nombre']) . '</option>';
        }
        
        return $opciones;
    }
    
    /**
     * Crear nuevo tipo de formulario
     */
    public function crear($datos) {
        // Validar datos
        $errores = $this->validarDatos($datos);
        if (!empty($errores)) {
            return [
                'success' => false,
                'message' => 'Errores de validación: ' . implode(', ', $errores)
            ];
        }
        
        // Verificar duplicados
        if ($this->modelo->nombreExiste($datos['nombre'])) {
            return [
                'success' => false,
                'message' => 'Ya existe un tipo de formulario con ese nombre'
            ];
        }
        
        if ($this->modelo->codigoExiste($datos['codigo'])) {
            return [
                'success' => false,
                'message' => 'Ya existe un tipo de formulario con ese código'
            ];
        }
        
        // Crear el tipo de formulario
        return $this->modelo->crear($datos);
    }
    
    /**
     * Actualizar tipo de formulario
     */
    public function actualizar($id, $datos) {
        // Validar datos
        $errores = $this->validarDatos($datos);
        if (!empty($errores)) {
            return [
                'success' => false,
                'message' => 'Errores de validación: ' . implode(', ', $errores)
            ];
        }
        
        // Verificar duplicados (excluyendo el registro actual)
        if ($this->modelo->nombreExiste($datos['nombre'], $id)) {
            return [
                'success' => false,
                'message' => 'Ya existe un tipo de formulario con ese nombre'
            ];
        }
        
        if ($this->modelo->codigoExiste($datos['codigo'], $id)) {
            return [
                'success' => false,
                'message' => 'Ya existe un tipo de formulario con ese código'
            ];
        }
        
        // Actualizar el tipo de formulario
        return $this->modelo->actualizar($id, $datos);
    }
    
    /**
     * Obtener tipo de formulario por ID
     */
    public function obtenerPorId($id) {
        return $this->modelo->obtenerPorId($id);
    }
    
    /**
     * Cambiar estado de tipo de formulario
     */
    public function cambiarEstado($id, $activo, $modificadoPor) {
        return $this->modelo->cambiarEstado($id, $activo, $modificadoPor);
    }
    
    /**
     * Validar datos del formulario
     */
    private function validarDatos($datos) {
        $errores = [];
        
        // Validar nombre
        if (empty($datos['nombre'])) {
            $errores[] = 'El nombre es obligatorio';
        } elseif (strlen($datos['nombre']) > 100) {
            $errores[] = 'El nombre no puede exceder 100 caracteres';
        }
        
        // Validar código
        if (empty($datos['codigo'])) {
            $errores[] = 'El código es obligatorio';
        } elseif (strlen($datos['codigo']) > 50) {
            $errores[] = 'El código no puede exceder 50 caracteres';
        } elseif (!preg_match('/^[a-z0-9_]+$/', $datos['codigo'])) {
            $errores[] = 'El código solo puede contener letras minúsculas, números y guiones bajos';
        }
        
        // Validar descripción
        if (strlen($datos['descripcion']) > 500) {
            $errores[] = 'La descripción no puede exceder 500 caracteres';
        }
        
        return $errores;
    }
    
    /**
     * Obtener estadísticas de uso
     */
    public function obtenerEstadisticas() {
        return $this->modelo->obtenerEstadisticas();
    }
    
    /**
     * Obtener todos los tipos de formularios en formato JSON
     */
    public function obtenerTodosJson($soloActivos = false) {
        return $this->modelo->obtenerTodos($soloActivos);
    }
    
    /**
     * Eliminar tipo de formulario
     */
    public function eliminar($id) {
        return $this->modelo->eliminar($id);
    }
    
    /**
     * Generar código automático basado en el nombre
     */
    public function generarCodigo($nombre) {
        // Convertir a minúsculas, remover acentos y espacios
        $codigo = strtolower($nombre);
        $codigo = str_replace(['á', 'é', 'í', 'ó', 'ú', 'ñ'], ['a', 'e', 'i', 'o', 'u', 'n'], $codigo);
        $codigo = preg_replace('/[^a-z0-9]/', '_', $codigo);
        $codigo = preg_replace('/_+/', '_', $codigo);
        $codigo = trim($codigo, '_');
        
        // Verificar si el código ya existe y agregar número si es necesario
        $codigoOriginal = $codigo;
        $contador = 1;
        
        while ($this->modelo->codigoExiste($codigo)) {
            $codigo = $codigoOriginal . '_' . $contador;
            $contador++;
        }
        
        return $codigo;
    }
}
?>
