<?php
// Debug simple para ver los campos de la tabla rh_person
require_once 'modules/consultas/api/livewire-system.php';

// Crear instancia del sistema
$system = new LivewireCRUDSystem(true);

// Hacer una consulta directa a la base de datos para ver los campos reales
try {
    $stmt = $system->db->prepare("SELECT * FROM rh_person WHERE first_name ILIKE '%guido%' OR last_name ILIKE '%guido%' LIMIT 3");
    $stmt->execute();
    $patients = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "=== PACIENTES ENCONTRADOS ===\n";
    echo "Total: " . count($patients) . "\n\n";
    
    if (count($patients) > 0) {
        echo "=== CAMPOS DEL PRIMER PACIENTE ===\n";
        $firstPatient = $patients[0];
        foreach ($firstPatient as $key => $value) {
            echo "$key: " . ($value ?? 'NULL') . "\n";
        }
        
        echo "\n=== ESTRUCTURA DE TODOS LOS PACIENTES ===\n";
        foreach ($patients as $i => $patient) {
            echo "Paciente " . ($i + 1) . ":\n";
            echo "  ID: " . ($patient['person_id'] ?? 'N/A') . "\n";
            echo "  Nombres: " . ($patient['first_name'] ?? 'N/A') . "\n";
            echo "  Apellidos: " . ($patient['last_name'] ?? 'N/A') . "\n";
            echo "  CI: " . ($patient['document_number'] ?? 'N/A') . "\n";
            echo "  Teléfono: " . ($patient['phone_number'] ?? 'N/A') . "\n";
            echo "\n";
        }
    }
    
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
}
?>