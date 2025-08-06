<?php
/**
 * Script para insertar datos de prueba en el sistema de referenciales dinámicos
 * Este script agrega datos de ejemplo para probar todas las funcionalidades
 */

require_once "model/conexion.php";

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
    
    echo "<html><head><title>Insertar Datos de Prueba - Referenciales</title></head><body>";
    echo "<h1>🧪 Insertando Datos de Prueba</h1>";
    echo "<p>Agregando datos de ejemplo para probar el sistema de referenciales dinámicos...</p>";
    
    $pdo->beginTransaction();
    
    // ===== TIPOS DE FORMULARIOS ADICIONALES =====
    mostrarResultado("Insertando tipos de formularios adicionales...", 'info');
    
    $tiposFormularios = [
        ['nombre' => 'Consulta Cardiológica', 'codigo' => 'consulta_cardio', 'descripcion' => 'Formulario para consultas de cardiología'],
        ['nombre' => 'Consulta Pediátrica', 'codigo' => 'consulta_pediatria', 'descripcion' => 'Formulario para consultas pediátricas'],
        ['nombre' => 'Examen de Laboratorio', 'codigo' => 'examen_lab', 'descripcion' => 'Formulario para órdenes de laboratorio'],
        ['nombre' => 'Receta Médica', 'codigo' => 'receta_medica', 'descripcion' => 'Formulario para prescripciones médicas']
    ];
    
    foreach ($tiposFormularios as $tipo) {
        $stmt = $pdo->prepare("
            INSERT INTO tipos_formularios (nombre, codigo, descripcion, activo) 
            VALUES (:nombre, :codigo, :descripcion, 1)
            ON CONFLICT (codigo) DO NOTHING
        ");
        $stmt->execute($tipo);
    }
    mostrarResultado("✅ Tipos de formularios insertados", 'success');
    
    // ===== TIPOS DE CAMPOS ADICIONALES =====
    mostrarResultado("Insertando tipos de campos adicionales...", 'info');
    
    $tiposCampos = [
        ['nombre' => 'Email', 'codigo' => 'email', 'descripcion' => 'Campo de email con validación', 'html_input_type' => 'email', 'requiere_opciones' => 'false'],
        ['nombre' => 'Teléfono', 'codigo' => 'tel', 'descripcion' => 'Campo de teléfono', 'html_input_type' => 'tel', 'requiere_opciones' => 'false'],
        ['nombre' => 'Archivo', 'codigo' => 'file', 'descripcion' => 'Campo para subir archivos', 'html_input_type' => 'file', 'requiere_opciones' => 'false'],
        ['nombre' => 'Rango', 'codigo' => 'range', 'descripcion' => 'Campo de rango numérico', 'html_input_type' => 'range', 'requiere_opciones' => 'false'],
        ['nombre' => 'Color', 'codigo' => 'color', 'descripcion' => 'Selector de color', 'html_input_type' => 'color', 'requiere_opciones' => 'false'],
        ['nombre' => 'Hora', 'codigo' => 'time', 'descripcion' => 'Selector de hora', 'html_input_type' => 'time', 'requiere_opciones' => 'false']
    ];
    
    foreach ($tiposCampos as $tipo) {
        $stmt = $pdo->prepare("
            INSERT INTO tipos_campos (nombre, codigo, descripcion, html_input_type, requiere_opciones, activo) 
            VALUES (:nombre, :codigo, :descripcion, :html_input_type, :requiere_opciones::boolean, 1)
            ON CONFLICT (codigo) DO NOTHING
        ");
        $stmt->execute($tipo);
    }
    mostrarResultado("✅ Tipos de campos insertados", 'success');
    
    // ===== REFERENCIALES ADICIONALES =====
    mostrarResultado("Insertando referenciales adicionales...", 'info');
    
    $referenciales = [
        ['nombre' => 'Tipos de Sangre', 'codigo' => 'tipos_sangre', 'categoria' => 'medicina_general', 'descripcion' => 'Tipos de sangre del sistema ABO y Rh'],
        ['nombre' => 'Presión Arterial', 'codigo' => 'presion_arterial', 'categoria' => 'cardiologia', 'descripcion' => 'Rangos de presión arterial'],
        ['nombre' => 'Frecuencia Cardíaca', 'codigo' => 'frecuencia_cardiaca', 'categoria' => 'cardiologia', 'descripcion' => 'Rangos de frecuencia cardíaca'],
        ['nombre' => 'IMC Categorías', 'codigo' => 'imc_categorias', 'categoria' => 'medicina_general', 'descripcion' => 'Categorías del índice de masa corporal'],
        ['nombre' => 'Vías de Administración', 'codigo' => 'vias_administracion', 'categoria' => 'farmacologia', 'descripcion' => 'Vías de administración de medicamentos'],
        ['nombre' => 'Frecuencia Medicamentos', 'codigo' => 'frecuencia_medicamentos', 'categoria' => 'farmacologia', 'descripcion' => 'Frecuencias de administración de medicamentos'],
        ['nombre' => 'Estados de Consulta', 'codigo' => 'estados_consulta', 'categoria' => 'administracion', 'descripcion' => 'Estados posibles de una consulta médica']
    ];
    
    foreach ($referenciales as $ref) {
        $stmt = $pdo->prepare("
            INSERT INTO referenciales (nombre, codigo, categoria, descripcion, activo) 
            VALUES (:nombre, :codigo, :categoria, :descripcion, 1)
            ON CONFLICT (codigo) DO NOTHING
        ");
        $stmt->execute($ref);
    }
    mostrarResultado("✅ Referenciales insertados", 'success');
    
    // ===== VALORES DE REFERENCIALES =====
    mostrarResultado("Insertando valores de referenciales...", 'info');
    
    // Tipos de sangre
    $stmt = $pdo->prepare("SELECT id FROM referenciales WHERE codigo = 'tipos_sangre'");
    $stmt->execute();
    $refSangre = $stmt->fetch();
    
    if ($refSangre) {
        $tiposSangre = [
            ['valor' => 'O+', 'etiqueta' => 'O Positivo', 'orden' => 1],
            ['valor' => 'O-', 'etiqueta' => 'O Negativo', 'orden' => 2],
            ['valor' => 'A+', 'etiqueta' => 'A Positivo', 'orden' => 3],
            ['valor' => 'A-', 'etiqueta' => 'A Negativo', 'orden' => 4],
            ['valor' => 'B+', 'etiqueta' => 'B Positivo', 'orden' => 5],
            ['valor' => 'B-', 'etiqueta' => 'B Negativo', 'orden' => 6],
            ['valor' => 'AB+', 'etiqueta' => 'AB Positivo', 'orden' => 7],
            ['valor' => 'AB-', 'etiqueta' => 'AB Negativo', 'orden' => 8]
        ];
        
        foreach ($tiposSangre as $tipo) {
            $stmt = $pdo->prepare("
                INSERT INTO referencial_valores (referencial_id, valor, etiqueta, orden_visualizacion, activo) 
                VALUES (:ref_id, :valor, :etiqueta, :orden, 1)
            ");
            $stmt->execute([
                'ref_id' => $refSangre['id'],
                'valor' => $tipo['valor'],
                'etiqueta' => $tipo['etiqueta'],
                'orden' => $tipo['orden']
            ]);
        }
    }
    
    // Presión arterial
    $stmt = $pdo->prepare("SELECT id FROM referenciales WHERE codigo = 'presion_arterial'");
    $stmt->execute();
    $refPresion = $stmt->fetch();
    
    if ($refPresion) {
        $presionValues = [
            ['valor' => 'normal', 'etiqueta' => 'Normal (< 120/80)', 'orden' => 1],
            ['valor' => 'elevada', 'etiqueta' => 'Elevada (120-129/<80)', 'orden' => 2],
            ['valor' => 'hipertension_1', 'etiqueta' => 'Hipertensión Etapa 1 (130-139/80-89)', 'orden' => 3],
            ['valor' => 'hipertension_2', 'etiqueta' => 'Hipertensión Etapa 2 (≥140/≥90)', 'orden' => 4],
            ['valor' => 'crisis', 'etiqueta' => 'Crisis Hipertensiva (>180/>120)', 'orden' => 5]
        ];
        
        foreach ($presionValues as $presion) {
            $stmt = $pdo->prepare("
                INSERT INTO referencial_valores (referencial_id, valor, etiqueta, orden_visualizacion, activo) 
                VALUES (:ref_id, :valor, :etiqueta, :orden, 1)
            ");
            $stmt->execute([
                'ref_id' => $refPresion['id'],
                'valor' => $presion['valor'],
                'etiqueta' => $presion['etiqueta'],
                'orden' => $presion['orden']
            ]);
        }
    }
    
    // Vías de administración
    $stmt = $pdo->prepare("SELECT id FROM referenciales WHERE codigo = 'vias_administracion'");
    $stmt->execute();
    $refVias = $stmt->fetch();
    
    if ($refVias) {
        $vias = [
            ['valor' => 'oral', 'etiqueta' => 'Oral (VO)', 'orden' => 1],
            ['valor' => 'sublingual', 'etiqueta' => 'Sublingual (SL)', 'orden' => 2],
            ['valor' => 'intramuscular', 'etiqueta' => 'Intramuscular (IM)', 'orden' => 3],
            ['valor' => 'intravenosa', 'etiqueta' => 'Intravenosa (IV)', 'orden' => 4],
            ['valor' => 'subcutanea', 'etiqueta' => 'Subcutánea (SC)', 'orden' => 5],
            ['valor' => 'topica', 'etiqueta' => 'Tópica', 'orden' => 6],
            ['valor' => 'inhalatoria', 'etiqueta' => 'Inhalatoria', 'orden' => 7],
            ['valor' => 'oftalmica', 'etiqueta' => 'Oftálmica', 'orden' => 8],
            ['valor' => 'otica', 'etiqueta' => 'Ótica', 'orden' => 9],
            ['valor' => 'nasal', 'etiqueta' => 'Nasal', 'orden' => 10]
        ];
        
        foreach ($vias as $via) {
            $stmt = $pdo->prepare("
                INSERT INTO referencial_valores (referencial_id, valor, etiqueta, orden_visualizacion, activo) 
                VALUES (:ref_id, :valor, :etiqueta, :orden, 1)
            ");
            $stmt->execute([
                'ref_id' => $refVias['id'],
                'valor' => $via['valor'],
                'etiqueta' => $via['etiqueta'],
                'orden' => $via['orden']
            ]);
        }
    }
    
    // Frecuencia de medicamentos
    $stmt = $pdo->prepare("SELECT id FROM referenciales WHERE codigo = 'frecuencia_medicamentos'");
    $stmt->execute();
    $refFrecuencia = $stmt->fetch();
    
    if ($refFrecuencia) {
        $frecuencias = [
            ['valor' => 'c4h', 'etiqueta' => 'Cada 4 horas', 'orden' => 1],
            ['valor' => 'c6h', 'etiqueta' => 'Cada 6 horas', 'orden' => 2],
            ['valor' => 'c8h', 'etiqueta' => 'Cada 8 horas', 'orden' => 3],
            ['valor' => 'c12h', 'etiqueta' => 'Cada 12 horas', 'orden' => 4],
            ['valor' => 'c24h', 'etiqueta' => 'Cada 24 horas (una vez al día)', 'orden' => 5],
            ['valor' => 'bid', 'etiqueta' => 'BID (dos veces al día)', 'orden' => 6],
            ['valor' => 'tid', 'etiqueta' => 'TID (tres veces al día)', 'orden' => 7],
            ['valor' => 'qid', 'etiqueta' => 'QID (cuatro veces al día)', 'orden' => 8],
            ['valor' => 'prn', 'etiqueta' => 'PRN (según necesidad)', 'orden' => 9]
        ];
        
        foreach ($frecuencias as $freq) {
            $stmt = $pdo->prepare("
                INSERT INTO referencial_valores (referencial_id, valor, etiqueta, orden_visualizacion, activo) 
                VALUES (:ref_id, :valor, :etiqueta, :orden, 1)
            ");
            $stmt->execute([
                'ref_id' => $refFrecuencia['id'],
                'valor' => $freq['valor'],
                'etiqueta' => $freq['etiqueta'],
                'orden' => $freq['orden']
            ]);
        }
    }
    
    // Estados de consulta
    $stmt = $pdo->prepare("SELECT id FROM referenciales WHERE codigo = 'estados_consulta'");
    $stmt->execute();
    $refEstados = $stmt->fetch();
    
    if ($refEstados) {
        $estados = [
            ['valor' => 'programada', 'etiqueta' => 'Programada', 'orden' => 1],
            ['valor' => 'en_curso', 'etiqueta' => 'En Curso', 'orden' => 2],
            ['valor' => 'completada', 'etiqueta' => 'Completada', 'orden' => 3],
            ['valor' => 'cancelada', 'etiqueta' => 'Cancelada', 'orden' => 4],
            ['valor' => 'no_asistio', 'etiqueta' => 'No Asistió', 'orden' => 5],
            ['valor' => 'reprogramada', 'etiqueta' => 'Reprogramada', 'orden' => 6]
        ];
        
        foreach ($estados as $estado) {
            $stmt = $pdo->prepare("
                INSERT INTO referencial_valores (referencial_id, valor, etiqueta, orden_visualizacion, activo) 
                VALUES (:ref_id, :valor, :etiqueta, :orden, 1)
            ");
            $stmt->execute([
                'ref_id' => $refEstados['id'],
                'valor' => $estado['valor'],
                'etiqueta' => $estado['etiqueta'],
                'orden' => $estado['orden']
            ]);
        }
    }
    
    mostrarResultado("✅ Valores de referenciales insertados", 'success');
    
    // ===== CAMPOS DE FORMULARIOS DE EJEMPLO =====
    mostrarResultado("Insertando campos de formularios de ejemplo...", 'info');
    
    // Obtener IDs de tipos de formularios y campos
    $stmt = $pdo->prepare("SELECT id FROM tipos_formularios WHERE codigo = 'consulta_cardio'");
    $stmt->execute();
    $formCardio = $stmt->fetch();
    
    $stmt = $pdo->prepare("SELECT id FROM tipos_campos WHERE codigo = 'select'");
    $stmt->execute();
    $tipoSelect = $stmt->fetch();
    
    $stmt = $pdo->prepare("SELECT id FROM tipos_campos WHERE codigo = 'number'");
    $stmt->execute();
    $tipoNumber = $stmt->fetch();
    
    if ($formCardio && $tipoSelect && $tipoNumber) {
        $camposCardio = [
            [
                'tipo_formulario_id' => $formCardio['id'],
                'nombre_campo' => 'presion_sistolica',
                'etiqueta' => 'Presión Sistólica',
                'tipo_campo_id' => $tipoNumber['id'],
                'placeholder' => 'Ej: 120',
                'orden_visualizacion' => 1,
                'requerido' => 1,
                'grupo_seccion' => 'signos_vitales'
            ],
            [
                'tipo_formulario_id' => $formCardio['id'],
                'nombre_campo' => 'presion_diastolica',
                'etiqueta' => 'Presión Diastólica',
                'tipo_campo_id' => $tipoNumber['id'],
                'placeholder' => 'Ej: 80',
                'orden_visualizacion' => 2,
                'requerido' => 1,
                'grupo_seccion' => 'signos_vitales'
            ],
            [
                'tipo_formulario_id' => $formCardio['id'],
                'nombre_campo' => 'categoria_presion',
                'etiqueta' => 'Categoría de Presión',
                'tipo_campo_id' => $tipoSelect['id'],
                'orden_visualizacion' => 3,
                'requerido' => 0,
                'grupo_seccion' => 'signos_vitales'
            ]
        ];
        
        foreach ($camposCardio as $campo) {
            $stmt = $pdo->prepare("
                INSERT INTO formulario_campos (
                    tipo_formulario_id, nombre_campo, etiqueta, tipo_campo_id, 
                    placeholder, orden_visualizacion, requerido, grupo_seccion, activo
                ) VALUES (
                    :tipo_formulario_id, :nombre_campo, :etiqueta, :tipo_campo_id,
                    :placeholder, :orden_visualizacion, :requerido, :grupo_seccion, 1
                )
            ");
            $stmt->execute($campo);
        }
    }
    mostrarResultado("✅ Campos de formularios insertados", 'success');
    
    // ===== CONFIGURACIONES DE FORMULARIOS =====
    mostrarResultado("Insertando configuraciones de formularios...", 'info');
    
    if ($formCardio) {
        $configuraciones = [
            [
                'tipo_formulario_id' => $formCardio['id'],
                'clave' => 'validacion_presion',
                'valor' => '{"min_sistolica": 70, "max_sistolica": 250, "min_diastolica": 40, "max_diastolica": 150}',
                'descripcion' => 'Rangos de validación para presión arterial'
            ],
            [
                'tipo_formulario_id' => $formCardio['id'],
                'clave' => 'campos_obligatorios',
                'valor' => '["presion_sistolica", "presion_diastolica"]',
                'descripcion' => 'Lista de campos obligatorios para consulta cardiológica'
            ],
            [
                'tipo_formulario_id' => $formCardio['id'],
                'clave' => 'alertas_automaticas',
                'valor' => '{"presion_alta": "≥140/≥90", "presion_baja": "≤90/≤60"}',
                'descripcion' => 'Configuración de alertas automáticas'
            ]
        ];
        
        foreach ($configuraciones as $config) {
            $stmt = $pdo->prepare("
                INSERT INTO formulario_configuraciones (tipo_formulario_id, clave, valor, descripcion, activo) 
                VALUES (:tipo_formulario_id, :clave, :valor, :descripcion, 1)
            ");
            $stmt->execute($config);
        }
    }
    mostrarResultado("✅ Configuraciones de formularios insertadas", 'success');
    
    $pdo->commit();
    
    // ===== RESUMEN FINAL =====
    echo "<h2>📊 Resumen de Datos Insertados</h2>";
    
    $tablas = [
        'tipos_formularios' => 'Tipos de Formularios',
        'tipos_campos' => 'Tipos de Campos',
        'referenciales' => 'Referenciales',
        'referencial_valores' => 'Valores de Referenciales',
        'formulario_campos' => 'Campos de Formularios',
        'formulario_configuraciones' => 'Configuraciones de Formularios'
    ];
    
    echo "<table border='1' cellpadding='10' cellspacing='0' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background-color: #f8f9fa;'>";
    echo "<th>Tabla</th><th>Total de Registros</th><th>Descripción</th>";
    echo "</tr>";
    
    foreach ($tablas as $tabla => $descripcion) {
        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM {$tabla}");
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            echo "<tr>";
            echo "<td><strong>{$tabla}</strong></td>";
            echo "<td style='text-align: center;'><span class='badge badge-primary'>{$result['total']}</span></td>";
            echo "<td>{$descripcion}</td>";
            echo "</tr>";
        } catch (Exception $e) {
            echo "<tr>";
            echo "<td><strong>{$tabla}</strong></td>";
            echo "<td style='text-align: center;'><span class='badge badge-danger'>Error</span></td>";
            echo "<td>Error al contar registros</td>";
            echo "</tr>";
        }
    }
    echo "</table>";
    
    echo "<h2>🎯 ¡Datos de Prueba Insertados Exitosamente!</h2>";
    echo "<p><strong>El sistema está listo para ser probado con datos reales.</strong></p>";
    
    echo "<h3>🔗 Enlaces de Prueba:</h3>";
    echo "<ul>";
    echo "<li><a href='index.php?ruta=referenciales' target='_blank'>📋 Gestión de Referenciales</a></li>";
    echo "<li><a href='index.php?ruta=valores-referenciales' target='_blank'>📝 Gestión de Valores</a></li>";
    echo "<li><a href='index.php?ruta=tipos-formularios' target='_blank'>📄 Tipos de Formularios</a></li>";
    echo "<li><a href='index.php?ruta=campos-formularios' target='_blank'>🏗️ Campos de Formularios</a></li>";
    echo "<li><a href='index.php?ruta=configuraciones-formularios' target='_blank'>⚙️ Configuraciones</a></li>";
    echo "</ul>";
    
    echo "<p><a href='index.php' class='btn btn-primary'>🏠 Volver al Inicio</a></p>";
    
} catch (Exception $e) {
    $pdo->rollback();
    mostrarResultado("❌ Error crítico: " . $e->getMessage(), 'error');
    echo "<p>Por favor, revise la configuración de la base de datos y vuelva a intentar.</p>";
}

echo "</body></html>";
?>
