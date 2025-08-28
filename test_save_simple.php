<?php
// Test API Save Simplificado
session_start();
$_SESSION['user_id'] = 1; // Usuario de prueba

header('Content-Type: application/json');

try {
    // 1. Conectar a la base de datos
    require_once 'model/conexion.php';
    $pdo = Conexion::conectar();
    
    if (!$pdo) {
        throw new Exception('Error de conexión a la base de datos');
    }
    
    // 2. Datos de prueba para anteojos
    $testData = [
        'id_persona' => 45, // ID de persona válido
        'txtmotivo' => 'Consulta de prueba de anteojos',
        'od_esf' => '-1.25',
        'od_cil' => '-0.50',
        'od_eje' => '90',
        'od_dnp' => '32',
        'od_add' => '+1.00',
        'od_altura' => '20',
        'od_nota' => 'Nota OD',
        'oi_esf' => '-1.50',
        'oi_cil' => '-0.75',
        'oi_eje' => '85',
        'oi_dnp' => '31',
        'oi_add' => '+1.00',
        'oi_altura' => '20',
        'oi_nota' => 'Nota OI',
        'dist_interpupilar' => '63',
        'consulta_textarea' => 'Consulta de prueba',
        'receta_textarea' => 'Receta de prueba',
        'txtnota' => 'Notas generales',
        'proximaconsulta' => null,
        'whatsapptxt' => '',
        'email' => '',
        'medico_id' => 1
    ];
    
    // 3. Iniciar transacción
    $pdo->beginTransaction();
    
    // 4. Insertar consulta principal
    $sql = "INSERT INTO consultas (
        id_persona, motivo, consulta, receta, nota, 
        proxima_consulta, whatsapp, email, id_usuario, 
        fecha_consulta, tipo_formulario
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?)";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $testData['id_persona'],
        $testData['txtmotivo'],
        $testData['consulta_textarea'],
        $testData['receta_textarea'],
        $testData['txtnota'],
        $testData['proximaconsulta'],
        $testData['whatsapptxt'],
        $testData['email'],
        $_SESSION['user_id'],
        'anteojos'
    ]);
    
    $idConsulta = $pdo->lastInsertId();
    
    // 5. Insertar datos específicos de anteojos
    $sql = "INSERT INTO consulta_anteojos (
        id_consulta, esfera_od, cilindro_od, eje_od, dnp_od, add_od, altura_od, nota_od,
        esfera_oi, cilindro_oi, eje_oi, dnp_oi, add_oi, altura_oi, nota_oi,
        dist_interpupilar, notas
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $idConsulta,
        $testData['od_esf'],
        $testData['od_cil'],
        $testData['od_eje'],
        $testData['od_dnp'],
        $testData['od_add'],
        $testData['od_altura'],
        $testData['od_nota'],
        $testData['oi_esf'],
        $testData['oi_cil'],
        $testData['oi_eje'],
        $testData['oi_dnp'],
        $testData['oi_add'],
        $testData['oi_altura'],
        $testData['oi_nota'],
        $testData['dist_interpupilar'],
        $testData['txtnota']
    ]);
    
    // 6. Confirmar transacción
    $pdo->commit();
    
    // 7. Respuesta exitosa
    echo json_encode([
        'success' => true,
        'message' => 'Consulta de anteojos guardada exitosamente',
        'data' => [
            'id_consulta' => $idConsulta
        ]
    ]);
    
} catch (Exception $e) {
    // Rollback si hay error
    if (isset($pdo) && $pdo) {
        $pdo->rollBack();
    }
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'debug' => [
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]
    ]);
}
?>