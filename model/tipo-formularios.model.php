<?php

require_once "conexion.php";

class ModeloTipoFormularios {

    /*=============================================
    MOSTRAR TODOS LOS TIPOS DE FORMULARIOS
    =============================================*/
    static public function mdlMostrarTipoFormularios($tabla, $item, $valor) {
        if($item != null) {
            $stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla WHERE $item = :$item ORDER BY nombre ASC");
            $stmt -> bindParam(":".$item, $valor, PDO::PARAM_STR);
            $stmt -> execute();
            return $stmt -> fetch();
        } else {
            $stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla WHERE activo = true ORDER BY nombre ASC");
            $stmt -> execute();
            return $stmt -> fetchAll();
        }
    }

    /*=============================================
    CREAR TIPO DE FORMULARIO
    =============================================*/
    static public function mdlIngresarTipoFormulario($tabla, $datos) {
        $stmt = Conexion::conectar()->prepare("INSERT INTO $tabla(nombre, descripcion, codigo, creado_por) VALUES (:nombre, :descripcion, :codigo, :creado_por)");
        
        $stmt->bindParam(":nombre", $datos["nombre"], PDO::PARAM_STR);
        $stmt->bindParam(":descripcion", $datos["descripcion"], PDO::PARAM_STR);
        $stmt->bindParam(":codigo", $datos["codigo"], PDO::PARAM_STR);
        $stmt->bindParam(":creado_por", $datos["creado_por"], PDO::PARAM_INT);

        if($stmt->execute()) {
            return "ok";
        } else {
            return "error";
        }
    }

    /*=============================================
    EDITAR TIPO DE FORMULARIO
    =============================================*/
    static public function mdlEditarTipoFormulario($tabla, $datos) {
        $stmt = Conexion::conectar()->prepare("UPDATE $tabla SET nombre = :nombre, descripcion = :descripcion, codigo = :codigo, fecha_modificacion = CURRENT_TIMESTAMP, modificado_por = :modificado_por WHERE id = :id");
        
        $stmt->bindParam(":id", $datos["id"], PDO::PARAM_INT);
        $stmt->bindParam(":nombre", $datos["nombre"], PDO::PARAM_STR);
        $stmt->bindParam(":descripcion", $datos["descripcion"], PDO::PARAM_STR);
        $stmt->bindParam(":codigo", $datos["codigo"], PDO::PARAM_STR);
        $stmt->bindParam(":modificado_por", $datos["modificado_por"], PDO::PARAM_INT);

        if($stmt->execute()) {
            return "ok";
        } else {
            return "error";
        }
    }

    /*=============================================
    BORRAR TIPO DE FORMULARIO (SOFT DELETE)
    =============================================*/
    static public function mdlBorrarTipoFormulario($tabla, $datos) {
        $stmt = Conexion::conectar()->prepare("UPDATE $tabla SET activo = false, fecha_modificacion = CURRENT_TIMESTAMP, modificado_por = :modificado_por WHERE id = :id");
        
        $stmt->bindParam(":id", $datos["id"], PDO::PARAM_INT);
        $stmt->bindParam(":modificado_por", $datos["modificado_por"], PDO::PARAM_INT);

        if($stmt->execute()) {
            return "ok";
        } else {
            return "error";
        }
    }

    /*=============================================
    ACTIVAR/DESACTIVAR TIPO DE FORMULARIO
    =============================================*/
    static public function mdlActivarTipoFormulario($tabla, $datos) {
        $stmt = Conexion::conectar()->prepare("UPDATE $tabla SET activo = :activo, fecha_modificacion = CURRENT_TIMESTAMP, modificado_por = :modificado_por WHERE id = :id");
        
        $stmt->bindParam(":id", $datos["id"], PDO::PARAM_INT);
        $stmt->bindParam(":activo", $datos["activo"], PDO::PARAM_BOOL);
        $stmt->bindParam(":modificado_por", $datos["modificado_por"], PDO::PARAM_INT);

        if($stmt->execute()) {
            return "ok";
        } else {
            return "error";
        }
    }

    /*=============================================
    VERIFICAR SI CÓDIGO YA EXISTE
    =============================================*/
    static public function mdlVerificarCodigo($tabla, $codigo, $id = null) {
        if($id != null) {
            $stmt = Conexion::conectar()->prepare("SELECT id FROM $tabla WHERE codigo = :codigo AND id != :id");
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        } else {
            $stmt = Conexion::conectar()->prepare("SELECT id FROM $tabla WHERE codigo = :codigo");
        }
        
        $stmt->bindParam(":codigo", $codigo, PDO::PARAM_STR);
        $stmt->execute();
        
        return $stmt->fetch() ? true : false;
    }

    /*=============================================
    VERIFICAR SI NOMBRE YA EXISTE
    =============================================*/
    static public function mdlVerificarNombre($tabla, $nombre, $id = null) {
        if($id != null) {
            $stmt = Conexion::conectar()->prepare("SELECT id FROM $tabla WHERE nombre = :nombre AND id != :id");
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        } else {
            $stmt = Conexion::conectar()->prepare("SELECT id FROM $tabla WHERE nombre = :nombre");
        }
        
        $stmt->bindParam(":nombre", $nombre, PDO::PARAM_STR);
        $stmt->execute();
        
        return $stmt->fetch() ? true : false;
    }

    /*=============================================
    OBTENER TIPOS DE FORMULARIOS PARA SELECT
    =============================================*/
    static public function mdlObtenerTiposFormulariosSelect() {
        $stmt = Conexion::conectar()->prepare("SELECT id, nombre, codigo FROM tipo_formularios WHERE activo = true ORDER BY nombre ASC");
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
