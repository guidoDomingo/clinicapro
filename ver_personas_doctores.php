<?php
$pdo = new PDO('pgsql:host=localhost;port=5432;dbname=clinica', 'postgres', 'admin');

echo "=== PRIMERAS PERSONAS EN RH_PERSON ===\n";
$stmt = $pdo->query('SELECT person_id, first_name, last_name, email FROM rh_person ORDER BY person_id LIMIT 10');
while ($row = $stmt->fetch()) {
    echo "ID: " . $row['person_id'] . " - " . $row['first_name'] . " " . $row['last_name'] . " - " . ($row['email'] ?? 'sin email') . "\n";
}

echo "\n=== VERIFICAR DOCTORES EN RH_DOCTORS ===\n";
$stmt = $pdo->query('SELECT rd.doctor_id, rd.person_id, p.first_name, p.last_name FROM rh_doctors rd JOIN rh_person p ON rd.person_id = p.person_id LIMIT 5');
while ($row = $stmt->fetch()) {
    echo "Doctor ID: " . $row['doctor_id'] . " - Person ID: " . $row['person_id'] . " - " . $row['first_name'] . " " . $row['last_name'] . "\n";
}
?>