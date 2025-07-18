<?php
/**
 * Script para verificar directamente los doctores disponibles para JUEVES
 */

require_once "model/conexion.php";

// Configurar headers para mostrar texto plano con formato
header('Content-Type: text/plain; charset=utf-8');

echo "=== VERIFICACIÓN DE DOCTORES PARA EL DÍA JUEVES ===\n";
echo "Fecha de consulta: " . date('Y-m-d H:i:s') . "\n";
echo "Fecha objetivo: 2025-07-17 (JUEVES)\n\n";

try {
    $conexion = Conexion::conectar();
    if (!$conexion) {
        throw new Exception("Error de conexión a la base de datos");
    }
    
    echo "✅ Conexión a la base de datos exitosa\n\n";
    
    // 1. Verificar si hay registros en agendas_detalle para JUEVES
    echo "=== 1. REGISTROS EN AGENDAS_DETALLE PARA 'JUEVES' ===\n";
    $stmt = $conexion->prepare("SELECT COUNT(*) as total FROM agendas_detalle WHERE dia_semana = 'JUEVES'");
    $stmt->execute();
    $total = $stmt->fetchColumn();
    echo "Total de registros en agendas_detalle para JUEVES: $total\n\n";
    
    if ($total == 0) {
        echo "⚠️ No hay registros para JUEVES en agendas_detalle\n";
        echo "Verificando qué días están disponibles...\n\n";
        
        $stmt = $conexion->query("SELECT DISTINCT dia_semana, COUNT(*) as total FROM agendas_detalle GROUP BY dia_semana ORDER BY dia_semana");
        $dias = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "Días disponibles en agendas_detalle:\n";
        foreach ($dias as $dia) {
            echo "- {$dia['dia_semana']}: {$dia['total']} registros\n";
        }
        echo "\n";
    }
    
    // 2. Ejecutar la consulta completa exacta que proporcionaste
    echo "=== 2. CONSULTA COMPLETA (TU QUERY) ===\n";
    $queryCompleta = "
        SELECT 
            rp.person_id,
            rp.first_name,
            rp.last_name,
            rd.doctor_id,
            rd.especialidad,
            ad.hora_inicio,
            ad.hora_fin,
            ad.intervalo_minutos,
            ad.dia_semana
        FROM agendas_detalle ad 
        INNER JOIN agendas_cabecera ac ON ad.agenda_id = ac.agenda_id 
        INNER JOIN rh_doctors rd ON rd.doctor_id = ac.medico_id 
        INNER JOIN rh_person rp ON rd.person_id = rp.person_id 
        WHERE ad.dia_semana = 'JUEVES'
    ";
    
    $stmt = $conexion->prepare($queryCompleta);
    $stmt->execute();
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Resultados encontrados: " . count($resultados) . "\n";
    
    if (!empty($resultados)) {
        echo "\nDoctores disponibles para JUEVES:\n";
        foreach ($resultados as $i => $doctor) {
            echo "--- Doctor #" . ($i + 1) . " ---\n";
            echo "ID: {$doctor['doctor_id']}\n";
            echo "Nombre: {$doctor['first_name']} {$doctor['last_name']}\n";
            echo "Especialidad: {$doctor['especialidad']}\n";
            echo "Horario: {$doctor['hora_inicio']} - {$doctor['hora_fin']}\n";
            echo "Intervalo: {$doctor['intervalo_minutos']} minutos\n\n";
        }
    } else {
        echo "\n❌ No se encontraron doctores para JUEVES\n\n";
    }
    
    // 3. Verificar la estructura de las tablas involucradas
    echo "=== 3. VERIFICACIÓN DE ESTRUCTURA ===\n";
    
    $tablas = [
        'agendas_detalle' => 'SELECT COUNT(*) FROM agendas_detalle',
        'agendas_cabecera' => 'SELECT COUNT(*) FROM agendas_cabecera', 
        'rh_doctors' => 'SELECT COUNT(*) FROM rh_doctors',
        'rh_person' => 'SELECT COUNT(*) FROM rh_person'
    ];
    
    foreach ($tablas as $tabla => $query) {
        try {
            $stmt = $conexion->query($query);
            $count = $stmt->fetchColumn();
            echo "✅ Tabla $tabla: $count registros\n";
        } catch (Exception $e) {
            echo "❌ Error en tabla $tabla: " . $e->getMessage() . "\n";
        }
    }
    
    // 4. Probar el método del modelo con la fecha específica
    echo "\n=== 4. PRUEBA DEL MODELO ===\n";
    require_once "model/servicios.model.php";
    
    $doctoresModelo = ModelServicios::mdlObtenerDoctoresPorFecha('2025-07-17');
    echo "Resultado del modelo: " . count($doctoresModelo) . " doctores\n";
    
    if (!empty($doctoresModelo)) {
        echo "Doctores devueltos por el modelo:\n";
        foreach ($doctoresModelo as $doctor) {
            echo "- ID: {$doctor['doctor_id']}, Nombre: {$doctor['nombre_doctor']}\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . "\n";
    echo "Línea: " . $e->getLine() . "\n";
}

echo "\n=== FIN DE LA VERIFICACIÓN ===\n";
?>
