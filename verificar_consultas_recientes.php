<?php<?php

$pdo = new PDO('pgsql:host=localhost;port=5432;dbname=clinica', 'postgres', 'admin');$pdo = new PDO('pgsql:host=localhost;port=5432;dbname=clinica', 'postgres', 'admin');



echo "=== CONSULTAS MÁS RECIENTES ===\n";echo "\n=== Últimas 5 consultas para paciente ID 45 ===\n";

$stmt = $pdo->query('SELECT id_consulta, id_user, id_persona, fecha_registro, tipo_formulario FROM consultas ORDER BY fecha_registro DESC LIMIT 10');$stmt = $pdo->query('SELECT id_consulta, txtmotivo, fecha_registro, ultima_modificacion FROM consultas WHERE id_persona = 45 ORDER BY id_consulta DESC LIMIT 5');

while ($row = $stmt->fetch()) {

    echo "ID: " . $row['id_consulta'] . " - User: " . ($row['id_user'] ?? 'NULL') . " - Fecha: " . $row['fecha_registro'] . " - Tipo: " . $row['tipo_formulario'] . "\n";while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

}    echo 'ID: ' . $row['id_consulta'] . "\n";

    echo 'Motivo: ' . substr($row['txtmotivo'] ?? 'Sin motivo', 0, 80) . "\n";

echo "\n=== VERIFICAR CONSULTA MÁS RECIENTE CON DOCTOR ===\n";    echo 'Registro: ' . ($row['fecha_registro'] ?? 'Sin fecha') . "\n";

$sql = "SELECT     echo 'Modificación: ' . ($row['ultima_modificacion'] ?? 'Sin modificación') . "\n";

    c.id_consulta,    echo "---\n";

    c.id_user,}

    c.fecha_registro,?>
    patient.first_name as patient_first_name,
    patient.last_name as patient_last_name,
    doctor.email as doctor_email,
    doctor.first_name as doctor_first_name,
    doctor.last_name as doctor_last_name,
    doctor.document_number as doctor_document
FROM consultas c 
LEFT JOIN rh_person patient ON c.id_persona = patient.person_id 
LEFT JOIN person_system_user psu ON psu.system_user_id = c.id_user 
LEFT JOIN rh_person doctor ON psu.person_id = doctor.person_id
LEFT JOIN rh_doctors rd ON rd.person_id = doctor.person_id
WHERE c.id_user IS NOT NULL
ORDER BY c.fecha_registro DESC 
LIMIT 5";

$stmt = $pdo->query($sql);
while ($row = $stmt->fetch()) {
    echo "Consulta " . $row['id_consulta'] . ":\n";
    echo "  - Paciente: " . $row['patient_first_name'] . " " . $row['patient_last_name'] . "\n";
    echo "  - Doctor: " . ($row['doctor_first_name'] ?? 'N/A') . " " . ($row['doctor_last_name'] ?? 'N/A') . "\n";
    echo "  - Doctor Email: " . ($row['doctor_email'] ?? 'N/A') . "\n";
    echo "  - Fecha: " . $row['fecha_registro'] . "\n\n";
}
?>