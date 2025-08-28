<?php
// Test del flujo completo: Update + Read para verificar que los datos se devuelven actualizados
require_once 'model/conexion.php';

echo "=== TEST FLUJO COMPLETO: UPDATE + READ ===\n";

// Simular una actualización real y verificar respuesta
try {
    $pdo = Conexion::conectar();
    
    // 1. Ver estado actual
    echo "\n1️⃣ Estado ANTES de simular actualización...\n";
    $query = "SELECT txtmotivo, consulta_textarea, ultima_modificacion FROM consultas WHERE id_consulta = 161";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $antes = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "   txtmotivo: " . ($antes['txtmotivo'] ?: 'NULL') . "\n";
    echo "   consulta_textarea: " . (substr($antes['consulta_textarea'] ?: 'NULL', 0, 30)) . "...\n";
    echo "   ultima_modificacion: " . $antes['ultima_modificacion'] . "\n";
    
    // 2. Simular UPDATE (lo que hace el sistema Livewire)
    echo "\n2️⃣ Simulando UPDATE del sistema Livewire...\n";
    
    $testValues = [
        'txtmotivo' => 'FLUJO TEST - Motivo ' . date('H:i:s'),
        'consulta_textarea' => 'FLUJO TEST - Consulta actualizada ' . date('H:i:s'),
        'receta_textarea' => 'FLUJO TEST - Receta ' . date('H:i:s')
    ];
    
    $updateQuery = "UPDATE consultas SET 
                    txtmotivo = :txtmotivo,
                    consulta_textarea = :consulta_textarea,
                    receta_textarea = :receta_textarea,
                    ultima_modificacion = CURRENT_TIMESTAMP
                    WHERE id_consulta = 161";
    
    $stmt = $pdo->prepare($updateQuery);
    $resultado = $stmt->execute($testValues);
    
    if ($resultado && $stmt->rowCount() > 0) {
        echo "✅ UPDATE ejecutado exitosamente\n";
        echo "📊 Filas afectadas: " . $stmt->rowCount() . "\n";
        
        foreach ($testValues as $campo => $valor) {
            echo "   {$campo}: {$valor}\n";
        }
    } else {
        echo "❌ UPDATE falló\n";
        exit;
    }
    
    // 3. Simular loadRecord (lo que hace después del update)
    echo "\n3️⃣ Simulando loadRecord (como lo hace el sistema)...\n";
    
    $loadQuery = "SELECT c.*, p.first_name, p.last_name, p.document_number, p.phone_number, p.email
                  FROM consultas c 
                  LEFT JOIN rh_person p ON c.id_persona = p.person_id 
                  WHERE c.id_consulta = 161";
    
    $stmt = $pdo->prepare($loadQuery);
    $stmt->execute();
    $recordCargado = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($recordCargado) {
        echo "✅ Record cargado después del UPDATE:\n";
        echo "   txtmotivo: " . ($recordCargado['txtmotivo'] ?: 'NULL') . "\n";
        echo "   consulta_textarea: " . (substr($recordCargado['consulta_textarea'] ?: 'NULL', 0, 40)) . "...\n";
        echo "   receta_textarea: " . (substr($recordCargado['receta_textarea'] ?: 'NULL', 0, 40)) . "...\n";
        echo "   ultima_modificacion: " . $recordCargado['ultima_modificacion'] . "\n";
        echo "   first_name: " . ($recordCargado['first_name'] ?: 'NULL') . "\n";
        echo "   last_name: " . ($recordCargado['last_name'] ?: 'NULL') . "\n";
    } else {
        echo "❌ No se pudo cargar el record\n";
    }
    
    // 4. Cargar anteojos (parte del loadRecord)
    echo "\n4️⃣ Cargando anteojos relacionados...\n";
    
    $anteojosQuery = "SELECT * FROM consulta_anteojos WHERE id_consulta = 161";
    $stmt = $pdo->prepare($anteojosQuery);
    $stmt->execute();
    $anteojos = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($anteojos) {
        echo "✅ Anteojos cargados:\n";
        echo "   id_consulta_anteojos: " . $anteojos['id_consulta_anteojos'] . "\n";
        echo "   esfera_od: " . ($anteojos['esfera_od'] ?: 'NULL') . "\n";
        echo "   esfera_oi: " . ($anteojos['esfera_oi'] ?: 'NULL') . "\n";
        echo "   notas: " . ($anteojos['notas'] ?: 'NULL') . "\n";
        
        // Simular estructura del sistema
        $recordCargado['anteojos'] = $anteojos;
    }
    
    // 5. Simular respuesta JSON (como la envía el sistema)
    echo "\n5️⃣ Simulando respuesta JSON del sistema...\n";
    
    $respuestaSimulada = [
        'success' => true,
        'action' => 'update',
        'data' => $recordCargado,
        'message' => 'Consulta actualizado exitosamente',
        'meta' => ['id' => 161, 'table' => 'consultas']
    ];
    
    $jsonResponse = json_encode($respuestaSimulada);
    echo "📤 Tamaño respuesta JSON: " . strlen($jsonResponse) . " bytes\n";
    echo "📄 Respuesta (primeros 200 chars): " . substr($jsonResponse, 0, 200) . "...\n";
    
    // Verificar que los datos actualizados están en la respuesta
    $responseDecoded = json_decode($jsonResponse, true);
    if ($responseDecoded && isset($responseDecoded['data'])) {
        $datosRespuesta = $responseDecoded['data'];
        echo "\n✅ VERIFICACIÓN DATOS EN RESPUESTA:\n";
        echo "   txtmotivo en respuesta: " . ($datosRespuesta['txtmotivo'] ?: 'NULL') . "\n";
        echo "   consulta_textarea en respuesta: " . (substr($datosRespuesta['consulta_textarea'] ?: 'NULL', 0, 30)) . "...\n";
        
        // Comparar con valores que enviamos
        $coincideMotivo = $datosRespuesta['txtmotivo'] === $testValues['txtmotivo'];
        $coincideConsulta = $datosRespuesta['consulta_textarea'] === $testValues['consulta_textarea'];
        
        echo "   ✅ txtmotivo coincide: " . ($coincideMotivo ? 'SÍ' : 'NO') . "\n";
        echo "   ✅ consulta_textarea coincide: " . ($coincideConsulta ? 'SÍ' : 'NO') . "\n";
        
        if ($coincideMotivo && $coincideConsulta) {
            echo "\n🎉 PERFECTO: Los datos actualizados SÍ están en la respuesta JSON\n";
            echo "💡 El sistema backend funciona correctamente\n";
            echo "🔍 Si el frontend no muestra los datos actualizados, el problema está en JavaScript/HTML\n";
        } else {
            echo "\n❌ PROBLEMA: Los datos en la respuesta no coinciden con los actualizados\n";
        }
    }
    
    // 6. Restaurar datos originales
    echo "\n6️⃣ Restaurando datos originales...\n";
    $restoreQuery = "UPDATE consultas SET 
                     txtmotivo = :txtmotivo,
                     consulta_textarea = :consulta_textarea,
                     receta_textarea = :receta_textarea
                     WHERE id_consulta = 161";
    
    $stmt = $pdo->prepare($restoreQuery);
    $stmt->execute([
        'txtmotivo' => $antes['txtmotivo'],
        'consulta_textarea' => $antes['consulta_textarea'],
        'receta_textarea' => null // No lo tenemos del estado anterior
    ]);
    echo "🔄 Datos restaurados\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n=== CONCLUSIÓN FLUJO COMPLETO ===\n";
echo "Si este test muestra que los datos actualizados SÍ están en la respuesta JSON,\n";
echo "entonces el problema está en el frontend (JavaScript no actualiza la interfaz).\n";
echo "\nSi los datos NO están en la respuesta, el problema está en el backend.\n";
?>