<?php

require_once "conexion.php";

/**
 * Modelo para la gestión de empresas
 */
class EmpresasModel {
    
    /**
     * Mostrar todas las empresas
     */
    static public function mdlMostrarEmpresas($tabla, $item, $valor) {
        if($item != null) {
            $stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla WHERE $item = :$item");
            $stmt->bindParam(":".$item, $valor, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetch();
        } else {
            $stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla ORDER BY business_id DESC");
            $stmt->execute();
            return $stmt->fetchAll();
        }

        $stmt->close();
        $stmt = null;
    }

    /**
     * Ingresar una empresa
     */
    static public function mdlIngresarEmpresa($tabla, $datos) {
        $stmt = Conexion::conectar()->prepare("INSERT INTO $tabla(business_name, business_ruc, business_email, business_phone, business_address, business_is_active) 
                                             VALUES (:business_name, :business_ruc, :business_email, :business_phone, :business_address, :business_is_active)");

        $stmt->bindParam(":business_name", $datos["business_name"], PDO::PARAM_STR);
        $stmt->bindParam(":business_ruc", $datos["business_ruc"], PDO::PARAM_STR);
        $stmt->bindParam(":business_email", $datos["business_email"], PDO::PARAM_STR);
        $stmt->bindParam(":business_phone", $datos["business_phone"], PDO::PARAM_STR);
        $stmt->bindParam(":business_address", $datos["business_address"], PDO::PARAM_STR);
        $stmt->bindParam(":business_is_active", $datos["business_is_active"], PDO::PARAM_BOOL);

        if($stmt->execute()) {
            return "ok";
        } else {
            return "error";
        }

        $stmt->close();
        $stmt = null;
    }

    /**
     * Editar una empresa
     */
    static public function mdlEditarEmpresa($tabla, $datos) {
        $stmt = Conexion::conectar()->prepare("UPDATE $tabla SET business_name = :business_name, business_ruc = :business_ruc, 
                                              business_email = :business_email, business_phone = :business_phone, 
                                              business_address = :business_address, business_is_active = :business_is_active 
                                              WHERE business_id = :business_id");

        $stmt->bindParam(":business_name", $datos["business_name"], PDO::PARAM_STR);
        $stmt->bindParam(":business_ruc", $datos["business_ruc"], PDO::PARAM_STR);
        $stmt->bindParam(":business_email", $datos["business_email"], PDO::PARAM_STR);
        $stmt->bindParam(":business_phone", $datos["business_phone"], PDO::PARAM_STR);
        $stmt->bindParam(":business_address", $datos["business_address"], PDO::PARAM_STR);
        $stmt->bindParam(":business_is_active", $datos["business_is_active"], PDO::PARAM_BOOL);
        $stmt->bindParam(":business_id", $datos["business_id"], PDO::PARAM_INT);

        if($stmt->execute()) {
            return "ok";
        } else {
            return "error";
        }

        $stmt->close();
        $stmt = null;
    }
    
    /**
     * Actualizar estado de una empresa
     */
    static public function mdlActualizarEmpresa($tabla, $item1, $valor1, $item2, $valor2) {
        $stmt = Conexion::conectar()->prepare("UPDATE $tabla SET $item1 = :$item1 WHERE $item2 = :$item2");
        
        // Si el campo es business_is_active, usar PARAM_BOOL
        if($item1 === "business_is_active") {
            $stmt->bindParam(":".$item1, $valor1, PDO::PARAM_BOOL);
        } else {
            $stmt->bindParam(":".$item1, $valor1, PDO::PARAM_STR);
        }
        
        $stmt->bindParam(":".$item2, $valor2, PDO::PARAM_INT);

        if($stmt->execute()) {
            return "ok";
        } else {
            return "error";
        }

        $stmt->close();
        $stmt = null;
    }

    /**
     * Borrar una empresa
     */
    static public function mdlBorrarEmpresa($tabla, $datos) {
        $stmt = Conexion::conectar()->prepare("DELETE FROM $tabla WHERE business_id = :business_id");
        $stmt->bindParam(":business_id", $datos, PDO::PARAM_INT);

        if($stmt->execute()) {
            return "ok";
        } else {
            return "error";
        }

        $stmt->close();
        $stmt = null;
    }
}
