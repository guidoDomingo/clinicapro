<?php
/**
 * Test específico para el endpoint Livwire
 */

// Simular sesión autenticada para pruebas
session_start();
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1; // Simular usuario para pruebas
}

// Headers para JSON
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

echo "🔍 Iniciando test del endpoint...\n";

try {
    // Capturar todos los errores
    error_reporting(E_ALL);
    ini_set('display_errors', 0);
    
    // Buffer de salida para capturar errores
    ob_start();
    
    echo "📂 Incluyendo archivos necesarios...\n";
    
    // Test de inclusión paso a paso
    if (file_exists('./modules/consultas/api/livewire-crud.php')) {
        echo "✅ Archivo API encontrado\n";
        
        // Simular petición POST
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [];
        
        // Datos de prueba
        $input = json_encode([
            'action' => 'test',
            'data' => []
        ]);
        
        // Simular input stream
        $GLOBALS['HTTP_RAW_POST_DATA'] = $input;
        
        echo "🚀 Ejecutando endpoint...\n";
        
        // Incluir y ejecutar
        include './modules/consultas/api/livwire-crud.php';
        
    } else {
        throw new Exception("❌ Archivo API no encontrado");
    }
    
} catch (Exception $e) {
    // Limpiar buffer para evitar HTML mezclado
    ob_clean();
    
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString()
    ], JSON_PRETTY_PRINT);
    
} catch (Error $e) {
    // Limpiar buffer para evitar HTML mezclado  
    ob_clean();
    
    echo json_encode([
        'success' => false,
        'error' => 'PHP Fatal Error: ' . $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ], JSON_PRETTY_PRINT);
}
?>