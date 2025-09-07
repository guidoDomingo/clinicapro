<?php
// Determinar la ruta correcta según desde dónde se llama
if (file_exists("conexion.php")) {
    require_once "conexion.php";
} elseif (file_exists("model/conexion.php")) {
    require_once "model/conexion.php";
} else {
    // Intenta con ruta absoluta
    require_once dirname(__FILE__) . "/conexion.php";
}

class ModelAgendas {
    /**
     * Obtiene todas las agendas médicas
     * @return array Listado de agendas
     */
    static public function mdlObtenerAgendas() {
        $stmt = Conexion::conectar()->prepare(
            "SELECT a.agenda_id, a.medico_id, a.agenda_descripcion, a.agenda_estado, 
                    d.first_name || ' ' || d.last_name AS nombre_medico
             FROM agendas_cabecera a
             INNER JOIN rh_doctors rd ON a.medico_id = rd.doctor_id
             INNER JOIN rh_person d ON rd.person_id = d.person_id
             ORDER BY a.agenda_id DESC"
        );

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene una agenda específica por ID
     * @param int $agendaId ID de la agenda
     * @return array Datos de la agenda
     */
    static public function mdlObtenerAgendaPorId($agendaId) {
        $stmt = Conexion::conectar()->prepare(
            "SELECT a.agenda_id, a.medico_id, a.agenda_descripcion, a.agenda_estado, 
                    d.first_name || ' ' || d.last_name AS nombre_medico
             FROM agendas_cabecera a
             INNER JOIN rh_doctors rd ON a.medico_id = rd.doctor_id
             INNER JOIN rh_person d ON rd.person_id = d.person_id
             WHERE a.agenda_id = :agenda_id"
        );

        $stmt->bindParam(":agenda_id", $agendaId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene agendas por médico
     * @param int $medicoId ID del médico
     * @return array Listado de agendas del médico
     */
    static public function mdlObtenerAgendasPorMedico($medicoId) {
        $stmt = Conexion::conectar()->prepare(
            "SELECT a.agenda_id, a.medico_id, a.agenda_descripcion, a.agenda_estado, 
                    d.first_name || ' ' || d.last_name AS nombre_medico
             FROM agendas_cabecera a
             INNER JOIN rh_doctors rd ON a.medico_id = rd.doctor_id
             INNER JOIN rh_person d ON rd.person_id = d.person_id
             WHERE a.medico_id = :medico_id
             ORDER BY a.agenda_id DESC"
        );

        $stmt->bindParam(":medico_id", $medicoId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Crea una nueva agenda
     * @param array $datos Datos de la agenda
     * @return mixed ID de la agenda creada o false en caso de error
     */
    static public function mdlCrearAgenda($datos) {
        try {
            $stmt = Conexion::conectar()->prepare(
                "INSERT INTO agendas_cabecera (medico_id, agenda_descripcion, agenda_estado) 
                 VALUES (:medico_id, :agenda_descripcion, :agenda_estado) 
                 RETURNING agenda_id"
            );

            $stmt->bindParam(":medico_id", $datos["medico_id"], PDO::PARAM_INT);
            $stmt->bindParam(":agenda_descripcion", $datos["agenda_descripcion"], PDO::PARAM_STR);
            $stmt->bindParam(":agenda_estado", $datos["agenda_estado"], PDO::PARAM_BOOL);

            if ($stmt->execute()) {
                $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
                return $resultado["agenda_id"];
            } else {
                return false;
            }
        } catch (Exception $e) {
            // Registrar el error
            error_log("Error al crear agenda: " . $e->getMessage(), 0);
            return false;
        }
    }

    /**
     * Actualiza una agenda existente
     * @param array $datos Datos de la agenda
     * @return bool Resultado de la operación
     */
    static public function mdlActualizarAgenda($datos) {
        try {
            $stmt = Conexion::conectar()->prepare(
                "UPDATE agendas_cabecera 
                 SET medico_id = :medico_id, 
                     agenda_descripcion = :agenda_descripcion, 
                     agenda_estado = :agenda_estado,
                     fecha_modificacion = CURRENT_TIMESTAMP
                 WHERE agenda_id = :agenda_id"
            );

            $stmt->bindParam(":agenda_id", $datos["agenda_id"], PDO::PARAM_INT);
            $stmt->bindParam(":medico_id", $datos["medico_id"], PDO::PARAM_INT);
            $stmt->bindParam(":agenda_descripcion", $datos["agenda_descripcion"], PDO::PARAM_STR);
            $stmt->bindParam(":agenda_estado", $datos["agenda_estado"], PDO::PARAM_BOOL);

            return $stmt->execute();
        } catch (Exception $e) {
            // Registrar el error
            error_log("Error al actualizar agenda: " . $e->getMessage(), 0);
            return false;
        }
    }

    /**
     * Elimina una agenda
     * @param int $agendaId ID de la agenda
     * @return bool Resultado de la operación
     */
    static public function mdlEliminarAgenda($agendaId) {
        try {
            $stmt = Conexion::conectar()->prepare("DELETE FROM agendas_cabecera WHERE agenda_id = :agenda_id");
            $stmt->bindParam(":agenda_id", $agendaId, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (Exception $e) {
            // Registrar el error
            error_log("Error al eliminar agenda: " . $e->getMessage(), 0);
            return false;
        }
    }

    /**
     * Obtiene los detalles de horarios de una agenda
     * @param int $agendaId ID de la agenda
     * @return array Listado de detalles de horarios
     */
    static public function mdlObtenerDetallesAgenda($agendaId) {
        $stmt = Conexion::conectar()->prepare(
            "SELECT d.detalle_id, d.agenda_id, d.turno_id, d.sala_id, d.servicio_id, d.dia_semana, 
                    d.hora_inicio, d.hora_fin, d.intervalo_minutos, d.cupo_maximo, d.detalle_estado,
                    t.turno_nombre AS nombre_turno, s.sala_nombre AS nombre_sala,
                    rs.serv_descripcion AS nombre_servicio
             FROM agendas_detalle d
             INNER JOIN turnos t ON d.turno_id = t.turno_id
             INNER JOIN salas s ON d.sala_id = s.sala_id
             LEFT JOIN rs_servicios rs ON d.servicio_id = rs.serv_id
             WHERE d.agenda_id = :agenda_id
             ORDER BY CASE d.dia_semana 
                        WHEN 'LUNES'::dia_semana_enum THEN 1
                        WHEN 'MARTES'::dia_semana_enum THEN 2
                        WHEN 'MIERCOLES'::dia_semana_enum THEN 3
                        WHEN 'JUEVES'::dia_semana_enum THEN 4
                        WHEN 'VIERNES'::dia_semana_enum THEN 5
                        WHEN 'SABADO'::dia_semana_enum THEN 6
                        WHEN 'DOMINGO'::dia_semana_enum THEN 7
                      END, d.hora_inicio"
        );

        $stmt->bindParam(":agenda_id", $agendaId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtiene un detalle específico de agenda por ID
     * @param int $detalleId ID del detalle
     * @return array Datos del detalle
     */
    static public function mdlObtenerDetalleAgenda($detalleId) {
        $stmt = Conexion::conectar()->prepare(
            "SELECT d.detalle_id, d.agenda_id, d.turno_id, d.sala_id, d.servicio_id, d.dia_semana, 
                    d.hora_inicio, d.hora_fin, d.intervalo_minutos, d.cupo_maximo, d.detalle_estado,
                    t.turno_nombre AS nombre_turno, s.sala_nombre AS nombre_sala,
                    rs.serv_descripcion AS nombre_servicio
             FROM agendas_detalle d
             INNER JOIN turnos t ON d.turno_id = t.turno_id
             INNER JOIN salas s ON d.sala_id = s.sala_id
             LEFT JOIN rs_servicios rs ON d.servicio_id = rs.serv_id
             WHERE d.detalle_id = :detalle_id"
        );

        $stmt->bindParam(":detalle_id", $detalleId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Crea un nuevo detalle de horario para una agenda
     * @param array $datos Datos del horario
     * @return mixed ID del detalle creado o false en caso de error
     */
    static public function mdlCrearDetalleAgenda($datos) {
        try {
            $stmt = Conexion::conectar()->prepare(
                "INSERT INTO agendas_detalle 
                 (agenda_id, turno_id, sala_id, servicio_id, dia_semana, hora_inicio, hora_fin, 
                  intervalo_minutos, cupo_maximo, detalle_estado) 
                 VALUES 
                 (:agenda_id, :turno_id, :sala_id, :servicio_id, :dia_semana, :hora_inicio, :hora_fin, 
                  :intervalo_minutos, :cupo_maximo, :detalle_estado) 
                 RETURNING detalle_id"
            );

            $stmt->bindParam(":agenda_id", $datos["agenda_id"], PDO::PARAM_INT);
            $stmt->bindParam(":turno_id", $datos["turno_id"], PDO::PARAM_INT);
            $stmt->bindParam(":sala_id", $datos["sala_id"], PDO::PARAM_INT);
            $stmt->bindParam(":servicio_id", $datos["servicio_id"], PDO::PARAM_INT);
            $stmt->bindParam(":dia_semana", $datos["dia_semana"], PDO::PARAM_STR);
            $stmt->bindParam(":hora_inicio", $datos["hora_inicio"], PDO::PARAM_STR);
            $stmt->bindParam(":hora_fin", $datos["hora_fin"], PDO::PARAM_STR);
            $stmt->bindParam(":intervalo_minutos", $datos["intervalo_minutos"], PDO::PARAM_INT);
            $stmt->bindParam(":cupo_maximo", $datos["cupo_maximo"], PDO::PARAM_INT);
            $stmt->bindParam(":detalle_estado", $datos["detalle_estado"], PDO::PARAM_BOOL);

            if ($stmt->execute()) {
                $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
                return $resultado["detalle_id"];
            } else {
                return false;
            }
        } catch (Exception $e) {
            // Registrar el error
            error_log("Error al crear detalle de agenda: " . $e->getMessage(), 0);
            return false;
        }
    }

    /**
     * Actualiza un detalle de horario existente
     * @param array $datos Datos del horario
     * @return bool Resultado de la operación
     */
    static public function mdlActualizarDetalleAgenda($datos) {
        try {
            $stmt = Conexion::conectar()->prepare(
                "UPDATE agendas_detalle 
                 SET turno_id = :turno_id, 
                     sala_id = :sala_id, 
                     servicio_id = :servicio_id,
                     dia_semana = :dia_semana, 
                     hora_inicio = :hora_inicio, 
                     hora_fin = :hora_fin, 
                     intervalo_minutos = :intervalo_minutos, 
                     cupo_maximo = :cupo_maximo, 
                     detalle_estado = :detalle_estado,
                     fecha_modificacion = CURRENT_TIMESTAMP
                 WHERE detalle_id = :detalle_id"
            );

            $stmt->bindParam(":detalle_id", $datos["detalle_id"], PDO::PARAM_INT);
            $stmt->bindParam(":turno_id", $datos["turno_id"], PDO::PARAM_INT);
            $stmt->bindParam(":sala_id", $datos["sala_id"], PDO::PARAM_INT);
            $stmt->bindParam(":servicio_id", $datos["servicio_id"], PDO::PARAM_INT);
            $stmt->bindParam(":dia_semana", $datos["dia_semana"], PDO::PARAM_STR);
            $stmt->bindParam(":hora_inicio", $datos["hora_inicio"], PDO::PARAM_STR);
            $stmt->bindParam(":hora_fin", $datos["hora_fin"], PDO::PARAM_STR);
            $stmt->bindParam(":intervalo_minutos", $datos["intervalo_minutos"], PDO::PARAM_INT);
            $stmt->bindParam(":cupo_maximo", $datos["cupo_maximo"], PDO::PARAM_INT);
            $stmt->bindParam(":detalle_estado", $datos["detalle_estado"], PDO::PARAM_BOOL);

            $result = $stmt->execute();
            
            if (!$result) {
                // Log PDO error info
                $errorInfo = $stmt->errorInfo();
                $errorMsg = "PDO Error al actualizar detalle: " . json_encode($errorInfo) . " - Datos: " . json_encode($datos);
                error_log($errorMsg, 3, "c:/laragon/www/clinica/logs/database.log");
            }
            
            return $result;
        } catch (Exception $e) {
            // Registrar el error con más detalle
            $errorMsg = "Error al actualizar detalle de agenda: " . $e->getMessage() . " - Datos: " . json_encode($datos);
            error_log($errorMsg, 3, "c:/laragon/www/clinica/logs/database.log");
            return false;
        }
    }

    /**
     * Elimina un detalle de horario
     * @param int $detalleId ID del detalle
     * @return bool Resultado de la operación
     */
    static public function mdlEliminarDetalleAgenda($detalleId) {
        try {
            $stmt = Conexion::conectar()->prepare("DELETE FROM agendas_detalle WHERE detalle_id = :detalle_id");
            $stmt->bindParam(":detalle_id", $detalleId, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (Exception $e) {
            // Registrar el error
            error_log("Error al eliminar detalle de agenda: " . $e->getMessage(), 0);
            return false;
        }
    }

    /**
     * Obtiene todos los médicos disponibles
     * @return array Listado de médicos
     */
    static public function mdlObtenerMedicos() {
        $stmt = Conexion::conectar()->prepare(
            "SELECT d.doctor_id, p.person_id, p.first_name || ' ' || p.last_name AS nombre_completo
             FROM rh_doctors d
             INNER JOIN rh_person p ON d.person_id = p.person_id
             WHERE d.doctor_estado = 'ACTIVO' AND p.is_active = true
             ORDER BY p.first_name, p.last_name"
        );

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene todos los turnos disponibles
     * @return array Listado de turnos
     */
    static public function mdlObtenerTurnos() {
        $stmt = Conexion::conectar()->prepare(
            "SELECT turno_id, turno_nombre, turno_descripcion
             FROM turnos
             WHERE turno_estado = true
             ORDER BY turno_nombre"
        );

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene todas las salas disponibles
     * @return array Listado de salas
     */
    static public function mdlObtenerSalas() {
        $stmt = Conexion::conectar()->prepare(
            "SELECT sala_id, sala_codigo, sala_nombre, sala_descripcion
             FROM salas
             WHERE sala_estado = true
             ORDER BY sala_nombre"
        );

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Verifica si existe un horario duplicado
     * @param int $detalleId ID del detalle actual (para excluirlo de la verificación)
     * @param string $diaSemana Día de la semana (número del 0 al 6)
     * @param int $turnoId ID del turno
     * @param int $salaId ID de la sala
     * @param string $horaInicio Hora de inicio
     * @param string $horaFin Hora de fin
     * @return array|bool Información del duplicado o false si no existe
     */
    static public function mdlVerificarHorarioDuplicado($detalleId, $diaSemana, $turnoId, $salaId, $horaInicio, $horaFin) {
        try {
            // Si diaSemana es numérico, convertir a texto, sino usar directamente
            if (is_numeric($diaSemana)) {
                $diasSemana = ['DOMINGO', 'LUNES', 'MARTES', 'MIERCOLES', 'JUEVES', 'VIERNES', 'SABADO'];
                $diaSemanaTexto = $diasSemana[$diaSemana];
            } else {
                // Ya es un string, usar directamente
                $diaSemanaTexto = strtoupper($diaSemana);
            }
            
            // Asegurarnos de que detalleId sea un entero válido
            $detalleId = !empty($detalleId) ? intval($detalleId) : 0;
            
            // Asegurarnos de que turnoId y salaId sean enteros válidos
            $turnoId = !empty($turnoId) ? intval($turnoId) : 0;
            $salaId = !empty($salaId) ? intval($salaId) : 0;
            
            $stmt = Conexion::conectar()->prepare("
                SELECT 
                    ad.detalle_id, 
                    ad.agenda_id,
                    ad.dia_semana, 
                    ad.turno_id,
                    ad.sala_id,
                    ad.hora_inicio,
                    ad.hora_fin,
                    ac.medico_id,
                    rp.first_name || ' ' || rp.last_name AS doctor_nombre,
                    t.turno_descripcion AS turno_nombre,
                    s.sala_descripcion AS sala_nombre
                FROM 
                    agendas_detalle ad
                INNER JOIN 
                    agendas_cabecera ac ON ad.agenda_id = ac.agenda_id
                INNER JOIN 
                    rh_doctors rd ON ac.medico_id = rd.doctor_id
                INNER JOIN 
                    rh_person rp ON rd.person_id = rp.person_id
                LEFT JOIN
                    turnos t ON ad.turno_id = t.turno_id
                LEFT JOIN
                    salas s ON ad.sala_id = s.sala_id
                WHERE 
                    ad.detalle_id != :detalle_id
                AND
                    ad.dia_semana = :dia_semana
                AND (
                    -- Verifica superposición de horarios
                    (ad.hora_inicio <= :hora_inicio AND ad.hora_fin > :hora_inicio)
                    OR
                    (ad.hora_inicio < :hora_fin AND ad.hora_fin >= :hora_fin)
                    OR 
                    (:hora_inicio <= ad.hora_inicio AND :hora_fin >= ad.hora_fin)
                )
                AND (
                    -- Coincidencia exacta de turno y sala
                    (ad.turno_id = :turno_id AND ad.sala_id = :sala_id)
                    OR
                    -- O mismo turno (aunque la sala sea diferente)
                    (ad.turno_id = :turno_id)
                    OR
                    -- O misma sala (aunque el turno sea diferente)
                    (ad.sala_id = :sala_id)
                )
            ");

            $stmt->bindParam(":detalle_id", $detalleId, PDO::PARAM_INT);
            $stmt->bindParam(":dia_semana", $diaSemanaTexto, PDO::PARAM_STR);
            $stmt->bindParam(":turno_id", $turnoId, PDO::PARAM_INT);
            $stmt->bindParam(":sala_id", $salaId, PDO::PARAM_INT);
            $stmt->bindParam(":hora_inicio", $horaInicio, PDO::PARAM_STR);
            $stmt->bindParam(":hora_fin", $horaFin, PDO::PARAM_STR);

            $stmt->execute();
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($resultado) {
                // Si encontró un conflicto, devuelve los datos del horario duplicado
                return [
                    'duplicado' => true,
                    'detalle_id' => $resultado['detalle_id'],
                    'agenda_id' => $resultado['agenda_id'],
                    'doctor_id' => $resultado['medico_id'],
                    'doctor_nombre' => $resultado['doctor_nombre'],
                    'dia_semana' => $resultado['dia_semana'],
                    'turno_id' => $resultado['turno_id'],
                    'turno_nombre' => $resultado['turno_nombre'],
                    'sala_id' => $resultado['sala_id'],
                    'sala_nombre' => $resultado['sala_nombre'],
                    'hora_inicio' => $resultado['hora_inicio'],
                    'hora_fin' => $resultado['hora_fin'],
                    'mensaje' => 'Ya existe un horario con características similares.'
                ];
            }

            // Si no hay duplicados, devuelve false
            return ['duplicado' => false];
        } catch (PDOException $e) {
            // En caso de error, registrar y devolver false
            error_log("Error al verificar horario duplicado: " . $e->getMessage());
            return [
                'duplicado' => false,
                'error' => true, 
                'mensaje' => 'Error al verificar duplicados: ' . $e->getMessage()
            ];
        }
    }

    /**
     * ========== SERVICIOS POR DOCTOR ==========
     */

    /**
     * Obtiene todos los servicios disponibles
     * @return array Listado de servicios
     */
    static public function mdlObtenerServicios() {
        try {
            $stmt = Conexion::conectar()->prepare(
                "SELECT serv_id, serv_codigo, serv_descripcion, 
                       serv_descripcion_factura, serv_monto, is_active
                 FROM rs_servicios 
                 WHERE is_active = true
                 ORDER BY serv_descripcion"
            );

            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error al obtener servicios: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene todas las asociaciones servicio-doctor con filtros opcionales
     * @param array $filtros Filtros opcionales (medico_id, servicio_id)
     * @return array Listado de asociaciones servicio-doctor
     */
    static public function mdlObtenerServiciosDoctor($filtros = []) {
        try {
            $sql = "SELECT 
                        rsd.id,
                        rsd.servicio_id,
                        rsd.doctor_id,
                        rsd.is_active,
                        rsd.created_at,
                        rs.serv_descripcion as servicio_nombre,
                        CONCAT(rp.first_name, ' ', rp.last_name) as medico_nombre,
                        STRING_AGG(DISTINCT ad.dia_semana::text, ', ') as dias_disponibles,
                        COUNT(DISTINCT ad.detalle_id) as horarios_configurados
                    FROM rs_servicios_doctors rsd
                    INNER JOIN rs_servicios rs ON rsd.servicio_id = rs.serv_id
                    INNER JOIN rh_doctors rd ON rsd.doctor_id = rd.doctor_id
                    INNER JOIN rh_person rp ON rd.person_id = rp.person_id
                    LEFT JOIN agendas_cabecera ac ON rsd.doctor_id = ac.medico_id
                    LEFT JOIN agendas_detalle ad ON ac.agenda_id = ad.agenda_id AND ad.detalle_estado = true
                    WHERE rsd.is_active = true AND rp.is_active = true";

            $params = [];

            if (isset($filtros['medico_id']) && !empty($filtros['medico_id'])) {
                $sql .= " AND rsd.doctor_id = :medico_id";
                $params['medico_id'] = $filtros['medico_id'];
            }

            if (isset($filtros['servicio_id']) && !empty($filtros['servicio_id'])) {
                $sql .= " AND rsd.servicio_id = :servicio_id";
                $params['servicio_id'] = $filtros['servicio_id'];
            }

            $sql .= " GROUP BY rsd.id, rsd.servicio_id, rsd.doctor_id, rsd.is_active, rsd.created_at, 
                              rs.serv_descripcion, rp.first_name, rp.last_name
                      ORDER BY rsd.created_at DESC";

            $stmt = Conexion::conectar()->prepare($sql);

            foreach ($params as $key => $value) {
                $stmt->bindParam(":$key", $value, PDO::PARAM_INT);
            }

            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error al obtener servicios por doctor: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene una asociación servicio-doctor específica por ID
     * @param int $id ID de la asociación
     * @return array Datos de la asociación
     */
    static public function mdlObtenerServicioDoctorPorId($id) {
        try {
            $stmt = Conexion::conectar()->prepare(
                "SELECT 
                    rsd.id,
                    rsd.servicio_id,
                    rsd.doctor_id,
                    rsd.is_active,
                    rsd.created_at,
                    rs.serv_descripcion as servicio_nombre,
                    (rp.first_name || ' ' || rp.last_name) as medico_nombre
                 FROM rs_servicios_doctors rsd
                 INNER JOIN rs_servicios rs ON rsd.servicio_id = rs.serv_id
                 INNER JOIN rh_doctors rd ON rsd.doctor_id = rd.doctor_id
                 INNER JOIN rh_person rp ON rd.person_id = rp.person_id
                 WHERE rsd.id = :id"
            );

            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            $stmt->execute();
            
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

            // Obtener horarios específicos si existen
            if ($resultado) {
                $stmtHorarios = Conexion::conectar()->prepare(
                    "SELECT dia_semana, hora_inicio, hora_fin, intervalo_minutos, cupo_maximo
                     FROM agendas_detalle ad
                     INNER JOIN agendas_cabecera ac ON ad.agenda_id = ac.agenda_id
                     WHERE ac.medico_id = :doctor_id 
                     AND ad.detalle_estado = true
                     ORDER BY 
                        CASE ad.dia_semana
                            WHEN 'LUNES' THEN 1
                            WHEN 'MARTES' THEN 2
                            WHEN 'MIERCOLES' THEN 3
                            WHEN 'JUEVES' THEN 4
                            WHEN 'VIERNES' THEN 5
                            WHEN 'SABADO' THEN 6
                            WHEN 'DOMINGO' THEN 7
                        END, ad.hora_inicio"
                );
                
                $stmtHorarios->bindParam(":doctor_id", $resultado['doctor_id'], PDO::PARAM_INT);
                $stmtHorarios->execute();
                $resultado['horarios_especificos'] = $stmtHorarios->fetchAll(PDO::FETCH_ASSOC);
            }

            return $resultado;
        } catch (Exception $e) {
            error_log("Error al obtener servicio-doctor por ID: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Verifica si ya existe una asociación servicio-doctor
     * @param int $doctorId ID del doctor
     * @param int $servicioId ID del servicio
     * @return bool True si existe, false si no
     */
    static public function mdlVerificarAsociacionExistente($doctorId, $servicioId) {
        try {
            $stmt = Conexion::conectar()->prepare(
                "SELECT COUNT(*) as total 
                 FROM rs_servicios_doctors 
                 WHERE doctor_id = :doctor_id AND servicio_id = :servicio_id"
            );

            $stmt->bindParam(":doctor_id", $doctorId, PDO::PARAM_INT);
            $stmt->bindParam(":servicio_id", $servicioId, PDO::PARAM_INT);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total'] > 0;
        } catch (Exception $e) {
            error_log("Error al verificar asociación existente: " . $e->getMessage());
            return true; // Por seguridad, asumimos que existe
        }
    }

    /**
     * Crea una nueva asociación servicio-doctor
     * @param array $datos Datos de la asociación
     * @return int|bool ID de la nueva asociación o false en caso de error
     */
    static public function mdlCrearServicioDoctor($datos) {
        $pdo = Conexion::conectar();
        
        try {
            $pdo->beginTransaction();

            // Insertar asociación principal
            $stmt = $pdo->prepare(
                "INSERT INTO rs_servicios_doctors (servicio_id, doctor_id, agenda_detalle_id, is_active, created_at, updated_at)
                 VALUES (:servicio_id, :doctor_id, :agenda_detalle_id, :is_active, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)
                 RETURNING id"
            );

            $stmt->bindParam(":servicio_id", $datos["servicio_id"], PDO::PARAM_INT);
            $stmt->bindParam(":doctor_id", $datos["doctor_id"], PDO::PARAM_INT);
            $stmt->bindParam(":agenda_detalle_id", $datos["agenda_detalle_id"], PDO::PARAM_INT);
            $stmt->bindParam(":is_active", $datos["is_active"], PDO::PARAM_BOOL);
            $stmt->execute();
            
            $asociacionId = $stmt->fetchColumn();

            // Si hay horarios específicos, crear entradas en agendas_detalle
            if (!empty($datos["horarios_especificos"])) {
                self::mdlGuardarHorariosEspecificos($datos["doctor_id"], $datos["horarios_especificos"], $pdo);
            }

            $pdo->commit();
            return $asociacionId;
        } catch (Exception $e) {
            $pdo->rollBack();
            error_log("Error al crear servicio-doctor: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualiza una asociación servicio-doctor
     * @param array $datos Datos de la asociación
     * @return bool True en caso de éxito, false en caso de error
     */
    static public function mdlActualizarServicioDoctor($datos) {
        $pdo = Conexion::conectar();
        
        try {
            $pdo->beginTransaction();

            // Actualizar asociación principal
            $stmt = $pdo->prepare(
                "UPDATE rs_servicios_doctors 
                 SET servicio_id = :servicio_id, 
                     doctor_id = :doctor_id, 
                     is_active = :is_active, 
                     updated_at = CURRENT_TIMESTAMP
                 WHERE id = :id"
            );

            $stmt->bindParam(":id", $datos["id"], PDO::PARAM_INT);
            $stmt->bindParam(":servicio_id", $datos["servicio_id"], PDO::PARAM_INT);
            $stmt->bindParam(":doctor_id", $datos["doctor_id"], PDO::PARAM_INT);
            $stmt->bindParam(":is_active", $datos["is_active"], PDO::PARAM_BOOL);
            $stmt->execute();

            // Actualizar horarios específicos si los hay
            if (!empty($datos["horarios_especificos"])) {
                self::mdlActualizarHorariosEspecificos($datos["doctor_id"], $datos["horarios_especificos"], $pdo);
            }

            $pdo->commit();
            return true;
        } catch (Exception $e) {
            $pdo->rollBack();
            error_log("Error al actualizar servicio-doctor: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Elimina una asociación servicio-doctor
     * @param int $id ID de la asociación
     * @return bool True en caso de éxito, false en caso de error
     */
    static public function mdlEliminarServicioDoctor($id) {
        try {
            $stmt = Conexion::conectar()->prepare(
                "DELETE FROM rs_servicios_doctors WHERE id = :id"
            );

            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error al eliminar servicio-doctor: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Guarda horarios específicos para un doctor
     * @param int $doctorId ID del doctor
     * @param array $horarios Horarios específicos
     * @param PDO $pdo Conexión a la base de datos
     */
    private static function mdlGuardarHorariosEspecificos($doctorId, $horarios, $pdo) {
        // Primero obtener o crear agenda para el doctor
        $stmtAgenda = $pdo->prepare(
            "SELECT agenda_id FROM agendas_cabecera WHERE medico_id = :medico_id AND agenda_estado = true LIMIT 1"
        );
        $stmtAgenda->bindParam(":medico_id", $doctorId, PDO::PARAM_INT);
        $stmtAgenda->execute();
        
        $agendaId = $stmtAgenda->fetchColumn();
        
        if (!$agendaId) {
            // Crear nueva agenda para el doctor
            $stmtCrearAgenda = $pdo->prepare(
                "INSERT INTO agendas_cabecera (medico_id, agenda_descripcion, agenda_estado)
                 VALUES (:medico_id, 'Agenda automática', true)
                 RETURNING agenda_id"
            );
            $stmtCrearAgenda->bindParam(":medico_id", $doctorId, PDO::PARAM_INT);
            $stmtCrearAgenda->execute();
            $agendaId = $stmtCrearAgenda->fetchColumn();
        }

        // Insertar horarios específicos
        foreach ($horarios as $horario) {
            $stmtDetalle = $pdo->prepare(
                "INSERT INTO agendas_detalle 
                 (agenda_id, dia_semana, turno_id, sala_id, hora_inicio, hora_fin, intervalo_minutos, cupo_maximo, detalle_estado)
                 VALUES (:agenda_id, :dia_semana, 1, 1, :hora_inicio, :hora_fin, :intervalo_minutos, :cupo_maximo, true)"
            );

            $stmtDetalle->bindParam(":agenda_id", $agendaId, PDO::PARAM_INT);
            $stmtDetalle->bindParam(":dia_semana", $horario["dia_semana"], PDO::PARAM_STR);
            $stmtDetalle->bindParam(":hora_inicio", $horario["hora_inicio"], PDO::PARAM_STR);
            $stmtDetalle->bindParam(":hora_fin", $horario["hora_fin"], PDO::PARAM_STR);
            $stmtDetalle->bindParam(":intervalo_minutos", $horario["intervalo_minutos"], PDO::PARAM_INT);
            $stmtDetalle->bindParam(":cupo_maximo", $horario["cupo_maximo"], PDO::PARAM_INT);
            $stmtDetalle->execute();
        }
    }

    /**
     * Actualiza horarios específicos para un doctor
     * @param int $doctorId ID del doctor
     * @param array $horarios Horarios específicos
     * @param PDO $pdo Conexión a la base de datos
     */
    private static function mdlActualizarHorariosEspecificos($doctorId, $horarios, $pdo) {
        // Eliminar horarios existentes del doctor
        $stmtEliminar = $pdo->prepare(
            "DELETE FROM agendas_detalle 
             WHERE agenda_id IN (SELECT agenda_id FROM agendas_cabecera WHERE medico_id = :medico_id)"
        );
        $stmtEliminar->bindParam(":medico_id", $doctorId, PDO::PARAM_INT);
        $stmtEliminar->execute();

        // Crear nuevos horarios
        if (!empty($horarios)) {
            self::mdlGuardarHorariosEspecificos($doctorId, $horarios, $pdo);
        }
    }

    /**
     * Verifica si existe la relación servicio-doctor para un detalle específico
     * @param int $servicio_id ID del servicio
     * @param int $doctor_id ID del doctor
     * @param int $agenda_detalle_id ID del detalle de agenda
     * @return bool True si existe la relación
     */
    static public function mdlVerificarRelacionServicioDoctor($servicio_id, $doctor_id, $agenda_detalle_id = null) {
        try {
            if ($agenda_detalle_id) {
                // Verificar relación específica por detalle
                $stmt = Conexion::conectar()->prepare(
                    "SELECT COUNT(*) as count 
                     FROM rs_servicios_doctors 
                     WHERE servicio_id = :servicio_id 
                     AND doctor_id = :doctor_id 
                     AND agenda_detalle_id = :agenda_detalle_id
                     AND is_active = true"
                );
                $stmt->bindParam(":agenda_detalle_id", $agenda_detalle_id, PDO::PARAM_INT);
            } else {
                // Verificar relación general servicio-doctor
                $stmt = Conexion::conectar()->prepare(
                    "SELECT COUNT(*) as count 
                     FROM rs_servicios_doctors 
                     WHERE servicio_id = :servicio_id 
                     AND doctor_id = :doctor_id 
                     AND is_active = true"
                );
            }
            
            $stmt->bindParam(":servicio_id", $servicio_id, PDO::PARAM_INT);
            $stmt->bindParam(":doctor_id", $doctor_id, PDO::PARAM_INT);
            $stmt->execute();
            
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            return $resultado['count'] > 0;
        } catch (Exception $e) {
            error_log("Error al verificar relación servicio-doctor: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Elimina relaciones servicio-doctor por agenda_detalle_id
     * @param int $agenda_detalle_id ID del detalle de agenda
     * @return bool True si se elimina correctamente
     */
    static public function mdlEliminarRelacionPorDetalleId($agenda_detalle_id) {
        try {
            $stmt = Conexion::conectar()->prepare(
                "DELETE FROM rs_servicios_doctors 
                 WHERE agenda_detalle_id = :agenda_detalle_id"
            );
            
            $stmt->bindParam(":agenda_detalle_id", $agenda_detalle_id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error al eliminar relación por detalle ID: " . $e->getMessage());
            return false;
        }
    }
}