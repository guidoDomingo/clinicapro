<?php
try {
    echo "=== VERIFICACIÓN RESERVA ID 68 ===\n\n";
    
    // Conexión directa
    $dsn = "pgsql:host=localhost;port=5432;dbname=clinica";
    $pdo = new PDO($dsn, "postgres", "admin");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✓ Conexión establecida exitosamente\n\n";
    
    // 1. Verificar si existe en la tabla
    echo "1. Verificando existencia directa:\n";
    $stmt = $pdo->prepare('SELECT * FROM servicios_reservas WHERE reserva_id = 68');
    $stmt->execute();
    $reserva = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if($reserva) {
        echo "✓ Reserva encontrada en la tabla:\n";
        foreach($reserva as $key => $value) {
            echo "   $key: $value\n";
        }
        
        echo "\n2. Verificando tablas relacionadas:\n";
        
        // Verificar servicio
        $stmt = $pdo->prepare('SELECT servicio_nombre FROM servicios_medicos WHERE servicio_id = ?');
        $stmt->execute([$reserva['servicio_id']]);
        $servicio = $stmt->fetchColumn();
        echo "   Servicio (ID {$reserva['servicio_id']}): " . ($servicio ?: 'NO ENCONTRADO') . "\n";
        
        // Verificar doctor
        $stmt = $pdo->prepare('SELECT CONCAT(nombres, \' \', apellidos) as nombre FROM rh_person WHERE person_id = ?');
        $stmt->execute([$reserva['doctor_id']]);
        $doctor = $stmt->fetchColumn();
        echo "   Doctor (ID {$reserva['doctor_id']}): " . ($doctor ?: 'NO ENCONTRADO') . "\n";
        
        // Verificar paciente si existe
        if($reserva['paciente_id']) {
            $stmt = $pdo->prepare('SELECT CONCAT(nombres, \' \', apellidos) as nombre FROM pacientes WHERE paciente_id = ?');
            $stmt->execute([$reserva['paciente_id']]);
            $paciente = $stmt->fetchColumn();
            echo "   Paciente (ID {$reserva['paciente_id']}): " . ($paciente ?: 'NO ENCONTRADO') . "\n";
        } else {
            echo "   Paciente: No asignado\n";
        }
        
    } else {
        echo "✗ Reserva NO encontrada en la tabla\n";
    }
    
} catch(Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
?>
