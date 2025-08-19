<?php
try {
    $pdo = new PDO('pgsql:host=localhost;dbname=clinica', 'postgres', 'admin');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "📊 VERIFICANDO PREFORMATOS EN LA BASE DE DATOS:\n\n";
    
    // Verificar preformatos de anteojos
    $stmt = $pdo->query("
        SELECT id_preformato, nombre, tipo_formulario, creado_por, activo
        FROM preformatos 
        WHERE tipo_formulario = 'anteojos'
        ORDER BY id_preformato
    ");
    $anteojosPreformatos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "🔍 Preformatos de anteojos en BD:\n";
    foreach ($anteojosPreformatos as $p) {
        echo "  - ID: " . $p['id_preformato'] . ", Nombre: " . $p['nombre'] . ", Creado por: " . $p['creado_por'] . ", Activo: " . ($p['activo'] ? 'Sí' : 'No') . "\n";
    }
    
    // Verificar la consulta exacta que hace la API
    echo "\n🔍 Simulando consulta de la API para usuario 9 y anteojos:\n";
    $stmt = $pdo->prepare("
        SELECT p.id_preformato as id, p.nombre, p.contenido, p.tipo as categoria 
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
        LIMIT 20
    ");
    
    $stmt->execute([
        'user_id' => 9,
        'tipo_formulario' => 'anteojos',
        'tipo_formulario2' => 'anteojos',
        'tipo_preformato' => 'consulta'
    ]);
    
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "📊 Resultados de la consulta API:\n";
    foreach ($resultados as $r) {
        echo "  - ID: " . $r['id'] . ", Nombre: " . $r['nombre'] . "\n";
    }
    
    // Verificar qué tipo de preformato es el ID 11 (PRUEBAAAA)
    echo "\n🔍 Verificando preformato ID 11 (PRUEBAAAA):\n";
    $stmt = $pdo->query("SELECT id_preformato, nombre, tipo_formulario, tipo, creado_por FROM preformatos WHERE id_preformato = 11");
    $preformato11 = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($preformato11) {
        echo "  - ID: " . $preformato11['id_preformato'] . ", Nombre: " . $preformato11['nombre'] . ", Tipo Form: " . $preformato11['tipo_formulario'] . ", Tipo: " . $preformato11['tipo'] . ", Creado por: " . $preformato11['creado_por'] . "\n";
    } else {
        echo "  - No se encontró preformato con ID 11\n";
    }
    
    if (empty($resultados)) {
        echo "❌ No hay resultados. Verificando doctor_id para usuario 9...\n";
        $stmt = $pdo->prepare("
            SELECT rd.doctor_id 
            FROM person_system_user psu 
            INNER JOIN rh_doctors rd ON psu.person_id = rd.person_id 
            WHERE psu.system_user_id = ?
        ");
        $stmt->execute([9]);
        $doctorId = $stmt->fetchColumn();
        echo "👨‍⚕️ Doctor ID para usuario 9: " . $doctorId . "\n";
        
        // Verificar preformatos para ese doctor
        echo "\n🔍 Preformatos para doctor " . $doctorId . ":\n";
        $stmt = $pdo->prepare("
            SELECT id_preformato, nombre, tipo_formulario, tipo, activo
            FROM preformatos 
            WHERE creado_por = ?
            ORDER BY tipo_formulario, nombre
        ");
        $stmt->execute([$doctorId]);
        $preformatosDoctorResultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($preformatosDoctorResultados as $p) {
            echo "  - ID: " . $p['id_preformato'] . ", Nombre: " . $p['nombre'] . ", Tipo Form: " . $p['tipo_formulario'] . ", Tipo: " . $p['tipo'] . ", Activo: " . ($p['activo'] ? 'Sí' : 'No') . "\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
