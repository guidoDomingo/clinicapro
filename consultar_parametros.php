<?php
require_once 'model/conexion.php';

echo "📋 Consultando estructura de sistema_parametros\n";
echo "==============================================\n";

try {
    $pdo = Conexion::conectar();
    
    // Consultar estructura de la tabla
    $stmt = $pdo->query("SELECT column_name, data_type, is_nullable 
                         FROM information_schema.columns 
                         WHERE table_name = 'sistema_parametros' 
                         ORDER BY ordinal_position");
    $estructura = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Estructura de la tabla:\n";
    foreach ($estructura as $columna) {
        echo "- {$columna['column_name']}: {$columna['data_type']} (Nullable: {$columna['is_nullable']})\n";
    }
    
    echo "\n";
    
    // Consultar datos de muestra
    $stmt = $pdo->query('SELECT * FROM sistema_parametros LIMIT 10');
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Datos de muestra:\n";
    if ($data) {
        foreach ($data as $index => $row) {
            echo "Registro " . ($index + 1) . ":\n";
            foreach ($row as $key => $value) {
                echo "  $key: $value\n";
            }
            echo "\n";
        }
        echo "Total registros en muestra: " . count($data) . "\n";
    } else {
        echo "No hay datos en la tabla\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>