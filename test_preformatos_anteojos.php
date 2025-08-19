<?php
/**
 * TEST ESPECÍFICO PARA PREFORMATOS DE ANTEOJOS
 * Prueba directa usando la estructura correcta de tablas
 */

header('Content-Type: text/html; charset=UTF-8');
require_once "model/conexion.php";

$conexion = Conexion::conectar();

echo "<h1>🧪 Test Específico - Preformatos de Anteojos</h1>";
echo "<p><strong>Fecha:</strong> " . date('Y-m-d H:i:s') . "</p>";
echo "<p><strong>Usuario de Prueba:</strong> ID 9 (debería ser doctor ID 18)</p>";

// Test 1: Verificar relación usuario-doctor
echo "<h2>📋 1. Verificación Usuario-Doctor</h2>";

try {
    $userDoctorQuery = "
        SELECT 
            su.user_id,
            rp.first_name,
            rp.last_name,
            rd.doctor_id
        FROM sys_users su 
        INNER JOIN person_system_user psu ON su.user_id = psu.system_user_id 
        INNER JOIN rh_person rp ON rp.person_id = psu.person_id 
        INNER JOIN rh_doctors rd ON rd.person_id = rp.person_id 
        WHERE su.user_id = 9
    ";
    
    $stmt = $conexion->prepare($userDoctorQuery);
    $stmt->execute();
    $userDoctor = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($userDoctor) {
        echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px;'>";
        echo "<strong>✅ Relación encontrada:</strong><br>";
        echo "User ID: {$userDoctor['user_id']}<br>";
        echo "Nombre: {$userDoctor['first_name']} {$userDoctor['last_name']}<br>";
        echo "Doctor ID: <strong>{$userDoctor['doctor_id']}</strong><br>";
        echo "</div>";
        
        $doctorId = $userDoctor['doctor_id'];
    } else {
        echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; border-radius: 5px;'>";
        echo "❌ No se encontró relación usuario-doctor para user_id = 9";
        echo "</div>";
        exit;
    }
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; border-radius: 5px;'>";
    echo "❌ Error verificando relación: " . $e->getMessage();
    echo "</div>";
    exit;
}

// Test 2: Buscar preformatos del doctor
echo "<h2>🔍 2. Preformatos del Doctor ID $doctorId</h2>";

try {
    $preformatosQuery = "
        SELECT 
            id_preformato,
            nombre,
            tipo_formulario,
            tipo,
            activo,
            creado_por
        FROM preformatos 
        WHERE creado_por = :doctor_id 
          AND activo = true
        ORDER BY tipo_formulario, tipo, nombre
    ";
    
    $stmt = $conexion->prepare($preformatosQuery);
    $stmt->bindParam(':doctor_id', $doctorId, PDO::PARAM_INT);
    $stmt->execute();
    $preformatos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<p><strong>Total preformatos encontrados:</strong> " . count($preformatos) . "</p>";
    
    if (!empty($preformatos)) {
        echo "<table style='border-collapse: collapse; width: 100%; border: 1px solid #ddd;'>";
        echo "<thead style='background: #f8f9fa;'>";
        echo "<tr>";
        echo "<th style='border: 1px solid #ddd; padding: 8px;'>ID</th>";
        echo "<th style='border: 1px solid #ddd; padding: 8px;'>Nombre</th>";
        echo "<th style='border: 1px solid #ddd; padding: 8px;'>Tipo Formulario</th>";
        echo "<th style='border: 1px solid #ddd; padding: 8px;'>Tipo</th>";
        echo "<th style='border: 1px solid #ddd; padding: 8px;'>Activo</th>";
        echo "</tr>";
        echo "</thead>";
        echo "<tbody>";
        
        foreach ($preformatos as $p) {
            $rowColor = ($p['tipo_formulario'] === 'anteojos' && $p['tipo'] === 'consulta') ? '#fff3cd' : 'white';
            echo "<tr style='background: $rowColor;'>";
            echo "<td style='border: 1px solid #ddd; padding: 8px;'>{$p['id_preformato']}</td>";
            echo "<td style='border: 1px solid #ddd; padding: 8px;'>{$p['nombre']}</td>";
            echo "<td style='border: 1px solid #ddd; padding: 8px;'>{$p['tipo_formulario']}</td>";
            echo "<td style='border: 1px solid #ddd; padding: 8px;'>{$p['tipo']}</td>";
            echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . ($p['activo'] ? 'Sí' : 'No') . "</td>";
            echo "</tr>";
        }
        
        echo "</tbody>";
        echo "</table>";
        
        // Destacar los de anteojos tipo consulta
        $anteojosConsulta = array_filter($preformatos, function($p) {
            return $p['tipo_formulario'] === 'anteojos' && $p['tipo'] === 'consulta';
        });
        
        if (!empty($anteojosConsulta)) {
            echo "<div style='background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
            echo "<strong>🎯 Preformatos de Anteojos tipo 'consulta':</strong> " . count($anteojosConsulta);
            echo "</div>";
        }
    }
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; border-radius: 5px;'>";
    echo "❌ Error buscando preformatos: " . $e->getMessage();
    echo "</div>";
}

// Test 3: Simular llamada API completa
echo "<h2>🔬 3. Simulación API Completa</h2>";

try {
    $apiQuery = "
        SELECT p.id_preformato as id, p.nombre, p.contenido, p.tipo as categoria 
        FROM sys_users su 
        INNER JOIN person_system_user psu ON su.user_id = psu.system_user_id 
        INNER JOIN rh_person rp ON rp.person_id = psu.person_id 
        INNER JOIN rh_doctors rd ON rd.person_id = rp.person_id 
        INNER JOIN preformatos p ON p.creado_por = rd.doctor_id 
        WHERE su.user_id = :user_id 
          AND p.activo = true
          AND p.tipo_formulario = :tipo_formulario
          AND p.tipo = :tipo_preformato
        ORDER BY p.nombre
        LIMIT 20
    ";
    
    $stmt = $conexion->prepare($apiQuery);
    $stmt->bindValue(':user_id', 9, PDO::PARAM_INT);
    $stmt->bindValue(':tipo_formulario', 'anteojos', PDO::PARAM_STR);
    $stmt->bindValue(':tipo_preformato', 'consulta', PDO::PARAM_STR);
    $stmt->execute();
    $apiResult = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<p><strong>Parámetros de prueba:</strong></p>";
    echo "<ul>";
    echo "<li>user_id: 9</li>";
    echo "<li>tipo_formulario: 'anteojos'</li>";
    echo "<li>tipo_preformato: 'consulta'</li>";
    echo "</ul>";
    
    echo "<p><strong>Resultado API:</strong> " . count($apiResult) . " preformatos</p>";
    
    if (!empty($apiResult)) {
        echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px;'>";
        echo "<strong>✅ API funcionando correctamente!</strong><br>";
        foreach ($apiResult as $result) {
            echo "• ID: {$result['id']} - {$result['nombre']}<br>";
        }
        echo "</div>";
        
        // Mostrar JSON que recibiría el frontend
        echo "<h3>📤 JSON Response</h3>";
        echo "<pre style='background: #f8f9fa; border: 1px solid #ddd; padding: 15px; border-radius: 5px; overflow-x: auto;'>";
        echo json_encode([
            'success' => true,
            'data' => $apiResult,
            'preformatos' => $apiResult
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        echo "</pre>";
        
    } else {
        echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; border-radius: 5px;'>";
        echo "❌ API no devuelve resultados";
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; border-radius: 5px;'>";
    echo "❌ Error en simulación API: " . $e->getMessage();
    echo "</div>";
}

// Test 4: Verificar llamada directa al endpoint
echo "<h2>🌐 4. Test Endpoint Real</h2>";

$testUrl = "modules/consultas/api/consultas-api.php?action=get_preformatos_consulta&tipo_formulario=anteojos&tipo=consulta&usuario_id=9";

echo "<p><strong>URL de prueba:</strong></p>";
echo "<p><a href='$testUrl' target='_blank' style='background: #007bff; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px;'>🔗 Probar API Endpoint</a></p>";

echo "<p><em>Haz clic en el enlace para ver la respuesta JSON del endpoint real.</em></p>";

?>
