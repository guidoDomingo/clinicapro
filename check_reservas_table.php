<?php
require_once 'model/conexion.php';

try {
    $pdo = Conexion::conectar();
    
    if (!$pdo) {
        throw new Exception("No se pudo conectar a la base de datos");
    }
    
    // Verificar estructura de la tabla
    echo "=== ESTRUCTURA DE servicios_reservas ===\n";
    $stmt = $pdo->prepare("SELECT column_name, data_type, is_nullable 
                          FROM information_schema.columns 
                          WHERE table_name = 'servicios_reservas' 
                          ORDER BY ordinal_position");
    $stmt->execute();
    $columnas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($columnas as $col) {
        echo "- {$col['column_name']} ({$col['data_type']}) - Nullable: {$col['is_nullable']}\n";
    }
    
    echo "\n=== DATOS DE EJEMPLO ===\n";
    $stmt = $pdo->prepare('SELECT * FROM servicios_reservas LIMIT 5');
    $stmt->execute();
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (!empty($resultados)) {
        foreach ($resultados as $index => $fila) {
            echo "Registro " . ($index + 1) . ":\n";
            foreach ($fila as $columna => $valor) {
                echo "  {$columna}: {$valor}\n";
            }
            echo "\n";
        }
    } else {
        echo "No hay datos en la tabla\n";
    }
    
    echo "\n=== CONTEO TOTAL DE RESERVAS ===\n";
    $stmt = $pdo->prepare('SELECT COUNT(*) as total FROM servicios_reservas');
    $stmt->execute();
    $total = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Total de reservas: {$total['total']}\n";
    
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
}
?>
