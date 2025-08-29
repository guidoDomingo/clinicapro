<?php
// Script para probar el guardado de consultas
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
    
    echo "=== PRUEBA DE CREACIÓN DE CONSULTA ===\n\n";
    
    // Datos de prueba
    $testData = [
        'id_persona' => 45,
        'txtmotivo' => 'Prueba motivo de consulta',
        'visionod' => '20/20',
        'visionoi' => '20/30',
        'tensionod' => '12',
        'tensionoi' => '14',
        'consulta_textarea' => 'Texto de la consulta detallada',
        'receta_textarea' => 'Receta médica detallada',
        'tipo_formulario' => 'general'
    ];
    
    echo "Datos a insertar:\n";
    print_r($testData);
    echo "\n";
    
    // Crear consulta
    $result = $system->create([
        'table' => 'consultas',
        'data' => $testData
    ]);
    
    echo "Resultado de creación:\n";
    print_r($result);
    echo "\n";
    
    if ($result['success']) {
        $consultaId = $result['data']['id'];
        echo "Consulta creada con ID: $consultaId\n\n";
        
        // Leer la consulta recién creada
        echo "=== VERIFICACIÓN DE DATOS GUARDADOS ===\n";
        $readResult = $system->read([
            'table' => 'consultas',
            'id' => $consultaId
        ]);
        
        if ($readResult['success']) {
            echo "Datos guardados en BD:\n";
            $savedData = $readResult['data'];
            
            foreach ($testData as $key => $value) {
                $savedValue = $savedData[$key] ?? 'NULL';
                $status = ($savedValue == $value) ? '✅' : '❌';
                echo "  $key: '$value' → '$savedValue' $status\n";
            }
        } else {
            echo "Error leyendo la consulta: " . $readResult['message'] . "\n";
        }
    } else {
        echo "Error creando consulta: " . $result['message'] . "\n";
    }
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . "\n";
    echo "Línea: " . $e->getLine() . "\n";
}
?>