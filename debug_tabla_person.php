<?php
// Debug: Verificar estructura de tabla rh_person
try {
    $pdo = new PDO('pgsql:host=localhost;port=5432;dbname=clinica', 'postgres', 'admin');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== ESTRUCTURA TABLA rh_person ===\n";
    
    // Obtener todas las columnas de rh_person
    $sql = "SELECT column_name, data_type, is_nullable 
            FROM information_schema.columns 
            WHERE table_name = 'rh_person' 
            ORDER BY ordinal_position";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Columnas disponibles en rh_person:\n";
    foreach ($columns as $col) {
        echo "- " . $col['column_name'] . " (" . $col['data_type'] . ")\n";
    }
    
    echo "\n=== DATOS REALES DE LA PERSONA CON ID_USER = 9 ===\n";
    
    // Primero obtener person_id del system_user_id 9
    $sql1 = "SELECT person_id FROM person_system_user WHERE system_user_id = 9";
    $stmt1 = $pdo->prepare($sql1);
    $stmt1->execute();
    $person_relation = $stmt1->fetch(PDO::FETCH_ASSOC);
    
    if ($person_relation) {
        echo "Person ID relacionado con user_id 9: " . $person_relation['person_id'] . "\n";
        
        // Obtener todos los datos de esa persona
        $sql2 = "SELECT * FROM rh_person WHERE person_id = ?";
        $stmt2 = $pdo->prepare($sql2);
        $stmt2->execute([$person_relation['person_id']]);
        $person_data = $stmt2->fetch(PDO::FETCH_ASSOC);
        
        if ($person_data) {
            echo "\n✅ DATOS DE LA PERSONA:\n";
            foreach ($person_data as $campo => $valor) {
                echo "$campo: " . ($valor ?? 'NULL') . "\n";
            }
        } else {
            echo "❌ No se encontraron datos para person_id: " . $person_relation['person_id'] . "\n";
        }
        
    } else {
        echo "❌ No se encontró relación person_system_user para system_user_id = 9\n";
    }
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}
?>