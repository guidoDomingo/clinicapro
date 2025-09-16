<?php
require_once "conexion.php";

class ModelServiciosSimplificado {
    /**
     * Versión simplificada para generar slots disponibles basados en intervalos
     * @param int $doctorId ID del doctor
     * @param string $fecha Fecha para la verificación (formato YYYY-MM-DD)
     * @return array Listado de slots de horarios
     */
    static public function mdlGenerarSlotsSimple($doctorId, $fecha) {
        try {
            error_log("Generando slots simples para - DoctorID: {$doctorId}, Fecha: {$fecha}", 3, '/var/log/clinica/servicios.log');
            
            // Duración predeterminada de 30 minutos
            $duracionServicio = 30;
            
            // Verificar que el formato de la fecha sea correcto
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
                error_log("Formato de fecha incorrecto: " . $fecha, 3, '/var/log/clinica/servicios.log');
                return [];
            }
            
            // Asegurarse de que la fecha sea válida
            $fechaObj = DateTime::createFromFormat('Y-m-d', $fecha);
            if (!$fechaObj || $fechaObj->format('Y-m-d') !== $fecha) {
                error_log("Fecha inválida: " . $fecha, 3, '/var/log/clinica/servicios.log');
                return [];
            }
            
            // Determinar el día de la semana para la fecha
            $diaSemanaNum = (int)$fechaObj->format('N'); // 1 (lunes) a 7 (domingo) según ISO-8601
            $diasSemanaTexto = [1 => 'LUNES', 2 => 'MARTES', 3 => 'MIERCOLES', 4 => 'JUEVES', 5 => 'VIERNES', 6 => 'SABADO', 7 => 'DOMINGO'];
            $diaSemanaTexto = $diasSemanaTexto[$diaSemanaNum];
            
            error_log("Buscando horarios para: Doctor ID={$doctorId}, Día={$diaSemanaTexto}", 3, '/var/log/clinica/servicios.log');
            
            // Obtener los horarios del doctor para el día de la semana correspondiente
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
            );
            
            $stmt->bindParam(":doctor_id", $doctorId, PDO::PARAM_INT);
            $stmt->bindParam(":dia_semana", $diaSemanaTexto, PDO::PARAM_STR);
            $stmt->execute();
            $horarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            error_log("Horarios encontrados: " . count($horarios), 3, '/var/log/clinica/servicios.log');
            
            if (empty($horarios)) {
                error_log("No se encontraron horarios para el día " . $diaSemanaTexto, 3, '/var/log/clinica/servicios.log');
                return [];
            }
            
            // Generar slots disponibles para cada horario base
            $slotsDisponibles = [];
            foreach ($horarios as $horario) {
                // Convertir las cadenas de hora a objetos DateTime para manipulación
                $horaInicio = new DateTime($horario['hora_inicio']);
                $horaFin = new DateTime($horario['hora_fin']);
                $intervaloMinutos = $horario['intervalo_minutos'] ?? 30;
                
                // Crear una variable para llevar el seguimiento de la hora actual mientras generamos slots
                $horaActual = clone $horaInicio;
                
                // Generar slots mientras haya tiempo disponible
                while ($horaActual < $horaFin) {
                    $slotInicio = clone $horaActual;
                    $slotFin = clone $horaActual;
                    $slotFin->add(new DateInterval('PT' . $duracionServicio . 'M'));
                    
                    // Si el slot termina antes o igual que el fin del horario, agregarlo a la lista
                    if ($slotFin <= $horaFin) {
                        $slotsDisponibles[] = [
                            'agenda_id' => $horario['agenda_id'],
                            'horario_id' => $horario['horario_id'],
                            'turno_id' => $horario['turno_id'],
                            'turno_nombre' => $horario['turno_nombre'],
                            'sala_id' => $horario['sala_id'],
                            'sala_nombre' => $horario['sala_nombre'],
                            'doctor_id' => $horario['doctor_id'],
                            'nombre_doctor' => $horario['nombre_doctor'],
                            'hora_inicio' => $slotInicio->format('H:i:s'),
                            'hora_fin' => $slotFin->format('H:i:s'),
                            'duracion_minutos' => $duracionServicio,
                            'tipo_horario' => 'NORMAL',
                        ];
                    }
                    
                    // Avanzar al siguiente slot
                    $horaActual = clone $slotInicio;
                    $horaActual->add(new DateInterval('PT' . $intervaloMinutos . 'M'));
                }
            }
            
            error_log("Total de slots disponibles generados: " . count($slotsDisponibles), 3, '/var/log/clinica/servicios.log');
            
            return $slotsDisponibles;
            
        } catch (Exception $e) {
            error_log("Error al generar slots disponibles: " . $e->getMessage(), 3, '/var/log/clinica/servicios.log');
            return [];
        }
    }
}
