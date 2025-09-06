<?php
require_once 'model/conexion.php';

$db = Conexion::conectar();
$stmt = $db->query("SELECT column_name FROM information_schema.columns WHERE table_name = 'rh_person' AND table_schema = 'public'");
$columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
foreach($columns as $col) {
    echo $col . "\n";
}
?>