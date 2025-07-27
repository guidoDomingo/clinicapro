<?php
echo "<h1>Debug Log - Informe Imagen</h1>";

$logFile = 'logs/debug_informe_imagen.log';

if (file_exists($logFile)) {
    echo "<h2>Contenido del log:</h2>";
    echo "<pre>";
    echo htmlspecialchars(file_get_contents($logFile));
    echo "</pre>";
    
    echo "<hr>";
    echo "<p><a href='#' onclick='location.reload();'>Refrescar</a></p>";
    echo "<p><a href='#' onclick='if(confirm(\"¿Limpiar log?\")) { 
        fetch(\"limpiar_log.php\").then(() => location.reload()); 
    }'>Limpiar Log</a></p>";
} else {
    echo "<p>No existe el archivo de log aún. Haz una prueba del formulario primero.</p>";
}

echo "<hr>";
echo "<p><a href='debug_tabla_informe.php'>Ver tabla informe_imagen</a></p>";
echo "<p><a href='test_guardado_directo.php'>Test guardado directo</a></p>";
?>
