<?php
header('Content-Type: text/html; charset=UTF-8');
require_once 'model/conexion.php';

echo "<!DOCTYPE html>";
echo "<html><head><meta charset='UTF-8'><title>Verificar y Crear Preformatos Estudios</title>";
echo "<style>body{font-family:monospace;margin:20px;} .section{margin:20px 0; padding:15px; border:1px solid #ccc;} .error{color:red;} .success{color:green;} .info{color:blue;}</style>";
echo "</head><body>";

echo "<h1>🔍 Verificar y Crear Preformatos para Estudios</h1>";

try {
    $pdo = Conexion::conectar();
    
    if ($pdo === null) {
        throw new Exception("No se pudo conectar a la base de datos");
    }
    
    echo "<div class='section'>";
    echo "<h2>📊 1. Verificar preformatos existentes para estudios</h2>";
    
    $sql = "SELECT * FROM preformatos WHERE tipo_formulario = 'estudios' AND activo = true ORDER BY nombre";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $estudios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<p><strong>Preformatos existentes para estudios:</strong> " . count($estudios) . "</p>";
    
    if (count($estudios) > 0) {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>ID</th><th>Nombre</th><th>Tipo</th><th>Contenido</th></tr>";
        foreach ($estudios as $estudio) {
            echo "<tr>";
            echo "<td>{$estudio['id_preformato']}</td>";
            echo "<td>{$estudio['nombre']}</td>";
            echo "<td>{$estudio['tipo']}</td>";
            echo "<td>" . substr($estudio['contenido'], 0, 100) . "...</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p class='error'>❌ NO HAY PREFORMATOS PARA ESTUDIOS</p>";
        echo "<p class='info'>Vamos a crear algunos preformatos de ejemplo...</p>";
        
        // Crear preformatos de ejemplo para estudios
        $preformatosEstudios = [
            [
                'nombre' => 'Consulta Oftalmológica Estándar',
                'tipo' => 'consulta',
                'contenido' => "CONSULTA OFTALMOLÓGICA\n\nPaciente: [NOMBRE_PACIENTE]\nFecha: [FECHA]\n\nMOTIVO DE CONSULTA:\n\nEXAMEN OFTALMOLÓGICO:\n- Agudeza Visual: OD: ___ OI: ___\n- Presión Intraocular: OD: ___ mmHg OI: ___ mmHg\n- Fondo de Ojo:\n- Biomicroscopía:\n\nESTUDIOS REALIZADOS:\n\nIMPRESIÓN DIAGNÓSTICA:\n\nPLAN DE TRATAMIENTO:\n\nPróxima consulta: ___________\n\nDr. [NOMBRE_DOCTOR]\nMatrícula: [MATRICULA]"
            ],
            [
                'nombre' => 'Orden de Estudios Complementarios',
                'tipo' => 'receta',
                'contenido' => "ORDEN DE ESTUDIOS COMPLEMENTARIOS\n\nPaciente: [NOMBRE_PACIENTE]\nFecha: [FECHA]\n\nSe solicita realizar los siguientes estudios:\n\n□ Campo Visual\n□ OCT (Tomografía de Coherencia Óptica)\n□ Angiografía Fluoresceínica\n□ Ecografía Ocular\n□ Topografía Corneal\n□ Paquimetría\n□ Biometría\n□ Otros: _______________\n\nIndicaciones especiales:\n\nUrgencia: □ Normal □ Urgente\n\nDr. [NOMBRE_DOCTOR]\nMatrícula: [MATRICULA]"
            ],
            [
                'nombre' => 'Reporte de Estudios',
                'tipo' => 'consulta',
                'contenido' => "REPORTE DE ESTUDIOS OFTALMOLÓGICOS\n\nPaciente: [NOMBRE_PACIENTE]\nFecha del estudio: [FECHA]\n\nESTUDIO REALIZADO:\n\nEQUIPO UTILIZADO:\n\nRESULTADOS:\n\nHALLAZGOS RELEVANTES:\n\nCONCLUSIONES:\n\nRECOMENDACIONES:\n\nDr. [NOMBRE_DOCTOR]\nMatrícula: [MATRICULA]"
            ]
        ];
        
        echo "<h3>Creando preformatos de ejemplo...</h3>";
        
        foreach ($preformatosEstudios as $preformato) {
            try {
                $sqlInsert = "INSERT INTO preformatos (nombre, contenido, tipo, tipo_formulario, activo, fecha_creacion, creado_por) 
                             VALUES (:nombre, :contenido, :tipo, 'estudios', true, NOW(), 1)";
                
                $stmtInsert = $pdo->prepare($sqlInsert);
                $stmtInsert->bindParam(':nombre', $preformato['nombre']);
                $stmtInsert->bindParam(':contenido', $preformato['contenido']);
                $stmtInsert->bindParam(':tipo', $preformato['tipo']);
                
                if ($stmtInsert->execute()) {
                    echo "<p class='success'>✅ Creado: {$preformato['nombre']}</p>";
                } else {
                    echo "<p class='error'>❌ Error al crear: {$preformato['nombre']}</p>";
                }
            } catch (Exception $e) {
                echo "<p class='error'>❌ Error al crear {$preformato['nombre']}: " . $e->getMessage() . "</p>";
            }
        }
    }
    echo "</div>";
    
    // Verificar nuevamente después de crear
    echo "<div class='section'>";
    echo "<h2>📊 2. Verificación después de crear preformatos</h2>";
    
    $sql = "SELECT * FROM preformatos WHERE tipo_formulario = 'estudios' AND activo = true ORDER BY nombre";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $estudiosActualizados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<p><strong>Total de preformatos para estudios ahora:</strong> " . count($estudiosActualizados) . "</p>";
    
    if (count($estudiosActualizados) > 0) {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>ID</th><th>Nombre</th><th>Tipo</th><th>Tipo Formulario</th><th>Activo</th></tr>";
        foreach ($estudiosActualizados as $estudio) {
            echo "<tr>";
            echo "<td>{$estudio['id_preformato']}</td>";
            echo "<td>{$estudio['nombre']}</td>";
            echo "<td>{$estudio['tipo']}</td>";
            echo "<td><strong>{$estudio['tipo_formulario']}</strong></td>";
            echo "<td>" . ($estudio['activo'] ? 'Sí' : 'No') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    echo "</div>";
    
    // Test del endpoint AJAX específicamente para estudios
    echo "<div class='section'>";
    echo "<h2>🧪 3. Test del endpoint AJAX para estudios</h2>";
    
    // Simular llamada AJAX para preformatos de consulta de estudios
    echo "<h3>Test: getPreformatosConsulta con tipo_formulario = 'estudios'</h3>";
    
    $sqlTest = "SELECT * FROM preformatos 
                WHERE tipo = 'consulta' 
                AND tipo_formulario = 'estudios' 
                AND activo = true 
                ORDER BY nombre";
    
    $stmtTest = $pdo->prepare($sqlTest);
    $stmtTest->execute();
    $resultadoTest = $stmtTest->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<p><strong>Query ejecutada:</strong></p>";
    echo "<pre>{$sqlTest}</pre>";
    echo "<p><strong>Resultados:</strong> " . count($resultadoTest) . " preformatos encontrados</p>";
    
    if (count($resultadoTest) > 0) {
        echo "<ul>";
        foreach ($resultadoTest as $item) {
            echo "<li><strong>{$item['nombre']}</strong> (ID: {$item['id_preformato']}, Tipo: {$item['tipo']}, Tipo Formulario: {$item['tipo_formulario']})</li>";
        }
        echo "</ul>";
    }
    
    // Test para preformatos de receta de estudios
    echo "<h3>Test: getPreformatosReceta con tipo_formulario = 'estudios'</h3>";
    
    $sqlTestReceta = "SELECT * FROM preformatos 
                      WHERE tipo = 'receta' 
                      AND tipo_formulario = 'estudios' 
                      AND activo = true 
                      ORDER BY nombre";
    
    $stmtTestReceta = $pdo->prepare($sqlTestReceta);
    $stmtTestReceta->execute();
    $resultadoTestReceta = $stmtTestReceta->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<p><strong>Query ejecutada:</strong></p>";
    echo "<pre>{$sqlTestReceta}</pre>";
    echo "<p><strong>Resultados:</strong> " . count($resultadoTestReceta) . " preformatos encontrados</p>";
    
    if (count($resultadoTestReceta) > 0) {
        echo "<ul>";
        foreach ($resultadoTestReceta as $item) {
            echo "<li><strong>{$item['nombre']}</strong> (ID: {$item['id_preformato']}, Tipo: {$item['tipo']}, Tipo Formulario: {$item['tipo_formulario']})</li>";
        }
        echo "</ul>";
    }
    
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div class='section error'>";
    echo "<h2>❌ Error</h2>";
    echo "<p>{$e->getMessage()}</p>";
    echo "<p>Archivo: {$e->getFile()}</p>";
    echo "<p>Línea: {$e->getLine()}</p>";
    echo "</div>";
}

echo "<div class='section'>";
echo "<h2>🚀 Próximos pasos</h2>";
echo "<p>1. Los preformatos para estudios ahora deberían estar disponibles</p>";
echo "<p>2. Prueba el módulo de consultas cambiando al formulario de estudios</p>";
echo "<p>3. Verifica que los selectores de preformatos se carguen correctamente</p>";
echo "<br>";
echo "<a href='debug_preformatos_estudios.php' style='background:#17a2b8;color:white;padding:10px;text-decoration:none;margin-right:10px;'>🔍 Debug Detallado</a>";
echo "<a href='test_consultas_preformatos.php' style='background:#28a745;color:white;padding:10px;text-decoration:none;margin-right:10px;'>🧪 Test Interactivo</a>";
echo "<a href='view/modules/consultas.php?form_type=estudios' style='background:#007bff;color:white;padding:10px;text-decoration:none;'>🔬 Módulo Consultas - Estudios</a>";
echo "</div>";

echo "</body></html>";
?>
