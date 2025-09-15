<?php
require_once 'model/conexion.php';

echo "=== ESTRUCTURA DE rs_servicios_doctors ===\n";
try {
    $pdo = Conexion::conectar();
    
    // Verificar estructura de la tabla
    $stmt = $pdo->query("SELECT column_name, data_type FROM information_schema.columns WHERE table_name = 'rs_servicios_doctors' ORDER BY ordinal_position");
    
    echo "Columnas encontradas:\n";
    while($row = $stmt->fetch()) {
        echo "- " . $row['column_name'] . " (" . $row['data_type'] . ")\n";
    }
    
    // Verificar algunos datos de ejemplo
    echo "\n=== DATOS DE EJEMPLO ===\n";
    $stmt = $pdo->query("SELECT * FROM rs_servicios_doctors LIMIT 5");
    $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($datos) > 0) {
        echo "Se encontraron " . count($datos) . " registros de ejemplo:\n";
        foreach ($datos as $dato) {
            print_r($dato);
        }
    } else {
        echo "No se encontraron datos en la tabla.\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>