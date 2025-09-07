<?php
require_once 'model/conexion.php';
$conexion = Conexion::conectar();

echo "=== INVESTIGACIÓN COMPLETA DE ESTRUCTURA ===\n";

echo "\n1. Estructura de tabla agendas:\n";
$stmt = $conexion->query("SELECT column_name FROM information_schema.columns WHERE table_name = 'agendas' ORDER BY ordinal_position");
while ($row = $stmt->fetch()) {
    echo "- " . $row['column_name'] . "\n";
}

echo "\n2. Datos en tabla agendas:\n";
$stmt = $conexion->prepare("SELECT * FROM agendas LIMIT 5");
$stmt->execute();
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    print_r($row);
}

echo "\n3. Buscando relación con doctor 18:\n";
// Intentar encontrar cómo se relaciona el doctor
$stmt = $conexion->prepare("SELECT * FROM agendas WHERE agenda_medico = 18 OR medico_id = 18 OR doctor_id = 18 LIMIT 5");
try {
    $stmt->execute();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        print_r($row);
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    
    // Probar con diferentes campos
    echo "Probando otros campos...\n";
    $stmt = $conexion->query("SELECT * FROM agendas LIMIT 2");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        echo "Campos disponibles en agendas:\n";
        foreach (array_keys($row) as $campo) {
            echo "- $campo\n";
        }
    }
}

echo "\n4. Datos completos en agendas_detalle:\n";
$stmt = $conexion->prepare("SELECT * FROM agendas_detalle LIMIT 5");
$stmt->execute();
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    print_r($row);
}
?>