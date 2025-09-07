<?php
require_once 'model/conexion.php';
$conexion = Conexion::conectar();

echo "=== BUSCANDO TABLAS RELACIONADAS ===\n";

$stmt = $conexion->query("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public' ORDER BY table_name");
echo "Todas las tablas en la base de datos:\n";
while ($row = $stmt->fetch()) {
    $tabla = $row['table_name'];
    if (strpos($tabla, 'agenda') !== false || 
        strpos($tabla, 'turno') !== false || 
        strpos($tabla, 'horario') !== false ||
        strpos($tabla, 'slot') !== false ||
        strpos($tabla, 'reserva') !== false) {
        echo ">>> $tabla <<<\n";
    } else {
        echo "- $tabla\n";
    }
}

echo "\n=== INVESTIGANDO agendas_detalle ===\n";
echo "Datos en agendas_detalle:\n";
$stmt = $conexion->prepare("SELECT detalle_id, agenda_id, servicio_id, dia_semana, hora_inicio, hora_fin FROM agendas_detalle LIMIT 10");
$stmt->execute();
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    print_r($row);
}

echo "\n=== BUSCANDO DÓNDE ESTÁ EL DOCTOR ===\n";
// Buscar en qué tabla está la relación con el doctor
$tablas = ['agendas_detalle', 'rs_servicios_doctors', 'usuarios'];
foreach ($tablas as $tabla) {
    echo "Verificando $tabla:\n";
    try {
        $stmt = $conexion->query("SELECT column_name FROM information_schema.columns WHERE table_name = '$tabla' AND (column_name LIKE '%doctor%' OR column_name LIKE '%medico%' OR column_name LIKE '%usuario%')");
        while ($row = $stmt->fetch()) {
            echo "- Campo relacionado con doctor: " . $row['column_name'] . "\n";
        }
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
}
?>