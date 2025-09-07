<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "model/conexion.php";

echo "<h3>🔍 Debug: Cirugía de prueba - Horarios incorrectos</h3>";

try {
    $pdo = Conexion::conectar();
    
    // 1. Buscar el servicio "Cirugía de prueba"
    echo "<h4>1. Servicio 'Cirugía de prueba':</h4>";
    $stmt = $pdo->prepare("SELECT * FROM rs_servicios WHERE serv_descripcion LIKE '%cirugía%' OR serv_descripcion LIKE '%prueba%'");
    $stmt->execute();
    $servicios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($servicios)) {
        echo "❌ No se encontró el servicio 'Cirugía de prueba'<br>";
        
        // Mostrar todos los servicios disponibles
        $stmt = $pdo->prepare("SELECT * FROM rs_servicios ORDER BY serv_descripcion");
        $stmt->execute();
        $todosServicios = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h5>Servicios disponibles:</h5>";
        foreach ($todosServicios as $serv) {
            echo "- ID: {$serv['serv_id']}, Descripción: {$serv['serv_descripcion']}<br>";
        }
    } else {
        foreach ($servicios as $serv) {
            echo "✅ Encontrado - ID: {$serv['serv_id']}, Descripción: {$serv['serv_descripcion']}<br>";
        }
    }
    
    // 2. Verificar doctor Angel Isnardi
    echo "<h4>2. Doctor Angel Isnardi:</h4>";
    $stmt = $pdo->prepare("
        SELECT 
            rp.person_id,
            CONCAT(rp.first_name, ' ', rp.last_name) as nombre_completo,
            rd.doctor_id
        FROM rh_person rp
        INNER JOIN rh_doctors rd ON rp.person_id = rd.person_id
        WHERE rp.first_name LIKE '%angel%'
    ");
    $stmt->execute();
    $doctores = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($doctores)) {
        echo "❌ No se encontró el doctor Angel Isnardi<br>";
    } else {
        foreach ($doctores as $doc) {
            echo "✅ Doctor - Person ID: {$doc['person_id']}, Doctor ID: {$doc['doctor_id']}, Nombre: {$doc['nombre_completo']}<br>";
            $doctor_id = $doc['doctor_id'];
        }
    }
    
    // 3. Verificar horarios del doctor
    echo "<h4>3. Horarios del doctor Angel Isnardi:</h4>";
    if (isset($doctor_id)) {
        $stmt = $pdo->prepare("
            SELECT 
                ad.detalle_id,
                ad.agenda_id,
                ad.dia_semana,
                ad.hora_inicio,
                ad.hora_fin,
                ad.intervalo_minutos,
                ad.cupo_maximo,
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
        
        echo "Total horarios encontrados: " . count($horarios) . "<br>";
        foreach ($horarios as $horario) {
            echo "- Detalle ID: {$horario['detalle_id']}, {$horario['dia_semana']}, {$horario['hora_inicio']}-{$horario['hora_fin']}, Turno: {$horario['turno_nombre']}, Sala: {$horario['sala_nombre']}<br>";
        }
    }
    
    // 4. Verificar relaciones en rs_servicios_doctors
    echo "<h4>4. Relaciones servicio-doctor para Angel Isnardi:</h4>";
    if (isset($doctor_id)) {
        $stmt = $pdo->prepare("
            SELECT 
                rsd.id,
                rsd.servicio_id,
                rsd.doctor_id,
                rsd.agenda_detalle_id,
                rsd.is_active,
                s.serv_descripcion,
                ad.dia_semana,
                ad.hora_inicio,
                ad.hora_fin
            FROM rs_servicios_doctors rsd
            INNER JOIN rs_servicios s ON rsd.servicio_id = s.serv_id
            INNER JOIN agendas_detalle ad ON rsd.agenda_detalle_id = ad.detalle_id
            WHERE rsd.doctor_id = ?
            AND rsd.is_active = true
            ORDER BY s.serv_descripcion, ad.dia_semana, ad.hora_inicio
        ");
        $stmt->execute([$doctor_id]);
        $relaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "Total relaciones encontradas: " . count($relaciones) . "<br>";
        foreach ($relaciones as $rel) {
            echo "- Servicio: {$rel['serv_descripcion']} (ID: {$rel['servicio_id']}), Detalle ID: {$rel['agenda_detalle_id']}, {$rel['dia_semana']}, {$rel['hora_inicio']}-{$rel['hora_fin']}<br>";
        }
    }
    
    // 5. Simulación específica de "Cirugía de prueba"
    echo "<h4>5. Simulación para 'Cirugía de prueba' el lunes:</h4>";
    
    // Buscar ID específico de cirugía de prueba
    $stmt = $pdo->prepare("SELECT serv_id FROM rs_servicios WHERE serv_descripcion = 'Cirugía de prueba'");
    $stmt->execute();
    $cirugia_id = $stmt->fetchColumn();
    
    if ($cirugia_id && isset($doctor_id)) {
        echo "Servicio ID: {$cirugia_id}, Doctor ID: {$doctor_id}<br>";
        
        // Ejecutar la misma consulta que está en mdlGenerarSlotsDisponibles
        $stmt = $pdo->prepare("
            SELECT 
                ad.detalle_id,
                ad.dia_semana,
                ad.hora_inicio,
                ad.hora_fin,
                ad.intervalo_minutos,
                ad.cupo_maximo,
                t.turno_nombre,
                s.sala_nombre
            FROM agendas_detalle ad 
            INNER JOIN agendas_cabecera ac ON ad.agenda_id = ac.agenda_id 
            INNER JOIN turnos t ON ad.turno_id = t.turno_id
            INNER JOIN salas s ON ad.sala_id = s.sala_id
            INNER JOIN rs_servicios_doctors rsd ON rsd.doctor_id = ac.medico_id 
                                               AND rsd.agenda_detalle_id = ad.detalle_id
                                               AND rsd.servicio_id = ?
            WHERE 
                ac.medico_id = ?
                AND ad.dia_semana = 'LUNES'
                AND ad.detalle_estado = true
                AND ac.agenda_estado = true
                AND rsd.is_active = true
            ORDER BY ad.hora_inicio ASC
        ");
        $stmt->execute([$cirugia_id, $doctor_id]);
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "Horarios filtrados para 'Cirugía de prueba' el lunes: " . count($resultados) . "<br>";
        foreach ($resultados as $res) {
            echo "- Detalle ID: {$res['detalle_id']}, {$res['hora_inicio']}-{$res['hora_fin']}, Turno: {$res['turno_nombre']}<br>";
        }
        
        if (count($resultados) > 1) {
            echo "❌ PROBLEMA: Se encontraron múltiples horarios cuando debería ser solo el de la tarde<br>";
        } elseif (count($resultados) == 1) {
            echo "✅ CORRECTO: Solo se encontró un horario<br>";
        } else {
            echo "⚠️ No se encontraron horarios para este servicio<br>";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}
?>