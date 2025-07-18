<?php
/**
 * Script para insertar datos de ejemplo en las tablas de agenda
 * para poder probar la funcionalidad de doctores por fecha
 */

require_once "model/conexion.php";

header('Content-Type: text/plain; charset=utf-8');

echo "=== INSERCIÓN DE DATOS DE EJEMPLO PARA AGENDA ===\n";
echo "Fecha: " . date('Y-m-d H:i:s') . "\n\n";

try {
    $conexion = Conexion::conectar();
    if (!$conexion) {
        throw new Exception("Error de conexión a la base de datos");
    }
    
    echo "✅ Conexión exitosa\n\n";
    
    // Verificar si ya existen datos
    $stmt = $conexion->query("SELECT COUNT(*) FROM agendas_detalle WHERE dia_semana = 'JUEVES'");
    $existentes = $stmt->fetchColumn();
    
    if ($existentes > 0) {
        echo "⚠️ Ya existen $existentes registros para JUEVES en agendas_detalle\n";
        echo "Mostrando registros existentes...\n\n";
        
        $stmt = $conexion->query("
            SELECT 
                rp.first_name,
                rp.last_name,
                rd.doctor_id,
                ad.hora_inicio,
                ad.hora_fin,
                ad.dia_semana
            FROM agendas_detalle ad 
            INNER JOIN agendas_cabecera ac ON ad.agenda_id = ac.agenda_id 
            INNER JOIN rh_doctors rd ON rd.doctor_id = ac.medico_id 
            INNER JOIN rh_person rp ON rd.person_id = rp.person_id 
            WHERE ad.dia_semana = 'JUEVES'
        ");
        
        $existentes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($existentes as $doctor) {
            echo "- Dr. {$doctor['first_name']} {$doctor['last_name']} (ID: {$doctor['doctor_id']}) - {$doctor['hora_inicio']} a {$doctor['hora_fin']}\n";
        }
        exit;
    }
    
    echo "=== VERIFICANDO DOCTORES DISPONIBLES ===\n";
    
    // Verificar qué doctores existen
    $stmt = $conexion->query("
        SELECT 
            rd.doctor_id,
            rp.first_name,
            rp.last_name,
            rd.especialidad
        FROM rh_doctors rd 
        INNER JOIN rh_person rp ON rd.person_id = rp.person_id 
        WHERE rd.doctor_estado = 'ACTIVO'
        LIMIT 5
    ");
    
    $doctores = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($doctores)) {
        echo "❌ No se encontraron doctores activos en la base de datos\n";
        echo "Es necesario tener doctores registrados primero\n";
        exit;
    }
    
    echo "Doctores disponibles para crear agendas:\n";
    foreach ($doctores as $doctor) {
        echo "- Dr. {$doctor['first_name']} {$doctor['last_name']} (ID: {$doctor['doctor_id']}) - {$doctor['especialidad']}\n";
    }
    echo "\n";
    
    // Comenzar transacción
    $conexion->beginTransaction();
    
    echo "=== CREANDO AGENDAS DE EJEMPLO ===\n";
    
    $agendaCreada = false;
    
    foreach ($doctores as $i => $doctor) {
        try {
            // Crear agenda_cabecera
            $stmt = $conexion->prepare("
                INSERT INTO agendas_cabecera (medico_id, agenda_estado, fecha_creacion)
                VALUES (:medico_id, true, NOW())
                RETURNING agenda_id
            ");
            $stmt->bindParam(':medico_id', $doctor['doctor_id'], PDO::PARAM_INT);
            $stmt->execute();
            $agendaId = $stmt->fetchColumn();
            
            echo "✅ Agenda creada para Dr. {$doctor['first_name']} {$doctor['last_name']} (agenda_id: $agendaId)\n";
            
            // Crear detalles de agenda para diferentes días
            $dias = ['LUNES', 'MARTES', 'MIERCOLES', 'JUEVES', 'VIERNES'];
            $horarios = [
                ['08:00:00', '12:00:00'],
                ['14:00:00', '18:00:00']
            ];
            
            foreach ($dias as $dia) {
                foreach ($horarios as $horario) {
                    $stmt = $conexion->prepare("
                        INSERT INTO agendas_detalle (
                            agenda_id, 
                            dia_semana, 
                            hora_inicio, 
                            hora_fin, 
                            intervalo_minutos, 
                            detalle_estado
                        ) VALUES (
                            :agenda_id, 
                            :dia_semana, 
                            :hora_inicio, 
                            :hora_fin, 
                            30, 
                            true
                        )
                    ");
                    
                    $stmt->bindParam(':agenda_id', $agendaId, PDO::PARAM_INT);
                    $stmt->bindParam(':dia_semana', $dia, PDO::PARAM_STR);
                    $stmt->bindParam(':hora_inicio', $horario[0], PDO::PARAM_STR);
                    $stmt->bindParam(':hora_fin', $horario[1], PDO::PARAM_STR);
                    $stmt->execute();
                    
                    if ($dia === 'JUEVES') {
                        echo "  ✅ Horario JUEVES: {$horario[0]} - {$horario[1]}\n";
                        $agendaCreada = true;
                    }
                }
            }
            
            // Solo crear agenda para los primeros 2 doctores
            if ($i >= 1) break;
            
        } catch (Exception $e) {
            echo "❌ Error al crear agenda para Dr. {$doctor['first_name']} {$doctor['last_name']}: " . $e->getMessage() . "\n";
        }
    }
    
    if ($agendaCreada) {
        $conexion->commit();
        echo "\n✅ AGENDAS CREADAS EXITOSAMENTE\n";
        
        // Verificar los datos creados
        echo "\n=== VERIFICACIÓN FINAL ===\n";
        $stmt = $conexion->query("
            SELECT 
                rp.first_name,
                rp.last_name,
                rd.doctor_id,
                ad.hora_inicio,
                ad.hora_fin
            FROM agendas_detalle ad 
            INNER JOIN agendas_cabecera ac ON ad.agenda_id = ac.agenda_id 
            INNER JOIN rh_doctors rd ON rd.doctor_id = ac.medico_id 
            INNER JOIN rh_person rp ON rd.person_id = rp.person_id 
            WHERE ad.dia_semana = 'JUEVES'
        ");
        
        $jueves = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "Doctores disponibles para JUEVES (" . count($jueves) . "):\n";
        foreach ($jueves as $doctor) {
            echo "- Dr. {$doctor['first_name']} {$doctor['last_name']} (ID: {$doctor['doctor_id']}) - {$doctor['hora_inicio']} a {$doctor['hora_fin']}\n";
        }
        
    } else {
        $conexion->rollback();
        echo "\n❌ No se pudieron crear agendas\n";
    }
    
} catch (Exception $e) {
    if (isset($conexion)) {
        $conexion->rollback();
    }
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . "\n";
    echo "Línea: " . $e->getLine() . "\n";
}

echo "\n=== FIN DEL SCRIPT ===\n";
?>
