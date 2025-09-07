<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "controller/servicios.controller.php";
require_once "model/servicios.model.php";
require_once "model/conexion.php";

echo "<h3>🐛 Debug AJAX: cargarHorariosDisponibles</h3>";

// Simular exactamente la misma llamada que hace el frontend
$servicioId = 4; // Cirugía de prueba
$doctorId = 18;  // Angel Isnardi
$fecha = "2025-09-08"; // Lunes

echo "<h4>Parámetros de entrada:</h4>";
echo "ServicioID: {$servicioId}<br>";
echo "DoctorID: {$doctorId}<br>";
echo "Fecha: {$fecha}<br>";

try {
    // Llamar exactamente al mismo método que llama el AJAX
    echo "<h4>Llamando a ControladorServicios::ctrGenerarSlotsDisponibles()...</h4>";
    $slots = ControladorServicios::ctrGenerarSlotsDisponibles($servicioId, $doctorId, $fecha);
    
    echo "Slots obtenidos: " . count($slots) . "<br>";
    
    if (!empty($slots)) {
        echo "<h5>Slots encontrados:</h5>";
        foreach ($slots as $slot) {
            echo "- {$slot['hora_inicio']}-{$slot['hora_fin']}: Detalle ID {$slot['detalle_id']}<br>";
        }
        
        // Verificar si todos los slots son del mismo horario
        $horariosUnicos = array_unique(array_column($slots, 'detalle_id'));
        echo "<br>Horarios únicos (detalle_id): " . implode(', ', $horariosUnicos) . "<br>";
        
        if (count($horariosUnicos) > 1) {
            echo "❌ PROBLEMA: Se están generando slots de múltiples horarios<br>";
        } else {
            echo "✅ CORRECTO: Todos los slots son del mismo horario<br>";
        }
    } else {
        echo "⚠️ No se encontraron slots<br>";
    }
    
    // Verificar directamente en la base de datos qué está pasando
    echo "<h4>Verificación directa en base de datos:</h4>";
    $pdo = Conexion::conectar();
    
    // La misma consulta que usa mdlGenerarSlotsDisponibles
    $stmt = $pdo->prepare("
        SELECT 
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
        INNER JOIN rs_servicios_doctors rsd ON rsd.doctor_id = ac.medico_id 
                                           AND rsd.agenda_detalle_id = ad.detalle_id
                                           AND rsd.servicio_id = :servicio_id
        WHERE 
            ac.medico_id = :doctor_id
            AND ad.dia_semana = 'LUNES'
            AND ad.detalle_estado = true
            AND ac.agenda_estado = true
            AND rsd.is_active = true
        ORDER BY ad.hora_inicio ASC
    ");
    
    $stmt->bindParam(":doctor_id", $doctorId, PDO::PARAM_INT);
    $stmt->bindParam(":servicio_id", $servicioId, PDO::PARAM_INT);
    $stmt->execute();
    $horarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Horarios encontrados en consulta directa: " . count($horarios) . "<br>";
    foreach ($horarios as $horario) {
        echo "- Detalle ID: {$horario['detalle_id']}, {$horario['hora_inicio']}-{$horario['hora_fin']}, Turno: {$horario['turno_nombre']}<br>";
    }
    
    // Si la consulta directa funciona pero los slots no, hay problema en la generación de slots
    if (count($horarios) == 1 && count($slots) > 1) {
        echo "<br>❌ PROBLEMA IDENTIFICADO: La consulta SQL filtra correctamente, pero la generación de slots está mal<br>";
        
        // Revisar el primer horario encontrado
        $horario = $horarios[0];
        echo "<h5>Analizando generación de slots para el horario encontrado:</h5>";
        echo "Hora inicio: {$horario['hora_inicio']}<br>";
        echo "Hora fin: {$horario['hora_fin']}<br>";
        echo "Intervalo: {$horario['intervalo_minutos']} minutos<br>";
        
        // Calcular cuántos slots debería generar
        $inicio = new DateTime($horario['hora_inicio']);
        $fin = new DateTime($horario['hora_fin']);
        $intervalo = new DateInterval('PT' . $horario['intervalo_minutos'] . 'M');
        
        $slotsEsperados = 0;
        $horaActual = clone $inicio;
        while ($horaActual < $fin) {
            $slotsEsperados++;
            $horaActual->add($intervalo);
        }
        
        echo "Slots esperados para este horario: {$slotsEsperados}<br>";
        echo "Slots generados: " . count($slots) . "<br>";
        
        if (count($slots) != $slotsEsperados) {
            echo "❌ Los slots generados no coinciden con los esperados para UN SOLO horario<br>";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
    echo "Trace: " . $e->getTraceAsString() . "<br>";
}
?>