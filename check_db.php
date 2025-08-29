<?php
echo "=== VERIFICACIÓN DE PACIENTES EN BD ===" . PHP_EOL . PHP_EOL;

// Intentar diferentes configuraciones de conexión
$configs = [
    ['host' => 'localhost', 'dbname' => 'clinica_db', 'user' => 'postgres', 'pass' => ''],
    ['host' => 'localhost', 'dbname' => 'clinica_db', 'user' => 'postgres', 'pass' => 'root'],
    ['host' => 'localhost', 'dbname' => 'clinica_db', 'user' => 'postgres', 'pass' => 'postgres'],
    ['host' => 'localhost', 'dbname' => 'clinica', 'user' => 'postgres', 'pass' => ''],
    ['host' => 'localhost', 'dbname' => 'clinica', 'user' => 'postgres', 'pass' => 'root'],
    ['host' => 'localhost', 'dbname' => 'clinica', 'user' => 'postgres', 'pass' => 'postgres'],
];

$pdo = null;
$workingConfig = null;

foreach ($configs as $config) {
    try {
        echo "🔄 Probando: {$config['user']}@{$config['host']}/{$config['dbname']}..." . PHP_EOL;
        
        $pdo = new PDO(
            "pgsql:host={$config['host']};dbname={$config['dbname']}", 
            $config['user'], 
            $config['pass']
        );
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Test simple
        $stmt = $pdo->query("SELECT 1");
        if ($stmt) {
            $workingConfig = $config;
            echo "✅ Conexión exitosa!" . PHP_EOL . PHP_EOL;
            break;
        }
        
    } catch (PDOException $e) {
        echo "❌ Error: " . $e->getMessage() . PHP_EOL;
        $pdo = null;
    }
}

if (!$pdo) {
    echo "❌ No se pudo conectar a ninguna base de datos." . PHP_EOL;
    echo "Verifica que PostgreSQL esté corriendo y las credenciales sean correctas." . PHP_EOL;
    exit(1);
}

try {
    echo "📊 Usando: {$workingConfig['user']}@{$workingConfig['host']}/{$workingConfig['dbname']}" . PHP_EOL . PHP_EOL;
    
    // Verificar si existe la tabla rh_person
    $tables = ['rh_person', 'person', 'patients', 'pacientes'];
    $foundTable = null;
    
    foreach ($tables as $table) {
        try {
            $stmt = $pdo->query("SELECT COUNT(*) FROM $table LIMIT 1");
            $foundTable = $table;
            echo "✅ Tabla encontrada: $table" . PHP_EOL;
            break;
        } catch (PDOException $e) {
            echo "❌ Tabla $table no existe" . PHP_EOL;
        }
    }
    
    if (!$foundTable) {
        echo PHP_EOL . "❌ No se encontró ninguna tabla de pacientes." . PHP_EOL;
        echo "Tablas disponibles:" . PHP_EOL;
        
        $stmt = $pdo->query("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public'");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        foreach ($tables as $table) {
            echo "- $table" . PHP_EOL;
        }
        exit(1);
    }
    
    // Contar registros
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM $foundTable");
    $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    echo "📊 Total de registros en $foundTable: $total" . PHP_EOL . PHP_EOL;
    
    if ($total > 0) {
        // Mostrar estructura de la tabla
        echo "🔍 Estructura de la tabla:" . PHP_EOL;
        $stmt = $pdo->query("SELECT column_name, data_type FROM information_schema.columns WHERE table_name = '$foundTable' ORDER BY ordinal_position");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($columns as $column) {
            echo "- {$column['column_name']} ({$column['data_type']})" . PHP_EOL;
        }
        echo PHP_EOL;
        
        // Mostrar algunos registros
        $stmt = $pdo->query("SELECT * FROM $foundTable LIMIT 5");
        $samples = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "👥 Primeros 5 registros:" . PHP_EOL;
        foreach ($samples as $i => $record) {
            echo "Registro " . ($i + 1) . ":" . PHP_EOL;
            foreach ($record as $field => $value) {
                echo "  $field: " . (is_null($value) ? 'NULL' : $value) . PHP_EOL;
            }
            echo PHP_EOL;
        }
        
        // Buscar campos que contengan 'name'
        $nameFields = [];
        foreach ($columns as $column) {
            if (strpos(strtolower($column['column_name']), 'name') !== false) {
                $nameFields[] = $column['column_name'];
            }
        }
        
        if (count($nameFields) > 0) {
            echo "🔍 Campos de nombre encontrados: " . implode(', ', $nameFields) . PHP_EOL;
            
            // Buscar registros que contengan texto
            foreach ($nameFields as $field) {
                $stmt = $pdo->query("SELECT DISTINCT $field FROM $foundTable WHERE $field IS NOT NULL AND $field != '' LIMIT 10");
                $values = $stmt->fetchAll(PDO::FETCH_COLUMN);
                
                if (count($values) > 0) {
                    echo "Ejemplos en $field: " . implode(', ', array_slice($values, 0, 5)) . PHP_EOL;
                }
            }
        }
        
    } else {
        echo "⚠️ La tabla está vacía. Necesitas agregar datos de prueba." . PHP_EOL;
    }
    
} catch (Exception $e) {
    echo "❌ Error consultando base de datos: " . $e->getMessage() . PHP_EOL;
}
?>