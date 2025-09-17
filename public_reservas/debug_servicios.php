<?php
/**
 * Archivo de depuración para verificar los servicios con monto
 */

require_once "controller/ReservasPublicController.php";
require_once "model/ReservasPublicModel.php";

echo "<h2>Debug de Servicios con Monto</h2>";

try {
    $servicios = ReservasPublicModel::mdlObtenerServicios();
    
    echo "<h3>Servicios encontrados: " . count($servicios) . "</h3>";
    
    if (count($servicios) > 0) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Descripción</th><th>Monto</th><th>Código</th></tr>";
        
        foreach ($servicios as $servicio) {
            echo "<tr>";
            echo "<td>" . ($servicio['serv_id'] ?? 'N/A') . "</td>";
            echo "<td>" . ($servicio['serv_descripcion'] ?? 'N/A') . "</td>";
            echo "<td>" . ($servicio['serv_monto'] ?? 'N/A') . "</td>";
            echo "<td>" . ($servicio['serv_codigo'] ?? 'N/A') . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
        
        echo "<h3>JSON de ejemplo:</h3>";
        echo "<pre>" . json_encode($servicios, JSON_PRETTY_PRINT) . "</pre>";
    } else {
        echo "<p style='color: red;'>No se encontraron servicios</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>