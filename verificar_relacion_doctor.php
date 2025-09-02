<?php
/**
 * Verificar la nueva relación SQL para obtener información del doctor
 */

$pdo = new PDO('pgsql:host=localhost;port=5432;dbname=clinica', 'postgres', 'admin');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "=== PROBANDO NUEVA RELACIÓN SQL PARA DOCTOR ===\n";

$sql = "SELECT 
    c.id_consulta,
    c.id_user,
    patient.first_name as patient_first_name,
    patient.last_name as patient_last_name,
    doctor.email as doctor_email,
    doctor.first_name as doctor_first_name,
    doctor.last_name as doctor_last_name,
    doctor.document_number as doctor_document,
    doctor.phone_number as doctor_phone
FROM consultas c 
LEFT JOIN rh_person patient ON c.id_persona = patient.person_id 
LEFT JOIN person_system_user psu ON psu.system_user_id = c.id_user 
LEFT JOIN rh_person doctor ON psu.person_id = doctor.person_id
LEFT JOIN rh_doctors rd ON rd.person_id = doctor.person_id
WHERE c.id_consulta = 36";

try {
    $stmt = $pdo->query($sql);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result) {
        echo "✅ CONSULTA EXITOSA:\n";
        echo "ID Consulta: " . $result['id_consulta'] . "\n";
        echo "ID User: " . $result['id_user'] . "\n";
        echo "Paciente: " . $result['patient_first_name'] . " " . $result['patient_last_name'] . "\n";
        echo "Doctor: " . $result['doctor_first_name'] . " " . $result['doctor_last_name'] . "\n";
        echo "Doctor Email: " . $result['doctor_email'] . "\n";
        echo "Doctor Documento: " . $result['doctor_document'] . "\n";
        echo "Doctor Teléfono: " . $result['doctor_phone'] . "\n";
    } else {
        echo "❌ No se encontraron datos\n";
    }
    
    // Probar con otra consulta
    echo "\n=== PROBANDO CON CONSULTA 13 ===\n";
    $sql2 = str_replace('c.id_consulta = 36', 'c.id_consulta = 13', $sql);
    $stmt2 = $pdo->query($sql2);
    $result2 = $stmt2->fetch(PDO::FETCH_ASSOC);
    
    if ($result2) {
        echo "✅ CONSULTA 13 EXITOSA:\n";
        echo "Doctor: " . $result2['doctor_first_name'] . " " . $result2['doctor_last_name'] . "\n";
        echo "Doctor Email: " . $result2['doctor_email'] . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}
?>