<?php
/**
 * 🚀 SISTEMA DE TESTING COMPLETO PARA LOS 4 FORMULARIOS
 * Crear, Editar y Verificar datos para: General, Anteojos, Estudios, Informe Imagen
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'modules/consultas/core/DatabaseMapper.php';

echo "🧪 SISTEMA DE TESTING COMPLETO - 4 FORMULARIOS\n";
echo "================================================\n\n";

class FormularioTester {
    private $mapper;
    private $resultados = [];
    
    public function __construct() {
        $this->mapper = new DatabaseMapper();
    }
    
    /**
     * Test completo de un tipo de formulario
     */
    public function testFormulario($tipo, $datosCreacion, $datosEdicion) {
        echo "🔬 TESTING FORMULARIO: " . strtoupper($tipo) . "\n";
        echo str_repeat("-", 50) . "\n";
        
        $resultado = [
            'tipo' => $tipo,
            'crear' => false,
            'cargar' => false,
            'editar' => false,
            'datos_correctos' => false,
            'id_creado' => null
        ];
        
        try {
            // 📝 PASO 1: CREAR
            echo "1️⃣ Creando consulta $tipo...\n";
            $resultadoCrear = $this->mapper->saveConsulta($datosCreacion, $tipo);
            
            if ($resultadoCrear && (is_array($resultadoCrear) ? $resultadoCrear['success'] : true)) {
                $idConsulta = is_array($resultadoCrear) ? $resultadoCrear['id_consulta'] : $resultadoCrear;
                
                if ($idConsulta) {
                    echo "✅ Consulta creada con ID: $idConsulta\n";
                    $resultado['crear'] = true;
                    $resultado['id_creado'] = $idConsulta;
                    
                    // 📖 PASO 2: CARGAR
                    echo "2️⃣ Cargando consulta para verificar datos...\n";
                    $resultadoCargar = $this->mapper->getConsulta($idConsulta);
                
                if ($resultadoCargar['success']) {
                    echo "✅ Consulta cargada exitosamente\n";
                    $resultado['cargar'] = true;
                    
                    // Verificar estructura de datos
                    $data = $resultadoCargar['data'];
                    $this->verificarEstructuraDatos($data, $tipo);
                    
                    // ✏️ PASO 3: EDITAR
                    echo "3️⃣ Editando consulta...\n";
                    $resultadoEditar = $this->mapper->saveConsulta($datosEdicion, $tipo, $idConsulta);
                    
                    if ($resultadoEditar) {
                        echo "✅ Consulta editada exitosamente\n";
                        $resultado['editar'] = true;
                        
                        // 🔍 PASO 4: VERIFICAR CAMBIOS
                        echo "4️⃣ Verificando cambios aplicados...\n";
                        $resultadoVerificar = $this->mapper->getConsulta($idConsulta);
                        
                        if ($resultadoVerificar['success']) {
                            $dataEditada = $resultadoVerificar['data'];
                            $resultado['datos_correctos'] = $this->verificarCambios($dataEditada, $datosEdicion, $tipo);
                        }
                    } else {
                        echo "❌ Error al editar consulta\n";
                    }
                } else {
                    echo "❌ Error: ID de consulta no válido\n";
                }
            } else {
                echo "❌ Error al crear consulta: " . (is_array($resultadoCrear) ? $resultadoCrear['message'] : 'Respuesta inválida') . "\n";
            }
            
        } catch (Exception $e) {
            echo "💥 ERROR: " . $e->getMessage() . "\n";
        }
        
        // Resumen del test
        $exitosos = array_sum(array_map(function($v) { return $v ? 1 : 0; }, $resultado));
        $total = 4;
        
        echo "\n📊 RESULTADO FINAL: $exitosos/$total operaciones exitosas\n";
        if ($exitosos == $total) {
            echo "🎉 ¡FORMULARIO $tipo FUNCIONA AL 100%!\n";
        } else {
            echo "⚠️ FORMULARIO $tipo necesita correcciones\n";
        }
        
        echo "\n" . str_repeat("=", 60) . "\n\n";
        $this->resultados[$tipo] = $resultado;
        
        return $resultado;
    }
    
    private function verificarEstructuraDatos($data, $tipo) {
        echo "  🔍 Verificando estructura de datos...\n";
        
        $checks = [
            'main' => isset($data['main']),
            'type' => isset($data['type']) && $data['type'] === $tipo,
            'tipo_formulario' => isset($data['tipo_formulario']) && $data['tipo_formulario'] === $tipo,
            'html_mapping' => isset($data['html_mapping']) && count($data['html_mapping']) > 0
        ];
        
        if ($tipo !== 'general') {
            $checks['related'] = isset($data['related']) && !empty($data['related']);
        }
        
        foreach ($checks as $check => $result) {
            echo "    " . ($result ? "✅" : "❌") . " $check\n";
        }
    }
    
    private function verificarCambios($data, $datosEdicion, $tipo) {
        echo "  🔍 Verificando que los cambios se aplicaron...\n";
        
        $cambiosCorrectos = true;
        
        // Verificar campos principales
        if (isset($data['main']['txtmotivo']) && isset($datosEdicion['txtmotivo'])) {
            $correcto = $data['main']['txtmotivo'] === $datosEdicion['txtmotivo'];
            echo "    " . ($correcto ? "✅" : "❌") . " txtmotivo: " . $data['main']['txtmotivo'] . "\n";
            $cambiosCorrectos = $cambiosCorrectos && $correcto;
        }
        
        // Verificar campos específicos según el tipo
        if ($tipo !== 'general' && isset($data['related'])) {
            $this->verificarCamposEspecificos($data['related'], $datosEdicion, $tipo);
        }
        
        return $cambiosCorrectos;
    }
    
    private function verificarCamposEspecificos($related, $datosEdicion, $tipo) {
        switch ($tipo) {
            case 'anteojos':
                if (isset($related['consulta_anteojos'])) {
                    $anteojos = $related['consulta_anteojos'];
                    if (isset($datosEdicion['esfera_od'])) {
                        $correcto = $anteojos['esfera_od'] == $datosEdicion['esfera_od'];
                        echo "    " . ($correcto ? "✅" : "❌") . " esfera_od: " . $anteojos['esfera_od'] . "\n";
                    }
                }
                break;
                
            case 'estudios':
                if (isset($related['consulta_estudios'])) {
                    $estudios = $related['consulta_estudios'];
                    if (isset($datosEdicion['tipo_estudio'])) {
                        $correcto = $estudios['equipo_medico'] === $datosEdicion['tipo_estudio'];
                        echo "    " . ($correcto ? "✅" : "❌") . " tipo_estudio: " . $estudios['equipo_medico'] . "\n";
                    }
                }
                break;
                
            case 'informe_imagen':
                if (isset($related['consulta_informe_imagen'])) {
                    $informe = $related['consulta_informe_imagen'];
                    if (isset($datosEdicion['equipoMedico-informe-imagen'])) {
                        $correcto = $informe['equipo_medico'] === $datosEdicion['equipoMedico-informe-imagen'];
                        echo "    " . ($correcto ? "✅" : "❌") . " equipo_medico: " . $informe['equipo_medico'] . "\n";
                    }
                }
                break;
        }
    }
    
    public function mostrarResumenFinal() {
        echo "🏆 RESUMEN FINAL DE TODOS LOS FORMULARIOS\n";
        echo "=========================================\n\n";
        
        $totalExitosos = 0;
        $totalFormularios = count($this->resultados);
        
        foreach ($this->resultados as $tipo => $resultado) {
            $operacionesExitosas = array_sum(array_map(function($v) { return $v ? 1 : 0; }, $resultado)) - 1; // -1 por el campo tipo
            $estado = $operacionesExitosas == 4 ? "🎉 100% FUNCIONAL" : "⚠️ NECESITA CORRECCIÓN";
            
            echo "📋 " . strtoupper($tipo) . ": $estado ($operacionesExitosas/4)\n";
            echo "   - Crear: " . ($resultado['crear'] ? "✅" : "❌") . "\n";
            echo "   - Cargar: " . ($resultado['cargar'] ? "✅" : "❌") . "\n";
            echo "   - Editar: " . ($resultado['editar'] ? "✅" : "❌") . "\n";
            echo "   - Datos correctos: " . ($resultado['datos_correctos'] ? "✅" : "❌") . "\n";
            
            if ($operacionesExitosas == 4) {
                $totalExitosos++;
            }
            
            echo "\n";
        }
        
        echo "🎯 RESULTADO GLOBAL: $totalExitosos/$totalFormularios formularios 100% funcionales\n";
        
        if ($totalExitosos == $totalFormularios) {
            echo "\n🎉🎉🎉 ¡TODOS LOS FORMULARIOS FUNCIONAN PERFECTAMENTE! 🎉🎉🎉\n";
            echo "✅ Sistema completo de consultas médicas operativo\n";
            echo "✅ Crear, editar y cargar funciona en todos los tipos\n";
            echo "✅ Base de datos integrada correctamente\n";
        } else {
            echo "\n⚠️ Algunos formularios necesitan ajustes\n";
        }
    }
}

// 📊 DATOS DE PRUEBA PARA CADA FORMULARIO
$datosGeneral = [
    'crear' => [
        'id_persona' => 45,
        'tipo_formulario' => 'general',
        'txtmotivo' => 'Consulta general de prueba',
        'visionod' => '20/20',
        'visionoi' => '20/25',
        'consulta_textarea' => '<p>Consulta general para testing</p>',
        'receta_textarea' => '<p>Receta de prueba</p>'
    ],
    'editar' => [
        'id_persona' => 45,
        'tipo_formulario' => 'general',
        'txtmotivo' => 'Consulta general EDITADA',
        'visionod' => '20/30',
        'visionoi' => '20/20',
        'consulta_textarea' => '<p>Consulta EDITADA exitosamente</p>',
        'receta_textarea' => '<p>Receta EDITADA</p>'
    ]
];

$datosAnteojos = [
    'crear' => [
        'id_persona' => 45,
        'tipo_formulario' => 'anteojos',
        'txtmotivo' => 'Prescripción de anteojos',
        'esfera_od' => '-1.50',
        'cilindro_od' => '-0.25',
        'eje_od' => '90',
        'esfera_oi' => '-1.25',
        'cilindro_oi' => '-0.50',
        'eje_oi' => '85'
    ],
    'editar' => [
        'id_persona' => 45,
        'tipo_formulario' => 'anteojos',
        'txtmotivo' => 'Prescripción EDITADA',
        'esfera_od' => '-2.00',
        'cilindro_od' => '-0.75',
        'eje_od' => '95',
        'esfera_oi' => '-1.75',
        'cilindro_oi' => '-0.25',
        'eje_oi' => '80'
    ]
];

$datosEstudios = [
    'crear' => [
        'id_persona' => 45,
        'tipo_formulario' => 'estudios',
        'txtmotivo' => 'Estudio OCT de rutina',
        'tipo_estudio' => 'OCT',
        'observaciones' => 'Estudio sin alteraciones',
        'fecha_realizacion' => '2025-08-24'
    ],
    'editar' => [
        'id_persona' => 45,
        'tipo_formulario' => 'estudios',
        'txtmotivo' => 'Estudio OCT EDITADO',
        'tipo_estudio' => 'Angiofluoresceína',
        'observaciones' => 'Estudio EDITADO con cambios',
        'fecha_realizacion' => '2025-08-25'
    ]
];

$datosInforme = [
    'crear' => [
        'id_persona' => 45,
        'tipo_formulario' => 'informe_imagen',
        'txtmotivo' => 'Informe con imagen',
        'equipoMedico-informe-imagen' => 'Cámara retinal HD',
        'descripcion-od-textarea-informe-imagen' => '<p>OD: Normal</p>',
        'descripcion-oi-textarea-informe-imagen' => '<p>OI: Normal</p>'
    ],
    'editar' => [
        'id_persona' => 45,
        'tipo_formulario' => 'informe_imagen',
        'txtmotivo' => 'Informe EDITADO',
        'equipoMedico-informe-imagen' => 'Oftalmoscopio Digital',
        'descripcion-od-textarea-informe-imagen' => '<p>OD: EDITADO</p>',
        'descripcion-oi-textarea-informe-imagen' => '<p>OI: EDITADO</p>'
    ]
];

// 🚀 EJECUTAR TESTING COMPLETO
$tester = new FormularioTester();

$tester->testFormulario('general', $datosGeneral['crear'], $datosGeneral['editar']);
$tester->testFormulario('anteojos', $datosAnteojos['crear'], $datosAnteojos['editar']);
$tester->testFormulario('estudios', $datosEstudios['crear'], $datosEstudios['editar']);
$tester->testFormulario('informe_imagen', $datosInforme['crear'], $datosInforme['editar']);

$tester->mostrarResumenFinal();

echo "\n" . str_repeat("🎉", 50) . "\n";
echo "🔥 TESTING COMPLETADO - REVISA LOS RESULTADOS 🔥\n";
echo str_repeat("🎉", 50) . "\n";
?>