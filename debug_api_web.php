<?php
// Debug específico para el API web
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    // Log de debugging
    $debug_log = [];
    $debug_log[] = "=== DEBUGGING API WEB ===";
    
    // Verificar conexión
    require_once __DIR__ . '/model/conexion.php';
    $pdo = Conexion::conectar();
    $debug_log[] = "✅ Conexión establecida";
    
    // Verificar input
    $rawInput = file_get_contents('php://input');
    $debug_log[] = "Raw input: " . $rawInput;
    
    $input = json_decode($rawInput, true);
    $debug_log[] = "Parsed input: " . json_encode($input);
    
    if (!$input || !isset($input['action']) || $input['action'] !== 'loadPatientHistory') {
        throw new Exception('Invalid action');
    }
    
    $patientId = $input['data']['id'] ?? 0;
    $debug_log[] = "Patient ID: " . $patientId;
    
    // Test específico para ID 45
    $sql = "SELECT COUNT(*) as total FROM consultas WHERE id_persona = :patient_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['patient_id' => $patientId]);
    $count = $stmt->fetchColumn();
    $debug_log[] = "Consultas encontradas: " . $count;
    
    // Verificar si el paciente existe
    $sql = "SELECT person_id, first_name, last_name FROM rh_person WHERE person_id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id' => $patientId]);
    $patient = $stmt->fetch(PDO::FETCH_ASSOC);
    $debug_log[] = "Paciente encontrado: " . ($patient ? json_encode($patient) : 'NO');
    
    // Verificar consultas específicas
    $sql = "SELECT id_consulta, fecha_registro, txtmotivo, tipo_formulario 
            FROM consultas 
            WHERE id_persona = :patient_id 
            ORDER BY fecha_registro DESC 
            LIMIT 3";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['patient_id' => $patientId]);
    $consultas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $debug_log[] = "Consultas sample: " . json_encode($consultas);
    
    // Verificar todas las combinaciones posibles de ID
    $debug_log[] = "=== VERIFICANDO IDs ALTERNATIVOS ===";
    
    // Buscar en rh_person para encontrar el ID correcto
    $sql = "SELECT person_id, first_name, last_name 
            FROM rh_person 
            WHERE LOWER(first_name || ' ' || COALESCE(last_name, '')) LIKE LOWER('%alejandro%visconte%')
            OR LOWER(first_name || ' ' || COALESCE(last_name, '')) LIKE LOWER('%visconte%alejandro%')";
    $stmt = $pdo->query($sql);
    $patients = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $debug_log[] = "Pacientes con 'alejandro visconte': " . json_encode($patients);
    
    // Para cada paciente encontrado, verificar consultas
    foreach ($patients as $p) {
        $sql = "SELECT COUNT(*) FROM consultas WHERE id_persona = " . $p['person_id'];
        $count = $pdo->query($sql)->fetchColumn();
        $debug_log[] = "ID {$p['person_id']} ({$p['first_name']} {$p['last_name']}): $count consultas";
    }
    
    echo json_encode([
        'success' => true,
        'debug' => $debug_log,
        'data' => [
            'consultas' => $count,
            'cuota_mb' => 0,
            'historial' => $consultas,
            'timeline' => [],
            'archivos' => []
        ]
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'debug' => $debug_log ?? []
    ]);
}
?>