<?php
// Debug simple sin usar la clase LivewireCRUDSystem
try {
    // Configuración de base de datos
    $host = 'localhost';
    $dbname = 'clinica';  // ✅ Nombre correcto de la BD
    $username = 'postgres';
    $password = 'admin';
    
    $pdo = new PDO("pgsql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Consultar pacientes
    $stmt = $pdo->prepare("SELECT * FROM rh_person WHERE first_name ILIKE '%guido%' OR last_name ILIKE '%guido%' LIMIT 3");
    $stmt->execute();
    $patients = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "=== PACIENTES ENCONTRADOS ===\n";
    echo "Total: " . count($patients) . "\n\n";
    
    if (count($patients) > 0) {
        echo "=== CAMPOS DISPONIBLES ===\n";
        $firstPatient = $patients[0];
        foreach ($firstPatient as $key => $value) {
            echo "$key: " . ($value ?? 'NULL') . "\n";
        }
        
        echo "\n=== PACIENTES DETALLADOS ===\n";
        foreach ($patients as $i => $patient) {
            echo "Paciente " . ($i + 1) . ":\n";
            echo "  person_id: " . ($patient['person_id'] ?? 'N/A') . "\n";
            echo "  first_name: " . ($patient['first_name'] ?? 'N/A') . "\n";
            echo "  last_name: " . ($patient['last_name'] ?? 'N/A') . "\n";
            echo "  document_number: " . ($patient['document_number'] ?? 'N/A') . "\n";
            echo "  phone_number: " . ($patient['phone_number'] ?? 'N/A') . "\n";
            echo "\n";
        }
    } else {
        echo "No se encontraron pacientes con 'guido'\n";
        
        // Intentar obtener cualquier paciente para ver la estructura
        $stmt = $pdo->prepare("SELECT * FROM rh_person LIMIT 3");
        $stmt->execute();
        $patients = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($patients) > 0) {
            echo "\n=== CAMPOS DE LA TABLA (primeros 3 pacientes) ===\n";
            foreach ($patients as $i => $patient) {
                echo "Paciente " . ($i + 1) . ":\n";
                foreach ($patient as $key => $value) {
                    echo "  $key: " . ($value ?? 'NULL') . "\n";
                }
                echo "\n";
            }
        }
    }
    
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
}
?>