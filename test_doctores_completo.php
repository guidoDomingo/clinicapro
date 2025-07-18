<?php
/**
 * Test específico para verificar la consulta de doctores por fecha
 * Con los datos reales de la base de datos
 */

require_once "model/conexion.php";
require_once "model/servicios.model.php";
require_once "controller/servicios.controller.php";

header('Content-Type: application/json; charset=utf-8');

$fecha = $_GET['fecha'] ?? '2025-07-17';

try {
    $conexion = Conexion::conectar();
    if (!$conexion) {
        throw new Exception("Error de conexión");
    }
    
    // 1. Probar la consulta directa que funciona
    echo "=== CONSULTA DIRECTA QUE FUNCIONA ===\n";
    $stmt = $conexion->prepare("
        SELECT 
            rp.person_id,
            rp.first_name,
            rp.last_name,
            rd.doctor_id,
            ad.*
        FROM agendas_detalle ad 
        INNER JOIN agendas_cabecera ac ON ad.agenda_id = ac.agenda_id 
        INNER JOIN rh_doctors rd ON rd.doctor_id = ac.medico_id 
        INNER JOIN rh_person rp ON rd.person_id = rp.person_id 
        WHERE ad.dia_semana = 'JUEVES'
    ");
    
    $stmt->execute();
    $consultaDirecta = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Consulta directa - Registros encontrados: " . count($consultaDirecta) . "\n";
    foreach ($consultaDirecta as $registro) {
        echo "- Dr. {$registro['first_name']} (ID: {$registro['doctor_id']}) - {$registro['hora_inicio']} a {$registro['hora_fin']}\n";
    }
    echo "\n";
    
    // 2. Probar la consulta del modelo
    echo "=== CONSULTA DEL MODELO ===\n";
    $doctoresModelo = ModelServicios::mdlObtenerDoctoresPorFecha($fecha);
    
    echo "Modelo - Registros encontrados: " . count($doctoresModelo) . "\n";
    foreach ($doctoresModelo as $doctor) {
        echo "- Dr. {$doctor['nombre_doctor']} (ID: {$doctor['doctor_id']}) - {$doctor['hora_inicio']} a {$doctor['hora_fin']}\n";
    }
    echo "\n";
    
    // 3. Probar el controlador
    echo "=== CONTROLADOR ===\n";
    $doctoresControlador = ControladorServicios::ctrObtenerDoctoresPorFecha($fecha);
    
    echo "Controlador - Registros encontrados: " . count($doctoresControlador) . "\n";
    foreach ($doctoresControlador as $doctor) {
        echo "- Dr. {$doctor['nombre_doctor']} (ID: {$doctor['doctor_id']}) - {$doctor['hora_inicio']} a {$doctor['hora_fin']}\n";
    }
    echo "\n";
    
    // 4. Respuesta JSON final
    $respuesta = [
        'fecha_consultada' => $fecha,
        'dia_semana' => 'JUEVES',
        'consulta_directa' => [
            'total' => count($consultaDirecta),
            'datos' => $consultaDirecta
        ],
        'modelo' => [
            'total' => count($doctoresModelo),
            'datos' => $doctoresModelo
        ],
        'controlador' => [
            'total' => count($doctoresControlador),
            'datos' => $doctoresControlador
        ],
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    echo "\n=== RESPUESTA JSON ===\n";
    echo json_encode($respuesta, JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    echo json_encode([
        'error' => true,
        'mensaje' => $e->getMessage(),
        'archivo' => $e->getFile(),
        'linea' => $e->getLine(),
        'fecha' => $fecha
    ], JSON_PRETTY_PRINT);
}
?>
