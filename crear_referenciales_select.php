<?php
/**
 * Crear referenciales para los campos SELECT del formulario de anteojos
 */

require_once "model/conexion.php";

try {
    $pdo = Conexion::conectar();
    $pdo->beginTransaction();
    
    echo "<h1>🎯 Creando Referenciales para Campos SELECT</h1>";
    
    // 1. Crear referencial para MOTIVOS COMUNES
    echo "<h2>📋 1. Motivos Comunes de Consulta</h2>";
    
    $stmt = $pdo->prepare("
        INSERT INTO referenciales (nombre, codigo, descripcion, activo) 
        VALUES ('Motivos Comunes', 'motivos_comunes', 'Motivos comunes de consulta oftalmológica', true)
        ON CONFLICT (codigo) DO NOTHING
    ");
    $stmt->execute();
    
    $stmt = $pdo->prepare("SELECT id FROM referenciales WHERE codigo = 'motivos_comunes'");
    $stmt->execute();
    $refMotivos = $stmt->fetch();
    
    if ($refMotivos) {
        echo "<p>✅ Referencial 'motivos_comunes' creado con ID: {$refMotivos['id']}</p>";
        
        $motivosComunes = [
            ['revision_rutina', 'Revisión de rutina', 1],
            ['problemas_vision', 'Problemas de visión', 2],
            ['dolor_ocular', 'Dolor ocular', 3],
            ['vision_borrosa', 'Visión borrosa', 4],
            ['sequedad_ocular', 'Sequedad ocular', 5],
            ['cambio_lentes', 'Cambio de lentes', 6],
            ['control_glaucoma', 'Control de glaucoma', 7],
            ['control_diabetico', 'Control diabético', 8],
            ['irritacion_ocular', 'Irritación ocular', 9],
            ['fatiga_visual', 'Fatiga visual', 10],
            ['examen_conducir', 'Examen para conducir', 11],
            ['seguimiento_cirugia', 'Seguimiento post-cirugía', 12]
        ];
        
        foreach ($motivosComunes as $motivo) {
            $stmt = $pdo->prepare("
                INSERT INTO referencial_valores (referencial_id, valor, etiqueta, orden_visualizacion, activo) 
                VALUES (:ref_id, :valor, :etiqueta, :orden, true)
                ON CONFLICT (referencial_id, valor) DO NOTHING
            ");
            $stmt->bindParam(':ref_id', $refMotivos['id']);
            $stmt->bindParam(':valor', $motivo[0]);
            $stmt->bindParam(':etiqueta', $motivo[1]);
            $stmt->bindParam(':orden', $motivo[2]);
            $stmt->execute();
        }
        
        echo "<p>✅ " . count($motivosComunes) . " motivos comunes insertados</p>";
    }
    
    // 2. Crear referencial para PREFORMATOS DE CONSULTA
    echo "<h2>📄 2. Preformatos de Consulta</h2>";
    
    $stmt = $pdo->prepare("
        INSERT INTO referenciales (nombre, codigo, descripcion, activo) 
        VALUES ('Preformatos de Consulta', 'preformatos_consulta', 'Plantillas predefinidas para consultas oftalmológicas', true)
        ON CONFLICT (codigo) DO NOTHING
    ");
    $stmt->execute();
    
    $stmt = $pdo->prepare("SELECT id FROM referenciales WHERE codigo = 'preformatos_consulta'");
    $stmt->execute();
    $refPreformatos = $stmt->fetch();
    
    if ($refPreformatos) {
        echo "<p>✅ Referencial 'preformatos_consulta' creado con ID: {$refPreformatos['id']}</p>";
        
        $preformatosConsulta = [
            ['consulta_general', 'Consulta General Oftalmológica', 1],
            ['revision_anteojos', 'Revisión para Anteojos', 2],
            ['control_glaucoma', 'Control de Glaucoma', 3],
            ['examen_retinopatia', 'Examen de Retinopatía Diabética', 4],
            ['consulta_pediatrica', 'Consulta Pediátrica', 5],
            ['cirugia_catarata', 'Evaluación para Cirugía de Catarata', 6],
            ['lentes_contacto', 'Adaptación de Lentes de Contacto', 7],
            ['urgencia_ocular', 'Urgencia Oftalmológica', 8]
        ];
        
        foreach ($preformatosConsulta as $preformato) {
            $stmt = $pdo->prepare("
                INSERT INTO referencial_valores (referencial_id, valor, etiqueta, orden_visualizacion, activo) 
                VALUES (:ref_id, :valor, :etiqueta, :orden, true)
                ON CONFLICT (referencial_id, valor) DO NOTHING
            ");
            $stmt->bindParam(':ref_id', $refPreformatos['id']);
            $stmt->bindParam(':valor', $preformato[0]);
            $stmt->bindParam(':etiqueta', $preformato[1]);
            $stmt->bindParam(':orden', $preformato[2]);
            $stmt->execute();
        }
        
        echo "<p>✅ " . count($preformatosConsulta) . " preformatos de consulta insertados</p>";
    }
    
    // 3. Crear referencial para PREFORMATOS DE RECETA
    echo "<h2>📝 3. Preformatos de Receta</h2>";
    
    $stmt = $pdo->prepare("
        INSERT INTO referenciales (nombre, codigo, descripcion, activo) 
        VALUES ('Preformatos de Receta', 'preformatos_receta', 'Plantillas predefinidas para recetas oftalmológicas', true)
        ON CONFLICT (codigo) DO NOTHING
    ");
    $stmt->execute();
    
    $stmt = $pdo->prepare("SELECT id FROM referenciales WHERE codigo = 'preformatos_receta'");
    $stmt->execute();
    $refRecetas = $stmt->fetch();
    
    if ($refRecetas) {
        echo "<p>✅ Referencial 'preformatos_receta' creado con ID: {$refRecetas['id']}</p>";
        
        $preformatosReceta = [
            ['receta_anteojos', 'Receta para Anteojos', 1],
            ['receta_bifocales', 'Receta para Lentes Bifocales', 2],
            ['receta_progresivos', 'Receta para Lentes Progresivos', 3],
            ['receta_lectura', 'Receta solo para Lectura', 4],
            ['receta_distancia', 'Receta solo para Distancia', 5],
            ['receta_lentes_contacto', 'Receta para Lentes de Contacto', 6],
            ['medicamentos', 'Receta de Medicamentos', 7],
            ['control_seguimiento', 'Control y Seguimiento', 8]
        ];
        
        foreach ($preformatosReceta as $receta) {
            $stmt = $pdo->prepare("
                INSERT INTO referencial_valores (referencial_id, valor, etiqueta, orden_visualizacion, activo) 
                VALUES (:ref_id, :valor, :etiqueta, :orden, true)
                ON CONFLICT (referencial_id, valor) DO NOTHING
            ");
            $stmt->bindParam(':ref_id', $refRecetas['id']);
            $stmt->bindParam(':valor', $receta[0]);
            $stmt->bindParam(':etiqueta', $receta[1]);
            $stmt->bindParam(':orden', $receta[2]);
            $stmt->execute();
        }
        
        echo "<p>✅ " . count($preformatosReceta) . " preformatos de receta insertados</p>";
    }
    
    $pdo->commit();
    
    echo "<h2>🎉 ¡Referenciales para SELECT Completados!</h2>";
    echo "<div style='background-color: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3>✅ Referenciales SELECT Creados:</h3>";
    echo "<ul>";
    echo "<li><strong>motivos_comunes</strong> - Para el campo 'Motivos comunes'</li>";
    echo "<li><strong>preformatos_consulta</strong> - Para el campo 'Preformato'</li>";
    echo "<li><strong>preformatos_receta</strong> - Para el campo 'Preformato de receta'</li>";
    echo "</ul>";
    echo "<p><strong>Ahora puedes convertir todos los campos SELECT a dinámicos!</strong></p>";
    echo "</div>";
    
    // Resumen final
    echo "<h3>📊 Resumen de TODOS los Referenciales SELECT:</h3>";
    echo "<table border='1' cellpadding='8' cellspacing='0' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background-color: #f8f9fa;'>";
    echo "<th>Campo SELECT</th><th>Código Referencial</th><th>Estado</th></tr>";
    
    $camposSelect = [
        ['Esfera (OD/OI)', 'valores_esfera', '✅ YA DINÁMICO'],
        ['Cilindro (OD/OI)', 'valores_cilindro', '✅ YA DINÁMICO'],
        ['Adición (OD/OI)', 'valores_adicion', '✅ YA DINÁMICO'],
        ['Motivos comunes', 'motivos_comunes', '🆕 NUEVO - Listo para convertir'],
        ['Preformato', 'preformatos_consulta', '🆕 NUEVO - Listo para convertir'],
        ['Preformato de receta', 'preformatos_receta', '🆕 NUEVO - Listo para convertir']
    ];
    
    foreach ($camposSelect as $campo) {
        echo "<tr>";
        echo "<td><strong>{$campo[0]}</strong></td>";
        echo "<td><code>{$campo[1]}</code></td>";
        echo "<td>{$campo[2]}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
} catch (Exception $e) {
    $pdo->rollback();
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>
