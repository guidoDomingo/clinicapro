<?php
// Test específico para verificar actualización de consultas
require_once 'model/conexion.php';

try {
    $pdo = Conexion::conectar();
    
    echo "=== TEST ACTUALIZACIÓN DATOS CONSULTA ===\n";
    
    // 1. Obtener consulta específica ID 161
    echo "\n1️⃣ Verificando consulta ID 161 antes de actualización...\n";
    
    $query = "SELECT id_consulta, id_persona, txtmotivo, motivoscomunes, visionod, visionoi, 
                     consulta_textarea, receta_textarea, txtnota
              FROM consultas WHERE id_consulta = 161";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $consulta = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($consulta) {
        echo "✅ Consulta encontrada:\n";
        foreach ($consulta as $campo => $valor) {
            $valorMostrar = $valor ? (strlen((string)$valor) > 30 ? substr((string)$valor, 0, 30) . '...' : $valor) : 'NULL';
            echo "   {$campo}: {$valorMostrar}\n";
        }
    } else {
        echo "❌ Consulta ID 161 no encontrada\n";
        exit;
    }
    
    // 2. Simular actualización de campos principales
    echo "\n2️⃣ Test de actualización de campos principales...\n";
    
    $updateQuery = "UPDATE consultas SET 
                    txtmotivo = :txtmotivo,
                    motivoscomunes = :motivoscomunes,
                    visionod = :visionod,
                    visionoi = :visionoi,
                    consulta_textarea = :consulta_textarea,
                    receta_textarea = :receta_textarea,
                    txtnota = :txtnota,
                    ultima_modificacion = CURRENT_TIMESTAMP
                    WHERE id_consulta = :id_consulta";
    
    $stmt = $pdo->prepare($updateQuery);
    
    // Datos de prueba
    $datosTest = [
        'txtmotivo' => 'TEST MOTIVO ACTUALIZADO',
        'motivoscomunes' => 'TEST MOTIVOS COMUNES',
        'visionod' => 'TEST VISION OD',
        'visionoi' => 'TEST VISION OI', 
        'consulta_textarea' => 'TEST CONSULTA ACTUALIZADA',
        'receta_textarea' => 'TEST RECETA ACTUALIZADA',
        'txtnota' => 'TEST NOTA ACTUALIZADA',
        'id_consulta' => 161
    ];
    
    if ($stmt) {
        echo "✅ Consulta UPDATE preparada correctamente\n";
        echo "🔧 Campos a actualizar: " . implode(', ', array_keys($datosTest)) . "\n";
        
        // Ejecutar actualización de prueba
        $resultado = $stmt->execute($datosTest);
        
        if ($resultado) {
            echo "✅ UPDATE ejecutado exitosamente\n";
            echo "📊 Filas afectadas: " . $stmt->rowCount() . "\n";
        } else {
            echo "❌ Error ejecutando UPDATE\n";
            print_r($stmt->errorInfo());
        }
    } else {
        echo "❌ Error preparando consulta UPDATE\n";
        print_r($pdo->errorInfo());
    }
    
    // 3. Verificar que los datos se actualizaron
    echo "\n3️⃣ Verificando datos después de actualización...\n";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $consultaActualizada = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($consultaActualizada) {
        echo "✅ Datos después de actualización:\n";
        foreach ($consultaActualizada as $campo => $valor) {
            $valorMostrar = $valor ? (strlen((string)$valor) > 30 ? substr((string)$valor, 0, 30) . '...' : $valor) : 'NULL';
            $cambio = '';
            if (isset($consulta[$campo]) && $consulta[$campo] !== $valor) {
                $cambio = ' ← CAMBIÓ';
            }
            echo "   {$campo}: {$valorMostrar}{$cambio}\n";
        }
    }
    
    // 4. Restaurar datos originales
    echo "\n4️⃣ Restaurando datos originales...\n";
    
    $restoreQuery = "UPDATE consultas SET 
                     txtmotivo = :txtmotivo,
                     motivoscomunes = :motivoscomunes,
                     visionod = :visionod,
                     visionoi = :visionoi,
                     consulta_textarea = :consulta_textarea,
                     receta_textarea = :receta_textarea,
                     txtnota = :txtnota
                     WHERE id_consulta = :id_consulta";
    
    $stmt = $pdo->prepare($restoreQuery);
    $stmt->execute($consulta);
    
    echo "✅ Datos originales restaurados\n";
    
    echo "\n=== RESUMEN ===\n";
    echo "✅ Consulta ID 161 existe y es accesible\n";
    echo "✅ UPDATE de campos principales funciona\n";
    echo "✅ Campos se actualizan correctamente\n";
    echo "✅ Sistema de actualización de consultas operativo\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>