<?php
/**
 * Limpieza completa y verificación de la base de datos de valores de esfera
 */

require_once "model/conexion.php";

echo "<h1>🧹 Limpieza y Verificación de Valores de Esfera</h1>";

try {
    $pdo = Conexion::conectar();
    $pdo->beginTransaction();
    
    // 1. Ver todos los referenciales de esfera (incluso inactivos)
    echo "<h2>🔍 1. Auditoria Completa de Referenciales de Esfera</h2>";
    
    $stmt = $pdo->prepare("
        SELECT r.*, COUNT(rv.id) as total_valores
        FROM referenciales r
        LEFT JOIN referencial_valores rv ON r.id = rv.referencial_id
        WHERE r.codigo LIKE '%esfera%'
        GROUP BY r.id
        ORDER BY r.codigo
    ");
    $stmt->execute();
    $referenciales = $stmt->fetchAll();
    
    echo "<table border='1' cellpadding='5' cellspacing='0' style='border-collapse: collapse;'>";
    echo "<tr style='background-color: #e3f2fd;'>";
    echo "<th>ID</th><th>Nombre</th><th>Código</th><th>Activo</th><th>Total Valores</th></tr>";
    
    foreach ($referenciales as $ref) {
        $bgColor = $ref['activo'] ? '#e8f5e8' : '#ffebee';
        echo "<tr style='background-color: $bgColor;'>";
        echo "<td>{$ref['id']}</td>";
        echo "<td>{$ref['nombre']}</td>";
        echo "<td><code>{$ref['codigo']}</code></td>";
        echo "<td>" . ($ref['activo'] ? '✅' : '❌') . "</td>";
        echo "<td><strong>{$ref['total_valores']}</strong></td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // 2. Verificar si hay múltiples referenciales de esfera
    if (count($referenciales) > 1) {
        echo "<div style='background-color: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; margin: 20px 0;'>";
        echo "<h3>⚠️ PROBLEMA DETECTADO: Múltiples referenciales de esfera</h3>";
        echo "<p>Se encontraron " . count($referenciales) . " referenciales con 'esfera' en el código. Esto puede causar confusión.</p>";
        echo "</div>";
    }
    
    // 3. Verificar el referencial principal 'valores_esfera'
    echo "<h2>📊 2. Análisis del Referencial Principal: 'valores_esfera'</h2>";
    
    $stmt = $pdo->prepare("SELECT id FROM referenciales WHERE codigo = 'valores_esfera'");
    $stmt->execute();
    $refPrincipal = $stmt->fetch();
    
    if ($refPrincipal) {
        echo "<p>✅ Referencial principal encontrado con ID: {$refPrincipal['id']}</p>";
        
        // Ver todos los valores (incluso inactivos)
        $stmt = $pdo->prepare("
            SELECT * FROM referencial_valores 
            WHERE referencial_id = :ref_id 
            ORDER BY activo DESC, orden_visualizacion, valor_numerico
        ");
        $stmt->bindParam(':ref_id', $refPrincipal['id']);
        $stmt->execute();
        $todosLosValores = $stmt->fetchAll();
        
        echo "<h3>Todos los valores (activos e inactivos):</h3>";
        echo "<table border='1' cellpadding='5' cellspacing='0' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background-color: #f8f9fa;'>";
        echo "<th>ID</th><th>Valor</th><th>Etiqueta</th><th>Activo</th><th>Orden</th><th>Valor Numérico</th><th>Acción</th></tr>";
        
        $activos = 0;
        $inactivos = 0;
        
        foreach ($todosLosValores as $valor) {
            if ($valor['activo']) {
                $activos++;
                $bgColor = '#e8f5e8';
                $estado = '✅ Activo';
            } else {
                $inactivos++;
                $bgColor = '#ffebee';
                $estado = '❌ Inactivo';
            }
            
            echo "<tr style='background-color: $bgColor;'>";
            echo "<td>{$valor['id']}</td>";
            echo "<td><code>{$valor['valor']}</code></td>";
            echo "<td><strong>{$valor['etiqueta']}</strong></td>";
            echo "<td>$estado</td>";
            echo "<td>{$valor['orden_visualizacion']}</td>";
            echo "<td>{$valor['valor_numerico']}</td>";
            echo "<td>";
            if (!$valor['activo']) {
                echo "<small style='color: #666;'>Inactivo - No se muestra</small>";
            } else {
                echo "<small style='color: #28a745;'>Se muestra en formulario</small>";
            }
            echo "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        echo "<div style='margin: 20px 0; padding: 15px; background-color: #f8f9fa; border-radius: 5px;'>";
        echo "<h4>📈 Resumen:</h4>";
        echo "<p><strong>Total de registros:</strong> " . count($todosLosValores) . "</p>";
        echo "<p><strong>Valores activos:</strong> <span style='color: #28a745;'>$activos</span></p>";
        echo "<p><strong>Valores inactivos:</strong> <span style='color: #dc3545;'>$inactivos</span></p>";
        echo "</div>";
        
        // 4. Verificar duplicados
        echo "<h3>🔍 Verificación de Duplicados:</h3>";
        
        $stmt = $pdo->prepare("
            SELECT valor, COUNT(*) as cantidad
            FROM referencial_valores 
            WHERE referencial_id = :ref_id AND activo = true
            GROUP BY valor
            HAVING COUNT(*) > 1
        ");
        $stmt->bindParam(':ref_id', $refPrincipal['id']);
        $stmt->execute();
        $duplicados = $stmt->fetchAll();
        
        if (empty($duplicados)) {
            echo "<p style='color: #28a745;'>✅ No se encontraron duplicados en valores activos</p>";
        } else {
            echo "<div style='background-color: #f8d7da; border: 1px solid #f5c6cb; padding: 15px;'>";
            echo "<h4>❌ Valores duplicados encontrados:</h4>";
            foreach ($duplicados as $dup) {
                echo "<p><strong>Valor:</strong> <code>{$dup['valor']}</code> - <strong>Aparece:</strong> {$dup['cantidad']} veces</p>";
            }
            echo "</div>";
        }
        
    } else {
        echo "<p style='color: red;'>❌ No se encontró el referencial 'valores_esfera'</p>";
    }
    
    // 5. Limpieza sugerida
    echo "<h2>🧹 3. Opciones de Limpieza</h2>";
    
    if ($inactivos > 0) {
        echo "<div style='background-color: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; margin: 15px 0;'>";
        echo "<h4>🗑️ Valores Inactivos Detectados</h4>";
        echo "<p>Hay <strong>$inactivos valores inactivos</strong> que no se muestran pero ocupan espacio en la BD.</p>";
        echo "<button onclick='confirmarLimpiezaInactivos()' class='btn btn-warning'>🗑️ Eliminar Valores Inactivos</button>";
        echo "</div>";
    }
    
    if (!empty($duplicados)) {
        echo "<div style='background-color: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; margin: 15px 0;'>";
        echo "<h4>🔧 Duplicados Detectados</h4>";
        echo "<p>Hay valores duplicados que pueden causar problemas.</p>";
        echo "<button onclick='confirmarLimpiezaDuplicados()' class='btn btn-danger'>🔧 Limpiar Duplicados</button>";
        echo "</div>";
    }
    
    // 6. Estado final esperado
    echo "<h2>🎯 4. Estado Final Esperado</h2>";
    echo "<div style='background-color: #d4edda; border: 1px solid #c3e6cb; padding: 15px;'>";
    echo "<h4>✅ Configuración Ideal:</h4>";
    echo "<ul>";
    echo "<li><strong>1 solo referencial</strong> con código 'valores_esfera'</li>";
    echo "<li><strong>7 valores activos</strong> únicos (sin duplicados)</li>";
    echo "<li><strong>0 valores inactivos</strong> (para limpiar BD)</li>";
    echo "<li><strong>Formulario mostrando:</strong> 8 opciones (7 valores + 1 'Seleccionar')</li>";
    echo "</ul>";
    echo "</div>";
    
    $pdo->commit();
    
} catch (Exception $e) {
    $pdo->rollback();
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>

<script>
function confirmarLimpiezaInactivos() {
    if (confirm('¿Estás seguro de que deseas eliminar todos los valores inactivos?\n\nEsta acción no se puede deshacer.')) {
        window.location.href = 'limpiar_valores_inactivos.php';
    }
}

function confirmarLimpiezaDuplicados() {
    if (confirm('¿Estás seguro de que deseas limpiar los valores duplicados?\n\nSe mantendrá solo una copia de cada valor.')) {
        window.location.href = 'limpiar_duplicados_esfera.php';
    }
}
</script>
