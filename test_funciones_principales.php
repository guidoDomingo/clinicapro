<?php
/**
 * Test rápido de las funciones principales
 */

require_once "model/conexion.php";
require_once "model/servicios.model.php";
require_once "controller/servicios.controller.php";

echo "<h2>Test de Funciones Principales</h2>";

echo "<h3>1. Test de Categorías</h3>";
try {
    $categorias = ControladorServicios::ctrObtenerCategorias();
    echo "<p style='color: green;'>✓ Categorías obtenidas: " . count($categorias) . "</p>";
    echo "<pre>" . print_r($categorias, true) . "</pre>";
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error en categorías: " . $e->getMessage() . "</p>";
}

echo "<h3>2. Test de Servicios</h3>";
try {
    $servicios = ControladorServicios::ctrObtenerServicios();
    echo "<p style='color: green;'>✓ Servicios obtenidos: " . count($servicios) . "</p>";
    echo "<pre>" . print_r($servicios, true) . "</pre>";
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error en servicios: " . $e->getMessage() . "</p>";
}

echo "<h3>3. Test de Reserva por ID</h3>";
try {
    $reserva = ControladorServicios::ctrObtenerReservaPorId(68);
    if ($reserva) {
        echo "<p style='color: green;'>✓ Reserva 68 encontrada</p>";
        echo "<pre>" . print_r($reserva, true) . "</pre>";
    } else {
        echo "<p style='color: orange;'>⚠ Reserva 68 no encontrada</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error en reserva: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><a href='servicios'>→ Ir a Servicios</a> | <a href='javascript:history.back()'>← Volver</a></p>";
?>
