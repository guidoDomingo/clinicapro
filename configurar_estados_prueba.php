<?php
/**
 * Script para restaurar algunas reservas canceladas como activo = true 
 * para probar la nueva lógica de botones
 */

echo "🔄 Restaurando reservas canceladas para prueba" . PHP_EOL;
echo "=============================================" . PHP_EOL;

require_once 'model/conexion.php';

$conexion = Conexion::conectar();

// Encontrar reservas canceladas
$stmt = $conexion->prepare('
    SELECT reserva_id, reserva_estado, activo, fecha_reserva
    FROM servicios_reservas 
    WHERE reserva_estado = \'CANCELADA\'
    ORDER BY reserva_id
    LIMIT 3
');
$stmt->execute();
$reservas = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Reservas canceladas encontradas: " . count($reservas) . PHP_EOL;

if (count($reservas) > 0) {
    // Tomar la primera reserva y ponerla como activo = true (para demostrar diferentes estados)
    $primeraReserva = $reservas[0];
    
    echo "Configurando diferentes estados para prueba:" . PHP_EOL;
    echo "- Reserva {$primeraReserva['reserva_id']}: CANCELADA + activo = true (botones HABILITADOS)" . PHP_EOL;
    
    $stmt = $conexion->prepare('
        UPDATE servicios_reservas 
        SET activo = true,
            updated_at = NOW()
        WHERE reserva_id = ?
    ');
    
    if ($stmt->execute([$primeraReserva['reserva_id']])) {
        echo "✅ Reserva {$primeraReserva['reserva_id']} actualizada a activo = true" . PHP_EOL;
    }
    
    // Las demás reservas quedan como están (CANCELADA + activo = false)
    for ($i = 1; $i < count($reservas); $i++) {
        echo "- Reserva {$reservas[$i]['reserva_id']}: CANCELADA + activo = false (botones DESHABILITADOS)" . PHP_EOL;
    }
}

echo PHP_EOL . "🧪 Estado final para pruebas:" . PHP_EOL;
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
$todasCanceladas = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($todasCanceladas as $reserva) {
    $estadoBotones = ($reserva['activo']) ? "HABILITADOS 🔓" : "DESHABILITADOS 🔒";
    echo "- ID {$reserva['reserva_id']}: Estado={$reserva['reserva_estado']}, Activo=" . ($reserva['activo'] ? 'true' : 'false') . " → Botones $estadoBotones" . PHP_EOL;
}

echo PHP_EOL . "🎯 NUEVA LÓGICA:" . PHP_EOL;
echo "✅ Mostrar TODAS las reservas (activo = true y false)" . PHP_EOL;
echo "✅ Estilo visual: Todas las CANCELADAS tienen apariencia cancelada" . PHP_EOL;
echo "✅ Botones: Solo deshabilitados si CANCELADA Y activo = false" . PHP_EOL;
echo "✅ Botones: Habilitados si CANCELADA Y activo = true" . PHP_EOL;

?>