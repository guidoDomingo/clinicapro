<?php
/**
 * Debug directo de la base de datos para ver todas las reservas canceladas
 */

echo "🔍 Debug directo de reservas canceladas" . PHP_EOL;
echo "=======================================" . PHP_EOL;

require_once 'model/conexion.php';

$conexion = Conexion::conectar();

$stmt = $conexion->prepare('
    SELECT 
        sr.reserva_id,
        sr.reserva_estado,
        sr.activo,
        sr.fecha_reserva,
        rp.first_name ||\' - \' || rp.last_name as paciente,
        rp2.first_name ||\' - \' || rp2.last_name as doctor
    FROM servicios_reservas sr 
    INNER JOIN rh_doctors rd ON sr.doctor_id = rd.doctor_id 
    INNER JOIN rh_person rp ON sr.paciente_id = rp.person_id 
    INNER JOIN rh_person rp2 ON rd.person_id = rp2.person_id 
    WHERE sr.reserva_estado = \'CANCELADA\'
    ORDER BY sr.reserva_id
');
$stmt->execute();
$reservas = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Reservas canceladas en BD: " . count($reservas) . PHP_EOL;

foreach ($reservas as $reserva) {
    echo "- ID {$reserva['reserva_id']}: {$reserva['reserva_estado']}, activo=" . ($reserva['activo'] ? 'true' : 'false') . ", Fecha: {$reserva['fecha_reserva']}" . PHP_EOL;
    echo "  Paciente: {$reserva['paciente']}, Doctor: {$reserva['doctor']}" . PHP_EOL;
}

// Ahora probar la función del modelo directamente
echo PHP_EOL . "Probando función del modelo:" . PHP_EOL;
require_once 'model/servicios.model.php';

$reservasModelo = ModelServicios::mdlObtenerReservasConCanceladas(null, null, null, null, null, null, true);
echo "Total reservas del modelo: " . count($reservasModelo) . PHP_EOL;

$canceladasEnModelo = array_filter($reservasModelo, function($r) {
    return $r['reserva_estado'] === 'CANCELADA';
});

echo "Canceladas en resultado del modelo: " . count($canceladasEnModelo) . PHP_EOL;
foreach ($canceladasEnModelo as $reserva) {
    echo "- ID {$reserva['reserva_id']}: {$reserva['reserva_estado']}, activo=" . ($reserva['activo'] ? 'true' : 'false') . PHP_EOL;
}

?>