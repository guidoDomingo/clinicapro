<?php
/**
 * Script para analizar la estructura completa de las tablas de consultas
 */

try {
    $pdo = new PDO('pgsql:host=localhost;port=5432;dbname=clinica', 'postgres', 'admin');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== ESTRUCTURA TABLA CONSULTAS ===\n";
    $stmt = $pdo->query("
        SELECT column_name, data_type, character_maximum_length, is_nullable, column_default 
        FROM information_schema.columns 
        WHERE table_name = 'consultas' 
        ORDER BY ordinal_position
    ");
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        printf("%-25s %-15s %-10s %-8s %s\n", 
               $row['column_name'], 
               $row['data_type'], 
               $row['character_maximum_length'] ?? 'N/A',
               $row['is_nullable'], 
               $row['column_default'] ?? '');
    }
    
    echo "\n=== ESTRUCTURA TABLA CONSULTA_ANTEOJOS ===\n";
    $stmt = $pdo->query("
        SELECT column_name, data_type, character_maximum_length, is_nullable, column_default 
        FROM information_schema.columns 
        WHERE table_name = 'consulta_anteojos' 
        ORDER BY ordinal_position
    ");
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        printf("%-25s %-15s %-10s %-8s %s\n", 
               $row['column_name'], 
               $row['data_type'], 
               $row['character_maximum_length'] ?? 'N/A',
               $row['is_nullable'], 
               $row['column_default'] ?? '');
    }
    
    echo "\n=== ESTRUCTURA TABLA CONSULTA_INFORME_IMAGEN ===\n";
    $stmt = $pdo->query("
        SELECT column_name, data_type, character_maximum_length, is_nullable, column_default 
        FROM information_schema.columns 
        WHERE table_name = 'consulta_informe_imagen' 
        ORDER BY ordinal_position
    ");
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        printf("%-25s %-15s %-10s %-8s %s\n", 
               $row['column_name'], 
               $row['data_type'], 
               $row['character_maximum_length'] ?? 'N/A',
               $row['is_nullable'], 
               $row['column_default'] ?? '');
    }
    
    echo "\n=== BUSCAR TABLAS RELACIONADAS CON ESTUDIOS ===\n";
    $stmt = $pdo->query("
        SELECT table_name, column_name, data_type 
        FROM information_schema.columns 
        WHERE table_name LIKE '%estudio%' OR table_name LIKE '%consulta%'
        ORDER BY table_name, ordinal_position
    ");
    
    $current_table = '';
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if ($current_table !== $row['table_name']) {
            if ($current_table !== '') echo "\n";
            echo "=== TABLA: " . strtoupper($row['table_name']) . " ===\n";
            $current_table = $row['table_name'];
        }
        printf("%-25s %s\n", $row['column_name'], $row['data_type']);
    }
    
    echo "\n=== SAMPLE DATA FROM CONSULTAS ===\n";
    $stmt = $pdo->query("SELECT * FROM consultas LIMIT 3");
    $columns = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if (empty($columns)) {
            $columns = array_keys($row);
            echo implode(' | ', array_map(fn($col) => str_pad($col, 15), $columns)) . "\n";
            echo str_repeat('-', count($columns) * 18) . "\n";
        }
        echo implode(' | ', array_map(fn($val) => str_pad(substr($val ?? 'NULL', 0, 13), 15), $row)) . "\n";
    }
    
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
}
?>