<?php
require_once 'modules/consultas/core/DatabaseMapper.php';

$mapper = new DatabaseMapper();

echo "📋 ESTRUCTURA DE TABLA consulta_estudios:\n";
echo "========================================\n";
$estudios = $mapper->getTableStructure('consulta_estudios');
foreach ($estudios as $row) {
    echo $row['column_name'] . " (" . $row['data_type'] . ")\n";
}

echo "\n📋 ESTRUCTURA DE TABLA consulta_informe_imagen:\n";
echo "===========================================\n";
$informe = $mapper->getTableStructure('consulta_informe_imagen');
foreach ($informe as $row) {
    echo $row['column_name'] . " (" . $row['data_type'] . ")\n";
}
?>