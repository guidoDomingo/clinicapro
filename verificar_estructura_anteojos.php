<?php
// Verificar estructura real de la tabla consulta_anteojos
require_once 'config/database.php';

try {
    $pdo = new PDO($dsn, $username, $password, $options);
    
    echo "=== ESTRUCTURA TABLA consulta_anteojos ===\n";
    
    // Obtener estructura de la tabla
    $query = "SELECT column_name, data_type, is_nullable, column_default 
              FROM information_schema.columns 
              WHERE table_name = 'consulta_anteojos' 
              ORDER BY ordinal_position";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($columns)) {
        echo "❌ Tabla 'consulta_anteojos' no encontrada\n";
        exit;
    }
    
    foreach ($columns as $column) {
        $nullable = $column['is_nullable'] === 'YES' ? 'NULL' : 'NOT NULL';
        $default = $column['column_default'] ? " DEFAULT " . $column['column_default'] : '';
        echo "📋 {$column['column_name']} ({$column['data_type']}) {$nullable}{$default}\n";
    }
    
    echo "\n=== PRIMER REGISTRO DE EJEMPLO ===\n";
    
    // Obtener primer registro para ver los datos
    $query = "SELECT * FROM consulta_anteojos LIMIT 1";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $sample = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($sample) {
        foreach ($sample as $field => $value) {
            $displayValue = is_null($value) ? 'NULL' : (strlen($value) > 50 ? substr($value, 0, 50) . '...' : $value);
            echo "🔍 {$field}: {$displayValue}\n";
        }
    } else {
        echo "❌ No hay registros en la tabla\n";
    }
    
    echo "\n=== CONTEO DE REGISTROS ===\n";
    $query = "SELECT COUNT(*) as total FROM consulta_anteojos";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $count = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "📊 Total registros: {$count['total']}\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>