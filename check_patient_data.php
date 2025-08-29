<?php
// Verificar datos en rh_person
require_once 'model/conexion.php';

try {
    $conexion = Conexion::conectar();
    
    $sql = "SELECT id, first_name, last_name, document_number, phone_number FROM rh_person LIMIT 10";
    $result = $conexion->query($sql);
    
    echo "<h2>Datos en tabla rh_person:</h2>";
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>Nombres</th><th>Apellidos</th><th>CI</th><th>Teléfono</th></tr>";
    
    $count = 0;
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr>";
        echo "<td>{$row['id']}</td>";
        echo "<td>{$row['first_name']}</td>";
        echo "<td>{$row['last_name']}</td>";
        echo "<td>{$row['document_number']}</td>";
        echo "<td>{$row['phone_number']}</td>";
        echo "</tr>";
        $count++;
    }
    
    echo "</table>";
    echo "<p>Total de registros mostrados: $count</p>";
    
    // Verificar total
    $totalResult = $conexion->query("SELECT COUNT(*) as total FROM rh_person");
    $total = $totalResult->fetch(PDO::FETCH_ASSOC)['total'];
    echo "<p><strong>Total de registros en la tabla: $total</strong></p>";
    
    if ($total == 0) {
        echo "<h3>⚠️ No hay datos de prueba. Insertando algunos datos...</h3>";
        
        // Insertar datos de prueba
        $insertSQL = "
            INSERT INTO rh_person (first_name, last_name, document_number, phone_number) VALUES 
            ('Alejandro', 'Visconte', '8886767671', '0981123456'),
            ('María', 'González', '1234567890', '0981234567'),
            ('Juan', 'Pérez', '9876543210', '0981345678'),
            ('Ana', 'López', '1122334455', '0981456789')
        ";
        
        $conexion->exec($insertSQL);
        echo "<p>✅ Datos de prueba insertados exitosamente</p>";
    }
    
} catch (Exception $e) {
    echo "<h3>❌ Error:</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
}
?>
    
    echo "<h2>Datos en tabla rh_person:</h2>";
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>Nombres</th><th>Apellidos</th><th>CI</th><th>Teléfono</th></tr>";
    
    $count = 0;
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr>";
        echo "<td>{$row['id']}</td>";
        echo "<td>{$row['first_name']}</td>";
        echo "<td>{$row['last_name']}</td>";
        echo "<td>{$row['document_number']}</td>";
        echo "<td>{$row['phone_number']}</td>";
        echo "</tr>";
        $count++;
    }
    
    echo "</table>";
    echo "<p>Total de registros mostrados: $count</p>";
    
    // Verificar total
    $totalResult = $conexion->query("SELECT COUNT(*) as total FROM rh_person");
    $total = $totalResult->fetch(PDO::FETCH_ASSOC)['total'];
    echo "<p><strong>Total de registros en la tabla: $total</strong></p>";
    
    if ($total == 0) {
        echo "<h3>⚠️ No hay datos de prueba. Insertando algunos datos...</h3>";
        
        // Insertar datos de prueba
        $insertSQL = "
            INSERT INTO rh_person (first_name, last_name, document_number, phone_number) VALUES 
            ('Alejandro', 'Visconte', '8886767671', '0981123456'),
            ('María', 'González', '1234567890', '0981234567'),
            ('Juan', 'Pérez', '9876543210', '0981345678'),
            ('Ana', 'López', '1122334455', '0981456789')
        ";
        
        $conexion->exec($insertSQL);
        echo "<p>✅ Datos de prueba insertados exitosamente</p>";
    }
    
} catch (Exception $e) {
    echo "<h3>❌ Error:</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
}
?>