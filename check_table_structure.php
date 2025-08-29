<?php
require_once 'model/conexion.php';

try {
    $db = Conexion::conectar();
    
    echo "=== ESTRUCTURA TABLA CONSULTAS ===\n";
    echo str_repeat('-', 60) . "\n";
    
    $stmt = $db->prepare("
        SELECT column_name, data_type, character_maximum_length, is_nullable
        FROM information_schema.columns 
        WHERE table_name = 'consultas' 
        ORDER BY ordinal_position
    ");
    $stmt->execute();
    $columns = $stmt->fetchAll();
    
    foreach($columns as $col) {
        $len = $col['character_maximum_length'] ? '(' . $col['character_maximum_length'] . ')' : '';
        $null = $col['is_nullable'] == 'YES' ? 'NULL' : 'NOT NULL';
        echo sprintf("%-20s %-20s %s\n", 
            $col['column_name'], 
            $col['data_type'] . $len, 
            $null
        );
    }
    
    echo "\n=== CAMPOS CON LIMITE DE 255 CARACTERES ===\n";
    echo str_repeat('-', 60) . "\n";
    
    foreach($columns as $col) {
        if ($col['character_maximum_length'] == 255) {
            echo "- " . $col['column_name'] . "\n";
        }
    }
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
?>
