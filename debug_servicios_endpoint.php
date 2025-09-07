<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "controller/servicios.controller.php";
require_once "model/servicios.model.php";
require_once "model/conexion.php";

echo "<h3>🔍 Debug: obtenerServiciosPorFechaMedico</h3>";

$fecha = "2025-09-08";
$doctorId = 18;

echo "Parámetros:<br>";
echo "- Fecha: {$fecha}<br>";
echo "- Doctor ID: {$doctorId}<br><br>";

try {
    echo "<h4>Llamando a ControladorServicios::ctrObtenerServiciosPorFechaMedico()...</h4>";
    $servicios = ControladorServicios::ctrObtenerServiciosPorFechaMedico($fecha, $doctorId);
    
    echo "Servicios obtenidos: " . count($servicios) . "<br><br>";
    
    echo "<h5>Estructura de datos:</h5>";
    echo "<pre>";
    print_r($servicios);
    echo "</pre>";
    
    if (!empty($servicios)) {
        echo "<h5>Formato para JavaScript:</h5>";
        echo "```javascript<br>";
        foreach ($servicios as $servicio) {
            if (isset($servicio['servicio_id']) && isset($servicio['servicio_nombre'])) {
                echo "- servicio_id: {$servicio['servicio_id']}, servicio_nombre: {$servicio['servicio_nombre']}<br>";
            } elseif (isset($servicio['serv_id']) && isset($servicio['serv_descripcion'])) {
                echo "- serv_id: {$servicio['serv_id']}, serv_descripcion: {$servicio['serv_descripcion']}<br>";
            } else {
                echo "- Estructura desconocida: " . json_encode($servicio) . "<br>";
            }
        }
        echo "```<br>";
        
        echo "<h5>¿Qué propiedad usar en JavaScript?</h5>";
        $primer_servicio = $servicios[0];
        if (isset($primer_servicio['servicio_id'])) {
            echo "✅ Usar 'servicio_id' y 'servicio_nombre'<br>";
        } elseif (isset($primer_servicio['serv_id'])) {
            echo "⚠️ Usar 'serv_id' y 'serv_descripcion'<br>";
        } else {
            echo "❌ Estructura de datos incorrecta<br>";
        }
    }
    
    // Simular exactamente lo que hace el AJAX
    echo "<h4>Simulación de respuesta AJAX:</h4>";
    $respuesta = [
        "status" => "success",
        "data" => $servicios
    ];
    echo "<pre>";
    echo json_encode($respuesta, JSON_PRETTY_PRINT);
    echo "</pre>";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}
?>