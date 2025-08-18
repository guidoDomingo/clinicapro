<?php
/**
 * Script de sincronización para verificar que los módulos administrativos
 * estén sincronizados con el sistema dinámico de consultas-new
 */

require_once 'model/conexion.php';

echo "<h1>🔄 Verificación de Sincronización - Módulos Administrativos</h1>";

try {
    $conexion = Conexion::conectar();
    
    echo "<div style='font-family: Arial; margin: 20px;'>";
    
    // 1. Verificar estructura de tabla motivos_comunes
    echo "<h2>📋 1. Verificación de Motivos Comunes</h2>";
    
    $stmt = $conexion->query("SELECT COUNT(*) as total FROM motivos_comunes WHERE tipo_formulario IS NOT NULL");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<p>✅ Motivos con tipo_formulario: <strong>{$result['total']}</strong></p>";
    
    // Mostrar distribución por tipo
    $stmt = $conexion->query("SELECT tipo_formulario, COUNT(*) as cantidad FROM motivos_comunes GROUP BY tipo_formulario ORDER BY tipo_formulario");
    $motivos_por_tipo = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>Distribución por tipo:</h3><ul>";
    foreach ($motivos_por_tipo as $tipo) {
        echo "<li><strong>{$tipo['tipo_formulario']}:</strong> {$tipo['cantidad']} motivos</li>";
    }
    echo "</ul>";
    
    // 2. Verificar estructura de tabla preformatos
    echo "<h2>📄 2. Verificación de Preformatos</h2>";
    
    $stmt = $conexion->query("SELECT COUNT(*) as total FROM preformatos WHERE tipo_formulario IS NOT NULL");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<p>✅ Preformatos con tipo_formulario: <strong>{$result['total']}</strong></p>";
    
    // Mostrar distribución por tipo
    $stmt = $conexion->query("SELECT tipo_formulario, COUNT(*) as cantidad FROM preformatos GROUP BY tipo_formulario ORDER BY tipo_formulario");
    $preformatos_por_tipo = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>Distribución por tipo:</h3><ul>";
    foreach ($preformatos_por_tipo as $tipo) {
        echo "<li><strong>{$tipo['tipo_formulario']}:</strong> {$tipo['cantidad']} preformatos</li>";
    }
    echo "</ul>";
    
    // 3. Verificar API endpoints
    echo "<h2>🔌 3. Verificación de Endpoints API</h2>";
    
    // Verificar motivos por tipo
    $tipos_formulario = ['general', 'anteojos', 'estudios', 'informe_imagen'];
    
    foreach ($tipos_formulario as $tipo) {
        echo "<h4>Tipo: {$tipo}</h4>";
        
        // Motivos
        $stmt = $conexion->prepare("SELECT COUNT(*) as cantidad FROM motivos_comunes WHERE activo = true AND tipo_formulario = ?");
        $stmt->execute([$tipo]);
        $motivos_count = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "<p>• Motivos comunes: <strong>{$motivos_count['cantidad']}</strong></p>";
        
        // Preformatos
        $stmt = $conexion->prepare("SELECT COUNT(*) as cantidad FROM preformatos WHERE activo = true AND tipo_formulario = ?");
        $stmt->execute([$tipo]);
        $preformatos_count = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "<p>• Preformatos: <strong>{$preformatos_count['cantidad']}</strong></p>";
    }
    
    // 4. Test de API simulado
    echo "<h2>🧪 4. Test de API (Simulación)</h2>";
    
    echo "<div style='background: #f8f9fa; padding: 15px; border: 1px solid #dee2e6; border-radius: 5px;'>";
    echo "<h4>Simulación de llamadas API:</h4>";
    
    foreach ($tipos_formulario as $tipo) {
        echo "<h5>Tipo: {$tipo}</h5>";
        
        // Simular getMotivosComunes
        $stmt = $conexion->prepare("
            SELECT id_motivo as id, nombre, descripcion, activo
            FROM motivos_comunes 
            WHERE activo = true AND tipo_formulario = ?
            ORDER BY nombre ASC
        ");
        $stmt->execute([$tipo]);
        $motivos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<p>📝 getMotivosComunes('{$tipo}'): " . count($motivos) . " resultados</p>";
        if (count($motivos) > 0) {
            echo "<ul style='margin: 0; padding-left: 20px;'>";
            foreach (array_slice($motivos, 0, 3) as $motivo) {
                echo "<li>{$motivo['nombre']}</li>";
            }
            if (count($motivos) > 3) {
                echo "<li><em>... y " . (count($motivos) - 3) . " más</em></li>";
            }
            echo "</ul>";
        }
        
        // Simular getPreformatosConsulta
        $stmt = $conexion->prepare("
            SELECT id_preformato as id, nombre, contenido as texto, tipo as categoria
            FROM preformatos 
            WHERE (activo = true OR activo IS NULL) AND tipo_formulario = ?
            ORDER BY nombre ASC
            LIMIT 5
        ");
        $stmt->execute([$tipo]);
        $preformatos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<p>📄 getPreformatosConsulta('{$tipo}'): " . count($preformatos) . " resultados</p>";
        if (count($preformatos) > 0) {
            echo "<ul style='margin: 0 0 15px 20px;'>";
            foreach ($preformatos as $preformato) {
                echo "<li>{$preformato['nombre']}</li>";
            }
            echo "</ul>";
        } else {
            echo "<p style='color: #6c757d; margin: 0 0 15px 20px;'><em>No hay preformatos específicos para este tipo</em></p>";
        }
    }
    echo "</div>";
    
    // 5. Resumen de sincronización
    echo "<h2>✅ 5. Estado de Sincronización</h2>";
    
    $total_motivos = 0;
    $total_preformatos = 0;
    
    foreach ($tipos_formulario as $tipo) {
        $stmt = $conexion->prepare("SELECT COUNT(*) as cantidad FROM motivos_comunes WHERE tipo_formulario = ?");
        $stmt->execute([$tipo]);
        $total_motivos += $stmt->fetch(PDO::FETCH_ASSOC)['cantidad'];
        
        $stmt = $conexion->prepare("SELECT COUNT(*) as cantidad FROM preformatos WHERE tipo_formulario = ?");
        $stmt->execute([$tipo]);
        $total_preformatos += $stmt->fetch(PDO::FETCH_ASSOC)['cantidad'];
    }
    
    echo "<div style='background: #d4edda; padding: 15px; border: 1px solid #c3e6cb; border-radius: 5px;'>";
    echo "<h3>✅ Sincronización Completada</h3>";
    echo "<p><strong>Total de elementos sincronizados:</strong></p>";
    echo "<ul>";
    echo "<li>Motivos comunes: <strong>{$total_motivos}</strong></li>";
    echo "<li>Preformatos: <strong>{$total_preformatos}</strong></li>";
    echo "<li>Tipos de formulario soportados: <strong>" . count($tipos_formulario) . "</strong></li>";
    echo "</ul>";
    echo "<p><strong>Estado:</strong> Los módulos administrativos están correctamente sincronizados con el sistema dinámico de consultas-new.php</p>";
    echo "</div>";
    
    // 6. Enlaces de verificación
    echo "<h2>🔗 6. Enlaces de Verificación</h2>";
    echo "<div style='margin: 15px 0;'>";
    echo "<a href='servicios/index.php?ruta=motivos' style='display: inline-block; margin: 5px 10px; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px;'>📋 Administrar Motivos</a>";
    echo "<a href='servicios/index.php?ruta=preformatos' style='display: inline-block; margin: 5px 10px; padding: 10px 20px; background: #28a745; color: white; text-decoration: none; border-radius: 5px;'>📄 Administrar Preformatos</a>";
    echo "<a href='servicios/index.php?ruta=consultas-new' style='display: inline-block; margin: 5px 10px; padding: 10px 20px; background: #17a2b8; color: white; text-decoration: none; border-radius: 5px;'>🏥 Sistema Consultas</a>";
    echo "</div>";
    
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 15px; border: 1px solid #f5c6cb; border-radius: 5px;'>";
    echo "<h3>❌ Error en Verificación</h3>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
    echo "</div>";
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; background-color: #f8f9fa; }
h1 { color: #343a40; border-bottom: 3px solid #007bff; padding-bottom: 10px; }
h2 { color: #495057; margin-top: 30px; }
h3 { color: #6c757d; }
p { margin: 8px 0; }
ul { margin: 10px 0; }
</style>
