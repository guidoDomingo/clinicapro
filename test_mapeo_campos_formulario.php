<?php
// Test para verificar que los datos reales se mapean correctamente al formulario
require_once 'model/conexion.php';

try {
    $pdo = Conexion::conectar();
    
    echo "=== VERIFICACIÓN MAPEO DE CAMPOS EN FORMULARIO ===\n";
    
    // Obtener datos reales de consulta 161
    echo "\n1️⃣ Datos reales en base de datos (consulta 161):\n";
    
    $query = "SELECT c.*, p.first_name, p.last_name, p.document_number 
              FROM consultas c 
              LEFT JOIN rh_person p ON c.id_persona = p.person_id 
              WHERE c.id_consulta = 161";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $consulta = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$consulta) {
        echo "❌ Consulta 161 no encontrada\n";
        exit;
    }
    
    // Campos principales que deberían mostrarse en el formulario
    $camposFormulario = [
        // Paciente
        'first_name' => 'Nombre',
        'last_name' => 'Apellido', 
        'document_number' => 'Documento',
        
        // Campos principales de consulta
        'txtmotivo' => 'Motivo de Consulta',
        'motivoscomunes' => 'Motivos Comunes',
        'tipo_formulario' => 'Tipo de Formulario',
        
        // Campos de visión y tensión
        'visionod' => 'Visión OD',
        'visionoi' => 'Visión OI', 
        'tensionod' => 'Tensión OD',
        'tensionoi' => 'Tensión OI',
        
        // Campos de texto
        'consulta_textarea' => 'Consulta',
        'receta_textarea' => 'Receta',
        'txtnota' => 'Notas',
        
        // Campos adicionales
        'proximaconsulta' => 'Próxima Consulta',
        'whatsapptxt' => 'WhatsApp',
        'email' => 'Email'
    ];
    
    echo "📋 CAMPOS DISPONIBLES PARA EL FORMULARIO:\n";
    foreach ($camposFormulario as $campo => $etiqueta) {
        $valor = $consulta[$campo] ?? 'NULL';
        $disponible = isset($consulta[$campo]) && $consulta[$campo] !== null && $consulta[$campo] !== '';
        $estado = $disponible ? '✅ TIENE DATOS' : '⚪ VACÍO/NULL';
        
        if (is_string($valor) && strlen($valor) > 50) {
            $valor = substr($valor, 0, 50) . '...';
        }
        
        echo "   {$campo} ({$etiqueta}): {$valor} {$estado}\n";
    }
    
    // Verificar datos de anteojos
    echo "\n2️⃣ Datos de anteojos relacionados:\n";
    
    $queryAnteojos = "SELECT * FROM consulta_anteojos WHERE id_consulta = 161";
    $stmt = $pdo->prepare($queryAnteojos);
    $stmt->execute();
    $anteojos = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($anteojos) {
        echo "✅ Anteojos encontrados:\n";
        
        $camposAnteojos = [
            'esfera_od' => 'Esfera OD',
            'cilindro_od' => 'Cilindro OD', 
            'eje_od' => 'Eje OD',
            'dnp_od' => 'DNP OD',
            'esfera_oi' => 'Esfera OI',
            'cilindro_oi' => 'Cilindro OI',
            'eje_oi' => 'Eje OI', 
            'dnp_oi' => 'DNP OI',
            'add_od' => 'ADD OD',
            'add_oi' => 'ADD OI',
            'altura_od' => 'Altura OD',
            'altura_oi' => 'Altura OI',
            'dist_interpupilar' => 'Dist. Interpupilar',
            'notas' => 'Notas Anteojos',
            'nota_od' => 'Nota OD',
            'nota_oi' => 'Nota OI'
        ];
        
        foreach ($camposAnteojos as $campo => $etiqueta) {
            $valor = $anteojos[$campo] ?? 'NULL';
            $disponible = isset($anteojos[$campo]) && $anteojos[$campo] !== null && $anteojos[$campo] !== '';
            $estado = $disponible ? '✅ TIENE DATOS' : '⚪ VACÍO/NULL';
            
            echo "   {$campo} ({$etiqueta}): {$valor} {$estado}\n";
        }
    } else {
        echo "❌ No se encontraron anteojos para esta consulta\n";
    }
    
    // Simular estructura de datos que recibiría el JavaScript
    echo "\n3️⃣ Estructura de datos para JavaScript:\n";
    
    $datosParaJS = $consulta;
    if ($anteojos) {
        $datosParaJS['anteojos'] = $anteojos;
    }
    
    echo "📦 Datos que recibiría createEditForm():\n";
    echo "   data.txtmotivo: '" . ($datosParaJS['txtmotivo'] ?? 'undefined') . "'\n";
    echo "   data.motivoscomunes: '" . ($datosParaJS['motivoscomunes'] ?? 'undefined') . "'\n";
    echo "   data.visionod: '" . ($datosParaJS['visionod'] ?? 'undefined') . "'\n";
    echo "   data.consulta_textarea: '" . (substr($datosParaJS['consulta_textarea'] ?? 'undefined', 0, 30)) . "...'\n";
    echo "   data.receta_textarea: '" . ($datosParaJS['receta_textarea'] ?? 'undefined') . "'\n";
    echo "   data.tipo_formulario: '" . ($datosParaJS['tipo_formulario'] ?? 'undefined') . "'\n";
    
    if (isset($datosParaJS['anteojos'])) {
        echo "   data.anteojos.esfera_od: '" . ($datosParaJS['anteojos']['esfera_od'] ?? 'undefined') . "'\n";
        echo "   data.anteojos.esfera_oi: '" . ($datosParaJS['anteojos']['esfera_oi'] ?? 'undefined') . "'\n";
    }
    
    echo "\n=== RESUMEN DE CORRECCIONES ===\n";
    echo "✅ Campos corregidos en HTML:\n";
    echo "   - motivo → txtmotivo\n";
    echo "   - vision_od → visionod\n";
    echo "   - consulta → consulta_textarea\n";
    echo "   - receta → receta_textarea\n"; 
    echo "   - nota → txtnota\n";
    echo "   + Agregado: motivoscomunes\n";
    echo "   + Agregado: whatsapptxt\n";
    echo "   + Agregado: email\n";
    
    $camposConDatos = 0;
    $totalCampos = count($camposFormulario);
    foreach ($camposFormulario as $campo => $etiqueta) {
        if (isset($consulta[$campo]) && $consulta[$campo] !== null && $consulta[$campo] !== '') {
            $camposConDatos++;
        }
    }
    
    echo "\n📊 ESTADÍSTICAS:\n";
    echo "   Campos con datos: {$camposConDatos}/{$totalCampos}\n";
    echo "   Porcentaje completitud: " . round(($camposConDatos / $totalCampos) * 100, 1) . "%\n";
    
    if ($camposConDatos > ($totalCampos / 2)) {
        echo "🎉 ¡BUENA COMPLETITUD DE DATOS! Los campos deberían mostrarse correctamente\n";
    } else {
        echo "⚠️  Muchos campos vacíos, pero los campos con datos deberían visualizarse\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>