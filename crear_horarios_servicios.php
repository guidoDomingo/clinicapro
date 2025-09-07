<?php
require_once 'model/conexion.php';
$conexion = Conexion::conectar();

echo "=== CREANDO HORARIOS PARA LOS SERVICIOS ===\n";

try {
    // Verificar qué horarios existen para el doctor 18 (agenda_id = 20)
    echo "\n1. Horarios existentes en agenda_id = 20:\n";
    $stmt = $conexion->prepare("
        SELECT detalle_id, agenda_id, servicio_id, dia_semana, hora_inicio, hora_fin
        FROM agendas_detalle 
        WHERE agenda_id = 20
        ORDER BY detalle_id
    ");
    $stmt->execute();
    $horariosExistentes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($horariosExistentes as $horario) {
        print_r($horario);
    }
    
    echo "\n2. Verificar relación rs_servicios_doctors existente:\n";
    $stmt = $conexion->prepare("
        SELECT id, servicio_id, doctor_id, agenda_detalle_id, is_active
        FROM rs_servicios_doctors 
        WHERE doctor_id = 18
        ORDER BY id
    ");
    $stmt->execute();
    $relaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($relaciones as $relacion) {
        print_r($relacion);
    }
    
    // Ahora vamos a crear horarios específicos para cada servicio
    echo "\n3. Creando horarios específicos:\n";
    
    // Para Cirugía de prueba (servicio_id = 4) - Solo tarde
    echo "Creando horario para Cirugía de prueba (solo tarde)...\n";
    $stmt = $conexion->prepare("
        INSERT INTO agendas_detalle (
            agenda_id, servicio_id, dia_semana, hora_inicio, hora_fin, 
            intervalo_minutos, cupo_maximo, detalle_estado
        ) VALUES (
            20, 4, 'SABADO', '13:00:00', '17:00:00', 
            30, 8, true
        ) RETURNING detalle_id
    ");
    $stmt->execute();
    $detalle_id_cirugia = $stmt->fetchColumn();
    echo "Horario creado con detalle_id: $detalle_id_cirugia\n";
    
    // Para prueba 789545612 (servicio_id = 8) - Solo mañana
    echo "Creando horario para prueba 789545612 (solo mañana)...\n";
    $stmt = $conexion->prepare("
        INSERT INTO agendas_detalle (
            agenda_id, servicio_id, dia_semana, hora_inicio, hora_fin, 
            intervalo_minutos, cupo_maximo, detalle_estado
        ) VALUES (
            20, 8, 'SABADO', '08:00:00', '12:00:00', 
            30, 8, true
        ) RETURNING detalle_id
    ");
    $stmt->execute();
    $detalle_id_prueba = $stmt->fetchColumn();
    echo "Horario creado con detalle_id: $detalle_id_prueba\n";
    
    // Actualizar rs_servicios_doctors con los nuevos detalle_id
    echo "\n4. Actualizando rs_servicios_doctors:\n";
    
    // Actualizar Cirugía de prueba
    $stmt = $conexion->prepare("
        UPDATE rs_servicios_doctors 
        SET agenda_detalle_id = :detalle_id, updated_at = NOW()
        WHERE servicio_id = 4 AND doctor_id = 18
    ");
    $stmt->bindParam(':detalle_id', $detalle_id_cirugia);
    $stmt->execute();
    echo "Actualizada relación para Cirugía de prueba\n";
    
    // Actualizar prueba 789545612
    $stmt = $conexion->prepare("
        UPDATE rs_servicios_doctors 
        SET agenda_detalle_id = :detalle_id, updated_at = NOW()
        WHERE servicio_id = 8 AND doctor_id = 18
    ");
    $stmt->bindParam(':detalle_id', $detalle_id_prueba);
    $stmt->execute();
    echo "Actualizada relación para prueba 789545612\n";
    
    echo "\n5. Verificación final:\n";
    $stmt = $conexion->prepare("
        SELECT 
            rsd.servicio_id,
            rs.serv_descripcion,
            ad.dia_semana,
            ad.hora_inicio,
            ad.hora_fin,
            rsd.agenda_detalle_id
        FROM rs_servicios_doctors rsd
        INNER JOIN rs_servicios rs ON rsd.servicio_id = rs.serv_id
        INNER JOIN agendas_detalle ad ON rsd.agenda_detalle_id = ad.detalle_id
        WHERE rsd.doctor_id = 18 AND rsd.is_active = true
        ORDER BY rsd.servicio_id
    ");
    $stmt->execute();
    $verificacion = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($verificacion as $item) {
        print_r($item);
    }
    
    echo "\n¡HORARIOS CREADOS EXITOSAMENTE!\n";
    echo "- Cirugía de prueba: SABADO 13:00-17:00 (solo tarde)\n";
    echo "- prueba 789545612: SABADO 08:00-12:00 (solo mañana)\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>