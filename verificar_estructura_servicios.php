<?php
require_once 'model/conexion.php';

try {
    $db = Conexion::conectar();
    echo "📋 Verificando tablas relacionadas con servicios y doctores...\n\n";
    
    // Primero verificar cuál es el schema actual
    $stmt = $db->query("SELECT current_schema()");
    $schema_actual = $stmt->fetchColumn();
    echo "🏛️ Schema actual: $schema_actual\n\n";
    
    // Buscar tablas que contengan 'servicio' en el nombre
    $stmt = $db->query("SELECT table_name FROM information_schema.tables WHERE table_schema = '$schema_actual' AND table_name LIKE '%servicio%'");
    $tablas_servicio = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "🔧 Tablas con 'servicio':\n";
    foreach ($tablas_servicio as $tabla) {
        echo "   - $tabla\n";
    }
    
    // Buscar tablas que contengan 'doctor' en el nombre
    $stmt = $db->query("SELECT table_name FROM information_schema.tables WHERE table_schema = '$schema_actual' AND table_name LIKE '%doctor%'");
    $tablas_doctor = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "\n👨‍⚕️ Tablas con 'doctor':\n";
    foreach ($tablas_doctor as $tabla) {
        echo "   - $tabla\n";
    }
    
    // Buscar si existe tabla intermedia
    $stmt = $db->query("SELECT table_name FROM information_schema.tables WHERE table_schema = '$schema_actual' AND (table_name LIKE '%doctor_servicio%' OR table_name LIKE '%servicio_doctor%')");
    $tablas_intermedia = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "\n🔗 Tablas intermedias servicio-doctor:\n";
    if (empty($tablas_intermedia)) {
        echo "   ❌ No se encontró tabla intermedia\n";
    } else {
        foreach ($tablas_intermedia as $tabla) {
            echo "   - $tabla\n";
        }
    }
    
    // Verificar estructura de la tabla rs_servicios
    echo "\n📊 Estructura de la tabla 'rs_servicios':\n";
    $stmt = $db->query("SELECT column_name, data_type, is_nullable FROM information_schema.columns WHERE table_name = 'rs_servicios' AND table_schema = '$schema_actual'");
    $columnas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($columnas as $col) {
        echo "   - {$col['column_name']} ({$col['data_type']}) - Nullable: {$col['is_nullable']}\n";
    }
    
    // Verificar estructura de la tabla rh_doctors
    echo "\n👨‍⚕️ Estructura de la tabla 'rh_doctors':\n";
    $stmt = $db->query("SELECT column_name, data_type, is_nullable FROM information_schema.columns WHERE table_name = 'rh_doctors' AND table_schema = '$schema_actual'");
    $columnas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($columnas as $col) {
        echo "   - {$col['column_name']} ({$col['data_type']}) - Nullable: {$col['is_nullable']}\n";
    }
    
    // Verificar si en rs_servicios hay alguna relación directa con doctor
    echo "\n🔍 Verificando datos en tabla rs_servicios (primeros 5 registros):\n";
    $stmt = $db->query("SELECT * FROM rs_servicios LIMIT 5");
    $servicios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (!empty($servicios)) {
        $first = $servicios[0];
        echo "   Columnas: " . implode(", ", array_keys($first)) . "\n";
        foreach ($servicios as $servicio) {
            echo "   - ID: {$servicio['serv_id']}, Código: {$servicio['serv_codigo']}, Descripción: {$servicio['serv_descripcion']}\n";
        }
    } else {
        echo "   ❌ No hay datos en la tabla rs_servicios\n";
    }
    
    // Verificar si en rh_doctors hay alguna relación con servicios
    echo "\n🔍 Verificando datos en tabla rh_doctors (primeros 5 registros):\n";
    $stmt = $db->query("SELECT * FROM rh_doctors LIMIT 5");
    $doctors = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (!empty($doctors)) {
        $first = $doctors[0];
        echo "   Columnas: " . implode(", ", array_keys($first)) . "\n";
        foreach ($doctors as $doctor) {
            echo "   - Doctor ID: {$doctor['doctor_id']}, Person ID: {$doctor['person_id']}\n";
        }
    } else {
        echo "   ❌ No hay datos en la tabla rh_doctors\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>