<?php
// Verificar qué pacientes existen en la base de datos
require_once 'modules/consultas/api/livewire-system.php';

echo "=== VERIFICACIÓN DE PACIENTES EN BD ===" . PHP_EOL . PHP_EOL;

try {
    // Obtener conexión
    $pdo = getConnection();
    
    // Contar total de pacientes
    $countQuery = "SELECT COUNT(*) as total FROM rh_person";
    $stmt = $pdo->query($countQuery);
    $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    echo "📊 Total de pacientes en BD: " . $total . PHP_EOL . PHP_EOL;
    
    if ($total > 0) {
        // Mostrar algunos ejemplos
        $sampleQuery = "SELECT person_id, first_name, last_name, document_number 
                       FROM rh_person 
                       ORDER BY person_id 
                       LIMIT 10";
        $stmt = $pdo->query($sampleQuery);
        $patients = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "👥 Ejemplos de pacientes:" . PHP_EOL;
        foreach ($patients as $patient) {
            echo "- ID: {$patient['person_id']}, " . 
                 "Nombre: {$patient['first_name']} {$patient['last_name']}, " .
                 "Doc: {$patient['document_number']}" . PHP_EOL;
        }
        echo PHP_EOL;
        
        // Buscar específicamente "visconte"
        echo "🔍 Buscando 'visconte'..." . PHP_EOL;
        $searchQuery = "SELECT person_id, first_name, last_name, document_number 
                       FROM rh_person 
                       WHERE LOWER(first_name) LIKE LOWER('%visconte%') 
                          OR LOWER(last_name) LIKE LOWER('%visconte%')";
        $stmt = $pdo->query($searchQuery);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($results) > 0) {
            echo "✅ Encontrados " . count($results) . " resultados:" . PHP_EOL;
            foreach ($results as $patient) {
                echo "- {$patient['first_name']} {$patient['last_name']} ({$patient['document_number']})" . PHP_EOL;
            }
        } else {
            echo "❌ No se encontraron pacientes con 'visconte'" . PHP_EOL;
        }
        
    } else {
        echo "⚠️ La tabla rh_person está vacía" . PHP_EOL;
        echo "Necesitas agregar algunos pacientes de prueba." . PHP_EOL;
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . PHP_EOL;
}

function getConnection() {
    $host = 'localhost';
    $dbname = 'clinica_db';
    $username = 'postgres';
    $password = 'root';
    
    try {
        $pdo = new PDO("pgsql:host=$host;dbname=$dbname", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (PDOException $e) {
        throw new Exception("Error de conexión: " . $e->getMessage());
    }
}
?>