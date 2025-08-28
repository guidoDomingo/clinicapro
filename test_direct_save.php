<?php
// Test directo del endpoint de guardado
session_start();
$_SESSION['user_id'] = 1; // Simular usuario logueado

// Headers para la respuesta
header('Content-Type: text/html; charset=utf-8');

echo "<!DOCTYPE html>
<html>
<head>
    <title>Test Endpoint Directo</title>
    <style>
        body { font-family: Arial; padding: 20px; background: #f5f5f5; }
        .result { padding: 15px; margin: 10px 0; border-radius: 5px; }
        .success { background: #d4edda; color: #155724; }
        .error { background: #f8d7da; color: #721c24; }
        .info { background: #cce7ff; color: #004085; }
        pre { background: #1a1a1a; color: #0f0; padding: 15px; border-radius: 5px; }
    </style>
</head>
<body>";

echo "<h1>🧪 Test Directo del Endpoint</h1>";

try {
    // Verificar conexión primero
    require_once 'model/conexion.php';
    $pdo = Conexion::conectar();
    
    if (!$pdo) {
        throw new Exception("No se pudo conectar a la base de datos");
    }
    
    echo "<div class='result success'>✅ Conexión a BD exitosa</div>";
    
    // Importar el handler directamente
    require_once 'modules/consultas/api/livewire-crud.php';
    
    // Crear instancia del handler
    $handler = new LivewireCRUDHandler();
    
    echo "<div class='result success'>✅ Handler creado exitosamente</div>";
    
    // Datos de prueba
    $testState = [
        'id_persona' => '45',
        'txtmotivo' => 'Test directo desde PHP',
        'od_esf' => '-1.25',
        'od_cil' => '-0.50',
        'od_eje' => '90',
        'od_dnp' => '32',
        'oi_esf' => '-1.50',
        'oi_cil' => '-0.75',
        'oi_eje' => '85',
        'oi_dnp' => '31',
        'dist_interpupilar' => '63',
        'consulta_textarea' => 'Consulta de prueba',
        'receta_textarea' => 'Receta de prueba',
        'txtnota' => 'Notas de prueba'
    ];
    
    echo "<div class='result info'>📝 Probando método save...</div>";
    
    // Probar el método save directamente
    $result = $handler->save($testState, 'anteojos');
    
    echo "<div class='result success'>✅ Método save ejecutado</div>";
    echo "<pre>" . json_encode($result, JSON_PRETTY_PRINT) . "</pre>";
    
} catch (Exception $e) {
    echo "<div class='result error'>❌ Error: " . $e->getMessage() . "</div>";
    echo "<div class='result error'>📍 Archivo: " . $e->getFile() . "</div>";
    echo "<div class='result error'>📍 Línea: " . $e->getLine() . "</div>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "</body></html>";
?>