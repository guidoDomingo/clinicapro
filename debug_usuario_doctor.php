<?php
try {
    $pdo = new PDO('pgsql:host=localhost;dbname=clinica', 'postgres', 'admin');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "🔍 VERIFICANDO RELACIÓN USUARIO-DOCTOR:\n\n";
    
    // Verificar el doctor_id para usuario 9
    $stmt = $pdo->prepare("
        SELECT psu.system_user_id, psu.person_id, rd.doctor_id 
        FROM person_system_user psu 
        INNER JOIN rh_doctors rd ON psu.person_id = rd.person_id 
        WHERE psu.system_user_id = ?
    ");
    $stmt->execute([9]);
    $usuarioDoctor = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($usuarioDoctor) {
        echo "👤 Usuario 9 → Person ID: " . $usuarioDoctor['person_id'] . " → Doctor ID: " . $usuarioDoctor['doctor_id'] . "\n\n";
        
        $doctorId = $usuarioDoctor['doctor_id'];
        
        // Verificar todos los preformatos de ese doctor
        echo "🔍 Preformatos del doctor " . $doctorId . ":\n";
        $stmt = $pdo->prepare("
            SELECT id_preformato, nombre, tipo_formulario, tipo, activo
            FROM preformatos 
            WHERE creado_por = ?
            ORDER BY tipo_formulario, nombre
        ");
        $stmt->execute([$doctorId]);
        $preformatos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($preformatos as $p) {
            echo "  - ID: " . $p['id_preformato'] . ", Nombre: " . $p['nombre'] . ", Tipo Form: " . $p['tipo_formulario'] . ", Tipo: " . $p['tipo'] . ", Activo: " . ($p['activo'] ? 'Sí' : 'No') . "\n";
        }
        
        // Ahora ejecutar la consulta exacta de la API con debugging
        echo "\n🔍 Consulta API paso a paso:\n";
        echo "  - Usuario ID: 9\n";
        echo "  - Doctor ID asociado: " . $doctorId . "\n";
        echo "  - Tipo formulario: anteojos\n";
        echo "  - Tipo preformato: consulta\n\n";
        
        $stmt = $pdo->prepare("
            SELECT p.id_preformato as id, p.nombre, p.tipo_formulario, p.tipo,
                   CASE WHEN p.tipo_formulario = :tipo_formulario_order THEN 0 ELSE 1 END as order_priority
            FROM person_system_user psu 
            INNER JOIN rh_doctors rd ON psu.person_id = rd.person_id 
            INNER JOIN preformatos p ON p.creado_por = rd.doctor_id 
            WHERE psu.system_user_id = :user_id 
              AND p.activo = true
              AND (p.tipo_formulario = :tipo_formulario OR p.tipo_formulario = 'general')
              AND p.tipo = :tipo_preformato
            ORDER BY 
              CASE WHEN p.tipo_formulario = :tipo_formulario2 THEN 0 ELSE 1 END,
              p.nombre
        ");
        
        $stmt->execute([
            'user_id' => 9,
            'tipo_formulario' => 'anteojos',
            'tipo_formulario_order' => 'anteojos',
            'tipo_formulario2' => 'anteojos',
            'tipo_preformato' => 'consulta'
        ]);
        
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "📊 Resultados con orden detallado:\n";
        foreach ($resultados as $r) {
            echo "  - ID: " . $r['id'] . ", Nombre: " . $r['nombre'] . ", Tipo: " . $r['tipo_formulario'] . ", Prioridad: " . $r['order_priority'] . "\n";
        }
        
    } else {
        echo "❌ No se encontró relación usuario-doctor para el usuario 9\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
