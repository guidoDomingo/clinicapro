<?php
$pdo = new PDO('pgsql:host=localhost;port=5432;dbname=clinica', 'postgres', 'admin');

echo "=== Verificar consulta ID 112 ===\n";
$stmt = $pdo->prepare('SELECT txtmotivo, consulta_textarea, receta_textarea, ultima_modificacion FROM consultas WHERE id_consulta = ?');
$stmt->execute([112]);

if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "Motivo: " . ($row['txtmotivo'] ?? 'Sin motivo') . "\n";
    echo "Consulta textarea: " . substr($row['consulta_textarea'] ?? 'Vacío', 0, 100) . "\n";
    echo "Receta textarea: " . substr($row['receta_textarea'] ?? 'Vacío', 0, 100) . "\n";
    echo "Última modificación: " . ($row['ultima_modificacion'] ?? 'Sin modificación') . "\n";
} else {
    echo "No se encontró la consulta ID 112\n";
}
?>