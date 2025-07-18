<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Debug Consulta Doctores</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .success { color: green; }
        .error { color: red; }
        .warning { color: orange; }
        .info { color: blue; }
        pre { background: #f5f5f5; padding: 10px; border-radius: 5px; }
        .section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
    </style>
</head>
<body>
    <h1>Debug: Consulta de Doctores por Fecha</h1>
    
    <?php
    /**
     * Test web del problema de doctores
     */
    
    require_once "model/conexion.php";
    require_once "model/servicios.model.php";
    
    echo '<div class="section">';
    echo '<h2>1. Información Básica</h2>';
    echo '<p><strong>Fecha objetivo:</strong> 2025-07-17 (JUEVES)</p>';
    echo '<p><strong>Timestamp:</strong> ' . date('Y-m-d H:i:s') . '</p>';
    echo '</div>';
    
    try {
        // Verificar conexión
        echo '<div class="section">';
        echo '<h2>2. Verificación de Conexión</h2>';
        $conexion = Conexion::conectar();
        if (!$conexion) {
            echo '<p class="error">❌ Error de conexión a la base de datos</p>';
            exit;
        }
        echo '<p class="success">✅ Conexión exitosa</p>';
        echo '</div>';
        
        // Test directo de la consulta
        echo '<div class="section">';
        echo '<h2>3. Test Directo de Consulta</h2>';
        
        $fecha = '2025-07-17';
        $fechaObj = DateTime::createFromFormat('Y-m-d', $fecha);
        $diaSemanaNum = (int)$fechaObj->format('N');
        $diasSemanaTexto = [1 => 'LUNES', 2 => 'MARTES', 3 => 'MIERCOLES', 4 => 'JUEVES', 5 => 'VIERNES', 6 => 'SABADO', 7 => 'DOMINGO'];
        $diaSemanaTexto = $diasSemanaTexto[$diaSemanaNum];
        
        echo "<p><strong>Día calculado:</strong> $diaSemanaTexto</p>";
        
        // Consulta paso a paso
        echo '<h3>3.1. Datos básicos en agendas_detalle</h3>';
        $stmt = $conexion->prepare("SELECT COUNT(*) as total FROM agendas_detalle WHERE dia_semana = :dia");
        $stmt->bindParam(":dia", $diaSemanaTexto, PDO::PARAM_STR);
        $stmt->execute();
        $count = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "<p>Registros para $diaSemanaTexto: {$count['total']}</p>";
        
        if ($count['total'] > 0) {
            echo '<p class="success">✅ Hay datos básicos</p>';
            
            // Mostrar algunos registros
            $stmt = $conexion->prepare("SELECT * FROM agendas_detalle WHERE dia_semana = :dia LIMIT 2");
            $stmt->bindParam(":dia", $diaSemanaTexto, PDO::PARAM_STR);
            $stmt->execute();
            $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo '<h4>Primeros registros:</h4>';
            echo '<pre>' . print_r($registros, true) . '</pre>';
            
            // Test de JOINs
            echo '<h3>3.2. Test de JOINs</h3>';
            
            // JOIN 1: agendas_cabecera
            $sql1 = "SELECT COUNT(*) as total FROM agendas_detalle ad 
                    INNER JOIN agendas_cabecera ac ON ad.agenda_id = ac.agenda_id 
                    WHERE ad.dia_semana = :dia";
            $stmt1 = $conexion->prepare($sql1);
            $stmt1->bindParam(":dia", $diaSemanaTexto, PDO::PARAM_STR);
            $stmt1->execute();
            $join1 = $stmt1->fetch(PDO::FETCH_ASSOC);
            echo "<p>Con JOIN agendas_cabecera: {$join1['total']} registros</p>";
            
            if ($join1['total'] == 0) {
                echo '<p class="error">❌ Problema en JOIN con agendas_cabecera</p>';
            } else {
                // JOIN 2: rh_doctors
                $sql2 = "SELECT COUNT(*) as total FROM agendas_detalle ad 
                        INNER JOIN agendas_cabecera ac ON ad.agenda_id = ac.agenda_id 
                        INNER JOIN rh_doctors rd ON rd.doctor_id = ac.medico_id 
                        WHERE ad.dia_semana = :dia";
                $stmt2 = $conexion->prepare($sql2);
                $stmt2->bindParam(":dia", $diaSemanaTexto, PDO::PARAM_STR);
                $stmt2->execute();
                $join2 = $stmt2->fetch(PDO::FETCH_ASSOC);
                echo "<p>Con JOIN rh_doctors: {$join2['total']} registros</p>";
                
                if ($join2['total'] == 0) {
                    echo '<p class="error">❌ Problema en JOIN con rh_doctors</p>';
                    
                    // Verificar medico_ids problemáticos
                    $sqlCheck = "SELECT DISTINCT ac.medico_id 
                                FROM agendas_detalle ad 
                                INNER JOIN agendas_cabecera ac ON ad.agenda_id = ac.agenda_id 
                                LEFT JOIN rh_doctors rd ON rd.doctor_id = ac.medico_id 
                                WHERE ad.dia_semana = :dia AND rd.doctor_id IS NULL";
                    $stmtCheck = $conexion->prepare($sqlCheck);
                    $stmtCheck->bindParam(":dia", $diaSemanaTexto, PDO::PARAM_STR);
                    $stmtCheck->execute();
                    $medicos_huerfanos = $stmtCheck->fetchAll(PDO::FETCH_COLUMN);
                    if (!empty($medicos_huerfanos)) {
                        echo '<p class="warning">⚠️ medico_ids que no existen en rh_doctors: ' . implode(', ', $medicos_huerfanos) . '</p>';
                    }
                } else {
                    // JOIN 3: rh_person
                    $sql3 = "SELECT COUNT(*) as total FROM agendas_detalle ad 
                            INNER JOIN agendas_cabecera ac ON ad.agenda_id = ac.agenda_id 
                            INNER JOIN rh_doctors rd ON rd.doctor_id = ac.medico_id 
                            INNER JOIN rh_person rp ON rd.person_id = rp.person_id 
                            WHERE ad.dia_semana = :dia";
                    $stmt3 = $conexion->prepare($sql3);
                    $stmt3->bindParam(":dia", $diaSemanaTexto, PDO::PARAM_STR);
                    $stmt3->execute();
                    $join3 = $stmt3->fetch(PDO::FETCH_ASSOC);
                    echo "<p>Con JOIN rh_person: {$join3['total']} registros</p>";
                    
                    if ($join3['total'] > 0) {
                        echo '<p class="success">✅ Todos los JOINs funcionan</p>';
                        
                        // Consulta completa
                        echo '<h3>3.3. Consulta Completa (igual al modelo)</h3>';
                        $sqlCompleta = "SELECT DISTINCT
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
                                WHERE ad.dia_semana = :dia
                                ORDER BY rp.first_name, rp.last_name";
                        
                        $stmtCompleta = $conexion->prepare($sqlCompleta);
                        $stmtCompleta->bindParam(":dia", $diaSemanaTexto, PDO::PARAM_STR);
                        $stmtCompleta->execute();
                        $doctores = $stmtCompleta->fetchAll(PDO::FETCH_ASSOC);
                        
                        echo "<p><strong>Doctores encontrados:</strong> " . count($doctores) . "</p>";
                        
                        if (count($doctores) > 0) {
                            echo '<p class="success">🎉 ¡ÉXITO! Datos encontrados con consulta directa</p>';
                            echo '<h4>Doctores:</h4>';
                            echo '<pre>' . print_r($doctores, true) . '</pre>';
                        } else {
                            echo '<p class="error">❌ Consulta completa no devuelve resultados</p>';
                        }
                    } else {
                        echo '<p class="error">❌ Problema en JOIN con rh_person</p>';
                    }
                }
            }
        } else {
            echo '<p class="error">❌ No hay datos básicos para JUEVES</p>';
            
            // Verificar qué días SÍ hay
            $stmt = $conexion->query("SELECT dia_semana, COUNT(*) as total FROM agendas_detalle GROUP BY dia_semana ORDER BY dia_semana");
            $dias = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo '<h4>Días disponibles:</h4>';
            echo '<pre>' . print_r($dias, true) . '</pre>';
        }
        echo '</div>';
        
        // Test del método del modelo
        echo '<div class="section">';
        echo '<h2>4. Test del Método del Modelo</h2>';
        $resultadoModelo = ModelServicios::mdlObtenerDoctoresPorFecha($fecha);
        echo "<p><strong>Resultado del método del modelo:</strong> " . count($resultadoModelo) . " doctores</p>";
        
        if (!empty($resultadoModelo)) {
            echo '<p class="success">✅ El método del modelo SÍ funciona</p>';
            echo '<pre>' . print_r($resultadoModelo, true) . '</pre>';
        } else {
            echo '<p class="error">❌ El método del modelo NO funciona</p>';
        }
        echo '</div>';
        
    } catch (Exception $e) {
        echo '<div class="section">';
        echo '<h2>Error</h2>';
        echo '<p class="error">❌ ERROR: ' . $e->getMessage() . '</p>';
        echo '<p>Archivo: ' . $e->getFile() . '</p>';
        echo '<p>Línea: ' . $e->getLine() . '</p>';
        echo '</div>';
    }
    ?>
    
    <div class="section">
        <h2>5. Acciones de Debug</h2>
        <p><a href="test_ajax_debug.php" target="_blank">🔍 Test AJAX Debug</a></p>
        <p><a href="test_modal_exacto.html" target="_blank">🎯 Test Modal Exacto</a></p>
        <p><a href="logs/public_reservas.log" target="_blank">📋 Ver Logs</a></p>
    </div>
</body>
</html>
