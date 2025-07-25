<?php
require_once 'model/conexion.php';

try {
    $pdo = Conexion::conectar();
    
    if ($pdo === null) {
        throw new Exception("No se pudo conectar a la base de datos");
    }
    
    echo '<div class="result-box info">';
    echo '<h5>📊 Comparación: ANTES vs DESPUÉS del cambio</h5>';
    
    // Consulta ANTES (con ambos filtros)
    echo '<h6>❌ ANTES (con filtro tipo + tipo_formulario):</h6>';
    $sqlAntes = "SELECT * FROM preformatos 
                 WHERE activo = true 
                 AND tipo = 'consulta' 
                 AND tipo_formulario = 'estudios' 
                 ORDER BY nombre";
    
    $stmtAntes = $pdo->prepare($sqlAntes);
    $stmtAntes->execute();
    $resultadosAntes = $stmtAntes->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<p><strong>Query:</strong> <code>{$sqlAntes}</code></p>";
    echo "<p><strong>Resultados:</strong> " . count($resultadosAntes) . " preformatos</p>";
    
    if (count($resultadosAntes) > 0) {
        echo "<ul>";
        foreach ($resultadosAntes as $item) {
            echo "<li>{$item['nombre']} (Tipo: {$item['tipo']}, Tipo Formulario: {$item['tipo_formulario']})</li>";
        }
        echo "</ul>";
    } else {
        echo "<p class='text-warning'>⚠️ No se encontraron preformatos</p>";
    }
    
    echo '<hr>';
    
    // Consulta DESPUÉS (solo tipo_formulario)
    echo '<h6>✅ DESPUÉS (solo filtro tipo_formulario):</h6>';
    $sqlDespues = "SELECT * FROM preformatos 
                   WHERE activo = true 
                   AND tipo_formulario = 'estudios' 
                   ORDER BY nombre";
    
    $stmtDespues = $pdo->prepare($sqlDespues);
    $stmtDespues->execute();
    $resultadosDespues = $stmtDespues->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<p><strong>Query:</strong> <code>{$sqlDespues}</code></p>";
    echo "<p><strong>Resultados:</strong> " . count($resultadosDespues) . " preformatos</p>";
    
    if (count($resultadosDespues) > 0) {
        echo "<ul>";
        foreach ($resultadosDespues as $item) {
            echo "<li><strong>{$item['nombre']}</strong> (Tipo: <span class='badge bg-info'>{$item['tipo']}</span>, Tipo Formulario: <span class='badge bg-success'>{$item['tipo_formulario']}</span>)</li>";
        }
        echo "</ul>";
        
        // Mostrar los diferentes tipos encontrados
        $tipos = array_unique(array_column($resultadosDespues, 'tipo'));
        echo "<p><strong>Tipos de preformatos incluidos:</strong> " . implode(', ', $tipos) . "</p>";
        
    } else {
        echo "<p class='text-warning'>⚠️ No se encontraron preformatos para estudios</p>";
        echo "<p><strong>Recomendación:</strong> Crear preformatos con tipo_formulario = 'estudios'</p>";
    }
    
    echo '<hr>';
    echo '<h6>📈 Mejora obtenida:</h6>';
    $mejora = count($resultadosDespues) - count($resultadosAntes);
    if ($mejora > 0) {
        echo "<p class='text-success'>✅ <strong>+{$mejora} preformatos adicionales</strong> ahora están disponibles</p>";
    } elseif ($mejora == 0) {
        echo "<p class='text-info'>ℹ️ Misma cantidad de preformatos</p>";
    } else {
        echo "<p class='text-warning'>⚠️ Se perdieron preformatos (revisar datos)</p>";
    }
    
    echo '</div>';
    
    // Verificar todos los preformatos disponibles
    echo '<div class="result-box success">';
    echo '<h5>📋 Todos los preformatos disponibles por tipo_formulario:</h5>';
    
    $sqlTodos = "SELECT tipo_formulario, tipo, COUNT(*) as cantidad 
                 FROM preformatos 
                 WHERE activo = true 
                 GROUP BY tipo_formulario, tipo 
                 ORDER BY tipo_formulario, tipo";
    
    $stmtTodos = $pdo->prepare($sqlTodos);
    $stmtTodos->execute();
    $todosTipos = $stmtTodos->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table class='table table-sm'>";
    echo "<thead><tr><th>Tipo Formulario</th><th>Tipo</th><th>Cantidad</th></tr></thead>";
    echo "<tbody>";
    foreach ($todosTipos as $row) {
        $class = $row['tipo_formulario'] === 'estudios' ? 'table-warning' : '';
        echo "<tr class='{$class}'>";
        echo "<td><strong>{$row['tipo_formulario']}</strong></td>";
        echo "<td>{$row['tipo']}</td>";
        echo "<td>{$row['cantidad']}</td>";
        echo "</tr>";
    }
    echo "</tbody></table>";
    echo '</div>';
    
} catch (Exception $e) {
    echo '<div class="result-box error">';
    echo "<h5>❌ Error</h5>";
    echo "<p>{$e->getMessage()}</p>";
    echo '</div>';
}
?>
