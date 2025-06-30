<?php
/**
 * Modelo para reservas públicas
 * Adapta las funciones existentes del modelo de servicios
 */

require_once __DIR__ . "/../../model/conexion.php";
require_once __DIR__ . "/../../model/servicios.model.php"; // Modelo de servicios (clase ModelServicios)

class ReservasPublicModel {
    
    /**
     * Obtiene todos los servicios disponibles
     * @return array Lista de servicios disponibles
     */
    static public function mdlObtenerServicios() {
        return ModelServicios::mdlObtenerTodosRsServicios();
    }
    
    /**
     * Obtiene los médicos disponibles para una fecha específica
     * @param string $fecha Fecha en formato YYYY-MM-DD
     * @return array Lista de médicos disponibles
     */
    static public function mdlObtenerMedicosDisponibles($fecha) {
        return ModelServicios::mdlObtenerMedicosDisponiblesPorFecha($fecha);
    }
    
    /**
     * Obtiene los horarios disponibles para un médico y fecha específicos
     * @param string $fecha Fecha en formato YYYY-MM-DD
     * @param int $doctorId ID del doctor
     * @return array Horarios disponibles
     */
    static public function mdlObtenerHorariosDisponibles($fecha, $doctorId) {
        return ModelServicios::mdlObtenerServiciosPorFechaMedico($fecha, $doctorId);
    }
    
    /**
     * Verifica las reservas existentes para un médico y fecha
     * @param string $fecha Fecha en formato YYYY-MM-DD
     * @param int $doctorId ID del doctor
     * @return array Reservas existentes
     */
    static public function mdlVerificarReservasExistentes($fecha, $doctorId) {
        // Verificar formato de la fecha y hacer log
        error_log("mdlVerificarReservasExistentes: Verificando reservas para fecha=$fecha, doctorId=$doctorId", 3, "c:/laragon/www/clinica/logs/public_reservas.log");
        
        // Obtener las reservas existentes
        $reservas = ModelServicios::mdlObtenerReservasPorFecha($fecha, $doctorId);
        
        // Hacer log de los resultados para depuración
        error_log("mdlVerificarReservasExistentes: Se encontraron " . count($reservas) . " reservas existentes", 3, "c:/laragon/www/clinica/logs/public_reservas.log");
        if (!empty($reservas)) {
            error_log("mdlVerificarReservasExistentes: Primera reserva: " . json_encode($reservas[0]), 3, "c:/laragon/www/clinica/logs/public_reservas.log");
        }
        
        return $reservas;
    }
    
    /**
     * Crea un paciente temporal o vincula uno existente
     * @param array $datos Datos del paciente
     * @return int ID del paciente
     */
    static public function mdlGuardarPaciente($datos) {
        // Verificar si ya existe un paciente con este documento
        if (!empty($datos["document_number"])) {
            $paciente = self::mdlBuscarPacientePorDocumento($datos["document_number"]);
            if ($paciente) {
                return $paciente["person_id"];
            }
        }
        
        // Si no existe, crear nuevo
        return ModelServicios::mdlGuardarNuevoPaciente($datos);
    }
    
    /**
     * Busca un paciente por su número de documento
     * @param string $documento Número de documento
     * @return array|bool Datos del paciente o false si no existe
     */
    static public function mdlBuscarPacientePorDocumento($documento) {
        try {
            $stmt = Conexion::conectar()->prepare(
                "SELECT 
                    person_id, 
                    first_name, 
                    last_name, 
                    document_number,
                    email,
                    phone
                FROM 
                    rh_person 
                WHERE 
                    document_number = :documento
                LIMIT 1"
            );
            
            $stmt->bindParam(":documento", $documento, PDO::PARAM_STR);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al buscar paciente por documento: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtiene los datos de un paciente por su ID
     * @param int $pacienteId ID del paciente
     * @return array|bool Datos del paciente o false si no existe
     */
    static public function mdlObtenerPacientePorId($pacienteId) {
        try {
            $stmt = Conexion::conectar()->prepare(
                "SELECT 
                    p.person_id, 
                    p.first_name AS nombre, 
                    p.last_name AS apellido, 
                    p.document_number AS documento,
                    p.email,
                    p.phone AS telefono,
                    pa.ultimo_login
                FROM 
                    rh_person p
                LEFT JOIN 
                    reservas_pacientes_auth pa ON p.person_id = pa.paciente_id
                WHERE 
                    p.person_id = :paciente_id
                LIMIT 1"
            );
            
            $stmt->bindParam(":paciente_id", $pacienteId, PDO::PARAM_INT);
            $stmt->execute();
            
            // Log para depuración
            error_log("mdlObtenerPacientePorId: Buscando paciente con ID $pacienteId", 3, "c:/laragon/www/clinica/logs/auth.log");
            
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($resultado) {
                error_log("mdlObtenerPacientePorId: Paciente encontrado: " . $resultado['nombre'] . ' ' . $resultado['apellido'], 3, "c:/laragon/www/clinica/logs/auth.log");
            } else {
                error_log("mdlObtenerPacientePorId: No se encontró paciente con ID $pacienteId", 3, "c:/laragon/www/clinica/logs/auth.log");
            }
            
            return $resultado;
        } catch (PDOException $e) {
            error_log("mdlObtenerPacientePorId: Error al buscar paciente: " . $e->getMessage(), 3, "c:/laragon/www/clinica/logs/auth.log");
            return false;
        }
    }
    
    /**
     * Guarda una reserva en el sistema
     * @param array $datos Datos de la reserva
     * @return mixed ID de la reserva o false en caso de error
     */
    static public function mdlGuardarReserva($datos) {
        return ModelServicios::mdlGuardarReserva($datos);
    }
    
    /**
     * Obtiene los proveedores de seguro médico
     * @return array Lista de proveedores
     */
    static public function mdlObtenerSeguros() {
        return ModelServicios::mdlObtenerProveedoresSeguro();
    }
    
    /**
     * Busca reservas por código de seguimiento
     * @param string $codigo Código de seguimiento
     * @return array Datos de la reserva
     */
    static public function mdlBuscarReservaCodigo($codigo) {
        try {
            $stmt = Conexion::conectar()->prepare(
                "SELECT 
                    sr.reserva_id,
                    sr.fecha_reserva,
                    sr.hora_inicio,
                    sr.hora_fin,
                    sr.reserva_estado,
                    sr.codigo_seguimiento,
                    s.serv_descripcion as servicio_nombre,
                    s.serv_monto as monto_servicio,
                    CONCAT(dr.first_name, ' ', dr.last_name) as nombre_doctor,
                    CONCAT(p.first_name, ' ', p.last_name) as nombre_paciente,
                    p.email as email_paciente,
                    p.phone as telefono_paciente,
                    pa.prov_razon as seguro_nombre
                FROM 
                    servicios_reservas sr
                INNER JOIN 
                    rs_servicios s ON sr.servicio_id = s.serv_id
                INNER JOIN 
                    rh_doctors d ON sr.doctor_id = d.doctor_id
                INNER JOIN 
                    rh_person dr ON d.person_id = dr.person_id
                INNER JOIN 
                    rh_person p ON sr.paciente_id = p.person_id
                LEFT JOIN 
                    cm_proveedores_acreedores pa ON sr.seguro_id = pa.prov_id
                WHERE 
                    sr.codigo_seguimiento = :codigo"
            );
            
            $stmt->bindParam(":codigo", $codigo, PDO::PARAM_STR);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al buscar reserva por código: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Guarda un código de verificación para el paciente
     * @param int $pacienteId ID del paciente
     * @param string $codigo Código de verificación
     * @param string $email Email del paciente
     * @return bool Resultado de la operación
     */
    static public function mdlGuardarCodigoVerificacion($pacienteId, $codigo, $email) {
        try {
            // Verificar si ya existe un registro para este paciente
            $stmtCheck = Conexion::conectar()->prepare(
                "SELECT COUNT(*) FROM rh_verificacion WHERE paciente_id = :paciente_id"
            );
            $stmtCheck->bindParam(":paciente_id", $pacienteId, PDO::PARAM_INT);
            $stmtCheck->execute();
            
            if ($stmtCheck->fetchColumn() > 0) {
                // Actualizar registro existente
                $stmt = Conexion::conectar()->prepare(
                    "UPDATE rh_verificacion 
                     SET codigo = :codigo, 
                         fecha_creacion = NOW(), 
                         verificado = false,
                         email = :email
                     WHERE paciente_id = :paciente_id"
                );
            } else {
                // Crear nuevo registro
                $stmt = Conexion::conectar()->prepare(
                    "INSERT INTO rh_verificacion 
                     (paciente_id, codigo, fecha_creacion, verificado, email)
                     VALUES
                     (:paciente_id, :codigo, NOW(), false, :email)"
                );
            }
            
            $stmt->bindParam(":paciente_id", $pacienteId, PDO::PARAM_INT);
            $stmt->bindParam(":codigo", $codigo, PDO::PARAM_STR);
            $stmt->bindParam(":email", $email, PDO::PARAM_STR);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error al guardar código de verificación: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Verifica si un código de verificación es válido
     * @param int $pacienteId ID del paciente
     * @param string $codigo Código de verificación
     * @return bool Resultado de la verificación
     */
    static public function mdlVerificarCodigo($pacienteId, $codigo) {
        try {
            $stmt = Conexion::conectar()->prepare(
                "SELECT * FROM rh_verificacion 
                 WHERE paciente_id = :paciente_id
                 AND codigo = :codigo
                 AND fecha_creacion > NOW() - INTERVAL '24 HOURS'"
            );
            
            $stmt->bindParam(":paciente_id", $pacienteId, PDO::PARAM_INT);
            $stmt->bindParam(":codigo", $codigo, PDO::PARAM_STR);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                // Marcar código como verificado
                $update = Conexion::conectar()->prepare(
                    "UPDATE rh_verificacion 
                     SET verificado = true 
                     WHERE paciente_id = :paciente_id
                     AND codigo = :codigo"
                );
                
                $update->bindParam(":paciente_id", $pacienteId, PDO::PARAM_INT);
                $update->bindParam(":codigo", $codigo, PDO::PARAM_STR);
                $update->execute();
                
                return true;
            }
            
            return false;
        } catch (PDOException $e) {
            error_log("Error al verificar código: " . $e->getMessage());
            return false;
        }
    }
    
    /* 
     * MÉTODOS PARA AUTENTICACIÓN DE USUARIOS
     */
    
    /**
     * Verifica las credenciales de un usuario
     * @param string $email Email del usuario
     * @param string $password Contraseña del usuario
     * @return array Resultado de la verificación
     */
    static public function mdlVerificarUsuario($email, $password) {
        error_log("mdlVerificarUsuario: Verificando usuario con email $email", 3, "c:/laragon/www/clinica/logs/auth.log");
        
        try {
            // Primero buscamos en la tabla de pacientes registrados
            $stmt = Conexion::conectar()->prepare(
                "SELECT 
                    pr.paciente_id,
                    pr.email,
                    pr.password,
                    p.first_name || ' ' || p.last_name AS nombre
                FROM 
                    reservas_pacientes_auth pr
                LEFT JOIN 
                    rh_person p ON pr.paciente_id = p.person_id
                WHERE 
                    pr.email = :email
                LIMIT 1"
            );
            
            $stmt->bindParam(":email", $email, PDO::PARAM_STR);
            $stmt->execute();
            
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$usuario) {
                error_log("mdlVerificarUsuario: Usuario no encontrado con email $email", 3, "c:/laragon/www/clinica/logs/auth.log");
                return [
                    'error' => true,
                    'mensaje' => 'El email no está registrado en el sistema'
                ];
            }
            
            // Verificar la contraseña
            if (password_verify($password, $usuario['password'])) {
                error_log("mdlVerificarUsuario: Contraseña correcta para usuario ID " . $usuario['paciente_id'], 3, "c:/laragon/www/clinica/logs/auth.log");
                
                // Actualizar último login
                $updateStmt = Conexion::conectar()->prepare(
                    "UPDATE reservas_pacientes_auth 
                     SET ultimo_login = CURRENT_TIMESTAMP 
                     WHERE paciente_id = :paciente_id"
                );
                
                $updateStmt->bindParam(":paciente_id", $usuario['paciente_id'], PDO::PARAM_INT);
                $updateStmt->execute();
                
                return [
                    'error' => false,
                    'paciente_id' => $usuario['paciente_id'],
                    'email' => $usuario['email'],
                    'nombre' => $usuario['nombre']
                ];
            } else {
                error_log("mdlVerificarUsuario: Contraseña incorrecta para email $email", 3, "c:/laragon/www/clinica/logs/auth.log");
                return [
                    'error' => true,
                    'mensaje' => 'Contraseña incorrecta'
                ];
            }
        } catch (PDOException $e) {
            error_log("mdlVerificarUsuario: Error de base de datos: " . $e->getMessage(), 3, "c:/laragon/www/clinica/logs/auth.log");
            return [
                'error' => true,
                'mensaje' => 'Error en el proceso de login: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Guarda un token de "recordarme" para un usuario
     * @param int $pacienteId ID del paciente
     * @param string $token Token de autenticación
     * @return bool Resultado de la operación
     */
    static public function mdlGuardarToken($pacienteId, $token) {
        error_log("mdlGuardarToken: Guardando token para paciente ID $pacienteId", 3, "c:/laragon/www/clinica/logs/auth.log");
        
        try {
            // Primero eliminamos cualquier token anterior
            self::mdlEliminarToken($pacienteId);
            
            // Luego guardamos el nuevo token
            $stmt = Conexion::conectar()->prepare(
                "INSERT INTO reservas_auth_tokens 
                 (paciente_id, token, fecha_creacion, fecha_expiracion)
                 VALUES
                 (:paciente_id, :token, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP + INTERVAL '30 days')"
            );
            
            $stmt->bindParam(":paciente_id", $pacienteId, PDO::PARAM_INT);
            $stmt->bindParam(":token", $token, PDO::PARAM_STR);
            
            $resultado = $stmt->execute();
            
            error_log("mdlGuardarToken: Token guardado: " . ($resultado ? 'sí' : 'no'), 3, "c:/laragon/www/clinica/logs/auth.log");
            
            return $resultado;
        } catch (PDOException $e) {
            error_log("mdlGuardarToken: Error al guardar token: " . $e->getMessage(), 3, "c:/laragon/www/clinica/logs/auth.log");
            return false;
        }
    }
    
    /**
     * Verifica si un email ya está registrado
     * @param string $email Email a verificar
     * @return bool True si el email ya existe, false en caso contrario
     */
    static public function mdlVerificarEmailExistente($email) {
        error_log("mdlVerificarEmailExistente: Verificando si el email $email ya está registrado", 3, "c:/laragon/www/clinica/logs/auth.log");
        
        try {
            $stmt = Conexion::conectar()->prepare(
                "SELECT COUNT(*) FROM reservas_pacientes_auth WHERE email = :email"
            );
            
            $stmt->bindParam(":email", $email, PDO::PARAM_STR);
            $stmt->execute();
            
            $existe = ($stmt->fetchColumn() > 0);
            
            error_log("mdlVerificarEmailExistente: Email ya existe: " . ($existe ? 'sí' : 'no'), 3, "c:/laragon/www/clinica/logs/auth.log");
            
            return $existe;
        } catch (PDOException $e) {
            error_log("mdlVerificarEmailExistente: Error al verificar email: " . $e->getMessage(), 3, "c:/laragon/www/clinica/logs/auth.log");
            return false;
        }
    }
    
    /**
     * Registra un nuevo usuario en el sistema
     * @param array $datos Datos del usuario
     * @return array Resultado del registro
     */
    static public function mdlRegistrarUsuario($datos) {
        error_log("mdlRegistrarUsuario: Registrando nuevo usuario con email " . $datos['email'], 3, "c:/laragon/www/clinica/logs/auth.log");
        
        try {
            // Iniciar una transacción para asegurar la integridad de los datos
            $db = Conexion::conectar();
            $db->beginTransaction();
            
            // Primero creamos el registro en rh_person
            $stmtPerson = $db->prepare(
                "INSERT INTO rh_person 
                 (first_name, last_name, document_number, email, phone, created_at)
                 VALUES 
                 (:first_name, :last_name, :document_number, :email, :phone, CURRENT_TIMESTAMP)
                 RETURNING person_id"
            );
            
            $stmtPerson->bindParam(":first_name", $datos['nombre'], PDO::PARAM_STR);
            $stmtPerson->bindParam(":last_name", $datos['apellido'], PDO::PARAM_STR);
            $stmtPerson->bindParam(":document_number", $datos['documento'], PDO::PARAM_STR);
            $stmtPerson->bindParam(":email", $datos['email'], PDO::PARAM_STR);
            $stmtPerson->bindParam(":phone", $datos['telefono'], PDO::PARAM_STR);
            
            $stmtPerson->execute();
            $personId = $stmtPerson->fetchColumn();
            
            if (!$personId) {
                $db->rollBack();
                error_log("mdlRegistrarUsuario: Error al crear registro en rh_person", 3, "c:/laragon/www/clinica/logs/auth.log");
                return [
                    'error' => true,
                    'mensaje' => 'Error al crear el registro de persona'
                ];
            }
            
            // Ahora creamos el registro de autenticación
            $stmtAuth = $db->prepare(
                "INSERT INTO reservas_pacientes_auth 
                 (paciente_id, email, password, fecha_registro, ultimo_login)
                 VALUES 
                 (:paciente_id, :email, :password, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)"
            );
            
            $stmtAuth->bindParam(":paciente_id", $personId, PDO::PARAM_INT);
            $stmtAuth->bindParam(":email", $datos['email'], PDO::PARAM_STR);
            $stmtAuth->bindParam(":password", $datos['password'], PDO::PARAM_STR);
            
            if (!$stmtAuth->execute()) {
                $db->rollBack();
                error_log("mdlRegistrarUsuario: Error al crear registro de autenticación", 3, "c:/laragon/www/clinica/logs/auth.log");
                return [
                    'error' => true,
                    'mensaje' => 'Error al crear el registro de autenticación'
                ];
            }
            
            // Si todo está bien, confirmamos la transacción
            $db->commit();
            
            error_log("mdlRegistrarUsuario: Registro exitoso para usuario con ID $personId", 3, "c:/laragon/www/clinica/logs/auth.log");
            
            return [
                'error' => false,
                'paciente_id' => $personId,
                'mensaje' => 'Usuario registrado con éxito'
            ];
        } catch (PDOException $e) {
            // En caso de error, revertimos la transacción
            if (isset($db)) {
                $db->rollBack();
            }
            
            error_log("mdlRegistrarUsuario: Error de base de datos: " . $e->getMessage(), 3, "c:/laragon/www/clinica/logs/auth.log");
            
            return [
                'error' => true,
                'mensaje' => 'Error en el proceso de registro: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Elimina un token de autenticación
     * @param int $pacienteId ID del paciente
     * @return bool Resultado de la operación
     */
    static public function mdlEliminarToken($pacienteId) {
        error_log("mdlEliminarToken: Eliminando token para paciente ID $pacienteId", 3, "c:/laragon/www/clinica/logs/auth.log");
        
        try {
            $stmt = Conexion::conectar()->prepare(
                "DELETE FROM reservas_auth_tokens WHERE paciente_id = :paciente_id"
            );
            
            $stmt->bindParam(":paciente_id", $pacienteId, PDO::PARAM_INT);
            $resultado = $stmt->execute();
            
            error_log("mdlEliminarToken: Token eliminado: " . ($resultado ? 'sí' : 'no'), 3, "c:/laragon/www/clinica/logs/auth.log");
            
            return $resultado;
        } catch (PDOException $e) {
            error_log("mdlEliminarToken: Error al eliminar token: " . $e->getMessage(), 3, "c:/laragon/www/clinica/logs/auth.log");
            return false;
        }
    }
    
    /**
     * Verifica si un token es válido
     * @param string $token Token a verificar
     * @return array|bool Datos del usuario o false si el token no es válido
     */
    static public function mdlVerificarToken($token) {
        error_log("mdlVerificarToken: Verificando token", 3, "c:/laragon/www/clinica/logs/auth.log");
        
        try {
            $stmt = Conexion::conectar()->prepare(
                "SELECT 
                    t.paciente_id,
                    t.token,
                    p.email,
                    p.first_name || ' ' || p.last_name AS nombre
                FROM 
                    reservas_auth_tokens t
                INNER JOIN
                    reservas_pacientes_auth a ON t.paciente_id = a.paciente_id
                INNER JOIN
                    rh_person p ON t.paciente_id = p.person_id
                WHERE 
                    t.token = :token
                    AND t.fecha_expiracion > CURRENT_TIMESTAMP
                LIMIT 1"
            );
            
            $stmt->bindParam(":token", $token, PDO::PARAM_STR);
            $stmt->execute();
            
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($resultado) {
                error_log("mdlVerificarToken: Token válido para usuario ID " . $resultado['paciente_id'], 3, "c:/laragon/www/clinica/logs/auth.log");
                return [
                    'error' => false,
                    'paciente_id' => $resultado['paciente_id'],
                    'email' => $resultado['email'],
                    'nombre' => $resultado['nombre']
                ];
            } else {
                error_log("mdlVerificarToken: Token inválido", 3, "c:/laragon/www/clinica/logs/auth.log");
                return [
                    'error' => true,
                    'mensaje' => 'Token inválido o expirado'
                ];
            }
        } catch (PDOException $e) {
            error_log("mdlVerificarToken: Error al verificar token: " . $e->getMessage(), 3, "c:/laragon/www/clinica/logs/auth.log");
            return [
                'error' => true,
                'mensaje' => 'Error al verificar token: ' . $e->getMessage()
            ];
        }
    }
}
?>
