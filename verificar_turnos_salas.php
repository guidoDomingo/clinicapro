<?php
require_once 'model/conexion.php';
$conexion = Conexion::conectar();

echo "Turnos disponibles:\n";
$stmt = $conexion->query("SELECT * FROM turnos LIMIT 5");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    print_r($row);
}

echo "\nSalas disponibles:\n";
$stmt = $conexion->query("SELECT * FROM salas LIMIT 5");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    print_r($row);
}

echo "\nDetalles de los horarios existentes:\n";
$stmt = $conexion->prepare("
    SELECT ad.*, t.turno_nombre, s.sala_nombre
    FROM agendas_detalle ad
    LEFT JOIN turnos t ON ad.turno_id = t.turno_id
    LEFT JOIN salas s ON ad.sala_id = s.sala_id
    WHERE ad.agenda_id = 20
");
$stmt->execute();
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    print_r($row);
}
?>