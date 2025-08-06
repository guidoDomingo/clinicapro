<?php
/**
 * RESET COMPLETO - Limpiar y recrear valores de esfera correctamente
 */

require_once "model/conexion.php";

echo "<h1>🔄 RESET COMPLETO: Valores de Esfera</h1>";
echo "<p><strong>Esta operación eliminará TODOS los valores de esfera existentes y creará solo 7 valores correctos.</strong></p>";

try {
    $pdo = Conexion::conectar();
    $pdo->beginTransaction();
    
    // 1. Buscar el referencial de esfera
    echo "<h2>🎯 1. Localizando Referencial de Esfera</h2>";
    
    $stmt = $pdo->prepare("SELECT id FROM referenciales WHERE codigo = 'valores_esfera'");
    $stmt->execute();
    $refEsfera = $stmt->fetch();
    
    if (!$refEsfera) {
        echo "<p style='color: red;'>❌ No se encontró el referencial 'valores_esfera'. Creándolo...</p>";
        
        $stmt = $pdo->prepare("
            INSERT INTO referenciales (nombre, codigo, descripcion, activo) 
            VALUES ('Valores de Esfera', 'valores_esfera', 'Valores de esfera para prescripción de lentes', true)
        ");
        $stmt->execute();
        
        $stmt = $pdo->prepare("SELECT id FROM referenciales WHERE codigo = 'valores_esfera'");
        $stmt->execute();
        $refEsfera = $stmt->fetch();
        
        echo "<p style='color: green;'>✅ Referencial creado con ID: {$refEsfera['id']}</p>";
    } else {
        echo "<p>✅ Referencial encontrado con ID: {$refEsfera['id']}</p>";
    }
    
    // 2. Eliminar TODOS los valores existentes
    echo "<h2>🗑️ 2. Eliminando Todos los Valores Existentes</h2>";
    
    $stmt = $pdo->prepare("DELETE FROM referencial_valores WHERE referencial_id = :ref_id");
    $stmt->bindParam(':ref_id', $refEsfera['id']);
    $stmt->execute();
    $eliminados = $stmt->rowCount();
    
    echo "<p>🗑️ Eliminados: <strong>$eliminados</strong> valores antiguos</p>";
    
    // 3. Crear SOLO los 7 valores correctos
    echo "<h2>✨ 3. Creando los 7 Valores Correctos</h2>";
    
    // Valores de esfera comunes para prescripción oftalmológica
    $valoresCorrectos = [
        ['0.00', 'Neutro', 0.00, 1],
        ['+0.25', '+0.25', 0.25, 2],
        ['+0.50', '+0.50', 0.50, 3],
        ['+0.75', '+0.75', 0.75, 4],
        ['+1.00', '+1.00', 1.00, 5],
        ['+1.25', '+1.25', 1.25, 6],
        ['+1.50', '+1.50', 1.50, 7]
    ];
    
    echo "<table border='1' cellpadding='5' cellspacing='0' style='border-collapse: collapse;'>";
    echo "<tr style='background-color: #e8f5e8;'>";
    echo "<th>Orden</th><th>Valor</th><th>Etiqueta</th><th>Valor Numérico</th><th>Estado</th></tr>";
    
    foreach ($valoresCorrectos as $valor) {
        $stmt = $pdo->prepare("
            INSERT INTO referencial_valores (referencial_id, valor, etiqueta, valor_numerico, orden_visualizacion, activo) 
            VALUES (:ref_id, :valor, :etiqueta, :valor_num, :orden, true)
        ");
        $stmt->bindParam(':ref_id', $refEsfera['id']);
        $stmt->bindParam(':valor', $valor[0]);
        $stmt->bindParam(':etiqueta', $valor[1]);
        $stmt->bindParam(':valor_num', $valor[2]);
        $stmt->bindParam(':orden', $valor[3]);
        $stmt->execute();
        
        echo "<tr>";
        echo "<td>{$valor[3]}</td>";
        echo "<td><code>{$valor[0]}</code></td>";
        echo "<td><strong>{$valor[1]}</strong></td>";
        echo "<td>{$valor[2]}</td>";
        echo "<td>✅ Insertado</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // 4. Verificación final
    echo "<h2>✅ 4. Verificación Final</h2>";
    
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as total_final
        FROM referencial_valores 
        WHERE referencial_id = :ref_id AND activo = true
    ");
    $stmt->bindParam(':ref_id', $refEsfera['id']);
    $stmt->execute();
    $totalFinal = $stmt->fetch()['total_final'];
    
    if ($totalFinal == 7) {
        echo "<div style='background-color: #d4edda; border: 1px solid #c3e6cb; padding: 20px; border-radius: 8px;'>";
        echo "<h3>🎉 ¡RESET COMPLETADO EXITOSAMENTE!</h3>";
        echo "<p><strong>✅ Total de valores:</strong> $totalFinal (correcto)</p>";
        echo "<p><strong>✅ Estado:</strong> Limpio y sin duplicados</p>";
        echo "<p><strong>✅ Formulario mostrará:</strong> 8 opciones (7 valores + 1 'Seleccionar')</p>";
        echo "</div>";
    } else {
        echo "<div style='background-color: #f8d7da; border: 1px solid #f5c6cb; padding: 20px; border-radius: 8px;'>";
        echo "<h3>❌ ERROR en el Reset</h3>";
        echo "<p><strong>Esperado:</strong> 7 valores</p>";
        echo "<p><strong>Actual:</strong> $totalFinal valores</p>";
        echo "</div>";
    }
    
    // 5. Probar la función generadora
    echo "<h2>🧪 5. Prueba de Generación HTML</h2>";
    
    require_once "model/formularios_dinamicos.model.php";
    
    $htmlGenerado = FormulariosDinamicos::generarSelectReferencial('valores_esfera', 'test_reset', 'test_reset');
    $opcionesGeneradas = substr_count($htmlGenerado, '<option');
    
    echo "<div style='background-color: #f8f9fa; padding: 15px; border-radius: 5px;'>";
    echo "<h4>📊 Resultado de la Función:</h4>";
    echo "<p><strong>Opciones generadas:</strong> $opcionesGeneradas</p>";
    echo "<p><strong>Esperado:</strong> 8 opciones</p>";
    
    if ($opcionesGeneradas == 8) {
        echo "<p style='color: #28a745;'><strong>✅ PERFECTO: La función genera las opciones correctas</strong></p>";
    } else {
        echo "<p style='color: #dc3545;'><strong>❌ ERROR: La función no genera las opciones esperadas</strong></p>";
    }
    echo "</div>";
    
    echo "<h4>Vista previa del select:</h4>";
    echo "<div style='border: 2px solid #28a745; padding: 15px; background-color: #f8f9fa; margin: 10px 0;'>";
    echo $htmlGenerado;
    echo "</div>";
    
    $pdo->commit();
    
    echo "<hr>";
    echo "<div style='background-color: #cce5ff; border: 1px solid #99ccff; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
    echo "<h3>🎯 Próximos Pasos:</h3>";
    echo "<ol>";
    echo "<li><strong>Limpiar caché del navegador</strong> (Ctrl+F5)</li>";
    echo "<li><strong>Abrir el formulario de anteojos</strong> en el sistema</li>";
    echo "<li><strong>Verificar que aparezcan solo 7 valores + 'Seleccionar'</strong></li>";
    echo "<li><strong>Probar que la funcionalidad trabaje correctamente</strong></li>";
    echo "</ol>";
    echo "<p><a href='servicios' class='btn btn-primary'>🚀 Ir al Sistema</a></p>";
    echo "</div>";
    
} catch (Exception $e) {
    $pdo->rollback();
    echo "<p style='color: red;'>❌ Error durante el reset: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>

<style>
.btn {
    display: inline-block;
    padding: 8px 16px;
    background-color: #007bff;
    color: white;
    text-decoration: none;
    border-radius: 4px;
    border: none;
    cursor: pointer;
}
.btn-primary { background-color: #007bff; }
.btn:hover { opacity: 0.8; }
</style>
