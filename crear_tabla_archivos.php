<?php
require_once "model/conexion.php";

try {
    $pdo = Conexion::conectar();
    
    if (!$pdo) {
        echo "Error: No se pudo conectar a la base de datos\n";
        exit;
    }
    
    // SQL para crear la tabla
    $sql = "
    CREATE TABLE IF NOT EXISTS reserva_archivos (
        archivo_id SERIAL PRIMARY KEY,
        reserva_id INTEGER NOT NULL,
        codigo_seguimiento VARCHAR(50) NOT NULL,
        nombre_original VARCHAR(255) NOT NULL,
        nombre_archivo VARCHAR(255) NOT NULL,
        ruta_archivo VARCHAR(500) NOT NULL,
        tipo_archivo VARCHAR(10) NOT NULL,
        tamaño_archivo INTEGER NOT NULL,
        fecha_subida TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        subido_por INTEGER,
        estado VARCHAR(20) DEFAULT 'ACTIVO'
    );
    
    CREATE INDEX IF NOT EXISTS idx_reserva_archivos_reserva_id ON reserva_archivos(reserva_id);
    CREATE INDEX IF NOT EXISTS idx_reserva_archivos_codigo ON reserva_archivos(codigo_seguimiento);
    CREATE INDEX IF NOT EXISTS idx_reserva_archivos_estado ON reserva_archivos(estado);
    ";
    
    // Ejecutar el SQL
    $pdo->exec($sql);
    
    echo "✅ Tabla 'reserva_archivos' creada exitosamente\n";
    echo "✅ Índices creados exitosamente\n";
    
    // Verificar que la tabla se creó
    $stmt = $pdo->query("SELECT column_name, data_type FROM information_schema.columns WHERE table_name = 'reserva_archivos' ORDER BY ordinal_position");
    
    echo "\n📋 Estructura de la tabla:\n";
    echo "========================\n";
    
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo $row['column_name'] . " (" . $row['data_type'] . ")\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
