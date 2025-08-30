<?php
// Test rápido de la API get_archivos_consulta
echo "🧪 Testing get_archivos_consulta API\n";

// Incluir el sistema
require_once __DIR__ . '/modules/consultas/api/livewire-system.php';

try {
    $api = new LivewireSystem();
    
    // Simular una consulta con ID conocido (179 según los logs)
    $_GET['action'] = 'get_archivos_consulta';
    $_POST = [
        'action' => 'get_archivos_consulta',
        'id_consulta' => '179'
    ];
    
    $result = $api->handleRequest();
    
    echo "✅ Resultado:\n";
    echo json_encode($result, JSON_PRETTY_PRINT) . "\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>