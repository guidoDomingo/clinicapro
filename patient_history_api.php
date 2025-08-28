<?php
// API simple directo en la raíz que sabemos que funciona
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
error_reporting(E_ALL);
ini_set('display_errors', 0);

require_once 'model/conexion.php';

try {
    $pdo = Conexion::conectar();
    
    // Get input
    $rawInput = file_get_contents('php://input');
    $input = json_decode($rawInput, true);
    
    if (!$input || $input['action'] !== 'loadPatientHistory') {
        throw new Exception('Invalid action');
    }
    
    $patientId = $input['data']['id'] ?? 0;
    
    // Count consultas
    $sql = "SELECT COUNT(*) as total FROM consultas WHERE id_persona = :patient_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['patient_id' => $patientId]);
    $consultasCount = (int) $stmt->fetchColumn();
    
    // Get historial
    $sql = "SELECT 
                c.id_consulta,
                c.fecha_registro,
                c.txtmotivo as motivo_consulta,
                c.consulta_textarea as diagnostico,
                c.tipo_formulario
            FROM consultas c
            WHERE c.id_persona = :patient_id 
            ORDER BY c.fecha_registro DESC 
            LIMIT 10";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['patient_id' => $patientId]);
    $historial = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get timeline
    $sql = "SELECT 
                'consulta' as tipo,
                fecha_registro as fecha,
                CONCAT('Consulta: ', COALESCE(LEFT(txtmotivo, 50), 'Sin motivo')) as descripcion,
                id_consulta as referencia_id,
                tipo_formulario
            FROM consultas 
            WHERE id_persona = :patient_id
            ORDER BY fecha_registro DESC 
            LIMIT 15";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['patient_id' => $patientId]);
    $timeline = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => [
            'consultas' => $consultasCount,
            'cuota_mb' => 0,
            'historial' => $historial,
            'timeline' => $timeline,
            'archivos' => []
        ],
        'message' => 'Datos históricos del paciente cargados exitosamente',
        'debug' => [
            'patient_id' => $patientId,
            'consultas_count' => $consultasCount,
            'historial_count' => count($historial),
            'timeline_count' => count($timeline)
        ]
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'data' => [
            'consultas' => 0,
            'cuota_mb' => 0,
            'historial' => [],
            'timeline' => [],
            'archivos' => []
        ]
    ]);
}
?>