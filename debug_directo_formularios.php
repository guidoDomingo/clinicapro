<?php
/**
 * DEBUG DIRECTO PARA FORMULARIOS - SIN cURL
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Simular entorno del API
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['CONTENT_TYPE'] = 'application/json';

echo "🐛 DEBUG DIRECTO PARA FORMULARIOS ESTUDIOS E INFORME_IMAGEN\n";
echo "==========================================================\n\n";

function debugFormularioDirecto($formType, $data) {
    echo "🔍 Debuggeando formulario: $formType\n";
    echo "-----------------------------------\n";
    
    try {
        // Simular datos POST del API
        $_POST = [];
        $_GET['action'] = 'create_consulta';
        
        // Simular input JSON
        $jsonData = json_encode($data);
        
        echo "📤 Datos de entrada: $jsonData\n\n";
        
        // Incluir el DatabaseMapper directamente
        ob_start();
        
        // Cambiar directorio para includes relativos
        chdir(__DIR__ . '/modules/consultas/api');
        
        // Capturar contenido input
        $backup_input = file_get_contents('php://input');
        file_put_contents('php://input', $jsonData); // Esto no funciona, usemos variable global
        $GLOBALS['debug_input'] = $jsonData;
        
        // Incluir el archivo API pero capturar errores
        include 'modern-api.php';
        
        $output = ob_get_clean();
        
        echo "✅ Output del API:\n";
        echo $output . "\n";
        
        // Restaurar directorio
        chdir(__DIR__);
        
    } catch (Exception $e) {
        echo "❌ ERROR: " . $e->getMessage() . "\n";
        echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    } catch (Error $e) {
        echo "💥 FATAL ERROR: " . $e->getMessage() . "\n";
        echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    }
    
    echo "\n" . str_repeat("=", 60) . "\n\n";
}

// Datos de prueba
$testDataEstudios = [
    'id_persona' => 45,
    'tipo_formulario' => 'estudios',
    'txtmotivo' => 'Debug estudios',
    'tipo_estudio' => 'OCT',
    'observaciones' => 'Test observaciones',
    'fecha_realizacion' => '2025-08-24'
];

$testDataInforme = [
    'id_persona' => 45,
    'tipo_formulario' => 'informe_imagen',
    'txtmotivo' => 'Debug informe',
    'equipoMedico-informe-imagen' => 'Equipo test',
    'descripcion-od-textarea-informe-imagen' => 'Test OD',
    'descripcion-oi-textarea-informe-imagen' => 'Test OI'
];

debugFormularioDirecto('estudios', $testDataEstudios);
debugFormularioDirecto('informe_imagen', $testDataInforme);
?>