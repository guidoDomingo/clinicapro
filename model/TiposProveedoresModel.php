<?php

require_once "conexion.php";

/**
 * Modelo para la gestión de tipos de proveedores
 */
class TiposProveedoresModel {
    
    /**
     * Mostrar todos los tipos de proveedores
     */
    static public function mdlMostrarTiposProveedores($tabla, $item, $valor) {
        if($item != null) {
            $stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla WHERE $item = :$item");
            $stmt->bindParam(":".$item, $valor, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetch();
        } else {
            $stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla ORDER BY tipo_cod DESC");
            $stmt->execute();
            return $stmt->fetchAll();
        }

        $stmt->close();
        $stmt = null;
    }

    /**
     * Ingresar un tipo de proveedor
     */
    static public function mdlIngresarTipoProveedor($tabla, $datos) {
        $stmt = Conexion::conectar()->prepare("INSERT INTO $tabla(tipo_nombre, descripcion) 
                                             VALUES (:tipo_nombre, :descripcion)");

        $stmt->bindParam(":tipo_nombre", $datos["tipo_nombre"], PDO::PARAM_STR);
        $stmt->bindParam(":descripcion", $datos["descripcion"], PDO::PARAM_STR);

        if($stmt->execute()) {
            return "ok";
        } else {
            return "error";
        }

        $stmt->close();
        $stmt = null;
    }

    /**
     * Editar un tipo de proveedor
     */
    static public function mdlEditarTipoProveedor($tabla, $datos) {
        $stmt = Conexion::conectar()->prepare("UPDATE $tabla SET tipo_nombre = :tipo_nombre, 
                                             descripcion = :descripcion 
                                             WHERE tipo_cod = :tipo_cod");

        $stmt->bindParam(":tipo_nombre", $datos["tipo_nombre"], PDO::PARAM_STR);
        $stmt->bindParam(":descripcion", $datos["descripcion"], PDO::PARAM_STR);
        $stmt->bindParam(":tipo_cod", $datos["tipo_cod"], PDO::PARAM_INT);

        if($stmt->execute()) {
            return "ok";
        } else {
            return "error";
        }

        $stmt->close();
        $stmt = null;
    }
    
    /**
     * Borrar un tipo de proveedor
     */
    static public function mdlBorrarTipoProveedor($tabla, $datos) {
        $stmt = Conexion::conectar()->prepare("DELETE FROM $tabla WHERE tipo_cod = :tipo_cod");
        $stmt->bindParam(":tipo_cod", $datos, PDO::PARAM_INT);

        if($stmt->execute()) {
            return "ok";
        } else {
            return "error";
        }

        $stmt->close();
        $stmt = null;
    }
}
