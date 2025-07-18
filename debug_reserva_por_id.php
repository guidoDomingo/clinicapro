<?php
/**
 * Script de diagnóstico para debug de obtener reserva por ID
 * Conexión directa a PostgreSQL
 */

// Configuración de la base de datos
$host = 'localhost';
$port = '5432';
$dbname = 'clinica';
$username = 'postgres';
$password = 'admin';

try {
    // Conexión directa a PostgreSQL
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h2>Diagnóstico de Reserva por ID</h2>";
    echo "<p>Conexión a PostgreSQL: <strong style='color: green;'>EXITOSA</strong></p>";
    
    // ID de la reserva a probar
    $reservaId = 68;
    
    echo "<h3>1. Verificando existencia de la reserva ID: $reservaId</h3>";
    
    // Consulta simple para verificar si existe
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM servicios_reservas WHERE reserva_id = :reserva_id");
    $stmt->bindParam(":reserva_id", $reservaId, PDO::PARAM_INT);
    $stmt->execute();
    $count = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "<p>Registros encontrados: <strong>" . $count['count'] . "</strong></p>";
    
    if ($count['count'] > 0) {
        echo "<h3>2. Datos básicos de la reserva</h3>";
        
        // Consulta básica de la reserva
        $stmt = $pdo->prepare("SELECT * FROM servicios_reservas WHERE reserva_id = :reserva_id");
        $stmt->bindParam(":reserva_id", $reservaId, PDO::PARAM_INT);
        $stmt->execute();
        $reserva = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
        foreach ($reserva as $campo => $valor) {
            echo "<tr><td style='padding: 5px; font-weight: bold;'>$campo</td><td style='padding: 5px;'>$valor</td></tr>";
        }
        echo "</table>";
        
        echo "<h3>3. Verificando tablas relacionadas</h3>";
        
        // Verificar servicios_medicos
        if ($reserva['servicio_id']) {
            $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM servicios_medicos WHERE servicio_id = :servicio_id");
            $stmt->bindParam(":servicio_id", $reserva['servicio_id'], PDO::PARAM_INT);
            $stmt->execute();
            $servicioCount = $stmt->fetch(PDO::FETCH_ASSOC);
            echo "<p>Servicio (ID: {$reserva['servicio_id']}): " . ($servicioCount['count'] > 0 ? "✓ Existe" : "✗ No existe") . "</p>";
        }
        
        // Verificar rh_person (doctor)
        if ($reserva['doctor_id']) {
            $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM rh_person WHERE person_id = :doctor_id");
            $stmt->bindParam(":doctor_id", $reserva['doctor_id'], PDO::PARAM_INT);
            $stmt->execute();
            $doctorCount = $stmt->fetch(PDO::FETCH_ASSOC);
            echo "<p>Doctor (ID: {$reserva['doctor_id']}): " . ($doctorCount['count'] > 0 ? "✓ Existe" : "✗ No existe") . "</p>";
        }
        
        // Verificar pacientes
        if ($reserva['paciente_id']) {
            $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM pacientes WHERE paciente_id = :paciente_id");
            $stmt->bindParam(":paciente_id", $reserva['paciente_id'], PDO::PARAM_INT);
            $stmt->execute();
            $pacienteCount = $stmt->fetch(PDO::FETCH_ASSOC);
            echo "<p>Paciente (ID: {$reserva['paciente_id']}): " . ($pacienteCount['count'] > 0 ? "✓ Existe" : "✗ No existe") . "</p>";
        }
        
        // Verificar salas
        if ($reserva['sala_id']) {
            $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM salas WHERE sala_id = :sala_id");
            $stmt->bindParam(":sala_id", $reserva['sala_id'], PDO::PARAM_INT);
            $stmt->execute();
            $salaCount = $stmt->fetch(PDO::FETCH_ASSOC);
            echo "<p>Sala (ID: {$reserva['sala_id']}): " . ($salaCount['count'] > 0 ? "✓ Existe" : "✗ No existe") . "</p>";
        }
        
        echo "<h3>4. Probando consulta completa con JOINs</h3>";
        
        // Consulta completa como en el modelo original
        $sql = "SELECT 
                    sr.reserva_id,
                    sr.servicio_id,
                    sm.servicio_nombre,
                    sm.duracion_minutos,
                    sr.agenda_id,
                    sr.doctor_id,
                    CONCAT(rp.nombres, ' ', rp.apellidos) as doctor_nombre,
                    sr.paciente_id,
                    CONCAT(p.nombres, ' ', p.apellidos) as paciente_nombre,
                    p.cedula,
                    p.telefono,
                    p.email,
                    sr.fecha_reserva,
                    sr.hora_inicio,
                    sr.hora_fin,
                    sr.sala_id,
                    s.sala_nombre,
                    sr.reserva_estado,
                    sr.observaciones,
                    sr.created_at,
                    sr.updated_at
                FROM 
                    servicios_reservas sr
                INNER JOIN 
                    servicios_medicos sm ON sr.servicio_id = sm.servicio_id
                INNER JOIN 
                    rh_person rp ON sr.doctor_id = rp.person_id
                LEFT JOIN 
                    pacientes p ON sr.paciente_id = p.paciente_id
                LEFT JOIN 
                    salas s ON sr.sala_id = s.sala_id
                WHERE 
                    sr.reserva_id = :reserva_id";
        
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(":reserva_id", $reservaId, PDO::PARAM_INT);
            $stmt->execute();
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($resultado) {
                echo "<p style='color: green;'><strong>✓ Consulta completa EXITOSA</strong></p>";
                echo "<h4>Resultado:</h4>";
                echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
                foreach ($resultado as $campo => $valor) {
                    echo "<tr><td style='padding: 5px; font-weight: bold;'>$campo</td><td style='padding: 5px;'>$valor</td></tr>";
                }
                echo "</table>";
            } else {
                echo "<p style='color: red;'><strong>✗ Consulta completa devolvió resultado VACÍO</strong></p>";
                echo "<p>Esto indica que hay un problema con los JOINs.</p>";
            }
            
        } catch (Exception $e) {
            echo "<p style='color: red;'><strong>✗ Error en consulta completa:</strong> " . $e->getMessage() . "</p>";
        }
        
        echo "<h3>5. Probando consulta simplificada con LEFT JOINs</h3>";
        
        // Consulta simplificada con LEFT JOINs
        $sqlSimple = "SELECT 
                        sr.reserva_id,
                        sr.servicio_id,
                        COALESCE(sm.servicio_nombre, 'Servicio no encontrado') as servicio_nombre,
                        COALESCE(sm.duracion_minutos, 0) as duracion_minutos,
                        sr.agenda_id,
                        sr.doctor_id,
                        COALESCE(CONCAT(rp.nombres, ' ', rp.apellidos), 'Doctor no encontrado') as doctor_nombre,
                        sr.paciente_id,
                        COALESCE(CONCAT(p.nombres, ' ', p.apellidos), 'Paciente no encontrado') as paciente_nombre,
                        COALESCE(p.cedula, '') as cedula,
                        COALESCE(p.telefono, '') as telefono,
                        COALESCE(p.email, '') as email,
                        sr.fecha_reserva,
                        sr.hora_inicio,
                        sr.hora_fin,
                        sr.sala_id,
                        COALESCE(s.sala_nombre, 'Sin sala') as sala_nombre,
                        sr.reserva_estado,
                        COALESCE(sr.observaciones, '') as observaciones,
                        sr.created_at,
                        sr.updated_at
                    FROM 
                        servicios_reservas sr
                    LEFT JOIN 
                        servicios_medicos sm ON sr.servicio_id = sm.servicio_id
                    LEFT JOIN 
                        rh_person rp ON sr.doctor_id = rp.person_id
                    LEFT JOIN 
                        pacientes p ON sr.paciente_id = p.paciente_id
                    LEFT JOIN 
                        salas s ON sr.sala_id = s.sala_id
                    WHERE 
                        sr.reserva_id = :reserva_id";
        
        try {
            $stmt = $pdo->prepare($sqlSimple);
            $stmt->bindParam(":reserva_id", $reservaId, PDO::PARAM_INT);
            $stmt->execute();
            $resultadoSimple = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($resultadoSimple) {
                echo "<p style='color: green;'><strong>✓ Consulta simplificada EXITOSA</strong></p>";
                echo "<h4>Resultado simplificado:</h4>";
                echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
                foreach ($resultadoSimple as $campo => $valor) {
                    echo "<tr><td style='padding: 5px; font-weight: bold;'>$campo</td><td style='padding: 5px;'>$valor</td></tr>";
                }
                echo "</table>";
            } else {
                echo "<p style='color: red;'><strong>✗ Consulta simplificada también falló</strong></p>";
            }
            
        } catch (Exception $e) {
            echo "<p style='color: red;'><strong>✗ Error en consulta simplificada:</strong> " . $e->getMessage() . "</p>";
        }
        
    } else {
        echo "<p style='color: red;'>La reserva con ID $reservaId no existe en la tabla servicios_reservas.</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'><strong>Error de conexión:</strong> " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><a href='javascript:history.back()'>← Volver</a></p>";
?>
