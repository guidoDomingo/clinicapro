<?php
/**
 * Test directo de la base de datos sin depender del modelo
 */

// Configuración directa de conexión
$host = 'localhost';
$port = '5432';
$dbname = 'clinica';
$user = 'postgres';
$password = 'admin';

echo "=== TEST DIRECTO DE BASE DE DATOS ===\n";
echo "Timestamp: " . date('Y-m-d H:i:s') . "\n";
echo "Objetivo: Verificar datos para JUEVES en fecha 2025-07-17\n\n";

try {
    // Verificar extensión
    if (!extension_loaded('pdo_pgsql')) {
        echo "❌ EXTENSIÓN PDO_PGSQL NO DISPONIBLE\n";
        echo "Extensiones cargadas:\n";
        foreach (get_loaded_extensions() as $ext) {
            if (strpos(strtolower($ext), 'pdo') !== false || strpos(strtolower($ext), 'pgsql') !== false) {
                echo "- $ext\n";
            }
        }
        exit(1);
    }
    echo "✅ Extensión PDO_PGSQL disponible\n\n";
    
    // Conectar
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
    $pdo = new PDO($dsn, $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ Conexión exitosa\n\n";
    
    // 1. Verificar datos básicos
    echo "1. VERIFICANDO DATOS BÁSICOS...\n";
    echo "-------------------------------\n";
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM agendas_detalle WHERE dia_semana = 'JUEVES'");
    $count = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Registros en agendas_detalle para JUEVES: {$count['total']}\n";
    
    if ($count['total'] > 0) {
        echo "✅ Hay datos para JUEVES\n";
        
        // Mostrar algunos registros
        $stmt = $pdo->query("SELECT * FROM agendas_detalle WHERE dia_semana = 'JUEVES' LIMIT 3");
        $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "Primeros 3 registros:\n";
        foreach ($registros as $i => $reg) {
            echo "--- Registro " . ($i + 1) . " ---\n";
            foreach ($reg as $campo => $valor) {
                echo "$campo: $valor\n";
            }
            echo "\n";
        }
    } else {
        echo "❌ No hay datos para JUEVES\n";
        // Verificar qué días SÍ hay
        $stmt = $pdo->query("SELECT dia_semana, COUNT(*) as total FROM agendas_detalle GROUP BY dia_semana ORDER BY dia_semana");
        $dias = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "Días disponibles:\n";
        foreach ($dias as $dia) {
            echo "- {$dia['dia_semana']}: {$dia['total']} registros\n";
        }
        exit(1);
    }
    
    // 2. Verificar JOINs
    echo "\n2. VERIFICANDO JOINS...\n";
    echo "-----------------------\n";
    
    // JOIN con agendas_cabecera
    $sql1 = "SELECT ad.*, ac.medico_id 
            FROM agendas_detalle ad 
            INNER JOIN agendas_cabecera ac ON ad.agenda_id = ac.agenda_id 
            WHERE ad.dia_semana = 'JUEVES'";
    $stmt1 = $pdo->query($sql1);
    $join1_count = $stmt1->rowCount();
    echo "Con JOIN agendas_cabecera: $join1_count registros\n";
    
    if ($join1_count == 0) {
        echo "❌ Problema en JOIN con agendas_cabecera\n";
        // Verificar qué agenda_ids están en detalle pero no en cabecera
        $stmt = $pdo->query("
            SELECT DISTINCT ad.agenda_id 
            FROM agendas_detalle ad 
            LEFT JOIN agendas_cabecera ac ON ad.agenda_id = ac.agenda_id 
            WHERE ad.dia_semana = 'JUEVES' AND ac.agenda_id IS NULL
        ");
        $huerfanos = $stmt->fetchAll(PDO::FETCH_COLUMN);
        if (!empty($huerfanos)) {
            echo "agenda_ids huérfanos: " . implode(', ', $huerfanos) . "\n";
        }
        exit(1);
    }
    
    // JOIN con rh_doctors
    $sql2 = "SELECT ad.*, ac.medico_id, rd.doctor_id 
            FROM agendas_detalle ad 
            INNER JOIN agendas_cabecera ac ON ad.agenda_id = ac.agenda_id 
            INNER JOIN rh_doctors rd ON rd.doctor_id = ac.medico_id 
            WHERE ad.dia_semana = 'JUEVES'";
    $stmt2 = $pdo->query($sql2);
    $join2_count = $stmt2->rowCount();
    echo "Con JOIN rh_doctors: $join2_count registros\n";
    
    if ($join2_count == 0) {
        echo "❌ Problema en JOIN con rh_doctors\n";
        // Verificar qué medico_ids no existen
        $stmt = $pdo->query("
            SELECT DISTINCT ac.medico_id 
            FROM agendas_detalle ad 
            INNER JOIN agendas_cabecera ac ON ad.agenda_id = ac.agenda_id 
            LEFT JOIN rh_doctors rd ON rd.doctor_id = ac.medico_id 
            WHERE ad.dia_semana = 'JUEVES' AND rd.doctor_id IS NULL
        ");
        $medicos_huerfanos = $stmt->fetchAll(PDO::FETCH_COLUMN);
        if (!empty($medicos_huerfanos)) {
            echo "medico_ids que no existen en rh_doctors: " . implode(', ', $medicos_huerfanos) . "\n";
        }
        exit(1);
    }
    
    // JOIN con rh_person
    $sql3 = "SELECT ad.*, rd.doctor_id, rp.first_name, rp.last_name 
            FROM agendas_detalle ad 
            INNER JOIN agendas_cabecera ac ON ad.agenda_id = ac.agenda_id 
            INNER JOIN rh_doctors rd ON rd.doctor_id = ac.medico_id 
            INNER JOIN rh_person rp ON rd.person_id = rp.person_id 
            WHERE ad.dia_semana = 'JUEVES'";
    $stmt3 = $pdo->query($sql3);
    $join3_count = $stmt3->rowCount();
    echo "Con JOIN rh_person: $join3_count registros\n";
    
    if ($join3_count == 0) {
        echo "❌ Problema en JOIN con rh_person\n";
        exit(1);
    }
    
    // 3. Consulta completa (igual al modelo)
    echo "\n3. CONSULTA COMPLETA...\n";
    echo "-----------------------\n";
    
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
            WHERE ad.dia_semana = 'JUEVES'
            ORDER BY rp.first_name, rp.last_name";
    
    $stmt = $pdo->query($sqlCompleta);
    $doctores = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Doctores encontrados: " . count($doctores) . "\n";
    
    if (count($doctores) > 0) {
        echo "🎉 ¡ÉXITO! Datos encontrados:\n";
        echo "==============================\n";
        foreach ($doctores as $i => $doctor) {
            echo "--- Doctor " . ($i + 1) . " ---\n";
            foreach ($doctor as $campo => $valor) {
                echo "$campo: $valor\n";
            }
            echo "\n";
        }
    } else {
        echo "❌ Consulta completa no devuelve resultados\n";
        echo "Esto indica un problema en el SELECT DISTINCT o ORDER BY\n";
    }
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Código: " . $e->getCode() . "\n";
}

echo "\n=== FIN DEL TEST ===\n";
?>
