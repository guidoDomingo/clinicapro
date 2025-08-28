<?php
// Script para cambiar el endpoint a la versión con datos reales
$file = 'view/modules/consultas-new.php';
$content = file_get_contents($file);

// Reemplazar el endpoint
$content = str_replace('livwire-crud.php', 'livwire-crud-debug.php', $content);

// Guardar el archivo
file_put_contents($file, $content);

echo "✅ Endpoint cambiado exitosamente a livwire-crud-debug.php\n";
echo "✅ Ahora el sistema utilizará datos reales de la base de datos PostgreSQL\n";
?>