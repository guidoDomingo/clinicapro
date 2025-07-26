<?php
/**
 * Modelo para gestión de tipos de formularios
 */
class TipoFormularios {
    
    /**
     * Obtener todos los tipos de formularios activos
     */
    public static function obtenerTodos($soloActivos = true) {
        try {
            $conexion = Conexion::conectar();
            $sql = "SELECT * FROM tipo_formularios";
            if ($soloActivos) {
                $sql .= " WHERE activo = true";
            }
            $sql .= " ORDER BY nombre ASC";
            
            $stmt = $conexion->prepare($sql);
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
    public static function obtenerPorId($id) {
        try {
            $conexion = Conexion::conectar();
            $sql = "SELECT * FROM tipo_formularios WHERE id = :id";
            $stmt = $conexion->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error al obtener tipo de formulario por ID: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Obtener un tipo de formulario por código
     */
    public static function obtenerPorCodigo($codigo) {
        try {
            $conexion = Conexion::conectar();
            $sql = "SELECT * FROM tipo_formularios WHERE codigo = :codigo AND activo = true";
            $stmt = $conexion->prepare($sql);
            $stmt->bindParam(':codigo', $codigo, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error al obtener tipo de formulario por código: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Crear un nuevo tipo de formulario
     */
    public static function crear($datos) {
        try {
            $conexion = Conexion::conectar();
            $sql = "INSERT INTO tipo_formularios (nombre, descripcion, codigo, creado_por) 
                    VALUES (:nombre, :descripcion, :codigo, :creado_por)";
            
            $stmt = $conexion->prepare($sql);
            $stmt->bindParam(':nombre', $datos['nombre'], PDO::PARAM_STR);
            $stmt->bindParam(':descripcion', $datos['descripcion'], PDO::PARAM_STR);
            $stmt->bindParam(':codigo', $datos['codigo'], PDO::PARAM_STR);
            $stmt->bindParam(':creado_por', $datos['creado_por'], PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                return [
                    'success' => true,
                    'id' => $conexion->lastInsertId(),
                    'message' => 'Tipo de formulario creado exitosamente'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Error al crear el tipo de formulario'
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Actualizar un tipo de formulario
     */
    public static function actualizar($id, $datos) {
        try {
            $conexion = Conexion::conectar();
            $sql = "UPDATE tipo_formularios 
                    SET nombre = :nombre, 
                        descripcion = :descripcion, 
                        codigo = :codigo,
                        fecha_modificacion = CURRENT_TIMESTAMP,
                        modificado_por = :modificado_por
                    WHERE id = :id";
            
            $stmt = $conexion->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':nombre', $datos['nombre'], PDO::PARAM_STR);
            $stmt->bindParam(':descripcion', $datos['descripcion'], PDO::PARAM_STR);
            $stmt->bindParam(':codigo', $datos['codigo'], PDO::PARAM_STR);
            $stmt->bindParam(':modificado_por', $datos['modificado_por'], PDO::PARAM_INT);
            
            if ($stmt->execute()) {
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
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Eliminar (desactivar) un tipo de formulario
     */
    public static function eliminar($id) {
        return self::cambiarEstado($id, false, 1);
    }
    
    /**
     * Obtener tipos de formularios activos para selectores
     */
    public static function obtenerActivos() {
        return self::obtenerTodos(true);
    }
    
    /**
     * Cambiar estado activo/inactivo de un tipo de formulario
     */
    public static function cambiarEstado($id, $activo, $modificadoPor) {
        try {
            $conexion = Conexion::conectar();
            $sql = "UPDATE tipo_formularios 
                    SET activo = :activo,
                        fecha_modificacion = CURRENT_TIMESTAMP,
                        modificado_por = :modificado_por
                    WHERE id = :id";
            
            $stmt = $conexion->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':activo', $activo, PDO::PARAM_BOOL);
            $stmt->bindParam(':modificado_por', $modificadoPor, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                $estado = $activo ? 'activado' : 'desactivado';
                return [
                    'success' => true,
                    'message' => "Tipo de formulario $estado exitosamente"
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Error al cambiar el estado del tipo de formulario'
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Verificar si un código ya existe
     */
    public static function codigoExiste($codigo, $excluirId = null) {
        try {
            $conexion = Conexion::conectar();
            $sql = "SELECT COUNT(*) FROM tipo_formularios WHERE codigo = :codigo";
            if ($excluirId) {
                $sql .= " AND id != :excluir_id";
            }
            
            $stmt = $conexion->prepare($sql);
            $stmt->bindParam(':codigo', $codigo, PDO::PARAM_STR);
            if ($excluirId) {
                $stmt->bindParam(':excluir_id', $excluirId, PDO::PARAM_INT);
            }
            
            $stmt->execute();
            return $stmt->fetchColumn() > 0;
        } catch (Exception $e) {
            error_log("Error al verificar código: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Verificar si un nombre ya existe
     */
    public static function nombreExiste($nombre, $excluirId = null) {
        try {
            $conexion = Conexion::conectar();
            $sql = "SELECT COUNT(*) FROM tipo_formularios WHERE nombre = :nombre";
            if ($excluirId) {
                $sql .= " AND id != :excluir_id";
            }
            
            $stmt = $conexion->prepare($sql);
            $stmt->bindParam(':nombre', $nombre, PDO::PARAM_STR);
            if ($excluirId) {
                $stmt->bindParam(':excluir_id', $excluirId, PDO::PARAM_INT);
            }
            
            $stmt->execute();
            return $stmt->fetchColumn() > 0;
        } catch (Exception $e) {
            error_log("Error al verificar nombre: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtener estadísticas de uso de tipos de formularios
     */
    public static function obtenerEstadisticas() {
        try {
            $conexion = Conexion::conectar();
            $sql = "SELECT 
                        tf.id,
                        tf.nombre,
                        tf.codigo,
                        tf.activo,
                        COUNT(pf.id) as total_preformatos
                    FROM tipo_formularios tf
                    LEFT JOIN preformatos pf ON tf.codigo = pf.tipo_formulario
                    GROUP BY tf.id, tf.nombre, tf.codigo, tf.activo
                    ORDER BY tf.nombre ASC";
            
            $stmt = $conexion->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error al obtener estadísticas: " . $e->getMessage());
            return [];
        }
    }
}
?>
