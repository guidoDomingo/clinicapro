<?php
/**
 * Debug del API sin autenticación
 */

// Simular sesión para pruebas
session_start();
$_SESSION['user_id'] = 1; // Simular usuario autenticado

// Headers para JSON
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

try {
    // Test 1: Verificar includes
    echo "🔍 Probando includes...\n";
    
    if (!file_exists('./config/config.php')) {
        throw new Exception("❌ No existe config/config.php");
    }
    echo "✅ config/config.php existe\n";
    
    if (!file_exists('./model/personas.model.php')) {
        throw new Exception("❌ No existe model/personas.model.php");
    }
    echo "✅ model/personas.model.php existe\n";
    
    if (!file_exists('./controller/consultas.controller.php')) {
        throw new Exception("❌ No existe controller/consultas.controller.php");
    }
    echo "✅ controller/consultas.controller.php existe\n";
    
    // Test 2: Cargar archivos
    echo "\n🔧 Cargando archivos...\n";
    require_once './config/config.php';
    echo "✅ config.php cargado\n";
    
    require_once './model/personas.model.php';
    echo "✅ personas.model.php cargado\n";
    
    require_once './controller/consultas.controller.php';
    echo "✅ consultas.controller.php cargado\n";
    
    // Test 3: Verificar clases
    echo "\n🏗️ Verificando clases...\n";
    
    if (class_exists('ModelPersonas')) {
        echo "✅ Clase ModelPersonas existe\n";
        $personasModel = new ModelPersonas();
        echo "✅ Instancia de ModelPersonas creada\n";
    } else {
        throw new Exception("❌ Clase ModelPersonas no existe");
    }
    
    if (class_exists('ControllerConsulta')) {
        echo "✅ Clase ControllerConsulta existe\n";
        $consultasController = new ControllerConsulta();
        echo "✅ Instancia de ControllerConsulta creada\n";
    } else {
        throw new Exception("❌ Clase ControllerConsulta no existe");
    }
    
    // Test 4: Probar búsqueda de pacientes
    echo "\n🔍 Probando búsqueda de pacientes...\n";
    
    $testData = ['nombres' => 'test'];
    $result = $personasModel::mdlGetPersonaParam($testData);
    
    echo "✅ Búsqueda ejecutada sin errores\n";
    echo "📊 Resultado: " . (is_array($result) ? count($result) . " registros" : "Sin resultados") . "\n";
    
    // Test 5: Respuesta JSON de ejemplo
    echo "\n📦 Generando respuesta JSON...\n";
    
    $response = [
        'success' => true,
        'message' => 'API funcionando correctamente',
        'data' => $result ?: [],
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    echo json_encode($response, JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo "📍 Archivo: " . $e->getFile() . "\n";
    echo "📍 Línea: " . $e->getLine() . "\n";
    
    if ($e->getPrevious()) {
        echo "🔗 Error anterior: " . $e->getPrevious()->getMessage() . "\n";
    }
}
?>