<?php
// Verificar estructura real de la tabla consulta_anteojos
require_once 'model/conexion.php';

try {
    $pdo = Conexion::conectar();
    
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
    
    echo "🔑 COLUMNAS ENCONTRADAS:\n";
    foreach ($columns as $column) {
        $nullable = $column['is_nullable'] === 'YES' ? 'NULL' : 'NOT NULL';
        $default = $column['column_default'] ? " DEFAULT " . $column['column_default'] : '';
        echo "📋 {$column['column_name']} ({$column['data_type']}) {$nullable}{$default}\n";
        
        // Identificar la columna ID principal
        if (strpos(strtolower($column['column_name']), 'id') !== false && 
            strpos(strtolower($column['column_name']), 'anteojos') !== false) {
            echo "   🎯 ESTA PARECE SER LA CLAVE PRIMARIA!\n";
        }
    }
    
    echo "\n=== PRIMER REGISTRO DE EJEMPLO ===\n";
    
    // Obtener primer registro para ver los datos
    $query = "SELECT * FROM consulta_anteojos LIMIT 1";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $sample = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($sample) {
        foreach ($sample as $field => $value) {
            $displayValue = is_null($value) ? 'NULL' : (strlen((string)$value) > 50 ? substr((string)$value, 0, 50) . '...' : $value);
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
    
    // Verificar relaciones con consultas
    echo "\n=== VERIFICAR RELACIÓN CON CONSULTAS ===\n";
    $query = "SELECT ca.*, c.id_consulta 
              FROM consulta_anteojos ca 
              LEFT JOIN consultas c ON ca.id_consulta = c.id_consulta 
              LIMIT 3";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $relations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($relations) {
        echo "✅ Relación encontrada, primeros 3 registros:\n";
        foreach ($relations as $i => $rel) {
            echo "🔗 Registro " . ($i + 1) . ":\n";
            foreach ($rel as $field => $value) {
                if (strpos($field, 'id') !== false) {
                    echo "   {$field}: {$value}\n";
                }
            }
        }
    } else {
        echo "❌ No se encontraron relaciones\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>