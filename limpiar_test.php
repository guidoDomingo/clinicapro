<?php
// Script para limpiar archivos de debugging/test
$archivos_test = [
    'test_busqueda_alejandro.php',
    'test_busqueda_completa.php'
];

echo "<h1>🧹 Limpieza de Archivos de Test</h1>";

foreach ($archivos_test as $archivo) {
    if (file_exists($archivo)) {
        if (unlink($archivo)) {
            echo "<p>✅ Eliminado: $archivo</p>";
        } else {
            echo "<p>❌ Error al eliminar: $archivo</p>";
        }
    } else {
        echo "<p>ℹ️ No existe: $archivo</p>";
    }
}

echo "<p><strong>Limpieza completada!</strong></p>";
echo "<p><a href='view/modules/consultas.php?tipo=general'>← Volver a consultas</a></p>";
?>
