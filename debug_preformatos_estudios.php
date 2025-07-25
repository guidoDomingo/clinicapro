<?php
header('Content-Type: text/html; charset=UTF-8');
require_once 'model/conexion.php';

echo "<!DOCTYPE html>";
echo "<html><head><meta charset='UTF-8'><title>Debug Preformatos Estudios</title>";
echo "<style>body{font-family:monospace;margin:20px;} .section{margin:20px 0; padding:15px; border:1px solid #ccc;} .error{color:red;} .success{color:green;} .info{color:blue;}</style>";
echo "</head><body>";

echo "<h1>🔍 Debug: Preformatos para Formulario Estudios</h1>";

try {
    $pdo = Conexion::conectar();
    
    if ($pdo === null) {
        throw new Exception("No se pudo conectar a la base de datos");
    }
    
    echo "<div class='section'>";
    echo "<h2>📊 1. Verificar todos los preformatos en la base de datos</h2>";
    
    $sql = "SELECT id_preformato, nombre, tipo_formulario, tipo, activo, fecha_creacion 
            FROM preformatos 
            ORDER BY tipo_formulario, nombre";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $todos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<p><strong>Total de preformatos:</strong> " . count($todos) . "</p>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID</th><th>Nombre</th><th>Tipo Formulario</th><th>Tipo</th><th>Activo</th><th>Fecha</th></tr>";
    
    foreach ($todos as $preformato) {
        $class = $preformato['tipo_formulario'] === 'estudios' ? 'style="background-color: #ffffcc;"' : '';
        echo "<tr {$class}>";
        echo "<td>{$preformato['id_preformato']}</td>";
        echo "<td>{$preformato['nombre']}</td>";
        echo "<td><strong>{$preformato['tipo_formulario']}</strong></td>";
        echo "<td>{$preformato['tipo']}</td>";
        echo "<td>" . ($preformato['activo'] ? 'Sí' : 'No') . "</td>";
        echo "<td>{$preformato['fecha_creacion']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    echo "</div>";
    
    // Específicamente para estudios
    echo "<div class='section'>";
    echo "<h2>🔬 2. Preformatos específicos para 'estudios'</h2>";
    
    $sqlEstudios = "SELECT * FROM preformatos 
                    WHERE tipo_formulario = 'estudios' 
                    AND activo = true 
                    ORDER BY nombre";
    
    $stmtEstudios = $pdo->prepare($sqlEstudios);
    $stmtEstudios->execute();
    $estudios = $stmtEstudios->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<p class='info'><strong>Preformatos activos para estudios:</strong> " . count($estudios) . "</p>";
    
    if (count($estudios) > 0) {
        echo "<ul>";
        foreach ($estudios as $estudio) {
            echo "<li><strong>{$estudio['nombre']}</strong> (ID: {$estudio['id_preformato']}, Tipo: {$estudio['tipo']})</li>";
        }
        echo "</ul>";
    } else {
        echo "<p class='error'>❌ NO HAY PREFORMATOS PARA ESTUDIOS</p>";
        echo "<p>Esto explica por qué no aparecen en el formulario.</p>";
    }
    echo "</div>";
    
    // Test del endpoint AJAX
    echo "<div class='section'>";
    echo "<h2>🌐 3. Test del endpoint AJAX</h2>";
    
    // Simular la llamada AJAX
    $_POST['operacion'] = 'getPreformatosConsulta';
    $_POST['tipo_formulario'] = 'estudios';
    $_POST['usuario_id'] = 1;
    
    echo "<p><strong>Simulando llamada AJAX:</strong></p>";
    echo "<pre>";
    echo "POST ajax/preformatos.ajax.php\n";
    echo "operacion: getPreformatosConsulta\n";
    echo "tipo_formulario: estudios\n";
    echo "usuario_id: 1\n";
    echo "</pre>";
    
    // Capturar la salida del archivo AJAX
    ob_start();
    if (file_exists('ajax/preformatos.ajax.php')) {
        include 'ajax/preformatos.ajax.php';
    } else {
        echo "❌ Archivo ajax/preformatos.ajax.php no encontrado";
    }
    $ajax_output = ob_get_clean();
    
    echo "<p><strong>Respuesta del endpoint:</strong></p>";
    echo "<pre style='background:#f5f5f5; padding:10px;'>";
    echo htmlspecialchars($ajax_output);
    echo "</pre>";
    echo "</div>";
    
    // Verificar archivos JavaScript
    echo "<div class='section'>";
    echo "<h2>📁 4. Verificar archivos JavaScript</h2>";
    
    $jsFiles = [
        'view/js/cargar_datos.js',
        'view/js/consultas.js',
        'view/js/preformatos.js'
    ];
    
    foreach ($jsFiles as $jsFile) {
        echo "<p><strong>{$jsFile}:</strong> ";
        if (file_exists($jsFile)) {
            echo "<span class='success'>✅ Existe</span>";
            
            $content = file_get_contents($jsFile);
            if (strpos($content, 'estudios') !== false) {
                echo " <span class='info'>(contiene 'estudios')</span>";
            }
            if (strpos($content, 'tipo_formulario') !== false) {
                echo " <span class='info'>(contiene 'tipo_formulario')</span>";
            }
        } else {
            echo "<span class='error'>❌ No existe</span>";
        }
        echo "</p>";
    }
    echo "</div>";
    
    // Verificar inclusión de scripts en consultas.php
    echo "<div class='section'>";
    echo "<h2>📄 5. Verificar inclusión de scripts en consultas.php</h2>";
    
    if (file_exists('view/modules/consultas.php')) {
        $consultasContent = file_get_contents('view/modules/consultas.php');
        
        echo "<p><strong>Scripts incluidos en consultas.php:</strong></p>";
        echo "<ul>";
        
        $scripts = ['cargar_datos.js', 'consultas.js', 'preformatos.js'];
        foreach ($scripts as $script) {
            if (strpos($consultasContent, $script) !== false) {
                echo "<li class='success'>✅ {$script}</li>";
            } else {
                echo "<li class='error'>❌ {$script}</li>";
            }
        }
        echo "</ul>";
        
        // Buscar la función cambiarFormulario
        if (strpos($consultasContent, 'cambiarFormulario') !== false) {
            echo "<p class='success'>✅ Función cambiarFormulario encontrada</p>";
        } else {
            echo "<p class='error'>❌ Función cambiarFormulario NO encontrada</p>";
        }
        
    } else {
        echo "<p class='error'>❌ Archivo view/modules/consultas.php no encontrado</p>";
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
echo "<h2>🚀 Siguiente paso</h2>";
echo "<p>Basado en los resultados anteriores, podemos identificar exactamente qué está causando el problema.</p>";
echo "<a href='consultas.php' style='background:#007bff;color:white;padding:10px;text-decoration:none;'>🧪 Probar Módulo Consultas</a>";
echo "</div>";

echo "</body></html>";
?>
