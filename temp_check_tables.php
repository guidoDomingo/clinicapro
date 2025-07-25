<?php
require_once 'model/conexion.php';

try {
    $pdo = Conexion::conectar();
    if ($pdo) {
        echo "=== Estructura de tabla motivos_comunes ===" . PHP_EOL;
        $stmt = $pdo->query("SELECT column_name, data_type, is_nullable, column_default 
                            FROM information_schema.columns 
                            WHERE table_name = 'motivos_comunes' 
                            ORDER BY ordinal_position");
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            printf("%-20s %-15s %-10s %s" . PHP_EOL, 
                   $row['column_name'], 
                   $row['data_type'], 
                   $row['is_nullable'], 
                   $row['column_default'] ?? 'NULL');
        }
        
        echo PHP_EOL . "=== Datos existentes (primeros 5) ===" . PHP_EOL;
        $stmt2 = $pdo->query("SELECT * FROM motivos_comunes LIMIT 5");
        while ($row = $stmt2->fetch(PDO::FETCH_ASSOC)) {
            print_r($row);
        }
        
        echo PHP_EOL . "=== Total de registros ===" . PHP_EOL;
        $stmt3 = $pdo->query("SELECT COUNT(*) as total FROM motivos_comunes");
        $total = $stmt3->fetch(PDO::FETCH_ASSOC);
        echo "Total de motivos comunes: " . $total['total'] . PHP_EOL;
        
    } else {
        echo "No se pudo conectar a la base de datos" . PHP_EOL;
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . PHP_EOL;
    echo "Archivo: " . $e->getFile() . PHP_EOL;
    echo "Línea: " . $e->getLine() . PHP_EOL;
}
?>
