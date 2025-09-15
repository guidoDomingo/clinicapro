<?php
require_once 'public_reservas/model/ReservasPublicModel.php';

echo "=== PRUEBA DEL SISTEMA DE MÉDICOS POR SERVICIO ===\n";

// Obtener servicios disponibles
echo "\n1. Servicios disponibles:\n";
$servicios = ReservasPublicModel::mdlObtenerServicios();
foreach ($servicios as $servicio) {
    echo "- ID: " . $servicio['serv_id'] . " - " . $servicio['serv_descripcion'] . "\n";
}

// Probar con un servicio específico
$servicioId = 9; // Cambiar por un ID de servicio que sepas que existe
$fecha = '2025-09-16'; // Lunes

echo "\n2. Médicos para servicio ID $servicioId en fecha $fecha (Lunes):\n";
$medicos = ReservasPublicModel::mdlObtenerMedicosPorServicio($fecha, $servicioId);

if (count($medicos) > 0) {
    foreach ($medicos as $medico) {
        echo "- Doctor ID: " . $medico['doctor_id'] . " - " . $medico['nombre_doctor'] . "\n";
        echo "  Servicio: " . $medico['serv_descripcion'] . "\n";
        echo "  Especialidad: " . $medico['especialidad'] . "\n\n";
    }
} else {
    echo "No se encontraron médicos para este servicio y fecha.\n";
}

// Probar con todos los médicos (método anterior)
echo "\n3. Todos los médicos disponibles para $fecha:\n";
$todosMedicos = ReservasPublicModel::mdlObtenerMedicosDisponibles($fecha);

if (count($todosMedicos) > 0) {
    foreach ($todosMedicos as $medico) {
        echo "- Doctor ID: " . $medico['doctor_id'] . " - " . $medico['nombre_doctor'] . "\n";
    }
} else {
    echo "No se encontraron médicos disponibles para esta fecha.\n";
}
?>