<?php
require_once "conexion.php";

class ModelServicios {
    /**
     * Obtiene los horarios configurados para un médico
     * @param int $doctorId ID del médico/doctor
     * @return array Listado de horarios del médico
     */    static public function mdlObtenerHorariosMedico($doctorId) {
        try {
            $stmt = Conexion::conectar()->prepare(
                "SELECT 
                    ad.detalle_id,
                    ad.agenda_id,
                    ac.agenda_descripcion,
                    ad.turno_id,
                    t.turno_nombre,
                    ad.sala_id,
                    s.sala_nombre,
                    ad.dia_semana,
                    ad.hora_inicio,
                    ad.hora_fin,
                    ad.intervalo_minutos,
                    ad.cupo_maximo,
                    ad.detalle_estado
                FROM 
                    agendas_detalle ad
                INNER JOIN 
                    agendas_cabecera ac ON ad.agenda_id = ac.agenda_id
                INNER JOIN 
                    turnos t ON ad.turno_id = t.turno_id
                LEFT JOIN 
                    salas s ON ad.sala_id = s.sala_id
                WHERE 
                    ac.medico_id = :doctor_id
                    AND ad.detalle_estado = true
                    AND ac.agenda_estado = true
                ORDER BY
                    CASE ad.dia_semana 
                        WHEN 'LUNES' THEN 1
                        WHEN 'MARTES' THEN 2
                        WHEN 'MIERCOLES' THEN 3
                        WHEN 'JUEVES' THEN 4
                        WHEN 'VIERNES' THEN 5
                        WHEN 'SABADO' THEN 6
                        WHEN 'DOMINGO' THEN 7
                    END,
                    ad.hora_inicio"
            );
            
            $stmt->bindParam(":doctor_id", $doctorId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener horarios del médico: " . $e->getMessage(), 0);
            return [];
        }
    }
    
    /**
     * Obtiene todas las categorías de servicios activas
     * @return array Listado de categorías
     */
    static public function mdlObtenerCategorias() {
        $stmt = Conexion::conectar()->prepare(
            "SELECT categoria_id, categoria_nombre, categoria_descripcion
             FROM servicios_categorias
             WHERE categoria_estado = 'ACTIVO'
             ORDER BY categoria_nombre ASC"
        );

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene todos los servicios médicos activos
     * @param int $categoriaId ID de la categoría para filtrar (opcional)
     * @return array Listado de servicios
     */
    static public function mdlObtenerServicios($categoriaId = null) {
        $sql = "SELECT 
                s.servicio_id, 
                s.categoria_id, 
                c.categoria_nombre,
                s.servicio_codigo, 
                s.servicio_nombre, 
                s.servicio_descripcion, 
                s.duracion_minutos,
                s.precio_base,
                s.requiere_doctor
            FROM 
                servicios_medicos s
            INNER JOIN 
                servicios_categorias c ON s.categoria_id = c.categoria_id
            WHERE 
                s.servicio_estado = 'ACTIVO'";
                
        if ($categoriaId !== null) {
            $sql .= " AND s.categoria_id = :categoria_id";
        }
        
        $sql .= " ORDER BY s.servicio_nombre ASC";
        
        $stmt = Conexion::conectar()->prepare($sql);
        
        if ($categoriaId !== null) {
            $stmt->bindParam(":categoria_id", $categoriaId, PDO::PARAM_INT);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }    /**
     * Obtiene un servicio médico por su ID
     * @param int $servicioId ID del servicio
     * @return array Datos del servicio
     */
    static public function mdlObtenerServicioPorId($servicioId) {
        try {
            $stmt = Conexion::conectar()->prepare(
                "SELECT 
                    serv_id as servicio_id, 
                    serv_codigo as servicio_codigo, 
                    serv_descripcion as servicio_nombre, 
                    30 as duracion_minutos,
                    serv_monto as precio_base
                FROM 
                    rs_servicios
                WHERE 
                    serv_id = :servicio_id"
            );

            $stmt->bindParam(":servicio_id", $servicioId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener servicio por ID: " . $e->getMessage(), 3, 'c:/laragon/www/clinica/logs/servicios.log');
            
            // Si hay un error, devolvemos un array con valores predeterminados
            return [
                'servicio_id' => $servicioId,
                'duracion_minutos' => 30,
                'servicio_nombre' => 'Servicio #' . $servicioId
            ];
        }
    }

    /**
     * Obtiene los requisitos de un servicio médico
     * @param int $servicioId ID del servicio
     * @return array Listado de requisitos
     */
    static public function mdlObtenerRequisitosServicio($servicioId) {
        $stmt = Conexion::conectar()->prepare(
            "SELECT 
                requisito_id, 
                servicio_id,
                requisito_descripcion,
                es_obligatorio,
                orden
            FROM 
                servicios_requisitos
            WHERE 
                servicio_id = :servicio_id
                AND requisito_estado = true
            ORDER BY 
                orden ASC, 
                requisito_id ASC"
        );

        $stmt->bindParam(":servicio_id", $servicioId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene las tarifas disponibles de un servicio médico
     * @param int $servicioId ID del servicio
     * @return array Listado de tarifas
     */
    static public function mdlObtenerTarifasServicio($servicioId) {
        $stmt = Conexion::conectar()->prepare(
            "SELECT 
                tarifa_id,
                servicio_id,
                nombre_tarifa,
                precio,
                descuento_porcentaje
            FROM 
                servicios_tarifas
            WHERE 
                servicio_id = :servicio_id
                AND tarifa_estado = true
                AND (fecha_fin IS NULL OR fecha_fin >= CURRENT_DATE)
            ORDER BY 
                precio ASC"
        );

        $stmt->bindParam(":servicio_id", $servicioId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }    /**
     * Obtiene los proveedores (doctores) de un servicio médico
     * @param int $servicioId ID del servicio
     * @return array Listado de proveedores
     */
    static public function mdlObtenerProveedoresServicio($servicioId) {
        $stmt = Conexion::conectar()->prepare(
            "SELECT 
                sp.proveedor_id,
                sp.servicio_id,
                sp.doctor_id,
                rd.person_id,
                p.first_name || ' ' || p.last_name AS nombre_doctor,
                sp.es_proveedor_principal,
                sp.tarifa_personalizada,
                sp.proveedor_estado
            FROM 
                servicios_proveedores sp
            INNER JOIN 
                rh_doctors rd ON sp.doctor_id = rd.doctor_id
            INNER JOIN 
                rh_person p ON rd.person_id = p.person_id
            WHERE 
                sp.servicio_id = :servicio_id
                AND sp.proveedor_estado = true
            ORDER BY 
                sp.es_proveedor_principal DESC,
                p.first_name, p.last_name"
        );

        $stmt->bindParam(":servicio_id", $servicioId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene las agendas que tienen disponibilidad para un servicio
     * @param int $servicioId ID del servicio
     * @return array Listado de agendas
     */
    static public function mdlObtenerAgendasServicio($servicioId) {
        $stmt = Conexion::conectar()->prepare(
            "SELECT 
                as.agenda_servicio_id,
                as.agenda_id,
                as.servicio_id,
                as.cupo_diario,
                ac.agenda_descripcion,
                ac.medico_id,
                rd.person_id,
                p.first_name || ' ' || p.last_name AS nombre_medico
            FROM 
                agendas_servicios as
            INNER JOIN 
                agendas_cabecera ac ON as.agenda_id = ac.agenda_id
            INNER JOIN 
                rh_doctors rd ON ac.medico_id = rd.doctor_id
            INNER JOIN 
                rh_person p ON rd.person_id = p.person_id
            WHERE 
                as.servicio_id = :servicio_id
                AND as.agenda_servicio_estado = true
                AND ac.agenda_estado = true
            ORDER BY 
                p.first_name, p.last_name"
        );

        $stmt->bindParam(":servicio_id", $servicioId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }    /**     * Obtiene los horarios disponibles para un servicio y doctor específico
     * @param int $servicioId ID del servicio
     * @param int $doctorId ID del doctor
     * @param string $fecha Fecha para la verificación (formato YYYY-MM-DD)
     * @return array Listado de horarios
     */    static public function mdlObtenerHorariosDisponibles($servicioId, $doctorId, $fecha) {
        // Determinar el día de la semana para la fecha
        $fechaObj = DateTime::createFromFormat('Y-m-d', $fecha);
        if (!$fechaObj) {
            error_log("Fecha inválida en mdlObtenerHorariosDisponibles: " . $fecha, 3, 'c:/laragon/www/clinica/logs/database.log');
            return [];
        }
        
        $diaSemanaNum = (int)$fechaObj->format('N'); // 1 (lunes) a 7 (domingo) según ISO-8601
        
        // Mapping directo para los días de la semana
        $diasSemanaTexto = [1 => 'LUNES', 2 => 'MARTES', 3 => 'MIERCOLES', 4 => 'JUEVES', 5 => 'VIERNES', 6 => 'SABADO', 7 => 'DOMINGO'];
        $diaSemanaTexto = $diasSemanaTexto[$diaSemanaNum];        try {
            // Usando la SQL exacta proporcionada por el cliente
            $sql = "SELECT 
                ad.detalle_id as horario_id,
                ad.detalle_id as agenda_id,
                ac.agenda_id as cabecera_agenda_id,
                ad.turno_id,
                t.turno_nombre,
                ad.sala_id,
                s.sala_nombre,
                ac.medico_id as doctor_id,
                coalesce(p.first_name, '') || ' ' || coalesce(p.last_name, '') as nombre_doctor,
                ad.dia_semana,
                ad.hora_inicio,
                ad.hora_fin,
                ad.intervalo_minutos,
                ad.cupo_maximo
            FROM
                agendas_detalle ad
            INNER JOIN 
                agendas_cabecera ac ON ad.agenda_id = ac.agenda_id
            INNER JOIN 
                turnos t ON ad.turno_id = t.turno_id
            INNER JOIN 
                salas s ON ad.sala_id = s.sala_id
            INNER JOIN
                rh_doctors rd ON rd.doctor_id = ac.medico_id 
            INNER JOIN 
                rh_person p ON p.person_id = rd.person_id
            WHERE
                ac.medico_id = :doctor_id
                AND ad.dia_semana = :dia_semana
                AND ad.detalle_estado = true
                AND ac.agenda_estado = true";
            
            // Si se proporcionó un ID de servicio, agregar al WHERE
            if (!empty($servicioId) && $servicioId > 0) {
                $sql .= " AND ac.servicio_id = :servicio_id";
            }
            
            // Agregar ORDER BY
            $sql .= " ORDER BY ad.hora_inicio ASC";
            
            $stmt = Conexion::conectar()->prepare($sql);
            
            // Bind params
            if (!empty($servicioId) && $servicioId > 0) {
                $stmt->bindParam(":servicio_id", $servicioId, PDO::PARAM_INT);
            }            $stmt->bindParam(":doctor_id", $doctorId, PDO::PARAM_INT);
            $stmt->bindParam(":dia_semana", $diaSemanaTexto, PDO::PARAM_STR);
            
            // Log query before execution for debugging
            error_log("mdlObtenerHorariosDisponibles: SQL para DoctorID=$doctorId, Dia=$diaSemanaTexto: $sql", 
                      3, 'c:/laragon/www/clinica/logs/database.log');
            
            $stmt->execute();
            
            $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $conteo = count($resultados);
              // Log detailed results for debugging
            error_log("mdlObtenerHorariosDisponibles: Se encontraron $conteo resultados para DoctorID=$doctorId, Dia=$diaSemanaTexto", 
                      3, 'c:/laragon/www/clinica/logs/database.log');
            
            if ($conteo > 0) {
                error_log("Primer resultado: " . json_encode($resultados[0]), 
                          3, 'c:/laragon/www/clinica/logs/database.log');
                  // Generate detailed time slots based on the time range and interval
                $slotsGenerados = [];
                
                // First, get all existing reservations for this doctor on this day
                $reservasExistentes = self::mdlObtenerReservasExistentes($doctorId, $fecha);
                
                error_log("Verificando reservas existentes para DoctorID=$doctorId, Fecha=$fecha: " . 
                          count($reservasExistentes) . " encontradas", 3, 'c:/laragon/www/clinica/logs/database.log');
                
                foreach ($resultados as $horario) {
                    $horaInicio = new DateTime($fecha . ' ' . $horario['hora_inicio']);
                    $horaFin = new DateTime($fecha . ' ' . $horario['hora_fin']);
                    $intervaloMinutos = isset($horario['intervalo_minutos']) ? $horario['intervalo_minutos'] : 45; // Default to 45min if not specified
                    
                    error_log("Generando slots para horario: " . json_encode($horario) . 
                              ", Intervalo: $intervaloMinutos minutos", 3, 'c:/laragon/www/clinica/logs/database.log');
                    
                    $horaActual = clone $horaInicio;
                    
                    // Generate slots until we reach the end time
                    while ($horaActual < $horaFin) {
                        $slotInicio = clone $horaActual;
                        $slotFin = clone $horaActual;
                        $slotFin->add(new DateInterval('PT' . $intervaloMinutos . 'M'));
                        
                        // Only add the slot if it fits within the overall end time
                        if ($slotFin <= $horaFin) {
                            // Check if this slot overlaps with any existing reservation
                            $slotDisponible = true;
                            $horaInicioStr = $slotInicio->format('H:i:s');
                            $horaFinStr = $slotFin->format('H:i:s');
                            
                            foreach ($reservasExistentes as $reserva) {
                                $reservaInicio = $reserva['hora_inicio'];
                                $reservaFin = $reserva['hora_fin'];
                                
                                // Check for overlap: if the slot overlaps with an existing reservation, mark it as unavailable
                                if (
                                    // Slot starts during a reservation
                                    ($horaInicioStr >= $reservaInicio && $horaInicioStr < $reservaFin) ||
                                    // Slot ends during a reservation
                                    ($horaFinStr > $reservaInicio && $horaFinStr <= $reservaFin) ||
                                    // Slot completely contains a reservation
                                    ($horaInicioStr <= $reservaInicio && $horaFinStr >= $reservaFin) ||
                                    // Slot is completely within a reservation
                                    ($horaInicioStr >= $reservaInicio && $horaFinStr <= $reservaFin)
                                ) {
                                    $slotDisponible = false;
                                    error_log("Slot $horaInicioStr - $horaFinStr excluido por reserva existente: $reservaInicio - $reservaFin", 
                                              3, 'c:/laragon/www/clinica/logs/database.log');
                                    break;
                                }
                            }
                            
                            if ($slotDisponible) {
                                $slot = $horario; // Copy all properties from the base schedule
                                $slot['hora_inicio'] = $horaInicioStr;
                                $slot['hora_fin'] = $horaFinStr;
                                $slotsGenerados[] = $slot;
                            }
                        }
                        
                        // Move to the next slot
                        $horaActual->add(new DateInterval('PT' . $intervaloMinutos . 'M'));
                    }
                }
                
                if (count($slotsGenerados) > 0) {
                    error_log("Se generaron " . count($slotsGenerados) . " slots detallados", 
                              3, 'c:/laragon/www/clinica/logs/database.log');
                    return $slotsGenerados;
                }
            }
            
            return $resultados;
        } catch (PDOException $e) {
            error_log("Error al obtener horarios disponibles: " . $e->getMessage(), 0);
            return [];
        }
    }    /**
     * Genera los horarios disponibles para un servicio, doctor y fecha específica
     * @param int $servicioId ID del servicio
     * @param int $doctorId ID del doctor
     * @param string $fecha Fecha para la verificación (formato YYYY-MM-DD)
     * @return array Listado de slots de horarios     */    static public function mdlGenerarSlotsDisponibles($servicioId, $doctorId, $fecha) {
        try {
            // Asegurarnos de que la zona horaria esté correctamente configurada
            date_default_timezone_set('America/Caracas');
            $horaServidor = date('Y-m-d H:i:s');
            $zonaTiempo = date_default_timezone_get();
            
            error_log("Generando slots disponibles para - ServicioID: {$servicioId}, DoctorID: {$doctorId}, Fecha: {$fecha}", 3, 'c:/laragon/www/clinica/logs/servicios.log');
            error_log("Hora actual del servidor: {$horaServidor}, Zona horaria: {$zonaTiempo}", 3, 'c:/laragon/www/clinica/logs/servicios.log');
            
            // Obtener el servicio para conocer su duración
            $servicio = self::mdlObtenerServicioPorId($servicioId);

            if (!$servicio) {
                error_log("ADVERTENCIA: Servicio ID {$servicioId} no encontrado. Usando duración predeterminada.", 3, 'c:/laragon/www/clinica/logs/servicios.log');
            }
            
            // Duración predeterminada de 30 minutos si no se especifica o si el servicio no existe
            $duracionServicio = 30;
            if ($servicio && isset($servicio['duracion_minutos']) && $servicio['duracion_minutos'] > 0) {
                $duracionServicio = $servicio['duracion_minutos'];
            }
            
            error_log("Servicio ID {$servicioId}: " . ($servicio ? "Encontrado" : "No encontrado") . 
                      ", Duración: {$duracionServicio} minutos", 3, 'c:/laragon/www/clinica/logs/servicios.log');
            
            // Verificar que el formato de la fecha sea correcto
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
                error_log("Formato de fecha incorrecto: " . $fecha, 3, 'c:/laragon/www/clinica/logs/database.log');
                return [];
            }
            
            // Asegurarse de que la fecha sea válida
            $fechaObj = DateTime::createFromFormat('Y-m-d', $fecha);
            if (!$fechaObj || $fechaObj->format('Y-m-d') !== $fecha) {
                error_log("Fecha inválida: " . $fecha, 3, 'c:/laragon/www/clinica/logs/database.log');
                return [];
            }
              // Determinar el día de la semana para la fecha
            $diaSemanaNum = (int)$fechaObj->format('N'); // 1 (lunes) a 7 (domingo) según ISO-8601
              // Convertir de formato ISO-8601 (1=lunes, 7=domingo) a formato de array (0=LUNES, 6=DOMINGO)
            // IMPORTANTE: Aquí ajustamos nuestro array para que LUNES sea el índice 0
            $diasSemanaTexto = [1 => 'LUNES', 2 => 'MARTES', 3 => 'MIERCOLES', 4 => 'JUEVES', 5 => 'VIERNES', 6 => 'SABADO', 7 => 'DOMINGO'];
            $diaSemanaTexto = $diasSemanaTexto[$diaSemanaNum];
              
            // Verificar que el formato del día coincida con lo almacenado en la base de datos
            $stmtCheckDays = Conexion::conectar()->prepare("SELECT DISTINCT dia_semana FROM agendas_detalle ORDER BY dia_semana");
            $stmtCheckDays->execute();
            $diasEnBD = $stmtCheckDays->fetchAll(PDO::FETCH_COLUMN);
            
            error_log("Fecha: {$fecha}, Día num: {$diaSemanaNum}, Día texto: {$diaSemanaTexto}, PHP day: " . $fechaObj->format('l'), 3, 'c:/laragon/www/clinica/logs/servicios.log');
            error_log("Días disponibles en BD: " . json_encode($diasEnBD), 3, 'c:/laragon/www/clinica/logs/servicios.log');
            
            // Verificar si el día de la semana existe en la base de datos
            if (!in_array($diaSemanaTexto, $diasEnBD)) {
                error_log("ADVERTENCIA: El día {$diaSemanaTexto} no existe en la base de datos. Días disponibles: " . json_encode($diasEnBD), 3, 'c:/laragon/www/clinica/logs/servicios.log');
            }
              // Obtener los horarios del doctor para el día de la semana correspondiente 
            // desde agenda_detalle en lugar de servicios_horarios
            // CORREGIDO: Usando la relación correcta entre médicos y persona
            $stmt = Conexion::conectar()->prepare(
                "SELECT 
                    ad.detalle_id AS horario_id,
                    ac.agenda_id,
                    ad.turno_id,
                    t.turno_nombre,
                    ad.sala_id,
                    s.sala_nombre,
                    ac.medico_id AS doctor_id,
                    COALESCE(p.first_name, '') || ' ' || COALESCE(p.last_name, '') AS nombre_doctor,
                    ad.dia_semana,
                    ad.hora_inicio,
                    ad.hora_fin,
                    ad.intervalo_minutos,
                    ad.cupo_maximo
                FROM 
                    agendas_detalle ad
                INNER JOIN 
                    agendas_cabecera ac ON ad.agenda_id = ac.agenda_id
                INNER JOIN 
                    turnos t ON ad.turno_id = t.turno_id
                INNER JOIN 
                    salas s ON ad.sala_id = s.sala_id
                INNER JOIN
                    rh_doctors rd ON rd.doctor_id = ac.medico_id 
                INNER JOIN 
                    rh_person p ON p.person_id = rd.person_id
                WHERE 
                    ac.medico_id = :doctor_id
                    AND ad.dia_semana = :dia_semana
                    AND ad.detalle_estado = true
                    AND ac.agenda_estado = true
                ORDER BY 
                    ad.hora_inicio ASC"
            );              $stmt->bindParam(":doctor_id", $doctorId, PDO::PARAM_INT);
            $stmt->bindParam(":dia_semana", $diaSemanaTexto, PDO::PARAM_STR);
              error_log("Buscando horarios para: Doctor ID=" . $doctorId . ", Día=" . $diaSemanaTexto, 3, 'c:/laragon/www/clinica/logs/database.log');
              $stmt->execute();
            $horarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Log detallado de los horarios encontrados
            error_log("Horarios encontrados para doctor_id={$doctorId}, dia={$diaSemanaTexto}: " . count($horarios), 3, 'c:/laragon/www/clinica/logs/servicios.log');
            if (!empty($horarios)) {
                error_log("Primer horario: " . json_encode($horarios[0]), 3, 'c:/laragon/www/clinica/logs/servicios.log');
            }
            
            if (empty($horarios)) {
                error_log("No se encontraron horarios para el día " . $diaSemanaTexto, 3, 'c:/laragon/www/clinica/logs/servicios.log');
                  // Diagnóstico adicional: Verificar si existe el registro en agendas_detalle
                $stmtDiag = Conexion::conectar()->prepare("
                    SELECT COUNT(*) as total FROM agendas_detalle ad
                    INNER JOIN agendas_cabecera ac ON ad.agenda_id = ac.agenda_id
                    WHERE ac.medico_id = :doctor_id AND ad.dia_semana = :dia_semana
                ");
                $stmtDiag->bindParam(":doctor_id", $doctorId, PDO::PARAM_INT);
                $stmtDiag->bindParam(":dia_semana", $diaSemanaTexto, PDO::PARAM_STR);
                $stmtDiag->execute();
                $total = $stmtDiag->fetchColumn();
                
                error_log("Diagnóstico: Total de registros en agendas_detalle para doctor_id={$doctorId}, dia={$diaSemanaTexto}: {$total}", 3, 'c:/laragon/www/clinica/logs/servicios.log');
                return [];
            }
            
            // Para simplificar el proceso durante las pruebas, si no existe la tabla de reservas
            // generamos slots sin verificar reservas existentes
            $reservasExistentes = [];
              try {
                // Consulta para obtener las reservas existentes para ese día
                // MODIFICADO: Ahora ignoramos el servicio_id y solo consideramos las reservas del médico en esa fecha
                $stmt = Conexion::conectar()->prepare(
                    "SELECT 
                        reserva_id,
                        servicio_id,
                        agenda_id,
                        doctor_id,
                        fecha_reserva,
                        hora_inicio,
                        hora_fin
                    FROM 
                        servicios_reservas
                    WHERE 
                        doctor_id = :doctor_id
                        AND fecha_reserva = :fecha_reserva
                        AND reserva_estado IN ('CONFIRMADA', 'PENDIENTE')
                    ORDER BY 
                        hora_inicio ASC"
                );                // Ya no filtramos por servicio_id
                $stmt->bindParam(":doctor_id", $doctorId, PDO::PARAM_INT);
                $stmt->bindParam(":fecha_reserva", $fecha, PDO::PARAM_STR);
                $stmt->execute();
                $reservasExistentes = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                error_log("Reservas existentes para Doctor ID {$doctorId} en fecha {$fecha}: " . count($reservasExistentes), 3, 'c:/laragon/www/clinica/logs/servicios.log');
            } catch (PDOException $e) {
                // Si hay un error al obtener reservas (por ejemplo, la tabla no existe aún)
                // continuamos con un array vacío
                error_log("Advertencia al obtener reservas existentes: " . $e->getMessage(), 3, 'c:/laragon/www/clinica/logs/servicios.log');
            }
          // Generar slots disponibles para cada horario base
        $slotsDisponibles = [];
        foreach ($horarios as $horario) {
            // Registrar el horario base completo
            error_log("Horario base: " . json_encode($horario), 3, 'c:/laragon/www/clinica/logs/servicios.log');
            error_log("Agenda ID del horario base: " . ($horario['agenda_id'] ?? 'NULL'), 3, 'c:/laragon/www/clinica/logs/servicios.log');            // Convertir las cadenas de hora a objetos DateTime para manipulación
            // Asegurar que las horas tengan la fecha correcta de la cita
            $horaInicio = new DateTime($fecha . ' ' . $horario['hora_inicio']);
            $horaFin = new DateTime($fecha . ' ' . $horario['hora_fin']);
            $intervaloMinutos = $horario['intervalo_minutos'] ?? 30;
            
            // Ajustar el intervalo según duración del servicio
            $intervaloEfectivo = max($intervaloMinutos, $duracionServicio);
            
            // Crear una variable para llevar el seguimiento de la hora actual mientras generamos slots
            $horaActual = clone $horaInicio;

            // Log detallado de las horas de inicio y fin
            error_log("Hora de inicio: " . $horaInicio->format('Y-m-d H:i:s'), 3, 'c:/laragon/www/clinica/logs/servicios.log');
            error_log("Hora de fin: " . $horaFin->format('Y-m-d H:i:s'), 3, 'c:/laragon/www/clinica/logs/servicios.log');
            error_log("Intervalo efectivo: {$intervaloEfectivo} minutos", 3, 'c:/laragon/www/clinica/logs/servicios.log');
            
            // Generar slots mientras haya tiempo disponible
            while ($horaActual < $horaFin) {                $slotInicio = clone $horaActual;
                $slotFin = clone $horaActual;
                $slotFin->add(new DateInterval('PT' . $duracionServicio . 'M'));
                
                // Log para depuración de slots con fecha y hora completas
                error_log("Generando slot: " . $slotInicio->format('Y-m-d H:i:s') . " - " . $slotFin->format('Y-m-d H:i:s'), 3, 'c:/laragon/www/clinica/logs/servicios.log');
                  // Verificar si este slot ya está reservado
                $slotDisponible = true;
                
                // Comprobar la hora actual para evitar slots en el pasado
                $fechaHoySistema = date('Y-m-d');
                // Asegurarnos de usar la hora exacta del servidor con timezone adecuado
                date_default_timezone_set('America/Caracas'); // Confirmar que esté configurada correctamente
                $ahora = new DateTime(); // Momento actual completo con la zona horaria correcta
                
                // Crear fecha y hora completa para el slot
                $slotDateTime = new DateTime($fecha . ' ' . $slotInicio->format('H:i:s'));
                
                // Agregar 5 minutos de margen al horario actual para evitar reservas demasiado justas
                $ahoraConMargen = clone $ahora;
                $ahoraConMargen->add(new DateInterval('PT5M')); // Añade 5 minutos
                
                error_log("Generando slot: " . $slotDateTime->format('Y-m-d H:i:s') . " - " . $fecha . " " . $slotFin->format('H:i:s'), 3, 'c:/laragon/www/clinica/logs/servicios.log');
                error_log("Comparando slot: " . $slotDateTime->format('Y-m-d H:i:s') . " con ahora (+5min): " . $ahoraConMargen->format('Y-m-d H:i:s'), 3, 'c:/laragon/www/clinica/logs/servicios.log');
                
                // Verificamos sólo si la fecha del slot es igual a la fecha actual
                if ($fecha === $fechaHoySistema) {
                    // Solo para el día actual, comprobamos que la hora no esté en el pasado
                    // Usamos la hora actual más el margen para filtrar
                    if ($slotDateTime < $ahoraConMargen) {
                        error_log("Slot descartado por estar en el pasado o demasiado próximo (fecha/hora actual+5min: " . $ahoraConMargen->format('Y-m-d H:i:s') . ")", 3, 'c:/laragon/www/clinica/logs/servicios.log');
                        $slotDisponible = false;
                    } else {
                        error_log("Slot en el futuro para hoy, disponible por tiempo", 3, 'c:/laragon/www/clinica/logs/servicios.log');
                    }
                } else {
                    // Si es un día diferente al actual, comprobamos si es futuro o pasado
                    $fechaSlot = new DateTime($fecha);
                    $fechaActual = new DateTime($fechaHoySistema);
                    
                    if ($fechaSlot < $fechaActual) {
                        error_log("Slot descartado porque la fecha es anterior a hoy", 3, 'c:/laragon/www/clinica/logs/servicios.log');
                        $slotDisponible = false;
                    } else {
                        error_log("Slot para fecha futura (" . $fecha . "), disponible", 3, 'c:/laragon/www/clinica/logs/servicios.log');
                        // Para fechas futuras, siempre disponible sin importar la hora
                    }
                }
                
                // Verificar que el slot termine antes o a la misma hora que termina el horario
                if ($slotFin > $horaFin) {
                    $slotDisponible = false;
                }
                
                // Verificar colisiones con otras reservas
                if ($slotDisponible) {
                    foreach ($reservasExistentes as $reserva) {
                        $reservaInicio = new DateTime($reserva['hora_inicio']);
                        $reservaFin = new DateTime($reserva['hora_fin']);
                        
                        // Si hay superposición, marcar como no disponible
                        // Cuatro casos de superposición:
                        // 1. El inicio del slot está dentro de una reserva existente
                        // 2. El fin del slot está dentro de una reserva existente
                        // 3. El slot abarca completamente una reserva existente
                        // 4. El slot está completamente dentro de una reserva existente
                        if (
                            ($slotInicio >= $reservaInicio && $slotInicio < $reservaFin) ||
                            ($slotFin > $reservaInicio && $slotFin <= $reservaFin) ||
                            ($slotInicio <= $reservaInicio && $slotFin >= $reservaFin) ||
                            ($slotInicio >= $reservaInicio && $slotFin <= $reservaFin)
                        ) {
                            $slotDisponible = false;
                            break;
                        }
                    }
                }
                
                // Si el slot está disponible y termina antes o igual que el fin del horario, agregarlo a la lista
                if ($slotDisponible && $slotFin <= $horaFin) {
                    // Log detallado para agenda_id antes de construir el slot
                    error_log("Construyendo slot - Usando agenda_id: " . ($horario['agenda_id'] ?? 'NULL'), 3, 'c:/laragon/www/clinica/logs/servicios.log');
                      $slotsDisponibles[] = [
                        'agenda_id' => $horario['agenda_id'],
                        'horario_id' => $horario['horario_id'],
                        'turno_id' => $horario['turno_id'],
                        'turno_nombre' => $horario['turno_nombre'],
                        'sala_id' => $horario['sala_id'],
                        'sala_nombre' => $horario['sala_nombre'],
                        'doctor_id' => $horario['doctor_id'],
                        'nombre_doctor' => $horario['nombre_doctor'],
                        'fecha_reserva' => $fecha,
                        'hora_inicio' => $slotInicio->format('H:i:s'),
                        'hora_fin' => $slotFin->format('H:i:s'),
                        'duracion_minutos' => $duracionServicio,
                        'tipo_horario' => 'NORMAL',
                    ];
                }                // Avanzar al siguiente slot (usamos directamente el intervalo efectivo)
                // CORREGIDO: Avanzamos desde la hora actual, no desde el inicio del slot
                $horaActual->add(new DateInterval('PT' . $intervaloEfectivo . 'M'));
                // Log para depurar el avance de slots
                error_log("Avanzando al siguiente slot: " . $horaActual->format('H:i:s'), 3, 'c:/laragon/www/clinica/logs/servicios.log');
            }        }
        
        // Agregar información de log sobre los slots generados
        error_log("Total de slots disponibles generados: " . count($slotsDisponibles), 3, 'c:/laragon/www/clinica/logs/servicios.log');
        if (count($slotsDisponibles) > 0) {
            error_log("Primer slot disponible: " . json_encode($slotsDisponibles[0]), 3, 'c:/laragon/www/clinica/logs/servicios.log');
            error_log("Último slot disponible: " . json_encode($slotsDisponibles[count($slotsDisponibles) - 1]), 3, 'c:/laragon/www/clinica/logs/servicios.log');
        } else {
            error_log("No se generaron slots disponibles para doctor_id={$doctorId}, fecha={$fecha}, servicio_id={$servicioId}", 3, 'c:/laragon/www/clinica/logs/servicios.log');
        }
        
        return $slotsDisponibles;
        
    } catch (Exception $e) {
        error_log("Error al generar slots disponibles: " . $e->getMessage(), 3, 'c:/laragon/www/clinica/logs/servicios.log');
        return [];
    }
}    /**
     * Obtiene los doctores disponibles para una fecha específica
     * @param string $fecha Fecha en formato YYYY-MM-DD
     * @return array Lista de doctores disponibles en esa fecha
     */
    static public function mdlObtenerDoctoresPorFecha($fecha) {
        try {
            // Verificar que el formato de la fecha sea correcto
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
                error_log("Formato de fecha incorrecto: " . $fecha, 3, 'c:/laragon/www/clinica/logs/database.log');
                return [];
            }
            
            // Asegurarse de que la fecha sea válida
            $fechaObj = DateTime::createFromFormat('Y-m-d', $fecha);
            if (!$fechaObj || $fechaObj->format('Y-m-d') !== $fecha) {
                error_log("Fecha inválida: " . $fecha, 3, 'c:/laragon/www/clinica/logs/database.log');
                return [];
            }
            
            // Determinar el día de la semana para la fecha
            $diaSemanaNum = (int)$fechaObj->format('N'); // 1 (lunes) a 7 (domingo) según ISO-8601
            $diasSemanaTexto = [1 => 'LUNES', 2 => 'MARTES', 3 => 'MIERCOLES', 4 => 'JUEVES', 5 => 'VIERNES', 6 => 'SABADO', 7 => 'DOMINGO'];
            $diaSemanaTexto = $diasSemanaTexto[$diaSemanaNum];
            
            error_log("Buscando doctores para el día: {$diaSemanaTexto}, fecha: {$fecha}", 3, 'c:/laragon/www/clinica/logs/database.log');
              // Obtener doctores que tienen horarios para ese día de semana
            $stmt = Conexion::conectar()->prepare(
                "SELECT DISTINCT
                    ac.medico_id AS doctor_id,
                    COALESCE(p.first_name, '') || ' ' || COALESCE(p.last_name, '') AS nombre_doctor,
                    p.document_number,
                    p.person_id
                FROM 
                    agendas_detalle ad
                INNER JOIN 
                    agendas_cabecera ac ON ad.agenda_id = ac.agenda_id
                LEFT JOIN
                    rh_doctors d ON ac.medico_id = d.doctor_id
                LEFT JOIN
                    rh_person p ON d.person_id = p.person_id
                WHERE 
                    ad.dia_semana = :dia_semana
                    AND ad.detalle_estado = true
                    AND ac.agenda_estado = true
                ORDER BY 
                    nombre_doctor ASC"
            );
            
            $stmt->bindParam(":dia_semana", $diaSemanaTexto, PDO::PARAM_STR);
            $stmt->execute();
            
            $doctores = $stmt->fetchAll(PDO::FETCH_ASSOC);
            error_log("Doctores encontrados para {$diaSemanaTexto}: " . count($doctores), 3, 'c:/laragon/www/clinica/logs/database.log');
            
            return $doctores;
        } catch (Exception $e) {
            error_log("Error al obtener doctores por fecha: " . $e->getMessage(), 0);
            return [];
        }
    }
    
    /**
     * Obtiene los médicos disponibles para una fecha específica
     * @param string $fecha Fecha en formato YYYY-MM-DD
     * @return array Lista de médicos disponibles
     */
    static public function mdlObtenerMedicosDisponiblesPorFecha($fecha) {
        try {
            // Asegurarse que la fecha tenga un formato válido
            $fechaObj = DateTime::createFromFormat('Y-m-d', $fecha);
            if (!$fechaObj) {
                error_log("Formato de fecha incorrecto: " . $fecha, 3, 'c:/laragon/www/clinica/logs/database.log');
                return [];
            }
              // Determinar el día de la semana
            $diaSemanaNum = (int)$fechaObj->format('N'); // 1-7 (ISO format: 1=lunes, 7=domingo)
            $diasSemanaTexto = [1 => 'LUNES', 2 => 'MARTES', 3 => 'MIERCOLES', 4 => 'JUEVES', 5 => 'VIERNES', 6 => 'SABADO', 7 => 'DOMINGO'];
            $diaSemana = $diasSemanaTexto[$diaSemanaNum];
            
            error_log("DEBUG - Fecha: {$fecha}, Número día: {$diaSemanaNum}, Texto día: {$diaSemana}", 3, 'c:/laragon/www/clinica/logs/database.log');
            
            error_log("Buscando médicos para fecha: {$fecha}, día: {$diaSemana}", 3, 'c:/laragon/www/clinica/logs/database.log');
            
            // Verificar si hay médicos con agenda configurada para este día de la semana
            $stmtCheckDay = Conexion::conectar()->prepare("
                SELECT COUNT(*) FROM agendas_detalle WHERE dia_semana = :dia_semana AND detalle_estado = true
            ");
            $stmtCheckDay->bindParam(":dia_semana", $diaSemana, PDO::PARAM_STR);
            $stmtCheckDay->execute();
            $hayMedicosParaEsteDia = $stmtCheckDay->fetchColumn() > 0;
            
            if (!$hayMedicosParaEsteDia) {
                error_log("No hay médicos con agenda configurada para {$diaSemana}", 3, 'c:/laragon/www/clinica/logs/database.log');
                return [
                    ['message' => "No hay médicos disponibles para este día ({$diaSemana}). Sólo hay horarios para LUNES, MARTES y MIÉRCOLES."]
                ];
            }
              // Consulta para diagnosticar si hay agendas para este día de la semana
            $stmtDiag = Conexion::conectar()->prepare("
                SELECT ad.agenda_id, ad.dia_semana, ac.agenda_descripcion, ac.medico_id 
                FROM agendas_detalle ad 
                INNER JOIN agendas_cabecera ac ON ad.agenda_id = ac.agenda_id
                WHERE ad.dia_semana = :dia_semana AND ad.detalle_estado = true
            ");
            $stmtDiag->bindParam(":dia_semana", $diaSemana, PDO::PARAM_STR);
            $stmtDiag->execute();
            $agendasDiag = $stmtDiag->fetchAll(PDO::FETCH_ASSOC);
            
            error_log("Agendas encontradas para {$diaSemana}: " . json_encode($agendasDiag), 3, 'c:/laragon/www/clinica/logs/database.log');
              error_log("DEBUG - Dia semana utilizado para la consulta: '{$diaSemana}'", 3, 'c:/laragon/www/clinica/logs/database.log');
            
            // Consulta para verificar cuáles días existen en agendas_detalle
            $stmtDiasCheck = Conexion::conectar()->prepare("
                SELECT DISTINCT dia_semana FROM agendas_detalle ORDER BY dia_semana
            ");
            $stmtDiasCheck->execute();
            $diasDisponibles = $stmtDiasCheck->fetchAll(PDO::FETCH_COLUMN);
            error_log("DEBUG - Días disponibles en agendas_detalle: " . json_encode($diasDisponibles), 3, 'c:/laragon/www/clinica/logs/database.log');
            
            // Consulta para obtener médicos que tienen horarios en ese día
            // Modificando la consulta para usar LEFT JOIN y verificar cada relación
            $stmt = Conexion::conectar()->prepare(
                "SELECT DISTINCT 
                    d.doctor_id,
                    p.person_id,
                    p.first_name || ' ' || p.last_name AS nombre_doctor,
                    d.doctor_estado,
                    ac.agenda_id,
                    ac.medico_id,
                    d.doctor_id = ac.medico_id AS doctor_match,
                    ad.dia_semana
                FROM 
                    agendas_detalle ad
                LEFT JOIN
                    agendas_cabecera ac ON ad.agenda_id = ac.agenda_id
                LEFT JOIN
                    rh_doctors d ON ac.medico_id = d.doctor_id
                LEFT JOIN
                    rh_person p ON d.person_id = p.person_id                WHERE                    ad.dia_semana = :dia_semana
                    AND ad.detalle_estado = true
                    AND (ac.agenda_estado IS NULL OR ac.agenda_estado = true)
                    AND (d.doctor_estado IS NULL OR d.doctor_estado = 'ACTIVO')
                ORDER BY
                    nombre_doctor"
            );
              $stmt->bindParam(":dia_semana", $diaSemana, PDO::PARAM_STR);
            $stmt->execute();
              $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
            error_log("Resultados crudos de la consulta: " . json_encode($resultados), 3, 'c:/laragon/www/clinica/logs/database.log');
            
            // Si la consulta no devolvió resultados, verificar si existe el día en la tabla
            if (empty($resultados)) {
                $stmtVerificar = Conexion::conectar()->prepare("
                    SELECT COUNT(*) FROM agendas_detalle WHERE dia_semana = :dia_semana
                ");
                $stmtVerificar->bindParam(":dia_semana", $diaSemana, PDO::PARAM_STR);
                $stmtVerificar->execute();
                $conteo = $stmtVerificar->fetchColumn();
                
                error_log("Verificación adicional - Registros con día {$diaSemana}: {$conteo}", 3, 'c:/laragon/www/clinica/logs/database.log');
                
                // También verificar si el formato del día de semana es el esperado
                $stmtFormato = Conexion::conectar()->prepare("
                    SELECT DISTINCT dia_semana FROM agendas_detalle
                ");
                $stmtFormato->execute();
                $diasDisponibles = $stmtFormato->fetchAll(PDO::FETCH_COLUMN);
                error_log("Días disponibles en la tabla: " . json_encode($diasDisponibles), 3, 'c:/laragon/www/clinica/logs/database.log');
            }
            
            // Filtrar solo los resultados donde hay una coincidencia válida de doctor
            $medicos = [];
            foreach ($resultados as $resultado) {
                if (!empty($resultado['doctor_id']) && !empty($resultado['person_id'])) {
                    $medicos[] = [
                        'doctor_id' => $resultado['doctor_id'],
                        'person_id' => $resultado['person_id'],
                        'nombre_doctor' => $resultado['nombre_doctor'],
                        'doctor_estado' => $resultado['doctor_estado']
                    ];
                } else {
                    error_log("Descartado resultado por faltar información - doctor_id: " . 
                        (isset($resultado['doctor_id']) ? $resultado['doctor_id'] : 'NULL') . 
                        ", person_id: " . (isset($resultado['person_id']) ? $resultado['person_id'] : 'NULL'), 
                        3, 'c:/laragon/www/clinica/logs/database.log');
                }
            }
            
            error_log("Médicos encontrados para {$fecha} ({$diaSemana}): " . count($medicos), 3, 'c:/laragon/www/clinica/logs/database.log');
              // Si no hay médicos encontrados
            if (count($medicos) === 0) {
                error_log("No se encontraron médicos disponibles para {$fecha} ({$diaSemana})", 3, 'c:/laragon/www/clinica/logs/database.log');
                
                // Verificar si hay agendas configuradas para ese día
                if (count($agendasDiag) > 0) {
                    error_log("ADVERTENCIA: Hay agendas para {$diaSemana} pero no se encontraron médicos - posible problema de relación en la BD", 3, 'c:/laragon/www/clinica/logs/database.log');
                    
                    // Devolver un mensaje informativo para el usuario
                    return [
                        [
                            'message' => "No hay médicos disponibles para la fecha seleccionada ({$fecha}), pero hay agendas configuradas para {$diaSemana}. Podría haber un problema con la asignación de médicos."
                        ]
                    ];
                } else {
                    // No hay ni médicos ni agendas para ese día
                    return [
                        [
                            'message' => "No hay médicos disponibles para la fecha seleccionada ({$fecha}, {$diaSemana}). Por favor, seleccione otro día o contacte con la clínica."
                        ]
                    ];
                }
                
                // Verificar la relación entre agendas y médicos
                foreach ($agendasDiag as $agenda) {
                    $stmtMedico = Conexion::conectar()->prepare("SELECT d.doctor_id, p.first_name || ' ' || p.last_name AS nombre FROM rh_doctors d INNER JOIN rh_person p ON d.person_id = p.person_id WHERE d.doctor_id = :medico_id");
                    $stmtMedico->bindParam(":medico_id", $agenda['medico_id'], PDO::PARAM_INT);
                    $stmtMedico->execute();
                    $medicoData = $stmtMedico->fetch(PDO::FETCH_ASSOC);
                    
                    if ($medicoData) {
                        error_log("Agenda {$agenda['agenda_id']} corresponde al médico ID {$agenda['medico_id']}: {$medicoData['nombre']}", 3, 'c:/laragon/www/clinica/logs/database.log');
                          // Agregar este médico a la lista manualmente
                        $medicos[] = [
                            'doctor_id' => $medicoData['doctor_id'],
                            'nombre_doctor' => $medicoData['nombre'],
                            'doctor_estado' => 'ACTIVO',
                            'agenda_id' => $agenda['agenda_id']
                        ];
                    } else {
                        error_log("ADVERTENCIA: No se encontró médico con ID {$agenda['medico_id']} para la agenda {$agenda['agenda_id']}", 3, 'c:/laragon/www/clinica/logs/database.log');
                    }
                }
                
                // Si aún no hay médicos, devolver un mensaje específico
                if (count($medicos) === 0) {
                    return [
                        ['message' => "No se encontraron médicos para este día ({$diaSemana}) debido a un problema de configuración. Por favor contacte al administrador del sistema."]
                    ];
                }
            }
            
            // Filtrar médicos que estén bloqueados en esta fecha
            if (count($medicos) > 0) {
                // Comprobar si existe la tabla de bloqueos
                $stmtCheck = Conexion::conectar()->prepare("SELECT to_regclass('public.agendas_bloqueos')");
                $stmtCheck->execute();
                $tablaBloqueosExiste = $stmtCheck->fetchColumn();
                
                if ($tablaBloqueosExiste) {
                    // Filtrar médicos con bloqueos para esta fecha
                    foreach ($medicos as $key => $medico) {
                        $stmtBloqueo = Conexion::conectar()->prepare(
                            "SELECT COUNT(*) AS bloqueado
                            FROM agendas_bloqueos
                            WHERE doctor_id = :doctor_id
                            AND :fecha BETWEEN fecha_inicio AND fecha_fin
                            AND bloqueo_estado = true
                            AND (
                                hora_inicio IS NULL
                                OR hora_fin IS NULL
                            )"
                        );
                        
                        $stmtBloqueo->bindParam(":doctor_id", $medico['doctor_id'], PDO::PARAM_INT);
                        $stmtBloqueo->bindParam(":fecha", $fecha, PDO::PARAM_STR);
                        $stmtBloqueo->execute();
                        
                        $resultado = $stmtBloqueo->fetch(PDO::FETCH_ASSOC);
                        if ($resultado['bloqueado'] > 0) {
                            unset($medicos[$key]);
                        }
                    }
                    
                    // Reindexar array después de eliminar elementos
                    $medicos = array_values($medicos);
                }
            }
            
            return $medicos;
            
        } catch (PDOException $e) {
            error_log("Error al obtener médicos por fecha: " . $e->getMessage(), 0);
            return [];
        }
    }
    
    /**
     * Obtiene los servicios disponibles para una fecha y doctor específicos
     * @param string $fecha Fecha en formato YYYY-MM-DD
     * @param int $doctorId ID del doctor
     * @return array Lista de servicios disponibles
     */    static public function mdlObtenerServiciosPorFechaMedico($fecha, $doctorId) {
        try {
            $servicios = [];
            $serviciosPorDoctor = [];
            $todosServicios = [];
            
            // Verificar si existe la tabla servicios_medicos antes de consultarla
            $stmtCheck = Conexion::conectar()->prepare("SELECT to_regclass('public.servicios_medicos')");
            $stmtCheck->execute();
            $tablaServiciosMedicosExiste = $stmtCheck->fetchColumn();
            
            if ($tablaServiciosMedicosExiste) {
                // MODIFICADO: Primero obtenemos todos los servicios activos
                $stmtTodos = Conexion::conectar()->prepare(
                    "SELECT 
                        sm.servicio_id,
                        sm.servicio_codigo,
                        sm.servicio_nombre,
                        sm.duracion_minutos,
                        sm.precio_base,
                        c.categoria_nombre,
                        'servicios_medicos' as origen
                    FROM 
                        servicios_medicos sm
                    INNER JOIN
                        servicios_categorias c ON sm.categoria_id = c.categoria_id
                    WHERE 
                        sm.servicio_estado = 'ACTIVO'
                    ORDER BY
                        sm.servicio_nombre"
                );
                
                $stmtTodos->execute();
                $todosServicios = $stmtTodos->fetchAll(PDO::FETCH_ASSOC);
                
                // También obtenemos servicios asociados específicamente al médico 
                // (esto solo es para tener referencia, utilizaremos todos los servicios)
                $stmt = Conexion::conectar()->prepare(
                    "SELECT DISTINCT
                        sm.servicio_id,
                        sm.servicio_codigo,
                        sm.servicio_nombre,
                        sm.duracion_minutos,
                        sm.precio_base,
                        c.categoria_nombre,
                        'doctor_especifico' as origen
                    FROM 
                        servicios_medicos sm
                    INNER JOIN
                        servicios_categorias c ON sm.categoria_id = c.categoria_id
                    INNER JOIN
                        servicios_proveedores sp ON sm.servicio_id = sp.servicio_id
                    WHERE 
                        sp.doctor_id = :doctor_id
                        AND sm.servicio_estado = 'ACTIVO'
                        AND sp.proveedor_estado = true
                    ORDER BY
                        sm.servicio_nombre"
                );
                
                $stmt->bindParam(":doctor_id", $doctorId, PDO::PARAM_INT);
                $stmt->execute();
                
                $serviciosPorDoctor = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Agregamos todos los servicios activos al resultado
                $servicios = $todosServicios;
                
                // Log para seguimiento 
                $countPorDoctor = count($serviciosPorDoctor);
                $countTodos = count($todosServicios);
                error_log("Servicios por doctor ID {$doctorId}: {$countPorDoctor}, Todos los servicios: {$countTodos}", 3, 'c:/laragon/www/clinica/logs/servicios.log');
            }
            
            // También verificamos si tenemos los nuevos servicios disponibles (rs_servicios)
            $stmtCheck = Conexion::conectar()->prepare("SELECT to_regclass('public.rs_servicios')");
            $stmtCheck->execute();
            $tablaRsServiciosExiste = $stmtCheck->fetchColumn();
            
            if ($tablaRsServiciosExiste) {
                // Verificar si existe la tabla de relación médico-servicio
                $stmtCheckRel = Conexion::conectar()->prepare("SELECT to_regclass('public.rs_medico_servicio')");
                $stmtCheckRel->execute();
                $tablaMedicoServicioExiste = $stmtCheckRel->fetchColumn();
                
                // MODIFICADO: Base SQL para obtener todos los servicios activos
                $sqlBase = "SELECT 
                    rs.serv_id as servicio_id,
                    rs.serv_codigo as servicio_codigo,
                    rs.serv_descripcion as servicio_nombre,
                    30 as duracion_minutos,
                    rs.serv_monto as precio_base,
                    rst.servicio as categoria_nombre,
                    'rs_servicios' as origen
                FROM 
                    rs_servicios rs
                INNER JOIN
                    rs_servicios_tipos rst ON rs.tserv_cod = rst.tserv_cod
                WHERE 
                    rs.is_active = true
                ORDER BY
                    rs.serv_descripcion";
                
                $stmtRs = Conexion::conectar()->prepare($sqlBase);
                $stmtRs->execute();
                $serviciosRs = $stmtRs->fetchAll(PDO::FETCH_ASSOC);
                
                // Log para debug
                error_log("Servicios rs_servicios encontrados: " . count($serviciosRs), 3, 'c:/laragon/www/clinica/logs/servicios.log');
                
                // Combinar todos los servicios
                if (empty($servicios)) {
                    $servicios = $serviciosRs;
                } else {
                    // Combinar los resultados de ambas tablas
                    $servicios = array_merge($servicios, $serviciosRs);
                }
                
                // Ordenar por nombre
                usort($servicios, function($a, $b) {
                    return strcmp($a['servicio_nombre'], $b['servicio_nombre']);
                });
                
                // Log para debug
                error_log("Total de servicios combinados: " . count($servicios), 3, 'c:/laragon/www/clinica/logs/servicios.log');
                error_log("Detalles de servicios combinados: " . json_encode(array_slice($servicios, 0, 5)), 3, 'c:/laragon/www/clinica/logs/servicios.log');
            }
            
            // Log del resultado final
            error_log("Resultado final de mdlObtenerServiciosPorFechaMedico para médico ID {$doctorId}: " . count($servicios) . " servicios", 3, 'c:/laragon/www/clinica/logs/servicios.log');
            
            return $servicios;
            
        } catch (PDOException $e) {
            error_log("Error al obtener servicios por fecha y médico: " . $e->getMessage(), 3, 'c:/laragon/www/clinica/logs/servicios.log');
            return [];
        } catch (Exception $e) {
            error_log("Error general al obtener servicios: " . $e->getMessage(), 3, 'c:/laragon/www/clinica/logs/servicios.log');
            return [];
        }
    }
    
    /**
     * Obtiene las reservas existentes para una fecha específica
     * @param string $fecha Fecha en formato YYYY-MM-DD
     * @param int $doctorId ID del doctor (opcional)
     * @param string $estado Estado de la reserva (opcional)
     * @return array Lista de reservas
     */    static public function mdlObtenerReservasPorFecha($fecha, $doctorId = null, $estado = null, $paciente = null) {
        try {
            error_log("mdlObtenerReservasPorFecha: Fecha=$fecha, DoctorID=" . ($doctorId ?? "null") . ", Estado=" . ($estado ?? "null") . ", Paciente=" . ($paciente ?? "null"), 3, "c:/laragon/www/clinica/logs/reservas.log");
            
            // Verificar si existe la tabla de reservas
            $stmtCheck = Conexion::conectar()->prepare("SELECT to_regclass('public.servicios_reservas')");
            $stmtCheck->execute();
            $tablaReservasExiste = $stmtCheck->fetchColumn();
            
            if (!$tablaReservasExiste) {
                error_log("mdlObtenerReservasPorFecha: La tabla servicios_reservas no existe", 3, "c:/laragon/www/clinica/logs/reservas.log");
                return [];
            }
            
            // Asegurarse de que la fecha esté en formato YYYY-MM-DD
            if ($fecha !== null && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
                $fechaFormateada = date('Y-m-d', strtotime($fecha));
                error_log("mdlObtenerReservasPorFecha: Formato de fecha incorrecto ($fecha), reformateando a $fechaFormateada", 3, "c:/laragon/www/clinica/logs/reservas.log");
                $fecha = $fechaFormateada;
            }
            
            // Construir la consulta SQL
            $sql = "SELECT 
                sr.reserva_id,
                sr.servicio_id,
                sr.doctor_id,
                sr.paciente_id,
                sr.fecha_reserva,
                sr.hora_inicio,
                sr.hora_fin,
                sr.reserva_estado,
                sr.observaciones,
                sr.business_id,
                sr.created_at,
                sr.updated_at,
                sr.agenda_id,
                sr.sala_id,
                s.sala_nombre,
                sr.tarifa_id,
                rp.first_name ||' - ' || rp.last_name as doctor,
                rp2.first_name ||' - ' || rp2.last_name as paciente,
                rs.serv_descripcion,
                rs.serv_monto
            FROM servicios_reservas sr 
            INNER JOIN rh_doctors rd ON sr.doctor_id = rd.doctor_id 
            INNER JOIN rh_person rp ON rd.person_id = rp.person_id 
            INNER JOIN rh_person rp2 ON sr.paciente_id = rp2.person_id 
            INNER JOIN rs_servicios rs ON sr.servicio_id = rs.serv_id 
            inner join agendas_detalle ad on sr.agenda_id = ad.detalle_id 
            inner join salas s on s.sala_id = ad.sala_id 
            WHERE 1=1";
            
            // Añadir filtros según los parámetros proporcionados
            if ($fecha !== null) {
                // Construir rango de fechas para la consulta
                $fechaInicio = $fecha . " 00:00:00";
                $fechaFin = $fecha . " 23:59:59";
                $sql .= " AND sr.fecha_reserva BETWEEN :fecha_inicio AND :fecha_fin";
            }
            
            if ($doctorId !== null) {
                $sql .= " AND sr.doctor_id = :doctor_id";
            }
              
            if ($estado !== null && $estado !== '') {
                $sql .= " AND sr.reserva_estado = :estado";
            }
            
            // Filtrar por nombre de paciente (búsqueda parcial mejorada)
            if ($paciente !== null && trim($paciente) !== '') {
                $sql .= " AND (rp2.first_name ILIKE :nombre_paciente 
                        OR rp2.last_name ILIKE :nombre_paciente 
                        OR (rp2.first_name || ' ' || rp2.last_name) ILIKE :nombre_paciente)";
            }
            
            $sql .= " ORDER BY sr.fecha_reserva DESC, sr.hora_inicio ASC";
            
            error_log("mdlObtenerReservasPorFecha: SQL=$sql", 3, "c:/laragon/www/clinica/logs/reservas.log");
            
            $stmt = Conexion::conectar()->prepare($sql);
            
            // Bindear parámetros según los filtros usados
            if ($fecha !== null) {
                $stmt->bindParam(":fecha_inicio", $fechaInicio, PDO::PARAM_STR);
                $stmt->bindParam(":fecha_fin", $fechaFin, PDO::PARAM_STR);
            }
            
            if ($doctorId !== null) {
                $stmt->bindParam(":doctor_id", $doctorId, PDO::PARAM_INT);
            }
            
            if ($estado !== null && $estado !== '') {
                $stmt->bindParam(":estado", $estado, PDO::PARAM_STR);
            }
            
            if ($paciente !== null && trim($paciente) !== '') {
                $pacienteParam = '%' . trim($paciente) . '%';
                $stmt->bindParam(":nombre_paciente", $pacienteParam, PDO::PARAM_STR);
            }
            
            $stmt->execute();
            $reservas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            error_log("mdlObtenerReservasPorFecha: Se encontraron " . count($reservas) . " reservas", 3, "c:/laragon/www/clinica/logs/reservas.log");
            if (count($reservas) > 0) {
                error_log("mdlObtenerReservasPorFecha: Primera reserva: " . json_encode($reservas[0]), 3, "c:/laragon/www/clinica/logs/reservas.log");
            }
            
            return $reservas;
        } catch (PDOException $e) {
            error_log("Error al obtener reservas por fecha: " . $e->getMessage(), 3, "c:/laragon/www/clinica/logs/reservas.log");
            return [];
        }
    }

    /**
     * Crea una nueva reserva
     * @param array $datos Datos de la reserva
     * @return array Resultado de la operación
     */
    static public function mdlCrearReserva($datos) {
        try {
            // Verificar si existe la tabla de reservas
            $stmtCheck = Conexion::conectar()->prepare("SELECT to_regclass('public.servicios_reservas')");
            $stmtCheck->execute();
            $tablaReservasExiste = $stmtCheck->fetchColumn();
            
            if (!$tablaReservasExiste) {
                return ["error" => true, "mensaje" => "La tabla de reservas no existe. Por favor, ejecute el script de creación de tablas."];
            }
            
            // Verificar si ya existe una reserva en el mismo horario para el mismo doctor
            $stmtVerificar = Conexion::conectar()->prepare(
                "SELECT COUNT(*) AS coincidencias
                FROM servicios_reservas
                WHERE doctor_id = :doctor_id
                AND fecha_reserva = :fecha_reserva
                AND (
                    (hora_inicio <= :hora_inicio AND hora_fin > :hora_inicio) OR
                    (hora_inicio < :hora_fin AND hora_fin >= :hora_fin) OR
                    (hora_inicio >= :hora_inicio AND hora_fin <= :hora_fin)
                )
                AND reserva_estado IN ('PENDIENTE', 'CONFIRMADA')"
            );
            
            $stmtVerificar->bindParam(":doctor_id", $datos['doctor_id'], PDO::PARAM_INT);
            $stmtVerificar->bindParam(":fecha_reserva", $datos['fecha_reserva'], PDO::PARAM_STR);
            $stmtVerificar->bindParam(":hora_inicio", $datos['hora_inicio'], PDO::PARAM_STR);
            $stmtVerificar->bindParam(":hora_fin", $datos['hora_fin'], PDO::PARAM_STR);
            $stmtVerificar->execute();
            
            $resultado = $stmtVerificar->fetch(PDO::FETCH_ASSOC);
            if ($resultado['coincidencias'] > 0) {
                return ["error" => true, "mensaje" => "Ya existe una reserva para este doctor en el horario seleccionado."];
            }
            
            // Insertar la nueva reserva
            $stmtInsertar = Conexion::conectar()->prepare(
                "INSERT INTO servicios_reservas (
                    servicio_id, doctor_id, paciente_id, agenda_id, fecha_reserva,
                    hora_inicio, hora_fin, observaciones, sala_id, tarifa_id,
                    precio_final, business_id, created_by
                ) VALUES (
                    :servicio_id, :doctor_id, :paciente_id, :agenda_id, :fecha_reserva,
                    :hora_inicio, :hora_fin, :observaciones, :sala_id, :tarifa_id,
                    :precio_final, :business_id, :created_by
                ) RETURNING reserva_id"
            );
            
            // Valores requeridos
            $stmtInsertar->bindParam(":servicio_id", $datos['servicio_id'], PDO::PARAM_INT);
            $stmtInsertar->bindParam(":doctor_id", $datos['doctor_id'], PDO::PARAM_INT);
            $stmtInsertar->bindParam(":paciente_id", $datos['paciente_id'], PDO::PARAM_INT);
            $stmtInsertar->bindParam(":fecha_reserva", $datos['fecha_reserva'], PDO::PARAM_STR);
            $stmtInsertar->bindParam(":hora_inicio", $datos['hora_inicio'], PDO::PARAM_STR);
            $stmtInsertar->bindParam(":hora_fin", $datos['hora_fin'], PDO::PARAM_STR);
            
            // Valores opcionales
            $agendaId = isset($datos['agenda_id']) ? $datos['agenda_id'] : null;
            $observaciones = isset($datos['observaciones']) ? $datos['observaciones'] : null;
            $salaId = isset($datos['sala_id']) ? $datos['sala_id'] : null;
            $tarifaId = isset($datos['tarifa_id']) ? $datos['tarifa_id'] : null;
            $precioFinal = isset($datos['precio_final']) ? $datos['precio_final'] : null;
            $businessId = isset($datos['business_id']) ? $datos['business_id'] : 1;
            $createdBy = isset($datos['created_by']) ? $datos['created_by'] : 1;
            $seguroId = isset($datos['seguro_id']) ? $datos['seguro_id'] : null;
            
            $stmtInsertar->bindParam(":agenda_id", $agendaId, PDO::PARAM_INT);
            $stmtInsertar->bindParam(":observaciones", $observaciones, PDO::PARAM_STR);
            $stmtInsertar->bindParam(":sala_id", $salaId, PDO::PARAM_INT);
            $stmtInsertar->bindParam(":tarifa_id", $tarifaId, PDO::PARAM_INT);
            $stmtInsertar->bindParam(":precio_final", $precioFinal, PDO::PARAM_STR);
            $stmtInsertar->bindParam(":business_id", $businessId, PDO::PARAM_INT);
            $stmtInsertar->bindParam(":created_by", $createdBy, PDO::PARAM_INT);
            
            error_log("SQL a ejecutar: INSERT INTO servicios_reservas...", 3, 'c:/laragon/www/clinica/logs/reservas.log');
            error_log("Parámetros: " . json_encode([
                'servicio_id' => $datos['servicio_id'],
                'doctor_id' => $datos['doctor_id'],
                'paciente_id' => $datos['paciente_id'],
                'fecha_reserva' => $datos['fecha_reserva'],
                'hora_inicio' => $datos['hora_inicio'],
                'hora_fin' => $datos['hora_fin'],
                'agenda_id' => $agendaId,
                'tarifa_id' => $tarifaId,
                'seguro_id' => $seguroId
            ]), 3, 'c:/laragon/www/clinica/logs/reservas.log');
            
            if ($stmtInsertar->execute()) {
                $resultado = $stmtInsertar->fetch(PDO::FETCH_ASSOC);
                if ($resultado && isset($resultado['reserva_id'])) {
                    error_log("Reserva creada con ID: " . $resultado['reserva_id'], 3, 'c:/laragon/www/clinica/logs/reservas.log');
                    return $resultado['reserva_id'];
                } else {
                    error_log("Error: La consulta se ejecutó pero no se obtuvo un ID de reserva", 3, 'c:/laragon/www/clinica/logs/reservas.log');
                    return false;
                }
            } else {
                $errorInfo = $stmtInsertar->errorInfo();
                error_log("Error al insertar reserva: SQLSTATE=" . $errorInfo[0] . ", Driver-specific code=" . $errorInfo[1] . ", Message=" . $errorInfo[2], 3, 'c:/laragon/www/clinica/logs/reservas.log');
                return false;
            }
        } catch (PDOException $e) {
            error_log("Excepción PDO al guardar reserva: " . $e->getMessage() . "\n" . $e->getTraceAsString(), 3, 'c:/laragon/www/clinica/logs/reservas.log');
            return false;
        }
    }

    /**
     * Integrar datos de la tabla cm_servicios_medicos a la nueva estructura
     * @return array Resultado de la operación
     */
    static public function mdlIntegrarServiciosExistentes() {
        try {
            // Verificar si existe la tabla cm_servicios_medicos
            $stmt = Conexion::conectar()->prepare("
                SELECT EXISTS (
                    SELECT FROM information_schema.tables 
                    WHERE table_name = 'cm_servicios_medicos'
                ) AS existe_tabla
            ");
            
            $stmt->execute();
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$resultado['existe_tabla']) {
                return ["error" => true, "mensaje" => "La tabla cm_servicios_medicos no existe"];
            }
            
            // Obtener los servicios existentes
            $stmt = Conexion::conectar()->prepare("
                SELECT * FROM cm_servicios_medicos
                WHERE estado = true
            ");
            
            $stmt->execute();
            $serviciosExistentes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $serviciosImportados = 0;
            $errores = 0;
            
            // Comenzar una transacción
            $pdo = Conexion::conectar();
            $pdo->beginTransaction();
            
            try {
                // Asegurar que exista la categoría para servicios importados
                $stmtCategoria = $pdo->prepare("
                    INSERT INTO servicios_categorias 
                    (categoria_nombre, categoria_descripcion) 
                    VALUES ('Servicios Importados', 'Servicios importados del sistema anterior')
                    ON CONFLICT (categoria_nombre) DO UPDATE 
                    SET categoria_descripcion = EXCLUDED.categoria_descripcion
                    RETURNING categoria_id
                ");

                $stmtCategoria->execute();
                $categoriaResult = $stmtCategoria->fetch(PDO::FETCH_ASSOC);
                $categoriaId = $categoriaResult['categoria_id'];
                
                // Preparar la inserción de servicios
                $stmtServicio = $pdo->prepare("
                    INSERT INTO servicios_medicos
                    (categoria_id, servicio_codigo, servicio_nombre, servicio_descripcion, duracion_minutos, precio_base)
                    VALUES
                    (:categoria_id, :servicio_codigo, :servicio_nombre, :servicio_descripcion, :duracion_minutos, :precio_base)
                    ON CONFLICT (servicio_codigo) DO NOTHING
                    RETURNING servicio_id
                ");
                
                foreach ($serviciosExistentes as $servicio) {
                    // Valores por defecto o mapeados desde la tabla existente
                    $stmtServicio->bindParam(":categoria_id", $categoriaId, PDO::PARAM_INT);
                    $stmtServicio->bindParam(":servicio_codigo", $servicio['codigo'], PDO::PARAM_STR);
                    $stmtServicio->bindParam(":servicio_nombre", $servicio['nombre'], PDO::PARAM_STR);
                    $stmtServicio->bindParam(":servicio_descripcion", $servicio['descripcion'], PDO::PARAM_STR);
                    
                    // Valores predeterminados si no existen en la tabla original
                    $duracion = isset($servicio['duracion']) ? $servicio['duracion'] : 30;
                    $precio = isset($servicio['precio']) ? $servicio['precio'] : 0.00;
                    
                    $stmtServicio->bindParam(":duracion_minutos", $duracion, PDO::PARAM_INT);
                    $stmtServicio->bindParam(":precio_base", $precio, PDO::PARAM_STR);
                    
                    if ($stmtServicio->execute()) {
                        $servicioResult = $stmtServicio->fetch(PDO::FETCH_ASSOC);
                        if ($servicioResult) {
                            $serviciosImportados++;
                            
                            // También podríamos insertar una tarifa estándar para cada servicio
                            // Pero lo omitimos por simplicidad
                        }
                    } else {
                        $errores++;
                    }
                }
                
                // Si todo salió bien, confirmar la transacción
                $pdo->commit();
                
                return [
                    "error" => false, 
                    "mensaje" => "Importación completada", 
                    "importados" => $serviciosImportados,
                    "errores" => $errores
                ];
            } catch (Exception $e) {
                $pdo->rollBack();
                error_log("Error al importar servicios: " . $e->getMessage(), 0);
                return ["error" => true, "mensaje" => "Error al importar: " . $e->getMessage()];
            }
        } catch (Exception $e) {
            error_log("Error general al importar servicios: " . $e->getMessage(), 0);            return ["error" => true, "mensaje" => "Error general: " . $e->getMessage()];
        }
    }    /**
     * Obtiene todos los médicos disponibles para las reservas
     * @return array Listado de médicos
     */
    static public function mdlObtenerMedicos() {
        try {
            $stmt = Conexion::conectar()->prepare(
                "SELECT 
                    d.doctor_id::INTEGER as doctor_id, 
                    p.first_name || ' ' || p.last_name AS nombre_doctor,
                    d.doctor_estado
                FROM 
                    rh_doctors d
                INNER JOIN 
                    rh_person p ON d.person_id = p.person_id
                WHERE 
                    d.doctor_estado = 'ACTIVO'
                ORDER BY 
                    p.last_name, p.first_name ASC"
            );

            $stmt->execute();
            $medicos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Asegurarse de que doctor_id sea un entero
            foreach ($medicos as &$medico) {
                $medico['doctor_id'] = intval($medico['doctor_id']);
            }
            
            return $medicos;
        } catch (PDOException $e) {
            error_log("Error al obtener médicos: " . $e->getMessage(), 3, "c:/laragon/www/clinica/logs/reservas.log");
            return [];
        }
    }
    
    /**
     * Busca pacientes por nombre o documento
     * @param string $termino Término de búsqueda
     * @return array Listado de pacientes encontrados
     */
    static public function mdlBuscarPaciente($termino) {
        $termino = "%" . $termino . "%";
        
        $stmt = Conexion::conectar()->prepare(
            "SELECT 
                p.person_id, 
                p.first_name,
                p.last_name,
                p.document_number
            FROM 
                rh_person p
            WHERE 
                p.first_name ILIKE :termino OR
                p.last_name ILIKE :termino OR
                p.document_number ILIKE :termino
            ORDER BY 
                p.last_name, p.first_name ASC
            LIMIT 10"
        );

        $stmt->bindParam(":termino", $termino, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
      /**
     * Guarda un nuevo paciente
     * @param array $datos Datos del paciente
     * @return int|bool ID del paciente creado o false en caso de error
     */
    static public function mdlGuardarNuevoPaciente($datos) {
        try {
            // Generate a random document if none is provided
            if (empty($datos["document_number"])) {
                $datos["document_number"] = "TMP" . date("YmdHis") . rand(100, 999);
            }
            
            $stmt = Conexion::conectar()->prepare(
                "INSERT INTO rh_person (
                    first_name, last_name, document_number, created_at
                ) VALUES (
                    :first_name, :last_name, :document_number, CURRENT_TIMESTAMP
                ) RETURNING person_id"
            );

            $stmt->bindParam(":first_name", $datos["first_name"], PDO::PARAM_STR);
            $stmt->bindParam(":last_name", $datos["last_name"], PDO::PARAM_STR);
            $stmt->bindParam(":document_number", $datos["document_number"], PDO::PARAM_STR);
            
            if ($stmt->execute()) {
                $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
                return $resultado["person_id"];
            } else {
                return false;
            }
        } catch (Exception $e) {
            error_log("Error al guardar paciente: " . $e->getMessage(), 0);
            return false;
        }
    }
    
    /**
     * Guarda una nueva reserva en la base de datos
     * @param array $datos Datos de la reserva
     * @return mixed ID de la reserva creada o false en caso de error
     */
    static public function mdlGuardarReserva($datos) {
        try {
            error_log("Intentando guardar reserva: " . json_encode($datos), 3, 'c:/laragon/www/clinica/logs/reservas.log');
              // Verificar si ya existe una reserva en el mismo horario para el mismo doctor
            // La lógica modificada permite reservas adyacentes (cuando una termina exactamente cuando la otra comienza)
            $stmtVerificar = Conexion::conectar()->prepare(
                "SELECT COUNT(*) AS coincidencias
                FROM servicios_reservas
                WHERE doctor_id = :doctor_id 
                AND fecha_reserva = :fecha_reserva
                AND ((hora_inicio < :hora_fin AND hora_fin > :hora_inicio))
                AND reserva_estado IN ('PENDIENTE', 'CONFIRMADA')"
            );
            
            $stmtVerificar->bindParam(":doctor_id", $datos['doctor_id'], PDO::PARAM_INT);
            $stmtVerificar->bindParam(":fecha_reserva", $datos['fecha_reserva'], PDO::PARAM_STR);
            $stmtVerificar->bindParam(":hora_inicio", $datos['hora_inicio'], PDO::PARAM_STR);
            $stmtVerificar->bindParam(":hora_fin", $datos['hora_fin'], PDO::PARAM_STR);
            $stmtVerificar->execute();
            
            if ($stmtVerificar->fetchColumn() > 0) {
                error_log("Reserva rechazada: Ya existe una reserva en este horario para este médico", 3, 'c:/laragon/www/clinica/logs/reservas.log');
                return false;
            }            // Insertar la nueva reserva - adaptado al esquema actual de la base de datos
            $stmt = Conexion::conectar()->prepare(
                "INSERT INTO servicios_reservas (
                    servicio_id, doctor_id, paciente_id, fecha_reserva, 
                    hora_inicio, hora_fin, observaciones, reserva_estado, 
                    business_id, created_by, agenda_id, tarifa_id, seguro_id
                ) VALUES (
                    :servicio_id, :doctor_id, :paciente_id, :fecha_reserva, 
                    :hora_inicio, :hora_fin, :observaciones, :reserva_estado, 
                    :business_id, :created_by, :agenda_id, :tarifa_id, :seguro_id
                ) RETURNING reserva_id"
            );
            
            // Bindear los parámetros obligatorios
            $stmt->bindParam(":servicio_id", $datos['servicio_id'], PDO::PARAM_INT);
            $stmt->bindParam(":doctor_id", $datos['doctor_id'], PDO::PARAM_INT);

            $stmt->bindParam(":paciente_id", $datos['paciente_id'], PDO::PARAM_INT);
            $stmt->bindParam(":fecha_reserva", $datos['fecha_reserva'], PDO::PARAM_STR);
            $stmt->bindParam(":hora_inicio", $datos['hora_inicio'], PDO::PARAM_STR);
            $stmt->bindParam(":hora_fin", $datos['hora_fin'], PDO::PARAM_STR);
            $stmt->bindParam(":reserva_estado", $datos['reserva_estado'], PDO::PARAM_STR);
            
            // Bindear los parámetros opcionales
            $observaciones = isset($datos['observaciones']) ? $datos['observaciones'] : '';
            $stmt->bindParam(":observaciones", $observaciones, PDO::PARAM_STR);
            
            $businessId = isset($datos['business_id']) ? $datos['business_id'] : 1;
            $stmt->bindParam(":business_id", $businessId, PDO::PARAM_INT);
            
            $createdBy = isset($datos['created_by']) ? $datos['created_by'] : 1;
            $stmt->bindParam(":created_by", $createdBy, PDO::PARAM_INT);
            
           
            // Estos campos no estaban en la consulta original pero sí están en la estructura de la tabla
            $agendaId = isset($datos['agenda_id']) ? $datos['agenda_id'] : null;
            $stmt->bindParam(":agenda_id", $agendaId, $agendaId ? PDO::PARAM_INT : PDO::PARAM_NULL);
              $tarifaId = isset($datos['tarifa_id']) ? $datos['tarifa_id'] : null;
            $stmt->bindParam(":tarifa_id", $tarifaId, $tarifaId ? PDO::PARAM_INT : PDO::PARAM_NULL);
            
            $seguroId = isset($datos['seguro_id']) ? $datos['seguro_id'] : null;
            $stmt->bindParam(":seguro_id", $seguroId, $seguroId ? PDO::PARAM_INT : PDO::PARAM_NULL);
            
            error_log("SQL a ejecutar: INSERT INTO servicios_reservas...", 3, 'c:/laragon/www/clinica/logs/reservas.log');
            error_log("Parámetros: " . json_encode([
                'servicio_id' => $datos['servicio_id'],
                'doctor_id' => $datos['doctor_id'],
                'paciente_id' => $datos['paciente_id'],
                'fecha_reserva' => $datos['fecha_reserva'],
                'hora_inicio' => $datos['hora_inicio'],
                'hora_fin' => $datos['hora_fin'],
                'agenda_id' => $agendaId,
                'tarifa_id' => $tarifaId,
                'seguro_id' => $seguroId
            ]), 3, 'c:/laragon/www/clinica/logs/reservas.log');
            
            if ($stmt->execute()) {
                $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($resultado && isset($resultado['reserva_id'])) {
                    error_log("Reserva creada con ID: " . $resultado['reserva_id'], 3, 'c:/laragon/www/clinica/logs/reservas.log');
                    return $resultado['reserva_id'];
                } else {
                    error_log("Error: La consulta se ejecutó pero no se obtuvo un ID de reserva", 3, 'c:/laragon/www/clinica/logs/reservas.log');
                    return false;
                }
            } else {
                $errorInfo = $stmt->errorInfo();
                error_log("Error al insertar reserva: SQLSTATE=" . $errorInfo[0] . ", Driver-specific code=" . $errorInfo[1] . ", Message=" . $errorInfo[2], 3, 'c:/laragon/www/clinica/logs/reservas.log');
                return false;
            }
        } catch (PDOException $e) {
            error_log("Excepción PDO al guardar reserva: " . $e->getMessage() . "\n" . $e->getTraceAsString(), 3, 'c:/laragon/www/clinica/logs/reservas.log');
            return false;
        } catch (Exception $e) {
            error_log("Excepción general al guardar reserva: " . $e->getMessage() . "\n" . $e->getTraceAsString(),  3, 'c:/laragon/www/clinica/logs/reservas.log');
            return false;
        }
    }
    
    /**
     * Obtiene los proveedores de seguro médico
     * @return array Listado de proveedores de seguro médico
     */
    static public function mdlObtenerProveedoresSeguro() {
        try {
            $stmt = Conexion::conectar()->prepare(
                "SELECT 
                    pa.prov_id,
                    pa.prov_name,
                    pa.prov_lastname,
                    pa.prov_razon,
                    pa.prov_ruc,
                    tp.tipo_nombre
                FROM 
                    cm_proveedores_acreedores pa
                INNER JOIN 
                    cm_tipos_proveedores tp ON pa.tipo_cod = tp.tipo_cod
                WHERE 
                    -- tp.tipo_nombre = 'SEGURO MÉDICO' 
                    --AND 
                    pa.prov_is_active = true
                ORDER BY 
                    pa.prov_razon, pa.prov_name"
            );
            
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error en mdlObtenerProveedoresSeguro: " . $e->getMessage());
            return [];
        }
    }    /**
     * Busca reservas con filtros
     * @param string $fecha Fecha de reserva (opcional)
     * @param int $doctorId ID del doctor (opcional)
     * @param string $estado Estado de la reserva (opcional)
     * @param string $paciente Nombre del paciente para búsqueda (opcional)
     * @return array Lista de reservas que coinciden con los filtros
     */
    static public function mdlBuscarReservas($fecha = null, $doctorId = null, $estado = null, $paciente = null) {
        // Log detallado de parámetros recibidos
        error_log("mdlBuscarReservas INICIO: Fecha=" . ($fecha ?? "null") . 
                  ", DoctorID=" . ($doctorId ?? "null") . 
                  ", Estado=" . ($estado ?? "null") . 
                  ", Paciente=" . ($paciente ?? "null"), 
                  3, "c:/laragon/www/clinica/logs/reservas.log");
        
        try {
            // Verificar si existe la tabla de reservas
            $stmtCheck = Conexion::conectar()->prepare("SELECT to_regclass('public.servicios_reservas')");
            $stmtCheck->execute();
            $tablaReservasExiste = $stmtCheck->fetchColumn();
            
            if (!$tablaReservasExiste) {
                error_log("mdlBuscarReservas: La tabla servicios_reservas no existe", 3, "c:/laragon/www/clinica/logs/reservas.log");
                return [];
            }
            
            // Primero, verificar si existen reservas para este médico
            if ($doctorId !== null) {
                $checkStmt = Conexion::conectar()->prepare("SELECT COUNT(*) FROM servicios_reservas WHERE doctor_id = :doctor_id");
                $checkStmt->bindValue(':doctor_id', intval($doctorId), PDO::PARAM_INT);
                $checkStmt->execute();
                $count = $checkStmt->fetchColumn();
                error_log("mdlBuscarReservas: Verificación previa - Existen {$count} reservas con doctor_id={$doctorId}", 3, "c:/laragon/www/clinica/logs/reservas.log");
                
                // Comprobar si hay reservas para esta fecha y doctor
                $checkDateDoctorStmt = Conexion::conectar()->prepare("SELECT COUNT(*) FROM servicios_reservas WHERE doctor_id = :doctor_id AND fecha_reserva::date = :fecha::date");
                $checkDateDoctorStmt->bindValue(':doctor_id', intval($doctorId), PDO::PARAM_INT);
                $checkDateDoctorStmt->bindParam(':fecha', $fecha);
                $checkDateDoctorStmt->execute();
                $countDateDoctor = $checkDateDoctorStmt->fetchColumn();
                error_log("mdlBuscarReservas: Verificación previa - Existen {$countDateDoctor} reservas con doctor_id={$doctorId} y fecha={$fecha}", 3, "c:/laragon/www/clinica/logs/reservas.log");
            }
              
            // Construir la consulta SQL basada en el query proporcionado
            $sql = "SELECT 
                sr.reserva_id,
                sr.fecha_reserva,
                ad.dia_semana,
                sr.hora_inicio || ' - ' || sr.hora_fin as horario,
                ad.intervalo_minutos,
                s.sala_nombre,
                rp.first_name || ' - ' || rp.last_name as doctor,
                rp2.first_name || ' - ' || rp2.last_name as paciente,
                rs.serv_descripcion as nombre_servicio,
                rs.serv_monto as monto,
                sr.reserva_estado,
                sr.doctor_id as doctor_id_original
            FROM servicios_reservas sr 
            INNER JOIN agendas_detalle ad ON sr.agenda_id = ad.detalle_id 
            INNER JOIN salas s ON ad.sala_id = s.sala_id
            INNER JOIN rh_doctors rd ON sr.doctor_id = rd.doctor_id 
            INNER JOIN rh_person rp ON rd.person_id = rp.person_id
            INNER JOIN rh_person rp2 ON sr.paciente_id = rp2.person_id 
            INNER JOIN rs_servicios rs ON sr.servicio_id = rs.serv_id 
            WHERE 1=1";
            
            // Agregar condiciones según los filtros
            $params = [];
            
            // Filtro por fecha
            if ($fecha !== null && $fecha !== '') {
                $sql .= " AND sr.fecha_reserva::date = :fecha::date";
                $params[':fecha'] = $fecha;
                error_log("mdlBuscarReservas: Aplicando filtro de fecha: $fecha", 3, "c:/laragon/www/clinica/logs/reservas.log");
            }
              // Filtro por doctor - asegurarse de que es un entero
            if ($doctorId !== null) {
                $doctorIdInt = intval($doctorId);
                $sql .= " AND sr.doctor_id = :doctor_id";
                $params[':doctor_id'] = $doctorIdInt;
                error_log("mdlBuscarReservas: Aplicando filtro de doctor: {$doctorIdInt} (original: {$doctorId})", 3, "c:/laragon/www/clinica/logs/reservas.log");
                
                // Verificación adicional para diagnosticar
                $checkStmt = Conexion::conectar()->prepare("SELECT * FROM servicios_reservas WHERE doctor_id = :doctor_id LIMIT 1");
                $checkStmt->bindValue(':doctor_id', $doctorIdInt, PDO::PARAM_INT);
                $checkStmt->execute();
                $checkResult = $checkStmt->fetch(PDO::FETCH_ASSOC);
                if ($checkResult) {
                    error_log("mdlBuscarReservas: Doctor {$doctorIdInt} SÍ tiene reservas en la base de datos. Primera: " . json_encode($checkResult), 3, "c:/laragon/www/clinica/logs/reservas.log");
                } else {
                    error_log("mdlBuscarReservas: Doctor {$doctorIdInt} NO tiene reservas en la base de datos.", 3, "c:/laragon/www/clinica/logs/reservas.log");
                }
            }
            
            // Filtro por estado
            if ($estado !== null && $estado !== '') {
                $sql .= " AND sr.reserva_estado = :estado";
                $params[':estado'] = $estado;
                error_log("mdlBuscarReservas: Aplicando filtro de estado: $estado", 3, "c:/laragon/www/clinica/logs/reservas.log");
            }
            
            // Filtro por paciente (búsqueda por nombre o apellido)
            if ($paciente !== null && $paciente !== '') {
                $sql .= " AND (rp2.first_name ILIKE :paciente OR rp2.last_name ILIKE :paciente OR (rp2.first_name || ' ' || rp2.last_name) ILIKE :paciente)";
                $params[':paciente'] = '%' . $paciente . '%';
                error_log("mdlBuscarReservas: Aplicando filtro de paciente: $paciente", 3, "c:/laragon/www/clinica/logs/reservas.log");
            }
            
            // Ordenar los resultados
            $sql .= " ORDER BY sr.fecha_reserva DESC, ad.hora_inicio ASC";
            
            error_log("mdlBuscarReservas: SQL=$sql", 3, "c:/laragon/www/clinica/logs/reservas.log");
            
            $stmt = Conexion::conectar()->prepare($sql);
              
            // Logging detallado de parámetros
            error_log("mdlBuscarReservas: Parámetros a bindear: " . json_encode($params), 3, "c:/laragon/www/clinica/logs/reservas.log");
            
            // Bindear parámetros
            foreach ($params as $param => $value) {                if ($param === ':doctor_id') {
                    // Garantizar que doctor_id sea un entero
                    $doctorIdInt = intval($value);
                    $stmt->bindValue($param, $doctorIdInt, PDO::PARAM_INT);
                    error_log("mdlBuscarReservas: Bindeando $param = $doctorIdInt (original: $value) como PARAM_INT", 3, "c:/laragon/www/clinica/logs/reservas.log");
                } else if (strpos($param, ':paciente') !== false) {
                    $stmt->bindValue($param, $value, PDO::PARAM_STR);
                    error_log("mdlBuscarReservas: Bindeando $param = $value como PARAM_STR", 3, "c:/laragon/www/clinica/logs/reservas.log");
                } else {
                    $stmt->bindParam($param, $value);
                    error_log("mdlBuscarReservas: Bindeando $param = $value con bindParam", 3, "c:/laragon/www/clinica/logs/reservas.log");
                }
            }
            
            $stmt->execute();
            $reservas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            error_log("mdlBuscarReservas: Se encontraron " . count($reservas) . " reservas", 3, "c:/laragon/www/clinica/logs/reservas.log");
            if (count($reservas) > 0) {
                error_log("mdlBuscarReservas: Primera reserva: " . json_encode($reservas[0]), 3, "c:/laragon/www/clinica/logs/reservas.log");
            } else {
                // Si no hay resultados, realizar una consulta directa para ver si hay datos
                if ($doctorId !== null) {
                    $simpleCheck = Conexion::conectar()->prepare("SELECT reserva_id, doctor_id, fecha_reserva, reserva_estado FROM servicios_reservas WHERE doctor_id = :doctor_id AND fecha_reserva::date = :fecha::date");
                    $simpleCheck->bindValue(':doctor_id', intval($doctorId), PDO::PARAM_INT);
                    $simpleCheck->bindParam(':fecha', $fecha);
                    $simpleCheck->execute();
                    $simpleResults = $simpleCheck->fetchAll(PDO::FETCH_ASSOC);
                    error_log("mdlBuscarReservas: Consulta directa - Resultados para doctor_id={$doctorId}, fecha={$fecha}: " . json_encode($simpleResults), 3, "c:/laragon/www/clinica/logs/reservas.log");
                }
            }
            
            return $reservas;
        } catch (PDOException $e) {
            error_log("Error al buscar reservas: " . $e->getMessage(), 3, "c:/laragon/www/clinica/logs/reservas.log");
            return [];
        }
    }

    /**
     * Busca reservas por doctor específico (consulta directa simplificada)
     * @param int $doctorId ID del doctor     * @param string $fecha Fecha de la reserva (opcional)
     * @param string $estado Estado de la reserva (opcional)
     * @param string $paciente Nombre del paciente para búsqueda (opcional)
     * @return array Lista de reservas encontradas
     */
    static public function mdlBuscarReservasPorDoctor($doctorId, $fecha = null, $estado = null, $paciente = null) {
        try {
            error_log("mdlBuscarReservasPorDoctor: Ejecutando consulta directa para doctor_id=$doctorId, fecha=" . 
                     ($fecha ? $fecha : "NULL") . ", estado=" . ($estado ?? "NULL") . ", paciente=" . ($paciente ?? "NULL"), 
                     3, "c:/laragon/www/clinica/logs/reservas.log");
            
            $sql = "SELECT 
                sr.reserva_id,
                sr.servicio_id,
                sr.doctor_id,
                sr.paciente_id,
                sr.fecha_reserva,
                sr.hora_inicio,
                sr.hora_fin,
                sr.reserva_estado,
                sr.observaciones,
                sr.business_id,
                sr.created_at,
                sr.updated_at,
                sr.agenda_id,
                sr.sala_id,
                s.sala_nombre,
                sr.tarifa_id,
                rp.first_name ||' - ' || rp.last_name as doctor,
                rp2.first_name ||' - ' || rp2.last_name as paciente,
                rs.serv_descripcion,
                rs.serv_monto
            FROM servicios_reservas sr 
            INNER JOIN rh_doctors rd ON sr.doctor_id = rd.doctor_id 
            INNER JOIN rh_person rp ON rd.person_id = rp.person_id 
            INNER JOIN rh_person rp2 ON sr.paciente_id = rp2.person_id 
            INNER JOIN rs_servicios rs ON sr.servicio_id = rs.serv_id 
            inner join agendas_detalle ad on sr.agenda_id = ad.detalle_id 
            inner join salas s on s.sala_id = ad.sala_id
            WHERE sr.doctor_id = :doctor_id";
            
            if ($fecha) {
                $sql .= " AND sr.fecha_reserva::date = :fecha::date";
            }
            
            // Filtro por estado
            if ($estado !== null && $estado !== '') {
                $sql .= " AND sr.reserva_estado = :estado";
            }
            
            // Filtro por paciente
            if ($paciente !== null && trim($paciente) !== '') {
                $sql .= " AND (rp_paciente.first_name ILIKE :paciente OR rp_paciente.last_name ILIKE :paciente OR (rp_paciente.first_name || ' ' || rp_paciente.last_name) ILIKE :paciente)";
            }
            
            $sql .= " ORDER BY sr.fecha_reserva DESC, sr.hora_inicio ASC";
            
            $stmt = Conexion::conectar()->prepare($sql);
            $stmt->bindValue(':doctor_id', intval($doctorId), PDO::PARAM_INT);
            
            if ($fecha) {
                $stmt->bindParam(':fecha', $fecha, PDO::PARAM_STR);
            }
            
            // Bindear parámetro de estado si se proporciona
            if ($estado !== null && $estado !== '') {
                $stmt->bindParam(':estado', $estado, PDO::PARAM_STR);
            }
            
            // Bindear parámetro de paciente si se proporciona
            if ($paciente !== null && trim($paciente) !== '') {
                $pacienteParam = '%' . trim($paciente) . '%';
                $stmt->bindParam(':paciente', $pacienteParam, PDO::PARAM_STR);
            }
            
            $stmt->execute();
            $reservas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            error_log("mdlBuscarReservasPorDoctor: Encontradas " . count($reservas) . " reservas", 
                     3, "c:/laragon/www/clinica/logs/reservas.log");
            
            return $reservas;
        } catch (PDOException $e) {
            error_log("Error en mdlBuscarReservasPorDoctor: " . $e->getMessage(), 
                     3, "c:/laragon/www/clinica/logs/reservas.log");
            return [];
        }
    }

    /**
     * Obtiene todos los servicios de la tabla rs_servicios
     * @return array Listado de servicios
     */
    static public function mdlObtenerTodosRsServicios() {
        try {
            $stmt = Conexion::conectar()->prepare(
                "SELECT 
                rs.serv_id, 
                rs.serv_codigo, 
                rs.serv_descripcion,
                rs.serv_descripcion_factura,
                rs.serv_monto,
                rs.serv_tte,
                rs.serv_rtte,
                rs.serv_uso_equipo,
                rs.serv_der_sala,
                rs.tserv_cod,
                rs.is_active,
                rst.servicio as categoria_nombre
            FROM 
                rs_servicios rs
            INNER JOIN 
                rs_servicios_tipos rst ON rs.tserv_cod = rst.tserv_cod
            ORDER BY 
                rs.serv_id DESC"
            );
            
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Error en mdlObtenerTodosRsServicios: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene los tipos de servicios
     * @return array Listado de tipos de servicios
     */
    static public function mdlObtenerTiposRsServicio() {
        try {
            $stmt = Conexion::conectar()->prepare(
                "SELECT 
                tserv_cod,
                servicio
            FROM 
                rs_servicios_tipos
            WHERE 
                is_active = true
            ORDER BY 
                servicio"
            );
            
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Error en mdlObtenerTiposRsServicio: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Crea un nuevo servicio
     * @param array $datos Datos del servicio
     * @return array Respuesta de la operación
     */
    static public function mdlCrearRsServicio($datos) {
        try {
            $db = Conexion::conectar();
            $stmt = $db->prepare(                "INSERT INTO rs_servicios (
                serv_codigo, 
                serv_descripcion, 
                serv_descripcion_factura,
                serv_monto,
                serv_tte,
                tserv_cod,
                is_active,
                user_id,
                business_id
            ) VALUES (
                :codigo,
                :descripcion,
                :descripcion_factura,
                :monto,
                :duracion,
                :tipo_servicio,
                :estado,
                :user_id,
                :business_id
            ) RETURNING serv_id"
            );
            
            // Asegurar de tener valores para los campos requeridos
            $datos['business_id'] = $_SESSION['business_id'] ?? 1;
            $datos['user_id'] = $_SESSION['id_usuario'] ?? 1;
              $stmt->bindParam(":codigo", $datos['serv_codigo'], PDO::PARAM_STR);
            $stmt->bindParam(":descripcion", $datos['serv_descripcion'], PDO::PARAM_STR);
            $stmt->bindParam(":descripcion_factura", $datos['serv_descripcion'], PDO::PARAM_STR); // Usamos la misma descripción
            $stmt->bindParam(":monto", $datos['serv_monto'], PDO::PARAM_STR);
            $stmt->bindParam(":duracion", $datos['serv_tte'], PDO::PARAM_STR);
            $stmt->bindParam(":tipo_servicio", $datos['tserv_cod'], PDO::PARAM_INT);
            $stmt->bindParam(":estado", $datos['is_active'], PDO::PARAM_BOOL);
            $stmt->bindParam(":user_id", $datos['user_id'], PDO::PARAM_INT);
            $stmt->bindParam(":business_id", $datos['business_id'], PDO::PARAM_INT);
            
            $stmt->execute();
            $idServicio = $stmt->fetchColumn();
            
            return [
                'exito' => true,
                'mensaje' => 'Servicio creado correctamente',
                'id' => $idServicio
            ];
        } catch(PDOException $e) {
            error_log("Error en mdlCrearRsServicio: " . $e->getMessage());
            return [
                'exito' => false,
                'mensaje' => 'Error al crear el servicio: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Actualiza un servicio existente
     * @param array $datos Datos del servicio
     * @return array Respuesta de la operación
     */
    static public function mdlActualizarRsServicio($datos) {
        try {
            $db = Conexion::conectar();
            $stmt = $db->prepare(
                "UPDATE rs_servicios SET
                serv_codigo = :codigo,
                serv_descripcion = :descripcion,
                serv_descripcion_factura = :descripcion_factura,
                serv_monto = :monto,
                serv_tte = :duracion,
                tserv_cod = :tipo_servicio,
                is_active = :estado
            WHERE serv_id = :id"
            );
              $stmt->bindParam(":id", $datos['serv_id'], PDO::PARAM_INT);
            $stmt->bindParam(":codigo", $datos['serv_codigo'], PDO::PARAM_STR);
            $stmt->bindParam(":descripcion", $datos['serv_descripcion'], PDO::PARAM_STR);
            $stmt->bindParam(":descripcion_factura", $datos['serv_descripcion'], PDO::PARAM_STR); // Usamos la misma descripción 
            $stmt->bindParam(":monto", $datos['serv_monto'], PDO::PARAM_STR);
            $stmt->bindParam(":duracion", $datos['serv_tte'], PDO::PARAM_STR);
            $stmt->bindParam(":tipo_servicio", $datos['tserv_cod'], PDO::PARAM_INT);
            $stmt->bindParam(":estado", $datos['is_active'], PDO::PARAM_BOOL);
            
            $stmt->execute();
            
            return [
                'exito' => true,
                'mensaje' => 'Servicio actualizado correctamente'
            ];
        } catch(PDOException $e) {
            error_log("Error en mdlActualizarRsServicio: " . $e->getMessage());
            return [
                'exito' => false,
                'mensaje' => 'Error al actualizar el servicio: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Elimina un servicio (desactivación lógica)
     * @param int $id ID del servicio
     * @return array Respuesta de la operación
     */
    static public function mdlEliminarRsServicio($id) {
        try {
            $db = Conexion::conectar();
            $stmt = $db->prepare(
                "UPDATE rs_servicios SET
                is_active = false
            WHERE serv_id = :id"
            );
            
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            $stmt->execute();
            
            return [
                'exito' => true,
                'mensaje' => 'Servicio eliminado correctamente'
            ];
        } catch(PDOException $e) {
            error_log("Error en mdlEliminarRsServicio: " . $e->getMessage());
            return [
                'exito' => false,
                'mensaje' => 'Error al eliminar el servicio: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Crea un nuevo tipo de servicio
     * @param string $nombre Nombre del tipo de servicio
     * @return array Respuesta de la operación
     */
    static public function mdlCrearTipoRsServicio($nombre) {
        try {
            $db = Conexion::conectar();
            $stmt = $db->prepare(
                "INSERT INTO rs_servicios_tipos (
                servicio,
                is_active,
                user_id,
                business_id
            ) VALUES (
                :nombre,
                true,
                :user_id,
                :business_id
            ) RETURNING tserv_cod"
            );
            
            $userId = $_SESSION['id_usuario'] ?? 1;
            $businessId = $_SESSION['business_id'] ?? 1;
            
            $stmt->bindParam(":nombre", $nombre, PDO::PARAM_STR);
            $stmt->bindParam(":user_id", $userId, PDO::PARAM_INT);
            $stmt->bindParam(":business_id", $businessId, PDO::PARAM_INT);
            
            $stmt->execute();
            $idTipo = $stmt->fetchColumn();
            
            return [
                'exito' => true,
                'mensaje' => 'Tipo de servicio creado correctamente',
                'id' => $idTipo
            ];
        } catch(PDOException $e) {
            error_log("Error en mdlCrearTipoRsServicio: " . $e->getMessage());
            return [
                'exito' => false,
                'mensaje' => 'Error al crear el tipo de servicio: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Actualiza un tipo de servicio
     * @param int $id ID del tipo
     * @param string $nombre Nuevo nombre
     * @return array Respuesta de la operación
     */
    static public function mdlActualizarTipoRsServicio($id, $nombre) {
        try {
            $db = Conexion::conectar();
            $stmt = $db->prepare(
                "UPDATE rs_servicios_tipos SET
                servicio = :nombre
            WHERE tserv_cod = :id"
            );
            
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            $stmt->bindParam(":nombre", $nombre, PDO::PARAM_STR);
            
            $stmt->execute();
            
            return [
                'exito' => true,
                'mensaje' => 'Tipo de servicio actualizado correctamente'
            ];
        } catch(PDOException $e) {
            error_log("Error en mdlActualizarTipoRsServicio: " . $e->getMessage());
            return [
                'exito' => false,
                'mensaje' => 'Error al actualizar el tipo de servicio: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Elimina un tipo de servicio (desactivación lógica)
     * @param int $id ID del tipo
     * @return array Respuesta de la operación
     */
    static public function mdlEliminarTipoRsServicio($id) {
        try {
            // Primero verificamos si hay servicios usando este tipo
            $db = Conexion::conectar();
            $stmt = $db->prepare(
                "SELECT COUNT(*) FROM rs_servicios WHERE tserv_cod = :id AND is_active = true"
            );
            
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            $stmt->execute();
            $count = $stmt->fetchColumn();
            
            if ($count > 0) {
                return [
                    'exito' => false,
                    'mensaje' => 'No se puede eliminar este tipo porque está siendo utilizado por ' . $count . ' servicio(s)'
                ];
            }
            
            // Si no hay servicios asociados, procedemos a desactivar
            $stmt = $db->prepare(
                "UPDATE rs_servicios_tipos SET
                is_active = false
            WHERE tserv_cod = :id"
            );
            
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            $stmt->execute();
            
            return [
                'exito' => true,
                'mensaje' => 'Tipo de servicio eliminado correctamente'
            ];
        } catch(PDOException $e) {
            error_log("Error en mdlEliminarTipoRsServicio: " . $e->getMessage());
            return [
                'exito' => false,
                'mensaje' => 'Error al eliminar el tipo de servicio: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Filtra servicios según criterios
     * @param array $filtros Criterios de filtrado
     * @return array Listado filtrado de servicios
     */
    static public function mdlFiltrarRsServicios($filtros) {
        try {
            $condiciones = [];
            $parametros = [];
            
            // Construir condiciones de filtrado
            if (!empty($filtros['codigo'])) {
                $condiciones[] = "rs.serv_codigo ILIKE :codigo";
                $parametros[':codigo'] = '%' . $filtros['codigo'] . '%';
            }
            
            if (!empty($filtros['descripcion'])) {
                $condiciones[] = "rs.serv_descripcion ILIKE :descripcion";
                $parametros[':descripcion'] = '%' . $filtros['descripcion'] . '%';
            }
            
            if (!empty($filtros['tipo']) && $filtros['tipo'] != "0") {
                $condiciones[] = "rs.tserv_cod = :tipo";
                $parametros[':tipo'] = $filtros['tipo'];
            }
            
            $whereSql = count($condiciones) > 0 ? "AND " . implode(" AND ", $condiciones) : "";
            
            $sql = "SELECT 
                rs.serv_id, 
                rs.serv_codigo, 
                rs.serv_descripcion,
                rs.serv_descripcion_factura,
                rs.serv_monto,
                rs.serv_tte,
                rs.serv_rtte,
                rs.serv_uso_equipo,
                rs.serv_der_sala,
                rs.tserv_cod,
                rs.is_active,
                rst.servicio as categoria_nombre
            FROM 
                rs_servicios rs
            INNER JOIN 
                rs_servicios_tipos rst ON rs.tserv_cod = rst.tserv_cod
            WHERE 1=1 {$whereSql}
            ORDER BY 
                rs.serv_id DESC";
            
            $stmt = Conexion::conectar()->prepare($sql);
            
            // Vincular parámetros
            foreach ($parametros as $param => $value) {
                $stmt->bindValue($param, $value);
            }
            
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Error en mdlFiltrarRsServicios: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene las reservas existentes para un doctor y fecha específica
     * @param int $doctorId ID del doctor
     * @param string $fecha Fecha para la verificación (formato YYYY-MM-DD)
     * @return array Lista de reservas con horario de inicio y fin
     */
    static public function mdlObtenerReservasExistentes($doctorId, $fecha) {
        try {
            // Verificar si existe la tabla de reservas
            $stmtCheck = Conexion::conectar()->prepare("SELECT to_regclass('public.servicios_reservas')");
            $stmtCheck->execute();
            $tablaReservasExiste = $stmtCheck->fetchColumn();
            
            if (!$tablaReservasExiste) {
                error_log("mdlObtenerReservasExistentes: La tabla servicios_reservas no existe", 
                          3, "c:/laragon/www/clinica/logs/database.log");
                return [];
            }
            
            // Consulta para obtener las reservas existentes para ese doctor y día
            $stmt = Conexion::conectar()->prepare(
                "SELECT 
                    reserva_id,
                    servicio_id,
                    agenda_id,
                    doctor_id,
                    fecha_reserva,
                    hora_inicio,
                    hora_fin
                FROM 
                    servicios_reservas
                WHERE 
                    doctor_id = :doctor_id
                    AND fecha_reserva = :fecha_reserva
                    AND reserva_estado IN ('CONFIRMADA', 'PENDIENTE')
                ORDER BY 
                    hora_inicio ASC"
            );
            
            $stmt->bindParam(":doctor_id", $doctorId, PDO::PARAM_INT);
            $stmt->bindParam(":fecha_reserva", $fecha, PDO::PARAM_STR);
            $stmt->execute();
            
            $reservas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            error_log("mdlObtenerReservasExistentes: Encontradas " . count($reservas) . 
                      " reservas para doctor ID $doctorId en fecha $fecha", 
                      3, "c:/laragon/www/clinica/logs/database.log");
            
            if (count($reservas) > 0) {
                error_log("Primera reserva: " . json_encode($reservas[0]), 
                          3, "c:/laragon/www/clinica/logs/database.log");
            }
            
            return $reservas;
        } catch (PDOException $e) {
            error_log("Error al obtener reservas existentes: " . $e->getMessage(), 
                      3, "c:/laragon/www/clinica/logs/database.log");
            return [];
        }
    }
}