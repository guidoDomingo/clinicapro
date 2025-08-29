<?php
// Verificar estructura de la tabla rh_person
require_once 'model/conexion.php';

try {
    $conexion = Conexion::conectar();
    
    // Obtener estructura de la tabla
    $sql = "SELECT column_name, data_type, is_nullable, column_default 
            FROM information_schema.columns 
            WHERE table_name = 'rh_person' 
            ORDER BY ordinal_position";
    
    $result = $conexion->query($sql);
    
    echo "<h2>Estructura de la tabla rh_person:</h2>";
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Columna</th><th>Tipo</th><th>Nullable</th><th>Default</th></tr>";
    
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr>";
        echo "<td>{$row['column_name']}</td>";
        echo "<td>{$row['data_type']}</td>";
        echo "<td>{$row['is_nullable']}</td>";
        echo "<td>{$row['column_default']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // También ver algunos datos si existen
    echo "<h3>Primeros registros:</h3>";
    
    // Primero verificar qué columnas tienen datos
    $dataSQL = "SELECT * FROM rh_person LIMIT 5";
    $dataResult = $conexion->query($dataSQL);
    
    if ($dataResult->rowCount() > 0) {
        $firstRow = $dataResult->fetch(PDO::FETCH_ASSOC);
        echo "<pre>";
        print_r($firstRow);
        echo "</pre>";
    } else {
        echo "<p>No hay datos en la tabla</p>";
    }
    
} catch (Exception $e) {
    echo "<h3>❌ Error:</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
}
?>