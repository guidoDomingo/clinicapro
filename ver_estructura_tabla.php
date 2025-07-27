<?php
require_once "model/conexion.php";

try {
    $db = Conexion::conectar();
    $stmt = $db->prepare("SELECT column_name, data_type FROM information_schema.columns WHERE table_name = 'consulta_informe_imagen' ORDER BY ordinal_position");
    $stmt->execute();
    $cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "=== ESTRUCTURA TABLA consulta_informe_imagen ===\n";
    foreach($cols as $col) {
        echo $col['column_name'] . ' (' . $col['data_type'] . ')\n';
    }
} catch(Exception $e) {
    echo 'Error: ' . $e->getMessage();
}
?>
