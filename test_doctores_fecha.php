<?php
/**
 * Script de prueba para verificar la funcionalidad de obtener doctores por fecha
 */

// Incluir archivos necesarios en el orden correcto
require_once "model/conexion.php";
require_once "model/servicios.model.php";
require_once "controller/servicios.controller.php";

echo "<h1>🩺 Prueba de Doctores por Fecha</h1>";
echo "<hr>";

// Verificar conexión primero
try {
    $conexion = Conexion::conectar();
    if ($conexion) {
        echo "<p style='color: green;'>✅ Conexión a la base de datos exitosa</p>";
    } else {
        echo "<p style='color: red;'>❌ Error de conexión a la base de datos</p>";
        exit;
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error de conexión: " . $e->getMessage() . "</p>";
    exit;
}

// Fechas de prueba
$fechasPrueba = [
    date('Y-m-d'), // Hoy
    date('Y-m-d', strtotime('+1 day')), // Mañana
    date('Y-m-d', strtotime('+2 days')), // Pasado mañana
    '2024-01-15', // Fecha específica (lunes)
    '2024-01-16', // Fecha específica (martes)
];

foreach ($fechasPrueba as $fecha) {
    echo "<h3>📅 Fecha: $fecha</h3>";
    
    // Determinar día de la semana
    $timestamp = strtotime($fecha);
    $diaSemana = date('N', $timestamp); // 1 = lunes, 7 = domingo
    $diasSemana = [1 => 'Lunes', 2 => 'Martes', 3 => 'Miércoles', 4 => 'Jueves', 5 => 'Viernes', 6 => 'Sábado', 7 => 'Domingo'];
    echo "<p><strong>Día de la semana:</strong> " . $diasSemana[$diaSemana] . "</p>";
    
    try {
        // Probar el método del modelo directamente
        echo "<h4>🔧 Prueba del Modelo:</h4>";
        $doctoresModelo = ModelServicios::mdlObtenerDoctoresPorFecha($fecha);
        echo "<pre>";
        print_r($doctoresModelo);
        echo "</pre>";
        
        // Probar el controlador
        echo "<h4>🎮 Prueba del Controlador:</h4>";
        $doctoresControlador = ControladorServicios::ctrObtenerDoctoresPorFecha($fecha);
        echo "<pre>";
        print_r($doctoresControlador);
        echo "</pre>";
        
        echo "<p><strong>Total doctores encontrados:</strong> " . count($doctoresControlador) . "</p>";
        
    } catch (Exception $e) {
        echo "<p style='color: red;'><strong>Error:</strong> " . $e->getMessage() . "</p>";
    }
    
    echo "<hr>";
}

// Prueba adicional: Verificar estructura de tablas
echo "<h3>🗄️ Verificación de Estructura de Tablas</h3>";

try {
    $conexion = Conexion::conectar();
    
    // Verificar tabla agendas_detalle
    echo "<h4>Tabla agendas_detalle:</h4>";
    $stmt = $conexion->prepare("SELECT column_name, data_type FROM information_schema.columns WHERE table_name = 'agendas_detalle' ORDER BY ordinal_position");
    $stmt->execute();
    $columnas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<pre>";
    print_r($columnas);
    echo "</pre>";
    
    // Verificar algunos registros de ejemplo
    echo "<h4>Registros de ejemplo en agendas_detalle:</h4>";
    $stmt = $conexion->prepare("SELECT * FROM agendas_detalle LIMIT 5");
    $stmt->execute();
    $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<pre>";
    print_r($registros);
    echo "</pre>";
    
    // Verificar tabla agendas_cabecera
    echo "<h4>Registros de ejemplo en agendas_cabecera:</h4>";
    $stmt = $conexion->prepare("SELECT * FROM agendas_cabecera LIMIT 5");
    $stmt->execute();
    $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<pre>";
    print_r($registros);
    echo "</pre>";
    
    // Verificar tabla rh_doctors
    echo "<h4>Registros de ejemplo en rh_doctors:</h4>";
    $stmt = $conexion->prepare("SELECT * FROM rh_doctors LIMIT 5");
    $stmt->execute();
    $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<pre>";
    print_r($registros);
    echo "</pre>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'><strong>Error al verificar tablas:</strong> " . $e->getMessage() . "</p>";
}

echo "<h3>✅ Prueba completada</h3>";
echo "<p><a href='index.php'>← Volver al índice</a></p>";
?>
