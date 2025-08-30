<?php
try {
    $pdo = new PDO('pgsql:host=localhost;dbname=clinica', 'postgres', 'admin');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Eliminar vínculo con consulta
    $stmt = $pdo->prepare('DELETE FROM archivos_consulta WHERE id_consulta = 179');
    $stmt->execute();
    echo 'Vínculos eliminados: ' . $stmt->rowCount() . PHP_EOL;
    
    // Eliminar archivo de BD  
    $stmt = $pdo->prepare('DELETE FROM archivos WHERE id_archivo = 77');
    $stmt->execute();
    echo 'Archivos eliminados: ' . $stmt->rowCount() . PHP_EOL;
    
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage() . PHP_EOL;
}
?>