<?php
$pdo = new PDO('pgsql:host=localhost;port=5432;dbname=clinica', 'postgres', 'admin');

echo "VERIFICAR CONSULTAS RECIENTES\n";
echo "=============================\n";

$stmt = $pdo->query('SELECT id_consulta, id_user, fecha_registro FROM consultas ORDER BY fecha_registro DESC LIMIT 5');
while ($row = $stmt->fetch()) {
    echo "ID: " . $row['id_consulta'] . " - User: " . ($row['id_user'] ?? 'NULL') . " - Fecha: " . $row['fecha_registro'] . "\n";
}
?>