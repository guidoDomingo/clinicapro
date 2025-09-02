<?php
$pdo = new PDO('pgsql:host=localhost;port=5432;dbname=clinica', 'postgres', 'admin');
$stmt = $pdo->query('SELECT p.person_id, p.first_name, p.last_name, p.email FROM rh_person p WHERE p.person_id IN (1,2,3,9)');
echo "=== DATOS DE PERSONAS ===\n";
while ($row = $stmt->fetch()) {
    echo "ID: " . $row['person_id'] . " - Nombre: " . $row['first_name'] . " " . $row['last_name'] . " - Email: " . $row['email'] . "\n";
}
?>