<?php
/**
 * Script para probar directamente la llamada AJAX
 */

// Simular la llamada POST
$_POST['action'] = 'obtenerReservaPorId';
$_POST['reserva_id'] = 68;

echo "<h2>Test de AJAX obtenerReservaPorId</h2>";
echo "<p><strong>Datos enviados:</strong></p>";
echo "<pre>";
print_r($_POST);
echo "</pre>";

echo "<p><strong>Respuesta del servidor:</strong></p>";
echo "<div style='border: 1px solid #ccc; padding: 10px; background: #f9f9f9;'>";

// Capturar la salida
ob_start();
include 'ajax/servicios.ajax.php';
$response = ob_get_clean();

echo "<pre>" . htmlspecialchars($response) . "</pre>";
echo "</div>";

echo "<hr>";
echo "<p><a href='javascript:history.back()'>← Volver</a></p>";
?>
