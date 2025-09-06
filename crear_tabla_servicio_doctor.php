<?php
require_once 'model/conexion.php';

try {
    $db = Conexion::conectar();
    echo "🔧 Creando tabla de relación servicio-doctor...\n\n";
    
    // Crear tabla de relación servicio-doctor
    $sql = "
    CREATE TABLE IF NOT EXISTS rs_servicios_doctors (
        id SERIAL PRIMARY KEY,
        servicio_id INTEGER NOT NULL,
        doctor_id INTEGER NOT NULL,
        business_id INTEGER,
        is_active BOOLEAN DEFAULT true,
        created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
        
        -- Claves foráneas
        CONSTRAINT fk_rs_servicios_doctors_servicio 
            FOREIGN KEY (servicio_id) REFERENCES rs_servicios(serv_id) ON DELETE CASCADE,
        CONSTRAINT fk_rs_servicios_doctors_doctor 
            FOREIGN KEY (doctor_id) REFERENCES rh_doctors(doctor_id) ON DELETE CASCADE,
        
        -- Evitar duplicados
        CONSTRAINT uk_servicio_doctor UNIQUE (servicio_id, doctor_id)
    );
    ";
    
    $db->exec($sql);
    echo "✅ Tabla 'rs_servicios_doctors' creada exitosamente\n\n";
    
    // Crear índices para mejorar rendimiento
    echo "📊 Creando índices...\n";
    $db->exec("CREATE INDEX IF NOT EXISTS idx_rs_servicios_doctors_servicio ON rs_servicios_doctors(servicio_id);");
    $db->exec("CREATE INDEX IF NOT EXISTS idx_rs_servicios_doctors_doctor ON rs_servicios_doctors(doctor_id);");
    $db->exec("CREATE INDEX IF NOT EXISTS idx_rs_servicios_doctors_active ON rs_servicios_doctors(is_active);");
    echo "✅ Índices creados exitosamente\n\n";
    
    // Insertar algunos datos de ejemplo
    echo "📝 Insertando datos de ejemplo...\n";
    
    // Obtener algunos servicios
    $stmt = $db->query("SELECT serv_id FROM rs_servicios WHERE is_active = true LIMIT 3");
    $servicios = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Obtener algunos doctores
    $stmt = $db->query("SELECT doctor_id FROM rh_doctors LIMIT 3");
    $doctores = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (!empty($servicios) && !empty($doctores)) {
        $stmt = $db->prepare("
            INSERT INTO rs_servicios_doctors (servicio_id, doctor_id, is_active) 
            VALUES (?, ?, true)
            ON CONFLICT (servicio_id, doctor_id) DO NOTHING
        ");
        
        $insertados = 0;
        foreach ($servicios as $servicio_id) {
            foreach ($doctores as $doctor_id) {
                $stmt->execute([$servicio_id, $doctor_id]);
                $insertados++;
            }
        }
        
        echo "✅ Se insertaron $insertados relaciones de ejemplo\n\n";
    } else {
        echo "⚠️ No se encontraron servicios o doctores para crear datos de ejemplo\n\n";
    }
    
    // Verificar los datos insertados
    echo "📋 Verificando datos insertados:\n";
    $stmt = $db->query("
        SELECT 
            rsd.id,
            rs.serv_codigo,
            rs.serv_descripcion,
            rh.doctor_id,
            p.first_name || ' ' || p.last_name as doctor_nombre
        FROM rs_servicios_doctors rsd
        JOIN rs_servicios rs ON rsd.servicio_id = rs.serv_id
        JOIN rh_doctors rh ON rsd.doctor_id = rh.doctor_id
        LEFT JOIN rh_person p ON rh.person_id = p.person_id
        WHERE rsd.is_active = true
        LIMIT 10
    ");
    
    $relaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($relaciones as $rel) {
        $doctor_nombre = $rel['doctor_nombre'] ?? "Doctor ID: " . $rel['doctor_id'];
        echo "   - {$rel['serv_codigo']}: {$rel['serv_descripcion']} → {$doctor_nombre}\n";
    }
    
    echo "\n🎉 ¡Tabla de relación servicio-doctor creada exitosamente!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "📍 Línea: " . $e->getLine() . "\n";
    echo "📄 Archivo: " . $e->getFile() . "\n";
}
?>