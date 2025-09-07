<?php
require_once "model/conexion.php";

try {
    $pdo = Conexion::conectar();
    
    echo "<h2>Diagnóstico de Estructura de Tablas</h2>";
    
    // Verificar estructura de servicios_reservas
    echo "<h3>Estructura de la tabla servicios_reservas:</h3>";
    $sql = "SELECT column_name, data_type, is_nullable, column_default 
            FROM information_schema.columns 
            WHERE table_name = 'servicios_reservas' 
            ORDER BY ordinal_position";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($columns) > 0) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>Columna</th><th>Tipo</th><th>Permite NULL</th><th>Default</th></tr>";
        foreach ($columns as $column) {
            echo "<tr>";
            echo "<td>" . $column['column_name'] . "</td>";
            echo "<td>" . $column['data_type'] . "</td>";
            echo "<td>" . $column['is_nullable'] . "</td>";
            echo "<td>" . ($column['column_default'] ?? 'NULL') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No se encontró la tabla servicios_reservas</p>";
    }
    
    // Verificar estructura de rs_servicios_doctors
    echo "<h3>Estructura de la tabla rs_servicios_doctors:</h3>";
    $sql2 = "SELECT column_name, data_type, is_nullable, column_default 
             FROM information_schema.columns 
             WHERE table_name = 'rs_servicios_doctors' 
             ORDER BY ordinal_position";
    
    $stmt2 = $pdo->prepare($sql2);
    $stmt2->execute();
    $columns2 = $stmt2->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($columns2) > 0) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>Columna</th><th>Tipo</th><th>Permite NULL</th><th>Default</th></tr>";
        foreach ($columns2 as $column) {
            echo "<tr>";
            echo "<td>" . $column['column_name'] . "</td>";
            echo "<td>" . $column['data_type'] . "</td>";
            echo "<td>" . $column['is_nullable'] . "</td>";
            echo "<td>" . ($column['column_default'] ?? 'NULL') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No se encontró la tabla rs_servicios_doctors</p>";
    }
    
    // Verificar muestra de datos de servicios_reservas
    echo "<h3>Muestra de datos de servicios_reservas (primeros 5 registros):</h3>";
    $sql3 = "SELECT * FROM servicios_reservas LIMIT 5";
    $stmt3 = $pdo->prepare($sql3);
    $stmt3->execute();
    $reservas = $stmt3->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($reservas) > 0) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        $headers = array_keys($reservas[0]);
        echo "<tr>";
        foreach ($headers as $header) {
            echo "<th>" . $header . "</th>";
        }
        echo "</tr>";
        
        foreach ($reservas as $reserva) {
            echo "<tr>";
            foreach ($reserva as $value) {
                echo "<td>" . ($value ?? 'NULL') . "</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No hay datos en servicios_reservas</p>";
    }
    
} catch (Exception $e) {
    echo "<p>Error: " . $e->getMessage() . "</p>";
}
?>