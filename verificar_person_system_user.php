<?php
$pdo = new PDO('pgsql:host=localhost;port=5432;dbname=clinica', 'postgres', 'admin');

echo "=== VERIFICAR DATOS PERSON_SYSTEM_USER ===\n";
$stmt = $pdo->query('SELECT * FROM person_system_user LIMIT 5');
while ($row = $stmt->fetch()) {
    echo "System User ID: " . $row['system_user_id'] . " - Person ID: " . $row['person_id'] . "\n";
}

echo "\n=== VERIFICAR CONSULTAS SIN DOCTOR ===\n";
$stmt = $pdo->query('SELECT c.id_consulta, c.id_user FROM consultas c WHERE c.id_user IS NULL OR c.id_user = 0 LIMIT 5');
while ($row = $stmt->fetch()) {
    echo "Consulta sin doctor: ID " . $row['id_consulta'] . " - id_user: " . ($row['id_user'] ?? 'NULL') . "\n";
}

echo "\n=== VERIFICAR CONSULTAS CON DOCTOR ===\n";
$stmt = $pdo->query('SELECT c.id_consulta, c.id_user FROM consultas c WHERE c.id_user IS NOT NULL AND c.id_user > 0 LIMIT 5');
while ($row = $stmt->fetch()) {
    echo "Consulta con doctor: ID " . $row['id_consulta'] . " - id_user: " . $row['id_user'] . "\n";
}
?>