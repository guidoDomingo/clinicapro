<?php
require_once "config/database.php";

try {
    $database = new Database();
    $pdo = $database->getConnection();
    
    echo "<h2>Diagnóstico de Reservas para Servicio 8</h2>";
    
    // Verificar reservas existentes para servicio 8
    $sql = "
        SELECT 
            sr.*,
            rsd.servicio_id,
            ad.dia_semana,
            ad.hora_inicio as agenda_hora_inicio,
            ad.hora_fin as agenda_hora_fin
        FROM servicios_reservas sr
        INNER JOIN agendas_detalle ad ON sr.agenda_detalle_id = ad.detalle_id
        INNER JOIN rs_servicios_doctors rsd ON rsd.agenda_detalle_id = ad.detalle_id 
                                            AND sr.doctor_id = rsd.doctor_id
        WHERE rsd.servicio_id = 8
        AND rsd.is_active = true
        ORDER BY sr.fecha_reserva, sr.hora_inicio
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $reservas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>Reservas encontradas para servicio 8: " . count($reservas) . "</h3>";
    
    if (count($reservas) > 0) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr>";
        echo "<th>ID</th><th>Fecha</th><th>Hora Inicio</th><th>Hora Fin</th>";
        echo "<th>Doctor ID</th><th>Estado</th><th>Agenda Hora</th>";
        echo "</tr>";
        
        foreach ($reservas as $reserva) {
            echo "<tr>";
            echo "<td>" . $reserva['reserva_id'] . "</td>";
            echo "<td>" . $reserva['fecha_reserva'] . "</td>";
            echo "<td>" . $reserva['hora_inicio'] . "</td>";
            echo "<td>" . $reserva['hora_fin'] . "</td>";
            echo "<td>" . $reserva['doctor_id'] . "</td>";
            echo "<td>" . $reserva['reserva_estado'] . "</td>";
            echo "<td>" . $reserva['agenda_hora_inicio'] . " - " . $reserva['agenda_hora_fin'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No se encontraron reservas para el servicio 8.</p>";
    }
    
    // Verificar configuración de horarios para servicio 8
    echo "<h3>Horarios configurados para servicio 8</h3>";
    $sqlHorarios = "
        SELECT DISTINCT
            ad.detalle_id,
            ad.dia_semana,
            ad.hora_inicio,
            ad.hora_fin,
            ad.intervalo_minutos,
            ad.cupo_maximo,
            ac.medico_id,
            rh.doctor_id,
            p.first_name || ' ' || p.last_name as doctor_nombre,
            CASE 
                WHEN EXTRACT(HOUR FROM ad.hora_inicio::time) BETWEEN 6 AND 11 THEN 'Mañana'
                WHEN EXTRACT(HOUR FROM ad.hora_inicio::time) BETWEEN 12 AND 17 THEN 'Tarde'
                ELSE 'Noche'
            END as turno
        FROM agendas_detalle ad
        INNER JOIN agendas_cabecera ac ON ad.agenda_id = ac.agenda_id
        INNER JOIN rs_servicios_doctors rsd ON rsd.agenda_detalle_id = ad.detalle_id 
                                            AND ac.medico_id = rsd.doctor_id
        INNER JOIN rh_doctors rh ON rsd.doctor_id = rh.doctor_id
        INNER JOIN rh_person p ON rh.person_id = p.person_id
        WHERE ac.agenda_estado = true 
        AND ad.detalle_estado = true
        AND rsd.servicio_id = 8
        AND rsd.is_active = true
        AND rh.doctor_estado = 'ACTIVO'
        AND p.is_active = true
        ORDER BY ad.hora_inicio, ac.medico_id
    ";
    
    $stmtHorarios = $pdo->prepare($sqlHorarios);
    $stmtHorarios->execute();
    $horarios = $stmtHorarios->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<p>Horarios encontrados: " . count($horarios) . "</p>";
    
    if (count($horarios) > 0) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr>";
        echo "<th>Detalle ID</th><th>Día</th><th>Hora Inicio</th><th>Hora Fin</th>";
        echo "<th>Intervalo</th><th>Cupo Máximo</th><th>Doctor</th><th>Turno</th>";
        echo "</tr>";
        
        foreach ($horarios as $horario) {
            echo "<tr>";
            echo "<td>" . $horario['detalle_id'] . "</td>";
            echo "<td>" . $horario['dia_semana'] . "</td>";
            echo "<td>" . $horario['hora_inicio'] . "</td>";
            echo "<td>" . $horario['hora_fin'] . "</td>";
            echo "<td>" . $horario['intervalo_minutos'] . "</td>";
            echo "<td>" . $horario['cupo_maximo'] . "</td>";
            echo "<td>" . $horario['doctor_nombre'] . "</td>";
            echo "<td>" . $horario['turno'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Calcular cupos teóricos
        echo "<h3>Cálculo teórico de cupos por día</h3>";
        foreach ($horarios as $horario) {
            $horaInicio = new DateTime($horario['hora_inicio']);
            $horaFin = new DateTime($horario['hora_fin']);
            $intervaloMinutos = (int)$horario['intervalo_minutos'];
            $cupoMaximo = (int)$horario['cupo_maximo'];
            
            $slots = 0;
            $detalleSlots = [];
            if ($intervaloMinutos > 0) {
                $horaActual = clone $horaInicio;
                while ($horaActual < $horaFin) {
                    // Verificar que el slot completo quepa dentro del horario
                    $horaFinSlot = clone $horaActual;
                    $horaFinSlot->add(new DateInterval('PT' . $intervaloMinutos . 'M'));
                    
                    if ($horaFinSlot <= $horaFin) {
                        $slots++;
                        $detalleSlots[] = $horaActual->format('H:i') . " - " . $horaFinSlot->format('H:i');
                    } else {
                        // El slot no cabe completo, anotar y salir
                        $detalleSlots[] = "<span style='color: red;'>" . $horaActual->format('H:i') . " - " . $horaFinSlot->format('H:i') . " (EXCEDE, no se cuenta)</span>";
                        break;
                    }
                    
                    $horaActual->add(new DateInterval('PT' . $intervaloMinutos . 'M'));
                }
            }
            
            $cuposTotales = $slots * $cupoMaximo;
            
            echo "<div style='border: 1px solid #ccc; padding: 10px; margin: 10px 0;'>";
            echo "<p><strong>" . $horario['dia_semana'] . " - " . $horario['turno'] . ":</strong></p>";
            echo "<p>Horario: " . $horario['hora_inicio'] . " - " . $horario['hora_fin'] . " (intervalo: " . $intervaloMinutos . " min)</p>";
            echo "<p>Slots calculados:</p>";
            echo "<ul>";
            foreach ($detalleSlots as $slot) {
                echo "<li>" . $slot . "</li>";
            }
            echo "</ul>";
            echo "<p><strong>Total: " . $slots . " slots × " . $cupoMaximo . " cupo máximo = " . $cuposTotales . " cupos totales</strong></p>";
            echo "</div>";
        }
    }
    
} catch (Exception $e) {
    echo "<p>Error: " . $e->getMessage() . "</p>";
}
?>