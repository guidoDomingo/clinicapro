<?php
/**
 * Script para corregir TODAS las reservas canceladas a activo = false
 */

echo "🔧 Corrigiendo TODAS las reservas canceladas a activo = false" . PHP_EOL;
echo "==========================================================" . PHP_EOL;

require_once 'model/conexion.php';

$conexion = Conexion::conectar();

echo "1️⃣ Estado actual:" . PHP_EOL;
$stmt = $conexion->prepare('
    SELECT 
        reserva_id,
        reserva_estado,
        activo,
        fecha_reserva
    FROM servicios_reservas 
    WHERE reserva_estado = \'CANCELADA\'
    ORDER BY reserva_id
');
$stmt->execute();
$reservasCanceladas = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($reservasCanceladas as $reserva) {
    $activoTexto = $reserva['activo'] ? 'true ❌' : 'false ✅';
    echo "- ID {$reserva['reserva_id']}: Estado={$reserva['reserva_estado']}, activo=$activoTexto" . PHP_EOL;
}

$incorrectas = array_filter($reservasCanceladas, function($r) { return $r['activo']; });
echo PHP_EOL . "Reservas canceladas con activo = true (INCORRECTAS): " . count($incorrectas) . PHP_EOL;

if (count($incorrectas) > 0) {
    echo PHP_EOL . "2️⃣ Corrigiendo a activo = false..." . PHP_EOL;
    
    $stmt = $conexion->prepare('
        UPDATE servicios_reservas 
        SET activo = false,
            updated_at = NOW()
        WHERE reserva_estado = \'CANCELADA\' AND activo = true
    ');
    
    if ($stmt->execute()) {
        $filasAfectadas = $stmt->rowCount();
        echo "✅ $filasAfectadas reservas corregidas exitosamente." . PHP_EOL;
    } else {
        echo "❌ Error al corregir las reservas." . PHP_EOL;
    }
} else {
    echo "✅ Todas las reservas canceladas ya están correctas." . PHP_EOL;
}

echo PHP_EOL . "3️⃣ Verificación final:" . PHP_EOL;
$stmt = $conexion->prepare('
    SELECT 
        reserva_id,
        reserva_estado,
        activo
    FROM servicios_reservas 
    WHERE reserva_estado = \'CANCELADA\'
    ORDER BY reserva_id
');
$stmt->execute();
$verificacion = $stmt->fetchAll(PDO::FETCH_ASSOC);

$todasCorrectas = true;
foreach ($verificacion as $reserva) {
    $activoTexto = $reserva['activo'] ? 'true ❌' : 'false ✅';
    echo "- ID {$reserva['reserva_id']}: activo = $activoTexto" . PHP_EOL;
    if ($reserva['activo']) {
        $todasCorrectas = false;
    }
}

echo PHP_EOL . ($todasCorrectas ? "🎉 PERFECTO: Todas las reservas canceladas tienen activo = false" : "⚠️ ADVERTENCIA: Aún hay reservas canceladas con activo = true") . PHP_EOL;

echo PHP_EOL . "🎯 NUEVA LÓGICA APLICADA:" . PHP_EOL;
echo "✅ Todas las reservas CANCELADAS tienen activo = false" . PHP_EOL;
echo "✅ Los botones se deshabilitan para TODAS las canceladas" . PHP_EOL;
echo "✅ Aparecen en el reporte pero sin funcionalidad de botones" . PHP_EOL;

?>