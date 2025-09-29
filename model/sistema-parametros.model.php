<?php
/**
 * Modelo para Sistema de Parámetros
 * Maneja las operaciones CRUD para la tabla sistema_parametros
 */
class ModelSistemaParametros {
    
    /**
     * Obtener todos los parámetros del sistema
     * @param bool $soloActivos - Si true, solo obtiene parámetros activos
     * @return array
     */
    static public function mdlObtenerTodosParametros($soloActivos = true) {
        try {
            $sql = "SELECT 
                        parametro_id,
                        parametro_codigo,
                        parametro_nombre,
                        parametro_valor,
                        parametro_descripcion,
                        parametro_tipo,
                        parametro_categoria,
                        is_active,
                        created_at,
                        updated_at
                    FROM sistema_parametros";
            
            if ($soloActivos) {
                $sql .= " WHERE is_active = true";
            }
            
            $sql .= " ORDER BY parametro_categoria, parametro_nombre";
            
            $stmt = Conexion::conectar()->prepare($sql);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("Error en mdlObtenerTodosParametros: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtener un parámetro específico por ID
     * @param int $id
     * @return array|false
     */
    static public function mdlObtenerParametroPorId($id) {
        try {
            $sql = "SELECT * FROM sistema_parametros WHERE parametro_id = :id";
            $stmt = Conexion::conectar()->prepare($sql);
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("Error en mdlObtenerParametroPorId: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtener un parámetro por código
     * @param string $codigo
     * @return array|false
     */
    static public function mdlObtenerParametroPorCodigo($codigo) {
        try {
            $sql = "SELECT * FROM sistema_parametros WHERE parametro_codigo = :codigo AND is_active = true";
            $stmt = Conexion::conectar()->prepare($sql);
            $stmt->bindParam(":codigo", $codigo, PDO::PARAM_STR);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("Error en mdlObtenerParametroPorCodigo: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Crear un nuevo parámetro
     * @param array $datos
     * @return bool|int - ID del nuevo parámetro o false en caso de error
     */
    static public function mdlCrearParametro($datos) {
        try {
            // Verificar que el código no exista
            $verificar = "SELECT COUNT(*) FROM sistema_parametros WHERE parametro_codigo = :codigo";
            $stmt = Conexion::conectar()->prepare($verificar);
            $stmt->bindParam(":codigo", $datos["parametro_codigo"], PDO::PARAM_STR);
            $stmt->execute();
            
            if ($stmt->fetchColumn() > 0) {
                throw new Exception("El código del parámetro ya existe");
            }
            
            $sql = "INSERT INTO sistema_parametros 
                    (parametro_codigo, parametro_nombre, parametro_valor, parametro_descripcion, 
                     parametro_tipo, parametro_categoria, is_active, created_at, updated_at)
                    VALUES 
                    (:codigo, :nombre, :valor, :descripcion, :tipo, :categoria, :activo, NOW(), NOW())";
            
            $stmt = Conexion::conectar()->prepare($sql);
            
            $stmt->bindParam(":codigo", $datos["parametro_codigo"], PDO::PARAM_STR);
            $stmt->bindParam(":nombre", $datos["parametro_nombre"], PDO::PARAM_STR);
            $stmt->bindParam(":valor", $datos["parametro_valor"], PDO::PARAM_STR);
            $stmt->bindParam(":descripcion", $datos["parametro_descripcion"], PDO::PARAM_STR);
            $stmt->bindParam(":tipo", $datos["parametro_tipo"], PDO::PARAM_STR);
            $stmt->bindParam(":categoria", $datos["parametro_categoria"], PDO::PARAM_STR);
            
            $activo = isset($datos["is_active"]) ? $datos["is_active"] : true;
            $stmt->bindParam(":activo", $activo, PDO::PARAM_BOOL);
            
            $stmt->execute();
            
            return Conexion::conectar()->lastInsertId();
            
        } catch (Exception $e) {
            error_log("Error en mdlCrearParametro: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Actualizar un parámetro existente
     * @param array $datos
     * @return bool
     */
    static public function mdlActualizarParametro($datos) {
        try {
            // Verificar que el código no exista en otros registros
            $verificar = "SELECT COUNT(*) FROM sistema_parametros 
                         WHERE parametro_codigo = :codigo AND parametro_id != :id";
            $stmt = Conexion::conectar()->prepare($verificar);
            $stmt->bindParam(":codigo", $datos["parametro_codigo"], PDO::PARAM_STR);
            $stmt->bindParam(":id", $datos["parametro_id"], PDO::PARAM_INT);
            $stmt->execute();
            
            if ($stmt->fetchColumn() > 0) {
                throw new Exception("El código del parámetro ya existe en otro registro");
            }
            
            $sql = "UPDATE sistema_parametros SET 
                        parametro_codigo = :codigo,
                        parametro_nombre = :nombre,
                        parametro_valor = :valor,
                        parametro_descripcion = :descripcion,
                        parametro_tipo = :tipo,
                        parametro_categoria = :categoria,
                        is_active = :activo,
                        updated_at = NOW()
                    WHERE parametro_id = :id";
            
            $stmt = Conexion::conectar()->prepare($sql);
            
            $stmt->bindParam(":id", $datos["parametro_id"], PDO::PARAM_INT);
            $stmt->bindParam(":codigo", $datos["parametro_codigo"], PDO::PARAM_STR);
            $stmt->bindParam(":nombre", $datos["parametro_nombre"], PDO::PARAM_STR);
            $stmt->bindParam(":valor", $datos["parametro_valor"], PDO::PARAM_STR);
            $stmt->bindParam(":descripcion", $datos["parametro_descripcion"], PDO::PARAM_STR);
            $stmt->bindParam(":tipo", $datos["parametro_tipo"], PDO::PARAM_STR);
            $stmt->bindParam(":categoria", $datos["parametro_categoria"], PDO::PARAM_STR);
            $stmt->bindParam(":activo", $datos["is_active"], PDO::PARAM_BOOL);
            
            return $stmt->execute();
            
        } catch (Exception $e) {
            error_log("Error en mdlActualizarParametro: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Eliminar (desactivar) un parámetro
     * @param int $id
     * @return bool
     */
    static public function mdlEliminarParametro($id) {
        try {
            $sql = "UPDATE sistema_parametros SET is_active = false, updated_at = NOW() 
                    WHERE parametro_id = :id";
            $stmt = Conexion::conectar()->prepare($sql);
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            
            return $stmt->execute();
            
        } catch (Exception $e) {
            error_log("Error en mdlEliminarParametro: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtener categorías únicas de parámetros
     * @return array
     */
    static public function mdlObtenerCategorias() {
        try {
            $sql = "SELECT DISTINCT parametro_categoria 
                    FROM sistema_parametros 
                    WHERE parametro_categoria IS NOT NULL 
                    AND is_active = true
                    ORDER BY parametro_categoria";
            
            $stmt = Conexion::conectar()->prepare($sql);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
            
        } catch (Exception $e) {
            error_log("Error en mdlObtenerCategorias: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtener tipos únicos de parámetros
     * @return array
     */
    static public function mdlObtenerTipos() {
        try {
            $sql = "SELECT DISTINCT parametro_tipo 
                    FROM sistema_parametros 
                    WHERE parametro_tipo IS NOT NULL 
                    AND is_active = true
                    ORDER BY parametro_tipo";
            
            $stmt = Conexion::conectar()->prepare($sql);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
            
        } catch (Exception $e) {
            error_log("Error en mdlObtenerTipos: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Buscar parámetros por filtros
     * @param array $filtros
     * @return array
     */
    static public function mdlBuscarParametros($filtros = []) {
        try {
            $sql = "SELECT * FROM sistema_parametros WHERE 1=1";
            $params = [];
            
            if (!empty($filtros['categoria'])) {
                $sql .= " AND parametro_categoria = :categoria";
                $params[':categoria'] = $filtros['categoria'];
            }
            
            if (!empty($filtros['tipo'])) {
                $sql .= " AND parametro_tipo = :tipo";
                $params[':tipo'] = $filtros['tipo'];
            }
            
            if (!empty($filtros['buscar'])) {
                $sql .= " AND (parametro_nombre ILIKE :buscar OR parametro_codigo ILIKE :buscar)";
                $params[':buscar'] = '%' . $filtros['buscar'] . '%';
            }
            
            if (isset($filtros['activo'])) {
                $sql .= " AND is_active = :activo";
                $params[':activo'] = $filtros['activo'];
            }
            
            $sql .= " ORDER BY parametro_categoria, parametro_nombre";
            
            $stmt = Conexion::conectar()->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("Error en mdlBuscarParametros: " . $e->getMessage());
            return [];
        }
    }
}
?>