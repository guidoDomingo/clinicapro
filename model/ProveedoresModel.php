<?php
// Modelo para la gestión de proveedores y acreedores

require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/conexion.php";

class ProveedoresModel {
    
    /**
     * Mostrar todos los proveedores
     */
    public static function mdlMostrarProveedores($tabla, $item, $valor) {
        try {
            $conexion = Conexion::conectar();
            
            if($item != null) {
                $stmt = $conexion->prepare("SELECT * FROM $tabla WHERE $item = :$item");
                $stmt->bindParam(":".$item, $valor, PDO::PARAM_STR);
                $stmt->execute();
                return $stmt->fetch(PDO::FETCH_ASSOC);
            } else {
                $stmt = $conexion->prepare("SELECT * FROM $tabla ORDER BY prov_id DESC");
                $stmt->execute();
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
        } catch (Exception $e) {
            // Registrar el error para depuración
            error_log("Error en mdlMostrarProveedores: " . $e->getMessage());
            return [];
        } finally {
            $stmt = null;
        }
    }
    
    /**
     * Mostrar proveedores con información relacionada (joins para tipos de proveedor y empresas)
     */
    public static function mdlMostrarProveedoresCompleto($tabla, $item, $valor) {
        try {
            $conexion = Conexion::conectar();
            
            if($item != null) {
                $stmt = $conexion->prepare("SELECT p.*, 
                                                tp.descripcion AS tipo_proveedor_nombre, 
                                                e.business_name AS empresa_nombre,
                                                e.business_id
                                              FROM $tabla p
                                              LEFT JOIN cm_tipos_proveedores tp ON p.tipo_cod = tp.tipo_cod
                                              LEFT JOIN sys_business e ON p.business_id = e.business_id
                                              WHERE p.$item = :$item");
                $stmt->bindParam(":".$item, $valor, PDO::PARAM_STR);
                $stmt->execute();
                return $stmt->fetch(PDO::FETCH_ASSOC);
            } else {
                $stmt = $conexion->prepare("SELECT p.*, 
                                                tp.descripcion AS tipo_proveedor_nombre, 
                                                e.business_name AS empresa_nombre,
                                                e.business_id
                                              FROM $tabla p
                                              LEFT JOIN cm_tipos_proveedores tp ON p.tipo_cod = tp.tipo_cod
                                              LEFT JOIN sys_business e ON p.business_id = e.business_id
                                              ORDER BY p.prov_id DESC");
                $stmt->execute();
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
        } catch (Exception $e) {
            // Registrar el error para depuración
            error_log("Error en mdlMostrarProveedoresCompleto: " . $e->getMessage());
            return [];
        } finally {
            $stmt = null;
        }
    }
    
    /**
     * Crear un nuevo proveedor
     */
    public static function mdlCrearProveedor($tabla, $datos) {
        try {
            $conexion = Conexion::conectar();
            
            $stmt = $conexion->prepare("INSERT INTO $tabla(
                                        tipo_cod, 
                                        prov_type, 
                                        prov_name, 
                                        prov_lastname, 
                                        prov_razon, 
                                        prov_ruc, 
                                        prov_dv, 
                                        prov_timbrado, 
                                        prov_phone, 
                                        prov_email, 
                                        prov_address, 
                                        business_id, 
                                        prov_is_active, 
                                        created_at) 
                                    VALUES (
                                        :tipo_cod, 
                                        :prov_type, 
                                        :prov_name, 
                                        :prov_lastname, 
                                        :prov_razon, 
                                        :prov_ruc, 
                                        :prov_dv, 
                                        :prov_timbrado, 
                                        :prov_phone, 
                                        :prov_email, 
                                        :prov_address, 
                                        :business_id, 
                                        :prov_is_active, 
                                        NOW())");
            
            $stmt->bindParam(":tipo_cod", $datos["tipo_cod"], PDO::PARAM_STR);
            $stmt->bindParam(":prov_type", $datos["tipo_persona"], PDO::PARAM_STR);
            $stmt->bindParam(":prov_name", $datos["nombre"], PDO::PARAM_STR);
            $stmt->bindParam(":prov_lastname", $datos["apellido"], PDO::PARAM_STR);
            $stmt->bindParam(":prov_razon", $datos["razon_social"], PDO::PARAM_STR);
            $stmt->bindParam(":prov_ruc", $datos["ruc"], PDO::PARAM_STR);
            $stmt->bindParam(":prov_dv", $datos["dv"], PDO::PARAM_STR);
            $stmt->bindParam(":prov_timbrado", $datos["timbrado"], PDO::PARAM_STR);
            $stmt->bindParam(":prov_phone", $datos["telefono"], PDO::PARAM_STR);
            $stmt->bindParam(":prov_email", $datos["email"], PDO::PARAM_STR);
            $stmt->bindParam(":prov_address", $datos["direccion"], PDO::PARAM_STR);
            
            // Manejar valor nulo de business_id
            if ($datos["business_id"] === null) {
                $stmt->bindValue(":business_id", null, PDO::PARAM_NULL);
            } else {
                $stmt->bindParam(":business_id", $datos["business_id"], PDO::PARAM_INT);
            }
            
            // Convertir el valor booleano para PostgreSQL
            $isActive = $datos["is_active"] == "1" ? true : false;
            $stmt->bindParam(":prov_is_active", $isActive, PDO::PARAM_BOOL);
            
            if($stmt->execute()) {
                return "ok";
            } else {
                return "error";
            }
            
        } catch (Exception $e) {
            return "error: " . $e->getMessage();
        }
        
        $stmt = null;
    }
    
    /**
     * Editar un proveedor existente
     */
    public static function mdlEditarProveedor($tabla, $datos) {
        try {
            $conexion = Conexion::conectar();
            
            $stmt = $conexion->prepare("UPDATE $tabla SET 
                                        tipo_cod = :tipo_cod, 
                                        prov_type = :prov_type, 
                                        prov_name = :prov_name, 
                                        prov_lastname = :prov_lastname, 
                                        prov_razon = :prov_razon, 
                                        prov_ruc = :prov_ruc, 
                                        prov_dv = :prov_dv, 
                                        prov_timbrado = :prov_timbrado, 
                                        prov_phone = :prov_phone, 
                                        prov_email = :prov_email, 
                                        prov_address = :prov_address, 
                                        business_id = :business_id, 
                                        prov_is_active = :prov_is_active, 
                                        last_modified_at = NOW() 
                                    WHERE prov_id = :prov_id");
            
            $stmt->bindParam(":prov_id", $datos["id"], PDO::PARAM_INT);
            $stmt->bindParam(":tipo_cod", $datos["tipo_cod"], PDO::PARAM_STR);
            $stmt->bindParam(":prov_type", $datos["tipo_persona"], PDO::PARAM_STR);
            $stmt->bindParam(":prov_name", $datos["nombre"], PDO::PARAM_STR);
            $stmt->bindParam(":prov_lastname", $datos["apellido"], PDO::PARAM_STR);
            $stmt->bindParam(":prov_razon", $datos["razon_social"], PDO::PARAM_STR);
            $stmt->bindParam(":prov_ruc", $datos["ruc"], PDO::PARAM_STR);
            $stmt->bindParam(":prov_dv", $datos["dv"], PDO::PARAM_STR);
            $stmt->bindParam(":prov_timbrado", $datos["timbrado"], PDO::PARAM_STR);
            $stmt->bindParam(":prov_phone", $datos["telefono"], PDO::PARAM_STR);
            $stmt->bindParam(":prov_email", $datos["email"], PDO::PARAM_STR);
            $stmt->bindParam(":prov_address", $datos["direccion"], PDO::PARAM_STR);
            
            // Manejar valor nulo de business_id
            if ($datos["business_id"] === null) {
                $stmt->bindValue(":business_id", null, PDO::PARAM_NULL);
            } else {
                $stmt->bindParam(":business_id", $datos["business_id"], PDO::PARAM_INT);
            }
            
            // Convertir el valor booleano para PostgreSQL
            $isActive = $datos["is_active"] == "1" ? true : false;
            $stmt->bindParam(":prov_is_active", $isActive, PDO::PARAM_BOOL);
            
            if($stmt->execute()) {
                return "ok";
            } else {
                return "error";
            }
            
        } catch (Exception $e) {
            return "error: " . $e->getMessage();
        }
        
        $stmt = null;
    }
    
    /**
     * Borrar un proveedor
     */
    public static function mdlBorrarProveedor($tabla, $datos) {
        try {
            $stmt = Conexion::conectar()->prepare("DELETE FROM $tabla WHERE prov_id = :prov_id");
            $stmt->bindParam(":prov_id", $datos, PDO::PARAM_INT);
            
            if($stmt->execute()) {
                return "ok";
            } else {
                return "error";
            }
        } catch (Exception $e) {
            return "error: " . $e->getMessage();
        }
        
        $stmt = null;
    }
}
