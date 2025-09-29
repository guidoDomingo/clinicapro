<?php
/**
 * Script para corregir reservas canceladas que no tienen activo = false
 */

echo "🔧 Corrigiendo reservas canceladas en la base de datos" . PHP_EOL;
echo "=====================================================" . PHP_EOL;

require_once 'model/conexion.php';

$conexion = Conexion::conectar();

// Encontrar reservas con estado CANCELADA pero activo = true
$stmt = $conexion->prepare('
    SELECT reserva_id, reserva_estado, activo
    FROM servicios_reservas 
    WHERE reserva_estado = \'CANCELADA\' AND activo = true
');
$stmt->execute();
$reservasIncorrectas = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Reservas encontradas con estado CANCELADA pero activo = true: " . count($reservasIncorrectas) . PHP_EOL;

if (count($reservasIncorrectas) > 0) {
    foreach ($reservasIncorrectas as $reserva) {
        echo "- Reserva ID {$reserva['reserva_id']}: Estado={$reserva['reserva_estado']}, Activo=" . ($reserva['activo'] ? 'true' : 'false') . PHP_EOL;
    }
    
    echo PHP_EOL . "¿Deseas corregir estas reservas marcándolas como activo = false? (y/n): ";
    $handle = fopen("php://stdin", "r");
    $respuesta = trim(fgets($handle));
    fclose($handle);
    
    if (strtolower($respuesta) === 'y') {
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
        echo "⏭️ Corrección cancelada por el usuario." . PHP_EOL;
    }
} else {
    echo "✅ No se encontraron reservas que necesiten corrección." . PHP_EOL;
}

echo PHP_EOL . "🧪 Verificación final:" . PHP_EOL;
$stmt = $conexion->prepare('
    SELECT 
        COUNT(*) as total,
        COUNT(CASE WHEN activo = false THEN 1 END) as canceladas_correctas,
        COUNT(CASE WHEN activo = true THEN 1 END) as canceladas_incorrectas
    FROM servicios_reservas 
    WHERE reserva_estado = \'CANCELADA\'
');
$stmt->execute();
$resultado = $stmt->fetch(PDO::FETCH_ASSOC);

echo "- Total reservas CANCELADAS: " . $resultado['total'] . PHP_EOL;
echo "- Con activo = false (correcto): " . $resultado['canceladas_correctas'] . PHP_EOL;
echo "- Con activo = true (incorrecto): " . $resultado['canceladas_incorrectas'] . PHP_EOL;

if ($resultado['canceladas_incorrectas'] == 0) {
    echo "✅ Todas las reservas canceladas están marcadas correctamente." . PHP_EOL;
} else {
    echo "⚠️ Aún hay " . $resultado['canceladas_incorrectas'] . " reservas canceladas mal marcadas." . PHP_EOL;
}

?>