<?php
/**
 * AJAX para gestión de reservas desde el módulo público
 */

// Verificar que sea una solicitud POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => true, 'mensaje' => 'Método de solicitud no permitido']);
    exit;
}

// Incluir controladores necesarios
require_once '../controller/AuthController.php';
require_once '../controller/ReservasPublicController.php';
require_once dirname(dirname(__DIR__)) . "/controller/servicios.controller.php";

// Verificar que la acción exista
if (!isset($_POST['action'])) {
    echo json_encode(['error' => true, 'mensaje' => 'Acción no especificada']);
    exit;
}

switch ($_POST['action']) {
    case 'obtenerReservasPaciente':
        // Verificar que el usuario esté autenticado
        if (!AuthController::isAuthenticated()) {
            echo json_encode(['error' => true, 'mensaje' => 'Usuario no autenticado']);
            exit;
        }
        
        // Obtener las reservas del paciente actual
        $reservas = ReservasPublicController::ctrObtenerReservasPaciente();
        
        if ($reservas !== false) {
            echo json_encode(['error' => false, 'reservas' => $reservas]);
        } else {
            echo json_encode(['error' => true, 'mensaje' => 'No se pudieron obtener las reservas']);
        }
        break;
        
    case 'cancelarReserva':
        // Verificar que el código de seguimiento esté presente
        if (empty($_POST['codigo'])) {
            echo json_encode(['error' => true, 'mensaje' => 'Código de seguimiento no especificado']);
            exit;
        }

        // Verificar que el usuario esté autenticado
        if (!AuthController::isAuthenticated()) {
            echo json_encode(['error' => true, 'mensaje' => 'Usuario no autenticado']);
            exit;
        }

        // Obtener el ID del paciente del usuario actual
        $userData = AuthController::ctrGetUserData();
        $pacienteId = $userData['person_id'] ?? null;

        if (!$pacienteId) {
            echo json_encode(['error' => true, 'mensaje' => 'No se pudo identificar al paciente']);
            exit;
        }

        // Verificar que la reserva pertenece al paciente actual
        $codigoSeguimiento = $_POST['codigo'];
        $reserva = ReservasPublicController::ctrBuscarReservaCodigo($codigoSeguimiento);

        if (!$reserva) {
            echo json_encode(['error' => true, 'mensaje' => 'Reserva no encontrada']);
            exit;
        }

        // Verificar que la reserva pertenezca al paciente actual
        if ($reserva['paciente_id'] != $pacienteId) {
            echo json_encode(['error' => true, 'mensaje' => 'No tienes permisos para cancelar esta reserva']);
            exit;
        }

        // Verificar que la reserva esté en estado PENDIENTE
        if ($reserva['reserva_estado'] !== 'PENDIENTE') {
            echo json_encode(['error' => true, 'mensaje' => 'Solo se pueden cancelar reservas pendientes']);
            exit;
        }

        // Cancelar la reserva - Aquí necesitamos implementar la cancelación
        // Esto es una implementación temporal para actualizar el estado de la reserva
        require_once dirname(dirname(__DIR__)) . "/model/conexion.php";
        
        try {
            $conn = Conexion::conectar();
            $stmt = $conn->prepare("UPDATE servicios_reservas SET reserva_estado = 'CANCELADA', 
                                    observaciones = COALESCE(observaciones, '') || ' Cancelada por el paciente desde módulo público'
                                    WHERE codigo_seguimiento = :codigo");
            $stmt->bindParam(':codigo', $codigoSeguimiento, PDO::PARAM_STR);
            $resultado = $stmt->execute();
            
            if ($resultado) {
                // Registrar en log
                error_log("Reserva {$codigoSeguimiento} cancelada por el paciente {$pacienteId}", 3, "c:/laragon/www/clinica/logs/public_reservas.log");
                echo json_encode(['error' => false, 'mensaje' => 'Reserva cancelada correctamente']);
            } else {
                echo json_encode(['error' => true, 'mensaje' => 'Error al cancelar la reserva']);
            }
        } catch (PDOException $e) {
            error_log("Error al cancelar reserva: " . $e->getMessage(), 3, "c:/laragon/www/clinica/logs/public_reservas.log");
            echo json_encode(['error' => true, 'mensaje' => 'Error al cancelar la reserva: ' . $e->getMessage()]);
        }
        break;
    
    default:
        echo json_encode(['error' => true, 'mensaje' => 'Acción no reconocida']);
        break;
}
?>
