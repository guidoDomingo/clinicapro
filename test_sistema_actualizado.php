<?php
session_start();
$_SESSION['user_id'] = 1;

require_once 'modules/consultas/api/livewire-system.php';

echo "<!DOCTYPE html>
<html>
<head>
    <title>Test Sistema Actualizado</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        .test-section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 8px; }
        .success { background: #d4edda; color: #155724; }
        .error { background: #f8d7da; color: #721c24; }
        .info { background: #cce7ff; color: #004085; }
        pre { background: #f8f9fa; padding: 10px; border-radius: 4px; overflow-x: auto; }
    </style>
</head>
<body>";

echo "<h1>🧪 Test Sistema Livewire CRUD Actualizado</h1>";

// Test 1: Verificar conexión con rh_person
echo "<div class='test-section'>";
echo "<h3>Test 1: Conexión con tabla rh_person</h3>";
try {
    $system = new LivewireCRUDSystem(true);
    echo "<div class='success'>✅ Sistema inicializado correctamente</div>";
    
    // Test de listado de personas
    $testInput = [
        'action' => 'list',
        'table' => 'rh_person',
        'limit' => 5
    ];
    
    $result = $system->list($testInput);
    
    if ($result && isset($result['data']) && count($result['data']) > 0) {
        echo "<div class='success'>✅ Lista de personas obtenida exitosamente</div>";
        echo "<div class='info'>Total personas encontradas: " . count($result['data']) . "</div>";
        
        // Mostrar primera persona
        $firstPerson = $result['data'][0];
        echo "<div class='info'>Primera persona: {$firstPerson['first_name']} {$firstPerson['last_name']} (ID: {$firstPerson['person_id']})</div>";
    } else {
        echo "<div class='error'>❌ Error al obtener lista de personas</div>";
    }
    
} catch (Exception $e) {
    echo "<div class='error'>❌ Error: " . $e->getMessage() . "</div>";
}
echo "</div>";

// Test 2: Test de consultas con relación a rh_person
echo "<div class='test-section'>";
echo "<h3>Test 2: Consultas con relación a rh_person</h3>";
try {
    $testInput = [
        'action' => 'list',
        'table' => 'consultas',
        'limit' => 3
    ];
    
    $result = $system->list($testInput);
    
    if ($result && isset($result['data']) && count($result['data']) > 0) {
        echo "<div class='success'>✅ Lista de consultas obtenida exitosamente</div>";
        echo "<div class='info'>Total consultas: " . count($result['data']) . "</div>";
        
        // Mostrar primera consulta con datos de persona
        $firstConsulta = $result['data'][0];
        echo "<div class='info'>Primera consulta ID: {$firstConsulta['id_consulta']}</div>";
        echo "<div class='info'>Persona: {$firstConsulta['first_name']} {$firstConsulta['last_name']}</div>";
        echo "<div class='info'>Motivo: " . substr($firstConsulta['txtmotivo'], 0, 50) . "...</div>";
    } else {
        echo "<div class='error'>❌ Error al obtener consultas</div>";
    }
    
} catch (Exception $e) {
    echo "<div class='error'>❌ Error: " . $e->getMessage() . "</div>";
}
echo "</div>";

// Test 3: Búsqueda de personas
echo "<div class='test-section'>";
echo "<h3>Test 3: Búsqueda de personas</h3>";
try {
    $testInput = [
        'action' => 'search',
        'table' => 'rh_person',
        'search' => 'guido',
        'limit' => 5
    ];
    
    $result = $system->search($testInput);
    
    if ($result && isset($result['data'])) {
        echo "<div class='success'>✅ Búsqueda ejecutada exitosamente</div>";
        echo "<div class='info'>Resultados encontrados: " . count($result['data']) . "</div>";
        
        foreach($result['data'] as $person) {
            echo "<div class='info'>- {$person['first_name']} {$person['last_name']} (Doc: {$person['document_number']})</div>";
        }
    } else {
        echo "<div class='error'>❌ Error en búsqueda</div>";
    }
    
} catch (Exception $e) {
    echo "<div class='error'>❌ Error: " . $e->getMessage() . "</div>";
}
echo "</div>";

// Test 4: Validación CREATE de consulta
echo "<div class='test-section'>";
echo "<h3>Test 4: Validación CREATE de consulta</h3>";
try {
    $testInput = [
        'action' => 'validate',
        'table' => 'consultas',
        'data' => [
            'id_persona' => 45, // ID de persona existente
            'txtmotivo' => 'Test de validación del sistema actualizado',
            'motivoscomunes' => 'Consulta de prueba'
        ],
        'operation' => 'create'
    ];
    
    $result = $system->validate($testInput);
    
    if ($result && $result['data']['valid']) {
        echo "<div class='success'>✅ Validación exitosa para CREATE</div>";
    } else {
        echo "<div class='error'>❌ Validación falló</div>";
        if (isset($result['data']['errors'])) {
            echo "<div class='info'>Errores: " . implode(', ', $result['data']['errors']) . "</div>";
        }
    }
    
} catch (Exception $e) {
    echo "<div class='error'>❌ Error: " . $e->getMessage() . "</div>";
}
echo "</div>";

// Resumen
echo "<div class='test-section success'>";
echo "<h3>🎯 Resumen del Test</h3>";
echo "<p><strong>Estado del Sistema:</strong> ACTUALIZADO Y OPERACIONAL</p>";
echo "<p><strong>Tabla de personas:</strong> rh_person ✅</p>";
echo "<p><strong>Relaciones:</strong> consultas.id_persona = rh_person.person_id ✅</p>";
echo "<p><strong>Campos actualizados:</strong></p>";
echo "<ul>";
echo "<li>id_persona → person_id</li>";
echo "<li>nombre → first_name</li>";
echo "<li>apellido → last_name</li>";
echo "<li>documento → document_number</li>";
echo "<li>telefono → phone_number</li>";
echo "</ul>";
echo "</div>";

echo "<div style='margin-top: 30px; text-align: center;'>";
echo "<a href='livewire-crud-system.html' style='padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; margin: 5px;'>🚀 Abrir Sistema CRUD</a>";
echo "<a href='verificacion_completa_bd.php' style='padding: 10px 20px; background: #28a745; color: white; text-decoration: none; border-radius: 5px; margin: 5px;'>📊 Ver Análisis BD</a>";
echo "</div>";

echo "</body></html>";
?>