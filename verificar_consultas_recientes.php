<?php
$pdo = new PDO('pgsql:host=localhost;port=5432;dbname=clinica', 'postgres', 'admin');

echo "\n=== Últimas 5 consultas para paciente ID 45 ===\n";
$stmt = $pdo->query('SELECT id_consulta, txtmotivo, fecha_registro, ultima_modificacion FROM consultas WHERE id_persona = 45 ORDER BY id_consulta DESC LIMIT 5');

while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo 'ID: ' . $row['id_consulta'] . "\n";
    echo 'Motivo: ' . substr($row['txtmotivo'] ?? 'Sin motivo', 0, 80) . "\n";
    echo 'Registro: ' . ($row['fecha_registro'] ?? 'Sin fecha') . "\n";
    echo 'Modificación: ' . ($row['ultima_modificacion'] ?? 'Sin modificación') . "\n";
    echo "---\n";
}
?>