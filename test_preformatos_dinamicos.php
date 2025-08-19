<?php
/**
 * PRUEBA DE CARGA DINÁMICA DE PREFORMATOS
 * Test para verificar que la nueva funcionalidad funciona correctamente
 */

require_once 'model/conexion.php';

echo "<h1>🧪 Prueba de Preformatos Dinámicos</h1>";

// Simular datos de prueba
$userId = 9;
$tipoFormulario = 'anteojos';
$preformatoId = 41;

echo "<h2>📋 Datos de prueba</h2>";
echo "<ul>";
echo "<li><strong>User ID:</strong> $userId</li>";
echo "<li><strong>Tipo de Formulario:</strong> $tipoFormulario</li>";
echo "<li><strong>Preformato ID:</strong> $preformatoId</li>";
echo "</ul>";

// Test 1: Probar carga de lista de preformatos con file_get_contents
echo "<h2>🔍 Test 1: Carga de lista de preformatos (via HTTP)</h2>";

try {
    $postData = http_build_query([
        'action' => 'get_preformatos_consulta',
        'tipo_formulario' => $tipoFormulario,
        'tipo' => 'consulta',
        'usuario_id' => $userId
    ]);
    
    $context = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => 'Content-Type: application/x-www-form-urlencoded',
            'content' => $postData
        ]
    ]);
    
    $response1 = file_get_contents('http://localhost/clinica/modules/consultas/api/consultas-api.php', false, $context);
    
    if ($response1 === false) {
        throw new Exception('Error al llamar API');
    }
    
    echo "<div style='background: #d4edda; padding: 10px; border: 1px solid #c3e6cb; border-radius: 5px;'>";
    echo "<h4>✅ Respuesta HTTP para getPreformatos:</h4>";
    echo "<pre>" . htmlspecialchars($response1) . "</pre>";
    echo "</div>";
    
    // Decodificar JSON para análisis
    $data1 = json_decode($response1, true);
    if (json_last_error() === JSON_ERROR_NONE && is_array($data1)) {
        echo "<div style='background: #d1ecf1; padding: 10px; border: 1px solid #bee5eb; border-radius: 5px;'>";
        echo "<h5>📊 Análisis de la respuesta:</h5>";
        echo "<ul>";
        echo "<li><strong>Success:</strong> " . ($data1['success'] ? 'true' : 'false') . "</li>";
        if (isset($data1['data'])) {
            echo "<li><strong>Data count:</strong> " . count($data1['data']) . "</li>";
        }
        if (isset($data1['message'])) {
            echo "<li><strong>Message:</strong> " . htmlspecialchars($data1['message']) . "</li>";
        }
        echo "</ul>";
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 10px; border: 1px solid #f5c6cb; border-radius: 5px;'>";
    echo "<h4>❌ Error en Test 1:</h4>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}

// Test 2: Probar carga de contenido específico
echo "<h2>📄 Test 2: Carga de contenido específico (via HTTP)</h2>";

try {
    $postData2 = http_build_query([
        'action' => 'get_preformato_content',
        'preformato_id' => $preformatoId,
        'usuario_id' => $userId,
        'tipo_formulario' => $tipoFormulario
    ]);
    
    $context2 = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => 'Content-Type: application/x-www-form-urlencoded',
            'content' => $postData2
        ]
    ]);
    
    $response2 = file_get_contents('http://localhost/clinica/modules/consultas/api/consultas-api.php', false, $context2);
    
    if ($response2 === false) {
        throw new Exception('Error al llamar API');
    }
    
    echo "<div style='background: #d4edda; padding: 10px; border: 1px solid #c3e6cb; border-radius: 5px;'>";
    echo "<h4>✅ Respuesta HTTP para getPreformatoContent:</h4>";
    echo "<pre>" . htmlspecialchars($response2) . "</pre>";
    echo "</div>";
    
    // Decodificar JSON para análisis
    $data2 = json_decode($response2, true);
    if (json_last_error() === JSON_ERROR_NONE && is_array($data2)) {
        echo "<div style='background: #d1ecf1; padding: 10px; border: 1px solid #bee5eb; border-radius: 5px;'>";
        echo "<h5>📊 Análisis de la respuesta:</h5>";
        echo "<ul>";
        echo "<li><strong>Success:</strong> " . ($data2['success'] ? 'true' : 'false') . "</li>";
        if (isset($data2['contenido'])) {
            echo "<li><strong>Contenido length:</strong> " . strlen($data2['contenido']) . "</li>";
        }
        if (isset($data2['message'])) {
            echo "<li><strong>Message:</strong> " . htmlspecialchars($data2['message']) . "</li>";
        }
        echo "</ul>";
        echo "</div>";
        
        // Mostrar contenido si existe
        if ($data2['success'] && isset($data2['contenido'])) {
            echo "<div style='background: #f8f9fa; padding: 10px; border: 1px solid #dee2e6; border-radius: 5px; margin-top: 10px;'>";
            echo "<h5>📄 Contenido del preformato:</h5>";
            echo "<div style='max-height: 200px; overflow-y: auto; background: white; padding: 10px; border: 1px solid #ccc;'>";
            echo htmlspecialchars($data2['contenido']);
            echo "</div>";
            echo "</div>";
        }
    }
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 10px; border: 1px solid #f5c6cb; border-radius: 5px;'>";
    echo "<h4>❌ Error en Test 2:</h4>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}

// Test 3: Verificar estructura de base de datos
echo "<h2>🗄️ Test 3: Verificación directa de base de datos</h2>";

try {
    $conexion = Conexion::conectar();
    
    // Verificar preformato específico
    $stmt = $conexion->prepare("SELECT id_preformato, nombre, contenido, tipo_formulario, tipo FROM preformatos WHERE id_preformato = ?");
    $stmt->execute([$preformatoId]);
    $preformato = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($preformato) {
        echo "<div style='background: #d4edda; padding: 10px; border: 1px solid #c3e6cb; border-radius: 5px;'>";
        echo "<h4>✅ Preformato encontrado</h4>";
        echo "<ul>";
        echo "<li><strong>ID:</strong> " . $preformato['id_preformato'] . "</li>";
        echo "<li><strong>Nombre:</strong> " . htmlspecialchars($preformato['nombre']) . "</li>";
        echo "<li><strong>Tipo Formulario:</strong> " . $preformato['tipo_formulario'] . "</li>";
        echo "<li><strong>Tipo:</strong> " . $preformato['tipo'] . "</li>";
        echo "<li><strong>Contenido (primeros 100 chars):</strong> " . htmlspecialchars(substr($preformato['contenido'], 0, 100)) . "...</li>";
        echo "</ul>";
        echo "</div>";
    } else {
        echo "<div style='background: #f8d7da; padding: 10px; border: 1px solid #f5c6cb; border-radius: 5px;'>";
        echo "<h4>❌ Preformato no encontrado</h4>";
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 10px; border: 1px solid #f5c6cb; border-radius: 5px;'>";
    echo "<h4>❌ Error de base de datos</h4>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}

echo "<h2>🚀 Sistema listo para pruebas</h2>";
echo "<p>Ahora puedes ir a: <strong><a href='index.php?ruta=consultas-new'>Consultas → Nueva Consulta</a></strong></p>";
echo "<p>Y probar los selects de preformatos con carga dinámica.</p>";
?>
