<?php
/**
 * Captura exacta del contenido del formulario de anteojos
 */

require_once "model/conexion.php";
require_once "model/formularios_dinamicos.model.php";

echo "<h1>🔍 Captura Exacta del Formulario de Anteojos</h1>";

try {
    // Simular la generación de los campos del formulario tal como aparecen
    echo "<h2>📋 Campos de Esfera en el Formulario</h2>";
    
    // OD Esfera
    echo "<h3>🔹 Campo OD Esfera (od_esf)</h3>";
    $odEsfera = FormulariosDinamicos::generarSelectReferencial('valores_esfera', 'od_esf', 'od_esf');
    
    echo "<h4>Código HTML generado:</h4>";
    echo "<pre style='background-color: #f0f8ff; padding: 10px; border: 1px solid #007bff;'>";
    echo htmlspecialchars($odEsfera);
    echo "</pre>";
    
    echo "<h4>Vista previa:</h4>";
    echo "<div style='border: 1px solid #007bff; padding: 10px; margin: 10px 0;'>";
    echo $odEsfera;
    echo "</div>";
    
    // Contar opciones
    $opcionesOD = substr_count($odEsfera, '<option');
    echo "<p><strong>Opciones en OD Esfera:</strong> $opcionesOD</p>";
    
    // OI Esfera
    echo "<h3>🔹 Campo OI Esfera (oi_esf)</h3>";
    $oiEsfera = FormulariosDinamicos::generarSelectReferencial('valores_esfera', 'oi_esf', 'oi_esf');
    
    echo "<h4>Código HTML generado:</h4>";
    echo "<pre style='background-color: #f0fff0; padding: 10px; border: 1px solid #28a745;'>";
    echo htmlspecialchars($oiEsfera);
    echo "</pre>";
    
    echo "<h4>Vista previa:</h4>";
    echo "<div style='border: 1px solid #28a745; padding: 10px; margin: 10px 0;'>";
    echo $oiEsfera;
    echo "</div>";
    
    $opcionesOI = substr_count($oiEsfera, '<option');
    echo "<p><strong>Opciones en OI Esfera:</strong> $opcionesOI</p>";
    
    // Verificar si son idénticos
    echo "<h2>⚖️ Comparación entre OD y OI</h2>";
    if ($odEsfera === $oiEsfera) {
        echo "<p style='color: green;'>✅ Los campos OD y OI son idénticos</p>";
    } else {
        echo "<p style='color: red;'>❌ Los campos OD y OI son diferentes</p>";
        echo "<p><strong>Diferencias detectadas</strong></p>";
    }
    
    // Mostrar resumen de opciones extraídas
    echo "<h2>📊 Resumen de Opciones</h2>";
    
    preg_match_all('/<option[^>]*value="([^"]*)"[^>]*>([^<]*)<\/option>/', $odEsfera, $matches, PREG_SET_ORDER);
    
    echo "<h3>Opciones extraídas del campo OD Esfera:</h3>";
    echo "<table border='1' cellpadding='5' cellspacing='0' style='border-collapse: collapse;'>";
    echo "<tr style='background-color: #e3f2fd;'>";
    echo "<th>#</th><th>Value</th><th>Texto Mostrado</th>";
    echo "</tr>";
    
    foreach ($matches as $i => $match) {
        $bgColor = ($i == 0) ? '#fff3e0' : '#f8f9fa'; // Primera opción diferente (Seleccionar)
        echo "<tr style='background-color: $bgColor;'>";
        echo "<td>" . ($i + 1) . "</td>";
        echo "<td><code>" . htmlspecialchars($match[1]) . "</code></td>";
        echo "<td><strong>" . htmlspecialchars(trim($match[2])) . "</strong></td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<p><strong>Total de opciones mostradas en tabla:</strong> " . count($matches) . "</p>";
    
    // Verificar valores en base de datos vs mostrados
    echo "<h2>🔍 Verificación vs Base de Datos</h2>";
    
    $pdo = Conexion::conectar();
    $stmt = $pdo->prepare("
        SELECT rv.valor, rv.etiqueta 
        FROM referencial_valores rv
        INNER JOIN referenciales r ON rv.referencial_id = r.id
        WHERE r.codigo = 'valores_esfera' AND r.activo = 1 AND rv.activo = 1
        ORDER BY rv.orden_visualizacion, rv.etiqueta
    ");
    $stmt->execute();
    $valoresDB = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>Comparación Valor por Valor:</h3>";
    echo "<table border='1' cellpadding='5' cellspacing='0' style='border-collapse: collapse;'>";
    echo "<tr style='background-color: #e8f5e8;'>";
    echo "<th>Base de Datos</th><th>Formulario HTML</th><th>Estado</th>";
    echo "</tr>";
    
    // Saltar la primera opción del HTML (que es "Seleccionar")
    $opcionesHTML = array_slice($matches, 1);
    
    $maxCount = max(count($valoresDB), count($opcionesHTML));
    
    for ($i = 0; $i < $maxCount; $i++) {
        echo "<tr>";
        
        // Valor de BD
        if (isset($valoresDB[$i])) {
            echo "<td><code>{$valoresDB[$i]['valor']}</code> - {$valoresDB[$i]['etiqueta']}</td>";
        } else {
            echo "<td style='color: #999;'>--- Sin valor en BD ---</td>";
        }
        
        // Valor de HTML
        if (isset($opcionesHTML[$i])) {
            echo "<td><code>{$opcionesHTML[$i][1]}</code> - {$opcionesHTML[$i][2]}</td>";
        } else {
            echo "<td style='color: #999;'>--- Sin valor en HTML ---</td>";
        }
        
        // Estado
        if (isset($valoresDB[$i]) && isset($opcionesHTML[$i])) {
            if ($valoresDB[$i]['valor'] == $opcionesHTML[$i][1] && $valoresDB[$i]['etiqueta'] == trim($opcionesHTML[$i][2])) {
                echo "<td style='color: green;'>✅ Coincide</td>";
            } else {
                echo "<td style='color: red;'>❌ Diferente</td>";
            }
        } else {
            echo "<td style='color: orange;'>⚠️ Faltante</td>";
        }
        
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<h2>📝 Conclusiones</h2>";
    
    if (count($valoresDB) == count($opcionesHTML)) {
        echo "<p style='color: green;'>✅ <strong>La cantidad de valores coincide:</strong> " . count($valoresDB) . " en BD = " . count($opcionesHTML) . " en HTML</p>";
    } else {
        echo "<p style='color: red;'>❌ <strong>DISCREPANCIA en cantidad:</strong> " . count($valoresDB) . " en BD ≠ " . count($opcionesHTML) . " en HTML</p>";
    }
    
    echo "<p><strong>Recomendación:</strong> ";
    if (count($valoresDB) == 7 && count($matches) == 8) {
        echo "El formulario está funcionando correctamente. Muestra 7 valores de la BD + 1 opción 'Seleccionar' = 8 total.</p>";
    } else {
        echo "Revisar por qué se muestran " . count($matches) . " opciones cuando deberían ser 8 (7 valores + 1 'Seleccionar').</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ <strong>Error:</strong> " . $e->getMessage() . "</p>";
}
?>
