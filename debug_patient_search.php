<?php
// Debug para búsqueda de pacientes
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Debug: Búsqueda de Pacientes</h2>";

// Simular la misma request que hace el frontend
$_POST = [
    'action' => 'search',
    'table' => 'rh_person',
    'search' => [
        'first_name' => 'alejandro'
    ]
];

echo "<h3>POST Data:</h3>";
echo "<pre>" . print_r($_POST, true) . "</pre>";

echo "<h3>Calling API...</h3>";

// Incluir la API
try {
    ob_start();
    include 'modules/consultas/api/livewire-system.php';
    $output = ob_get_clean();
    
    echo "<h3>API Output:</h3>";
    echo "<pre>" . htmlspecialchars($output) . "</pre>";
    
    // Verificar si es JSON válido
    $json = json_decode($output, true);
    if ($json === null) {
        echo "<h3>❌ NO ES JSON VÁLIDO</h3>";
        echo "Error: " . json_last_error_msg();
    } else {
        echo "<h3>✅ JSON VÁLIDO</h3>";
        echo "<pre>" . print_r($json, true) . "</pre>";
    }
    
} catch (Exception $e) {
    echo "<h3>❌ EXCEPCIÓN:</h3>";
    echo "<pre>" . $e->getMessage() . "</pre>";
}
?>