<?php
/**
 * Función mdlGenerarSlotsDisponibles corregida
 */
static public function mdlGenerarSlotsDisponibles($servicioId, $doctorId, $fecha) {
    try {
        // CORREGIDO: Cambiar zona horaria a Asunción
        date_default_timezone_set('America/Asuncion');
        $horaServidor = date('Y-m-d H:i:s');
        $zonaTiempo = date_default_timezone_get();
        
        error_log("=== INICIO mdlGenerarSlotsDisponibles ===", 3, 'c:/laragon/www/clinica/logs/servicios.log');
        error_log("Parámetros: ServicioID={$servicioId}, DoctorID={$doctorId}, Fecha={$fecha}", 3, 'c:/laragon/www/clinica/logs/servicios.log');
        error_log("Hora servidor: {$horaServidor}, Zona: {$zonaTiempo}", 3, 'c:/laragon/www/clinica/logs/servicios.log');
        
        // Validar formato de fecha
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
            error_log("ERROR: Formato de fecha incorrecto: {$fecha}", 3, 'c:/laragon/www/clinica/logs/servicios.log');
            return [];
        }
        
        // Validar fecha
        $fechaObj = DateTime::createFromFormat('Y-m-d', $fecha);
        if (!$fechaObj || $fechaObj->format('Y-m-d') !== $fecha) {
            error_log("ERROR: Fecha inválida: {$fecha}", 3, 'c:/laragon/www/clinica/logs/servicios.log');
            return [];
        }
        
        // Determinar día de la semana
        $diaSemanaNum = (int)$fechaObj->format('N'); // 1=lunes, 7=domingo
        $diasSemanaTexto = [1 => 'LUNES', 2 => 'MARTES', 3 => 'MIERCOLES', 4 => 'JUEVES', 5 => 'VIERNES', 6 => 'SABADO', 7 => 'DOMINGO'];
        $diaSemanaTexto = $diasSemanaTexto[$diaSemanaNum];
        
        error_log("Día de la semana: {$diaSemanaTexto} (num: {$diaSemanaNum})", 3, 'c:/laragon/www/clinica/logs/servicios.log');
        
        // USAR LA CONSULTA SQL ESPECÍFICA PROPORCIONADA
        $stmt = Conexion::conectar()->prepare(
            "SELECT 
                rp.person_id,
                rp.first_name,
                ad.detalle_id,
                ad.agenda_id,
                ad.turno_id,
                ad.sala_id,
                ad.dia_semana,
                ad.hora_inicio,
                ad.hora_fin,
                ad.intervalo_minutos,
                ad.cupo_maximo,
                ad.detalle_estado,
                s.sala_nombre,
                t.turno_nombre
            FROM agendas_detalle ad 
            INNER JOIN agendas_cabecera ac ON ad.agenda_id = ac.agenda_id 
            INNER JOIN rh_doctors rd ON rd.doctor_id = ac.medico_id 
            INNER JOIN rh_person rp ON rd.person_id = rp.person_id 
            INNER JOIN salas s ON ad.sala_id = s.sala_id
            INNER JOIN turnos t ON ad.turno_id = t.turno_id
            WHERE 
                ac.medico_id = :doctor_id
                AND ad.dia_semana = :dia_semana
                AND ad.detalle_estado = true
                AND ac.agenda_estado = true
            ORDER BY ad.hora_inicio ASC"
        );
        
        $stmt->bindParam(":doctor_id", $doctorId, PDO::PARAM_INT);
        $stmt->bindParam(":dia_semana", $diaSemanaTexto, PDO::PARAM_STR);
        
        error_log("Ejecutando consulta para Doctor ID={$doctorId}, Día={$diaSemanaTexto}", 3, 'c:/laragon/www/clinica/logs/servicios.log');
        $stmt->execute();
        $horarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        error_log("Horarios encontrados: " . count($horarios), 3, 'c:/laragon/www/clinica/logs/servicios.log');
        if (!empty($horarios)) {
            error_log("Primer horario: " . json_encode($horarios[0]), 3, 'c:/laragon/www/clinica/logs/servicios.log');
        }
        
        if (empty($horarios)) {
            error_log("No se encontraron horarios para el día {$diaSemanaTexto}", 3, 'c:/laragon/www/clinica/logs/servicios.log');
            return [];
        }
        
        // Obtener reservas existentes para verificar disponibilidad
        $reservasExistentes = [];
        try {
            $stmtReservas = Conexion::conectar()->prepare(
                "SELECT 
                    reserva_id,
                    servicio_id,
                    agenda_id,
                    doctor_id,
                    fecha_reserva,
                    hora_inicio,
                    hora_fin
                FROM servicios_reservas
                WHERE 
                    doctor_id = :doctor_id
                    AND fecha_reserva = :fecha_reserva
                    AND reserva_estado IN ('CONFIRMADA', 'PENDIENTE')
                ORDER BY hora_inicio ASC"
            );
            
            $stmtReservas->bindParam(":doctor_id", $doctorId, PDO::PARAM_INT);
            $stmtReservas->bindParam(":fecha_reserva", $fecha, PDO::PARAM_STR);
            $stmtReservas->execute();
            $reservasExistentes = $stmtReservas->fetchAll(PDO::FETCH_ASSOC);
            
            error_log("Reservas existentes: " . count($reservasExistentes), 3, 'c:/laragon/www/clinica/logs/servicios.log');
        } catch (PDOException $e) {
            error_log("Error al obtener reservas existentes: " . $e->getMessage(), 3, 'c:/laragon/www/clinica/logs/servicios.log');
        }
        
        // Generar slots disponibles
        $slotsDisponibles = [];
        foreach ($horarios as $horario) {
            error_log("Procesando horario: Agenda={$horario['agenda_id']}, Intervalo={$horario['intervalo_minutos']}min", 3, 'c:/laragon/www/clinica/logs/servicios.log');
            
            // USAR EL INTERVALO DE AGENDAS_DETALLE
            $intervaloMinutos = (int)$horario['intervalo_minutos'];
            
            // Convertir horas a DateTime
            $horaInicio = new DateTime($fecha . ' ' . $horario['hora_inicio']);
            $horaFin = new DateTime($fecha . ' ' . $horario['hora_fin']);
            
            error_log("Rango: " . $horaInicio->format('H:i') . " - " . $horaFin->format('H:i') . " (intervalo: {$intervaloMinutos}min)", 3, 'c:/laragon/www/clinica/logs/servicios.log');
            
            // Generar slots cada intervalo de minutos
            $horaActual = clone $horaInicio;
            while ($horaActual < $horaFin) {
                $slotFin = clone $horaActual;
                $slotFin->add(new DateInterval('PT' . $intervaloMinutos . 'M'));
                
                // Verificar que el slot no exceda el horario fin
                if ($slotFin > $horaFin) {
                    break;
                }
                
                $slotInicioStr = $horaActual->format('H:i:s');
                $slotFinStr = $slotFin->format('H:i:s');
                
                // Verificar disponibilidad vs reservas existentes
                $disponible = true;
                foreach ($reservasExistentes as $reserva) {
                    $reservaInicio = $reserva['hora_inicio'];
                    $reservaFin = $reserva['hora_fin'];
                    
                    // Verificar solapamiento
                    if (($slotInicioStr < $reservaFin) && ($slotFinStr > $reservaInicio)) {
                        $disponible = false;
                        error_log("Slot ocupado: {$slotInicioStr}-{$slotFinStr} vs reserva {$reservaInicio}-{$reservaFin}", 3, 'c:/laragon/www/clinica/logs/servicios.log');
                        break;
                    }
                }
                
                // Crear el slot
                $slot = [
                    'hora_inicio' => $slotInicioStr,
                    'hora_fin' => $slotFinStr,
                    'sala_id' => $horario['sala_id'],
                    'sala_nombre' => $horario['sala_nombre'],
                    'turno_id' => $horario['turno_id'],
                    'turno_nombre' => $horario['turno_nombre'],
                    'agenda_id' => $horario['agenda_id'],
                    'detalle_id' => $horario['detalle_id'],
                    'disponible' => $disponible,
                    'doctor_id' => $doctorId,
                    'doctor_nombre' => $horario['first_name'],
                    'intervalo_minutos' => $intervaloMinutos,
                    'fecha' => $fecha
                ];
                
                $slotsDisponibles[] = $slot;
                
                // Avanzar al siguiente slot
                $horaActual->add(new DateInterval('PT' . $intervaloMinutos . 'M'));
            }
        }
        
        error_log("Total de slots generados: " . count($slotsDisponibles), 3, 'c:/laragon/www/clinica/logs/servicios.log');
        error_log("=== FIN mdlGenerarSlotsDisponibles ===", 3, 'c:/laragon/www/clinica/logs/servicios.log');
        
        return $slotsDisponibles;
        
    } catch (Exception $e) {
        error_log("ERROR en mdlGenerarSlotsDisponibles: " . $e->getMessage(), 3, 'c:/laragon/www/clinica/logs/servicios.log');
        return [];
    }
}
?>
