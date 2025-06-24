<?php
require_once "conexion.php";

class EspecialidadesModel {
    
    /*=============================================
    OBTENER TODAS LAS ESPECIALIDADES
    =============================================*/
    static public function mdlObtenerEspecialidades() {
        $stmt = Conexion::conectar()->prepare("SELECT especialidad_id, nombre, descripcion, activo, fecha_creacion FROM especialidades ORDER BY especialidad_id");
        
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /*=============================================
    CREAR NUEVA ESPECIALIDAD
    =============================================*/
    static public function mdlCrearEspecialidad($nombre, $descripcion = '', $activo = 1) {
        try {
            $stmt = Conexion::conectar()->prepare("INSERT INTO especialidades(nombre, descripcion, activo) VALUES (:nombre, :descripcion, :activo)");
            
            $stmt->bindParam(":nombre", $nombre, PDO::PARAM_STR);
            $stmt->bindParam(":descripcion", $descripcion, PDO::PARAM_STR);
            $stmt->bindParam(":activo", $activo, PDO::PARAM_BOOL);
            
            if($stmt->execute()) {
                return ["status" => "ok", "message" => "Especialidad creada correctamente"];
            } else {
                return ["status" => "error", "message" => "Error al crear la especialidad"];
            }
        } catch (Exception $e) {
            return ["status" => "error", "message" => "Error: " . $e->getMessage()];
        }
        
        $stmt = null;
    }
    
    /*=============================================
    ACTUALIZAR ESPECIALIDAD
    =============================================*/
    static public function mdlActualizarEspecialidad($id, $nombre, $descripcion = '', $activo = 1) {
        try {
            $stmt = Conexion::conectar()->prepare("UPDATE especialidades SET nombre = :nombre, descripcion = :descripcion, activo = :activo WHERE especialidad_id = :id");
            
            $stmt->bindParam(":nombre", $nombre, PDO::PARAM_STR);
            $stmt->bindParam(":descripcion", $descripcion, PDO::PARAM_STR);
            $stmt->bindParam(":activo", $activo, PDO::PARAM_BOOL);
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            
            if($stmt->execute()) {
                return ["status" => "ok", "message" => "Especialidad actualizada correctamente"];
            } else {
                return ["status" => "error", "message" => "Error al actualizar la especialidad"];
            }
        } catch (Exception $e) {
            return ["status" => "error", "message" => "Error: " . $e->getMessage()];
        }
        
        $stmt = null;
    }
    
    /*=============================================
    ELIMINAR ESPECIALIDAD (BORRADO LÓGICO)
    =============================================*/
    static public function mdlEliminarEspecialidad($id) {
        try {
            $stmt = Conexion::conectar()->prepare("UPDATE especialidades SET activo = false WHERE especialidad_id = :id");
            
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            
            if($stmt->execute()) {
                return ["status" => "ok", "message" => "Especialidad eliminada correctamente"];
            } else {
                return ["status" => "error", "message" => "Error al eliminar la especialidad"];
            }
        } catch (Exception $e) {
            return ["status" => "error", "message" => "Error: " . $e->getMessage()];
        }
        
        $stmt = null;
    }
    
    /*=============================================
    OBTENER UNA ESPECIALIDAD POR ID
    =============================================*/
    static public function mdlObtenerEspecialidadPorId($id) {
        try {
            $stmt = Conexion::conectar()->prepare("SELECT especialidad_id, nombre, descripcion, activo FROM especialidades WHERE especialidad_id = :id");
            
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return ["status" => "error", "message" => "Error: " . $e->getMessage()];
        }
        
        $stmt = null;
    }
}
