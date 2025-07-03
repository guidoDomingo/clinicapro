<?php
/**
 * Script de prueba para verificar la integración entre el módulo public_reservas
 * y el sistema principal para el guardado de reservas
 */

// Incluir archivos necesarios
require_once __DIR__ . "/../controller/servicios.controller.php";
require_once __DIR__ . "/../model/servicios.model.php";
require_once __DIR__ . "/controller/ReservasPublicController.php";
require_once __DIR__ . "/model/ReservasPublicModel.php";

// Configurar salida como JSON
header('Content-Type: application/json');

// Iniciar registro de logs
$logFile = __DIR__ . "/../logs/integration_test.log";
file_put_contents($logFile, date('Y-m-d H:i:s') . " - Iniciando prueba de integración\n", FILE_APPEND);

try {
    // Datos de prueba para una reserva
    $datos = [
        'servicio_id' => 1, // Asegúrate de usar un ID válido
        'doctor_id' => 1,   // Asegúrate de usar un ID válido
        'paciente_id' => 1, // Asegúrate de usar un ID válido
        'fecha_reserva' => date('Y-m-d', strtotime('+1 day')), // Mañana
        'hora_inicio' => '09:00:00',
        'hora_fin' => '09:30:00',
        'observaciones' => 'Prueba de integración',
        'reserva_estado' => 'PENDIENTE',
        'codigo_seguimiento' => 'TEST' . date('YmdHis')
    ];
    
    // Registrar datos de prueba
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - Datos de prueba: " . json_encode($datos) . "\n", FILE_APPEND);
    
    // Verificar que los modelos y controladores existen
    $tests = [
        'ControladorServicios' => class_exists('ControladorServicios'),
        'ModelServicios' => class_exists('ModelServicios'),
        'ReservasPublicController' => class_exists('ReservasPublicController'),
        'ReservasPublicModel' => class_exists('ReservasPublicModel')
    ];
    
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - Verificación de clases: " . json_encode($tests) . "\n", FILE_APPEND);
    
    // Verificar que el método existe en ControladorServicios
    $methods = [
        'ctrGuardarReserva' => method_exists('ControladorServicios', 'ctrGuardarReserva')
    ];
    
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - Verificación de métodos: " . json_encode($methods) . "\n", FILE_APPEND);
    
    // Intentar guardar una reserva usando ambos controladores para comparar
    $resultado1 = null;
    $resultado2 = null;
    
    try {
        // Intento de guardado con ControladorServicios
        file_put_contents($logFile, date('Y-m-d H:i:s') . " - Intentando guardar con ControladorServicios::ctrGuardarReserva\n", FILE_APPEND);
        $resultado1 = ControladorServicios::ctrGuardarReserva($datos);
        file_put_contents($logFile, date('Y-m-d H:i:s') . " - Resultado: " . json_encode($resultado1) . "\n", FILE_APPEND);
    } catch (Exception $e) {
        file_put_contents($logFile, date('Y-m-d H:i:s') . " - Error en ControladorServicios: " . $e->getMessage() . "\n", FILE_APPEND);
    }
    
    try {
        // Intento de guardado con ReservasPublicModel
        file_put_contents($logFile, date('Y-m-d H:i:s') . " - Intentando guardar con ReservasPublicModel::mdlGuardarReserva\n", FILE_APPEND);
        $resultado2 = ReservasPublicModel::mdlGuardarReserva($datos);
        file_put_contents($logFile, date('Y-m-d H:i:s') . " - Resultado: " . json_encode($resultado2) . "\n", FILE_APPEND);
    } catch (Exception $e) {
        file_put_contents($logFile, date('Y-m-d H:i:s') . " - Error en ReservasPublicModel: " . $e->getMessage() . "\n", FILE_APPEND);
    }
    
    // Respuesta final
    echo json_encode([
        'status' => 'success',
        'message' => 'Prueba de integración completada',
        'tests' => $tests,
        'methods' => $methods,
        'resultados' => [
            'ControladorServicios' => $resultado1,
            'ReservasPublicModel' => $resultado2
        ]
    ]);
    
} catch (Exception $e) {
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - Error general: " . $e->getMessage() . "\n", FILE_APPEND);
    
    echo json_encode([
        'status' => 'error',
        'message' => 'Error en la prueba de integración: ' . $e->getMessage()
    ]);
}
?>
