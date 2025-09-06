<?php
require_once 'model/conexion.php';

$db = Conexion::conectar();
$stmt = $db->query("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public' AND table_name LIKE '%person%'");
$tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
foreach($tables as $table) {
    echo $table . "\n";
}
?>