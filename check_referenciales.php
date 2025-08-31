<?php
try {
    $pdo = new PDO('pgsql:host=localhost;port=5432;dbname=clinica', 'postgres', 'admin');
    
    echo "=== ESTRUCTURA DE TABLA referenciales ===\n";
    $stmt = $pdo->query('SELECT column_name, data_type FROM information_schema.columns WHERE table_name = \'referenciales\' ORDER BY ordinal_position');
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "- {$row['column_name']}: {$row['data_type']}\n";
    }
    
    echo "\n=== CONTENIDO DE referenciales ===\n";
    $stmt = $pdo->query('SELECT * FROM referenciales ORDER BY id');
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "ID: {$row['id']} - Nombre: {$row['nombre']} - Codigo: {$row['codigo']} - Descripcion: {$row['descripcion']}\n";
    }
    
    echo "\n=== REFERENCIAL_VALORES para ID=4 (equipos médicos) ===\n";
    $stmt = $pdo->query('SELECT * FROM referencial_valores WHERE referencial_id = 4 ORDER BY id');
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "- {$row['valor']} (activo: " . ($row['activo'] ? 'true' : 'false') . ")\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
