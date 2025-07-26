<?php
require_once 'model/conexion.php';

try {
    $pdo = Conexion::conectar();
    
    // Verificar si existe la tabla tipo_formularios
    $result = $pdo->query("SHOW TABLES LIKE 'tipo_formularios'");
    $exists = $result->fetchColumn();
    
    if ($exists) {
        echo "✅ La tabla 'tipo_formularios' existe\n";
        
        // Verificar estructura
        $result = $pdo->query("DESCRIBE tipo_formularios");
        $columns = $result->fetchAll(PDO::FETCH_ASSOC);
        echo "Columnas de tipo_formularios:\n";
        foreach($columns as $col) {
            echo "- {$col['Field']} ({$col['Type']})\n";
        }
        
        // Verificar datos
        $result = $pdo->query("SELECT COUNT(*) as total FROM tipo_formularios");
        $count = $result->fetch(PDO::FETCH_ASSOC);
        echo "Total de registros: {$count['total']}\n";
        
    } else {
        echo "❌ La tabla 'tipo_formularios' NO existe\n";
        
        // Buscar tablas similares
        $result = $pdo->query("SHOW TABLES");
        $tables = $result->fetchAll(PDO::FETCH_COLUMN);
        
        echo "Buscando tablas similares:\n";
        foreach($tables as $table) {
            if (stripos($table, 'tipo') !== false || stripos($table, 'form') !== false) {
                echo "- $table\n";
            }
        }
    }
    
    echo "\n=== TEST DE CONSULTA ORIGINAL ===\n";
    
    // Probar la consulta original sin el JOIN problemático
    $sql = "SELECT 
            p.*,
            rp.first_name,
            rp.last_name,
            d.doctor_id,
            b.business_name
        FROM preformatos p
        LEFT JOIN rh_doctors d ON p.creado_por = d.doctor_id 
        LEFT JOIN rh_person rp ON d.person_id = rp.person_id
        LEFT JOIN sys_business b ON d.business_id = b.business_id
        WHERE p.activo = true
        ORDER BY p.nombre ASC
        LIMIT 3";
        
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Consulta SIN JOIN con tipo_formularios funciona: " . count($result) . " registros\n";
    if (!empty($result)) {
        echo "Primer registro:\n";
        echo "- ID: {$result[0]['id_preformato']}\n";
        echo "- Nombre: {$result[0]['nombre']}\n";
        echo "- Tipo formulario: " . ($result[0]['tipo_formulario'] ?? 'NULL') . "\n";
    }
    
} catch(Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
?>
