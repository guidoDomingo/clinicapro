<?php

require_once "conexion.php";

/**
 * Modelo para la gestión de referenciales dinámicos
 */
class ModelReferenciales {
    
    /**
     * Obtener registros de una tabla
     */
    static public function mdlObtenerRegistros($tabla, $item, $valor) {
        
        if ($item != null) {
            $stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla WHERE $item = :$item ORDER BY id");
            $stmt->bindParam(":".$item, $valor, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetch();
        } else {
            $stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla ORDER BY id");
            $stmt->execute();
            return $stmt->fetchAll();
        }
    }
    
    /**
     * Obtener registros con JOIN
     */
    static public function mdlObtenerRegistrosJoin($tabla1, $tabla2, $campoJoin1, $campoJoin2, $campos = "*", $condiciones = null) {
        
        $sql = "SELECT $campos FROM $tabla1 
                INNER JOIN $tabla2 ON $tabla1.$campoJoin1 = $tabla2.$campoJoin2";
        
        if ($condiciones != null) {
            $sql .= " WHERE $condiciones";
        }
        
        $sql .= " ORDER BY $tabla1.id";
        
        $stmt = Conexion::conectar()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Obtener campos de formulario con información del tipo de campo
     */
    static public function mdlObtenerCamposFormulario($tipoFormularioId = null) {
        
        $sql = "SELECT fc.*, tc.nombre as tipo_campo_nombre, tc.html_input_type, tc.requiere_opciones,
                       tf.nombre as formulario_nombre
                FROM formulario_campos fc
                INNER JOIN tipos_campos tc ON fc.tipo_campo_id = tc.id
                INNER JOIN tipos_formularios tf ON fc.tipo_formulario_id = tf.id";
        
        if ($tipoFormularioId != null) {
            $sql .= " WHERE fc.tipo_formulario_id = :tipoFormularioId";
        }
        
        $sql .= " ORDER BY fc.tipo_formulario_id, fc.orden_visualizacion, fc.id";
        
        $stmt = Conexion::conectar()->prepare($sql);
        
        if ($tipoFormularioId != null) {
            $stmt->bindParam(":tipoFormularioId", $tipoFormularioId, PDO::PARAM_INT);
        }
        
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Obtener valores de referencial
     */
    static public function mdlObtenerValoresReferencial($referencialId = null) {
        
        $sql = "SELECT rv.*, r.nombre as referencial_nombre, r.categoria
                FROM referencial_valores rv
                INNER JOIN referenciales r ON rv.referencial_id = r.id";
        
        if ($referencialId != null) {
            $sql .= " WHERE rv.referencial_id = :referencialId";
        }
        
        $sql .= " ORDER BY rv.referencial_id, rv.orden_visualizacion, rv.id";
        
        $stmt = Conexion::conectar()->prepare($sql);
        
        if ($referencialId != null) {
            $stmt->bindParam(":referencialId", $referencialId, PDO::PARAM_INT);
        }
        
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Obtener opciones de campo
     */
    static public function mdlObtenerOpcionesCampo($campoId) {
        
        $sql = "SELECT * FROM campo_opciones 
                WHERE formulario_campo_id = :campoId 
                ORDER BY orden_visualizacion, id";
        
        $stmt = Conexion::conectar()->prepare($sql);
        $stmt->bindParam(":campoId", $campoId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Insertar registro
     */
    static public function mdlIngresarRegistro($tabla, $datos) {
        
        // Generar dinámicamente la consulta SQL según los datos
        $campos = implode(', ', array_keys($datos));
        $valores = ':' . implode(', :', array_keys($datos));
        
        $stmt = Conexion::conectar()->prepare("INSERT INTO $tabla ($campos) VALUES ($valores)");
        
        // Bind de parámetros dinámico
        foreach ($datos as $clave => $valor) {
            if ($valor === null) {
                $stmt->bindValue(":$clave", $valor, PDO::PARAM_NULL);
            } elseif (is_int($valor)) {
                $stmt->bindValue(":$clave", $valor, PDO::PARAM_INT);
            } elseif (is_bool($valor)) {
                $stmt->bindValue(":$clave", $valor, PDO::PARAM_BOOL);
            } else {
                $stmt->bindValue(":$clave", $valor, PDO::PARAM_STR);
            }
        }
        
        if ($stmt->execute()) {
            return "ok";
        } else {
            return "error";
        }
    }
    
    /**
     * Editar registro
     */
    static public function mdlEditarRegistro($tabla, $datos, $item, $valor) {
        
        // Generar dinámicamente la parte SET de la consulta
        $setClauses = array();
        foreach ($datos as $campo => $val) {
            $setClauses[] = "$campo = :$campo";
        }
        $setString = implode(', ', $setClauses);
        
        $stmt = Conexion::conectar()->prepare("UPDATE $tabla SET $setString WHERE $item = :itemValue");
        
        // Bind de parámetros dinámico
        foreach ($datos as $clave => $val) {
            if ($val === null) {
                $stmt->bindValue(":$clave", $val, PDO::PARAM_NULL);
            } elseif (is_int($val)) {
                $stmt->bindValue(":$clave", $val, PDO::PARAM_INT);
            } elseif (is_bool($val)) {
                $stmt->bindValue(":$clave", $val, PDO::PARAM_BOOL);
            } else {
                $stmt->bindValue(":$clave", $val, PDO::PARAM_STR);
            }
        }
        
        $stmt->bindParam(":itemValue", $valor, PDO::PARAM_STR);
        
        if ($stmt->execute()) {
            return "ok";
        } else {
            return "error";
        }
    }
    
    /**
     * Eliminar registro
     */
    static public function mdlEliminarRegistro($tabla, $item, $valor) {
        
        $stmt = Conexion::conectar()->prepare("DELETE FROM $tabla WHERE $item = :$item");
        $stmt->bindParam(":".$item, $valor, PDO::PARAM_STR);
        
        if ($stmt->execute()) {
            return "ok";
        } else {
            return "error";
        }
    }
    
    /**
     * Verificar si un código ya existe en una tabla
     */
    static public function mdlVerificarCodigoExiste($tabla, $codigo, $id = null) {
        
        $sql = "SELECT COUNT(*) as total FROM $tabla WHERE codigo = :codigo";
        
        if ($id != null) {
            $sql .= " AND id != :id";
        }
        
        $stmt = Conexion::conectar()->prepare($sql);
        $stmt->bindParam(":codigo", $codigo, PDO::PARAM_STR);
        
        if ($id != null) {
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        }
        
        $stmt->execute();
        $resultado = $stmt->fetch();
        
        return $resultado['total'] > 0;
    }
    
    /**
     * Obtener el siguiente número de orden para un grupo
     */
    static public function mdlObtenerSiguienteOrden($tabla, $campoOrden, $condiciones = null) {
        
        $sql = "SELECT COALESCE(MAX($campoOrden), 0) + 1 as siguiente_orden FROM $tabla";
        
        if ($condiciones != null) {
            $sql .= " WHERE $condiciones";
        }
        
        $stmt = Conexion::conectar()->prepare($sql);
        $stmt->execute();
        $resultado = $stmt->fetch();
        
        return $resultado['siguiente_orden'];
    }
    
    /**
     * Obtener estadísticas de uso de referenciales
     */
    static public function mdlObtenerEstadisticas() {
        
        $sql = "SELECT 
                    (SELECT COUNT(*) FROM tipos_formularios WHERE activo = 1) as tipos_formularios,
                    (SELECT COUNT(*) FROM tipos_campos WHERE activo = 1) as tipos_campos,
                    (SELECT COUNT(*) FROM formulario_campos WHERE activo = 1) as campos_formularios,
                    (SELECT COUNT(*) FROM referenciales WHERE activo = 1) as referenciales,
                    (SELECT COUNT(*) FROM referencial_valores WHERE activo = 1) as valores_referenciales";
        
        $stmt = Conexion::conectar()->prepare($sql);
        $stmt->execute();
        return $stmt->fetch();
    }
    
    /**
     * Búsqueda avanzada en referenciales
     */
    static public function mdlBuscarReferenciales($termino, $categoria = null) {
        
        $sql = "SELECT r.*, COUNT(rv.id) as total_valores
                FROM referenciales r
                LEFT JOIN referencial_valores rv ON r.id = rv.referencial_id
                WHERE (r.nombre ILIKE :termino OR r.descripcion ILIKE :termino OR r.codigo ILIKE :termino)";
        
        if ($categoria != null) {
            $sql .= " AND r.categoria = :categoria";
        }
        
        $sql .= " GROUP BY r.id ORDER BY r.nombre";
        
        $stmt = Conexion::conectar()->prepare($sql);
        $stmt->bindValue(":termino", "%$termino%", PDO::PARAM_STR);
        
        if ($categoria != null) {
            $stmt->bindParam(":categoria", $categoria, PDO::PARAM_STR);
        }
        
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Obtener configuraciones de formulario
     */
    static public function mdlObtenerConfiguraciones($tipoFormularioId = null) {
        
        $sql = "SELECT fc.*, tf.nombre as formulario_nombre
                FROM formulario_configuraciones fc
                INNER JOIN tipos_formularios tf ON fc.tipo_formulario_id = tf.id";
        
        if ($tipoFormularioId != null) {
            $sql .= " WHERE fc.tipo_formulario_id = :tipoFormularioId";
        }
        
        $sql .= " ORDER BY fc.tipo_formulario_id, fc.configuracion_clave";
        
        $stmt = Conexion::conectar()->prepare($sql);
        
        if ($tipoFormularioId != null) {
            $stmt->bindParam(":tipoFormularioId", $tipoFormularioId, PDO::PARAM_INT);
        }
        
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Obtener campos de formularios con información de tipos
     */
    static public function mdlObtenerCamposFormularios() {
        $sql = "SELECT fc.*, tf.nombre as tipo_formulario, tc.nombre as tipo_campo
                FROM formulario_campos fc
                LEFT JOIN tipos_formularios tf ON fc.tipo_formulario_id = tf.id
                LEFT JOIN tipos_campos tc ON fc.tipo_campo_id = tc.id
                ORDER BY fc.tipo_formulario_id, fc.orden_visualizacion";
        
        $stmt = Conexion::conectar()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}

?>
