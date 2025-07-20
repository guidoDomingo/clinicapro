<?php
// Test directo para verificar datos de origen_reserva

try {
    // Conectar usando PDO directamente
    $pdo = new PDO("pgsql:host=localhost;dbname=clinica_db", "postgres", "admin");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $sql = "SELECT 
        sr.reserva_id,
        sr.origen_reserva,
        sr.fecha_reserva
    FROM servicios_reservas sr 
    ORDER BY sr.fecha_reserva DESC 
    LIMIT 5";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Datos de prueba origen_reserva:\n";
    echo "================================\n";
    foreach ($results as $row) {
        echo "ID: {$row['reserva_id']}, Origen: '{$row['origen_reserva']}', Fecha: {$row['fecha_reserva']}\n";
    }
    
    if (empty($results)) {
        echo "No hay reservas en la tabla\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
