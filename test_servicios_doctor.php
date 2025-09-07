<?php
/**
 * Script de prueba para verificar endpoints de servicios por doctor
 */

// Cambiar al directorio correcto para las rutas relativas
chdir(__DIR__);

require_once "controller/agendas.controller.php";

echo "<h1>Prueba de Endpoints - Servicios por Doctor</h1>";

// Test 1: Obtener médicos
echo "<h2>1. Probando obtener médicos:</h2>";
try {
    $medicos = ControllerAgendas::ctrObtenerMedicos();
    echo "<pre>";
    print_r($medicos);
    echo "</pre>";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

// Test 2: Obtener servicios
echo "<h2>2. Probando obtener servicios:</h2>";
try {
    $servicios = ControllerAgendas::ctrObtenerServicios();
    echo "<pre>";
    print_r($servicios);
    echo "</pre>";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

// Test 3: Obtener servicios por doctor
echo "<h2>3. Probando obtener servicios por doctor:</h2>";
try {
    $serviciosDoctor = ControllerAgendas::ctrObtenerServiciosDoctor();
    echo "<pre>";
    print_r($serviciosDoctor);
    echo "</pre>";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

// Test 4: Verificar estructura de base de datos
echo "<h2>4. Verificando estructura de base de datos:</h2>";
require_once "model/conexion.php";

try {
    $pdo = Conexion::conectar();
    
    // Verificar tabla rs_servicios_doctors
    echo "<h3>Tabla rs_servicios_doctors:</h3>";
    $stmt = $pdo->query("SELECT * FROM rs_servicios_doctors LIMIT 5");
    $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<pre>";
    print_r($datos);
    echo "</pre>";
    
    // Verificar tabla rs_servicios
    echo "<h3>Tabla rs_servicios:</h3>";
    $stmt = $pdo->query("SELECT serv_id, serv_name FROM rs_servicios WHERE is_active = true LIMIT 5");
    $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<pre>";
    print_r($datos);
    echo "</pre>";
    
    // Verificar médicos
    echo "<h3>Médicos disponibles:</h3>";
    $stmt = $pdo->query("SELECT rd.doctor_id, rp.first_name || ' ' || rp.last_name as nombre 
                        FROM rh_doctors rd 
                        INNER JOIN rh_person rp ON rd.person_id = rp.person_id 
                        WHERE rd.doctor_estado = 'ACTIVO' LIMIT 5");
    $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<pre>";
    print_r($datos);
    echo "</pre>";
    
} catch (Exception $e) {
    echo "Error de base de datos: " . $e->getMessage();
}
?>