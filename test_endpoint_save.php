<?php
session_start();

// Simular usuario logueado
$_SESSION['user_id'] = 1;

echo "<h1>Test de Conexión API</h1>";

try {
    // Test 1: Verificar conexión a BD
    require_once 'model/conexion.php';
    $pdo = Conexion::conectar();
    
    if ($pdo) {
        echo "✅ Conexión a BD exitosa<br>";
        
        // Test 2: Verificar tabla consultas
        $stmt = $pdo->query("SELECT COUNT(*) FROM consultas");
        $count = $stmt->fetchColumn();
        echo "✅ Tabla consultas accesible - $count registros<br>";
        
        // Test 3: Verificar tabla consulta_anteojos  
        $stmt = $pdo->query("SELECT COUNT(*) FROM consulta_anteojos");
        $count = $stmt->fetchColumn();
        echo "✅ Tabla consulta_anteojos accesible - $count registros<br>";
        
    } else {
        echo "❌ Error de conexión a BD<br>";
    }
    
    // Test 4: Probar endpoint con datos mínimos
    echo "<br><h2>Test del endpoint</h2>";
    
    $testData = [
        'action' => 'save',
        'formType' => 'anteojos',
        'consultaId' => null,
        'state' => [
            'id_persona' => '45',
            'txtmotivo' => 'Test desde script PHP',
            'od_esf' => '-1.00',
            'oi_esf' => '-1.00'
        ]
    ];
    
    // Simular llamada POST
    $_SERVER['REQUEST_METHOD'] = 'POST';
    $_SERVER['CONTENT_TYPE'] = 'application/json';
    
    // Capturar output del endpoint
    ob_start();
    
    // Simular input JSON
    $GLOBALS['test_input'] = json_encode($testData);
    
    // Modificar temporalmente file_get_contents para usar nuestros datos de prueba
    function file_get_contents_override($filename) {
        if ($filename === 'php://input') {
            return $GLOBALS['test_input'];
        }
        return file_get_contents($filename);
    }
    
    // Incluir el endpoint
    include 'modules/consultas/api/livewire-crud.php';
    
    $output = ob_get_clean();
    
    echo "Respuesta del endpoint:<br>";
    echo "<pre>" . htmlspecialchars($output) . "</pre>";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}
?>