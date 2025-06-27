<?php
/**
 * Script para verificar la funcionalidad de reservas públicas
 * Este archivo puede ejecutarse independientemente para verificar
 * las diferentes funciones del módulo de reservas públicas
 */

require_once __DIR__ . "/model/ReservasPublicModel.php";
require_once __DIR__ . "/controller/ReservasPublicController.php";

error_log("=====================================================", 3, "c:/laragon/www/clinica/logs/public_reservas.log");
error_log("Verificación de funcionalidades de reservas públicas", 3, "c:/laragon/www/clinica/logs/public_reservas.log");
error_log("=====================================================", 3, "c:/laragon/www/clinica/logs/public_reservas.log");

// Establecer fecha para pruebas (día actual)
$fecha = date('Y-m-d');
error_log("Fecha de prueba: $fecha", 3, "c:/laragon/www/clinica/logs/public_reservas.log");

// 1. Verificar obtención de servicios
$servicios = ReservasPublicModel::mdlObtenerServicios();
error_log("1. Servicios encontrados: " . count($servicios), 3, "c:/laragon/www/clinica/logs/public_reservas.log");
if (count($servicios) > 0) {
    error_log("   Primer servicio: " . json_encode($servicios[0]), 3, "c:/laragon/www/clinica/logs/public_reservas.log");
}

// 2. Verificar obtención de médicos disponibles
$medicos = ReservasPublicModel::mdlObtenerMedicosDisponibles($fecha);
error_log("2. Médicos disponibles para la fecha $fecha: " . count($medicos), 3, "c:/laragon/www/clinica/logs/public_reservas.log");
if (count($medicos) > 0) {
    error_log("   Primer médico: " . json_encode($medicos[0]), 3, "c:/laragon/www/clinica/logs/public_reservas.log");
    
    $doctorId = $medicos[0]['doctor_id'];
    $servicioId = count($servicios) > 0 ? $servicios[0]['serv_id'] : 1;
    
    // 3. Verificar reservas existentes
    $reservasExistentes = ReservasPublicModel::mdlVerificarReservasExistentes($fecha, $doctorId);
    error_log("3. Reservas existentes para la fecha $fecha y doctor_id=$doctorId: " . count($reservasExistentes), 3, "c:/laragon/www/clinica/logs/public_reservas.log");
    if (count($reservasExistentes) > 0) {
        error_log("   Primera reserva: " . json_encode($reservasExistentes[0]), 3, "c:/laragon/www/clinica/logs/public_reservas.log");
    }
    
    // 4. Verificar horarios disponibles
    $horariosDisponibles = ReservasPublicController::ctrObtenerHorariosDisponibles($fecha, $servicioId, $doctorId);
    error_log("4. Horarios disponibles generados: " . count($horariosDisponibles), 3, "c:/laragon/www/clinica/logs/public_reservas.log");
    if (count($horariosDisponibles) > 0) {
        error_log("   Primer horario disponible: " . json_encode($horariosDisponibles[0]), 3, "c:/laragon/www/clinica/logs/public_reservas.log");
    }
}

error_log("=====================================================", 3, "c:/laragon/www/clinica/logs/public_reservas.log");
echo "<h1>Verificación completada</h1>";
echo "<p>Se ha registrado la información en el archivo de logs: c:/laragon/www/clinica/logs/public_reservas.log</p>";
