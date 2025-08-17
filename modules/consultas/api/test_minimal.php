<?php
/**
 * TEST MÍNIMO DEL API - IDENTIFICAR ERROR 500
 */

// Habilitar todos los errores
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "🔍 Test paso a paso del API...\n\n";

try {
    // Paso 1: Headers
    echo "1. Headers... ";
    header('Content-Type: application/json');
    echo "✅\n";
    
    // Paso 2: Inclusión de archivos
    echo "2. Incluyendo conexion.php... ";
    require_once __DIR__ . '/../../../model/conexion.php';
    echo "✅\n";
    
    // Paso 3: Crear conexión
    echo "3. Creando conexión... ";
    $conexion = Conexion::conectar();
    echo "✅\n";
    
    // Paso 4: Inicialización de respuesta
    echo "4. Inicializando respuesta... ";
    $response = [
        'success' => false,
        'message' => '',
        'data' => null,
        'timestamp' => date('Y-m-d H:i:s')
    ];
    echo "✅\n";
    
    // Paso 5: Obtener acción
    echo "5. Obteniendo acción... ";
    $action = $_GET['action'] ?? 'test';
    echo "✅ (Action: $action)\n";
    
    // Paso 6: Verificar sesión
    echo "6. Verificando sesión... ";
    session_start();
    if (!isset($_SESSION['user_id']) && $action !== 'verify_session') {
        $_SESSION['user_id'] = 1; // Simular para test
    }
    echo "✅\n";
    
    // Paso 7: Test función específica
    echo "7. Testing getMotivosComunes... ";
    
    if ($action === 'getMotivosComunes') {
        // Definir función inline para test
        function getMotivosComunes() {
            global $conexion;
            
            try {
                // Test simple primero
                $stmt = $conexion->query("SELECT 'test' as test_field LIMIT 1");
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                
                return [
                    'success' => true,
                    'data' => [$result],
                    'message' => 'Test exitoso'
                ];
                
            } catch (PDOException $e) {
                return [
                    'success' => false,
                    'message' => 'Error DB: ' . $e->getMessage(),
                    'data' => null
                ];
            }
        }
        
        $result = getMotivosComunes();
        echo "✅\n";
        
        echo "\nResultado:\n";
        echo json_encode($result, JSON_PRETTY_PRINT);
    } else {
        echo "⚠️ (No es getMotivosComunes)\n";
        echo json_encode(['status' => 'test_ok', 'action' => $action], JSON_PRETTY_PRINT);
    }
    
} catch (Throwable $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo "📍 Archivo: " . $e->getFile() . " línea " . $e->getLine() . "\n";
    echo "\n🔍 Stack trace:\n" . $e->getTraceAsString() . "\n";
    
    // Respuesta de error
    $error_response = [
        'success' => false,
        'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ];
    
    echo "\n📋 Error JSON:\n";
    echo json_encode($error_response, JSON_PRETTY_PRINT);
}
?>
