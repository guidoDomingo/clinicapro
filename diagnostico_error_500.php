<?php
/**
 * 🔍 DIAGNÓSTICO DE ERROR 500 EN API MODERNA
 * Vamos a identificar exactamente qué está causando el error
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "🔍 DIAGNÓSTICO: Error 500 en modern-api.php\n";
echo "============================================\n\n";

// Test de la API moderna
echo "📡 Probando API moderna directamente...\n";

// Simular los datos que envía el frontend
$testData = [
    'id_persona' => 60,
    'tipo_formulario' => 'anteojos',
    'id_consulta' => '63',
    'proximaconsulta' => '2025-08-26'
];

echo "📤 Datos de prueba:\n";
print_r($testData);

// Test directo del DatabaseMapper
require_once 'modules/consultas/core/DatabaseMapper.php';
$mapper = new DatabaseMapper();

echo "\n🧪 Test directo con DatabaseMapper...\n";
try {
    $result = $mapper->saveConsulta($testData, 'anteojos', 63);
    echo "✅ DatabaseMapper funciona:\n";
    print_r($result);
} catch (Exception $e) {
    echo "❌ Error en DatabaseMapper: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

// Verificar si la consulta ID 63 existe
echo "\n🔍 Verificando si la consulta ID 63 existe...\n";
try {
    $consulta = $mapper->getConsulta(63);
    if ($consulta['success']) {
        echo "✅ Consulta ID 63 encontrada:\n";
        echo "   Tipo: " . ($consulta['data']['main']['tipo_formulario'] ?? 'N/A') . "\n";
        echo "   Persona: " . ($consulta['data']['main']['id_persona'] ?? 'N/A') . "\n";
    } else {
        echo "❌ Consulta ID 63 NO encontrada: " . $consulta['message'] . "\n";
    }
} catch (Exception $e) {
    echo "❌ Error verificando consulta: " . $e->getMessage() . "\n";
}

// Test de la API moderna con cURL
echo "\n🌐 Test de la API moderna vía HTTP...\n";

$url = 'http://localhost/clinica/modules/consultas/api/modern-api.php?action=update_consulta&id=63';
$data = json_encode($testData);

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Content-Length: ' . strlen($data)
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "📊 Respuesta HTTP:\n";
echo "   Código: $httpCode\n";
echo "   Error cURL: " . ($error ?: 'Ninguno') . "\n";
echo "   Respuesta: $response\n";

if ($httpCode == 500) {
    echo "\n🚨 ERROR 500 CONFIRMADO\n";
    echo "Revisando logs de PHP...\n";
    
    // Intentar leer el log de errores de PHP
    $logFiles = [
        '/laragon/logs/apache_error.log',
        '/laragon/logs/php_errors.log',
        ini_get('error_log')
    ];
    
    foreach ($logFiles as $logFile) {
        if ($logFile && file_exists($logFile)) {
            echo "\n📋 Últimas líneas de $logFile:\n";
            $lines = file($logFile);
            $lastLines = array_slice($lines, -10);
            foreach ($lastLines as $line) {
                echo "   " . trim($line) . "\n";
            }
            break;
        }
    }
}

echo "\n✅ Diagnóstico completado\n";
?>