<?php
session_start();
$_SESSION['user_id'] = 1;

$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['CONTENT_TYPE'] = 'application/json';

require_once 'model/conexion.php';
require_once 'modules/consultas/api/livewire-system.php';

echo "=== TEST DE ACTUALIZACIÓN DE CONSULTA ===\n\n";

try {
    $system = new LivewireCRUDSystem(true);
    
    // Primero, obtener una consulta existente
    echo "🔍 1. Obteniendo consulta existente...\n";
    $listResult = $system->list([
        'action' => 'list',
        'table' => 'consultas',
        'limit' => 1
    ]);
    
    if (!$listResult['success'] || empty($listResult['data'])) {
        echo "❌ No se pudieron obtener consultas para probar\n";
        exit;
    }
    
    $consulta = $listResult['data'][0];
    $consultaId = $consulta['id_consulta'];
    $personaId = $consulta['id_persona'];
    
    echo "✅ Consulta obtenida: ID=$consultaId, Persona ID=$personaId\n";
    echo "   Persona: {$consulta['first_name']} {$consulta['last_name']}\n\n";
    
    // Ahora probar UPDATE
    echo "🔄 2. Probando UPDATE con datos válidos...\n";
    
    $updateData = [
        'action' => 'update',
        'table' => 'consultas',
        'id' => $consultaId,
        'data' => [
            'id_consulta' => $consultaId,
            'id_persona' => $personaId,  // Asegurar que sea numérico
            'txtmotivo' => 'Consulta actualizada desde test - ' . date('Y-m-d H:i:s'),
            'motivoscomunes' => 'Test de actualización'
        ]
    ];
    
    echo "Datos de actualización:\n";
    echo "- ID Consulta: $consultaId\n";
    echo "- ID Persona: $personaId (tipo: " . gettype($personaId) . ")\n";
    echo "- Motivo: " . $updateData['data']['txtmotivo'] . "\n\n";
    
    $updateResult = $system->update($updateData);
    
    if ($updateResult['success']) {
        echo "✅ SUCCESS: Consulta actualizada exitosamente\n";
        echo "📊 Mensaje: " . $updateResult['message'] . "\n";
    } else {
        echo "❌ ERROR: " . $updateResult['message'] . "\n";
        if (isset($updateResult['debug'])) {
            echo "🐛 Debug: " . json_encode($updateResult['debug'], JSON_PRETTY_PRINT) . "\n";
        }
    }
    
    // Validar que la actualización funcionó
    echo "\n🔍 3. Verificando actualización...\n";
    $readResult = $system->read([
        'action' => 'read',
        'table' => 'consultas',
        'id' => $consultaId
    ]);
    
    if ($readResult['success']) {
        $updatedConsulta = $readResult['data'];
        echo "✅ Consulta después de actualizar:\n";
        echo "   - ID: {$updatedConsulta['id_consulta']}\n";
        echo "   - Persona ID: {$updatedConsulta['id_persona']}\n";
        echo "   - Motivo: {$updatedConsulta['txtmotivo']}\n";
        echo "   - Motivos Comunes: {$updatedConsulta['motivoscomunes']}\n";
    } else {
        echo "❌ Error al verificar: " . $readResult['message'] . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ EXCEPCIÓN: " . $e->getMessage() . "\n";
    echo "📍 Archivo: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "🔗 Ahora prueba en el navegador: http://localhost/clinica/init-livewire-session.php\n";
?>