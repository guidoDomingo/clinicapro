<?php
/**
 * Debug específico: Ver exactamente qué datos devuelve el backend
 */

echo "🔍 DEBUG: Datos exactos del backend para reservas canceladas" . PHP_EOL;
echo "==========================================================" . PHP_EOL;

require_once 'model/conexion.php';
require_once 'controller/servicios.controller.php';

// Probar la función exacta que usa AJAX
$fecha = '2025-09-29'; // Fecha que sabemos que tiene la reserva cancelada

echo "Llamando a ctrBuscarReservasConCanceladas para fecha: $fecha" . PHP_EOL;

$reservas = ControladorServicios::ctrBuscarReservasConCanceladas($fecha, null, null, null, null, null, true);

echo "Total reservas obtenidas: " . count($reservas) . PHP_EOL . PHP_EOL;

foreach ($reservas as $index => $reserva) {
    echo "=== RESERVA " . ($index + 1) . " ===" . PHP_EOL;
    echo "ID: " . ($reserva['reserva_id'] ?? 'NO_DEFINIDO') . PHP_EOL;
    echo "Estado: '" . ($reserva['reserva_estado'] ?? 'NO_DEFINIDO') . "'". PHP_EOL;
    echo "Longitud estado: " . strlen($reserva['reserva_estado'] ?? '') . PHP_EOL;
    echo "Estado ASCII: ";
    if (isset($reserva['reserva_estado'])) {
        for ($i = 0; $i < strlen($reserva['reserva_estado']); $i++) {
            echo ord($reserva['reserva_estado'][$i]) . " ";
        }
    }
    echo PHP_EOL;
    echo "Activo: " . ($reserva['activo'] ? 'true' : 'false') . PHP_EOL;
    echo "Paciente: " . ($reserva['paciente'] ?? 'NO_DEFINIDO') . PHP_EOL;
    echo "Doctor: " . ($reserva['doctor'] ?? 'NO_DEFINIDO') . PHP_EOL;
    
    // Prueba de comparación exacta
    $estado = $reserva['reserva_estado'] ?? '';
    echo "¿Es exactamente 'CANCELADA'? " . ($estado === 'CANCELADA' ? 'SÍ' : 'NO') . PHP_EOL;
    echo "¿Contiene 'CANCELADA'? " . (strpos($estado, 'CANCELADA') !== false ? 'SÍ' : 'NO') . PHP_EOL;
    echo "Estado trimmed: '" . trim($estado) . "'" . PHP_EOL;
    echo "¿Trimmed es 'CANCELADA'? " . (trim($estado) === 'CANCELADA' ? 'SÍ' : 'NO') . PHP_EOL;
    echo PHP_EOL;
}

echo "🎯 ESTE ES EL DATO EXACTO QUE RECIBE EL JAVASCRIPT" . PHP_EOL;
echo "Si hay diferencias, necesitamos ajustar la comparación en JS" . PHP_EOL;

?>