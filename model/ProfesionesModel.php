<?php
require_once "conexion.php";

class ProfesionesModel {
    
    /*=============================================
    OBTENER TODAS LAS PROFESIONES ACTIVAS
    =============================================*/
    static public function mdlObtenerProfesiones() {
        $stmt = Conexion::conectar()->prepare("SELECT id, nombre FROM profesiones WHERE activo = TRUE ORDER BY nombre");
        
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
            return "ok";
        } else {
            return "error";
        }
        
        $stmt = null;
    }
    
    /*=============================================
    ACTUALIZAR PROFESION
    =============================================*/
    static public function mdlActualizarProfesion($id, $nombre) {
        $stmt = Conexion::conectar()->prepare("UPDATE profesiones SET nombre = :nombre WHERE id = :id");
        
        $stmt->bindParam(":nombre", $nombre, PDO::PARAM_STR);
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        
        if($stmt->execute()) {
            return "ok";
        } else {
            return "error";
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
            return "ok";
        } else {
            return "error";
        }
        
        $stmt = null;
    }
}