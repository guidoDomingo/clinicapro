<?php
/**
 * Test edit functionality directly
 */

require_once "controller/agendas.controller.php";
require_once "model/agendas.model.php";

echo "<h2>Test Edit Functionality</h2>";

// Test getting a specific detail - using ID 33 from our test data
$detalleId = 33;
echo "<h3>Getting detail ID: $detalleId</h3>";

$modelo = new ModelAgendas();
$detalle = $modelo->mdlObtenerDetalleAgenda($detalleId);

echo "<h4>Raw Detail Data:</h4>";
echo "<pre>";
print_r($detalle);
echo "</pre>";

// Simulate AJAX call
echo "<h3>Simulating AJAX Response:</h3>";
if ($detalle) {
    $response = ["status" => "success", "data" => $detalle];
} else {
    $response = ["status" => "error", "message" => "Detail not found"];
}

echo "<pre>";
echo json_encode($response, JSON_PRETTY_PRINT);
echo "</pre>";

// Also test turnos and salas
echo "<h3>Testing related data:</h3>";

$turnos = $modelo->mdlObtenerTurnos();
echo "<h4>Turnos:</h4>";
echo "<pre>";
print_r($turnos);
echo "</pre>";

$salas = $modelo->mdlObtenerSalas();
echo "<h4>Salas:</h4>";
echo "<pre>";
print_r($salas);
echo "</pre>";

$servicios = $modelo->mdlObtenerServicios();
echo "<h4>Servicios:</h4>";
echo "<pre>";
print_r($servicios);
echo "</pre>";
?>