<?php
require_once 'model/conexion.php';

echo "<h1>🔍 Análisis de Tablas en la Base de Datos</h1>";

try {
    $pdo = Conexion::conectar();
    
    // Buscar tablas relacionadas con personas/pacientes
    echo "<h2>📋 Tablas relacionadas con personas/pacientes:</h2>";
    $stmt = $pdo->query("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public' AND (table_name LIKE '%person%' OR table_name LIKE '%pacient%' OR table_name LIKE '%client%')");
    $tables = $stmt->fetchAll();
    
    if (count($tables) > 0) {
        foreach($tables as $table) {
            echo "- " . $table['table_name'] . "<br>";
        }
    } else {
        echo "<p style='color: orange;'>No se encontraron tablas específicas de personas/pacientes</p>";
    }
    
    // Buscar todas las tablas disponibles
    echo "<h2>📊 Todas las tablas disponibles:</h2>";
    $stmt = $pdo->query("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public' ORDER BY table_name");
    $allTables = $stmt->fetchAll();
    
    echo "<div style='columns: 3; column-gap: 20px;'>";
    foreach($allTables as $table) {
        echo "- " . $table['table_name'] . "<br>";
    }
    echo "</div>";
    
    // Verificar estructura de la tabla consultas para ver cómo se relacionan los datos de personas
    echo "<h2>🔍 Estructura de la tabla consultas (campo id_persona):</h2>";
    $stmt = $pdo->query("SELECT DISTINCT id_persona FROM consultas ORDER BY id_persona LIMIT 10");
    $personas = $stmt->fetchAll();
    
    echo "<p><strong>Algunos IDs de persona encontrados:</strong></p>";
    foreach($personas as $persona) {
        echo "- ID: " . $persona['id_persona'] . "<br>";
    }
    
    // Buscar si existe una tabla que contenga datos de personas con estos IDs
    echo "<h2>🔎 Buscar datos de personas en otras tablas:</h2>";
    
    // Probar algunas posibles tablas
    $possibleTables = ['pacientes', 'clientes', 'users', 'usuarios', 'persona', 'patient'];
    
    foreach($possibleTables as $tableName) {
        try {
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM $tableName");
            $result = $stmt->fetch();
            echo "✅ Tabla '$tableName' existe con " . $result['count'] . " registros<br>";
            
            // Mostrar estructura
            $stmt = $pdo->query("SELECT column_name, data_type FROM information_schema.columns WHERE table_name = '$tableName' ORDER BY ordinal_position");
            $columns = $stmt->fetchAll();
            echo "Campos: ";
            foreach($columns as $col) {
                echo $col['column_name'] . " (" . $col['data_type'] . "), ";
            }
            echo "<br><br>";
            
        } catch (Exception $e) {
            // Tabla no existe, continuar
        }
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>