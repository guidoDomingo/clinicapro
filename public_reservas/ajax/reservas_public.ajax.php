<?php
/**
 * Controlador AJAX para manejar las solicitudes asíncronas del sistema de reservas públicas
 */

// Incluir los archivos de configuración y controladores necesarios
require_once '../controller/ReservasPublicController.php';
require_once '../model/ReservasPublicModel.php';

// Verificar que la solicitud sea POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Instanciar el controlador
    $reservasController = new ReservasPublicController();
    
    // Verificar la acción solicitada
    if (isset($_POST['accion'])) {
        
        // Manejar diferentes acciones AJAX
        switch ($_POST['accion']) {
            
            // Obtener servicios disponibles
            case 'obtenerServicios':
                if (isset($_POST['fecha'])) {
                    $fecha = $_POST['fecha'];
                    $servicios = $reservasController->ctrObtenerServicios($fecha);
                    
                    // Devolver respuesta en formato JSON
                    echo json_encode($servicios);
                } else {
                    echo json_encode(['error' => true, 'mensaje' => 'No se proporcionó una fecha']);
                }
                break;
                
            // Obtener médicos disponibles
            case 'obtenerMedicos':
                if (isset($_POST['fecha']) && isset($_POST['servicio_id'])) {
                    $fecha = $_POST['fecha'];
                    $servicioId = $_POST['servicio_id'];
                    
                    $medicos = $reservasController->ctrObtenerMedicosDisponibles($fecha, $servicioId);
                    
                    // Devolver respuesta en formato JSON
                    echo json_encode($medicos);
                } else {
                    echo json_encode(['error' => true, 'mensaje' => 'Faltan parámetros requeridos']);
                }
                break;
                
            // Obtener horarios disponibles
            case 'obtenerHorarios':
                if (isset($_POST['fecha']) && isset($_POST['servicio_id']) && isset($_POST['doctor_id'])) {
                    $fecha = $_POST['fecha'];
                    $servicioId = $_POST['servicio_id'];
                    $doctorId = $_POST['doctor_id'];
                    
                    $horarios = $reservasController->ctrObtenerHorariosDisponibles($fecha, $servicioId, $doctorId);
                    
                    // Devolver respuesta en formato JSON
                    echo json_encode($horarios);
                } else {
                    echo json_encode(['error' => true, 'mensaje' => 'Faltan parámetros requeridos']);
                }
                break;
                
            // Buscar una reserva por código de seguimiento
            case 'buscarReserva':
                if (isset($_POST['codigo'])) {
                    $codigo = $_POST['codigo'];
                    $reserva = $reservasController->ctrBuscarReservaCodigo($codigo);
                    
                    if ($reserva) {
                        echo json_encode(['error' => false, 'mensaje' => 'Reserva encontrada', 'datos' => $reserva]);
                    } else {
                        echo json_encode(['error' => true, 'mensaje' => 'No se encontró ninguna reserva con el código proporcionado']);
                    }
                } else {
                    echo json_encode(['error' => true, 'mensaje' => 'No se proporcionó un código de reserva']);
                }
                break;
                
            // Verificar una reserva con código de verificación
            case 'verificarReserva':
                if (isset($_POST['codigo']) && isset($_POST['codigo_verificacion'])) {
                    $codigo = $_POST['codigo'];
                    $codigoVerificacion = $_POST['codigo_verificacion'];
                    
                    $resultado = $reservasController->ctrVerificarCodigo($codigo, $codigoVerificacion);
                    
                    if ($resultado) {
                        echo json_encode(['error' => false, 'mensaje' => 'Reserva verificada exitosamente']);
                    } else {
                        echo json_encode(['error' => true, 'mensaje' => 'El código de verificación es incorrecto o ya caducó']);
                    }
                } else {
                    echo json_encode(['error' => true, 'mensaje' => 'Faltan parámetros requeridos']);
                }
                break;
                
            // Si no se encuentra la acción solicitada
            default:
                echo json_encode(['error' => true, 'mensaje' => 'Acción no reconocida']);
                break;
        }
    } else {
        // No se especificó ninguna acción
        echo json_encode(['error' => true, 'mensaje' => 'No se especificó ninguna acción']);
    }
} else {
    // Si la solicitud no es POST
    echo json_encode(['error' => true, 'mensaje' => 'Método de solicitud no permitido']);
}
?>
