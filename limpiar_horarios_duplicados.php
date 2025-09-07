<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "model/conexion.php";

echo "<h3>🔧 Limpieza Final: Horarios Duplicados</h3>";

try {
    $pdo = Conexion::conectar();
    
    // 1. Encontrar el doctor Angel Isnardi
    $stmt = $pdo->prepare("
        SELECT rd.doctor_id, CONCAT(rp.first_name, ' ', rp.last_name) as nombre
        FROM rh_person rp
        INNER JOIN rh_doctors rd ON rp.person_id = rd.person_id
        WHERE rp.first_name LIKE '%angel%'
    ");
    $stmt->execute();
    $doctor = $stmt->fetch(PDO::FETCH_ASSOC);
    $doctor_id = $doctor['doctor_id'];
    
    echo "Doctor: {$doctor['nombre']} (ID: {$doctor_id})<br>";
    
    // 2. Encontrar horarios duplicados
    echo "<h4>Horarios actuales del doctor:</h4>";
    $stmt = $pdo->prepare("
        SELECT 
            ad.detalle_id,
            ad.agenda_id,
            ad.dia_semana,
            ad.hora_inicio,
            ad.hora_fin,
            ad.intervalo_minutos,
            t.turno_nombre,
            s.sala_nombre
        FROM agendas_detalle ad
        INNER JOIN agendas_cabecera ac ON ad.agenda_id = ac.agenda_id
        INNER JOIN turnos t ON ad.turno_id = t.turno_id
        INNER JOIN salas s ON ad.sala_id = s.sala_id
        WHERE ac.medico_id = ?
        AND ad.detalle_estado = true
        AND ac.agenda_estado = true
        ORDER BY ad.dia_semana, ad.hora_inicio
    ");
    $stmt->execute([$doctor_id]);
    $horarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($horarios as $horario) {
        echo "- Detalle ID: {$horario['detalle_id']}, {$horario['dia_semana']}, {$horario['hora_inicio']}-{$horario['hora_fin']}, Turno: {$horario['turno_nombre']}<br>";
    }
    
    // 3. Identificar duplicados por hora
    $grupos = [];
    foreach ($horarios as $horario) {
        $clave = $horario['dia_semana'] . '_' . $horario['hora_inicio'] . '_' . $horario['hora_fin'];
        if (!isset($grupos[$clave])) {
            $grupos[$clave] = [];
        }
        $grupos[$clave][] = $horario;
    }
    
    echo "<h4>Análisis de duplicados:</h4>";
    $duplicados = [];
    foreach ($grupos as $clave => $grupo) {
        if (count($grupo) > 1) {
            echo "⚠️ Horario duplicado: $clave (" . count($grupo) . " registros)<br>";
            foreach ($grupo as $h) {
                echo "&nbsp;&nbsp;- Detalle ID: {$h['detalle_id']}<br>";
            }
            $duplicados[$clave] = $grupo;
        } else {
            echo "✅ Horario único: $clave (Detalle ID: {$grupo[0]['detalle_id']})<br>";
        }
    }
    
    // 4. Limpiar duplicados manteniendo solo uno por rango horario
    if (!empty($duplicados)) {
        echo "<h4>🔧 Limpiando duplicados:</h4>";
        
        foreach ($duplicados as $clave => $grupo) {
            echo "Procesando grupo: $clave<br>";
            
            // Mantener el primero, eliminar el resto
            $mantener = array_shift($grupo);
            echo "- Manteniendo Detalle ID: {$mantener['detalle_id']}<br>";
            
            foreach ($grupo as $eliminar) {
                echo "- Eliminando Detalle ID: {$eliminar['detalle_id']}<br>";
                
                // Primero eliminar las relaciones de servicios
                $stmt = $pdo->prepare("DELETE FROM rs_servicios_doctors WHERE agenda_detalle_id = ?");
                $stmt->execute([$eliminar['detalle_id']]);
                
                // Luego eliminar el detalle de agenda
                $stmt = $pdo->prepare("DELETE FROM agendas_detalle WHERE detalle_id = ?");
                $stmt->execute([$eliminar['detalle_id']]);
            }
        }
        
        echo "<h4>✅ Limpieza completada</h4>";
    }
    
    // 5. Reorganizar servicios para los horarios únicos
    echo "<h4>🔧 Reorganizando servicios:</h4>";
    
    // Obtener horarios únicos restantes
    $stmt = $pdo->prepare("
        SELECT 
            ad.detalle_id,
            ad.dia_semana,
            ad.hora_inicio,
            ad.hora_fin,
            t.turno_nombre
        FROM agendas_detalle ad
        INNER JOIN agendas_cabecera ac ON ad.agenda_id = ac.agenda_id
        INNER JOIN turnos t ON ad.turno_id = t.turno_id
        WHERE ac.medico_id = ?
        AND ad.detalle_estado = true
        AND ac.agenda_estado = true
        ORDER BY ad.dia_semana, ad.hora_inicio
    ");
    $stmt->execute([$doctor_id]);
    $horariosLimpios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Horarios únicos restantes: " . count($horariosLimpios) . "<br>";
    
    // Limpiar todas las relaciones existentes
    $stmt = $pdo->prepare("DELETE FROM rs_servicios_doctors WHERE doctor_id = ?");
    $stmt->execute([$doctor_id]);
    
    // Asignar servicios específicos a cada horario
    foreach ($horariosLimpios as $horario) {
        $detalle_id = $horario['detalle_id'];
        $horario_str = $horario['hora_inicio'] . '-' . $horario['hora_fin'];
        
        if ($horario['hora_inicio'] < '13:00:00') {
            // Horario de mañana -> Servicio 8 "prueba 789545612"
            $servicio_id = 8;
            $servicio_nombre = "prueba 789545612";
        } else {
            // Horario de tarde -> Servicio 4 "Cirugía de prueba"  
            $servicio_id = 4;
            $servicio_nombre = "Cirugía de prueba";
        }
        
        $stmt = $pdo->prepare("
            INSERT INTO rs_servicios_doctors (servicio_id, doctor_id, agenda_detalle_id, is_active)
            VALUES (?, ?, ?, true)
        ");
        $stmt->execute([$servicio_id, $doctor_id, $detalle_id]);
        
        echo "- Detalle ID {$detalle_id} ({$horario_str}): {$servicio_nombre}<br>";
    }
    
    // 6. Verificación final
    echo "<h4>✅ Verificación final:</h4>";
    
    // Test Cirugía de prueba
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count
        FROM agendas_detalle ad 
        INNER JOIN agendas_cabecera ac ON ad.agenda_id = ac.agenda_id 
        INNER JOIN rs_servicios_doctors rsd ON rsd.doctor_id = ac.medico_id 
                                           AND rsd.agenda_detalle_id = ad.detalle_id
                                           AND rsd.servicio_id = 4
        WHERE 
            ac.medico_id = ?
            AND ad.dia_semana = 'LUNES'
            AND ad.detalle_estado = true
            AND ac.agenda_estado = true
            AND rsd.is_active = true
    ");
    $stmt->execute([$doctor_id]);
    $count_cirugia = $stmt->fetchColumn();
    
    echo "Horarios para 'Cirugía de prueba' el lunes: {$count_cirugia}<br>";
    
    if ($count_cirugia == 1) {
        echo "🎉 ¡PERFECTO! Cirugía de prueba ahora muestra solo 1 horario<br>";
    } else {
        echo "❌ Aún hay problemas: se esperaba 1 horario<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}
?>