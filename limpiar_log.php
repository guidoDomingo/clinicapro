<?php
$logFile = 'logs/debug_informe_imagen.log';
if (file_exists($logFile)) {
    unlink($logFile);
    echo "Log limpiado";
} else {
    echo "No hay log para limpiar";
}
?>
