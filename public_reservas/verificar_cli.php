<?php
/**
 * Script CLI para verificar la funcionalidad de reservas públicas
 * Ejecutar con: php verificar_cli.php
 */

// Configurar para ejecución en CLI
if (php_sapi_name() !== 'cli') {
    echo "Este script está diseñado para ejecutarse en línea de comandos\n";
    exit(1);
}

// Cargar dependencias
require_once __DIR__ . "/model/ReservasPublicModel.php";
require_once __DIR__ . "/controller/ReservasPublicController.php";

echo "=====================================================\n";
echo "Verificación de funcionalidades de reservas públicas\n";
echo "=====================================================\n";

// Establecer fecha para pruebas (día actual)
$fecha = date('Y-m-d');
echo "Fecha de prueba: $fecha\n";

// 1. Verificar obtención de servicios
$servicios = ReservasPublicModel::mdlObtenerServicios();
echo "1. Servicios encontrados: " . count($servicios) . "\n";
if (count($servicios) > 0) {
    echo "   Primer servicio: " . json_encode($servicios[0], JSON_PRETTY_PRINT) . "\n";
}

// 2. Verificar obtención de médicos disponibles
$medicos = ReservasPublicModel::mdlObtenerMedicosDisponibles($fecha);
echo "2. Médicos disponibles para la fecha $fecha: " . count($medicos) . "\n";
if (count($medicos) > 0) {
    echo "   Primer médico: " . json_encode($medicos[0], JSON_PRETTY_PRINT) . "\n";
    
    $doctorId = $medicos[0]['doctor_id'];
    $servicioId = count($servicios) > 0 ? $servicios[0]['serv_id'] : 1;
    
    // 3. Verificar reservas existentes
    $reservasExistentes = ReservasPublicModel::mdlVerificarReservasExistentes($fecha, $doctorId);
    echo "3. Reservas existentes para la fecha $fecha y doctor_id=$doctorId: " . count($reservasExistentes) . "\n";
    if (count($reservasExistentes) > 0) {
        echo "   Primera reserva: " . json_encode($reservasExistentes[0], JSON_PRETTY_PRINT) . "\n";
    }
    
    // 4. Verificar horarios disponibles
    echo "4. Generando horarios disponibles para fecha=$fecha, servicio_id=$servicioId, doctor_id=$doctorId\n";
    $horariosDisponibles = ReservasPublicController::ctrObtenerHorariosDisponibles($fecha, $servicioId, $doctorId);
    echo "   Horarios disponibles generados: " . count($horariosDisponibles) . "\n";
    if (count($horariosDisponibles) > 0) {
        echo "   Primer horario disponible: " . json_encode($horariosDisponibles[0], JSON_PRETTY_PRINT) . "\n";
    }
}

echo "=====================================================\n";
echo "Verificación completada. Se ha registrado la información también en el archivo de logs: c:/laragon/www/clinica/logs/public_reservas.log\n";
