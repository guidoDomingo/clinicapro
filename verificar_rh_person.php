<?php
// Verificar estructura de rh_person

$baseDir = dirname(__FILE__);
require_once $baseDir . "/model/conexion.php";

echo "<h2>Estructura de rh_person</h2>";

try {
    $conexion = Conexion::conectar();
    
    // Verificar estructura de rh_person
    $stmt = $conexion->prepare("
        SELECT column_name, data_type, is_nullable, column_default
        FROM information_schema.columns 
        WHERE table_name = 'rh_person'
        ORDER BY ordinal_position
    ");
    $stmt->execute();
    $columnas = $stmt->fetchAll();
    
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr><th>Columna</th><th>Tipo</th><th>Nulo</th><th>Por Defecto</th></tr>";
    foreach ($columnas as $columna) {
        echo "<tr>";
        echo "<td>" . $columna['column_name'] . "</td>";
        echo "<td>" . $columna['data_type'] . "</td>";
        echo "<td>" . $columna['is_nullable'] . "</td>";
        echo "<td>" . ($columna['column_default'] ?? 'N/A') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Probar con person_id
    echo "<h3>Datos de ejemplo con person_id conocidos:</h3>";
    $stmt = $conexion->prepare("SELECT * FROM rh_person WHERE person_id IN (53, 56, 58) ORDER BY person_id");
    $stmt->execute();
    $personas = $stmt->fetchAll();
    
    if (count($personas) > 0) {
        echo "<pre>" . print_r($personas, true) . "</pre>";
    } else {
        echo "<p style='color: red;'>No se encontraron personas</p>";
        
        // Probar con LIMIT para ver qué hay
        $stmt = $conexion->prepare("SELECT * FROM rh_person LIMIT 3");
        $stmt->execute();
        $personas = $stmt->fetchAll();
        
        echo "<h3>Primeros 3 registros:</h3>";
        echo "<pre>" . print_r($personas, true) . "</pre>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>