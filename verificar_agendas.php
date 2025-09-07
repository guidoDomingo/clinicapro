<?php
require_once 'model/conexion.php';
$conexion = Conexion::conectar();

echo "Estructura de agendas_detalle:\n";
$stmt = $conexion->query("SELECT column_name FROM information_schema.columns WHERE table_name = 'agendas_detalle' ORDER BY ordinal_position");
while ($row = $stmt->fetch()) {
    echo "- " . $row['column_name'] . "\n";
}

echo "\nDatos completos en agendas_detalle para doctor 18:\n";
$stmt = $conexion->prepare("SELECT * FROM agendas_detalle WHERE doctor_id = 18 ORDER BY det_id");
$stmt->execute();
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    print_r($row);
}
?>