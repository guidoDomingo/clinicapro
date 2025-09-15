<?php
/**
 * Controlador AJAX para el Flujo Completo de Reservas
 * Maneja las peticiones AJAX del sistema de reservas paso a paso
 */

require_once __DIR__ . "/../model/ReservasPublicModel.php";
require_once __DIR__ . "/AuthController.php";
require_once __DIR__ . "/../helpers/MailerPublic.php";

// Solo permitir acceso AJAX
if (!isset($_POST['action'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'mensaje' => 'Acceso no válido']);
    exit;
}

$action = $_POST['action'];

try {
    switch ($action) {
        case 'obtener_servicios':
            obtenerServicios();
            break;
            
        case 'obtener_medicos_disponibles':
            obtenerMedicosDisponibles();
            break;
            
        case 'obtener_horarios_disponibles':
            obtenerHorariosDisponibles();
            break;
            
        case 'confirmar_reserva':
            confirmarReserva();
            break;
            
        default:
            responderError('Acción no válida: ' . $action);
            break;
    }
} catch (Exception $e) {
    error_log("Error en flujo AJAX: " . $e->getMessage(), 3, 'c:/laragon/www/clinica/logs/public_reservas.log');
    responderError('Error interno del servidor');
}

/**
 * Obtener todos los servicios disponibles
 */
function obtenerServicios() {
    $modelo = new ReservasPublicModel();
    $servicios = $modelo->mdlObtenerServicios();
    
    if ($servicios === false) {
        responderError('Error al obtener servicios');
        return;
    }
    
    // Formatear los servicios para el frontend
    $serviciosFormateados = [];
    foreach ($servicios as $servicio) {
        $serviciosFormateados[] = [
            'id' => $servicio['serv_id'],
            'nombre' => $servicio['serv_descripcion'],
            'precio' => floatval($servicio['serv_monto'] ?? 0),
            'descripcion' => $servicio['serv_observaciones'] ?? 'Consulta médica profesional',
            'duracion' => calcularDuracion($servicio['serv_tiempo'] ?? 30)
        ];
    }
    
    responderExito($serviciosFormateados);
}

/**
 * Obtener médicos disponibles para una fecha y servicio
 */
function obtenerMedicosDisponibles() {
    if (!isset($_POST['fecha']) || !isset($_POST['servicio_id'])) {
        responderError('Faltan parámetros requeridos');
        return;
    }
    
    $fecha = $_POST['fecha'];
    $servicioId = $_POST['servicio_id'];
    
    // Validar fecha
    if (!validarFecha($fecha)) {
        responderError('Fecha no válida');
        return;
    }
    
    $modelo = new ReservasPublicModel();
    $medicos = $modelo->mdlObtenerMedicosPorServicio($fecha, $servicioId);
    
    if ($medicos === false) {
        responderError('Error al obtener médicos disponibles');
        return;
    }
    
    // Formatear médicos para el frontend
    $medicosFormateados = [];
    foreach ($medicos as $medico) {
        $medicosFormateados[] = [
            'id' => $medico['doctor_id'],
            'nombre' => trim($medico['nombre_doctor']),
            'especialidad' => $medico['especialidad'] ?? 'Medicina General',
            'foto' => obtenerFotoMedico($medico['doctor_id']),
            'disponible' => true
        ];
    }
    
    responderExito($medicosFormateados);
}

/**
 * Obtener horarios disponibles para un médico en una fecha
 */
function obtenerHorariosDisponibles() {
    if (!isset($_POST['fecha']) || !isset($_POST['medico_id']) || !isset($_POST['servicio_id'])) {
        responderError('Faltan parámetros requeridos');
        return;
    }
    
    $fecha = $_POST['fecha'];
    $medicoId = $_POST['medico_id'];
    $servicioId = $_POST['servicio_id'];
    
    // Validar fecha
    if (!validarFecha($fecha)) {
        responderError('Fecha no válida');
        return;
    }
    
    // Usar el controlador existente para obtener horarios
    require_once __DIR__ . "/ReservasPublicController.php";
    $horarios = ReservasPublicController::ctrObtenerHorariosDisponibles($fecha, $servicioId, $medicoId);
    
    if ($horarios === false) {
        responderError('Error al obtener horarios disponibles');
        return;
    }
    
    // Formatear horarios para el frontend
    $horariosFormateados = [];
    foreach ($horarios as $horario) {
        $horariosFormateados[] = [
            'hora_inicio' => $horario['hora'],
            'hora_fin' => $horario['hora_fin'] ?? null,
            'disponible' => true // Ya están filtrados como disponibles
        ];
    }
    
    responderExito($horariosFormateados);
}

/**
 * Confirmar una reserva
 */
function confirmarReserva() {
    // Verificar autenticación
    if (!AuthController::isAuthenticated()) {
        responderError('Debe estar autenticado para realizar una reserva');
        return;
    }
    
    // Validar parámetros requeridos
    $requeridos = ['fecha', 'servicio_id', 'medico_id', 'horario'];
    foreach ($requeridos as $param) {
        if (!isset($_POST[$param]) || empty($_POST[$param])) {
            responderError("Falta el parámetro: $param");
            return;
        }
    }
    
    // Obtener datos del usuario autenticado
    $userData = AuthController::ctrGetUserData();
    $pacienteId = $userData['person_id'];
    
    if (!$pacienteId) {
        responderError('No se pudo identificar al paciente');
        return;
    }
    
    $datosReserva = [
        'fecha_reserva' => $_POST['fecha'],
        'servicio_id' => $_POST['servicio_id'],
        'doctor_id' => $_POST['medico_id'],
        'horario' => $_POST['horario'],
        'observaciones' => $_POST['motivo'] ?? ''
    ];
    
    // Validar fecha
    if (!validarFecha($datosReserva['fecha_reserva'])) {
        responderError('Fecha no válida');
        return;
    }
    
    // Simular POST para usar el controlador existente
    $_POST = array_merge($_POST, $datosReserva);
    $_POST['guardarReserva'] = true;
    
    // Usar el controlador existente
    require_once __DIR__ . "/ReservasPublicController.php";
    $controller = new ReservasPublicController();
    $resultado = $controller->ctrProcesarReserva();
    
    if (!$resultado) {
        responderError('Error procesando la reserva');
        return;
    }
    
    if ($resultado['error']) {
        responderError($resultado['mensaje']);
        return;
    }
    
    responderExito([
        'mensaje' => 'Reserva confirmada exitosamente',
        'codigo_reserva' => $resultado['codigo'],
        'email' => $resultado['email']
    ]);
}

/**
 * Validar formato de fecha
 */
function validarFecha($fecha) {
    $fechaObj = DateTime::createFromFormat('Y-m-d', $fecha);
    if (!$fechaObj) {
        return false;
    }
    
    // Verificar que la fecha no sea anterior a hoy
    $hoy = new DateTime();
    if ($fechaObj < $hoy->setTime(0, 0, 0)) {
        return false;
    }
    
    // Verificar que la fecha no sea más de 3 meses en el futuro
    $maxFecha = new DateTime();
    $maxFecha->add(new DateInterval('P3M'));
    if ($fechaObj > $maxFecha) {
        return false;
    }
    
    return true;
}

/**
 * Calcular duración del servicio
 */
function calcularDuracion($minutos) {
    if ($minutos <= 30) {
        return "30 minutos";
    } elseif ($minutos <= 60) {
        return "1 hora";
    } else {
        $horas = floor($minutos / 60);
        $minutosRestantes = $minutos % 60;
        if ($minutosRestantes > 0) {
            return "{$horas}h {$minutosRestantes}min";
        } else {
            return "{$horas} hora" . ($horas > 1 ? 's' : '');
        }
    }
}

/**
 * Obtener foto del médico
 */
function obtenerFotoMedico($medicoId) {
    // Por ahora retornar foto por defecto
    return 'assets/img/default-doctor.jpg';
}

/**
 * Responder con éxito
 */
function responderExito($data = null) {
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

/**
 * Responder con error
 */
function responderError($mensaje) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'mensaje' => $mensaje
    ]);
    exit;
}
?>