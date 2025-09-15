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
                    phone_number
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
                    p.phone_number AS telefono,
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
     * Obtiene los datos completos de un usuario desde rh_person usando la relación con sys_users
     * @param int $userId ID del usuario del sistema
     * @return array|bool Datos completos del paciente o false si no existe
     */
    static public function mdlObtenerPacienteDesdeUsuario($userId) {
        try {
            $stmt = Conexion::conectar()->prepare(
                "SELECT 
                    rp.*
                FROM 
                    sys_users su 
                INNER JOIN 
                    person_system_user psu ON su.user_id = psu.system_user_id  
                INNER JOIN 
                    rh_person rp ON rp.person_id = psu.person_id 
                WHERE 
                    su.user_id = :user_id
                LIMIT 1"
            );
            
            $stmt->bindParam(":user_id", $userId, PDO::PARAM_INT);
            $stmt->execute();
            
            // Log para depuración
            error_log("mdlObtenerPacienteDesdeUsuario: Buscando perfil de usuario con ID $userId", 3, "c:/laragon/www/clinica/logs/auth.log");
            
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($resultado) {
                error_log("mdlObtenerPacienteDesdeUsuario: Perfil encontrado para person_id: " . $resultado['person_id'], 3, "c:/laragon/www/clinica/logs/auth.log");
                error_log("mdlObtenerPacienteDesdeUsuario: Datos completos: " . json_encode($resultado), 3, "c:/laragon/www/clinica/logs/auth.log");
            } else {
                error_log("mdlObtenerPacienteDesdeUsuario: No se encontró perfil para el usuario con ID $userId", 3, "c:/laragon/www/clinica/logs/auth.log");
            }
            
            return $resultado;
        } catch (PDOException $e) {
            error_log("mdlObtenerPacienteDesdeUsuario: Error al buscar perfil: " . $e->getMessage(), 3, "c:/laragon/www/clinica/logs/auth.log");
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
     * Actualmente solo registra en el log, sin usar tabla de verificación.
     * @param int $pacienteId ID del paciente
     * @param string $codigo Código de verificación
     * @param string $email Email del paciente
     * @return bool Resultado de la operación
     */
    static public function mdlGuardarCodigoVerificacion($pacienteId, $codigo, $email) {
        // Para la implementación actual, solo registramos en el log
        // En el futuro se implementará usando una tabla dedicada
        if (!empty($codigo)) {
            error_log("mdlGuardarCodigoVerificacion: Guardando código en log.", 3, "c:/laragon/www/clinica/logs/verificacion.log");
            error_log("mdlGuardarCodigoVerificacion: Paciente ID: $pacienteId, Email: $email, Código: $codigo", 3, "c:/laragon/www/clinica/logs/verificacion.log");
        }
        
        // Siempre retornamos true ya que no necesitamos verificación por ahora
        return true;
    }
    
    /**
     * Verifica si un código de verificación es válido
     * Actualmente siempre retorna true ya que no usamos verificación.
     * @param int $pacienteId ID del paciente
     * @param string $codigo Código de verificación
     * @return bool Resultado de la verificación
     */
    static public function mdlVerificarCodigo($pacienteId, $codigo) {
        try {
            // Para la implementación actual, solo registramos en el log
            // y siempre retornamos verdadero ya que no necesitamos verificación
            if (!empty($codigo)) {
                error_log("mdlVerificarCodigo: Verificación automática sin tabla.", 3, "c:/laragon/www/clinica/logs/verificacion.log");
                error_log("mdlVerificarCodigo: Paciente ID: $pacienteId, Código: $codigo - Auto-verificado", 3, "c:/laragon/www/clinica/logs/verificacion.log");
            }
            
            // Siempre retornamos true ya que no necesitamos verificación por ahora
            return true;
        } catch (PDOException $e) {
            error_log("Error al verificar código: " . $e->getMessage(), 3, "c:/laragon/www/clinica/logs/verificacion.log");
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
            // Buscar usuario en las tablas del sistema principal
            $stmt = Conexion::conectar()->prepare(
                "SELECT 
                    su.user_id,
                    su.user_email,
                    su.user_pass,
                    su.user_is_active,
                    sr.reg_name || ' ' || sr.reg_lastname AS nombre,
                    p.person_id
                FROM 
                    sys_users su
                INNER JOIN 
                    sys_register sr ON su.reg_id = sr.reg_id
                LEFT JOIN 
                    person_system_user psu ON su.user_id = psu.system_user_id
                LEFT JOIN 
                    rh_person p ON psu.person_id = p.person_id
                WHERE 
                    su.user_email = :email AND su.user_is_active = true
                LIMIT 1"
            );
            
            $stmt->bindParam(":email", $email, PDO::PARAM_STR);
            $stmt->execute();
            
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$usuario) {
                error_log("mdlVerificarUsuario: Usuario no encontrado con email $email", 3, "c:/laragon/www/clinica/logs/auth.log");
                return [
                    'error' => true,
                    'mensaje' => 'Credenciales inválidas'
                ];
            }
            
            // Verificar la contraseña usando MD5 (igual que el sistema base)
            $passwordMD5 = md5($password);
            error_log("mdlVerificarUsuario: Comparando MD5($password) = $passwordMD5 con BD: " . $usuario['user_pass'], 3, "c:/laragon/www/clinica/logs/auth.log");
            
            if ($passwordMD5 === $usuario['user_pass']) {
                error_log("mdlVerificarUsuario: Contraseña correcta para usuario ID " . $usuario['user_id'], 3, "c:/laragon/www/clinica/logs/auth.log");
                
                // Actualizar último login
                $updateStmt = Conexion::conectar()->prepare(
                    "UPDATE sys_users 
                     SET user_last_login = CURRENT_TIMESTAMP 
                     WHERE user_id = :user_id"
                );
                
                $updateStmt->bindParam(":user_id", $usuario['user_id'], PDO::PARAM_INT);
                $updateStmt->execute();
                
                return [
                    'error' => false,
                    'paciente_id' => $usuario['user_id'],
                    'person_id' => $usuario['person_id'],
                    'email' => $usuario['user_email'],
                    'nombre' => $usuario['nombre']
                ];
            } else {
                error_log("mdlVerificarUsuario: Contraseña incorrecta para email $email", 3, "c:/laragon/www/clinica/logs/auth.log");
                return [
                    'error' => true,
                    'mensaje' => 'Credenciales inválidas'
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
                "SELECT COUNT(*) FROM sys_register WHERE reg_email = :email"
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
     * Verifica si un documento ya está registrado en el sistema
     * @param string $documento Documento a verificar
     * @return bool True si el documento ya existe, false en caso contrario
     */
    static public function mdlVerificarDocumentoExistente($documento) {
        error_log("mdlVerificarDocumentoExistente: Verificando si el documento $documento ya está registrado", 3, "c:/laragon/www/clinica/logs/auth.log");
        
        try {
            $stmt = Conexion::conectar()->prepare(
                "SELECT COUNT(*) FROM sys_register WHERE reg_document = :documento"
            );
            
            $stmt->bindParam(":documento", $documento, PDO::PARAM_STR);
            $stmt->execute();
            
            $existe = ($stmt->fetchColumn() > 0);
            
            error_log("mdlVerificarDocumentoExistente: Documento ya existe: " . ($existe ? 'sí' : 'no'), 3, "c:/laragon/www/clinica/logs/auth.log");
            
            return $existe;
        } catch (PDOException $e) {
            error_log("mdlVerificarDocumentoExistente: Error al verificar documento: " . $e->getMessage(), 3, "c:/laragon/www/clinica/logs/auth.log");
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
                 (first_name, last_name, document_number, email, phone_number, birth_date, created_at)
                 VALUES 
                 (:first_name, :last_name, :document_number, :email, :phone_number, :birth_date, CURRENT_TIMESTAMP)
                 RETURNING person_id"
            );
            
            $stmtPerson->bindParam(":first_name", $datos['nombre'], PDO::PARAM_STR);
            $stmtPerson->bindParam(":last_name", $datos['apellido'], PDO::PARAM_STR);
            $stmtPerson->bindParam(":document_number", $datos['documento'], PDO::PARAM_STR);
            $stmtPerson->bindParam(":email", $datos['email'], PDO::PARAM_STR);
            $stmtPerson->bindParam(":phone_number", $datos['telefono'], PDO::PARAM_STR);
            $stmtPerson->bindParam(":birth_date", $datos['fecha_nacimiento'], PDO::PARAM_STR);
            
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
            
            // 1. Primero insertamos en sys_register (esto activará el trigger que crea sys_users)
            $stmtRegister = $db->prepare(
                "INSERT INTO sys_register 
                 (reg_document, reg_name, reg_lastname, reg_email, reg_phone, reg_bdate, reg_activation)
                 VALUES 
                 (:reg_document, :reg_name, :reg_lastname, :reg_email, :reg_phone, :reg_bdate, 'pending')
                 RETURNING reg_id"
            );
            
            $stmtRegister->bindParam(":reg_document", $datos['documento'], PDO::PARAM_STR);
            $stmtRegister->bindParam(":reg_name", $datos['nombre'], PDO::PARAM_STR);
            $stmtRegister->bindParam(":reg_lastname", $datos['apellido'], PDO::PARAM_STR);
            $stmtRegister->bindParam(":reg_email", $datos['email'], PDO::PARAM_STR);
            $stmtRegister->bindParam(":reg_phone", $datos['telefono'], PDO::PARAM_STR);
            $stmtRegister->bindParam(":reg_bdate", $datos['fecha_nacimiento'], PDO::PARAM_STR);
            
            $stmtRegister->execute();
            $regId = $stmtRegister->fetchColumn();
            
            if (!$regId) {
                $db->rollBack();
                error_log("mdlRegistrarUsuario: Error al crear registro en sys_register", 3, "c:/laragon/www/clinica/logs/auth.log");
                return [
                    'error' => true,
                    'mensaje' => 'Error al crear el registro de usuario'
                ];
            }
            
            // 2. Activar el usuario en sys_users (el trigger ya creó el usuario con contraseña MD5)
            $stmtUser = $db->prepare(
                "UPDATE sys_users 
                 SET user_is_active = true, user_expire = NOW() + INTERVAL '1 year'
                 WHERE reg_id = :reg_id
                 RETURNING user_id"
            );
            
            $stmtUser->bindParam(":reg_id", $regId, PDO::PARAM_INT);
            
            $stmtUser->execute();
            $userId = $stmtUser->fetchColumn();
            
            if (!$userId) {
                $db->rollBack();
                error_log("mdlRegistrarUsuario: Error al activar usuario en sys_users", 3, "c:/laragon/www/clinica/logs/auth.log");
                return [
                    'error' => true,
                    'mensaje' => 'Error al activar usuario'
                ];
            }
            
            // 3. Crear el perfil en rh_person
            $stmtPerson = $db->prepare(
                "INSERT INTO rh_person 
                 (first_name, last_name, document_number, email, phone_number, birth_date, created_at, is_active)
                 VALUES 
                 (:first_name, :last_name, :document_number, :email, :phone_number, :birth_date, CURRENT_TIMESTAMP, true)
                 RETURNING person_id"
            );
            
            $stmtPerson->bindParam(":first_name", $datos['nombre'], PDO::PARAM_STR);
            $stmtPerson->bindParam(":last_name", $datos['apellido'], PDO::PARAM_STR);
            $stmtPerson->bindParam(":document_number", $datos['documento'], PDO::PARAM_STR);
            $stmtPerson->bindParam(":email", $datos['email'], PDO::PARAM_STR);
            $stmtPerson->bindParam(":phone_number", $datos['telefono'], PDO::PARAM_STR);
            $stmtPerson->bindParam(":birth_date", $datos['fecha_nacimiento'], PDO::PARAM_STR);
            
            $stmtPerson->execute();
            $personId = $stmtPerson->fetchColumn();
            
            if (!$personId) {
                $db->rollBack();
                error_log("mdlRegistrarUsuario: Error al crear perfil en rh_person", 3, "c:/laragon/www/clinica/logs/auth.log");
                return [
                    'error' => true,
                    'mensaje' => 'Error al crear el perfil de usuario'
                ];
            }
            
            // 4. Vincular el usuario del sistema con la persona
            $stmtLink = $db->prepare(
                "INSERT INTO person_system_user (person_id, system_user_id, assigned_at)
                 VALUES (:person_id, :system_user_id, CURRENT_TIMESTAMP)"
            );
            
            $stmtLink->bindParam(":person_id", $personId, PDO::PARAM_INT);
            $stmtLink->bindParam(":system_user_id", $userId, PDO::PARAM_INT);
            
            $stmtLink->execute();
            
            // Si todo está bien, confirmamos la transacción
            $db->commit();
            
            error_log("mdlRegistrarUsuario: Registro exitoso para usuario con ID $userId", 3, "c:/laragon/www/clinica/logs/auth.log");
            
            return [
                'error' => false,
                'paciente_id' => $userId,
                'person_id' => $personId,
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
    
    /**
     * Obtiene todas las reservas de un paciente específico
     * @param int $pacienteId ID del paciente (person_id)
     * @return array|bool Lista de reservas o false en caso de error
     */
    static public function mdlObtenerReservasPaciente($pacienteId) {
        try {
            $stmt = Conexion::conectar()->prepare(
                "SELECT 
                    ad.dia_semana,
                    sr.reserva_id,
                    sr.fecha_reserva,
                    sr.hora_inicio || ' - ' || sr.hora_fin as horario,
                    ad.intervalo_minutos,
                    s.sala_nombre,
                    rp.first_name || ' ' || rp.last_name as doctor,
                    rp2.first_name || ' ' || rp2.last_name as paciente,
                    rs.serv_descripcion as nombre_servicio,
                    rs.serv_monto as monto,
                    sr.reserva_estado,
                    'RES' || to_char(sr.created_at, 'YYYYMMDDHH24MISS') || sr.reserva_id as codigo_seguimiento
                FROM 
                    servicios_reservas sr 
                LEFT JOIN 
                    agendas_detalle ad ON sr.agenda_id = ad.detalle_id 
                LEFT JOIN 
                    salas s ON ad.sala_id = s.sala_id
                INNER JOIN 
                    rh_doctors rd ON sr.doctor_id = rd.doctor_id 
                INNER JOIN 
                    rh_person rp ON rd.person_id = rp.person_id
                INNER JOIN 
                    rh_person rp2 ON sr.paciente_id = rp2.person_id 
                INNER JOIN 
                    rs_servicios rs ON sr.servicio_id = rs.serv_id 
                WHERE 
                    rp2.person_id = :paciente_id
                ORDER BY 
                    sr.fecha_reserva DESC, sr.hora_inicio ASC"
            );
            
            $stmt->bindParam(':paciente_id', $pacienteId, PDO::PARAM_INT);
            $stmt->execute();
            
            error_log("mdlObtenerReservasPaciente: Consultando reservas para paciente_id=$pacienteId", 
                3, "c:/laragon/www/clinica/logs/public_reservas.log");
            
            $reservas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            error_log("mdlObtenerReservasPaciente: Se encontraron " . count($reservas) . " reservas", 
                3, "c:/laragon/www/clinica/logs/public_reservas.log");
            
            return $reservas;
            
        } catch (PDOException $e) {
            error_log("Error al obtener reservas del paciente: " . $e->getMessage(), 
                3, "c:/laragon/www/clinica/logs/public_reservas.log");
            return false;
        }
    }
    
    /**
     * Actualiza los datos de perfil de un paciente
     * @param array $datos Datos del paciente
     * @return array Resultado de la operación
     */
    static public function mdlActualizarPerfil($datos) {
        error_log("mdlActualizarPerfil: Actualizando perfil para usuario ID " . $datos['id'], 3, "c:/laragon/www/clinica/logs/auth.log");
        
        try {
            $db = Conexion::conectar();
            $db->beginTransaction();
            
            // Actualizar datos personales en rh_person
            $stmtPerson = $db->prepare(
                "UPDATE rh_person 
                 SET first_name = :nombre,
                     last_name = :apellido,
                     document_number = :documento,
                     email = :email,
                     phone = :telefono,
                     updated_at = CURRENT_TIMESTAMP
                 WHERE person_id = :id"
            );
            
            $stmtPerson->bindParam(":nombre", $datos['nombre'], PDO::PARAM_STR);
            $stmtPerson->bindParam(":apellido", $datos['apellido'], PDO::PARAM_STR);
            $stmtPerson->bindParam(":documento", $datos['documento'], PDO::PARAM_STR);
            $stmtPerson->bindParam(":email", $datos['email'], PDO::PARAM_STR);
            $stmtPerson->bindParam(":telefono", $datos['telefono'], PDO::PARAM_STR);
            $stmtPerson->bindParam(":id", $datos['id'], PDO::PARAM_INT);
            
            if (!$stmtPerson->execute()) {
                $db->rollBack();
                error_log("mdlActualizarPerfil: Error al actualizar datos personales", 3, "c:/laragon/www/clinica/logs/auth.log");
                return [
                    'error' => true,
                    'mensaje' => 'Error al actualizar datos personales'
                ];
            }
            
            // Actualizar email en la tabla de autenticación
            $stmtAuth = $db->prepare(
                "UPDATE reservas_pacientes_auth 
                 SET email = :email
                 WHERE paciente_id = :id"
            );
            
            $stmtAuth->bindParam(":email", $datos['email'], PDO::PARAM_STR);
            $stmtAuth->bindParam(":id", $datos['id'], PDO::PARAM_INT);
            
            if (!$stmtAuth->execute()) {
                $db->rollBack();
                error_log("mdlActualizarPerfil: Error al actualizar email de autenticación", 3, "c:/laragon/www/clinica/logs/auth.log");
                return [
                    'error' => true,
                    'mensaje' => 'Error al actualizar email de autenticación'
                ];
            }
            
            // Si se proporcionó una nueva contraseña, actualizarla
            if (isset($datos['password'])) {
                $stmtPass = $db->prepare(
                    "UPDATE reservas_pacientes_auth 
                     SET password = :password
                     WHERE paciente_id = :id"
                );
                
                $stmtPass->bindParam(":password", $datos['password'], PDO::PARAM_STR);
                $stmtPass->bindParam(":id", $datos['id'], PDO::PARAM_INT);
                
                if (!$stmtPass->execute()) {
                    $db->rollBack();
                    error_log("mdlActualizarPerfil: Error al actualizar contraseña", 3, "c:/laragon/www/clinica/logs/auth.log");
                    return [
                        'error' => true,
                        'mensaje' => 'Error al actualizar contraseña'
                    ];
                }
            }
            
            $db->commit();
            error_log("mdlActualizarPerfil: Perfil actualizado con éxito", 3, "c:/laragon/www/clinica/logs/auth.log");
            
            return [
                'error' => false,
                'mensaje' => 'Perfil actualizado con éxito'
            ];
        } catch (PDOException $e) {
            if (isset($db)) {
                $db->rollBack();
            }
            
            error_log("mdlActualizarPerfil: Error de base de datos: " . $e->getMessage(), 3, "c:/laragon/www/clinica/logs/auth.log");
            
            return [
                'error' => true,
                'mensaje' => 'Error al actualizar perfil: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Guarda la información de un archivo en la base de datos
     * @param array $datosArchivo Datos del archivo a guardar
     * @return int|false ID del archivo guardado o false en caso de error
     */
    static public function mdlGuardarArchivo($datosArchivo) {
        try {
            error_log("mdlGuardarArchivo: Iniciando guardado de archivo: " . $datosArchivo['nombre_original'], 
                3, "c:/laragon/www/clinica/logs/public_reservas.log");
            
            $stmt = Conexion::conectar()->prepare("
                INSERT INTO reserva_archivos 
                (reserva_id, codigo_seguimiento, nombre_original, nombre_archivo, 
                 ruta_archivo, tipo_archivo, tamano_archivo, subido_por, estado)
                VALUES 
                (:reserva_id, :codigo_seguimiento, :nombre_original, :nombre_archivo,
                 :ruta_archivo, :tipo_archivo, :tamano_archivo, :subido_por, :estado)
                RETURNING archivo_id
            ");
            
            $stmt->bindParam(":reserva_id", $datosArchivo['reserva_id'], PDO::PARAM_INT);
            $stmt->bindParam(":codigo_seguimiento", $datosArchivo['codigo_seguimiento'], PDO::PARAM_STR);
            $stmt->bindParam(":nombre_original", $datosArchivo['nombre_original'], PDO::PARAM_STR);
            $stmt->bindParam(":nombre_archivo", $datosArchivo['nombre_archivo'], PDO::PARAM_STR);
            $stmt->bindParam(":ruta_archivo", $datosArchivo['ruta_archivo'], PDO::PARAM_STR);
            $stmt->bindParam(":tipo_archivo", $datosArchivo['tipo_archivo'], PDO::PARAM_STR);
            $stmt->bindParam(":tamano_archivo", $datosArchivo['tamaño_archivo'], PDO::PARAM_INT);
            $stmt->bindParam(":subido_por", $datosArchivo['subido_por'], PDO::PARAM_INT);
            $stmt->bindParam(":estado", $datosArchivo['estado'], PDO::PARAM_STR);
            
            error_log("mdlGuardarArchivo: Ejecutando query con parámetros: " . json_encode($datosArchivo), 
                3, "c:/laragon/www/clinica/logs/public_reservas.log");
            
            if ($stmt->execute()) {
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                $archivoId = $result['archivo_id'];
                
                error_log("mdlGuardarArchivo: ✅ Archivo guardado exitosamente con ID: $archivoId", 
                    3, "c:/laragon/www/clinica/logs/public_reservas.log");
                
                return $archivoId;
            } else {
                error_log("mdlGuardarArchivo: ❌ Error ejecutando statement", 
                    3, "c:/laragon/www/clinica/logs/public_reservas.log");
                return false;
            }
            
        } catch (PDOException $e) {
            error_log("mdlGuardarArchivo: ❌ Error PDO: " . $e->getMessage(), 
                3, "c:/laragon/www/clinica/logs/public_reservas.log");
            return false;
        }
    }
    
    /**
     * Obtiene todos los archivos asociados a una reserva
     * @param int $reservaId ID de la reserva
     * @return array Lista de archivos
     */
    static public function mdlObtenerArchivosPorReserva($reservaId) {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT 
                    archivo_id,
                    reserva_id,
                    codigo_seguimiento,
                    nombre_original,
                    nombre_archivo,
                    ruta_archivo,
                    tipo_archivo,
                    tamaño_archivo,
                    fecha_subida,
                    subido_por,
                    estado
                FROM reserva_archivos 
                WHERE reserva_id = :reserva_id AND estado = 'ACTIVO'
                ORDER BY fecha_subida ASC
            ");
            
            $stmt->bindParam(":reserva_id", $reservaId, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener archivos de reserva: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtiene archivos por código de seguimiento
     * @param string $codigoSeguimiento Código de seguimiento de la reserva
     * @return array Lista de archivos
     */
    static public function mdlObtenerArchivosPorCodigo($codigoSeguimiento) {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT 
                    ra.*,
                    r.reserva_id,
                    r.fecha_reserva,
                    r.hora_inicio,
                    CONCAT(p.first_name, ' ', p.last_name) as paciente_nombre
                FROM reserva_archivos ra
                INNER JOIN reservas r ON ra.reserva_id = r.reserva_id
                INNER JOIN rh_person p ON r.paciente_id = p.person_id
                WHERE ra.codigo_seguimiento = :codigo_seguimiento AND ra.estado = 'ACTIVO'
                ORDER BY ra.fecha_subida ASC
            ");
            
            $stmt->bindParam(":codigo_seguimiento", $codigoSeguimiento, PDO::PARAM_STR);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener archivos por código: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Elimina un archivo (marca como eliminado)
     * @param int $archivoId ID del archivo
     * @param int $usuarioId ID del usuario que elimina
     * @return bool True si se eliminó exitosamente
     */
    static public function mdlEliminarArchivo($archivoId, $usuarioId) {
        try {
            $stmt = Conexion::conectar()->prepare("
                UPDATE reserva_archivos 
                SET estado = 'ELIMINADO', 
                    fecha_subida = CURRENT_TIMESTAMP
                WHERE archivo_id = :archivo_id
            ");
            
            $stmt->bindParam(":archivo_id", $archivoId, PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error al eliminar archivo: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtiene información de un archivo específico
     * @param int $archivoId ID del archivo
     * @return array|false Datos del archivo o false si no existe
     */
    static public function mdlObtenerArchivoPorId($archivoId) {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT * FROM reserva_archivos 
                WHERE archivo_id = :archivo_id AND estado = 'ACTIVO'
            ");
            
            $stmt->bindParam(":archivo_id", $archivoId, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener archivo: " . $e->getMessage());
            return false;
        }
    }
}
?>
