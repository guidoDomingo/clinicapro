<?php
/**
 * Debug específico para el problema de datos vacíos
 */

require_once "model/conexion.php";

header('Content-Type: text/plain; charset=utf-8');

echo "=== DEBUG: ¿POR QUÉ NO SE DEVUELVEN DOCTORES? ===\n";
echo "Fecha objetivo: 2025-07-17 (JUEVES)\n";
echo "Timestamp: " . date('Y-m-d H:i:s') . "\n\n";

try {
    $conexion = Conexion::conectar();
    if (!$conexion) {
        throw new Exception("Error de conexión");
    }
    
    echo "✅ Conexión exitosa\n\n";
    
    // 1. Tu consulta exacta que funciona
    echo "1. TU CONSULTA EXACTA (que funciona):\n";
    echo "-------------------------------------------\n";
    $stmt = $conexion->prepare("
        SELECT 
            rp.person_id,
            rp.first_name,
            ad.*
        FROM agendas_detalle ad 
        INNER JOIN agendas_cabecera ac ON ad.agenda_id = ac.agenda_id 
        INNER JOIN rh_doctors rd ON rd.doctor_id = ac.medico_id 
        INNER JOIN rh_person rp ON rd.person_id = rp.person_id 
        WHERE ad.dia_semana = 'JUEVES'
    ");
    
    $stmt->execute();
    $tuConsulta = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Resultados: " . count($tuConsulta) . " registros\n";
    foreach ($tuConsulta as $i => $row) {
        echo "  [$i] Dr. {$row['first_name']} - detalle_estado: " . ($row['detalle_estado'] ? 'true' : 'false') . "\n";
        echo "      Horario: {$row['hora_inicio']} - {$row['hora_fin']}\n";
    }
    echo "\n";
    
    // 2. La misma consulta pero con filtro detalle_estado = true
    echo "2. CON FILTRO detalle_estado = true:\n";
    echo "-----------------------------------\n";
    $stmt = $conexion->prepare("
        SELECT 
            rp.person_id,
            rp.first_name,
            ad.*
        FROM agendas_detalle ad 
        INNER JOIN agendas_cabecera ac ON ad.agenda_id = ac.agenda_id 
        INNER JOIN rh_doctors rd ON rd.doctor_id = ac.medico_id 
        INNER JOIN rh_person rp ON rd.person_id = rp.person_id 
        WHERE ad.dia_semana = 'JUEVES'
            AND ad.detalle_estado = true
    ");
    
    $stmt->execute();
    $conFiltro = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Resultados: " . count($conFiltro) . " registros\n";
    foreach ($conFiltro as $i => $row) {
        echo "  [$i] Dr. {$row['first_name']} - detalle_estado: " . ($row['detalle_estado'] ? 'true' : 'false') . "\n";
        echo "      Horario: {$row['hora_inicio']} - {$row['hora_fin']}\n";
    }
    echo "\n";
    
    // 3. Verificar el estado de agenda_estado en agendas_cabecera
    echo "3. VERIFICAR agenda_estado en agendas_cabecera:\n";
    echo "-----------------------------------------------\n";
    $stmt = $conexion->prepare("
        SELECT 
            ac.agenda_id,
            ac.agenda_estado,
            COUNT(ad.detalle_id) as total_detalles
        FROM agendas_cabecera ac
        LEFT JOIN agendas_detalle ad ON ac.agenda_id = ad.agenda_id AND ad.dia_semana = 'JUEVES'
        GROUP BY ac.agenda_id, ac.agenda_estado
        HAVING COUNT(ad.detalle_id) > 0
    ");
    
    $stmt->execute();
    $agendas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Agendas con detalles para JUEVES: " . count($agendas) . "\n";
    foreach ($agendas as $agenda) {
        echo "  - Agenda ID: {$agenda['agenda_id']}, Estado: " . ($agenda['agenda_estado'] ? 'true' : 'false') . ", Detalles: {$agenda['total_detalles']}\n";
    }
    echo "\n";
    
    // 4. La consulta del modelo actual (completa)
    echo "4. CONSULTA DEL MODELO ACTUAL:\n";
    echo "------------------------------\n";
    $stmt = $conexion->prepare("
        SELECT DISTINCT
            rd.doctor_id AS doctor_id,
            COALESCE(rp.first_name, '') || ' ' || COALESCE(rp.last_name, '') AS nombre_doctor,
            rp.document_number,
            rp.person_id,
            rd.especialidad,
            ad.hora_inicio,
            ad.hora_fin,
            ad.intervalo_minutos,
            ad.detalle_id,
            ad.agenda_id
        FROM agendas_detalle ad 
        INNER JOIN agendas_cabecera ac ON ad.agenda_id = ac.agenda_id 
        INNER JOIN rh_doctors rd ON rd.doctor_id = ac.medico_id 
        INNER JOIN rh_person rp ON rd.person_id = rp.person_id 
        WHERE ad.dia_semana = 'JUEVES'
            AND ad.detalle_estado = true
        ORDER BY rp.first_name, rp.last_name
    ");
    
    $stmt->execute();
    $modeloActual = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Resultados modelo actual: " . count($modeloActual) . " registros\n";
    foreach ($modeloActual as $i => $row) {
        echo "  [$i] {$row['nombre_doctor']} (ID: {$row['doctor_id']})\n";
        echo "      Horario: {$row['hora_inicio']} - {$row['hora_fin']}\n";
    }
    echo "\n";
    
    // 5. Sin filtros para ver todo
    echo "5. SIN FILTROS (para ver todos los estados):\n";
    echo "--------------------------------------------\n";
    $stmt = $conexion->prepare("
        SELECT DISTINCT
            rd.doctor_id AS doctor_id,
            COALESCE(rp.first_name, '') || ' ' || COALESCE(rp.last_name, '') AS nombre_doctor,
            ad.detalle_estado,
            ac.agenda_estado,
            ad.hora_inicio,
            ad.hora_fin
        FROM agendas_detalle ad 
        INNER JOIN agendas_cabecera ac ON ad.agenda_id = ac.agenda_id 
        INNER JOIN rh_doctors rd ON rd.doctor_id = ac.medico_id 
        INNER JOIN rh_person rp ON rd.person_id = rp.person_id 
        WHERE ad.dia_semana = 'JUEVES'
        ORDER BY rp.first_name, rp.last_name
    ");
    
    $stmt->execute();
    $sinFiltros = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Resultados sin filtros: " . count($sinFiltros) . " registros\n";
    foreach ($sinFiltros as $i => $row) {
        $detalleEstado = $row['detalle_estado'] ? 'true' : 'false';
        $agendaEstado = $row['agenda_estado'] ? 'true' : 'false';
        echo "  [$i] {$row['nombre_doctor']} (ID: {$row['doctor_id']})\n";
        echo "      Horario: {$row['hora_inicio']} - {$row['hora_fin']}\n";
        echo "      detalle_estado: $detalleEstado, agenda_estado: $agendaEstado\n";
    }
    echo "\n";
    
    // DIAGNÓSTICO
    echo "=== DIAGNÓSTICO ===\n";
    $tuTotal = count($tuConsulta);
    $filtradoTotal = count($conFiltro);
    $modeloTotal = count($modeloActual);
    
    echo "- Tu consulta (sin filtros): $tuTotal registros\n";
    echo "- Con filtro detalle_estado=true: $filtradoTotal registros\n";
    echo "- Modelo actual: $modeloTotal registros\n\n";
    
    if ($tuTotal > 0 && $filtradoTotal == 0) {
        echo "🔍 PROBLEMA IDENTIFICADO: Los registros tienen detalle_estado = false\n";
        echo "💡 SOLUCIÓN: Remover el filtro 'AND ad.detalle_estado = true' del modelo\n";
    } elseif ($tuTotal > 0 && $filtradoTotal > 0 && $modeloTotal == 0) {
        echo "🔍 PROBLEMA: Hay un problema en la consulta del modelo (posiblemente DISTINCT o ORDER BY)\n";
    } elseif ($tuTotal == 0) {
        echo "🔍 PROBLEMA: No hay datos para JUEVES en la base de datos\n";
    } else {
        echo "✅ Los datos están correctos. El problema puede estar en otro lugar.\n";
    }
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . "\n";
    echo "Línea: " . $e->getLine() . "\n";
}

echo "\n=== FIN DEL DEBUG ===\n";
?>
