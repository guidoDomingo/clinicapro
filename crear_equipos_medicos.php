<?php
/**
 * Crear tabla de equipos médicos para formulario de estudios
 * Usando la misma estructura que los referenciales de anteojos
 */

try {
    $pdo = new PDO('pgsql:host=localhost;port=5432;dbname=clinica_db', 'postgres', '123456');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "🏥 Creando tabla de equipos médicos...\n";
    
    // Crear tabla de equipos médicos
    $createTable = "
        CREATE TABLE IF NOT EXISTS equipos_medicos (
            id SERIAL PRIMARY KEY,
            codigo VARCHAR(50) UNIQUE NOT NULL,
            nombre VARCHAR(100) NOT NULL,
            descripcion TEXT,
            tipo VARCHAR(50) DEFAULT 'general',
            activo BOOLEAN DEFAULT true,
            orden INTEGER DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ";
    
    $pdo->exec($createTable);
    echo "✅ Tabla equipos_medicos creada.\n";
    
    // Insertar datos de equipos médicos (basados en los hardcodeados)
    $equipos = [
        ['cirrus_700', 'Cirrus 700', 'Tomografía de coherencia óptica Carl Zeiss', 'oct', 1],
        ['cirrus_500c', 'Cirrus 500c', 'Tomografía de coherencia óptica Carl Zeiss modelo 500c', 'oct', 2],
        ['oct_triton', 'OCT Triton', 'Tomografía de coherencia óptica Triton', 'oct', 3],
        ['humphrey', 'Humphrey', 'Campo visual Humphrey', 'campo_visual', 4],
        ['topcon', 'Topcon', 'Equipo oftalmológico Topcon', 'general', 5],
        ['pentacam', 'Pentacam', 'Cámara Scheimpflug rotacional', 'topografia', 6],
        ['autorefractor', 'Autorefractor', 'Refractómetro automático', 'refraccion', 7],
        ['keratometro', 'Keratómetro', 'Medición de curvatura corneal', 'queratometria', 8],
        ['tonometro', 'Tonómetro', 'Medición de presión intraocular', 'tonometria', 9],
        ['otro', 'Otro equipo', 'Equipo médico no listado', 'general', 99]
    ];
    
    $insertStmt = $pdo->prepare("
        INSERT INTO equipos_medicos (codigo, nombre, descripcion, tipo, orden) 
        VALUES (?, ?, ?, ?, ?)
        ON CONFLICT (codigo) DO UPDATE SET
            nombre = EXCLUDED.nombre,
            descripcion = EXCLUDED.descripcion,
            tipo = EXCLUDED.tipo,
            orden = EXCLUDED.orden,
            updated_at = CURRENT_TIMESTAMP
    ");
    
    foreach ($equipos as $equipo) {
        $insertStmt->execute($equipo);
    }
    
    echo "✅ " . count($equipos) . " equipos médicos insertados.\n";
    
    // Verificar datos insertados
    $stmt = $pdo->query("SELECT * FROM equipos_medicos ORDER BY orden ASC");
    $equiposInsertados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "\n📋 Equipos médicos en la base de datos:\n";
    foreach ($equiposInsertados as $equipo) {
        echo sprintf("- %s: %s (%s)\n", 
            $equipo['codigo'], 
            $equipo['nombre'], 
            $equipo['tipo']
        );
    }
    
    echo "\n🎯 Sistema de equipos médicos configurado correctamente!\n";
    
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>