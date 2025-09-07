<?php
require_once 'model/conexion.php';
$conexion = Conexion::conectar();

echo "=== VERIFICANDO ESTRUCTURA DE TABLAS ===\n";

// Verificar tabla rs_servicios
echo "\n1. Estructura de rs_servicios:\n";
$stmt = $conexion->query("SELECT column_name FROM information_schema.columns WHERE table_name = 'rs_servicios' ORDER BY ordinal_position");
while ($row = $stmt->fetch()) {
    echo "- " . $row['column_name'] . "\n";
}

// Verificar tabla rs_servicios_doctors
echo "\n2. Estructura de rs_servicios_doctors:\n";
$stmt = $conexion->query("SELECT column_name FROM information_schema.columns WHERE table_name = 'rs_servicios_doctors' ORDER BY ordinal_position");
while ($row = $stmt->fetch()) {
    echo "- " . $row['column_name'] . "\n";
}

// Verificar datos en rs_servicios_doctors
echo "\n3. Datos en rs_servicios_doctors para doctor 18:\n";
$stmt = $conexion->prepare("SELECT * FROM rs_servicios_doctors WHERE doctor_id = 18");
$stmt->execute();
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    print_r($row);
}

// Verificar agendas_detalle
echo "\n4. Horarios en agendas_detalle para doctor 18:\n";
$stmt = $conexion->prepare("SELECT id, servicio_id, hora_inicio, hora_fin, dia_semana FROM agendas_detalle WHERE doctor_id = 18 ORDER BY servicio_id, hora_inicio");
$stmt->execute();
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    print_r($row);
}

// Verificar con qué día de la semana corresponde hoy
echo "\n5. Día de la semana de hoy:\n";
$hoy = date('Y-m-d');
$diaSemana = date('N', strtotime($hoy)); // 1=Lunes, 7=Domingo
echo "Fecha: $hoy\n";
echo "Día de la semana (1=Lun, 7=Dom): $diaSemana\n";
?>