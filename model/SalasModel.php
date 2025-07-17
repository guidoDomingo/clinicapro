<?php
// Modelo para la gestión de salas

require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/conexion.php";

class SalasModel {
    
    /**
     * Mostrar todas las salas
     */
    public static function mdlMostrarSalas($tabla, $item, $valor) {
        try {
            $conexion = Conexion::conectar();
            
            if($item != null) {
                $stmt = $conexion->prepare("SELECT * FROM $tabla WHERE $item = :$item");
                $stmt->bindParam(":".$item, $valor, PDO::PARAM_STR);
                $stmt->execute();
                return $stmt->fetch(PDO::FETCH_ASSOC);
            } else {
                $stmt = $conexion->prepare("SELECT * FROM $tabla ORDER BY sala_id DESC");
                $stmt->execute();
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
        } catch (Exception $e) {
            // Registrar el error para depuración
            error_log("Error en mdlMostrarSalas: " . $e->getMessage());
            return [];
        } finally {
            $stmt = null;
        }
    }
    
    /**
     * Insertar nueva sala
     */
    public static function mdlIngresarSala($tabla, $datos) {
        try {
            $conexion = Conexion::conectar();
            
            // Verificar si el código ya existe
            $verificar = $conexion->prepare("SELECT sala_codigo FROM $tabla WHERE sala_codigo = :sala_codigo");
            $verificar->bindParam(":sala_codigo", $datos["sala_codigo"], PDO::PARAM_STR);
            $verificar->execute();
            
            if($verificar->fetch()) {
                return "El código de sala ya existe";
            }
            
            $stmt = $conexion->prepare("INSERT INTO $tabla(sala_codigo, sala_nombre, sala_descripcion, sala_estado, fecha_creacion) 
                                      VALUES (:sala_codigo, :sala_nombre, :sala_descripcion, :sala_estado, NOW())");
            
            $stmt->bindParam(":sala_codigo", $datos["sala_codigo"], PDO::PARAM_STR);
            $stmt->bindParam(":sala_nombre", $datos["sala_nombre"], PDO::PARAM_STR);
            $stmt->bindParam(":sala_descripcion", $datos["sala_descripcion"], PDO::PARAM_STR);
            $stmt->bindParam(":sala_estado", $datos["sala_estado"], PDO::PARAM_BOOL);
            
            if($stmt->execute()) {
                return "ok";
            } else {
                return "error";
            }
        } catch (Exception $e) {
            error_log("Error en mdlIngresarSala: " . $e->getMessage());
            return "Error: " . $e->getMessage();
        } finally {
            $stmt = null;
        }
    }
    
    /**
     * Editar sala
     */
    public static function mdlEditarSala($tabla, $datos) {
        try {
            $conexion = Conexion::conectar();
            
            // Verificar si el código ya existe en otra sala
            $verificar = $conexion->prepare("SELECT sala_id FROM $tabla WHERE sala_codigo = :sala_codigo AND sala_id != :sala_id");
            $verificar->bindParam(":sala_codigo", $datos["sala_codigo"], PDO::PARAM_STR);
            $verificar->bindParam(":sala_id", $datos["sala_id"], PDO::PARAM_INT);
            $verificar->execute();
            
            if($verificar->fetch()) {
                return "El código de sala ya existe en otra sala";
            }
            
            $stmt = $conexion->prepare("UPDATE $tabla SET 
                                      sala_codigo = :sala_codigo,
                                      sala_nombre = :sala_nombre,
                                      sala_descripcion = :sala_descripcion,
                                      sala_estado = :sala_estado,
                                      fecha_modificacion = NOW()
                                      WHERE sala_id = :sala_id");
            
            $stmt->bindParam(":sala_codigo", $datos["sala_codigo"], PDO::PARAM_STR);
            $stmt->bindParam(":sala_nombre", $datos["sala_nombre"], PDO::PARAM_STR);
            $stmt->bindParam(":sala_descripcion", $datos["sala_descripcion"], PDO::PARAM_STR);
            $stmt->bindParam(":sala_estado", $datos["sala_estado"], PDO::PARAM_BOOL);
            $stmt->bindParam(":sala_id", $datos["sala_id"], PDO::PARAM_INT);
            
            if($stmt->execute()) {
                return "ok";
            } else {
                return "error";
            }
        } catch (Exception $e) {
            error_log("Error en mdlEditarSala: " . $e->getMessage());
            return "Error: " . $e->getMessage();
        } finally {
            $stmt = null;
        }
    }
    
    /**
     * Eliminar sala (cambiar estado a inactivo)
     */
    public static function mdlEliminarSala($tabla, $datos) {
        try {
            $conexion = Conexion::conectar();
            
            $stmt = $conexion->prepare("UPDATE $tabla SET sala_estado = false, fecha_modificacion = NOW() WHERE sala_id = :sala_id");
            $stmt->bindParam(":sala_id", $datos, PDO::PARAM_INT);
            
            if($stmt->execute()) {
                return "ok";
            } else {
                return "error";
            }
        } catch (Exception $e) {
            error_log("Error en mdlEliminarSala: " . $e->getMessage());
            return "Error: " . $e->getMessage();
        } finally {
            $stmt = null;
        }
    }
    
    /**
     * Activar sala
     */
    public static function mdlActivarSala($tabla, $datos) {
        try {
            $conexion = Conexion::conectar();
            
            $stmt = $conexion->prepare("UPDATE $tabla SET sala_estado = true, fecha_modificacion = NOW() WHERE sala_id = :sala_id");
            $stmt->bindParam(":sala_id", $datos, PDO::PARAM_INT);
            
            if($stmt->execute()) {
                return "ok";
            } else {
                return "error";
            }
        } catch (Exception $e) {
            error_log("Error en mdlActivarSala: " . $e->getMessage());
            return "Error: " . $e->getMessage();
        } finally {
            $stmt = null;
        }
    }
    
    /**
     * Mostrar todas las salas activas para selectores
     */
    public static function mdlMostrarSalasActivas($tabla) {
        try {
            $conexion = Conexion::conectar();
            
            $stmt = $conexion->prepare("SELECT sala_id as id, sala_codigo as codigo, sala_nombre as nombre 
                                      FROM $tabla 
                                      WHERE sala_estado = true 
                                      ORDER BY sala_codigo ASC");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en mdlMostrarSalasActivas: " . $e->getMessage());
            return [];
        } finally {
            $stmt = null;
        }
    }
    
    /**
     * Buscar salas por código o nombre
     */
    public static function mdlBuscarSalas($tabla, $termino) {
        try {
            $conexion = Conexion::conectar();
            
            $stmt = $conexion->prepare("SELECT * FROM $tabla 
                                      WHERE sala_codigo ILIKE :termino 
                                      OR sala_nombre ILIKE :termino
                                      ORDER BY sala_id DESC");
            
            $terminoBusqueda = "%$termino%";
            $stmt->bindParam(":termino", $terminoBusqueda, PDO::PARAM_STR);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en mdlBuscarSalas: " . $e->getMessage());
            return [];
        } finally {
            $stmt = null;
        }
    }
    
    /**
     * Filtrar salas por estado
     */
    public static function mdlFiltrarSalasPorEstado($tabla, $estado) {
        try {
            $conexion = Conexion::conectar();
            
            $stmt = $conexion->prepare("SELECT * FROM $tabla WHERE sala_estado = :estado ORDER BY sala_id DESC");
            $stmt->bindParam(":estado", $estado, PDO::PARAM_BOOL);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en mdlFiltrarSalasPorEstado: " . $e->getMessage());
            return [];
        } finally {
            $stmt = null;
        }
    }
}
?>
