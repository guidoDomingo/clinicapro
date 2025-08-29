<?php
// Script para probar la inserción con texto largo
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
    
    echo "=== PRUEBA DE INSERCIÓN CON TEXTO LARGO ===\n\n";
    
    // Crear texto muy largo para probar
    $textoLargo = str_repeat("Este es un texto de prueba muy largo para verificar que los campos pueden almacenar más de 255 caracteres. ", 10);
    
    echo "Longitud del texto de prueba: " . strlen($textoLargo) . " caracteres\n\n";
    
    // Datos de prueba con texto largo
    $testData = [
        'id_persona' => 60, // leonardo castillo
        'motivoscomunes' => '5', // Un ID de motivo común
        'txtmotivo' => $textoLargo,
        'visionod' => '20/20',
        'visionoi' => '20/30', 
        'tensionod' => '12',
        'tensionoi' => '14',
        'consulta_textarea' => 'Consulta de prueba con texto largo funcionando correctamente',
        'receta_textarea' => 'Receta de prueba funcionando',
        'tipo_formulario' => 'general'
    ];
    
    echo "Creando consulta con texto largo...\n";
    
    // Crear consulta
    $result = $system->create([
        'table' => 'consultas',
        'data' => $testData
    ]);
    
    if (isset($result['data']) && isset($result['data']['id_consulta'])) {
        $consultaId = $result['data']['id_consulta'];
        echo "✅ Consulta creada exitosamente con ID: $consultaId\n";
        
        // Verificar que se guardó correctamente
        echo "\nVerificando datos guardados...\n";
        $readResult = $system->read([
            'table' => 'consultas', 
            'id' => $consultaId
        ]);
        
        if (isset($readResult['data'])) {
            $savedData = $readResult['data'];
            echo "✅ Motivo guardado (" . strlen($savedData['txtmotivo']) . " caracteres): " . substr($savedData['txtmotivo'], 0, 100) . "...\n";
            echo "✅ Motivos comunes: " . ($savedData['motivoscomunes'] ?? 'NULL') . "\n";
            echo "✅ Consulta: " . ($savedData['consulta_textarea'] ?? 'NULL') . "\n";
        }
        
    } else {
        echo "❌ Error creando consulta: " . ($result['message'] ?? 'Error desconocido') . "\n";
        if (isset($result['debug'])) {
            echo "Debug: " . print_r($result['debug'], true) . "\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . "\n";
    echo "Línea: " . $e->getLine() . "\n";
}
?>