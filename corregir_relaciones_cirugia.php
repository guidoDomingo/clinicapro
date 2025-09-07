<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "model/conexion.php";

echo "<h3>🔧 Corregir Relaciones Servicio-Doctor</h3>";

try {
    $pdo = Conexion::conectar();
    
    // Obtener doctor Angel Isnardi
    $stmt = $pdo->prepare("
        SELECT rd.doctor_id, CONCAT(rp.first_name, ' ', rp.last_name) as nombre
        FROM rh_person rp
        INNER JOIN rh_doctors rd ON rp.person_id = rd.person_id
        WHERE rp.first_name LIKE '%angel%'
    ");
    $stmt->execute();
    $doctor = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$doctor) {
        echo "❌ No se encontró el doctor Angel Isnardi<br>";
        exit;
    }
    
    $doctor_id = $doctor['doctor_id'];
    echo "✅ Doctor encontrado: {$doctor['nombre']} (ID: {$doctor_id})<br>";
    
    // Obtener horarios del doctor
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
    $horarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h4>Horarios del doctor:</h4>";
    foreach ($horarios as $horario) {
        echo "- Detalle ID: {$horario['detalle_id']}, {$horario['dia_semana']}, {$horario['hora_inicio']}-{$horario['hora_fin']}, Turno: {$horario['turno_nombre']}<br>";
    }
    
    // Verificar relaciones actuales
    echo "<h4>Relaciones actuales en rs_servicios_doctors:</h4>";
    $stmt = $pdo->prepare("
        SELECT 
            rsd.id,
            rsd.agenda_detalle_id,
            rsd.servicio_id,
            s.serv_descripcion,
            ad.dia_semana,
            ad.hora_inicio,
            ad.hora_fin
        FROM rs_servicios_doctors rsd
        INNER JOIN rs_servicios s ON rsd.servicio_id = s.serv_id
        INNER JOIN agendas_detalle ad ON rsd.agenda_detalle_id = ad.detalle_id
        WHERE rsd.doctor_id = ?
        ORDER BY ad.dia_semana, ad.hora_inicio
    ");
    $stmt->execute([$doctor_id]);
    $relaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($relaciones as $rel) {
        echo "- Detalle ID: {$rel['agenda_detalle_id']}, Servicio: {$rel['serv_descripcion']}, {$rel['dia_semana']}, {$rel['hora_inicio']}-{$rel['hora_fin']}<br>";
    }
    
    // Identificar el problema específico
    echo "<h4>Análisis del problema:</h4>";
    
    // Buscar horarios de mañana y tarde para el lunes
    $horariosLunes = array_filter($horarios, function($h) {
        return $h['dia_semana'] === 'LUNES';
    });
    
    echo "Horarios del lunes encontrados: " . count($horariosLunes) . "<br>";
    
    $mañana = null;
    $tarde = null;
    
    foreach ($horariosLunes as $h) {
        if (strpos($h['turno_nombre'], 'MAÑANA') !== false || $h['hora_inicio'] < '13:00:00') {
            $mañana = $h;
        }
        if (strpos($h['turno_nombre'], 'TARDE') !== false || $h['hora_inicio'] >= '13:00:00') {
            $tarde = $h;
        }
    }
    
    if ($mañana) {
        echo "- Horario de MAÑANA: Detalle ID {$mañana['detalle_id']}, {$mañana['hora_inicio']}-{$mañana['hora_fin']}<br>";
    }
    if ($tarde) {
        echo "- Horario de TARDE: Detalle ID {$tarde['detalle_id']}, {$tarde['hora_inicio']}-{$tarde['hora_fin']}<br>";
    }
    
    // SOLUCIÓN: Asignar servicio específico a cada horario
    echo "<h4>🔧 Aplicando corrección:</h4>";
    
    // Servicio "Cirugía de prueba" (ID: 4) solo para el horario de TARDE
    if ($tarde) {
        // Eliminar cualquier relación existente para este horario
        $stmt = $pdo->prepare("DELETE FROM rs_servicios_doctors WHERE agenda_detalle_id = ?");
        $stmt->execute([$tarde['detalle_id']]);
        
        // Crear relación específica para Cirugía de prueba
        $stmt = $pdo->prepare("
            INSERT INTO rs_servicios_doctors (servicio_id, doctor_id, agenda_detalle_id, is_active)
            VALUES (4, ?, ?, true)
        ");
        $stmt->execute([$doctor_id, $tarde['detalle_id']]);
        echo "✅ Asignado 'Cirugía de prueba' al horario de TARDE (Detalle ID: {$tarde['detalle_id']})<br>";
    }
    
    // Asignar otro servicio al horario de MAÑANA
    if ($mañana) {
        // Eliminar cualquier relación existente para este horario
        $stmt = $pdo->prepare("DELETE FROM rs_servicios_doctors WHERE agenda_detalle_id = ?");
        $stmt->execute([$mañana['detalle_id']]);
        
        // Crear relación para otro servicio (por ejemplo, servicio ID 8 "prueba 789545612")
        $stmt = $pdo->prepare("
            INSERT INTO rs_servicios_doctors (servicio_id, doctor_id, agenda_detalle_id, is_active)
            VALUES (8, ?, ?, true)
        ");
        $stmt->execute([$doctor_id, $mañana['detalle_id']]);
        echo "✅ Asignado 'prueba 789545612' al horario de MAÑANA (Detalle ID: {$mañana['detalle_id']})<br>";
    }
    
    // Verificar resultado final
    echo "<h4>✅ Verificación final:</h4>";
    
    // Test para Cirugía de prueba
    $stmt = $pdo->prepare("
        SELECT 
            ad.detalle_id,
            ad.hora_inicio,
            ad.hora_fin,
            s.serv_descripcion
        FROM agendas_detalle ad 
        INNER JOIN agendas_cabecera ac ON ad.agenda_id = ac.agenda_id 
        INNER JOIN rs_servicios_doctors rsd ON rsd.doctor_id = ac.medico_id 
                                           AND rsd.agenda_detalle_id = ad.detalle_id
                                           AND rsd.servicio_id = 4
        INNER JOIN rs_servicios s ON rsd.servicio_id = s.serv_id
        WHERE 
            ac.medico_id = ?
            AND ad.dia_semana = 'LUNES'
            AND ad.detalle_estado = true
            AND ac.agenda_estado = true
            AND rsd.is_active = true
        ORDER BY ad.hora_inicio ASC
    ");
    $stmt->execute([$doctor_id]);
    $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Horarios para 'Cirugía de prueba': " . count($resultado) . "<br>";
    foreach ($resultado as $res) {
        echo "- {$res['hora_inicio']}-{$res['hora_fin']}: {$res['serv_descripcion']}<br>";
    }
    
    if (count($resultado) == 1 && $resultado[0]['hora_inicio'] >= '13:00:00') {
        echo "🎉 ¡PERFECTO! Ahora 'Cirugía de prueba' solo muestra el horario de la tarde<br>";
    } else {
        echo "⚠️ Aún hay problemas con el filtrado<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}
?>