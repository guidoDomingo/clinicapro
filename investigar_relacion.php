<?php
require_once 'model/conexion.php';
$conexion = Conexion::conectar();

echo "=== INVESTIGANDO LA RELACIÓN COMPLETA ===\n";

echo "\n1. Estructura de agendas_cabecera:\n";
$stmt = $conexion->query("SELECT column_name FROM information_schema.columns WHERE table_name = 'agendas_cabecera' ORDER BY ordinal_position");
while ($row = $stmt->fetch()) {
    echo "- " . $row['column_name'] . "\n";
}

echo "\n2. Datos en agendas_cabecera:\n";
$stmt = $conexion->prepare("SELECT * FROM agendas_cabecera LIMIT 10");
$stmt->execute();
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    print_r($row);
}

echo "\n3. Agendas del doctor 18:\n";
$stmt = $conexion->prepare("SELECT * FROM agendas_cabecera WHERE medico_id = 18 OR doctor_id = 18");
try {
    $stmt->execute();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        print_r($row);
    }
} catch (Exception $e) {
    echo "Error buscando doctor_id: " . $e->getMessage() . "\n";
    
    // Probar otros campos
    $stmt = $conexion->query("SELECT * FROM agendas_cabecera LIMIT 1");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        echo "Campos en agendas_cabecera:\n";
        foreach (array_keys($row) as $campo) {
            echo "- $campo\n";
        }
    }
}

echo "\n4. Buscar qué agenda_id pertenece al doctor 18:\n";
// Basándonos en rs_servicios_doctors que tiene agenda_detalle_id
$stmt = $conexion->prepare("
    SELECT rsd.agenda_detalle_id, ad.agenda_id
    FROM rs_servicios_doctors rsd
    INNER JOIN agendas_detalle ad ON rsd.agenda_detalle_id = ad.detalle_id
    WHERE rsd.doctor_id = 18
");
$stmt->execute();
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    print_r($row);
}
?>