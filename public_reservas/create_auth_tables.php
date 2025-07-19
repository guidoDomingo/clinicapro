<?php
/**
 * Script para crear las tablas de autenticación
 */

require_once __DIR__ . "/../model/conexion.php";

try {
    $db = Conexion::conectar();
    
    // Leer el contenido del archivo SQL
    $sql = file_get_contents(__DIR__ . '/auth_tables.sql');
    
    // Dividir las consultas por punto y coma
    $queries = array_filter(array_map('trim', explode(';', $sql)));
    
    foreach ($queries as $query) {
        if (!empty($query)) {
            echo "Ejecutando: " . substr($query, 0, 50) . "...\n";
            $db->exec($query);
            echo "✅ Ejecutado exitosamente\n\n";
        }
    }
    
    echo "🎉 Todas las tablas de autenticación han sido creadas exitosamente!\n";
    
} catch (Exception $e) {
    echo "❌ Error al crear las tablas: " . $e->getMessage() . "\n";
}
?>
