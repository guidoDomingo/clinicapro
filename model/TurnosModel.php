<?php
// Modelo para la gestión de turnos

require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/conexion.php";

class TurnosModel {
    
    /**
     * Mostrar todos los turnos
     */
    public static function mdlMostrarTurnos($tabla, $item, $valor) {
        try {
            $conexion = Conexion::conectar();
            
            if($item != null) {
                $stmt = $conexion->prepare("SELECT * FROM $tabla WHERE $item = :$item");
                $stmt->bindParam(":".$item, $valor, PDO::PARAM_STR);
                $stmt->execute();
                return $stmt->fetch(PDO::FETCH_ASSOC);
            } else {
                $stmt = $conexion->prepare("SELECT * FROM $tabla ORDER BY turno_id DESC");
                $stmt->execute();
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
        } catch (Exception $e) {
            // Registrar el error para depuración
            error_log("Error en mdlMostrarTurnos: " . $e->getMessage());
            return [];
        } finally {
            $stmt = null;
        }
    }
    
    /**
     * Insertar nuevo turno
     */
    public static function mdlIngresarTurno($tabla, $datos) {
        try {
            $conexion = Conexion::conectar();
            
            // Verificar si el nombre ya existe
            $verificar = $conexion->prepare("SELECT turno_nombre FROM $tabla WHERE turno_nombre = :turno_nombre");
            $verificar->bindParam(":turno_nombre", $datos["turno_nombre"], PDO::PARAM_STR);
            $verificar->execute();
            
            if($verificar->fetch()) {
                return "El nombre del turno ya existe";
            }
            
            $stmt = $conexion->prepare("INSERT INTO $tabla(turno_nombre, turno_descripcion, turno_estado, fecha_creacion) 
                                        VALUES (:turno_nombre, :turno_descripcion, :turno_estado, CURRENT_TIMESTAMP)");
                                        
            $stmt->bindParam(":turno_nombre", $datos["turno_nombre"], PDO::PARAM_STR);
            $stmt->bindParam(":turno_descripcion", $datos["turno_descripcion"], PDO::PARAM_STR);
            $stmt->bindParam(":turno_estado", $datos["turno_estado"], PDO::PARAM_BOOL);
            
            if($stmt->execute()) {
                return "ok";
            } else {
                return "error";
            }
            
        } catch (Exception $e) {
            // Registrar el error específico
            error_log("Error en mdlIngresarTurno: " . $e->getMessage());
            
            // Verificar si es un error de clave duplicada
            if($e->getCode() == 23505) {
                return "El nombre del turno ya existe";
            }
            
            return "Error al insertar turno: " . $e->getMessage();
        } finally {
            $stmt = null;
        }
    }
    
    /**
     * Editar turno
     */
    public static function mdlEditarTurno($tabla, $datos) {
        try {
            $conexion = Conexion::conectar();
            
            // Verificar si el nombre ya existe en otro registro
            $verificar = $conexion->prepare("SELECT turno_nombre FROM $tabla WHERE turno_nombre = :turno_nombre AND turno_id != :turno_id");
            $verificar->bindParam(":turno_nombre", $datos["turno_nombre"], PDO::PARAM_STR);
            $verificar->bindParam(":turno_id", $datos["turno_id"], PDO::PARAM_INT);
            $verificar->execute();
            
            if($verificar->fetch()) {
                return "El nombre del turno ya existe";
            }
            
            $stmt = $conexion->prepare("UPDATE $tabla SET 
                                        turno_nombre = :turno_nombre, 
                                        turno_descripcion = :turno_descripcion, 
                                        turno_estado = :turno_estado,
                                        fecha_modificacion = CURRENT_TIMESTAMP
                                        WHERE turno_id = :turno_id");
                                        
            $stmt->bindParam(":turno_nombre", $datos["turno_nombre"], PDO::PARAM_STR);
            $stmt->bindParam(":turno_descripcion", $datos["turno_descripcion"], PDO::PARAM_STR);
            $stmt->bindParam(":turno_estado", $datos["turno_estado"], PDO::PARAM_BOOL);
            $stmt->bindParam(":turno_id", $datos["turno_id"], PDO::PARAM_INT);
            
            if($stmt->execute()) {
                return "ok";
            } else {
                return "error";
            }
            
        } catch (Exception $e) {
            error_log("Error en mdlEditarTurno: " . $e->getMessage());
            
            if($e->getCode() == 23505) {
                return "El nombre del turno ya existe";
            }
            
            return "Error al actualizar turno: " . $e->getMessage();
        } finally {
            $stmt = null;
        }
    }
    
    /**
     * Borrar turno
     */
    public static function mdlBorrarTurno($tabla, $datos) {
        try {
            $conexion = Conexion::conectar();
            
            // Verificar si el turno está siendo usado en agendas_detalle
            $verificarUso = $conexion->prepare("SELECT COUNT(*) as total FROM agendas_detalle WHERE turno_id = :turno_id");
            $verificarUso->bindParam(":turno_id", $datos, PDO::PARAM_INT);
            $verificarUso->execute();
            $resultado = $verificarUso->fetch(PDO::FETCH_ASSOC);
            
            if($resultado['total'] > 0) {
                return "No se puede eliminar el turno porque está siendo utilizado en " . $resultado['total'] . " agenda(s)";
            }
            
            $stmt = $conexion->prepare("DELETE FROM $tabla WHERE turno_id = :turno_id");
            $stmt->bindParam(":turno_id", $datos, PDO::PARAM_INT);
            
            if($stmt->execute()) {
                return "ok";
            } else {
                return "error";
            }
            
        } catch (Exception $e) {
            error_log("Error en mdlBorrarTurno: " . $e->getMessage());
            
            // Si es un error de clave foránea
            if($e->getCode() == 23503) {
                return "No se puede eliminar el turno porque está siendo utilizado en otras partes del sistema";
            }
            
            return "Error al eliminar turno: " . $e->getMessage();
        } finally {
            $stmt = null;
        }
    }
    
    /**
     * Buscar turnos por filtro
     */
    public static function mdlBuscarTurnos($tabla, $filtro) {
        try {
            $conexion = Conexion::conectar();
            
            $stmt = $conexion->prepare("SELECT * FROM $tabla 
                                        WHERE turno_nombre ILIKE :filtro 
                                        OR turno_descripcion ILIKE :filtro
                                        ORDER BY turno_id DESC");
                                        
            $filtroParam = "%".$filtro."%";
            $stmt->bindParam(":filtro", $filtroParam, PDO::PARAM_STR);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("Error en mdlBuscarTurnos: " . $e->getMessage());
            return [];
        } finally {
            $stmt = null;
        }
    }
    
    /**
     * Obtener turnos activos para selectores
     */
    public static function mdlObtenerTurnosActivos() {
        try {
            $conexion = Conexion::conectar();
            
            $stmt = $conexion->prepare("SELECT turno_id, turno_nombre 
                                        FROM turnos 
                                        WHERE turno_estado = true 
                                        ORDER BY turno_nombre ASC");
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("Error en mdlObtenerTurnosActivos: " . $e->getMessage());
            return [];
        } finally {
            $stmt = null;
        }
    }
    
    /**
     * Mostrar turnos activos (alias para compatibilidad con AJAX)
     */
    public static function mdlMostrarTurnosActivos($tabla) {
        return self::mdlObtenerTurnosActivos();
    }
    
    /**
     * Cambiar estado de turno (activar/desactivar)
     */
    public static function mdlCambiarEstadoTurno($tabla, $datos) {
        try {
            $conexion = Conexion::conectar();
            
            $stmt = $conexion->prepare("UPDATE $tabla SET 
                                        turno_estado = :turno_estado,
                                        fecha_modificacion = CURRENT_TIMESTAMP
                                        WHERE turno_id = :turno_id");
                                        
            $stmt->bindParam(":turno_estado", $datos["turno_estado"], PDO::PARAM_BOOL);
            $stmt->bindParam(":turno_id", $datos["turno_id"], PDO::PARAM_INT);
            
            if($stmt->execute()) {
                return "ok";
            } else {
                return "error";
            }
            
        } catch (Exception $e) {
            error_log("Error en mdlCambiarEstadoTurno: " . $e->getMessage());
            return "Error al cambiar estado del turno: " . $e->getMessage();
        } finally {
            $stmt = null;
        }
    }
}
?>
