<?php
/**
 * Debug para verificar qué tipo de datos devuelve el campo activo
 */

echo "🔍 Verificando tipos de datos del campo 'activo'" . PHP_EOL;
echo "===============================================" . PHP_EOL;

require_once 'model/conexion.php';

$conexion = Conexion::conectar();

// Verificar una reserva cancelada específica
$stmt = $conexion->prepare('
    SELECT 
        reserva_id,
        reserva_estado,
        activo,
        fecha_cancelacion
    FROM servicios_reservas 
    WHERE activo = false OR reserva_estado = \'CANCELADA\'
    LIMIT 1
');
$stmt->execute();
$reserva = $stmt->fetch(PDO::FETCH_ASSOC);

if ($reserva) {
    echo "Reserva encontrada:" . PHP_EOL;
    echo "- ID: " . $reserva['reserva_id'] . PHP_EOL;
    echo "- Estado: " . $reserva['reserva_estado'] . PHP_EOL;
    echo "- Activo: " . var_export($reserva['activo'], true) . PHP_EOL;
    echo "- Tipo de 'activo': " . gettype($reserva['activo']) . PHP_EOL;
    echo "- Fecha cancelación: " . ($reserva['fecha_cancelacion'] ?? 'NULL') . PHP_EOL;
    
    echo PHP_EOL . "Pruebas de lógica:" . PHP_EOL;
    echo "- !reserva['activo'] = " . (!$reserva['activo'] ? 'true' : 'false') . PHP_EOL;
    echo "- reserva['activo'] === false = " . ($reserva['activo'] === false ? 'true' : 'false') . PHP_EOL;
    echo "- reserva['activo'] === 'false' = " . ($reserva['activo'] === 'false' ? 'true' : 'false') . PHP_EOL;
    echo "- reserva['activo'] === 'f' = " . ($reserva['activo'] === 'f' ? 'true' : 'false') . PHP_EOL;
    echo "- reserva['activo'] === '0' = " . ($reserva['activo'] === '0' ? 'true' : 'false') . PHP_EOL;
    echo "- reserva['activo'] == 0 = " . ($reserva['activo'] == 0 ? 'true' : 'false') . PHP_EOL;
} else {
    echo "❌ No se encontró ninguna reserva cancelada" . PHP_EOL;
}

// También verificar una reserva activa para comparar
echo PHP_EOL . "Comparación con reserva activa:" . PHP_EOL;
$stmt = $conexion->prepare('
    SELECT 
        reserva_id,
        reserva_estado,
        activo
    FROM servicios_reservas 
    WHERE activo = true AND reserva_estado != \'CANCELADA\'
    LIMIT 1
');
$stmt->execute();
$reservaActiva = $stmt->fetch(PDO::FETCH_ASSOC);

if ($reservaActiva) {
    echo "- ID: " . $reservaActiva['reserva_id'] . PHP_EOL;
    echo "- Estado: " . $reservaActiva['reserva_estado'] . PHP_EOL;
    echo "- Activo: " . var_export($reservaActiva['activo'], true) . PHP_EOL;
    echo "- Tipo de 'activo': " . gettype($reservaActiva['activo']) . PHP_EOL;
}

?>