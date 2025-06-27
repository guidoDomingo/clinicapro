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
}
?>
