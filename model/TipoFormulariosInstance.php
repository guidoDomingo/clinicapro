<?php
/**
 * Modelo para tipos de formularios (instancia compatible)
 */
class TipoFormularios {
    private $conexion;
    
    public function __construct($conexion) {
        $this->conexion = $conexion;
    }
    
    /**
     * Obtener todos los tipos de formularios
     */
    public function obtenerTodos($soloActivos = true) {
        try {
            $sql = "SELECT * FROM tipo_formularios";
            if ($soloActivos) {
                $sql .= " WHERE activo = true";
            }
            $sql .= " ORDER BY nombre ASC";
            
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error al obtener tipos de formularios: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtener un tipo de formulario por ID
     */
    public function obtenerPorId($id) {
        try {
            $stmt = $this->conexion->prepare("SELECT * FROM tipo_formularios WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error al obtener tipo de formulario por ID: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Crear un nuevo tipo de formulario
     */
    public function crear($datos) {
        try {
            $sql = "INSERT INTO tipo_formularios (nombre, descripcion, codigo, activo, creado_por, fecha_creacion) 
                    VALUES (?, ?, ?, ?, ?, CURRENT_TIMESTAMP)";
            
            $stmt = $this->conexion->prepare($sql);
            $resultado = $stmt->execute([
                $datos['nombre'],
                $datos['descripcion'] ?? '',
                $datos['codigo'],
                true, // Por defecto activo
                $datos['creado_por'] ?? 1
            ]);
            
            if ($resultado) {
                return [
                    'success' => true,
                    'message' => 'Tipo de formulario creado exitosamente',
                    'id' => $this->conexion->lastInsertId()
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Error al crear el tipo de formulario'
                ];
            }
        } catch (Exception $e) {
            error_log("Error al crear tipo de formulario: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error interno: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Actualizar un tipo de formulario
     */
    public function actualizar($id, $datos) {
        try {
            $sql = "UPDATE tipo_formularios 
                    SET nombre = ?, descripcion = ?, codigo = ?, modificado_por = ?, fecha_modificacion = CURRENT_TIMESTAMP 
                    WHERE id = ?";
            
            $stmt = $this->conexion->prepare($sql);
            $resultado = $stmt->execute([
                $datos['nombre'],
                $datos['descripcion'] ?? '',
                $datos['codigo'],
                $datos['modificado_por'] ?? 1,
                $id
            ]);
            
            if ($resultado) {
                return [
                    'success' => true,
                    'message' => 'Tipo de formulario actualizado exitosamente'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Error al actualizar el tipo de formulario'
                ];
            }
        } catch (Exception $e) {
            error_log("Error al actualizar tipo de formulario: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error interno: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Eliminar un tipo de formulario
     */
    public function eliminar($id) {
        try {
            $stmt = $this->conexion->prepare("DELETE FROM tipo_formularios WHERE id = ?");
            $resultado = $stmt->execute([$id]);
            
            if ($resultado) {
                return [
                    'success' => true,
                    'message' => 'Tipo de formulario eliminado exitosamente'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Error al eliminar el tipo de formulario'
                ];
            }
        } catch (Exception $e) {
            error_log("Error al eliminar tipo de formulario: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error interno: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Cambiar estado de un tipo de formulario
     */
    public function cambiarEstado($id, $activo, $modificadoPor) {
        try {
            $stmt = $this->conexion->prepare(
                "UPDATE tipo_formularios 
                 SET activo = ?, modificado_por = ?, fecha_modificacion = CURRENT_TIMESTAMP 
                 WHERE id = ?"
            );
            $resultado = $stmt->execute([$activo, $modificadoPor, $id]);
            
            if ($resultado) {
                $accion = $activo ? 'activado' : 'desactivado';
                return [
                    'success' => true,
                    'message' => "Tipo de formulario $accion exitosamente"
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Error al cambiar el estado del tipo de formulario'
                ];
            }
        } catch (Exception $e) {
            error_log("Error al cambiar estado: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error interno: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Verificar si existe un nombre (excluyendo un ID específico)
     */
    public function nombreExiste($nombre, $excluirId = null) {
        try {
            $sql = "SELECT COUNT(*) FROM tipo_formularios WHERE nombre = ?";
            $params = [$nombre];
            
            if ($excluirId) {
                $sql .= " AND id != ?";
                $params[] = $excluirId;
            }
            
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchColumn() > 0;
        } catch (Exception $e) {
            error_log("Error al verificar nombre: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Verificar si existe un código (excluyendo un ID específico)
     */
    public function codigoExiste($codigo, $excluirId = null) {
        try {
            $sql = "SELECT COUNT(*) FROM tipo_formularios WHERE codigo = ?";
            $params = [$codigo];
            
            if ($excluirId) {
                $sql .= " AND id != ?";
                $params[] = $excluirId;
            }
            
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchColumn() > 0;
        } catch (Exception $e) {
            error_log("Error al verificar código: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtener estadísticas de uso
     */
    public function obtenerEstadisticas() {
        try {
            $stats = [];
            
            // Total de tipos
            $stmt = $this->conexion->prepare("SELECT COUNT(*) FROM tipo_formularios");
            $stmt->execute();
            $stats['total'] = $stmt->fetchColumn();
            
            // Activos
            $stmt = $this->conexion->prepare("SELECT COUNT(*) FROM tipo_formularios WHERE activo = true");
            $stmt->execute();
            $stats['activos'] = $stmt->fetchColumn();
            
            // Inactivos
            $stats['inactivos'] = $stats['total'] - $stats['activos'];
            
            return $stats;
        } catch (Exception $e) {
            error_log("Error al obtener estadísticas: " . $e->getMessage());
            return ['total' => 0, 'activos' => 0, 'inactivos' => 0];
        }
    }
}
?>
