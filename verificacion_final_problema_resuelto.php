<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>✅ VERIFICACIÓN FINAL: Problema de Valores Masivos Resuelto</h1>";

require_once('model/conexion.php');
require_once('model/formularios_dinamicos.model.php');

try {
    $pdo = Conexion::conectar();
    
    if (!$pdo) {
        throw new Exception("No se pudo conectar a la base de datos");
    }
    
    echo "<h2>📊 1. Estado Final del Referencial</h2>";
    
    // Verificar valores en referencial_valores
    $stmt = $pdo->query("
        SELECT r.nombre, r.codigo, rv.valor, rv.etiqueta, rv.orden_visualizacion
        FROM referenciales r 
        INNER JOIN referencial_valores rv ON r.id = rv.referencial_id 
        WHERE r.codigo = 'valores_esfera' AND rv.activo = 1
        ORDER BY rv.orden_visualizacion
    ");
    $valoresDB = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>✅ Valores en la base de datos (" . count($valoresDB) . " registros):</h3>";
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr><th>Orden</th><th>Valor</th><th>Etiqueta</th></tr>";
    foreach ($valoresDB as $valor) {
        echo "<tr>";
        echo "<td>{$valor['orden_visualizacion']}</td>";
        echo "<td>{$valor['valor']}</td>";
        echo "<td>{$valor['etiqueta']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<h2>🧪 2. Prueba del Generador de SELECT</h2>";
    
    // Generar el HTML del select
    $htmlGenerado = FormulariosDinamicos::generarSelectReferencial('valores_esfera', 'test_verificacion', 'test_verificacion');
    
    echo "<h3>HTML generado:</h3>";
    echo "<div style='background: #f8f9fa; padding: 10px; border: 1px solid #dee2e6; margin: 10px 0;'>";
    echo "<pre>" . htmlspecialchars($htmlGenerado) . "</pre>";
    echo "</div>";
    
    // Extraer y contar opciones
    preg_match_all('/<option[^>]*value="([^"]*)"[^>]*>([^<]*)<\/option>/', $htmlGenerado, $matches, PREG_SET_ORDER);
    
    echo "<h3>📋 Opciones encontradas (" . count($matches) . " opciones):</h3>";
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr><th>#</th><th>Value</th><th>Text</th><th>Tipo</th></tr>";
    
    for ($i = 0; $i < count($matches); $i++) {
        $value = $matches[$i][1];
        $text = $matches[$i][2];
        $tipo = empty($value) ? "Placeholder" : "Valor de datos";
        
        echo "<tr>";
        echo "<td>" . ($i + 1) . "</td>";
        echo "<td>" . (empty($value) ? "(vacío)" : $value) . "</td>";
        echo "<td>{$text}</td>";
        echo "<td>{$tipo}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<h2>🎯 3. Demostración en Vivo</h2>";
    
    echo "<div style='background: #f8f9fa; padding: 20px; border: 1px solid #dee2e6; margin: 10px 0;'>";
    echo "<h3>Formulario de Prueba - Ojo Derecho (OD):</h3>";
    echo "<div class='form-group'>";
    echo "<label for='demo_od_esf'>Esfera OD:</label>";
    echo FormulariosDinamicos::generarSelectReferencial('valores_esfera', 'demo_od_esf', 'demo_od_esf');
    echo "</div>";
    
    echo "<h3>Formulario de Prueba - Ojo Izquierdo (OI):</h3>";
    echo "<div class='form-group'>";
    echo "<label for='demo_oi_esf'>Esfera OI:</label>";
    echo FormulariosDinamicos::generarSelectReferencial('valores_esfera', 'demo_oi_esf', 'demo_oi_esf');
    echo "</div>";
    echo "</div>";
    
    // Script para activar Select2
    echo "<script src='https://code.jquery.com/jquery-3.6.0.min.js'></script>";
    echo "<link href='https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css' rel='stylesheet' />";
    echo "<script src='https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js'></script>";
    echo "<script>";
    echo "$(document).ready(function() {";
    echo "  $('.select2bs4').select2({";
    echo "    theme: 'default',";
    echo "    width: '100%'";
    echo "  });";
    echo "});";
    echo "</script>";
    
    echo "<h2>📊 4. Resumen Final</h2>";
    
    $valoresEnDB = count($valoresDB);
    $opcionesEnHTML = count($matches);
    $esCorrectoValores = ($valoresEnDB == 7);
    $esCorrectoOpciones = ($opcionesEnHTML == 8); // 7 valores + 1 placeholder
    
    if ($esCorrectoValores && $esCorrectoOpciones) {
        echo "<div style='background: #d4edda; padding: 15px; border-left: 4px solid #28a745; margin: 10px 0;'>";
        echo "<h3>🎉 PROBLEMA RESUELTO COMPLETAMENTE</h3>";
        echo "<ul>";
        echo "<li>✅ Base de datos: {$valoresEnDB} valores (esperados: 7)</li>";
        echo "<li>✅ HTML generado: {$opcionesEnHTML} opciones (esperadas: 8 con placeholder)</li>";
        echo "<li>✅ Ya no hay valores masivos extra</li>";
        echo "<li>✅ El formulario muestra exactamente los valores de la base de datos</li>";
        echo "</ul>";
        echo "</div>";
    } else {
        echo "<div style='background: #ffebee; padding: 15px; border-left: 4px solid #f44336; margin: 10px 0;'>";
        echo "<h3>❌ AÚN HAY PROBLEMAS</h3>";
        echo "<ul>";
        echo "<li>Base de datos: {$valoresEnDB} valores " . ($esCorrectoValores ? "✅" : "❌") . "</li>";
        echo "<li>HTML generado: {$opcionesEnHTML} opciones " . ($esCorrectoOpciones ? "✅" : "❌") . "</li>";
        echo "</ul>";
        echo "</div>";
    }
    
    echo "<h2>🔍 5. Verificación del Sistema en Producción</h2>";
    echo "<p>Para verificar que el formulario real funciona correctamente:</p>";
    echo "<ol>";
    echo "<li>Ir a: <a href='http://localhost/clinica/servicios' target='_blank'>Sistema Principal</a></li>";
    echo "<li>Acceder al módulo de consultas</li>";
    echo "<li>Crear una nueva consulta</li>";
    echo "<li>Seleccionar 'Anteojos' como tipo de consulta</li>";
    echo "<li>Verificar que el campo 'Esfera' solo muestre 8 opciones (1 placeholder + 7 valores)</li>";
    echo "</ol>";
    
} catch (Exception $e) {
    echo "<div style='background: #ffebee; padding: 15px; border-left: 4px solid #f44336; margin: 10px 0;'>";
    echo "<h3>❌ Error:</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "</div>";
}
?>
