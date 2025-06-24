<?php
require_once "conexion.php";

class ProfesionesModel {
    
    /*=============================================
    OBTENER TODAS LAS PROFESIONES ACTIVAS
    =============================================*/
    static public function mdlObtenerProfesiones() {
        $stmt = Conexion::conectar()->prepare("SELECT id, nombre, activo, fecha_creacion FROM profesiones WHERE activo = TRUE ORDER BY id");
        
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /*=============================================
    CREAR NUEVA PROFESION
    =============================================*/
    static public function mdlCrearProfesion($nombre) {
        $stmt = Conexion::conectar()->prepare("INSERT INTO profesiones(nombre) VALUES (:nombre)");
        
        $stmt->bindParam(":nombre", $nombre, PDO::PARAM_STR);
        
        if($stmt->execute()) {
            // Devolvemos un array asociativo que puede ser convertido a JSON
            return ["status" => "ok", "message" => "Profesión creada correctamente"];
        } else {
            return ["status" => "error", "message" => "Error al crear la profesión"];
        }
        
        $stmt = null;
    }
    
    /*=============================================
    ACTUALIZAR PROFESION
    =============================================*/
    static public function mdlActualizarProfesion($id, $nombre, $activo) {
        $stmt = Conexion::conectar()->prepare("UPDATE profesiones SET nombre = :nombre, activo = :activo WHERE id = :id");
        
        $stmt->bindParam(":nombre", $nombre, PDO::PARAM_STR);
        $stmt->bindParam(":activo", $activo, PDO::PARAM_BOOL);
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        
        if($stmt->execute()) {
            return ["status" => "ok", "message" => "Profesión actualizada correctamente"];
        } else {
            return ["status" => "error", "message" => "Error al actualizar la profesión"];
        }
        
        $stmt = null;
    }
    
    /*=============================================
    ELIMINAR PROFESION (BORRADO LÓGICO)
    =============================================*/
    static public function mdlEliminarProfesion($id) {
        $stmt = Conexion::conectar()->prepare("UPDATE profesiones SET activo = FALSE WHERE id = :id");
        
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        
        if($stmt->execute()) {
            return ["status" => "ok", "message" => "Profesión eliminada correctamente"];
        } else {
            return ["status" => "error", "message" => "Error al eliminar la profesión"];
        }
        
        $stmt = null;
    }
}