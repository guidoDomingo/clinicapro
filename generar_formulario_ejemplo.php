<?php
/**
 * Script para generar un formulario dinámico de ejemplo
 * Este script crea un formulario de "Cardiología" con campos dinámicos
 * para demostrar el funcionamiento del sistema de referenciales
 */

require_once "model/conexion.php";
require_once "model/referenciales.model.php";

// Función para mostrar resultados con estilo
function mostrarResultado($mensaje, $tipo = 'info') {
    $colores = [
        'success' => '#d4edda',
        'error' => '#f8d7da', 
        'warning' => '#fff3cd',
        'info' => '#d1ecf1'
    ];
    
    $color = $colores[$tipo] ?? $colores['info'];
    echo "<div style='background-color: {$color}; padding: 10px; margin: 5px 0; border-radius: 5px; border: 1px solid #ccc;'>";
    echo "<strong>" . ucfirst($tipo) . ":</strong> {$mensaje}";
    echo "</div>";
}

try {
    $pdo = Conexion::conectar();
    
    echo "<html><head><title>Generador de Formulario Dinámico de Ejemplo</title></head><body>";
    echo "<h1>🎯 Generando Formulario Dinámico de Cardiología</h1>";
    echo "<p>Creando un ejemplo completo de formulario dinámico...</p>";
    
    // 1. Crear tipo de formulario de Cardiología
    echo "<h2>📝 Paso 1: Crear Tipo de Formulario</h2>";
    
    $tipoFormulario = [
        'nombre' => 'Cardiología Avanzada',
        'codigo' => 'cardiologia_avanzada',
        'descripcion' => 'Formulario especializado para consultas de cardiología con campos específicos para evaluación cardiovascular',
        'activo' => 1,
        'created_by' => 1
    ];
    
    // Verificar si ya existe
    $stmt = $pdo->prepare("SELECT id FROM tipos_formularios WHERE codigo = ?");
    $stmt->execute([$tipoFormulario['codigo']]);
    $tipoFormularioId = $stmt->fetchColumn();
    
    if (!$tipoFormularioId) {
        $resultado = ModelReferenciales::mdlIngresarRegistro('tipos_formularios', $tipoFormulario);
        if ($resultado == 'ok') {
            $stmt = $pdo->prepare("SELECT id FROM tipos_formularios WHERE codigo = ?");
            $stmt->execute([$tipoFormulario['codigo']]);
            $tipoFormularioId = $stmt->fetchColumn();
            mostrarResultado("✅ Tipo de formulario 'Cardiología Avanzada' creado con ID: {$tipoFormularioId}", 'success');
        }
    } else {
        mostrarResultado("ℹ️ Tipo de formulario ya existe con ID: {$tipoFormularioId}", 'info');
    }
    
    // 2. Crear referenciales específicos de cardiología
    echo "<h2>🏷️ Paso 2: Crear Referenciales de Cardiología</h2>";
    
    $referencialesCardiologia = [
        [
            'nombre' => 'Grados de Insuficiencia Cardíaca',
            'codigo' => 'grados_insuficiencia_cardiaca',
            'descripcion' => 'Clasificación NYHA para insuficiencia cardíaca',
            'categoria' => 'cardiologia',
            'valores' => [
                ['valor' => 'clase_i', 'etiqueta' => 'Clase I - Sin limitaciones', 'orden' => 1],
                ['valor' => 'clase_ii', 'etiqueta' => 'Clase II - Limitación ligera', 'orden' => 2],
                ['valor' => 'clase_iii', 'etiqueta' => 'Clase III - Limitación marcada', 'orden' => 3],
                ['valor' => 'clase_iv', 'etiqueta' => 'Clase IV - Incapacidad', 'orden' => 4]
            ]
        ],
        [
            'nombre' => 'Tipos de Arritmias',
            'codigo' => 'tipos_arritmias',
            'descripcion' => 'Clasificación de arritmias cardíacas',
            'categoria' => 'cardiologia',
            'valores' => [
                ['valor' => 'bradicardia', 'etiqueta' => 'Bradicardia', 'orden' => 1],
                ['valor' => 'taquicardia', 'etiqueta' => 'Taquicardia', 'orden' => 2],
                ['valor' => 'fibrilacion_auricular', 'etiqueta' => 'Fibrilación Auricular', 'orden' => 3],
                ['valor' => 'flutter_auricular', 'etiqueta' => 'Flutter Auricular', 'orden' => 4],
                ['valor' => 'extrasistoles', 'etiqueta' => 'Extrasístoles', 'orden' => 5]
            ]
        ],
        [
            'nombre' => 'Presión Arterial Sistólica',
            'codigo' => 'presion_arterial_sistolica',
            'descripcion' => 'Rangos de presión arterial sistólica',
            'categoria' => 'cardiologia',
            'valores' => [
                ['valor' => 'normal', 'etiqueta' => 'Normal (< 120 mmHg)', 'valor_numerico' => 120, 'orden' => 1],
                ['valor' => 'elevada', 'etiqueta' => 'Elevada (120-129 mmHg)', 'valor_numerico' => 125, 'orden' => 2],
                ['valor' => 'hipertension_1', 'etiqueta' => 'Hipertensión Grado 1 (130-139 mmHg)', 'valor_numerico' => 135, 'orden' => 3],
                ['valor' => 'hipertension_2', 'etiqueta' => 'Hipertensión Grado 2 (≥ 140 mmHg)', 'valor_numerico' => 140, 'orden' => 4]
            ]
        ]
    ];
    
    foreach ($referencialesCardiologia as $refData) {
        // Crear referencial
        $referencialDatos = [
            'nombre' => $refData['nombre'],
            'codigo' => $refData['codigo'],
            'descripcion' => $refData['descripcion'],
            'categoria' => $refData['categoria'],
            'activo' => 1,
            'created_by' => 1
        ];
        
        // Verificar si ya existe
        $stmt = $pdo->prepare("SELECT id FROM referenciales WHERE codigo = ?");
        $stmt->execute([$referencialDatos['codigo']]);
        $referencialId = $stmt->fetchColumn();
        
        if (!$referencialId) {
            $resultado = ModelReferenciales::mdlIngresarRegistro('referenciales', $referencialDatos);
            if ($resultado == 'ok') {
                $stmt = $pdo->prepare("SELECT id FROM referenciales WHERE codigo = ?");
                $stmt->execute([$referencialDatos['codigo']]);
                $referencialId = $stmt->fetchColumn();
                mostrarResultado("✅ Referencial '{$refData['nombre']}' creado con ID: {$referencialId}", 'success');
            }
        } else {
            mostrarResultado("ℹ️ Referencial '{$refData['nombre']}' ya existe con ID: {$referencialId}", 'info');
        }
        
        // Crear valores del referencial
        foreach ($refData['valores'] as $valorData) {
            $valorDatos = [
                'referencial_id' => $referencialId,
                'valor' => $valorData['valor'],
                'etiqueta' => $valorData['etiqueta'],
                'valor_numerico' => $valorData['valor_numerico'] ?? null,
                'orden_visualizacion' => $valorData['orden'],
                'activo' => 1
            ];
            
            // Verificar si ya existe
            $stmt = $pdo->prepare("SELECT id FROM referencial_valores WHERE referencial_id = ? AND valor = ?");
            $stmt->execute([$referencialId, $valorData['valor']]);
            $valorExiste = $stmt->fetchColumn();
            
            if (!$valorExiste) {
                $resultado = ModelReferenciales::mdlIngresarRegistro('referencial_valores', $valorDatos);
                if ($resultado == 'ok') {
                    mostrarResultado("📊 Valor '{$valorData['etiqueta']}' agregado", 'success');
                }
            }
        }
    }
    
    // 3. Crear campos del formulario
    echo "<h2>🔧 Paso 3: Crear Campos del Formulario</h2>";
    
    // Obtener IDs de tipos de campos
    $tiposCampos = [];
    $stmt = $pdo->prepare("SELECT id, codigo FROM tipos_campos");
    $stmt->execute();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $tiposCampos[$row['codigo']] = $row['id'];
    }
    
    $camposFormulario = [
        [
            'nombre_campo' => 'motivo_consulta_cardio',
            'etiqueta' => 'Motivo de Consulta Cardiológica',
            'tipo_campo' => 'textarea',
            'placeholder' => 'Describa el motivo de la consulta cardiológica...',
            'orden' => 1,
            'requerido' => true,
            'grupo_seccion' => 'informacion_basica'
        ],
        [
            'nombre_campo' => 'presion_arterial_sistolica',
            'etiqueta' => 'Presión Arterial Sistólica',
            'tipo_campo' => 'select',
            'orden' => 2,
            'requerido' => false,
            'grupo_seccion' => 'signos_vitales'
        ],
        [
            'nombre_campo' => 'presion_arterial_diastolica',
            'etiqueta' => 'Presión Arterial Diastólica (mmHg)',
            'tipo_campo' => 'number',
            'placeholder' => 'Ej: 80',
            'orden' => 3,
            'validaciones' => '{"min": 40, "max": 120}',
            'grupo_seccion' => 'signos_vitales'
        ],
        [
            'nombre_campo' => 'frecuencia_cardiaca',
            'etiqueta' => 'Frecuencia Cardíaca (ppm)',
            'tipo_campo' => 'number',
            'placeholder' => 'Ej: 72',
            'orden' => 4,
            'validaciones' => '{"min": 30, "max": 200}',
            'grupo_seccion' => 'signos_vitales'
        ],
        [
            'nombre_campo' => 'grado_insuficiencia',
            'etiqueta' => 'Grado de Insuficiencia Cardíaca (NYHA)',
            'tipo_campo' => 'select',
            'orden' => 5,
            'grupo_seccion' => 'evaluacion_cardiologica'
        ],
        [
            'nombre_campo' => 'tipo_arritmia',
            'etiqueta' => 'Tipo de Arritmia Detectada',
            'tipo_campo' => 'select',
            'orden' => 6,
            'grupo_seccion' => 'evaluacion_cardiologica'
        ],
        [
            'nombre_campo' => 'antecedentes_familiares',
            'etiqueta' => 'Antecedentes Familiares Cardiovasculares',
            'tipo_campo' => 'checkbox',
            'orden' => 7,
            'grupo_seccion' => 'antecedentes'
        ],
        [
            'nombre_campo' => 'medicamentos_actuales',
            'etiqueta' => 'Medicamentos Cardiovasculares Actuales',
            'tipo_campo' => 'textarea',
            'placeholder' => 'Liste los medicamentos actuales para el corazón...',
            'orden' => 8,
            'grupo_seccion' => 'tratamiento'
        ],
        [
            'nombre_campo' => 'recomendaciones',
            'etiqueta' => 'Recomendaciones y Plan de Tratamiento',
            'tipo_campo' => 'summernote',
            'orden' => 9,
            'grupo_seccion' => 'tratamiento'
        ],
        [
            'nombre_campo' => 'fecha_siguiente_control',
            'etiqueta' => 'Fecha de Siguiente Control',
            'tipo_campo' => 'date',
            'orden' => 10,
            'grupo_seccion' => 'seguimiento'
        ]
    ];
    
    foreach ($camposFormulario as $campoData) {
        $campoDatos = [
            'tipo_formulario_id' => $tipoFormularioId,
            'nombre_campo' => $campoData['nombre_campo'],
            'etiqueta' => $campoData['etiqueta'],
            'tipo_campo_id' => $tiposCampos[$campoData['tipo_campo']] ?? $tiposCampos['text'],
            'placeholder' => $campoData['placeholder'] ?? '',
            'orden_visualizacion' => $campoData['orden'],
            'requerido' => $campoData['requerido'] ?? false ? 1 : 0,
            'validaciones' => $campoData['validaciones'] ?? null,
            'grupo_seccion' => $campoData['grupo_seccion'],
            'activo' => 1,
            'created_by' => 1
        ];
        
        // Verificar si ya existe
        $stmt = $pdo->prepare("SELECT id FROM formulario_campos WHERE tipo_formulario_id = ? AND nombre_campo = ?");
        $stmt->execute([$tipoFormularioId, $campoData['nombre_campo']]);
        $campoExiste = $stmt->fetchColumn();
        
        if (!$campoExiste) {
            $resultado = ModelReferenciales::mdlIngresarRegistro('formulario_campos', $campoDatos);
            if ($resultado == 'ok') {
                mostrarResultado("✅ Campo '{$campoData['etiqueta']}' creado", 'success');
            }
        } else {
            mostrarResultado("ℹ️ Campo '{$campoData['etiqueta']}' ya existe", 'info');
        }
    }
    
    // 4. Mostrar resumen
    echo "<h2>📊 Resumen del Sistema Dinámico</h2>";
    
    $estadisticas = ModelReferenciales::mdlObtenerEstadisticas();
    
    echo "<div style='background-color: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
    echo "<h3>🎯 Sistema de Referenciales Dinámicos Configurado</h3>";
    echo "<ul>";
    echo "<li><strong>Tipos de Formularios:</strong> {$estadisticas['tipos_formularios']}</li>";
    echo "<li><strong>Tipos de Campos:</strong> {$estadisticas['tipos_campos']}</li>";
    echo "<li><strong>Campos de Formularios:</strong> {$estadisticas['campos_formularios']}</li>";
    echo "<li><strong>Referenciales:</strong> {$estadisticas['referenciales']}</li>";
    echo "<li><strong>Valores de Referenciales:</strong> {$estadisticas['valores_referenciales']}</li>";
    echo "</ul>";
    echo "</div>";
    
    echo "<h2>🚀 Próximos Pasos</h2>";
    echo "<ol>";
    echo "<li>Acceder al módulo de <a href='index.php?ruta=referenciales'>Gestión de Referenciales</a></li>";
    echo "<li>Revisar los <a href='index.php?ruta=tipos-formularios'>Tipos de Formularios</a> creados</li>";
    echo "<li>Configurar los <a href='index.php?ruta=valores-referenciales'>Valores de Referenciales</a></li>";
    echo "<li>Modificar los formularios de consulta para usar los campos dinámicos</li>";
    echo "</ol>";
    
    echo "<div style='background-color: #d4edda; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h4>✅ ¡Sistema Generado Exitosamente!</h4>";
    echo "<p>El formulario dinámico de Cardiología ha sido creado con campos específicos y referenciales asociados.</p>";
    echo "<p><strong>Formulario creado:</strong> Cardiología Avanzada</p>";
    echo "<p><strong>Campos incluidos:</strong> 10 campos organizados en 5 secciones</p>";
    echo "<p><strong>Referenciales:</strong> 3 referenciales específicos de cardiología</p>";
    echo "</div>";
    
    echo "<p><a href='index.php' class='btn btn-primary'>🏠 Volver al Sistema</a></p>";
    
} catch (Exception $e) {
    mostrarResultado("❌ Error crítico: " . $e->getMessage(), 'error');
    echo "<p>Por favor, revise la configuración y vuelva a intentar.</p>";
}

echo "</body></html>";
?>
