<?php
/**
 * Probar creación de nueva consulta para verificar que se guarde el doctor automáticamente
 */

// Simular una sesión con un doctor logueado
session_start();
$_SESSION['user_id'] = 9; // Usuario angel isnardi

// Incluir la clase
require_once 'modules/consultas/api/livewire-system.php';

echo "=== PROBAR CREACIÓN DE CONSULTA CON DOCTOR AUTOMÁTICO ===\n";

try {
    $api = new LivewireConsultaSystem(true);
    
    // Datos de prueba para nueva consulta
    $testData = [
        'action' => 'create',
        'table' => 'consultas',
        'data' => [
            'id_persona' => 20, // Juan Pérez que vimos en la lista
            'txtmotivo' => 'Consulta de prueba para verificar doctor automático',
            'consulta_textarea' => 'Verificando que se guarde el id_user automáticamente',
            'tipo_formulario' => 'general'
            // NO incluimos id_user - debe ponerse automáticamente
        ]
    ];
    
    // Simular la petición POST
    $_POST = json_encode($testData);
    
    $result = $api->handle();
    
    if ($result['status'] === 'success') {
        $consultaId = $result['data']['id_consulta'];
        echo "✅ CONSULTA CREADA EXITOSAMENTE!\n";
        echo "ID: $consultaId\n";
        
        // Verificar que se guardó el doctor
        $verifyData = [
            'action' => 'get',
            'table' => 'consultas',
            'id' => $consultaId
        ];
        
        $_POST = json_encode($verifyData);
        $verification = $api->handle();
        
        if ($verification['status'] === 'success') {
            $consulta = $verification['data'];
            echo "ID User guardado: " . ($consulta['id_user'] ?? 'NULL') . "\n";
            echo "Doctor: " . ($consulta['doctor_first_name'] ?? 'N/A') . " " . ($consulta['doctor_last_name'] ?? 'N/A') . "\n";
            echo "Doctor Email: " . ($consulta['doctor_email'] ?? 'N/A') . "\n";
        }
    } else {
        echo "❌ ERROR: " . $result['message'] . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ EXCEPCIÓN: " . $e->getMessage() . "\n";
}
?>