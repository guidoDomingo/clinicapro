<?php
/**
 * 🎯 TEST FINAL COMPLETO - TODOS LOS 4 FORMULARIOS
 * Prueba definitiva de funcionalidad 100%
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'modules/consultas/core/DatabaseMapper.php';

echo "🎯 TEST FINAL: 4 FORMULARIOS COMPLETOS\n";
echo "=====================================\n\n";

$mapper = new DatabaseMapper();

// Datos de prueba para cada formulario
$testData = [
    'general' => [
        'id_persona' => 45,
        'tipo_formulario' => 'general',
        'txtmotivo' => 'TEST FINAL: Consulta general',
        'visionod' => '20/20',
        'visionoi' => '20/25',
        'tensionod' => '14',
        'tensionoi' => '15',
        'consulta-textarea' => '<p>Examen general satisfactorio</p>',
        'receta-textarea' => '<p>Sin receta necesaria</p>',
        'proximaconsulta' => '2025-09-20'
    ],
    
    'anteojos' => [
        'id_persona' => 45,
        'tipo_formulario' => 'anteojos',
        'txtmotivo' => 'TEST FINAL: Graduación anteojos',
        'visionod' => '20/30',
        'visionoi' => '20/40',
        'od_esf' => '-1.25',
        'od_cil' => '-0.50',
        'od_eje' => '90',
        'oi_esf' => '-1.50',
        'oi_cil' => '-0.75',
        'oi_eje' => '85',
        'consulta-textarea' => '<p>Prescripción de anteojos</p>'
    ],
    
    'estudios' => [
        'id_persona' => 45,
        'tipo_formulario' => 'estudios',
        'txtmotivo' => 'TEST FINAL: Estudio completo OCT',
        'tipo_estudio' => 'OCT',
        'observaciones' => 'Estudio OCT realizado correctamente, resultados normales'
    ],
    
    'informe_imagen' => [
        'id_persona' => 45,
        'tipo_formulario' => 'informe_imagen',
        'txtmotivo' => 'TEST FINAL: Informe de imagen',
        'equipoMedico-informe-imagen' => 'OCT ZEISS Cirrus',
        'descripcion-od-textarea-informe-imagen' => '<p><strong>OD:</strong> Estructura normal, sin alteraciones</p>',
        'descripcion-oi-textarea-informe-imagen' => '<p><strong>OI:</strong> Estructura normal, sin alteraciones</p>'
    ]
];

function testFormulario($mapper, $tipo, $datos) {
    echo "🧪 TESTING: $tipo\n";
    echo str_repeat("-", 30) . "\n";
    
    $success = 0;
    $errors = [];
    
    try {
        // 1. Crear
        echo "📝 1. CREAR...\n";
        $createResult = $mapper->saveConsulta($datos, $tipo);
        
        if ($createResult['success']) {
            $idConsulta = $createResult['id_consulta'];
            echo "   ✅ Creación exitosa (ID: $idConsulta)\n";
            $success++;
            
            // 2. Cargar
            echo "📖 2. CARGAR...\n";
            $loadResult = $mapper->getConsulta($idConsulta);
            
            if ($loadResult['success']) {
                echo "   ✅ Carga exitosa\n";
                $success++;
                
                // 3. Editar (agregar algo al motivo)
                echo "✏️ 3. EDITAR...\n";
                $datos['txtmotivo'] = $datos['txtmotivo'] . ' - EDITADO';
                $editResult = $mapper->saveConsulta($datos, $tipo, $idConsulta);
                
                if ($editResult['success']) {
                    echo "   ✅ Edición exitosa\n";
                    $success++;
                    
                    // 4. Verificar cambios
                    echo "🔍 4. VERIFICAR...\n";
                    $verifyResult = $mapper->getConsulta($idConsulta);
                    
                    if ($verifyResult['success']) {
                        $mainData = $verifyResult['data']['main'];
                        if (strpos($mainData['txtmotivo'], 'EDITADO') !== false) {
                            echo "   ✅ Verificación exitosa (cambios guardados)\n";
                            $success++;
                        } else {
                            echo "   ❌ Los cambios no se guardaron\n";
                            $errors[] = "Cambios no guardados";
                        }
                    } else {
                        echo "   ❌ Error verificando: " . $verifyResult['message'] . "\n";
                        $errors[] = "Error en verificación";
                    }
                } else {
                    echo "   ❌ Error editando: " . $editResult['message'] . "\n";
                    $errors[] = "Error en edición";
                }
            } else {
                echo "   ❌ Error cargando: " . $loadResult['message'] . "\n";
                $errors[] = "Error en carga";
            }
        } else {
            echo "   ❌ Error creando: " . $createResult['message'] . "\n";
            $errors[] = "Error en creación";
        }
        
    } catch (Exception $e) {
        echo "   💥 Excepción: " . $e->getMessage() . "\n";
        $errors[] = "Excepción: " . $e->getMessage();
    }
    
    echo "\n📊 RESULTADO: $success/4 operaciones exitosas\n";
    
    if (count($errors) > 0) {
        echo "❌ ERRORES:\n";
        foreach ($errors as $error) {
            echo "   - $error\n";
        }
    }
    
    echo "\n";
    return $success === 4;
}

// Ejecutar tests
echo "🚀 INICIANDO TESTS COMPLETOS...\n\n";

$resultados = [];
foreach ($testData as $tipo => $datos) {
    $resultados[$tipo] = testFormulario($mapper, $tipo, $datos);
}

// Resultado final
echo "🏆 RESULTADO FINAL COMPLETO:\n";
echo "============================\n";

$totalFuncional = 0;
foreach ($resultados as $tipo => $funcional) {
    $status = $funcional ? "✅ 100% FUNCIONAL" : "❌ CON ERRORES";
    echo strtoupper($tipo) . ": $status\n";
    if ($funcional) $totalFuncional++;
}

echo "\n";
echo "📈 ESTADÍSTICAS FINALES:\n";
echo "Formularios completamente funcionales: $totalFuncional/4\n";
echo "Porcentaje de éxito: " . round(($totalFuncional/4)*100) . "%\n";

if ($totalFuncional === 4) {
    echo "\n🎉🎉🎉 ¡ÉXITO TOTAL! 🎉🎉🎉\n";
    echo "TODOS LOS 4 FORMULARIOS ESTÁN 100% FUNCIONALES\n";
    echo "✅ Crear ✅ Cargar ✅ Editar ✅ Verificar\n";
} else {
    echo "\n⚠️ Algunos formularios necesitan atención\n";
}
?>