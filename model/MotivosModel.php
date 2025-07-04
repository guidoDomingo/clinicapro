<?php
require_once "conexion.php";

class MotivosModel {
    
    /*=============================================
    OBTENER TODOS LOS MOTIVOS COMUNES
    =============================================*/
    static public function mdlObtenerMotivos() {
        $stmt = Conexion::conectar()->prepare("SELECT id_motivo, nombre, descripcion, activo, fecha_creacion, creado_por, tipo_formulario FROM motivos_comunes ORDER BY id_motivo");
        
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /*=============================================
    CREAR NUEVO MOTIVO COMÚN
    =============================================*/
    static public function mdlCrearMotivo($nombre, $descripcion = '', $activo = 1, $creado_por = null, $tipo_formulario = 'general') {
        try {
            $stmt = Conexion::conectar()->prepare("INSERT INTO motivos_comunes(nombre, descripcion, activo, creado_por, tipo_formulario) VALUES (:nombre, :descripcion, :activo, :creado_por, :tipo_formulario)");
            
            $stmt->bindParam(":nombre", $nombre, PDO::PARAM_STR);
            $stmt->bindParam(":descripcion", $descripcion, PDO::PARAM_STR);
            $stmt->bindParam(":activo", $activo, PDO::PARAM_BOOL);
            $stmt->bindParam(":creado_por", $creado_por, PDO::PARAM_INT);
            $stmt->bindParam(":tipo_formulario", $tipo_formulario, PDO::PARAM_STR);
            
            if($stmt->execute()) {
                return ["status" => "ok", "message" => "Motivo creado correctamente"];
            } else {
                return ["status" => "error", "message" => "Error al crear el motivo"];
            }
        } catch (Exception $e) {
            return ["status" => "error", "message" => "Error: " . $e->getMessage()];
        }
        
        $stmt = null;
    }
    
    /*=============================================
    ACTUALIZAR MOTIVO COMÚN
    =============================================*/
    static public function mdlActualizarMotivo($id, $nombre, $descripcion = '', $activo = 1, $tipo_formulario = 'general') {
        try {
            $stmt = Conexion::conectar()->prepare("UPDATE motivos_comunes SET nombre = :nombre, descripcion = :descripcion, activo = :activo, tipo_formulario = :tipo_formulario WHERE id_motivo = :id");
            
            $stmt->bindParam(":nombre", $nombre, PDO::PARAM_STR);
            $stmt->bindParam(":descripcion", $descripcion, PDO::PARAM_STR);
            $stmt->bindParam(":activo", $activo, PDO::PARAM_BOOL);
            $stmt->bindParam(":tipo_formulario", $tipo_formulario, PDO::PARAM_STR);
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            
            if($stmt->execute()) {
                return ["status" => "ok", "message" => "Motivo actualizado correctamente"];
            } else {
                return ["status" => "error", "message" => "Error al actualizar el motivo"];
            }
        } catch (Exception $e) {
            return ["status" => "error", "message" => "Error: " . $e->getMessage()];
        }
        
        $stmt = null;
    }
    
    /*=============================================
    ELIMINAR MOTIVO COMÚN (BORRADO LÓGICO)
    =============================================*/
    static public function mdlEliminarMotivo($id) {
        try {
            $stmt = Conexion::conectar()->prepare("UPDATE motivos_comunes SET activo = false WHERE id_motivo = :id");
            
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            
            if($stmt->execute()) {
                return ["status" => "ok", "message" => "Motivo eliminado correctamente"];
            } else {
                return ["status" => "error", "message" => "Error al eliminar el motivo"];
            }
        } catch (Exception $e) {
            return ["status" => "error", "message" => "Error: " . $e->getMessage()];
        }
        
        $stmt = null;
    }
    
    /*=============================================
    OBTENER UN MOTIVO COMÚN POR ID
    =============================================*/
    static public function mdlObtenerMotivoPorId($id) {
        try {
            $stmt = Conexion::conectar()->prepare("SELECT id_motivo, nombre, descripcion, activo, tipo_formulario FROM motivos_comunes WHERE id_motivo = :id");
            
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return ["status" => "error", "message" => "Error: " . $e->getMessage()];
        }
        
        $stmt = null;
    }
}
