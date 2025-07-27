<?php
require_once 'model/conexion.php';

try {
    $db = Conexion::conectar();
    
    // Verificar si existe la tabla
    $stmt = $db->query("SELECT table_name FROM information_schema.tables WHERE table_name = 'consulta_informe_imagen' AND table_schema = 'public'");
    $tabla = $stmt->fetch();
    
    if ($tabla) {
        echo "✅ Tabla consulta_informe_imagen existe\n";
        
        // Verificar estructura
        $stmt = $db->query("SELECT column_name, data_type FROM information_schema.columns WHERE table_name = 'consulta_informe_imagen' ORDER BY ordinal_position");
        $columnas = $stmt->fetchAll();
        echo "\nColumnas existentes:\n";
        foreach ($columnas as $col) {
            echo "- {$col['column_name']} ({$col['data_type']})\n";
        }
        
        // Verificar datos existentes
        $stmt = $db->query("SELECT COUNT(*) as total FROM consulta_informe_imagen");
        $count = $stmt->fetch();
        echo "\nRegistros en la tabla: {$count['total']}\n";
        
    } else {
        echo "❌ Tabla consulta_informe_imagen NO existe\n";
        echo "Necesita ejecutar el script: crear_tabla_consulta_informe_imagen.sql\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
