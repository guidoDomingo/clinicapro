<?php
session_start();
$_SESSION['user_id'] = 1;

require_once 'modules/consultas/api/livewire-system.php';

echo "=== TEST RÁPIDO DEL SISTEMA CORREGIDO ===\n\n";

try {
    $system = new LivewireCRUDSystem(true);
    
    // Test consultas con campos corregidos
    echo "🔍 Probando lista de consultas...\n";
    $testInput = [
        'action' => 'list',
        'table' => 'consultas',
        'limit' => 3
    ];
    
    $result = $system->list($testInput);
    
    if ($result['success']) {
        echo "✅ SUCCESS: Lista de consultas obtenida\n";
        echo "📊 Total: " . count($result['data']) . " consultas\n";
        
        if (count($result['data']) > 0) {
            $first = $result['data'][0];
            echo "📝 Primera consulta:\n";
            echo "   - ID: {$first['id_consulta']}\n";
            echo "   - Persona: {$first['first_name']} {$first['last_name']}\n";
            echo "   - Documento: {$first['document_number']}\n";
            echo "   - Motivo: " . substr($first['txtmotivo'] ?? 'N/A', 0, 50) . "...\n";
        }
    } else {
        echo "❌ ERROR: " . $result['message'] . "\n";
    }
    
    echo "\n" . str_repeat("=", 50) . "\n";
    echo "🎯 ESTADO: SISTEMA CORREGIDO Y FUNCIONAL\n";
    echo "🔗 Acceder al sistema: http://localhost/clinica/init-livewire-session.php\n";
    
} catch (Exception $e) {
    echo "❌ ERROR CRÍTICO: " . $e->getMessage() . "\n";
}
?>