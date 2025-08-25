<?php
/**
 * TEST CORRECTO DEL DATABASEMAPPER - FORMULARIOS ESTUDIOS E INFORME
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'modules/consultas/core/DatabaseMapper.php';

echo "🔬 TEST CORRECTO DEL DATABASEMAPPER\n";
echo "===================================\n\n";

function testFormulario($formType, $data) {
    echo "🧪 Testing formulario: $formType\n";
    echo "--------------------------------\n";
    
    try {
        $mapper = new DatabaseMapper();
        
        // Test 1: Validación con parámetros correctos
        echo "1️⃣ Validando datos con tipoFormulario=$formType...\n";
        $validationResult = $mapper->validateData($data, $formType);
        
        if (!$validationResult['valid']) {
            echo "❌ Validación falló:\n";
            foreach ($validationResult['errors'] as $error) {
                echo "  - $error\n";
            }
            echo "\n";
            return;
        }
        echo "✅ Validación exitosa\n\n";
        
        // Test 2: Obtener configuración del formulario
        echo "2️⃣ Obteniendo configuración del formulario...\n";
        $formMapping = $mapper->getFormMapping($formType);
        if ($formMapping) {
            echo "✅ Configuración encontrada para $formType\n";
            echo "📄 Tabla principal: " . $formMapping['main_table'] . "\n";
            if (isset($formMapping['related_table'])) {
                echo "📄 Tabla relacionada: " . $formMapping['related_table'] . "\n";
            }
        } else {
            echo "❌ No se encontró configuración para $formType\n";
        }
        echo "\n";
        
        // Test 3: Mapeo HTML 
        echo "3️⃣ Obteniendo mapeo HTML...\n";
        $htmlMapping = $mapper->getHtmlFieldMapping($formType);
        echo "📄 Campos HTML mapeados: " . count($htmlMapping) . "\n";
        foreach ($htmlMapping as $htmlId => $dbField) {
            echo "  $htmlId -> $dbField\n";
        }
        echo "\n";
        
        // Test 4: Intentar guardar (modo creación)
        echo "4️⃣ Intentando crear nueva consulta...\n";
        $result = $mapper->saveConsulta($data, $formType);
        
        if ($result) {
            echo "✅ ¡Consulta creada exitosamente!\n";
            echo "📄 ID de consulta: $result\n";
        } else {
            echo "❌ Error al crear consulta\n";
        }
        
    } catch (Exception $e) {
        echo "❌ ERROR: " . $e->getMessage() . "\n";
        echo "📍 Archivo: " . $e->getFile() . " línea " . $e->getLine() . "\n";
    } catch (Error $e) {
        echo "💥 FATAL ERROR: " . $e->getMessage() . "\n";
        echo "📍 Archivo: " . $e->getFile() . " línea " . $e->getLine() . "\n";
    }
    
    echo "\n" . str_repeat("=", 50) . "\n\n";
}

// Datos de prueba para estudios
$testDataEstudios = [
    'id_persona' => 45,
    'tipo_formulario' => 'estudios',
    'txtmotivo' => 'Estudio de rutina para control anual',
    'tipo_estudio' => 'OCT',
    'observaciones' => 'Paciente refiere visión borrosa ocasional',
    'fecha_realizacion' => '2025-08-24'
];

// Datos de prueba para informe imagen  
$testDataInforme = [
    'id_persona' => 45,
    'tipo_formulario' => 'informe_imagen',
    'txtmotivo' => 'Seguimiento post cirugía cataratas',
    'equipoMedico-informe-imagen' => 'Oftalmoscopio Digital HD Plus',
    'descripcion-od-textarea-informe-imagen' => '<p><strong>Ojo Derecho:</strong> Cristalino transparente, no opacidades. Presión intraocular normal.</p>',
    'descripcion-oi-textarea-informe-imagen' => '<p><strong>Ojo Izquierdo:</strong> Leve opacidad cortical inferior. Requiere seguimiento.</p>'
];

// Ejecutar tests
testFormulario('estudios', $testDataEstudios);
testFormulario('informe_imagen', $testDataInforme);

echo "🎯 RESULTADO ESPERADO:\n";
echo "- Si ambos tests pasan = Los formularios están 100% funcionales\n";
echo "- Si hay errores = Necesitamos corregir la configuración del mapper\n";
?>