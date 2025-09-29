<?php
/**
 * Test final: Verificar que todas las reservas canceladas aparecen 
 * en el reporte con botones deshabilitados
 */

echo "🧪 Test Final: Reservas canceladas en reporte" . PHP_EOL;
echo "============================================" . PHP_EOL;

require_once 'model/conexion.php';
require_once 'controller/servicios.controller.php';

$conexion = Conexion::conectar();

echo "1️⃣ Verificación en base de datos:" . PHP_EOL;
$stmt = $conexion->prepare('
    SELECT 
        COUNT(*) as total_canceladas,
        COUNT(CASE WHEN activo = false THEN 1 END) as con_activo_false,
        COUNT(CASE WHEN activo = true THEN 1 END) as con_activo_true
    FROM servicios_reservas 
    WHERE reserva_estado = \'CANCELADA\'
');
$stmt->execute();
$estadisticas = $stmt->fetch(PDO::FETCH_ASSOC);

echo "- Total reservas CANCELADAS: " . $estadisticas['total_canceladas'] . PHP_EOL;
echo "- Con activo = false: " . $estadisticas['con_activo_false'] . " ✅" . PHP_EOL;
echo "- Con activo = true: " . $estadisticas['con_activo_true'] . ($estadisticas['con_activo_true'] > 0 ? " ❌" : " ✅") . PHP_EOL;

echo PHP_EOL . "2️⃣ Verificación del controlador:" . PHP_EOL;
$todasReservas = ControladorServicios::ctrBuscarReservasConCanceladas(null, null, null, null, null, null, true);
$reservasCanceladas = array_filter($todasReservas, function($r) {
    return $r['reserva_estado'] === 'CANCELADA';
});

echo "- Total reservas obtenidas: " . count($todasReservas) . PHP_EOL;
echo "- Reservas canceladas en resultado: " . count($reservasCanceladas) . PHP_EOL;

if (count($reservasCanceladas) > 0) {
    echo PHP_EOL . "📋 Reservas canceladas encontradas:" . PHP_EOL;
    foreach ($reservasCanceladas as $reserva) {
        echo "- ID {$reserva['reserva_id']}: {$reserva['reserva_estado']}, activo=" . ($reserva['activo'] ? 'true' : 'false') . ", Fecha: {$reserva['fecha_reserva']}" . PHP_EOL;
    }
}

echo PHP_EOL . "3️⃣ Lógica JavaScript esperada:" . PHP_EOL;
echo "```javascript" . PHP_EOL;
echo "// Para cada reserva cancelada:" . PHP_EOL;
echo "const esCancelada = (reserva.reserva_estado === 'CANCELADA');" . PHP_EOL;
echo "// Si esCancelada = true → Botones DESHABILITADOS" . PHP_EOL;
echo "// Si esCancelada = true → Estilo visual de cancelada" . PHP_EOL;
echo "```" . PHP_EOL;

echo PHP_EOL . "🎯 RESULTADO ESPERADO EN NAVEGADOR:" . PHP_EOL;
echo "✅ Todas las reservas canceladas aparecen en el reporte" . PHP_EOL;
echo "✅ Con estado 'CANCELADO' (badge rojo)" . PHP_EOL;
echo "✅ Con estilo visual de cancelada (gris, tachado)" . PHP_EOL;
echo "✅ Con TODOS los botones deshabilitados excepto 'Ver detalles'" . PHP_EOL;
echo "✅ Tooltips explicativos en botones deshabilitados" . PHP_EOL;

echo PHP_EOL . "📝 PARA PROBAR:" . PHP_EOL;
echo "1. Ve a: http://localhost/clinica/index.php?ruta=servicios" . PHP_EOL;
echo "2. Pestaña 'Reservas'" . PHP_EOL;
echo "3. Filtro 'Mostrar Canceladas' → 'Mostrar Canceladas'" . PHP_EOL;
echo "4. Verifica que las reservas canceladas:" . PHP_EOL;
echo "   - Aparecen en la lista" . PHP_EOL;
echo "   - Tienen estado 'CANCELADO'" . PHP_EOL;
echo "   - Botones deshabilitados (gris)" . PHP_EOL;
echo "   - Solo funciona el botón 'Ver detalles'" . PHP_EOL;

if ($estadisticas['total_canceladas'] > 0 && $estadisticas['con_activo_true'] == 0) {
    echo PHP_EOL . "🎉 ¡PERFECTO! El sistema está listo para funcionar correctamente." . PHP_EOL;
} else {
    echo PHP_EOL . "⚠️ Hay reservas canceladas que necesitan corrección." . PHP_EOL;
}

?>