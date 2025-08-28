<?php
require_once 'model/conexion.php';

echo "=== VERIFICACIÓN DE TABLA PERSONAS ===\n\n";

try {
    $pdo = Conexion::conectar();
    
    // 1. Verificar estructura de rh_person
    echo "📋 ESTRUCTURA DE rh_person:\n";
    echo str_repeat("-", 40) . "\n";
    
    $stmt = $pdo->query("SELECT column_name, data_type FROM information_schema.columns WHERE table_name = 'rh_person' ORDER BY ordinal_position");
    $columns = $stmt->fetchAll();
    
    foreach($columns as $col) {
        echo "- " . $col['column_name'] . " (" . $col['data_type'] . ")\n";
    }
    
    // 2. Mostrar algunos datos de rh_person
    echo "\n📊 DATOS DE EJEMPLO de rh_person:\n";
    echo str_repeat("-", 40) . "\n";
    
    $stmt = $pdo->query("SELECT person_id, document_number, first_name, last_name FROM rh_person LIMIT 5");
    $persons = $stmt->fetchAll();
    
    foreach($persons as $person) {
        echo "ID: {$person['person_id']} - {$person['first_name']} {$person['last_name']} (Doc: {$person['document_number']})\n";
    }
    
    // 3. Verificar coincidencias con consultas
    echo "\n🔗 VERIFICANDO RELACIÓN CON CONSULTAS:\n";
    echo str_repeat("-", 40) . "\n";
    
    $stmt = $pdo->query("
        SELECT DISTINCT 
            c.id_persona, 
            p.first_name, 
            p.last_name,
            COUNT(c.id_consulta) as total_consultas
        FROM consultas c 
        LEFT JOIN rh_person p ON c.id_persona = p.person_id 
        WHERE p.person_id IS NOT NULL 
        GROUP BY c.id_persona, p.first_name, p.last_name
        ORDER BY total_consultas DESC
        LIMIT 10
    ");
    $matches = $stmt->fetchAll();
    
    if (count($matches) > 0) {
        echo "✅ COINCIDENCIAS ENCONTRADAS:\n";
        foreach($matches as $match) {
            echo "- ID: {$match['id_persona']} | {$match['first_name']} {$match['last_name']} | Consultas: {$match['total_consultas']}\n";
        }
        
        echo "\n💡 CONCLUSIÓN: rh_person ES la tabla de personas correcta\n";
        
    } else {
        echo "❌ No hay coincidencias entre consultas.id_persona y rh_person.person_id\n";
        
        // Verificar qué IDs de persona existen en consultas
        echo "\n🔍 IDs de persona en consultas que NO están en rh_person:\n";
        $stmt = $pdo->query("
            SELECT DISTINCT c.id_persona
            FROM consultas c 
            LEFT JOIN rh_person p ON c.id_persona = p.person_id 
            WHERE p.person_id IS NULL
            ORDER BY c.id_persona
            LIMIT 10
        ");
        $missing = $stmt->fetchAll();
        
        foreach($missing as $miss) {
            echo "- ID faltante: {$miss['id_persona']}\n";
        }
    }
    
    // 4. Estadísticas
    echo "\n📈 ESTADÍSTICAS:\n";
    echo str_repeat("-", 40) . "\n";
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM rh_person");
    $totalPersons = $stmt->fetch()['total'];
    
    $stmt = $pdo->query("SELECT COUNT(DISTINCT id_persona) as total FROM consultas");
    $totalPersonsInConsultas = $stmt->fetch()['total'];
    
    $stmt = $pdo->query("
        SELECT COUNT(DISTINCT c.id_persona) as matched
        FROM consultas c 
        INNER JOIN rh_person p ON c.id_persona = p.person_id
    ");
    $matchedPersons = $stmt->fetch()['matched'];
    
    echo "- Total personas en rh_person: $totalPersons\n";
    echo "- Total personas en consultas: $totalPersonsInConsultas\n";
    echo "- Personas que coinciden: $matchedPersons\n";
    echo "- Porcentaje de coincidencia: " . round(($matchedPersons / $totalPersonsInConsultas) * 100, 2) . "%\n";
    
    // 5. Recomendación
    echo "\n🎯 RECOMENDACIÓN:\n";
    echo str_repeat("=", 50) . "\n";
    
    if ($matchedPersons > 0) {
        echo "✅ USAR rh_person como tabla de personas\n";
        echo "   - Campos clave: person_id, first_name, last_name, document_number\n";
        echo "   - Relación: consultas.id_persona = rh_person.person_id\n";
    } else {
        echo "❌ CREAR tabla personas o usar datos de consulta directamente\n";
        echo "   - Los IDs en consultas no coinciden con rh_person\n";
    }
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}

echo "\n=== FIN DE VERIFICACIÓN ===\n";
?>