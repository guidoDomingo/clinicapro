<?php
/**
 * Análisis completo de la estructura de base de datos
 * Para implementar sistema genérico de formularios
 */

require_once 'config/config.php';

echo "🔍 ANÁLISIS COMPLETO DE ESTRUCTURA DE BASE DE DATOS\n";
echo "==================================================\n\n";

try {
    $pdo = \Api\Core\Database::getConnection();
    
    // 1. Obtener todas las tablas
    echo "📊 1. TODAS LAS TABLAS EN LA BASE DE DATOS:\n";
    echo "==========================================\n";
    $stmt = $pdo->query("
        SELECT table_name, table_type
        FROM information_schema.tables 
        WHERE table_schema = 'public' 
        ORDER BY table_name
    ");
    
    $tables = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach($tables as $table) {
        echo "   • {$table['table_name']} ({$table['table_type']})\n";
    }
    
    // 2. Analizar tabla CONSULTAS (cabecera)
    echo "\n📋 2. ESTRUCTURA DE TABLA CONSULTAS (CABECERA):\n";
    echo "==============================================\n";
    $stmt = $pdo->query("
        SELECT column_name, data_type, is_nullable, column_default
        FROM information_schema.columns 
        WHERE table_name = 'consultas' 
        ORDER BY ordinal_position
    ");
    
    $consultasColumns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach($consultasColumns as $col) {
        $nullable = $col['is_nullable'] === 'YES' ? '✅' : '❌';
        $default = $col['column_default'] ? " (default: {$col['column_default']})" : '';
        echo "   • {$col['column_name']} - {$col['data_type']} - Null: {$nullable}{$default}\n";
    }
    
    // 3. Identificar tablas relacionadas con consultas
    echo "\n🔗 3. TABLAS RELACIONADAS CON CONSULTAS:\n";
    echo "=======================================\n";
    
    $relatedTables = [];
    
    // Buscar foreign keys hacia consultas
    $stmt = $pdo->query("
        SELECT 
            tc.table_name,
            kcu.column_name,
            ccu.table_name AS foreign_table_name,
            ccu.column_name AS foreign_column_name
        FROM information_schema.table_constraints tc
        JOIN information_schema.key_column_usage kcu ON tc.constraint_name = kcu.constraint_name
        JOIN information_schema.constraint_column_usage ccu ON ccu.constraint_name = tc.constraint_name
        WHERE tc.constraint_type = 'FOREIGN KEY' 
        AND (ccu.table_name = 'consultas' OR tc.table_name LIKE '%consulta%')
        ORDER BY tc.table_name
    ");
    
    $foreignKeys = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach($foreignKeys as $fk) {
        echo "   • {$fk['table_name']}.{$fk['column_name']} → {$fk['foreign_table_name']}.{$fk['foreign_column_name']}\n";
        if($fk['foreign_table_name'] === 'consultas') {
            $relatedTables[] = $fk['table_name'];
        }
    }
    
    // Buscar tablas que contengan 'consulta' en el nombre
    echo "\n📋 4. TABLAS CON 'CONSULTA' EN EL NOMBRE:\n";
    echo "=======================================\n";
    foreach($tables as $table) {
        if(stripos($table['table_name'], 'consulta') !== false) {
            echo "   • {$table['table_name']}\n";
            if(!in_array($table['table_name'], $relatedTables)) {
                $relatedTables[] = $table['table_name'];
            }
        }
    }
    
    // 5. Analizar cada tabla relacionada
    echo "\n🔍 5. ESTRUCTURA DE TABLAS RELACIONADAS:\n";
    echo "=======================================\n";
    
    $relatedTables = array_unique($relatedTables);
    sort($relatedTables);
    
    foreach($relatedTables as $tableName) {
        if($tableName === 'consultas') continue; // Ya analizada
        
        echo "\n📋 Tabla: {$tableName}\n";
        echo str_repeat("-", strlen($tableName) + 8) . "\n";
        
        try {
            $stmt = $pdo->query("
                SELECT column_name, data_type, is_nullable, column_default
                FROM information_schema.columns 
                WHERE table_name = '{$tableName}' 
                ORDER BY ordinal_position
            ");
            
            $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach($columns as $col) {
                $nullable = $col['is_nullable'] === 'YES' ? '✅' : '❌';
                $default = $col['column_default'] ? " (default: {$col['column_default']})" : '';
                echo "   • {$col['column_name']} - {$col['data_type']} - Null: {$nullable}{$default}\n";
            }
            
            // Contar registros
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM {$tableName}");
            $count = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            echo "   📊 Total registros: {$count}\n";
            
        } catch(Exception $e) {
            echo "   ❌ Error al analizar tabla: " . $e->getMessage() . "\n";
        }
    }
    
    // 6. Buscar tipos de formularios
    echo "\n📝 6. TIPOS DE FORMULARIOS IDENTIFICADOS:\n";
    echo "========================================\n";
    
    try {
        $stmt = $pdo->query("
            SELECT DISTINCT tipo_formulario, COUNT(*) as cantidad
            FROM consultas 
            WHERE tipo_formulario IS NOT NULL 
            GROUP BY tipo_formulario 
            ORDER BY cantidad DESC
        ");
        
        $tipos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach($tipos as $tipo) {
            echo "   • {$tipo['tipo_formulario']} ({$tipo['cantidad']} registros)\n";
        }
        
    } catch(Exception $e) {
        echo "   ❌ Error al obtener tipos: " . $e->getMessage() . "\n";
    }
    
    // 7. Identificar campos especiales
    echo "\n🎯 7. CAMPOS ESPECIALES EN CONSULTAS:\n";
    echo "====================================\n";
    
    $specialFields = [];
    foreach($consultasColumns as $col) {
        $name = $col['column_name'];
        if(stripos($name, 'fecha') !== false || $col['data_type'] === 'date') {
            $specialFields['dates'][] = $name;
        }
        if($col['data_type'] === 'text' || stripos($col['data_type'], 'varchar') !== false) {
            $specialFields['text'][] = $name;
        }
        if($col['data_type'] === 'integer' || $col['data_type'] === 'bigint') {
            $specialFields['numbers'][] = $name;
        }
    }
    
    echo "   📅 Campos de fecha:\n";
    if(isset($specialFields['dates'])) {
        foreach($specialFields['dates'] as $field) {
            echo "      • {$field}\n";
        }
    }
    
    echo "\n   📝 Campos de texto:\n";
    if(isset($specialFields['text'])) {
        foreach(array_slice($specialFields['text'], 0, 10) as $field) { // Mostrar solo primeros 10
            echo "      • {$field}\n";
        }
        if(count($specialFields['text']) > 10) {
            echo "      • ... y " . (count($specialFields['text']) - 10) . " más\n";
        }
    }
    
    echo "\n   🔢 Campos numéricos:\n";
    if(isset($specialFields['numbers'])) {
        foreach(array_slice($specialFields['numbers'], 0, 10) as $field) {
            echo "      • {$field}\n";
        }
    }
    
    // 8. Resumen para implementación
    echo "\n🚀 8. RESUMEN PARA IMPLEMENTACIÓN:\n";
    echo "=================================\n";
    echo "✅ Tabla principal: consultas\n";
    echo "✅ Tablas relacionadas encontradas: " . count($relatedTables) . "\n";
    echo "✅ Tipos de formulario: " . (isset($tipos) ? count($tipos) : 'N/A') . "\n";
    echo "✅ Campos en consultas: " . count($consultasColumns) . "\n";
    
    echo "\n📋 Tablas a integrar:\n";
    foreach($relatedTables as $table) {
        echo "   • {$table}\n";
    }
    
} catch(Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}

echo "\n🎯 SIGUIENTE PASO: Crear sistema genérico basado en esta estructura\n";
?>