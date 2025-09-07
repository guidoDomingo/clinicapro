<?php
/**
 * Update old schedule details to have a default service
 */

require_once "controller/agendas.controller.php";
require_once "model/agendas.model.php";

echo "<h2>Update Old Schedule Details</h2>";

$modelo = new ModelAgendas();

// Get all schedule details with null servicio_id
$conexion = Conexion::conectar();
$stmt = $conexion->prepare("
    SELECT detalle_id, agenda_id, dia_semana, turno_id, sala_id, servicio_id 
    FROM agendas_detalle 
    WHERE servicio_id IS NULL
");
$stmt->execute();
$detallesNulos = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<h3>Details with null servicio_id:</h3>";
echo "<pre>";
print_r($detallesNulos);
echo "</pre>";

if (!empty($detallesNulos)) {
    // Get first available service as default
    $servicios = $modelo->mdlObtenerServicios();
    $servicioDefault = $servicios[0]['serv_id'] ?? null;
    
    if ($servicioDefault) {
        echo "<h3>Setting default service ID: $servicioDefault</h3>";
        
        // Update all null servicio_id records
        $updateStmt = $conexion->prepare("
            UPDATE agendas_detalle 
            SET servicio_id = :servicio_id 
            WHERE servicio_id IS NULL
        ");
        $updateStmt->bindParam(':servicio_id', $servicioDefault, PDO::PARAM_INT);
        $updateStmt->execute();
        
        $affectedRows = $updateStmt->rowCount();
        echo "<p><strong>Updated $affectedRows records with default service.</strong></p>";
        
        // Verify the update
        $stmt->execute();
        $detallesDespues = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "<h3>Details after update:</h3>";
        echo "<pre>";
        print_r($detallesDespues);
        echo "</pre>";
        
    } else {
        echo "<p>No services available to set as default.</p>";
    }
} else {
    echo "<p>No details with null servicio_id found.</p>";
}

echo "<p><a href='servicios' target='_blank'>Test the interface again</a></p>";
?>