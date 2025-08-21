<?php
require_once 'model/conexion.php';
try {
    $pdo = Conexion::conectar();
    
    echo "=== ESTRUCTURA TABLA CONSULTAS ===\n";
    $result = $pdo->query("SELECT column_name, data_type, is_nullable, column_default FROM information_schema.columns WHERE table_name = 'consultas' ORDER BY ordinal_position");
    
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        echo sprintf("%-20s %-15s %-10s %s\n", 
            $row['column_name'], 
            $row['data_type'], 
            $row['is_nullable'], 
            $row['column_default'] ?? ''
        );
    }
    
    echo "\n=== TABLAS RELACIONADAS ===\n";
    $tablesQuery = "SELECT table_name FROM information_schema.tables WHERE table_name LIKE '%consulta%' OR table_name = 'tipos_formularios' ORDER BY table_name";
    $tablesResult = $pdo->query($tablesQuery);
    
    while ($table = $tablesResult->fetch(PDO::FETCH_ASSOC)) {
        echo "- " . $table['table_name'] . "\n";
    }
    
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
}
?>