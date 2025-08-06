<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>✅ VERIFICACIÓN FINAL SIMPLIFICADA</h1>";

try {
    // Incluir los archivos necesarios
    require_once('model/conexion.php');
    require_once('model/formularios_dinamicos.model.php');
    
    // Establecer conexión
    $pdo = Conexion::conectar();
    
    if (!$pdo) {
        throw new Exception("No se pudo establecer conexión con la base de datos");
    }
    
    echo "<h2>🔗 Conexión exitosa a la base de datos</h2>";
    
    echo "<h2>📊 Estado del Referencial de Esfera</h2>";
    
    // Verificar valores en referencial_valores
    $stmt = $pdo->query("
        SELECT r.nombre, r.codigo, rv.valor, rv.etiqueta, rv.orden_visualizacion
        FROM referenciales r 
        INNER JOIN referencial_valores rv ON r.id = rv.referencial_id 
        WHERE r.codigo = 'valores_esfera' AND rv.activo = 1
        ORDER BY rv.orden_visualizacion
    ");
    $valoresDB = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>Valores en la base de datos (" . count($valoresDB) . " registros):</h3>";
    
    if (count($valoresDB) > 0) {
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr style='background: #f8f9fa;'><th>Orden</th><th>Valor</th><th>Etiqueta</th></tr>";
        foreach ($valoresDB as $valor) {
            echo "<tr>";
            echo "<td style='padding: 5px;'>{$valor['orden_visualizacion']}</td>";
            echo "<td style='padding: 5px;'>{$valor['valor']}</td>";
            echo "<td style='padding: 5px;'>{$valor['etiqueta']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: red;'>❌ No se encontraron valores en el referencial</p>";
    }
    
    echo "<h2>🧪 Prueba del SELECT Dinámico</h2>";
    
    // Generar el select usando FormulariosDinamicos
    $htmlGenerado = FormulariosDinamicos::generarSelectReferencial('valores_esfera', 'test_esfera', 'test_esfera');
    
    echo "<h3>HTML Generado:</h3>";
    echo "<div style='background: #f8f9fa; padding: 10px; border: 1px solid #ddd; margin: 10px 0;'>";
    echo $htmlGenerado;
    echo "</div>";
    
    echo "<h3>Código HTML:</h3>";
    echo "<pre style='background: #f1f1f1; padding: 10px; overflow-x: auto;'>" . htmlspecialchars($htmlGenerado) . "</pre>";
    
    // Contar opciones
    preg_match_all('/<option[^>]*>([^<]*)<\/option>/', $htmlGenerado, $matches);
    $totalOpciones = count($matches[0]);
    
    echo "<h2>📈 Resumen Final</h2>";
    
    $valoresEnDB = count($valoresDB);
    $esCorrectoValores = ($valoresEnDB == 7);
    $esCorrectoOpciones = ($totalOpciones == 8); // 7 valores + 1 placeholder
    
    if ($esCorrectoValores && $esCorrectoOpciones) {
        echo "<div style='background: #d4edda; padding: 15px; border: 2px solid #28a745; border-radius: 5px; margin: 10px 0;'>";
        echo "<h3 style='color: #155724; margin-top: 0;'>🎉 ¡PROBLEMA COMPLETAMENTE RESUELTO!</h3>";
        echo "<ul style='color: #155724;'>";
        echo "<li>✅ Base de datos: {$valoresEnDB} valores (correcto)</li>";
        echo "<li>✅ HTML generado: {$totalOpciones} opciones (7 valores + 1 placeholder)</li>";
        echo "<li>✅ Ya no hay valores masivos extra</li>";
        echo "<li>✅ El sistema dinámico funciona perfectamente</li>";
        echo "</ul>";
        echo "</div>";
    } else {
        echo "<div style='background: #f8d7da; padding: 15px; border: 2px solid #dc3545; border-radius: 5px; margin: 10px 0;'>";
        echo "<h3 style='color: #721c24; margin-top: 0;'>⚠️ Revisar Estado</h3>";
        echo "<ul style='color: #721c24;'>";
        echo "<li>Base de datos: {$valoresEnDB} valores " . ($esCorrectoValores ? "✅" : "❌ (esperados: 7)") . "</li>";
        echo "<li>HTML generado: {$totalOpciones} opciones " . ($esCorrectoOpciones ? "✅" : "❌ (esperadas: 8)") . "</li>";
        echo "</ul>";
        echo "</div>";
    }
    
    echo "<h2>🔗 Acceso al Sistema</h2>";
    echo "<p>Para verificar en el sistema real:</p>";
    echo "<p><a href='http://localhost/clinica/servicios' target='_blank' style='display: inline-block; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px;'>Abrir Sistema Principal</a></p>";
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 15px; border: 2px solid #dc3545; border-radius: 5px; margin: 10px 0;'>";
    echo "<h3 style='color: #721c24; margin-top: 0;'>❌ Error:</h3>";
    echo "<p style='color: #721c24;'>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
    
    // Información adicional para debug
    echo "<h3>Información de Debug:</h3>";
    echo "<ul>";
    echo "<li>Archivo actual: " . __FILE__ . "</li>";
    echo "<li>Directorio actual: " . __DIR__ . "</li>";
    echo "<li>PHP Version: " . phpversion() . "</li>";
    echo "</ul>";
}
?>
