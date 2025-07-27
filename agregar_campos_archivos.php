<?php
require_once "model/conexion.php";

try {
    $db = Conexion::conectar();
    
    echo "=== AGREGANDO CAMPOS DE ARCHIVOS A TABLA consulta_informe_imagen ===\n\n";
    
    // Verificar si las columnas ya existen
    $checkColumns = "
    SELECT column_name 
    FROM information_schema.columns 
    WHERE table_name = 'consulta_informe_imagen' 
    AND column_name IN ('archivos_od', 'archivos_oi')
    ";
    
    $stmt = $db->prepare($checkColumns);
    $stmt->execute();
    $existingColumns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "Columnas existentes relacionadas con archivos: " . implode(', ', $existingColumns) . "\n\n";
    
    // Agregar columnas si no existen
    if (!in_array('archivos_od', $existingColumns)) {
        echo "Agregando columna archivos_od...\n";
        $sql1 = "ALTER TABLE consulta_informe_imagen ADD COLUMN archivos_od JSON";
        $db->exec($sql1);
        echo "✅ Columna archivos_od agregada\n";
    } else {
        echo "⚠️  Columna archivos_od ya existe\n";
    }
    
    if (!in_array('archivos_oi', $existingColumns)) {
        echo "Agregando columna archivos_oi...\n";
        $sql2 = "ALTER TABLE consulta_informe_imagen ADD COLUMN archivos_oi JSON";
        $db->exec($sql2);
        echo "✅ Columna archivos_oi agregada\n";
    } else {
        echo "⚠️  Columna archivos_oi ya existe\n";
    }
    
    // Verificar estructura final
    echo "\n=== ESTRUCTURA FINAL ===\n";
    $stmt = $db->prepare("SELECT column_name, data_type FROM information_schema.columns WHERE table_name = 'consulta_informe_imagen' ORDER BY ordinal_position");
    $stmt->execute();
    $cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach($cols as $col) {
        echo $col['column_name'] . ' (' . $col['data_type'] . ')\n';
    }
    
    echo "\n✅ Proceso completado exitosamente\n";
    
} catch(Exception $e) {
    echo '❌ Error: ' . $e->getMessage() . "\n";
}
?>
