<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include necessary files
require_once 'config/conexion.php';
require_once 'model/servicios.model.php';
require_once 'controller/servicios.controller.php';

// Test the controller function directly
echo "<h2>Test de filtrado por fecha específica</h2>";

echo "<h3>Prueba 1: Formato YYYY-MM-DD</h3>";
$result1 = ControladorServicios::ctrObtenerDiasDisponibles(13, 9, '2025-09-28');
echo "Resultado: " . json_encode($result1) . "<br>";

echo "<h3>Prueba 2: Formato dd/mm/yyyy</h3>";
$result2 = ControladorServicios::ctrObtenerDiasDisponibles(13, 9, '28/09/2025');
echo "Resultado: " . json_encode($result2) . "<br>";

echo "<h3>Prueba 3: Sin fecha (todos los días)</h3>";
$result3 = ControladorServicios::ctrObtenerDiasDisponibles(13, 9);
echo "Resultado: " . count($result3) . " días encontrados<br>";

echo "<h3>Logs recientes</h3>";
if (file_exists('/var/log/clinica/servicios.log')) {
    $logs = file_get_contents('/var/log/clinica/servicios.log');
    $lines = explode("\n", $logs);
    $recent = array_slice($lines, -10);
    foreach ($recent as $line) {
        if (strpos($line, 'FechaEspecifica') !== false || strpos($line, 'Filtrado por fecha') !== false) {
            echo htmlspecialchars($line) . "<br>";
        }
    }
}
?>