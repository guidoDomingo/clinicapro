<?php
echo "Testing API endpoints...\n";

// Test motivos comunes
$url = 'http://localhost/clinica/modules/consultas/api/consultas-api.php?action=get_motivos_comunes&tipo_formulario=general';
$result = file_get_contents($url);
echo "Motivos Comunes (general): " . $result . "\n\n";

// Test motivos comunes for anteojos
$url = 'http://localhost/clinica/modules/consultas/api/consultas-api.php?action=get_motivos_comunes&tipo_formulario=anteojos';
$result = file_get_contents($url);
echo "Motivos Comunes (anteojos): " . $result . "\n\n";

// Test preformatos
$url = 'http://localhost/clinica/modules/consultas/api/consultas-api.php?action=get_preformatos_consulta&tipo_formulario=general';
$result = file_get_contents($url);
echo "Preformatos (general): " . $result . "\n\n";

// Test preformatos for estudios
$url = 'http://localhost/clinica/modules/consultas/api/consultas-api.php?action=get_preformatos_consulta&tipo_formulario=estudios';
$result = file_get_contents($url);
echo "Preformatos (estudios): " . $result . "\n\n";
?>
