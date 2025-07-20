<?php
require_once "model/conexion.php";

try {
    $pdo = Conexion::conectar();
    
    if (!$pdo) {
        echo "Error: No se pudo conectar a la base de datos\n";
        exit;
    }
    
    // SQL para agregar el campo origen_reserva
    $sql = "
    ALTER TABLE servicios_reservas 
    ADD COLUMN IF NOT EXISTS origen_reserva VARCHAR(20) DEFAULT 'SISTEMA';
    
    -- Crear índice para el nuevo campo
    CREATE INDEX IF NOT EXISTS idx_reservas_origen ON servicios_reservas(origen_reserva);
    
    -- Agregar comentario
    COMMENT ON COLUMN servicios_reservas.origen_reserva IS 'Origen de la reserva: SISTEMA (desde el sistema principal) u ONLINE (desde reservas públicas)';
    ";
    
    // Ejecutar el SQL
    $pdo->exec($sql);
    
    echo "✅ Campo 'origen_reserva' agregado exitosamente\n";
    echo "✅ Índice creado exitosamente\n";
    
    // Verificar que el campo se agregó
    $stmt = $pdo->query("SELECT column_name, data_type, column_default FROM information_schema.columns WHERE table_name = 'servicios_reservas' AND column_name = 'origen_reserva'");
    
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($result) {
        echo "\n📋 Campo agregado:\n";
        echo "========================\n";
        echo "Nombre: " . $result['column_name'] . "\n";
        echo "Tipo: " . $result['data_type'] . "\n";
        echo "Default: " . $result['column_default'] . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
