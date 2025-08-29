<?php
// Script para probar la búsqueda de pacientes
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Simular una sesión válida
session_start();
$_SESSION['authenticated'] = true;
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'admin';

// Incluir el sistema
require_once 'modules/consultas/api/livewire-system.php';

try {
    $system = new LivewireCRUDSystem();
    
    echo "=== PRUEBA DE BÚSQUEDA DE PACIENTES ===\n\n";
    
    // Probar búsqueda con diferentes términos
    $searchTerms = ['visconte', 'juan', 'maria', 'test'];
    
    foreach ($searchTerms as $term) {
        echo "Buscando: '$term'\n";
        echo str_repeat('-', 30) . "\n";
        
        $result = $system->search([
            'table' => 'rh_person',
            'search' => $term,
            'limit' => 5
        ]);
        
        echo "Estado: " . ($result ? 'OK' : 'ERROR') . "\n";
        echo "Datos encontrados: " . (isset($result['data']) ? count($result['data']) : '0') . "\n";
        
        if (isset($result['data']) && count($result['data']) > 0) {
            echo "Primeros resultados:\n";
            foreach (array_slice($result['data'], 0, 3) as $i => $patient) {
                echo "  " . ($i+1) . ". ";
                echo ($patient['first_name'] ?? 'N/A') . ' ' . ($patient['last_name'] ?? 'N/A');
                echo " (Doc: " . ($patient['document_number'] ?? 'N/A') . ")";
                echo " (ID: " . ($patient['person_id'] ?? 'N/A') . ")\n";
            }
        }
        
        if (isset($result['message'])) {
            echo "Mensaje: " . $result['message'] . "\n";
        }
        
        echo "\n";
    }
    
    // Verificar estructura de tabla
    echo "=== VERIFICACIÓN DE TABLA ===\n";
    $result = $system->list([
        'table' => 'rh_person',
        'limit' => 3
    ]);
    
    if (isset($result['data']) && count($result['data']) > 0) {
        echo "Estructura del primer registro:\n";
        $first = $result['data'][0];
        foreach ($first as $key => $value) {
            echo "  $key: " . (is_null($value) ? 'NULL' : $value) . "\n";
        }
    }
    
    echo "\nTotal de registros en rh_person: " . ($result['meta']['total'] ?? '?') . "\n";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . "\n";
    echo "Línea: " . $e->getLine() . "\n";
}
?>