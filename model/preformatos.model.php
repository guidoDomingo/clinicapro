<?php
require_once "conexion.php";

class ModelPreformatos {
    /**
     * Obtiene todos los motivos comunes activos
     * @return array Arreglo con los motivos comunes
     */
    public static function mdlGetMotivosComunes($tipo_formulario = 'general') {
        try {
            $stmt = Conexion::conectar()->prepare(
                "SELECT id_motivo, nombre, descripcion, tipo_formulario
                FROM motivos_comunes 
                WHERE activo = true 
                AND tipo_formulario = :tipo_formulario
                ORDER BY nombre ASC"
            );
            
            $stmt->bindParam(":tipo_formulario", $tipo_formulario, PDO::PARAM_STR);
            
            $stmt->execute();
            $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $resultado;
        } catch (PDOException $e) {
            error_log("Error al obtener motivos comunes: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtiene todos los preformatos activos de un tipo específico
     * @param string $tipo Tipo de preformato ('consulta' o 'receta')
     * @param int $doctorId ID del doctor para filtrar preformatos (opcional)
     * @param string $tipoFormulario Tipo de formulario ('general', 'anteojos', etc.)
     * @return array Arreglo con los preformatos
     */
    public static function mdlGetPreformatos($tipo, $doctorId = null, $tipoFormulario = 'general') {
        try {
            $sql = "SELECT
                    p.id_preformato,
                    p.nombre,
                    p.contenido,
                    p.tipo,
                    p.tipo_formulario,
                    p.creado_por
                FROM preformatos p
                WHERE p.activo = true
                AND p.tipo = :tipo
                AND p.tipo_formulario = :tipo_formulario";
            
            // Si se proporciona un ID de doctor, añadir filtro
            if ($doctorId !== null) {
                $sql .= " AND p.creado_por = :doctor_id";
            }
            
            // Ordenar por nombre al final
            $sql .= " ORDER BY p.nombre ASC";
            
            $stmt = Conexion::conectar()->prepare($sql);
            $stmt->bindParam(":tipo", $tipo, PDO::PARAM_STR);
            $stmt->bindParam(":tipo_formulario", $tipoFormulario, PDO::PARAM_STR);
            
            // Bind doctor_id si se proporcionó
            if ($doctorId !== null) {
                $stmt->bindParam(":doctor_id", $doctorId, PDO::PARAM_INT);
            }
            
            $stmt->execute();
            $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Registrar para depuración
            error_log("mdlGetPreformatos - Tipo: $tipo, Doctor ID: " . ($doctorId ? $doctorId : 'ninguno') . ", Tipo Formulario: $tipoFormulario - Total registros: " . count($resultado));
            
            return $resultado;
        } catch (PDOException $e) {
            error_log("Error al obtener preformatos: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtiene todos los preformatos con opciones de filtrado
     * @param array $filtros Filtros a aplicar (tipo, propietario, título)
     * @return array Arreglo con los preformatos filtrados
     */
    public static function mdlGetAllPreformatos($filtros = []) {
        try {
            $condiciones = ["p.activo = true"];
            $parametros = [];
            
            // Aplicar filtros si existen
            if (!empty($filtros['tipo'])) {
                $condiciones[] = "p.tipo = :tipo";
                $parametros[':tipo'] = $filtros['tipo'];
            }
            
            if (!empty($filtros['creado_por'])) {
                $condiciones[] = "p.creado_por = :creado_por";
                $parametros[':creado_por'] = $filtros['creado_por'];
            }
            
            if (!empty($filtros['titulo'])) {
                $condiciones[] = "p.nombre LIKE :titulo";
                $parametros[':titulo'] = "%" . $filtros['titulo'] . "%";
            }
            
            $condicionesSQL = implode(" AND ", $condiciones);
            
            $sql = "SELECT 
                    p.id_preformato,
                    p.nombre,
                    p.tipo,
                    p.contenido,
                    p.fecha_creacion,
                    p.creado_por,
                    rp.first_name,
                    rp.last_name,
                    d.doctor_id,
                    b.business_name
                FROM 
                    preformatos p
                LEFT JOIN rh_doctors d ON p.creado_por = d.doctor_id
                LEFT JOIN rh_person rp ON d.person_id = rp.person_id
                LEFT JOIN sys_business b ON d.business_id = b.business_id
                WHERE $condicionesSQL
                ORDER BY p.nombre ASC";
            
            error_log("SQL getAllPreformatos: " . $sql);
            error_log("Parámetros: " . json_encode($parametros));
            
            $stmt = Conexion::conectar()->prepare($sql);
            
            // Bind de parámetros
            foreach ($parametros as $param => $valor) {
                $tipo = is_int($valor) ? PDO::PARAM_INT : PDO::PARAM_STR;
                $stmt->bindValue($param, $valor, $tipo);
            }
            
            $stmt->execute();
            $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Agregar el nombre completo del doctor para cada preformato
            $formateados = [];
            foreach ($resultados as $preformato) {
                // Agregar el nombre completo del doctor
                if (!empty($preformato['first_name']) && !empty($preformato['last_name'])) {
                    $nombreDoctor = $preformato['last_name'] . ', ' . $preformato['first_name'];
                    
                    // Agregar el nombre del consultorio/empresa si existe
                    if (!empty($preformato['business_name'])) {
                        $nombreDoctor .= ' (' . $preformato['business_name'] . ')';
                    }
                    
                    $preformato['nombre_propietario'] = $nombreDoctor;
                } else {
                    // Si no hay información de doctor, usar ID de doctor
                    $preformato['nombre_propietario'] = 'Doctor ID: ' . $preformato['creado_por'];
                }
                
                $formateados[] = $preformato;
            }
            
            return $formateados;
        } catch (PDOException $e) {
            error_log("Error al obtener preformatos con filtros: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtiene todos los preformatos ordenados por nombre
     * @param int $userId ID del usuario logueado para filtrar los preformatos (opcional)
     * @return array Arreglo con los preformatos
     */
    public static function mdlGetAllPreformatosOrdered($userId = null) {
        try {
            // Si se proporciona un ID de usuario, usar la consulta con joins adecuados para obtener preformatos por usuario
            if ($userId) {
                $sql = "SELECT 
                        p.*,
                        rp.first_name,
                        rp.last_name,
                        d.doctor_id,
                        b.business_name
                    FROM person_system_user psu 
                    INNER JOIN rh_doctors d 
                    ON psu.person_id = d.person_id 
                    INNER JOIN preformatos p 
                    ON p.creado_por = d.doctor_id 
                    LEFT JOIN rh_person rp ON d.person_id = rp.person_id
                    LEFT JOIN sys_business b ON d.business_id = b.business_id
                    WHERE psu.system_user_id = :user_id
                    AND p.activo = true
                    ORDER BY p.nombre ASC";
                
                error_log("SQL para obtener preformatos de usuario específico: " . $sql);
                
                $stmt = Conexion::conectar()->prepare($sql);
                $stmt->bindParam(":user_id", $userId, PDO::PARAM_INT);
            } else {
                // Consulta para todos los preformatos si no hay filtro de usuario
                $sql = "SELECT 
                        p.*,
                        rp.first_name,
                        rp.last_name,
                        d.doctor_id,
                        b.business_name
                    FROM preformatos p
                    LEFT JOIN rh_doctors d ON p.creado_por = d.doctor_id 
                    LEFT JOIN rh_person rp ON d.person_id = rp.person_id
                    LEFT JOIN sys_business b ON d.business_id = b.business_id
                    WHERE p.activo = true
                    ORDER BY p.nombre ASC";
                
                error_log("SQL para obtener todos los preformatos: " . $sql);
                
                $stmt = Conexion::conectar()->prepare($sql);
            }
            
            $stmt->execute();
            $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Agregar el nombre completo del doctor para cada preformato
            $formateados = [];
            foreach ($resultados as $preformato) {
                // Agregar el nombre completo del doctor
                if (!empty($preformato['first_name']) && !empty($preformato['last_name'])) {
                    $nombreDoctor = $preformato['last_name'] . ', ' . $preformato['first_name'];
                    
                    // Agregar el nombre del consultorio/empresa si existe
                    if (!empty($preformato['business_name'])) {
                        $nombreDoctor .= ' (' . $preformato['business_name'] . ')';
                    }
                    
                    $preformato['doctor_nombre'] = $nombreDoctor;
                } else {
                    // Si no hay información de doctor, usar ID de doctor
                    $preformato['doctor_nombre'] = 'Doctor ID: ' . $preformato['creado_por'];
                }
                
                $formateados[] = $preformato;
            }
            
            // Registrar para depuración
            error_log("mdlGetAllPreformatosOrdered - Usuario ID: " . ($userId ? $userId : 'ninguno') . " - Total registros: " . count($formateados));
            
            return $formateados;
        } catch (PDOException $e) {
            error_log("Error al obtener preformatos ordenados: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtiene la lista de usuarios para el selector de propietarios
     * @return array Arreglo con los usuarios
     */
    public static function mdlGetUsuarios() {
        try {
            $stmt = Conexion::conectar()->prepare(
                "SELECT u.user_id, CONCAT(r.reg_name, ' ', r.reg_lastname) as nombre_usuario
                FROM sys_users u
                JOIN sys_register r ON u.reg_id = r.reg_id
                WHERE u.user_is_active = true 
                ORDER BY r.reg_name ASC"
            );
            
            $stmt->execute();
            $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $resultado;
        } catch (PDOException $e) {
            error_log("Error al obtener usuarios: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtiene todos los tipos de formularios activos
     * @return array Arreglo con los tipos de formularios
     */
    public static function mdlGetTiposFormularios() {
        try {
            $stmt = Conexion::conectar()->prepare(
                "SELECT id, nombre, descripcion, codigo 
                FROM tipo_formularios 
                WHERE activo = true 
                ORDER BY nombre ASC"
            );
            
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en mdlGetTiposFormularios: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtiene un preformato por su ID
     * @param int $idPreformato ID del preformato a obtener
     * @return array|false Arreglo con los datos del preformato o false si no existe
     */
    public static function mdlGetPreformatoById($idPreformato) {
        try {
            $stmt = Conexion::conectar()->prepare(
                "SELECT 
                    p.id_preformato, 
                    p.nombre, 
                    p.contenido, 
                    p.tipo, 
                    p.creado_por,
                    rp.first_name,
                    rp.last_name,
                    d.doctor_id,
                    b.business_name
                FROM preformatos p
                LEFT JOIN rh_doctors d ON p.creado_por = d.doctor_id
                LEFT JOIN rh_person rp ON d.person_id = rp.person_id
                LEFT JOIN sys_business b ON d.business_id = b.business_id
                WHERE p.id_preformato = :id_preformato AND p.activo = true"
            );
            
            $stmt->bindParam(":id_preformato", $idPreformato, PDO::PARAM_INT);
            $stmt->execute();
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($resultado) {
                // Agregar el nombre completo del doctor
                if (!empty($resultado['first_name']) && !empty($resultado['last_name'])) {
                    $nombreDoctor = $resultado['last_name'] . ', ' . $resultado['first_name'];
                    
                    // Agregar el nombre del consultorio/empresa si existe
                    if (!empty($resultado['business_name'])) {
                        $nombreDoctor .= ' (' . $resultado['business_name'] . ')';
                    }
                    
                    $resultado['nombre_propietario'] = $nombreDoctor;
                } else {
                    // Si no hay información de doctor, usar ID del doctor directamente
                    $resultado['nombre_propietario'] = 'Doctor ID: ' . $resultado['creado_por'];
                }
            }
            
            return $resultado;
        } catch (PDOException $e) {
            error_log("Error al obtener preformato por ID: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Crea un nuevo motivo común
     * @param array $datos Datos del motivo común
     * @return string 'ok' si se creó correctamente, 'error' en caso contrario
     */
    public static function mdlCrearMotivoComun($datos) {
        try {
            $stmt = Conexion::conectar()->prepare(
                "INSERT INTO motivos_comunes (nombre, descripcion, creado_por) 
                VALUES (:nombre, :descripcion, :creado_por)"
            );
            
            $stmt->bindParam(":nombre", $datos['nombre'], PDO::PARAM_STR);
            $stmt->bindParam(":descripcion", $datos['descripcion'], PDO::PARAM_STR);
            $stmt->bindParam(":creado_por", $datos['creado_por'], PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                return "ok";
            } else {
                return "error";
            }
        } catch (PDOException $e) {
            error_log("Error al crear motivo común: " . $e->getMessage());
            return "error";
        }
    }
    
    /**
     * Crea un nuevo preformato
     * @param array $datos Datos del preformato
     * @return string 'ok' si se creó correctamente, 'error' en caso contrario
     */
    public static function mdlCrearPreformato($datos) {
        try {
            $tipoFormulario = isset($datos['tipo_formulario']) ? $datos['tipo_formulario'] : 'general';
            
            $stmt = Conexion::conectar()->prepare(
                "INSERT INTO preformatos (nombre, contenido, tipo, tipo_formulario, creado_por) 
                VALUES (:nombre, :contenido, :tipo, :tipo_formulario, :creado_por)"
            );
            
            $stmt->bindParam(":nombre", $datos['nombre'], PDO::PARAM_STR);
            $stmt->bindParam(":contenido", $datos['contenido'], PDO::PARAM_STR);
            $stmt->bindParam(":tipo", $datos['tipo'], PDO::PARAM_STR);
            $stmt->bindParam(":tipo_formulario", $tipoFormulario, PDO::PARAM_STR);
            $stmt->bindParam(":creado_por", $datos['creado_por'], PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                return "ok";
            } else {
                return "error";
            }
        } catch (PDOException $e) {
            error_log("Error al crear preformato: " . $e->getMessage());
            return "error";
        }
    }
    
    /**
     * Actualiza un preformato existente
     * @param array $datos Datos del preformato
     * @return string 'ok' si se actualizó correctamente, 'error' en caso contrario
     */
    public static function mdlActualizarPreformato($datos) {
        try {
            $tipoFormulario = isset($datos['tipo_formulario']) ? $datos['tipo_formulario'] : 'general';
            
            $stmt = Conexion::conectar()->prepare(
                "UPDATE preformatos 
                SET nombre = :nombre, contenido = :contenido, tipo = :tipo, tipo_formulario = :tipo_formulario, creado_por = :creado_por 
                WHERE id_preformato = :id_preformato"
            );
            
            $stmt->bindParam(":nombre", $datos['nombre'], PDO::PARAM_STR);
            $stmt->bindParam(":contenido", $datos['contenido'], PDO::PARAM_STR);
            $stmt->bindParam(":tipo", $datos['tipo'], PDO::PARAM_STR);
            $stmt->bindParam(":tipo_formulario", $tipoFormulario, PDO::PARAM_STR);
            $stmt->bindParam(":creado_por", $datos['creado_por'], PDO::PARAM_INT);
            $stmt->bindParam(":id_preformato", $datos['id_preformato'], PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                return "ok";
            } else {
                return "error";
            }
        } catch (PDOException $e) {
            error_log("Error al actualizar preformato: " . $e->getMessage());
            return "error";
        }
    }
    
    /**
     * Elimina un preformato (inactivación lógica)
     * @param int $idPreformato ID del preformato a eliminar
     * @return string 'ok' si se eliminó correctamente, 'error' en caso contrario
     */
    public static function mdlEliminarPreformato($idPreformato) {
        try {
            $stmt = Conexion::conectar()->prepare(
                "UPDATE preformatos 
                SET activo = false 
                WHERE id_preformato = :id_preformato"
            );
            
            $stmt->bindParam(":id_preformato", $idPreformato, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                return "ok";
            } else {
                return "error";
            }
        } catch (PDOException $e) {
            error_log("Error al eliminar preformato: " . $e->getMessage());
            return "error";
        }
    }
}