<?php
require_once 'model/conexion.php';

echo "\n=== ANÁLISIS RÁPIDO DE BASE DE DATOS ===\n\n";

try {
    $pdo = Conexion::conectar();
    
    // Obtener todas las tablas
    $stmt = $pdo->query("
        SELECT table_name 
        FROM information_schema.tables 
        WHERE table_schema = 'public' 
        ORDER BY table_name
    ");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "📋 TABLAS ENCONTRADAS: " . count($tables) . "\n";
    echo str_repeat("-", 50) . "\n";
    
    foreach($tables as $tableName) {
        try {
            // Contar registros
            $countStmt = $pdo->query("SELECT COUNT(*) FROM \"$tableName\"");
            $count = $countStmt->fetch(PDO::FETCH_COLUMN);
            
            // Obtener algunas columnas
            $colStmt = $pdo->query("
                SELECT column_name 
                FROM information_schema.columns 
                WHERE table_name = '$tableName' AND table_schema = 'public' 
                ORDER BY ordinal_position 
                LIMIT 5
            ");
            $columns = $colStmt->fetchAll(PDO::FETCH_COLUMN);
            
            echo "📊 $tableName: $count registros\n";
            echo "   Columnas: " . implode(', ', $columns) . "\n\n";
            
        } catch (Exception $e) {
            echo "❌ Error con $tableName: " . $e->getMessage() . "\n\n";
        }
    }
    
    // Verificaciones específicas
    echo "\n" . str_repeat("=", 50) . "\n";
    echo "🔍 VERIFICACIONES ESPECÍFICAS\n";
    echo str_repeat("=", 50) . "\n";
    
    // Verificar consultas
    if (in_array('consultas', $tables)) {
        echo "✅ Tabla 'consultas' EXISTE\n";
        
        $stmt = $pdo->query("SELECT COUNT(DISTINCT id_persona) as personas_count FROM consultas");
        $result = $stmt->fetch();
        echo "   - Personas diferentes en consultas: " . $result['personas_count'] . "\n";
        
        $stmt = $pdo->query("SELECT MIN(id_persona) as min_id, MAX(id_persona) as max_id FROM consultas");
        $result = $stmt->fetch();
        echo "   - Rango de IDs persona: " . $result['min_id'] . " - " . $result['max_id'] . "\n";
    } else {
        echo "❌ Tabla 'consultas' NO existe\n";
    }
    
    // Verificar consulta_anteojos
    if (in_array('consulta_anteojos', $tables)) {
        echo "✅ Tabla 'consulta_anteojos' EXISTE\n";
        $stmt = $pdo->query("SELECT COUNT(*) FROM consulta_anteojos");
        $count = $stmt->fetch(PDO::FETCH_COLUMN);
        echo "   - Total registros: $count\n";
    } else {
        echo "❌ Tabla 'consulta_anteojos' NO existe\n";
    }
    
    // Verificar personas
    if (in_array('personas', $tables)) {
        echo "✅ Tabla 'personas' EXISTE\n";
    } else {
        echo "❌ Tabla 'personas' NO EXISTE\n";
        
        // Buscar alternativas
        $personTables = array_filter($tables, function($table) {
            return stripos($table, 'person') !== false || 
                   stripos($table, 'pacient') !== false || 
                   stripos($table, 'client') !== false ||
                   stripos($table, 'user') !== false;
        });
        
        if (!empty($personTables)) {
            echo "💡 Posibles alternativas: " . implode(', ', $personTables) . "\n";
        }
    }
    
    echo "\n" . str_repeat("=", 50) . "\n";
    echo "🎯 RECOMENDACIÓN PARA SISTEMA LIVEWIRE:\n";
    echo str_repeat("=", 50) . "\n";
    
    if (in_array('consultas', $tables) && in_array('consulta_anteojos', $tables)) {
        if (in_array('personas', $tables)) {
            echo "🚀 SISTEMA COMPLETO: Usar consultas + consulta_anteojos + personas\n";
        } else {
            echo "⚠️  SISTEMA PARCIAL: Usar solo consultas + consulta_anteojos (sin relación a personas)\n";
            echo "    Necesitarás modificar el sistema para que funcione sin tabla personas\n";
        }
    } else {
        echo "🚨 SISTEMA INCOMPLETO: Faltan tablas principales\n";
    }
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}

echo "\n=== FIN DEL ANÁLISIS ===\n";
?>