<?php
/**
 * Test ultra-específico del método del modelo
 */

require_once "model/conexion.php";
require_once "model/servicios.model.php";

header('Content-Type: text/plain; charset=utf-8');

echo "=== DEBUG ULTRA-ESPECÍFICO DEL MODELO ===\n";
echo "Timestamp: " . date('Y-m-d H:i:s') . "\n";
echo "Fecha objetivo: 2025-07-17 (JUEVES)\n\n";

try {
    // 1. Verificar conexión
    echo "1. VERIFICANDO CONEXIÓN...\n";
    $conexion = Conexion::conectar();
    if (!$conexion) {
        throw new Exception("Error de conexión");
    }
    echo "✅ Conexión exitosa\n\n";
    
    // 2. Simular exactamente lo que hace el método del modelo
    echo "2. SIMULANDO EL MÉTODO DEL MODELO PASO A PASO...\n";
    echo "------------------------------------------------------\n";
    
    $fecha = '2025-07-17';
    echo "Fecha de entrada: $fecha\n";
    
    // Verificar formato de fecha
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
        throw new Exception("Formato de fecha incorrecto");
    }
    echo "✅ Formato de fecha correcto\n";
    
    // Crear objeto DateTime
    $fechaObj = DateTime::createFromFormat('Y-m-d', $fecha);
    if (!$fechaObj || $fechaObj->format('Y-m-d') !== $fecha) {
        throw new Exception("Fecha inválida");
    }
    echo "✅ DateTime creado correctamente\n";
    
    // Determinar día de la semana
    $diaSemanaNum = (int)$fechaObj->format('N'); // ISO-8601
    $diasSemanaTexto = [
        1 => 'LUNES', 
        2 => 'MARTES', 
        3 => 'MIERCOLES', 
        4 => 'JUEVES', 
        5 => 'VIERNES', 
        6 => 'SABADO', 
        7 => 'DOMINGO'
    ];
    $diaSemanaTexto = $diasSemanaTexto[$diaSemanaNum];
    
    echo "Día de la semana (número): $diaSemanaNum\n";
    echo "Día de la semana (texto): $diaSemanaTexto\n";
    echo "✅ Mapeo de día correcto\n\n";
    
    // 3. Ejecutar consulta paso a paso
    echo "3. EJECUTANDO CONSULTA...\n";
    echo "-------------------------\n";
    
    $sql = "SELECT DISTINCT
                rd.doctor_id AS doctor_id,
                COALESCE(rp.first_name, '') || ' ' || COALESCE(rp.last_name, '') AS nombre_doctor,
                rp.document_number,
                rp.person_id,
                rd.especialidad,
                ad.hora_inicio,
                ad.hora_fin,
                ad.intervalo_minutos,
                ad.detalle_id,
                ad.agenda_id
            FROM agendas_detalle ad 
            INNER JOIN agendas_cabecera ac ON ad.agenda_id = ac.agenda_id 
            INNER JOIN rh_doctors rd ON rd.doctor_id = ac.medico_id 
            INNER JOIN rh_person rp ON rd.person_id = rp.person_id 
            WHERE ad.dia_semana = :dia_semana
            ORDER BY rp.first_name, rp.last_name";
    
    echo "SQL a ejecutar:\n";
    echo $sql . "\n\n";
    echo "Parámetro dia_semana: '$diaSemanaTexto'\n\n";
    
    $stmt = $conexion->prepare($sql);
    $stmt->bindParam(":dia_semana", $diaSemanaTexto, PDO::PARAM_STR);
    
    echo "✅ Consulta preparada\n";
    echo "Ejecutando...\n";
    
    $stmt->execute();
    echo "✅ Consulta ejecutada\n";
    
    $doctores = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "Resultados obtenidos: " . count($doctores) . " registros\n\n";
    
    if (!empty($doctores)) {
        echo "🎉 ¡DATOS ENCONTRADOS!\n";
        echo "======================\n";
        foreach ($doctores as $i => $doctor) {
            echo "--- Doctor #" . ($i + 1) . " ---\n";
            foreach ($doctor as $campo => $valor) {
                echo "$campo: $valor\n";
            }
            echo "\n";
        }
    } else {
        echo "❌ NO SE ENCONTRARON DATOS\n";
        echo "Vamos a investigar por qué...\n\n";
        
        // Verificar si hay datos para JUEVES sin JOINs
        echo "4. VERIFICANDO DATOS SIN JOINS...\n";
        echo "-----------------------------------\n";
        $stmtSimple = $conexion->prepare("SELECT * FROM agendas_detalle WHERE dia_semana = :dia_semana");
        $stmtSimple->bindParam(":dia_semana", $diaSemanaTexto, PDO::PARAM_STR);
        $stmtSimple->execute();
        $datosSimples = $stmtSimple->fetchAll(PDO::FETCH_ASSOC);
        
        echo "Registros en agendas_detalle para '$diaSemanaTexto': " . count($datosSimples) . "\n";
        
        if (!empty($datosSimples)) {
            echo "✅ Hay datos en agendas_detalle\n";
            echo "Primer registro:\n";
            print_r($datosSimples[0]);
            
            // Verificar JOINs uno por uno
            echo "\n5. VERIFICANDO JOINS UNO POR UNO...\n";
            echo "------------------------------------\n";
            
            // JOIN con agendas_cabecera
            $sqlJoin1 = "SELECT ad.*, ac.agenda_id as ac_agenda_id, ac.medico_id 
                        FROM agendas_detalle ad 
                        INNER JOIN agendas_cabecera ac ON ad.agenda_id = ac.agenda_id 
                        WHERE ad.dia_semana = :dia_semana";
            $stmtJoin1 = $conexion->prepare($sqlJoin1);
            $stmtJoin1->bindParam(":dia_semana", $diaSemanaTexto, PDO::PARAM_STR);
            $stmtJoin1->execute();
            $join1 = $stmtJoin1->fetchAll(PDO::FETCH_ASSOC);
            echo "Con JOIN agendas_cabecera: " . count($join1) . " registros\n";
            
            if (empty($join1)) {
                echo "❌ El problema está en el JOIN con agendas_cabecera\n";
            } else {
                // JOIN con rh_doctors
                $sqlJoin2 = "SELECT ad.*, ac.medico_id, rd.doctor_id, rd.person_id as rd_person_id
                            FROM agendas_detalle ad 
                            INNER JOIN agendas_cabecera ac ON ad.agenda_id = ac.agenda_id 
                            INNER JOIN rh_doctors rd ON rd.doctor_id = ac.medico_id 
                            WHERE ad.dia_semana = :dia_semana";
                $stmtJoin2 = $conexion->prepare($sqlJoin2);
                $stmtJoin2->bindParam(":dia_semana", $diaSemanaTexto, PDO::PARAM_STR);
                $stmtJoin2->execute();
                $join2 = $stmtJoin2->fetchAll(PDO::FETCH_ASSOC);
                echo "Con JOIN rh_doctors: " . count($join2) . " registros\n";
                
                if (empty($join2)) {
                    echo "❌ El problema está en el JOIN con rh_doctors\n";
                    echo "Verificando datos de medico_id...\n";
                    if (!empty($join1)) {
                        echo "medico_id en agendas_cabecera: " . $join1[0]['medico_id'] . "\n";
                        
                        $checkDoctor = $conexion->prepare("SELECT * FROM rh_doctors WHERE doctor_id = :doctor_id");
                        $checkDoctor->bindParam(":doctor_id", $join1[0]['medico_id'], PDO::PARAM_INT);
                        $checkDoctor->execute();
                        $doctorExiste = $checkDoctor->fetchAll(PDO::FETCH_ASSOC);
                        echo "Doctor con ID {$join1[0]['medico_id']} existe: " . (count($doctorExiste) > 0 ? "SÍ" : "NO") . "\n";
                        if (!empty($doctorExiste)) {
                            print_r($doctorExiste[0]);
                        }
                    }
                } else {
                    // JOIN con rh_person
                    $sqlJoin3 = "SELECT ad.*, rd.doctor_id, rd.person_id, rp.first_name, rp.last_name
                                FROM agendas_detalle ad 
                                INNER JOIN agendas_cabecera ac ON ad.agenda_id = ac.agenda_id 
                                INNER JOIN rh_doctors rd ON rd.doctor_id = ac.medico_id 
                                INNER JOIN rh_person rp ON rd.person_id = rp.person_id 
                                WHERE ad.dia_semana = :dia_semana";
                    $stmtJoin3 = $conexion->prepare($sqlJoin3);
                    $stmtJoin3->bindParam(":dia_semana", $diaSemanaTexto, PDO::PARAM_STR);
                    $stmtJoin3->execute();
                    $join3 = $stmtJoin3->fetchAll(PDO::FETCH_ASSOC);
                    echo "Con JOIN rh_person: " . count($join3) . " registros\n";
                    
                    if (empty($join3)) {
                        echo "❌ El problema está en el JOIN con rh_person\n";
                    } else {
                        echo "✅ Todos los JOINs funcionan. El problema debe ser en el DISTINCT o ORDER BY\n";
                        print_r($join3[0]);
                    }
                }
            }
        } else {
            echo "❌ No hay datos en agendas_detalle para '$diaSemanaTexto'\n";
        }
    }
    
    // 6. Llamar al método real del modelo
    echo "\n6. LLAMANDO AL MÉTODO REAL DEL MODELO...\n";
    echo "----------------------------------------\n";
    
    $resultadoModelo = ModelServicios::mdlObtenerDoctoresPorFecha($fecha);
    echo "Resultado del método real: " . count($resultadoModelo) . " doctores\n";
    
    if (!empty($resultadoModelo)) {
        echo "Primer doctor del modelo:\n";
        print_r($resultadoModelo[0]);
    }
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . "\n";
    echo "Línea: " . $e->getLine() . "\n";
}

echo "\n=== FIN DEL DEBUG ===\n";
?>
