<?php
require_once 'model/conexion.php';

try {
    $pdo = Conexion::conectar();
    
    if ($pdo === null) {
        throw new Exception("No se pudo conectar a la base de datos");
    }
    
    echo '<div class="result-box">';
    echo '<h5>🔍 Análisis de duplicados en base de datos</h5>';
    
    // 1. Buscar duplicados exactos por nombre y tipo_formulario
    echo '<h6>1. Duplicados exactos (mismo nombre + tipo_formulario):</h6>';
    $sqlDuplicados = "
        SELECT nombre, tipo_formulario, COUNT(*) as cantidad, 
               string_agg(id_preformato::text, ', ') as ids
        FROM preformatos 
        WHERE activo = true 
        AND tipo_formulario = 'estudios'
        GROUP BY nombre, tipo_formulario
        HAVING COUNT(*) > 1
        ORDER BY cantidad DESC, nombre
    ";
    
    $stmtDuplicados = $pdo->prepare($sqlDuplicados);
    $stmtDuplicados->execute();
    $duplicados = $stmtDuplicados->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($duplicados) > 0) {
        echo '<div class="alert alert-warning">';
        echo '<strong>⚠️ DUPLICADOS ENCONTRADOS EN BASE DE DATOS:</strong>';
        echo '<table class="table table-sm mt-2">';
        echo '<thead><tr><th>Nombre</th><th>Tipo Formulario</th><th>Cantidad</th><th>IDs</th><th>Acción</th></tr></thead>';
        echo '<tbody>';
        
        foreach ($duplicados as $dup) {
            echo '<tr>';
            echo "<td><strong>{$dup['nombre']}</strong></td>";
            echo "<td>{$dup['tipo_formulario']}</td>";
            echo "<td><span class='badge bg-warning'>{$dup['cantidad']}</span></td>";
            echo "<td><small>{$dup['ids']}</small></td>";
            echo "<td><button class='btn btn-sm btn-danger' onclick='eliminarDuplicados(\"{$dup['nombre']}\", \"{$dup['tipo_formulario']}\")'>Limpiar</button></td>";
            echo '</tr>';
        }
        
        echo '</tbody></table>';
        echo '</div>';
    } else {
        echo '<div class="alert alert-success">✅ No hay duplicados exactos en la base de datos</div>';
    }
    
    // 2. Mostrar todos los preformatos de estudios
    echo '<h6>2. Todos los preformatos de estudios (activos):</h6>';
    $sqlTodos = "
        SELECT id_preformato, nombre, tipo, tipo_formulario, fecha_creacion, creado_por
        FROM preformatos 
        WHERE activo = true 
        AND tipo_formulario = 'estudios'
        ORDER BY nombre, id_preformato
    ";
    
    $stmtTodos = $pdo->prepare($sqlTodos);
    $stmtTodos->execute();
    $todos = $stmtTodos->fetchAll(PDO::FETCH_ASSOC);
    
    echo '<table class="table table-sm">';
    echo '<thead><tr><th>ID</th><th>Nombre</th><th>Tipo</th><th>Creado</th><th>Por</th></tr></thead>';
    echo '<tbody>';
    
    $nombreAnterior = '';
    foreach ($todos as $item) {
        $isDuplicate = ($item['nombre'] === $nombreAnterior);
        $rowClass = $isDuplicate ? 'table-warning' : '';
        
        echo "<tr class='{$rowClass}'>";
        echo "<td>{$item['id_preformato']}</td>";
        echo "<td>";
        if ($isDuplicate) echo "🔄 ";
        echo "{$item['nombre']}</td>";
        echo "<td>{$item['tipo']}</td>";
        echo "<td><small>{$item['fecha_creacion']}</small></td>";
        echo "<td>{$item['creado_por']}</td>";
        echo "</tr>";
        
        $nombreAnterior = $item['nombre'];
    }
    
    echo '</tbody></table>';
    
    // 3. Estadísticas
    echo '<h6>3. Estadísticas:</h6>';
    echo "<p><strong>Total de preformatos de estudios:</strong> " . count($todos) . "</p>";
    echo "<p><strong>Duplicados encontrados:</strong> " . count($duplicados) . "</p>";
    
    if (count($duplicados) > 0) {
        $totalDuplicados = array_sum(array_column($duplicados, 'cantidad')) - count($duplicados);
        echo "<p><strong>Registros duplicados que se pueden eliminar:</strong> {$totalDuplicados}</p>";
    }
    
    echo '</div>';
    
    // 4. Script de limpieza automática
    if (count($duplicados) > 0) {
        echo '<div class="result-box duplicate">';
        echo '<h5>🧹 Limpieza automática de duplicados</h5>';
        echo '<p>Se detectaron duplicados. Se puede crear un script para eliminar automáticamente los registros duplicados (manteniendo el más reciente).</p>';
        echo '<button class="btn btn-warning" onclick="generarScriptLimpieza()">📝 Generar Script de Limpieza</button>';
        echo '<div id="script-limpieza"></div>';
        echo '</div>';
    }
    
} catch (Exception $e) {
    echo '<div class="result-box" style="border-color: #dc3545; background-color: #f8d7da;">';
    echo "<h5>❌ Error</h5>";
    echo "<p>{$e->getMessage()}</p>";
    echo '</div>';
}
?>

<script>
function eliminarDuplicados(nombre, tipoFormulario) {
    if (confirm(`¿Estás seguro de que quieres eliminar los duplicados de "${nombre}"?\n\nEsto mantendrá solo el registro más reciente.`)) {
        // Aquí se podría implementar la eliminación vía AJAX
        alert('Función de eliminación no implementada. Usa el script de limpieza.');
    }
}

function generarScriptLimpieza() {
    const scriptDiv = document.getElementById('script-limpieza');
    
    const script = `
-- Script de limpieza de duplicados en preformatos
-- Este script elimina duplicados manteniendo el registro con ID más alto (más reciente)

WITH duplicados AS (
    SELECT 
        id_preformato,
        nombre,
        tipo_formulario,
        ROW_NUMBER() OVER (PARTITION BY nombre, tipo_formulario ORDER BY id_preformato DESC) as rn
    FROM preformatos 
    WHERE activo = true 
    AND tipo_formulario = 'estudios'
)
UPDATE preformatos 
SET activo = false 
WHERE id_preformato IN (
    SELECT id_preformato 
    FROM duplicados 
    WHERE rn > 1
);

-- Para ver qué registros se marcarían como inactivos:
-- SELECT * FROM preformatos WHERE id_preformato IN (
--     SELECT id_preformato FROM duplicados WHERE rn > 1
-- );
    `;
    
    scriptDiv.innerHTML = `
        <div class="mt-3">
            <h6>📝 Script SQL de limpieza:</h6>
            <pre class="bg-dark text-light p-3" style="font-size: 0.8em;">${script}</pre>
            <div class="alert alert-info">
                <strong>💡 Instrucciones:</strong>
                <ol>
                    <li>Copia el script SQL de arriba</li>
                    <li>Ejecútalo en tu cliente PostgreSQL (pgAdmin, DBeaver, etc.)</li>
                    <li>Esto marcará como inactivos los duplicados, manteniendo solo el más reciente</li>
                    <li>Luego recarga la página de consultas para verificar</li>
                </ol>
            </div>
        </div>
    `;
}
</script>
