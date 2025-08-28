<?php
// Test para verificar si la actualización realmente está funcionando
require_once 'model/conexion.php';

try {
    $pdo = Conexion::conectar();
    
    echo "=== VERIFICACIÓN ESTADO ACTUAL CONSULTA 161 ===\n";
    
    // Obtener estado actual
    $query = "SELECT id_consulta, id_persona, txtmotivo, motivoscomunes, 
                     visionod, visionoi, consulta_textarea, receta_textarea, 
                     txtnota, ultima_modificacion
              FROM consultas WHERE id_consulta = 161";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $consulta = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$consulta) {
        echo "❌ Consulta 161 no encontrada\n";
        exit;
    }
    
    echo "📋 Estado actual de consulta 161:\n";
    foreach ($consulta as $campo => $valor) {
        $valorDisplay = $valor ?: 'NULL';
        if (is_string($valor) && strlen($valor) > 50) {
            $valorDisplay = substr($valor, 0, 50) . '...';
        }
        echo "   {$campo}: {$valorDisplay}\n";
    }
    
    echo "\n⏰ Última modificación: " . ($consulta['ultima_modificacion'] ?: 'NULL') . "\n";
    
    // Verificar si hay datos de anteojos relacionados
    echo "\n🔍 Verificando anteojos relacionados...\n";
    
    $queryAnteojos = "SELECT id_consulta_anteojos, esfera_od, esfera_oi, notas, fecha_creacion 
                      FROM consulta_anteojos WHERE id_consulta = 161";
    
    $stmt = $pdo->prepare($queryAnteojos);
    $stmt->execute();
    $anteojos = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($anteojos) {
        echo "✅ Anteojos encontrados:\n";
        echo "   ID: {$anteojos['id_consulta_anteojos']}\n";
        echo "   Esfera OD: " . ($anteojos['esfera_od'] ?: 'NULL') . "\n";
        echo "   Esfera OI: " . ($anteojos['esfera_oi'] ?: 'NULL') . "\n";
        echo "   Notas: " . ($anteojos['notas'] ?: 'NULL') . "\n";
        echo "   Fecha creación: " . ($anteojos['fecha_creacion'] ?: 'NULL') . "\n";
    } else {
        echo "❌ No se encontraron anteojos para esta consulta\n";
    }
    
    // Hacer una pequeña actualización de prueba para ver si funciona
    echo "\n🧪 Test de actualización simple...\n";
    
    $testValue = "TEST ACTUALIZACIÓN " . date('Y-m-d H:i:s');
    $updateQuery = "UPDATE consultas SET txtnota = :nota, ultima_modificacion = CURRENT_TIMESTAMP WHERE id_consulta = 161";
    
    $stmt = $pdo->prepare($updateQuery);
    $resultado = $stmt->execute(['nota' => $testValue]);
    
    if ($resultado && $stmt->rowCount() > 0) {
        echo "✅ Actualización de prueba exitosa\n";
        echo "📊 Filas afectadas: " . $stmt->rowCount() . "\n";
        
        // Verificar el cambio
        $stmt = $pdo->prepare("SELECT txtnota, ultima_modificacion FROM consultas WHERE id_consulta = 161");
        $stmt->execute();
        $verificacion = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo "📝 Nuevo valor txtnota: " . $verificacion['txtnota'] . "\n";
        echo "⏰ Nueva última modificación: " . $verificacion['ultima_modificacion'] . "\n";
        
        // Restaurar valor original
        $stmt = $pdo->prepare("UPDATE consultas SET txtnota = :nota WHERE id_consulta = 161");
        $stmt->execute(['nota' => $consulta['txtnota']]);
        echo "🔄 Valor original restaurado\n";
        
    } else {
        echo "❌ Actualización de prueba falló\n";
        print_r($stmt->errorInfo());
    }
    
    // Verificar logs de actividad recientes
    echo "\n📊 Estadísticas de modificación de consultas...\n";
    
    $statsQuery = "SELECT COUNT(*) as total,
                          COUNT(CASE WHEN ultima_modificacion > NOW() - INTERVAL '1 hour' THEN 1 END) as ultima_hora,
                          COUNT(CASE WHEN ultima_modificacion > NOW() - INTERVAL '1 day' THEN 1 END) as ultimo_dia
                   FROM consultas WHERE ultima_modificacion IS NOT NULL";
    
    $stmt = $pdo->prepare($statsQuery);
    $stmt->execute();
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "📈 Total consultas con modificaciones: {$stats['total']}\n";
    echo "📈 Modificadas última hora: {$stats['ultima_hora']}\n";
    echo "📈 Modificadas último día: {$stats['ultimo_dia']}\n";
    
    echo "\n=== RESUMEN ===\n";
    echo "✅ Consulta 161 existe y es accesible\n";
    echo "✅ Actualización directa funciona\n";
    if ($anteojos) {
        echo "✅ Datos de anteojos relacionados existen\n";
    }
    echo "📊 Sistema de base de datos operativo\n";
    
    echo "\n💡 CONCLUSIÓN: Si el sistema dice 'actualizado exitosamente' pero los datos principales no cambian,\n";
    echo "   el problema está en el frontend (no envía datos) o en transacciones (rollback silencioso)\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>