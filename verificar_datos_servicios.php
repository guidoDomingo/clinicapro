<?php
// Verificar datos en las tablas relacionadas con servicios

$baseDir = dirname(__FILE__);
require_once $baseDir . "/model/conexion.php";

echo "<h2>Verificación de Datos - Servicios por Doctor</h2>";

try {
    $conexion = Conexion::conectar();
    
    // Verificar tabla rs_servicios
    echo "<h3>1. Tabla 'rs_servicios':</h3>";
    $stmt = $conexion->prepare("SELECT serv_id, serv_name, serv_description, serv_active FROM rs_servicios WHERE serv_active = true ORDER BY serv_name");
    $stmt->execute();
    $servicios = $stmt->fetchAll();
    
    if (count($servicios) > 0) {
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr><th>ID</th><th>Nombre</th><th>Descripción</th><th>Activo</th></tr>";
        foreach ($servicios as $servicio) {
            echo "<tr>";
            echo "<td>" . $servicio['serv_id'] . "</td>";
            echo "<td>" . $servicio['serv_name'] . "</td>";
            echo "<td>" . ($servicio['serv_description'] ?? 'N/A') . "</td>";
            echo "<td>" . ($servicio['serv_active'] ? 'Sí' : 'No') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: red;'>❌ No hay servicios registrados</p>";
    }
    
    // Verificar tabla rs_servicios_doctors
    echo "<h3>2. Tabla 'rs_servicios_doctors':</h3>";
    $stmt = $conexion->prepare("
        SELECT rsd.*, s.serv_name as servicio_nombre, 
               CONCAT(p.nombre, ' ', p.apellido) as doctor_nombre
        FROM rs_servicios_doctors rsd
        LEFT JOIN rs_servicios s ON rsd.servicio_id = s.serv_id
        LEFT JOIN rh_doctors d ON rsd.doctor_id = d.doctor_id
        LEFT JOIN persons p ON d.person_id = p.id
        WHERE rsd.is_active = true
        ORDER BY rsd.id
    ");
    $stmt->execute();
    $serviciosDoctor = $stmt->fetchAll();
    
    if (count($serviciosDoctor) > 0) {
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr><th>ID</th><th>Servicio</th><th>Doctor</th><th>Activo</th></tr>";
        foreach ($serviciosDoctor as $sd) {
            echo "<tr>";
            echo "<td>" . $sd['id'] . "</td>";
            echo "<td>" . $sd['servicio_nombre'] . "</td>";
            echo "<td>" . $sd['doctor_nombre'] . "</td>";
            echo "<td>" . ($sd['is_active'] ? 'Sí' : 'No') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: orange;'>⚠️ No hay asociaciones servicio-doctor registradas</p>";
    }
    
    // Verificar doctores disponibles
    echo "<h3>3. Doctores disponibles:</h3>";
    $stmt = $conexion->prepare("
        SELECT d.doctor_id, p.id as person_id, 
               CONCAT(p.nombre, ' ', p.apellido) as nombre_completo
        FROM rh_doctors d
        INNER JOIN persons p ON d.person_id = p.id
        WHERE d.doc_active = true
        ORDER BY p.nombre, p.apellido
    ");
    $stmt->execute();
    $doctores = $stmt->fetchAll();
    
    if (count($doctores) > 0) {
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr><th>Doctor ID</th><th>Person ID</th><th>Nombre Completo</th></tr>";
        foreach ($doctores as $doctor) {
            echo "<tr>";
            echo "<td>" . $doctor['doctor_id'] . "</td>";
            echo "<td>" . $doctor['person_id'] . "</td>";
            echo "<td>" . $doctor['nombre_completo'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: red;'>❌ No hay doctores activos</p>";
    }
    
    // Verificar estructura de tabla rs_servicios
    echo "<h3>4. Estructura de tabla 'rs_servicios':</h3>";
    $stmt = $conexion->prepare("
        SELECT column_name, data_type, is_nullable, column_default
        FROM information_schema.columns 
        WHERE table_name = 'rs_servicios' 
        ORDER BY ordinal_position
    ");
    $stmt->execute();
    $columnas = $stmt->fetchAll();
    
    if (count($columnas) > 0) {
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr><th>Columna</th><th>Tipo</th><th>Nulo</th><th>Por Defecto</th></tr>";
        foreach ($columnas as $columna) {
            echo "<tr>";
            echo "<td>" . $columna['column_name'] . "</td>";
            echo "<td>" . $columna['data_type'] . "</td>";
            echo "<td>" . $columna['is_nullable'] . "</td>";
            echo "<td>" . ($columna['column_default'] ?? 'N/A') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: red;'>❌ No se pudo obtener la estructura de la tabla rs_servicios</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>