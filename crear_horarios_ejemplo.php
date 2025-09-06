<?php
/**
 * Script para crear horarios de ejemplo para testing
 */

require_once "model/conexion.php";

try {
    $pdo = Conexion::conectar();
    $pdo->beginTransaction();
    
    echo "<h2>Creando Horarios de Ejemplo</h2>";
    
    // Obtener el médico angel isnardi (ID: 18)
    $medicoId = 18;
    $medicoNombre = "angel isnardi";
    
    echo "<p>Configurando horarios para: <strong>$medicoNombre (ID: $medicoId)</strong></p>";
    
    // 1. Crear agenda cabecera si no existe
    $stmt = $pdo->prepare("
        INSERT INTO agendas_cabecera (medico_id, agenda_descripcion, agenda_estado, fecha_creacion)
        VALUES (:medico_id, :descripcion, true, NOW())
        ON CONFLICT (medico_id) DO UPDATE SET
        agenda_descripcion = EXCLUDED.agenda_descripcion
        RETURNING agenda_id
    ");
    
    $stmt->execute([
        ':medico_id' => $medicoId,
        ':descripcion' => "Agenda de $medicoNombre"
    ]);
    
    $agendaId = $stmt->fetchColumn();
    echo "<p>✅ Agenda creada/actualizada: ID $agendaId</p>";
    
    // 2. Eliminar horarios existentes para este médico
    $stmt = $pdo->prepare("DELETE FROM agendas_detalle WHERE agenda_id = :agenda_id");
    $stmt->execute([':agenda_id' => $agendaId]);
    echo "<p>🗑️ Horarios anteriores eliminados</p>";
    
    // 3. Crear horarios de ejemplo
    $horariosEjemplo = [
        ['LUNES', 1, 1, '08:00:00', '12:00:00', 30, 5],
        ['LUNES', 2, 1, '14:00:00', '18:00:00', 30, 5],
        ['MARTES', 1, 2, '08:00:00', '12:00:00', 30, 4],
        ['MARTES', 2, 2, '14:00:00', '17:00:00', 30, 4],
        ['MIERCOLES', 1, 1, '08:00:00', '12:00:00', 30, 5],
        ['JUEVES', 1, 3, '09:00:00', '13:00:00', 30, 6],
        ['JUEVES', 2, 3, '15:00:00', '19:00:00', 30, 6],
        ['VIERNES', 1, 1, '08:00:00', '11:00:00', 30, 3]
    ];
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 20px 0;'>";
    echo "<tr style='background-color: #f0f0f0;'>";
    echo "<th style='padding: 8px;'>Día</th>";
    echo "<th style='padding: 8px;'>Turno</th>";
    echo "<th style='padding: 8px;'>Sala</th>";
    echo "<th style='padding: 8px;'>Inicio</th>";
    echo "<th style='padding: 8px;'>Fin</th>";
    echo "<th style='padding: 8px;'>Intervalo</th>";
    echo "<th style='padding: 8px;'>Cupos</th>";
    echo "</tr>";
    
    foreach ($horariosEjemplo as $horario) {
        list($dia, $turnoId, $salaId, $horaInicio, $horaFin, $intervalo, $cupos) = $horario;
        
        $stmt = $pdo->prepare("
            INSERT INTO agendas_detalle (
                agenda_id, turno_id, sala_id, dia_semana, 
                hora_inicio, hora_fin, intervalo_minutos, cupo_maximo, detalle_estado
            ) VALUES (
                :agenda_id, :turno_id, :sala_id, :dia_semana,
                :hora_inicio, :hora_fin, :intervalo_minutos, :cupo_maximo, true
            )
        ");
        
        $stmt->execute([
            ':agenda_id' => $agendaId,
            ':turno_id' => $turnoId,
            ':sala_id' => $salaId,
            ':dia_semana' => $dia,
            ':hora_inicio' => $horaInicio,
            ':hora_fin' => $horaFin,
            ':intervalo_minutos' => $intervalo,
            ':cupo_maximo' => $cupos
        ]);
        
        echo "<tr>";
        echo "<td style='padding: 8px;'>$dia</td>";
        echo "<td style='padding: 8px;'>Turno $turnoId</td>";
        echo "<td style='padding: 8px;'>Sala $salaId</td>";
        echo "<td style='padding: 8px;'>$horaInicio</td>";
        echo "<td style='padding: 8px;'>$horaFin</td>";
        echo "<td style='padding: 8px;'>{$intervalo} min</td>";
        echo "<td style='padding: 8px;'>$cupos</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    $pdo->commit();
    
    echo "<div style='background-color: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; margin: 20px 0; border-radius: 5px;'>";
    echo "<h3>🎉 ¡Horarios Creados Exitosamente!</h3>";
    echo "<p>Se han configurado <strong>" . count($horariosEjemplo) . " horarios</strong> para el médico <strong>$medicoNombre</strong>.</p>";
    echo "<p>Ahora puedes probar la funcionalidad:</p>";
    echo "<ol>";
    echo "<li>Ve al módulo de servicios</li>";
    echo "<li>Selecciona paciente → servicio → médico 'angel isnardi'</li>";
    echo "<li>Deberías ver los días disponibles y horarios</li>";
    echo "</ol>";
    echo "</div>";
    
    echo "<p><a href='view/modules/servicios.php' style='background-color: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Ir a Probar Reservas</a></p>";
    echo "<p><a href='verificar_horarios_medicos.php' style='background-color: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Verificar Horarios</a></p>";
    
} catch (Exception $e) {
    $pdo->rollback();
    echo "<div style='background-color: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; margin: 20px 0; border-radius: 5px;'>";
    echo "<h3>❌ Error al crear horarios</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "</div>";
}
?>