<?php
// Test directo del API con los campos correctos
require_once 'model/conexion.php';

try {
    $pdo = Conexion::conectar();
    
    // Test 1: Contar consultas del paciente 45
    echo "=== TEST 1: CONTAR CONSULTAS ===\n";
    $sql = "SELECT COUNT(*) as total FROM consultas WHERE id_persona = 45";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $count = $stmt->fetchColumn();
    echo "✅ Consultas encontradas: $count\n\n";
    
    // Test 2: Obtener historial
    echo "=== TEST 2: HISTORIAL DE CONSULTAS ===\n";
    $sql = "SELECT 
                c.id_consulta,
                c.fecha_registro,
                c.txtmotivo as motivo_consulta,
                c.consulta_textarea as diagnostico,
                c.tipo_formulario
            FROM consultas c
            WHERE c.id_persona = 45 
            ORDER BY c.fecha_registro DESC 
            LIMIT 3";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $historial = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($historial as $i => $consulta) {
        echo "--- Consulta " . ($i + 1) . " ---\n";
        echo "ID: " . $consulta['id_consulta'] . "\n";
        echo "Fecha: " . $consulta['fecha_registro'] . "\n";
        echo "Motivo: " . ($consulta['motivo_consulta'] ?: 'Sin motivo') . "\n";
        echo "Diagnóstico: " . ($consulta['diagnostico'] ?: 'Sin diagnóstico') . "\n";
        echo "Tipo: " . ($consulta['tipo_formulario'] ?: 'General') . "\n\n";
    }
    
    // Test 3: Timeline
    echo "=== TEST 3: TIMELINE ===\n";
    $sql = "SELECT 
                'consulta' as tipo,
                fecha_registro as fecha,
                CONCAT('Consulta: ', COALESCE(LEFT(txtmotivo, 50), 'Sin motivo'), 
                       CASE WHEN LENGTH(txtmotivo) > 50 THEN '...' ELSE '' END) as descripcion,
                id_consulta as referencia_id,
                tipo_formulario
            FROM consultas 
            WHERE id_persona = 45
            ORDER BY fecha_registro DESC 
            LIMIT 5";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $timeline = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($timeline as $i => $item) {
        echo ($i + 1) . ". [{$item['fecha']}] {$item['descripcion']} ({$item['tipo_formulario']})\n";
    }
    
    echo "\n✅ API debería funcionar correctamente ahora!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>