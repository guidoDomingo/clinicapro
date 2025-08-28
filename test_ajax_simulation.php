<?php
// Test que simula exactamente la llamada AJAX del frontend
session_start();
$_SESSION['user_id'] = 1; // Simular usuario logueado

// Simular POST request
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['CONTENT_TYPE'] = 'application/json';

// Datos exactos que envía el frontend (basados en los logs del navegador)
$postData = [
    'action' => 'update',
    'table' => 'consultas',
    'id' => '161',
    'data' => [
        'id_persona' => '45',
        'txtmotivo' => 'Motivo actualizado desde test AJAX',
        'motivoscomunes' => 'Motivos comunes actualizados',
        'visionod' => 'Test Vision OD',
        'visionoi' => 'Test Vision OI',
        'tensionod' => 'Test Tension OD',
        'tensionoi' => 'Test Tension OI',
        'consulta_textarea' => 'Test consulta textarea actualizada',
        'receta_textarea' => 'Test receta textarea actualizada',
        'txtnota' => 'Test nota actualizada',
        'proximaconsulta' => '2025-09-15',
        'whatsapptxt' => 'Test whatsapp',
        'email' => 'test@email.com',
        'id_user' => '1',
        'id_reserva' => '0',
        'tipo_formulario' => 'general'
    ],
    'related' => [
        'anteojos' => [
            'esfera_od' => '+2.50',
            'cilindro_od' => '-0.75',
            'eje_od' => '90',
            'dnp_od' => '32',
            'esfera_oi' => '+2.25',
            'cilindro_oi' => '-0.50',
            'eje_oi' => '85',
            'dnp_oi' => '30',
            'add_od' => '+1.25',
            'add_oi' => '+1.25',
            'altura_od' => '18',
            'altura_oi' => '18',
            'dist_interpupilar' => '62',
            'notas' => 'Test anteojos actualizados desde AJAX',
            'nota_od' => 'Test nota OD',
            'nota_oi' => 'Test nota OI'
        ]
    ]
];

// Simular el JSON input
$jsonInput = json_encode($postData);
file_put_contents('php://input', $jsonInput);

echo "=== SIMULANDO LLAMADA AJAX FRONTEND ===\n";
echo "📤 Enviando datos:\n";
echo "   Action: {$postData['action']}\n";
echo "   Table: {$postData['table']}\n"; 
echo "   ID: {$postData['id']}\n";
echo "   Campos data: " . implode(', ', array_keys($postData['data'])) . "\n";
echo "   Datos relacionados: " . (isset($postData['related']) ? 'SI' : 'NO') . "\n";

echo "\n📨 JSON enviado:\n";
echo substr($jsonInput, 0, 200) . "...\n";

// Capturar output
echo "\n🔄 Ejecutando sistema...\n";
ob_start();

// Configurar environment como en AJAX
putenv('QUERY_STRING=debug=1');
$_GET['debug'] = '1';

// Simular input stream
$GLOBALS['HTTP_RAW_POST_DATA'] = $jsonInput;

try {
    // Incluir el sistema 
    require_once 'modules/consultas/api/livewire-system.php';
    
    $output = ob_get_contents();
    ob_end_clean();
    
    echo "✅ Sistema ejecutado\n";
    echo "📄 Respuesta del sistema:\n";
    echo $output . "\n";
    
    // Verificar si fue exitoso
    $response = json_decode($output, true);
    if ($response && isset($response['success'])) {
        if ($response['success']) {
            echo "✅ Respuesta exitosa: " . $response['message'] . "\n";
        } else {
            echo "❌ Error en respuesta: " . $response['message'] . "\n";
        }
    } else {
        echo "⚠️  Respuesta no es JSON válido\n";
    }
    
} catch (Exception $e) {
    ob_end_clean();
    echo "❌ Error: " . $e->getMessage() . "\n";
}

// Verificar logs
echo "\n📋 Revisando logs recientes...\n";
$logFiles = ['debug.log', 'application.log', 'consultas.log'];
foreach ($logFiles as $logFile) {
    $logPath = "logs/$logFile";
    if (file_exists($logPath)) {
        $lastLines = `tail -5 "$logPath" 2>/dev/null`;
        if ($lastLines) {
            echo "📝 Últimas líneas de $logFile:\n";
            echo $lastLines . "\n";
        }
    }
}

echo "\n🎯 TEST AJAX COMPLETADO\n";
?>