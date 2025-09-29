<?php
/**
 * Test de la nueva lógica de botones para reservas canceladas
 */

echo "🧪 Test: Nueva lógica de botones para reservas canceladas" . PHP_EOL;
echo "========================================================" . PHP_EOL;

require_once 'model/conexion.php';
require_once 'controller/servicios.controller.php';

$conexion = Conexion::conectar();

echo "1️⃣ Estado actual de reservas canceladas:" . PHP_EOL;

$stmt = $conexion->prepare('
    SELECT 
        reserva_id,
        reserva_estado,
        activo,
        fecha_reserva,
        CASE 
            WHEN reserva_estado = \'CANCELADA\' AND activo = false THEN \'BOTONES DESHABILITADOS\'
            WHEN reserva_estado = \'CANCELADA\' AND activo = true THEN \'BOTONES HABILITADOS\'
            ELSE \'BOTONES NORMALES\'
        END as estado_botones
    FROM servicios_reservas 
    WHERE reserva_estado = \'CANCELADA\'
    ORDER BY reserva_id
');
$stmt->execute();
$reservasCanceladas = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($reservasCanceladas as $reserva) {
    echo "- ID {$reserva['reserva_id']}: {$reserva['reserva_estado']} + activo={$reserva['activo']} → {$reserva['estado_botones']}" . PHP_EOL;
}

echo PHP_EOL . "2️⃣ Probando función del controlador (TODAS las reservas):" . PHP_EOL;

// Traer TODAS las reservas sin filtro de fecha para ver ambas canceladas
$todasReservas = ControladorServicios::ctrBuscarReservasConCanceladas(null, null, null, null, null, null, true);

echo "Total reservas obtenidas (sin filtro fecha): " . count($todasReservas) . PHP_EOL;

$activas = 0;
$canceladasHabilitadas = 0;
$canceladasDeshabilitadas = 0;

foreach ($todasReservas as $reserva) {
    if ($reserva['reserva_estado'] === 'CANCELADA') {
        if ($reserva['activo']) {
            $canceladasHabilitadas++;
            echo "  📝 ID {$reserva['reserva_id']}: CANCELADA + activo=true → Botones HABILITADOS" . PHP_EOL;
        } else {
            $canceladasDeshabilitadas++;
            echo "  🔒 ID {$reserva['reserva_id']}: CANCELADA + activo=false → Botones DESHABILITADOS" . PHP_EOL;
        }
    } else {
        $activas++;
    }
}

echo PHP_EOL . "📊 Resumen:" . PHP_EOL;
echo "- Reservas activas: $activas" . PHP_EOL;
echo "- Canceladas con botones HABILITADOS: $canceladasHabilitadas" . PHP_EOL;
echo "- Canceladas con botones DESHABILITADOS: $canceladasDeshabilitadas" . PHP_EOL;

echo PHP_EOL . "3️⃣ Lógica JavaScript esperada:" . PHP_EOL;
echo "```javascript" . PHP_EOL;
echo "// Para cada reserva:" . PHP_EOL;
echo "const esCanceladaYInactiva = (reserva.reserva_estado === 'CANCELADA') && (reserva.activo === false);" . PHP_EOL;
echo "const muestraComoCancelada = (reserva.reserva_estado === 'CANCELADA');" . PHP_EOL;
echo "" . PHP_EOL;
echo "// Resultado esperado:" . PHP_EOL;
foreach ($todasReservas as $reserva) {
    if ($reserva['reserva_estado'] === 'CANCELADA') {
        $jsEsCanceladaYInactiva = (!$reserva['activo']) ? 'true' : 'false';
        $jsMuestraComoCancelada = 'true';
        echo "// ID {$reserva['reserva_id']}: esCanceladaYInactiva=$jsEsCanceladaYInactiva, muestraComoCancelada=$jsMuestraComoCancelada" . PHP_EOL;
    }
}
echo "```" . PHP_EOL;

echo PHP_EOL . "🎯 RESULTADO ESPERADO EN LA INTERFAZ:" . PHP_EOL;
echo "✅ Ambas reservas canceladas aparecen con ESTILO visual de cancelada" . PHP_EOL;
echo "✅ Reserva ID 95: Botones HABILITADOS (puede editarse, cancelarse, etc.)" . PHP_EOL;
echo "✅ Reserva ID 123: Botones DESHABILITADOS (solo ver detalles)" . PHP_EOL;

?>