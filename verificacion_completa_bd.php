<?php
require_once 'model/conexion.php';

echo "<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <title>Análisis Completo de Base de Datos</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .table-info { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 8px; }
        .success { background: #d4edda; color: #155724; }
        .error { background: #f8d7da; color: #721c24; }
        .info { background: #cce7ff; color: #004085; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .column-type { font-family: monospace; font-size: 0.9em; }
        .sample-data { background: #f8f9fa; padding: 10px; border-radius: 4px; margin: 10px 0; }
    </style>
</head>
<body>";

echo "<h1>🔍 Análisis Completo de Base de Datos</h1>";

try {
    $pdo = Conexion::conectar();
    
    // 1. Listar todas las tablas
    echo "<div class='table-info success'>";
    echo "<h2>📋 Todas las tablas en la base de datos:</h2>";
    $stmt = $pdo->query("
        SELECT 
            table_name,
            (SELECT COUNT(*) FROM information_schema.columns WHERE table_name = t.table_name AND table_schema = 'public') as column_count
        FROM information_schema.tables t 
        WHERE table_schema = 'public' 
        ORDER BY table_name
    ");
    $tables = $stmt->fetchAll();
    
    echo "<table>";
    echo "<tr><th>Tabla</th><th>Columnas</th><th>Registros</th></tr>";
    
    $existingTables = [];
    foreach($tables as $table) {
        $tableName = $table['table_name'];
        $existingTables[] = $tableName;
        
        // Contar registros
        try {
            $countStmt = $pdo->query("SELECT COUNT(*) as count FROM \"$tableName\"");
            $count = $countStmt->fetch()['count'];
        } catch (Exception $e) {
            $count = "Error";
        }
        
        echo "<tr>";
        echo "<td><strong>$tableName</strong></td>";
        echo "<td>{$table['column_count']}</td>";
        echo "<td>$count</td>";
        echo "</tr>";
    }
    echo "</table>";
    echo "</div>";
    
    // 2. Análisis detallado de tablas principales
    $mainTables = ['consultas', 'consulta_anteojos'];
    
    foreach($mainTables as $tableName) {
        if (in_array($tableName, $existingTables)) {
            echo "<div class='table-info info'>";
            echo "<h2>📊 Estructura de tabla: $tableName</h2>";
            
            // Estructura de la tabla
            $stmt = $pdo->query("
                SELECT 
                    column_name,
                    data_type,
                    is_nullable,
                    column_default,
                    character_maximum_length
                FROM information_schema.columns 
                WHERE table_name = '$tableName' AND table_schema = 'public'
                ORDER BY ordinal_position
            ");
            $columns = $stmt->fetchAll();
            
            echo "<table>";
            echo "<tr><th>Campo</th><th>Tipo</th><th>Nullable</th><th>Default</th><th>Longitud</th></tr>";
            
            foreach($columns as $col) {
                echo "<tr>";
                echo "<td><strong>{$col['column_name']}</strong></td>";
                echo "<td class='column-type'>{$col['data_type']}</td>";
                echo "<td>{$col['is_nullable']}</td>";
                echo "<td>{$col['column_default']}</td>";
                echo "<td>{$col['character_maximum_length']}</td>";
                echo "</tr>";
            }
            echo "</table>";
            
            // Datos de ejemplo
            echo "<div class='sample-data'>";
            echo "<h4>📝 Datos de ejemplo (primeros 3 registros):</h4>";
            try {
                $stmt = $pdo->query("SELECT * FROM \"$tableName\" LIMIT 3");
                $sampleData = $stmt->fetchAll();
                
                if (count($sampleData) > 0) {
                    echo "<table>";
                    // Headers
                    echo "<tr>";
                    foreach(array_keys($sampleData[0]) as $key) {
                        if (!is_numeric($key)) {
                            echo "<th>$key</th>";
                        }
                    }
                    echo "</tr>";
                    
                    // Data
                    foreach($sampleData as $row) {
                        echo "<tr>";
                        foreach($row as $key => $value) {
                            if (!is_numeric($key)) {
                                $displayValue = strlen($value) > 30 ? substr($value, 0, 30) . "..." : $value;
                                echo "<td>" . htmlspecialchars($displayValue) . "</td>";
                            }
                        }
                        echo "</tr>";
                    }
                    echo "</table>";
                } else {
                    echo "<p>No hay datos en esta tabla.</p>";
                }
            } catch (Exception $e) {
                echo "<p style='color: red;'>Error al obtener datos: " . $e->getMessage() . "</p>";
            }
            echo "</div>";
            
            echo "</div>";
        }
    }
    
    // 3. Buscar tablas relacionadas con personas/pacientes
    echo "<div class='table-info info'>";
    echo "<h2>👥 Búsqueda de tablas de personas/pacientes:</h2>";
    
    $personTables = [];
    foreach($existingTables as $tableName) {
        if (stripos($tableName, 'person') !== false || 
            stripos($tableName, 'pacient') !== false || 
            stripos($tableName, 'client') !== false ||
            stripos($tableName, 'user') !== false ||
            stripos($tableName, 'usuario') !== false) {
            $personTables[] = $tableName;
        }
    }
    
    if (count($personTables) > 0) {
        echo "<p class='success'>✅ Encontradas tablas relacionadas con personas:</p>";
        foreach($personTables as $table) {
            echo "- <strong>$table</strong><br>";
            
            // Mostrar estructura básica
            try {
                $stmt = $pdo->query("SELECT column_name FROM information_schema.columns WHERE table_name = '$table' AND table_schema = 'public' ORDER BY ordinal_position");
                $cols = $stmt->fetchAll();
                echo "  Campos: ";
                foreach($cols as $col) {
                    echo $col['column_name'] . ", ";
                }
                echo "<br><br>";
            } catch (Exception $e) {
                echo "  Error al obtener campos<br><br>";
            }
        }
    } else {
        echo "<p style='color: orange;'>⚠️ No se encontraron tablas específicas de personas/pacientes</p>";
    }
    
    // 4. Análisis de relaciones en tabla consultas
    echo "<h3>🔗 Análisis de campo id_persona en consultas:</h3>";
    if (in_array('consultas', $existingTables)) {
        try {
            $stmt = $pdo->query("
                SELECT 
                    id_persona, 
                    COUNT(*) as consultas_count 
                FROM consultas 
                GROUP BY id_persona 
                ORDER BY consultas_count DESC 
                LIMIT 10
            ");
            $personStats = $stmt->fetchAll();
            
            echo "<table>";
            echo "<tr><th>ID Persona</th><th>Número de Consultas</th></tr>";
            foreach($personStats as $stat) {
                echo "<tr><td>{$stat['id_persona']}</td><td>{$stat['consultas_count']}</td></tr>";
            }
            echo "</table>";
            
        } catch (Exception $e) {
            echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
        }
    }
    
    echo "</div>";
    
    // 5. Recomendaciones
    echo "<div class='table-info success'>";
    echo "<h2>💡 Recomendaciones para el Sistema CRUD:</h2>";
    echo "<ul>";
    echo "<li>✅ Tabla <strong>consultas</strong> existe y tiene datos (" . (in_array('consultas', $existingTables) ? "DISPONIBLE" : "NO DISPONIBLE") . ")</li>";
    echo "<li>✅ Tabla <strong>consulta_anteojos</strong> existe y tiene datos (" . (in_array('consulta_anteojos', $existingTables) ? "DISPONIBLE" : "NO DISPONIBLE") . ")</li>";
    echo "<li>❌ Tabla <strong>personas</strong> NO existe - necesita ser creada o usar otra tabla para datos de personas</li>";
    
    if (count($personTables) > 0) {
        echo "<li>💡 Considerar usar tabla alternativa para personas: <strong>" . implode(', ', $personTables) . "</strong></li>";
    } else {
        echo "<li>🚨 Se necesita crear una tabla de personas o modificar el sistema para funcionar solo con consultas</li>";
    }
    
    echo "</ul>";
    echo "</div>";
    
    // 6. Configuración sugerida para el sistema
    echo "<div class='table-info info'>";
    echo "<h2>⚙️ Configuración sugerida para el sistema Livewire:</h2>";
    echo "<pre>";
    echo "// Configuración basada en tablas existentes
\$tableConfig = [
    'consultas' => [
        'primaryKey' => 'id_consulta',
        'displayName' => 'Consultas',
        'fields' => [
            'id_consulta' => ['type' => 'int', 'primary' => true, 'auto' => true],
            'id_persona' => ['type' => 'int', 'required' => true, 'label' => 'ID Persona'],";
    
    if (in_array('consultas', $existingTables)) {
        $stmt = $pdo->query("SELECT column_name FROM information_schema.columns WHERE table_name = 'consultas' AND table_schema = 'public' ORDER BY ordinal_position LIMIT 10");
        $cols = $stmt->fetchAll();
        foreach($cols as $col) {
            if ($col['column_name'] !== 'id_consulta' && $col['column_name'] !== 'id_persona') {
                echo "
            '{$col['column_name']}' => ['type' => 'text', 'label' => '" . ucfirst(str_replace('_', ' ', $col['column_name'])) . "'],";
            }
        }
    }
    
    echo "
        ]
    ],";
    
    if (in_array('consulta_anteojos', $existingTables)) {
        echo "
    'consulta_anteojos' => [
        'primaryKey' => 'id_consulta_anteojos',
        'displayName' => 'Anteojos',
        'fields' => [
            'id_consulta_anteojos' => ['type' => 'int', 'primary' => true, 'auto' => true],
            'id_consulta' => ['type' => 'int', 'required' => true, 'foreign' => 'consultas.id_consulta'],";
        
        $stmt = $pdo->query("SELECT column_name FROM information_schema.columns WHERE table_name = 'consulta_anteojos' AND table_schema = 'public' ORDER BY ordinal_position LIMIT 10");
        $cols = $stmt->fetchAll();
        foreach($cols as $col) {
            if (!in_array($col['column_name'], ['id_consulta_anteojos', 'id_consulta'])) {
                echo "
            '{$col['column_name']}' => ['type' => 'text', 'label' => '" . ucfirst(str_replace('_', ' ', $col['column_name'])) . "'],";
            }
        }
        
        echo "
        ]
    ]";
    }
    
    echo "
];";
    echo "</pre>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div class='table-info error'>";
    echo "<h2>❌ Error de Conexión</h2>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
    echo "</div>";
}

echo "<div style='margin-top: 30px; text-align: center;'>";
echo "<a href='livewire-crud-system.html' style='padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; margin: 5px;'>🔙 Volver al Sistema CRUD</a>";
echo "<a href='analizar_bd_livewire.php' style='padding: 10px 20px; background: #28a745; color: white; text-decoration: none; border-radius: 5px; margin: 5px;'>🔄 Análisis Original</a>";
echo "</div>";

echo "</body></html>";
?>