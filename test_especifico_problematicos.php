<?php
/**
 * 🚀 TEST ESPECÍFICO PARA ESTUDIOS E INFORME IMAGEN
 * Vamos directo al problema para arreglarlo
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'modules/consultas/core/DatabaseMapper.php';

echo "🔧 TEST ESPECÍFICO: ESTUDIOS E INFORME IMAGEN\n";
echo "==============================================\n\n";

$mapper = new DatabaseMapper();

// Test datos de estudios
$datosEstudios = [
    'id_persona' => 45,
    'tipo_formulario' => 'estudios',
    'txtmotivo' => 'TEST: Estudio OCT específico',
    'tipo_estudio' => 'OCT',
    'observaciones' => 'Test específico de estudios',
    'fecha_realizacion' => '2025-08-24'
];

// Test datos de informe imagen
$datosInforme = [
    'id_persona' => 45,
    'tipo_formulario' => 'informe_imagen',
    'txtmotivo' => 'TEST: Informe específico',
    'equipoMedico-informe-imagen' => 'TEST: Equipo específico',
    'descripcion-od-textarea-informe-imagen' => '<p>TEST: OD específico</p>',
    'descripcion-oi-textarea-informe-imagen' => '<p>TEST: OI específico</p>'
];

function testEspecifico($mapper, $tipo, $datos) {
    echo "🧪 TESTING $tipo\n";
    echo str_repeat("-", 30) . "\n";
    
    try {
        echo "📝 Creando consulta...\n";
        $resultado = $mapper->saveConsulta($datos, $tipo);
        
        echo "🔍 Respuesta completa:\n";
        print_r($resultado);
        
        if (is_array($resultado) && isset($resultado['success']) && $resultado['success']) {
            $id = $resultado['id_consulta'];
            echo "✅ ÉXITO: Consulta creada con ID $id\n";
            
            // Test de carga
            echo "📖 Probando carga...\n";
            $cargaResultado = $mapper->getConsulta($id);
            
            if ($cargaResultado['success']) {
                echo "✅ CARGA EXITOSA\n";
                return true;
            } else {
                echo "❌ ERROR EN CARGA: " . $cargaResultado['message'] . "\n";
                return false;
            }
        } else {
            echo "❌ ERROR EN CREACIÓN\n";
            if (is_array($resultado) && isset($resultado['message'])) {
                echo "   Mensaje: " . $resultado['message'] . "\n";
            }
            return false;
        }
        
    } catch (Exception $e) {
        echo "💥 EXCEPCIÓN: " . $e->getMessage() . "\n";
        return false;
    }
    
    echo "\n";
}

// Ejecutar tests específicos
$resultadoEstudios = testEspecifico($mapper, 'estudios', $datosEstudios);
$resultadoInforme = testEspecifico($mapper, 'informe_imagen', $datosInforme);

echo "🎯 RESULTADO FINAL:\n";
echo "==================\n";
echo "Estudios: " . ($resultadoEstudios ? "✅ FUNCIONAL" : "❌ CON ERRORES") . "\n";
echo "Informe Imagen: " . ($resultadoInforme ? "✅ FUNCIONAL" : "❌ CON ERRORES") . "\n";

if ($resultadoEstudios && $resultadoInforme) {
    echo "\n🎉 ¡AMBOS FORMULARIOS FUNCIONAN!\n";
} else {
    echo "\n⚠️ Hay errores que corregir en la configuración\n";
}
?>