<?php
/**
 * Script de diagnóstico para verificar la conexión y doctores por fecha
 */

// Incluir configuración del entorno
require_once __DIR__ . '/config/environment_setup.php';
use Config\EnvironmentSetup;

echo "<h1>🔍 Diagnóstico de Doctores por Fecha</h1>";
echo "<hr>";

// Verificar extensión PDO PostgreSQL
echo "<h3>1. Verificación de Extensión PDO PostgreSQL</h3>";
if (extension_loaded('pdo_pgsql')) {
    echo "<p style='color: green;'>✅ Extensión pdo_pgsql está cargada</p>";
} else {
    echo "<p style='color: red;'>❌ Extensión pdo_pgsql NO está cargada</p>";
    echo "<p>Para habilitar PostgreSQL, visite: <a href='check_and_enable_pgsql.php'>check_and_enable_pgsql.php</a></p>";
    exit;
}

// Incluir archivos necesarios
require_once "model/conexion.php";

// Probar conexión directa
echo "<h3>2. Prueba de Conexión Directa</h3>";
try {
    // Obtener configuración de base de datos dinámicamente
    $dbConfig = EnvironmentSetup::getDatabaseConfig();
    $dsn = "pgsql:host={$dbConfig['host']};port={$dbConfig['port']};dbname={$dbConfig['dbname']}";
    $conexion = new PDO($dsn, $dbConfig['username'], $dbConfig['password']);
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p style='color: green;'>✅ Conexión directa exitosa</p>";
    
    // Probar consulta simple
    $stmt = $conexion->query("SELECT version()");
    $version = $stmt->fetchColumn();
    echo "<p><strong>Versión PostgreSQL:</strong> $version</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error de conexión directa: " . $e->getMessage() . "</p>";
    exit;
}

// Probar conexión a través de la clase
echo "<h3>3. Prueba de Conexión a través de Clase</h3>";
try {
    $conexionClase = Conexion::conectar();
    if ($conexionClase) {
        echo "<p style='color: green;'>✅ Conexión a través de clase exitosa</p>";
    } else {
        echo "<p style='color: red;'>❌ La clase Conexion devolvió null</p>";
        exit;
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error en clase Conexion: " . $e->getMessage() . "</p>";
    exit;
}

// Verificar estructura de tablas
echo "<h3>4. Verificación de Tablas</h3>";
$tablas = ['agendas_detalle', 'agendas_cabecera', 'rh_doctors', 'rh_person'];

foreach ($tablas as $tabla) {
    try {
        $stmt = $conexion->prepare("SELECT COUNT(*) FROM $tabla");
        $stmt->execute();
        $count = $stmt->fetchColumn();
        echo "<p>✅ Tabla <strong>$tabla</strong>: $count registros</p>";
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Tabla <strong>$tabla</strong>: " . $e->getMessage() . "</p>";
    }
}

// Probar consulta específica para doctores
echo "<h3>5. Consulta de Doctores para Hoy</h3>";
try {
    $fechaHoy = date('Y-m-d');
    $timestamp = strtotime($fechaHoy);
    $diaSemanaNum = (int)date('N', $timestamp);
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
    
    echo "<p><strong>Fecha:</strong> $fechaHoy</p>";
    echo "<p><strong>Día de la semana:</strong> $diaSemanaTexto</p>";
    
    // Consulta paso a paso
    echo "<h4>5.1 Registros en agendas_detalle para $diaSemanaTexto:</h4>";
    $stmt = $conexion->prepare("SELECT * FROM agendas_detalle WHERE dia_semana = :dia_semana");
    $stmt->bindParam(":dia_semana", $diaSemanaTexto, PDO::PARAM_STR);
    $stmt->execute();
    $detalles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<pre>" . print_r($detalles, true) . "</pre>";
    
    if (empty($detalles)) {
        echo "<p style='color: orange;'>⚠️ No hay registros en agendas_detalle para el día $diaSemanaTexto</p>";
        
        // Verificar qué días tienen registros
        echo "<h4>5.2 Días disponibles en agendas_detalle:</h4>";
        $stmt = $conexion->query("SELECT DISTINCT dia_semana, COUNT(*) as total FROM agendas_detalle GROUP BY dia_semana ORDER BY dia_semana");
        $diasDisponibles = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "<pre>" . print_r($diasDisponibles, true) . "</pre>";
    }
    
    // Consulta completa con JOINs
    echo "<h4>5.3 Consulta completa con JOINs:</h4>";
    $stmt = $conexion->prepare("
        SELECT DISTINCT
            ac.medico_id AS doctor_id,
            COALESCE(p.first_name, '') || ' ' || COALESCE(p.last_name, '') AS nombre_doctor,
            p.document_number,
            p.person_id,
            d.especialidad,
            ad.hora_inicio,
            ad.hora_fin,
            ad.intervalo_minutos
        FROM 
            agendas_detalle ad
        INNER JOIN 
            agendas_cabecera ac ON ad.agenda_id = ac.agenda_id
        LEFT JOIN
            rh_doctors d ON ac.medico_id = d.doctor_id
        LEFT JOIN
            rh_person p ON d.person_id = p.person_id
        WHERE 
            ad.dia_semana = :dia_semana
            AND ad.detalle_estado = true
            AND ac.agenda_estado = true
            AND (d.doctor_estado IS NULL OR d.doctor_estado = 'ACTIVO')
        ORDER BY 
            nombre_doctor ASC
    ");
    
    $stmt->bindParam(":dia_semana", $diaSemanaTexto, PDO::PARAM_STR);
    $stmt->execute();
    $doctores = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<p><strong>Doctores encontrados:</strong> " . count($doctores) . "</p>";
    echo "<pre>" . print_r($doctores, true) . "</pre>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error en consulta de doctores: " . $e->getMessage() . "</p>";
}

echo "<h3>✅ Diagnóstico completado</h3>";
echo "<p><a href='index.php'>← Volver al índice</a></p>";
?>
