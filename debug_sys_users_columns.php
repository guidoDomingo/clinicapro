<?php
require_once "model/conexion.php";

try {
    $pdo = Conexion::conectar();
    
    if (!$pdo) {
        echo "Error: No se pudo conectar a la base de datos\n";
        exit;
    }
    
    // Obtener información de las columnas de sys_users
    $stmt = $pdo->query("SELECT column_name, data_type FROM information_schema.columns WHERE table_name = 'sys_users' ORDER BY ordinal_position");
    
    echo "Columnas de la tabla sys_users:\n";
    echo "============================\n";
    
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo $row['column_name'] . " (" . $row['data_type'] . ")\n";
    }
    
    echo "\n\nEjemplo de registro con user_id = 74:\n";
    echo "=====================================\n";
    
    $stmt = $pdo->prepare("SELECT * FROM sys_users WHERE user_id = 74");
    $stmt->execute();
    $data = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($data) {
        foreach($data as $column => $value) {
            echo $column . ": " . ($value ?? 'NULL') . "\n";
        }
    } else {
        echo "No se encontró usuario con ID 74\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
