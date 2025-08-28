<?php
// Usar el endpoint principal pero modificar su contenido
echo "Copiando contenido del archivo debug al principal...\n";

$debugFile = 'modules/consultas/api/livwire-crud-debug.php';
$mainFile = 'modules/consultas/api/livwire-crud.php';

if (file_exists($debugFile)) {
    $content = file_get_contents($debugFile);
    file_put_contents($mainFile, $content);
    echo "✅ Archivo principal actualizado con datos reales de la base de datos\n";
} else {
    echo "❌ Archivo debug no encontrado\n";
}
?>