<?php
/**
 * Script para verificar qué médicos tienen horarios configurados
 */

require_once "model/conexion.php";

try {
    $stmt = Conexion::conectar()->prepare("
        SELECT DISTINCT
            ac.medico_id,
            p.first_name as medico_nombre,
            COUNT(ad.detalle_id) as total_horarios,
            GROUP_CONCAT(DISTINCT ad.dia_semana) as dias_disponibles
        FROM agendas_cabecera ac
        INNER JOIN agendas_detalle ad ON ac.agenda_id = ad.agenda_id
        LEFT JOIN rs_persons p ON ac.medico_id = p.person_id
        WHERE ad.detalle_estado = true 
        AND ac.agenda_estado = true
        GROUP BY ac.medico_id, p.first_name
        ORDER BY p.first_name
    ");
    $stmt->execute();
    $medicosConHorarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h2>Médicos con Horarios Configurados</h2>";
    
    if (empty($medicosConHorarios)) {
        echo "<p style='color: red;'>⚠️ <strong>NO HAY MÉDICOS CON HORARIOS CONFIGURADOS</strong></p>";
        echo "<p>Necesitas configurar horarios para poder probar la funcionalidad.</p>";
    } else {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background-color: #f0f0f0;'>";
        echo "<th style='padding: 10px;'>ID Médico</th>";
        echo "<th style='padding: 10px;'>Nombre</th>";
        echo "<th style='padding: 10px;'>Total Horarios</th>";
        echo "<th style='padding: 10px;'>Días Disponibles</th>";
        echo "</tr>";
        
        foreach ($medicosConHorarios as $medico) {
            echo "<tr>";
            echo "<td style='padding: 10px; text-align: center;'>" . $medico['medico_id'] . "</td>";
            echo "<td style='padding: 10px;'>" . ($medico['medico_nombre'] ?: 'Sin nombre') . "</td>";
            echo "<td style='padding: 10px; text-align: center;'>" . $medico['total_horarios'] . "</td>";
            echo "<td style='padding: 10px;'>" . $medico['dias_disponibles'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    echo "<hr>";
    echo "<h3>Crear Horarios de Ejemplo</h3>";
    echo "<p>Si no hay horarios configurados, puedes ejecutar este script para crear algunos:</p>";
    echo "<a href='crear_horarios_ejemplo.php' style='background-color: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Crear Horarios de Ejemplo</a>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>