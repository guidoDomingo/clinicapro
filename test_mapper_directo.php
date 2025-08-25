<?php
/**
 * TEST DIRECTO DEL DATABASEMAPPER - FORMULARIOS ESTUDIOS E INFORME
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'modules/consultas/api/DatabaseMapper.php';

echo "🔬 TEST DIRECTO DEL DATABASEMAPPER\n";
echo "==================================\n\n";

function testFormulario($formType, $data) {
    echo "🧪 Testing formulario: $formType\n";
    echo "--------------------------------\n";
    
    try {
        $mapper = new DatabaseMapper();
        
        // Test 1: Validación
        echo "1️⃣ Validando datos...\n";
        $validationResult = $mapper->validateData($data);
        
        if (!$validationResult['valid']) {
            echo "❌ Validación falló:\n";
            foreach ($validationResult['errors'] as $error) {
                echo "  - $error\n";
            }
            echo "\n";
            return;
        }
        echo "✅ Validación exitosa\n\n";
        
        // Test 2: Preparar datos tabla principal
        echo "2️⃣ Preparando datos tabla principal...\n";
        $mainTableData = $mapper->prepareMainTableData($data);
        echo "📄 Datos tabla principal:\n";
        print_r($mainTableData);
        echo "\n";
        
        // Test 3: Preparar datos tabla específica
        echo "3️⃣ Preparando datos tabla específica...\n";
        $specificTableData = $mapper->prepareSpecificTableData($data);
        echo "📄 Datos tabla específica:\n";
        print_r($specificTableData);
        echo "\n";
        
        // Test 4: Crear consulta (simulado)
        echo "4️⃣ Simulando creación...\n";
        echo "✅ Todo listo para INSERT\n";
        
    } catch (Exception $e) {
        echo "❌ ERROR: " . $e->getMessage() . "\n";
        echo "📍 Archivo: " . $e->getFile() . " línea " . $e->getLine() . "\n";
        echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    } catch (Error $e) {
        echo "💥 FATAL ERROR: " . $e->getMessage() . "\n";
        echo "📍 Archivo: " . $e->getFile() . " línea " . $e->getLine() . "\n";
        echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    }
    
    echo "\n" . str_repeat("=", 50) . "\n\n";
}

// Datos de prueba más completos
$testDataEstudios = [
    'id_persona' => 45,
    'tipo_formulario' => 'estudios',
    'txtmotivo' => 'Test estudios completo',
    'tipo_estudio' => 'OCT',
    'observaciones' => 'Observaciones detalladas de prueba',
    'fecha_realizacion' => '2025-08-24'
];

$testDataInforme = [
    'id_persona' => 45,
    'tipo_formulario' => 'informe_imagen',
    'txtmotivo' => 'Test informe imagen completo',
    'equipoMedico-informe-imagen' => 'Oftalmoscopio Digital HD Plus',
    'descripcion-od-textarea-informe-imagen' => 'Descripción detallada del ojo derecho',
    'descripcion-oi-textarea-informe-imagen' => 'Descripción detallada del ojo izquierdo'
];

// Ejecutar tests
testFormulario('estudios', $testDataEstudios);
testFormulario('informe_imagen', $testDataInforme);

echo "🎯 Si no hay errores aquí, el problema está en el API endpoint\n";
echo "Si hay errores, necesitamos corregir el DatabaseMapper\n";
?>