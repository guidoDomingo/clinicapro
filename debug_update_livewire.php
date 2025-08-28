<?php
// Test debug específico para actualización de consulta en el sistema Livewire
require_once 'model/conexion.php';

// Simular el sistema Livewire
session_start();
$_SESSION['user_id'] = 1; // Simular usuario logueado

// Importar la clase del sistema
require_once 'modules/consultas/api/livewire-system.php';

try {
    echo "=== DEBUG ACTUALIZACIÓN SISTEMA LIVEWIRE ===\n";
    
    // 1. Crear instancia del sistema
    $sistema = new LivewireCRUDSystem(true); // debug = true
    
    // 2. Datos de prueba para actualización
    $datosActualizacion = [
        'table' => 'consultas',
        'id' => '161',
        'data' => [
            'id_persona' => '45',
            'txtmotivo' => 'Debug motivo actualizado',
            'motivoscomunes' => 'Debug motivos comunes', 
            'visionod' => 'Debug vision OD actualizada',
            'visionoi' => 'Debug vision OI actualizada',
            'consulta_textarea' => 'Debug consulta actualizada por sistema',
            'receta_textarea' => 'Debug receta actualizada por sistema',
            'txtnota' => 'Debug nota actualizada'
        ],
        'related' => [
            'anteojos' => [
                'esfera_od' => '+2.00',
                'cilindro_od' => '-0.25',
                'esfera_oi' => '+1.75',
                'cilindro_oi' => '-0.50',
                'notas' => 'Debug anteojos actualizados'
            ]
        ]
    ];
    
    echo "\n1️⃣ Datos preparados para actualización:\n";
    echo "📋 Tabla: {$datosActualizacion['table']}\n";
    echo "🆔 ID: {$datosActualizacion['id']}\n";
    echo "📝 Campos principales: " . implode(', ', array_keys($datosActualizacion['data'])) . "\n";
    echo "🔗 Datos relacionados: " . (isset($datosActualizacion['related']) ? 'SI (anteojos)' : 'NO') . "\n";
    
    // 3. Leer datos antes de actualizar
    echo "\n2️⃣ Leyendo datos actuales...\n";
    $datosAntes = $sistema->read(['table' => 'consultas', 'id' => 161, 'with' => ['persona']]);
    
    if ($datosAntes['success']) {
        echo "✅ Datos actuales leídos exitosamente\n";
        echo "   txtmotivo actual: " . ($datosAntes['data']['txtmotivo'] ?: 'NULL') . "\n";
        echo "   consulta_textarea actual: " . (substr($datosAntes['data']['consulta_textarea'] ?: 'NULL', 0, 30)) . "\n";
    } else {
        echo "❌ Error leyendo datos actuales: " . $datosAntes['message'] . "\n";
    }
    
    // 4. Ejecutar actualización
    echo "\n3️⃣ Ejecutando actualización...\n";
    
    $resultado = $sistema->update($datosActualizacion);
    
    if ($resultado['success']) {
        echo "✅ Actualización ejecutada exitosamente\n";
        echo "📄 Mensaje: " . $resultado['message'] . "\n";
        
        if (isset($resultado['debug'])) {
            echo "🐛 Debug info:\n";
            print_r($resultado['debug']);
        }
    } else {
        echo "❌ Error en actualización: " . $resultado['message'] . "\n";
        
        if (isset($resultado['debug'])) {
            echo "🐛 Debug error:\n";
            print_r($resultado['debug']);
        }
    }
    
    // 5. Verificar datos después de actualización
    echo "\n4️⃣ Verificando datos después de actualización...\n";
    
    $datosDespues = $sistema->read(['table' => 'consultas', 'id' => 161, 'with' => ['persona']]);
    
    if ($datosDespues['success']) {
        echo "✅ Datos después de actualización:\n";
        echo "   txtmotivo después: " . ($datosDespues['data']['txtmotivo'] ?: 'NULL') . "\n";
        echo "   consulta_textarea después: " . (substr($datosDespues['data']['consulta_textarea'] ?: 'NULL', 0, 30)) . "\n";
        
        // Comparar cambios
        $campos = ['txtmotivo', 'consulta_textarea', 'receta_textarea', 'visionod', 'visionoi'];
        foreach ($campos as $campo) {
            $antes = $datosAntes['data'][$campo] ?? 'NULL';
            $despues = $datosDespues['data'][$campo] ?? 'NULL';
            $cambio = $antes !== $despues ? ' ← CAMBIÓ' : ' (sin cambios)';
            echo "   {$campo}: {$antes} → {$despues}{$cambio}\n";
        }
    } else {
        echo "❌ Error leyendo datos después: " . $datosDespues['message'] . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error general: " . $e->getMessage() . "\n";
    echo "📍 Archivo: " . $e->getFile() . "\n";
    echo "📍 Línea: " . $e->getLine() . "\n";
}
?>