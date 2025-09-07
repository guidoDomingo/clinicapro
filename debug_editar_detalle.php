<?php
/**
 * Debug para verificar la funcionalidad de editar detalle
 */

require_once "controller/agendas.controller.php";
require_once "model/agendas.model.php";

echo "<h2>Debug Editar Detalle</h2>";

// Simulamos una consulta para obtener un detalle específico
$modelo = new ModelAgendas();

// Primero verificamos que detalles existen
$detalles = $modelo->mdlObtenerDetallesAgenda(1); // Agenda ID 1
echo "<h3>Detalles disponibles:</h3>";
echo "<pre>";
print_r($detalles);
echo "</pre>";

// Si hay detalles, probamos obtener uno específico
if (!empty($detalles)) {
    $primerDetalle = $detalles[0];
    echo "<h3>Obteniendo detalle específico ID: " . $primerDetalle['detalle_id'] . "</h3>";
    
    $detalleEspecifico = $modelo->mdlObtenerDetalleAgenda($primerDetalle['detalle_id']);
    echo "<pre>";
    print_r($detalleEspecifico);
    echo "</pre>";
}

// También verificamos los servicios disponibles
echo "<h3>Servicios disponibles:</h3>";
$servicios = $modelo->mdlObtenerServicios();
echo "<pre>";
print_r($servicios);
echo "</pre>";
?>