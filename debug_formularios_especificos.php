<?php
/**
 * DEBUG ESPECÍFICO PARA FORMULARIOS ESTUDIOS E INFORME_IMAGEN
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

$testDataEstudios = [
    'id_persona' => 45,
    'tipo_formulario' => 'estudios',
    'txtmotivo' => 'Prueba debug estudios',
    'tipo_estudio' => 'OCT',
    'observaciones' => '<p><strong>Resultados de OCT de prueba debug</strong></p>',
    'fecha_realizacion' => '2025-08-24'
];

$testDataInforme = [
    'id_persona' => 45,
    'tipo_formulario' => 'informe_imagen',
    'txtmotivo' => 'Prueba debug informe',
    'equipoMedico-informe-imagen' => 'Oftalmoscopio Digital HD',
    'descripcion-od-textarea-informe-imagen' => '<p><strong>Descripción OD de prueba debug</strong></p>',
    'descripcion-oi-textarea-informe-imagen' => '<p><strong>Descripción OI de prueba debug</strong></p>'
];

echo "🐛 DEBUG ESPECÍFICO PARA FORMULARIOS PROBLEMATICOS\n";
echo "=================================================\n\n";

function debugFormulario($formType, $data) {
    echo "🔍 Debuggeando formulario: $formType\n";
    echo "-----------------------------------\n";
    
    // 1. Test directo con cURL para ver el error exacto
    $apiUrl = "http://localhost/clinica/modules/consultas/api/modern-api.php?action=create_consulta";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiUrl);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    
    $headers = substr($response, 0, $headerSize);
    $body = substr($response, $headerSize);
    
    curl_close($ch);
    
    echo "📡 HTTP Code: $httpCode\n";
    echo "📤 Datos enviados: " . json_encode($data) . "\n\n";
    
    if ($httpCode == 200) {
        echo "✅ Respuesta exitosa:\n";
        echo $body . "\n";
    } else {
        echo "❌ Error HTTP $httpCode:\n";
        echo "Headers:\n" . $headers . "\n";
        echo "Body:\n" . $body . "\n";
        
        // Buscar errores PHP específicos
        if (strpos($body, 'Fatal error') !== false || strpos($body, 'Warning') !== false) {
            echo "\n🚨 ERROR PHP DETECTADO:\n";
            preg_match_all('/<b>(.*?)<\/b>/', $body, $matches);
            foreach ($matches[1] as $error) {
                if (in_array($error, ['Fatal error', 'Warning', 'Notice'])) continue;
                echo "- $error\n";
            }
        }
    }
    
    echo "\n" . str_repeat("=", 60) . "\n\n";
}

// Debuggear ambos formularios
debugFormulario('estudios', $testDataEstudios);
debugFormulario('informe_imagen', $testDataInforme);

echo "🎯 DIAGNÓSTICO COMPLETO\n";
echo "Si hay errores PHP, necesitamos corregir el código.\n";
echo "Si hay errores de mapeo, necesitamos ajustar el DatabaseMapper.\n";
?>