<?php
try {
    $pdo = new PDO('pgsql:host=localhost;dbname=clinica', 'postgres', 'admin');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "📝 CREANDO PREFORMATO DE ANTEOJOS PARA CONSULTA:\n\n";
    
    // Crear preformato de anteojos para consulta
    $stmt = $pdo->prepare("
        INSERT INTO preformatos (nombre, contenido, tipo, tipo_formulario, creado_por, activo, fecha_creacion)
        VALUES (:nombre, :contenido, :tipo, :tipo_formulario, :creado_por, :activo, :fecha_creacion)
        RETURNING id_preformato
    ");
    
    $contenido = "<!-- Preformato para Consulta de Anteojos -->
<div class='consulta-anteojos'>
    <h3>Consulta de Anteojos - Evaluación Visual</h3>
    
    <h4>Agudeza Visual:</h4>
    <p><strong>OD:</strong> _______  <strong>OI:</strong> _______</p>
    <p><strong>AO:</strong> _______</p>
    
    <h4>Refracción:</h4>
    <table border='1' style='border-collapse: collapse; width: 100%;'>
        <tr>
            <th>Ojo</th>
            <th>Esfera</th>
            <th>Cilindro</th>
            <th>Eje</th>
            <th>AV</th>
        </tr>
        <tr>
            <td>OD</td>
            <td>_____</td>
            <td>_____</td>
            <td>_____</td>
            <td>_____</td>
        </tr>
        <tr>
            <td>OI</td>
            <td>_____</td>
            <td>_____</td>
            <td>_____</td>
            <td>_____</td>
        </tr>
    </table>
    
    <h4>Evaluación Binocular:</h4>
    <p><strong>Motilidad Ocular:</strong> _________________________</p>
    <p><strong>Convergencia:</strong> _____________________________</p>
    <p><strong>Estereopsis:</strong> _______________________________</p>
    
    <h4>Recomendaciones:</h4>
    <p>☐ Uso permanente de anteojos</p>
    <p>☐ Uso para visión lejana</p>
    <p>☐ Uso para visión cercana</p>
    <p>☐ Control en _____ meses</p>
    
    <h4>Observaciones:</h4>
    <p>_________________________________________________</p>
    <p>_________________________________________________</p>
</div>";
    
    $result = $stmt->execute([
        'nombre' => 'Consulta de Anteojos - Evaluación Visual',
        'contenido' => $contenido,
        'tipo' => 'consulta',
        'tipo_formulario' => 'anteojos',
        'creado_por' => 18, // Doctor ID del usuario 9
        'activo' => true,
        'fecha_creacion' => date('Y-m-d H:i:s')
    ]);
    
    if ($result) {
        $newId = $pdo->lastInsertId();
        echo "✅ Preformato de consulta de anteojos creado exitosamente!\n";
        echo "   - ID: " . $newId . "\n";
        echo "   - Nombre: Consulta de Anteojos - Evaluación Visual\n";
        echo "   - Tipo: consulta\n";
        echo "   - Tipo Formulario: anteojos\n";
        echo "   - Creado por: Doctor 18 (Usuario 9)\n\n";
        
        // Ahora probar la consulta API de nuevo
        echo "🔍 Probando consulta API después de crear el preformato:\n";
        $stmt = $pdo->prepare("
            SELECT p.id_preformato as id, p.nombre, p.tipo_formulario
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
        
        echo "📊 Nuevos resultados:\n";
        foreach ($resultados as $r) {
            echo "  - ID: " . $r['id'] . ", Nombre: " . $r['nombre'] . ", Tipo: " . $r['tipo_formulario'] . "\n";
        }
        
    } else {
        echo "❌ Error al crear el preformato\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
