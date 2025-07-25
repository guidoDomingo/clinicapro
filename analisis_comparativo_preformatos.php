<?php
header('Content-Type: text/html; charset=UTF-8');
require_once 'model/conexion.php';

echo "<!DOCTYPE html>";
echo "<html><head><meta charset='UTF-8'><title>Análisis Comparativo de Preformatos</title>";
echo "<style>body{font-family:monospace;margin:20px;} .section{margin:20px 0; padding:15px; border:1px solid #ccc;} .error{color:red;} .success{color:green;} .info{color:blue;} table{border-collapse:collapse;width:100%;} th,td{border:1px solid #ddd;padding:8px;text-align:left;} th{background-color:#f2f2f2;}</style>";
echo "</head><body>";

echo "<h1>🔍 Análisis Comparativo: Por qué funcionan otros formularios</h1>";

try {
    $pdo = Conexion::conectar();
    
    if ($pdo === null) {
        throw new Exception("No se pudo conectar a la base de datos");
    }
    
    echo "<div class='section'>";
    echo "<h2>📊 1. Preformatos por tipo de formulario</h2>";
    
    $sql = "SELECT tipo_formulario, tipo, COUNT(*) as cantidad, 
                   string_agg(nombre, ', ') as nombres
            FROM preformatos 
            WHERE activo = true 
            GROUP BY tipo_formulario, tipo 
            ORDER BY tipo_formulario, tipo";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $resumen = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table>";
    echo "<tr><th>Tipo Formulario</th><th>Tipo Preformato</th><th>Cantidad</th><th>Nombres</th></tr>";
    
    foreach ($resumen as $row) {
        $class = ($row['tipo_formulario'] === 'estudios') ? 'style="background-color: #ffffcc;"' : '';
        echo "<tr {$class}>";
        echo "<td><strong>{$row['tipo_formulario']}</strong></td>";
        echo "<td>{$row['tipo']}</td>";
        echo "<td>{$row['cantidad']}</td>";
        echo "<td>" . substr($row['nombres'], 0, 100) . (strlen($row['nombres']) > 100 ? '...' : '') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    echo "</div>";
    
    // Verificar qué tipos específicos se están buscando
    echo "<div class='section'>";
    echo "<h2>🔬 2. Test de llamadas AJAX específicas</h2>";
    
    $tipos_formulario = ['general', 'anteojos', 'estudios'];
    $tipos_preformato = ['consulta', 'receta'];
    
    foreach ($tipos_formulario as $tipo_form) {
        echo "<h3>Formulario: {$tipo_form}</h3>";
        
        foreach ($tipos_preformato as $tipo_pref) {
            echo "<h4>Buscando preformatos tipo '{$tipo_pref}' para formulario '{$tipo_form}':</h4>";
            
            $sqlTest = "SELECT id_preformato, nombre, tipo, tipo_formulario 
                       FROM preformatos 
                       WHERE tipo = :tipo 
                       AND tipo_formulario = :tipo_formulario 
                       AND activo = true 
                       ORDER BY nombre";
            
            $stmtTest = $pdo->prepare($sqlTest);
            $stmtTest->bindParam(':tipo', $tipo_pref);
            $stmtTest->bindParam(':tipo_formulario', $tipo_form);
            $stmtTest->execute();
            $resultados = $stmtTest->fetchAll(PDO::FETCH_ASSOC);
            
            echo "<p><strong>Query:</strong> <code>tipo = '{$tipo_pref}' AND tipo_formulario = '{$tipo_form}'</code></p>";
            echo "<p><strong>Resultados:</strong> " . count($resultados) . " preformatos</p>";
            
            if (count($resultados) > 0) {
                echo "<ul>";
                foreach ($resultados as $res) {
                    echo "<li>{$res['nombre']} (ID: {$res['id_preformato']})</li>";
                }
                echo "</ul>";
            } else {
                echo "<p class='error'>❌ No se encontraron preformatos</p>";
            }
            echo "<hr>";
        }
    }
    echo "</div>";
    
    // Verificar la lógica JavaScript
    echo "<div class='section'>";
    echo "<h2>🧪 3. Test en vivo de llamadas AJAX</h2>";
    echo "<p>Vamos a hacer las mismas llamadas que hace el JavaScript:</p>";
    
    echo "<h3>Para formulario 'anteojos' (que funciona):</h3>";
    echo "<div id='test-anteojos'></div>";
    
    echo "<h3>Para formulario 'estudios' (que no funciona):</h3>";
    echo "<div id='test-estudios'></div>";
    
    echo "<h3>Para formulario 'general' (referencia):</h3>";
    echo "<div id='test-general'></div>";
    
    echo "</div>";
    
    // Verificar operaciones específicas
    echo "<div class='section'>";
    echo "<h2>🔍 4. Verificar operaciones AJAX disponibles</h2>";
    
    // Leer el archivo preformatos.ajax.php para ver qué operaciones están disponibles
    if (file_exists('ajax/preformatos.ajax.php')) {
        $contenido = file_get_contents('ajax/preformatos.ajax.php');
        
        echo "<h3>Operaciones disponibles en preformatos.ajax.php:</h3>";
        
        // Buscar casos en el switch
        preg_match_all("/case\s+'([^']+)':/", $contenido, $matches);
        $operaciones = $matches[1];
        
        echo "<ul>";
        foreach ($operaciones as $op) {
            echo "<li><code>{$op}</code></li>";
        }
        echo "</ul>";
        
        // Verificar si hay lógica especial para anteojos
        if (strpos($contenido, 'anteojos') !== false) {
            echo "<p class='info'>✅ Se encontró lógica específica para 'anteojos'</p>";
        }
        
        if (strpos($contenido, 'estudios') !== false) {
            echo "<p class='info'>✅ Se encontró lógica específica para 'estudios'</p>";
        } else {
            echo "<p class='error'>❌ No se encontró lógica específica para 'estudios'</p>";
        }
    }
    echo "</div>";
    
    echo "<div class='section'>";
    echo "<h2>🎯 5. Próximos pasos</h2>";
    echo "<p>Basado en este análisis, podemos identificar exactamente qué está causando la diferencia.</p>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div class='section error'>";
    echo "<h2>❌ Error</h2>";
    echo "<p>{$e->getMessage()}</p>";
    echo "</div>";
}

// Script JavaScript para hacer tests en vivo
echo "<script src='https://code.jquery.com/jquery-3.6.0.min.js'></script>";
echo "<script>";
echo "
function testAjax(tipoFormulario, elementId) {
    console.log('Testing AJAX for: ' + tipoFormulario);
    
    // Test para preformatos de consulta
    const formDataConsulta = new FormData();
    formDataConsulta.append('operacion', 'getPreformatosConsulta');
    formDataConsulta.append('tipo_formulario', tipoFormulario);
    formDataConsulta.append('usuario_id', '1');
    
    $.ajax({
        type: 'POST',
        url: 'ajax/preformatos.ajax.php',
        data: formDataConsulta,
        dataType: 'json',
        processData: false,
        contentType: false,
        success: function(response) {
            const div = document.getElementById(elementId);
            let html = '<h4>getPreformatosConsulta para ' + tipoFormulario + ':</h4>';
            html += '<p><strong>Status:</strong> ' + response.status + '</p>';
            
            if (response.status === 'success' && response.data) {
                html += '<p><strong>Cantidad:</strong> ' + response.data.length + '</p>';
                if (response.data.length > 0) {
                    html += '<ul>';
                    response.data.forEach(function(item) {
                        html += '<li>' + item.nombre + ' (Tipo: ' + item.tipo + ', Tipo Formulario: ' + item.tipo_formulario + ')</li>';
                    });
                    html += '</ul>';
                } else {
                    html += '<p style=\"color:red;\">❌ No hay datos</p>';
                }
            } else {
                html += '<p style=\"color:red;\">❌ Error: ' + (response.message || 'Sin mensaje') + '</p>';
            }
            
            div.innerHTML = html;
            
            console.log('Response for ' + tipoFormulario + ':', response);
        },
        error: function(xhr, status, error) {
            const div = document.getElementById(elementId);
            div.innerHTML = '<p style=\"color:red;\">❌ Error AJAX: ' + error + '</p>';
            console.error('AJAX Error for ' + tipoFormulario + ':', error);
        }
    });
}

// Ejecutar tests cuando la página esté lista
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(() => {
        testAjax('anteojos', 'test-anteojos');
        testAjax('estudios', 'test-estudios');
        testAjax('general', 'test-general');
    }, 1000);
});
";
echo "</script>";

echo "</body></html>";
?>
