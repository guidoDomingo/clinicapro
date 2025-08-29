<?php
require_once 'model/conexion.php';

try {
    $db = Conexion::conectar();
    
    echo "=== ACTUALIZANDO ESTRUCTURA DE TABLA CONSULTAS ===\n\n";
    
    // Cambiar motivoscomunes de varchar(255) a text
    echo "1. Cambiando motivoscomunes de varchar(255) a text...\n";
    $stmt = $db->prepare("ALTER TABLE consultas ALTER COLUMN motivoscomunes TYPE text");
    $stmt->execute();
    echo "   ✅ motivoscomunes actualizado exitosamente\n\n";
    
    // Cambiar txtmotivo de varchar(255) a text
    echo "2. Cambiando txtmotivo de varchar(255) a text...\n";
    $stmt = $db->prepare("ALTER TABLE consultas ALTER COLUMN txtmotivo TYPE text");
    $stmt->execute();
    echo "   ✅ txtmotivo actualizado exitosamente\n\n";
    
    // Verificar los cambios
    echo "3. Verificando cambios...\n";
    $stmt = $db->prepare("
        SELECT column_name, data_type, character_maximum_length
        FROM information_schema.columns 
        WHERE table_name = 'consultas' 
        AND column_name IN ('motivoscomunes', 'txtmotivo')
        ORDER BY column_name
    ");
    $stmt->execute();
    $columns = $stmt->fetchAll();
    
    foreach($columns as $col) {
        $len = $col['character_maximum_length'] ? '(' . $col['character_maximum_length'] . ')' : '';
        echo "   - " . $col['column_name'] . ": " . $col['data_type'] . $len . "\n";
    }
    
    echo "\n✅ ACTUALIZACIÓN COMPLETADA EXITOSAMENTE\n";
    echo "Los campos motivoscomunes y txtmotivo ahora pueden almacenar texto ilimitado.\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . "\n";
    echo "Línea: " . $e->getLine() . "\n";
}
?>