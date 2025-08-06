<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🔍 VERIFICACIÓN DIRECTA DEL FORMULARIO REAL</h1>";

try {
    echo "<h2>1. 🧪 SIMULACIÓN DEL FORMULARIO REAL</h2>";
    
    // Incluir exactamente los mismos archivos que usa el formulario real
    require_once('model/conexion.php');
    require_once('model/formularios_dinamicos.model.php');
    
    $pdo = Conexion::conectar();
    
    if (!$pdo) {
        throw new Exception("No se pudo establecer conexión con la base de datos");
    }
    
    echo "<h3>✅ Conexión establecida</h3>";
    
    echo "<h2>2. 📊 ESTADO ACTUAL DE LA BASE DE DATOS</h2>";
    
    // Verificar exactamente lo que ve FormulariosDinamicos
    $stmt = $pdo->query("
        SELECT rv.valor, rv.etiqueta, rv.orden_visualizacion 
        FROM referencial_valores rv
        INNER JOIN referenciales r ON rv.referencial_id = r.id
        WHERE r.codigo = 'valores_esfera' AND r.activo = 1 AND rv.activo = 1
        ORDER BY rv.orden_visualizacion, rv.etiqueta
    ");
    $valoresBD = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>Valores en BD para 'valores_esfera' (" . count($valoresBD) . " registros):</h3>";
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr style='background: #f8f9fa;'><th>#</th><th>Valor</th><th>Etiqueta</th><th>Orden</th></tr>";
    
    foreach ($valoresBD as $i => $valor) {
        echo "<tr>";
        echo "<td>" . ($i + 1) . "</td>";
        echo "<td>{$valor['valor']}</td>";
        echo "<td>{$valor['etiqueta']}</td>";
        echo "<td>{$valor['orden_visualizacion']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<h2>3. 🎯 GENERACIÓN EXACTA DEL FORMULARIO</h2>";
    
    echo "<h3>Campo OD Esfera (Ojo Derecho):</h3>";
    $htmlOD = FormulariosDinamicos::generarSelectReferencial('valores_esfera', 'od_esf', 'od_esf');
    
    echo "<div style='background: #f8f9fa; padding: 15px; border: 1px solid #ddd; margin: 10px 0;'>";
    echo "<h4>HTML generado:</h4>";
    echo $htmlOD;
    echo "</div>";
    
    echo "<h4>Código HTML:</h4>";
    echo "<pre style='background: #f1f1f1; padding: 10px; overflow-x: auto; font-size: 12px;'>" . htmlspecialchars($htmlOD) . "</pre>";
    
    echo "<h3>Campo OI Esfera (Ojo Izquierdo):</h3>";
    $htmlOI = FormulariosDinamicos::generarSelectReferencial('valores_esfera', 'oi_esf', 'oi_esf');
    
    echo "<div style='background: #f8f9fa; padding: 15px; border: 1px solid #ddd; margin: 10px 0;'>";
    echo "<h4>HTML generado:</h4>";
    echo $htmlOI;
    echo "</div>";
    
    echo "<h2>4. 📋 ANÁLISIS DE OPCIONES</h2>";
    
    // Analizar las opciones del campo OD
    preg_match_all('/<option[^>]*value="([^"]*)"[^>]*>([^<]*)<\/option>/', $htmlOD, $matchesOD, PREG_SET_ORDER);
    
    echo "<h3>Opciones en OD Esfera (" . count($matchesOD) . " opciones):</h3>";
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr style='background: #f8f9fa;'><th>#</th><th>Value</th><th>Text</th><th>Tipo</th></tr>";
    
    foreach ($matchesOD as $i => $match) {
        $value = $match[1];
        $text = $match[2];
        $tipo = empty($value) ? "Placeholder" : "Valor";
        
        echo "<tr>";
        echo "<td>" . ($i + 1) . "</td>";
        echo "<td>" . (empty($value) ? "(vacío)" : $value) . "</td>";
        echo "<td>{$text}</td>";
        echo "<td>{$tipo}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Analizar las opciones del campo OI
    preg_match_all('/<option[^>]*value="([^"]*)"[^>]*>([^<]*)<\/option>/', $htmlOI, $matchesOI, PREG_SET_ORDER);
    
    echo "<h3>Opciones en OI Esfera (" . count($matchesOI) . " opciones):</h3>";
    echo "<p>Misma cantidad que OD: " . (count($matchesOI) == count($matchesOD) ? "✅ Sí" : "❌ No") . "</p>";
    
    echo "<h2>5. 🎯 COMPARACIÓN CON FORMULARIO REAL</h2>";
    
    echo "<div style='background: #e3f2fd; padding: 15px; border: 1px solid #2196f3; border-radius: 5px; margin: 10px 0;'>";
    echo "<h3>📝 Simulación del Formulario Real de Anteojos</h3>";
    echo "<p>Exactamente como aparece en el sistema:</p>";
    
    echo "<div style='background: linear-gradient(to right,rgb(29, 140, 244),rgb(81, 157, 232)); padding: 20px; border-radius: 8px; margin: 10px 0;'>";
    echo "<h4 style='color: white;'>OD (Ojo Derecho)</h4>";
    echo "<div style='background: white; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<label for='od_esf_test' style='font-weight: bold;'>Esfera (ESF)</label><br>";
    echo str_replace('od_esf', 'od_esf_test', $htmlOD);
    echo "</div>";
    
    echo "<h4 style='color: white;'>OI (Ojo Izquierdo)</h4>";
    echo "<div style='background: white; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<label for='oi_esf_test' style='font-weight: bold;'>Esfera (ESF)</label><br>";
    echo str_replace('oi_esf', 'oi_esf_test', $htmlOI);
    echo "</div>";
    echo "</div>";
    echo "</div>";
    
    echo "<h2>6. 📊 DIAGNÓSTICO FINAL</h2>";
    
    $valoresEnBD = count($valoresBD);
    $opcionesOD = count($matchesOD);
    $opcionesOI = count($matchesOI);
    
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0; width: 100%;'>";
    echo "<tr style='background: #f8f9fa;'><th>Componente</th><th>Cantidad</th><th>Esperado</th><th>Estado</th></tr>";
    echo "<tr>";
    echo "<td>Valores en BD</td>";
    echo "<td style='font-weight: bold;'>{$valoresEnBD}</td>";
    echo "<td>7</td>";
    echo "<td>" . ($valoresEnBD == 7 ? "✅ CORRECTO" : "❌ INCORRECTO") . "</td>";
    echo "</tr>";
    echo "<tr>";
    echo "<td>Opciones OD Esfera</td>";
    echo "<td style='font-weight: bold;'>{$opcionesOD}</td>";
    echo "<td>8 (7 + placeholder)</td>";
    echo "<td>" . ($opcionesOD == 8 ? "✅ CORRECTO" : "❌ INCORRECTO") . "</td>";
    echo "</tr>";
    echo "<tr>";
    echo "<td>Opciones OI Esfera</td>";
    echo "<td style='font-weight: bold;'>{$opcionesOI}</td>";
    echo "<td>8 (7 + placeholder)</td>";
    echo "<td>" . ($opcionesOI == 8 ? "✅ CORRECTO" : "❌ INCORRECTO") . "</td>";
    echo "</tr>";
    echo "</table>";
    
    $todoCorrect = ($valoresEnBD == 7 && $opcionesOD == 8 && $opcionesOI == 8);
    
    if ($todoCorrect) {
        echo "<div style='background: #d4edda; padding: 20px; border: 3px solid #28a745; border-radius: 10px; margin: 20px 0;'>";
        echo "<h3>🎉 ¡PROBLEMA COMPLETAMENTE RESUELTO!</h3>";
        echo "<p style='font-size: 18px; font-weight: bold; color: #155724;'>El formulario de anteojos ahora muestra exactamente los valores correctos.</p>";
        echo "<ul style='font-size: 16px; color: #155724;'>";
        echo "<li>✅ Base de datos limpia: 7 valores</li>";
        echo "<li>✅ Formulario correcto: 8 opciones (7 + placeholder)</li>";
        echo "<li>✅ No más valores masivos</li>";
        echo "<li>✅ Sistema dinámico funcionando</li>";
        echo "</ul>";
        
        echo "<h4>Instrucciones para verificar en el sistema real:</h4>";
        echo "<ol>";
        echo "<li>Abrir el sistema principal</li>";
        echo "<li>Ir al módulo de consultas</li>";
        echo "<li>Crear nueva consulta → Anteojos</li>";
        echo "<li>Verificar que el campo 'Esfera' muestre solo 8 opciones</li>";
        echo "</ol>";
        
        echo "<p><a href='http://localhost/clinica/servicios' target='_blank' style='display: inline-block; padding: 15px 30px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; font-size: 16px; margin: 10px 0;'>🔗 Abrir Sistema Principal</a></p>";
        echo "</div>";
        
    } else {
        echo "<div style='background: #f8d7da; padding: 15px; border: 2px solid #dc3545; border-radius: 5px; margin: 20px 0;'>";
        echo "<h3>❌ AÚN HAY PROBLEMAS</h3>";
        
        if ($valoresEnBD != 7) {
            echo "<p>🔧 Problema en BD: Se encontraron {$valoresEnBD} valores en lugar de 7</p>";
        }
        
        if ($opcionesOD != 8 || $opcionesOI != 8) {
            echo "<p>🔧 Problema en HTML: Opciones incorrectas en formulario</p>";
        }
        
        echo "<p><strong>Próximos pasos:</strong></p>";
        echo "<ul>";
        echo "<li>Ejecutar script de limpieza forzada</li>";
        echo "<li>Limpiar cache del navegador (Ctrl+F5)</li>";
        echo "<li>Reiniciar servidor web si es necesario</li>";
        echo "</ul>";
        echo "</div>";
    }
    
    echo "<h2>7. 🔗 Enlaces de Acción</h2>";
    
    echo "<p>";
    echo "<a href='limpieza_total_cache_bd.php' style='display: inline-block; padding: 10px 20px; background: #dc3545; color: white; text-decoration: none; border-radius: 5px; margin: 5px;'>🔧 Limpieza Total</a>";
    echo "<a href='diagnostico_completo_persistente.php' style='display: inline-block; padding: 10px 20px; background: #ffc107; color: black; text-decoration: none; border-radius: 5px; margin: 5px;'>🔍 Diagnóstico</a>";
    echo "<a href='verificacion_simplificada.php' style='display: inline-block; padding: 10px 20px; background: #28a745; color: white; text-decoration: none; border-radius: 5px; margin: 5px;'>✅ Verificación</a>";
    echo "</p>";
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 15px; border: 2px solid #dc3545; border-radius: 5px;'>";
    echo "<h3>❌ Error:</h3>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}
?>

<script>
// Agregar funcionalidad Select2 para una simulación más real
document.addEventListener('DOMContentLoaded', function() {
    // Simular el comportamiento de Select2 en los selectores de prueba
    const selects = document.querySelectorAll('select[id*="test"]');
    
    selects.forEach(function(select) {
        select.style.width = '100%';
        select.style.padding = '8px';
        select.style.border = '1px solid #ced4da';
        select.style.borderRadius = '4px';
        
        // Agregar event listener para mostrar cuántas opciones tiene
        select.addEventListener('focus', function() {
            const opciones = this.querySelectorAll('option');
            console.log(`Select ${this.id} tiene ${opciones.length} opciones`);
        });
    });
});
</script>
