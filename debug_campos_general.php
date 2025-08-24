<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'modules/consultas/core/DatabaseMapper.php';

echo "<h2>🔍 Diagnóstico de Campos Formulario General</h2>";

try {
    $mapper = new DatabaseMapper();
    
    // Obtener consulta #108 que es tipo general
    $result = $mapper->getConsulta(108);
    
    if ($result['success']) {
        $data = $result['data'];
        
        echo "<h3>📋 Datos reales de consulta #108:</h3>";
        echo "<h4>Main table (consultas):</h4>";
        echo "<pre>";
        print_r($data['main']);
        echo "</pre>";
        
        if (isset($data['related'])) {
            echo "<h4>Related tables:</h4>";
            echo "<pre>";
            print_r($data['related']);
            echo "</pre>";
        }
        
        echo "<h3>🗺️ Mapeo HTML actual para 'general':</h3>";
        $htmlMapping = $mapper->getHtmlFieldMapping('general');
        echo "<pre>";
        print_r($htmlMapping);
        echo "</pre>";
        
        echo "<h3>📊 Datos HTML preparados:</h3>";
        if (isset($data['html_data'])) {
            echo "<pre>";
            print_r($data['html_data']);
            echo "</pre>";
        }
        
        echo "<h3>🔧 Campos disponibles vs Mapeo:</h3>";
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>Campo HTML</th><th>Columna BD Mapeada</th><th>Valor Real</th><th>Estado</th></tr>";
        
        foreach ($htmlMapping as $htmlField => $dbColumn) {
            $valor = 'N/A';
            $estado = '❌ No encontrado';
            
            // Buscar el valor real
            if (isset($data['main'][$dbColumn])) {
                $valor = $data['main'][$dbColumn];
                $estado = '✅ Encontrado en main';
            } elseif (isset($data['html_data'][$htmlField])) {
                $valor = $data['html_data'][$htmlField];
                $estado = '🔄 Procesado en html_data';
            } else {
                // Buscar directamente por el nombre del campo HTML
                if (isset($data['main'][$htmlField])) {
                    $valor = $data['main'][$htmlField];
                    $estado = '⚠️ Encontrado por nombre directo';
                }
            }
            
            echo "<tr>";
            echo "<td><strong>$htmlField</strong></td>";
            echo "<td>$dbColumn</td>";
            echo "<td>" . (is_string($valor) ? htmlspecialchars(substr($valor, 0, 50)) : print_r($valor, true)) . "</td>";
            echo "<td>$estado</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        echo "<h3>🔍 Columnas reales disponibles en main:</h3>";
        echo "<ul>";
        foreach (array_keys($data['main']) as $realColumn) {
            echo "<li><code>$realColumn</code> = " . htmlspecialchars(substr($data['main'][$realColumn], 0, 50)) . "</li>";
        }
        echo "</ul>";
        
    } else {
        echo "<div style='color: red;'>❌ Error: " . $result['message'] . "</div>";
    }
    
} catch (Exception $e) {
    echo "<div style='color: red;'>❌ Exception: " . $e->getMessage() . "</div>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>