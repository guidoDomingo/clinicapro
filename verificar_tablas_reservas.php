<?php
require_once "model/conexion.php";

try {
    $pdo = Conexion::conectar();
    
    if (!$pdo) {
        echo "Error: No se pudo conectar a la base de datos\n";
        exit;
    }
    
    echo "=== VERIFICANDO TABLAS ===\n\n";
    
    // Verificar si existe la tabla reservas
    $stmt = $pdo->query("SELECT table_name FROM information_schema.tables WHERE table_name IN ('reservas', 'servicios_reservas')");
    $tablas = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "Tablas encontradas: " . implode(", ", $tablas) . "\n\n";
    
    // Verificar estructura de servicios_reservas
    $stmt = $pdo->query("SELECT column_name, data_type FROM information_schema.columns WHERE table_name = 'servicios_reservas' ORDER BY ordinal_position");
    
    echo "Columnas de servicios_reservas:\n";
    echo "==============================\n";
    
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo $row['column_name'] . " (" . $row['data_type'] . ")\n";
    }
    
    // Verificar si existe la vista o alias reservas
    $stmt = $pdo->query("SELECT viewname FROM pg_views WHERE viewname = 'reservas'");
    $vista = $stmt->fetch();
    
    if (!$vista) {
        echo "\n⚠️  No existe vista 'reservas'. Creando alias...\n";
        
        // Crear vista que apunte a servicios_reservas
        $sql = "CREATE OR REPLACE VIEW reservas AS SELECT * FROM servicios_reservas";
        $pdo->exec($sql);
        
        echo "✅ Vista 'reservas' creada exitosamente\n";
    } else {
        echo "\n✅ Vista 'reservas' ya existe\n";
    }
    
    // Verificar tabla reserva_archivos
    $stmt = $pdo->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_name = 'reserva_archivos'");
    $existe = $stmt->fetchColumn();
    
    if ($existe) {
        echo "✅ Tabla 'reserva_archivos' existe\n";
        
        // Mostrar estructura
        $stmt = $pdo->query("SELECT column_name FROM information_schema.columns WHERE table_name = 'reserva_archivos' ORDER BY ordinal_position");
        $columnas = $stmt->fetchAll(PDO::FETCH_COLUMN);
        echo "Columnas: " . implode(", ", $columnas) . "\n";
    } else {
        echo "❌ Tabla 'reserva_archivos' NO existe\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
