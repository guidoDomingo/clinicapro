<?php
/**
 * Controlador para reservas públicas
 * Maneja la lógica del sistema de reservas para usuarios no registrados
 */

require_once __DIR__ . "/../model/ReservasPublicModel.php";

class ReservasPublicController {
    
    /**
     * Inicia la aplicación y carga la plantilla
     * @param array|null $resultadoAuth Resultado de operaciones de autenticación
     */
    public function iniciarAplicacion($resultadoAuth = null) {
        include "view/template.php";
    }
    
    /**
     * Obtiene todos los servicios disponibles
     */
    static public function ctrObtenerServicios() {
        return ReservasPublicModel::mdlObtenerServicios();
    }
    
    /**
     * Obtiene médicos disponibles para una fecha
     */
    static public function ctrObtenerMedicosDisponibles($fecha) {
        return ReservasPublicModel::mdlObtenerMedicosDisponibles($fecha);
    }
    
    /**
     * Obtiene horarios disponibles para un médico y fecha
     */
    static public function ctrObtenerHorariosDisponibles($fecha, $servicioId, $doctorId) {
        // Obtener detalles del servicio
        $servicios = ReservasPublicModel::mdlObtenerServicios();
        $duracionServicio = 30; // Duración predeterminada en minutos
        
        // Buscar la duración del servicio seleccionado
        foreach ($servicios as $servicio) {
            if ($servicio['serv_id'] == $servicioId) {
                $duracionServicio = isset($servicio['duracion']) ? $servicio['duracion'] : 30;
                break;
            }
        }
        
        // Generar horarios disponibles basados en la agenda del médico
        $horariosGenerados = self::generarHorariosDisponibles($fecha, $doctorId, $duracionServicio);
        
        // Obtener reservas existentes para esta fecha y médico
        error_log("ctrObtenerHorariosDisponibles: Solicitando reservas existentes para fecha={$fecha}, doctorId={$doctorId}", 3, 'c:/laragon/www/clinica/logs/public_reservas.log');
        $reservasExistentes = ReservasPublicModel::mdlVerificarReservasExistentes($fecha, $doctorId);
        error_log("ctrObtenerHorariosDisponibles: Se recibieron " . count($reservasExistentes) . " reservas existentes", 3, 'c:/laragon/www/clinica/logs/public_reservas.log');
        
        // Filtrar horarios ocupados
        $horariosDisponibles = [];
        
        foreach ($horariosGenerados as $horario) {
            $disponible = true;
            $horaInicio = $horario['hora'];
            $horaFin = $horario['hora_fin'];
            $salaId = isset($horario['sala_id']) ? $horario['sala_id'] : null;
            
            // Verificar si el horario se solapa con alguna reserva existente
            foreach ($reservasExistentes as $reserva) {
                if (isset($reserva['hora_inicio']) && isset($reserva['hora_fin'])) {
                    try {
                        // Convertir a timestamp para comparación
                        $reservaInicio = strtotime($fecha . ' ' . $reserva['hora_inicio']);
                        $reservaFin = strtotime($fecha . ' ' . $reserva['hora_fin']);
                        $slotInicio = strtotime($fecha . ' ' . $horaInicio);
                        $slotFin = strtotime($fecha . ' ' . $horaFin);
                        
                        // Verificar valores nulos en timestamps para evitar errores
                        if (!$reservaInicio || !$reservaFin || !$slotInicio || !$slotFin) {
                            error_log("Error en conversión de timestamps: reservaInicio={$reservaInicio}, reservaFin={$reservaFin}, slotInicio={$slotInicio}, slotFin={$slotFin}", 3, 'c:/laragon/www/clinica/logs/public_reservas.log');
                            continue;
                        }
                        
                        // También verificar si coincide la sala (si está disponible)
                        $mismaSala = true;
                        $reservaSalaId = isset($reserva['sala_id']) ? $reserva['sala_id'] : null;
                        
                        if ($reservaSalaId !== null && $salaId !== null) {
                            $mismaSala = ($reservaSalaId == $salaId);
                            error_log("Comparando salas: reservaSalaId={$reservaSalaId}, horarioSalaId={$salaId}, coinciden=" . ($mismaSala ? 'sí' : 'no'), 3, 'c:/laragon/www/clinica/logs/public_reservas.log');
                        }
                        
                        $solapamiento = false;
                        
                        // Verificar solapamiento de horarios
                        if (
                            ($slotInicio >= $reservaInicio && $slotInicio < $reservaFin) ||
                            ($slotFin > $reservaInicio && $slotFin <= $reservaFin) ||
                            ($slotInicio <= $reservaInicio && $slotFin >= $reservaFin)
                        ) {
                            $solapamiento = true;
                            error_log("Solapamiento detectado: horario={$horaInicio}-{$horaFin}, reserva={$reserva['hora_inicio']}-{$reserva['hora_fin']}", 3, 'c:/laragon/www/clinica/logs/public_reservas.log');
                        }
                        
                        if ($mismaSala && $solapamiento) {
                            $disponible = false;
                            error_log("Horario {$horaInicio}-{$horaFin} no disponible debido a reserva existente {$reserva['hora_inicio']}-{$reserva['hora_fin']}, sala_id={$salaId}", 3, 'c:/laragon/www/clinica/logs/public_reservas.log');
                            break;
                        }
                    } catch (Exception $e) {
                        error_log("Excepción al verificar disponibilidad: " . $e->getMessage(), 3, 'c:/laragon/www/clinica/logs/public_reservas.log');
                        continue;
                    }
                }
            }
            
            if ($disponible) {
                $horariosDisponibles[] = $horario;
                error_log("Horario disponible: {$horaInicio}-{$horaFin}, sala_id={$salaId}", 3, 'c:/laragon/www/clinica/logs/public_reservas.log');
            }
        }
        
        // Registro de depuración
        error_log("ctrObtenerHorariosDisponibles: Se encontraron " . count($horariosDisponibles) . " horarios disponibles", 3, 'c:/laragon/www/clinica/logs/public_reservas.log');
        
        return $horariosDisponibles;
    }
    
    /**
     * Genera horarios disponibles en franjas para un médico en una fecha específica
     * @param string $fecha Fecha en formato YYYY-MM-DD
     * @param int $doctorId ID del médico/doctor
     * @param int $duracionServicio Duración del servicio en minutos
     * @return array Lista de horarios disponibles
     */
    static private function generarHorariosDisponibles($fecha, $doctorId, $duracionServicio = 30) {
        $horarios = [];
        
        // Obtener el día de la semana como texto (LUNES, MARTES, etc.)
        $diasSemanaEsp = ['', 'LUNES', 'MARTES', 'MIERCOLES', 'JUEVES', 'VIERNES', 'SABADO', 'DOMINGO'];
        $diaSemana = date('N', strtotime($fecha)); // 1 (lunes) a 7 (domingo)
        $diaSemanaTexto = $diasSemanaEsp[$diaSemana];
        
        try {
            // Conectar a la base de datos
            $pdo = Conexion::conectar();
            
            // Buscar la agenda del médico y sus detalles para ese día de la semana
            $stmt = $pdo->prepare(
                "SELECT 
                    ac.agenda_id,
                    ac.agenda_descripcion,
                    ad.detalle_id,
                    ad.turno_id,
                    t.turno_nombre,
                    ad.sala_id,
                    s.sala_nombre,
                    ad.dia_semana,
                    ad.hora_inicio,
                    ad.hora_fin,
                    ad.intervalo_minutos
                FROM 
                    agendas_cabecera ac
                INNER JOIN 
                    agendas_detalle ad ON ac.agenda_id = ad.agenda_id
                INNER JOIN
                    turnos t ON ad.turno_id = t.turno_id
                INNER JOIN
                    salas s ON ad.sala_id = s.sala_id
                WHERE 
                    ac.medico_id = :doctor_id
                    AND ad.dia_semana = :dia_semana
                    AND ac.agenda_estado = true
                    AND ad.detalle_estado = true
                ORDER BY
                    ad.hora_inicio ASC"
            );
            
            $stmt->bindParam(':doctor_id', $doctorId, PDO::PARAM_INT);
            $stmt->bindParam(':dia_semana', $diaSemanaTexto, PDO::PARAM_STR);
            $stmt->execute();
            
            $agendaDetalles = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Registro de depuración
            error_log("Horarios encontrados para doctor_id={$doctorId}, dia={$diaSemanaTexto}: " . count($agendaDetalles), 3, 'c:/laragon/www/clinica/logs/public_reservas.log');
            if (count($agendaDetalles) > 0) {
                error_log("Primer horario: " . json_encode($agendaDetalles[0]), 3, 'c:/laragon/www/clinica/logs/public_reservas.log');
            }
            
            // Generar slots de tiempo para cada detalle de agenda encontrado
            foreach ($agendaDetalles as $detalle) {
                // Usar el intervalo de la agenda o la duración del servicio, el que sea mayor
                $intervaloMinutos = max($detalle['intervalo_minutos'], $duracionServicio);
                
                // Convertir las horas a timestamps para facilitar cálculos
                $horaInicio = strtotime($detalle['hora_inicio']);
                $horaFin = strtotime($detalle['hora_fin']);
                
                // Generar slots desde hora_inicio hasta hora_fin según el intervalo
                for ($tiempo = $horaInicio; $tiempo < $horaFin; $tiempo += ($intervaloMinutos * 60)) {
                    $hora = date('H:i', $tiempo);
                    $horaFinSlot = date('H:i', $tiempo + ($duracionServicio * 60));
                    
                    // Formato para mostrar
                    $horaFormateada = date('h:i A', $tiempo); // Formato 12h con AM/PM
                    
                    $horarios[] = [
                        'hora' => $hora,
                        'hora_fin' => $horaFinSlot,
                        'hora_formateada' => $horaFormateada,
                        'duracion' => $duracionServicio,
                        'sala_id' => $detalle['sala_id'],
                        'sala_nombre' => $detalle['sala_nombre'],
                        'turno_nombre' => $detalle['turno_nombre']
                    ];
                }
            }
            
        } catch (PDOException $e) {
            error_log("Error generando horarios disponibles: " . $e->getMessage(), 3, 'c:/laragon/www/clinica/logs/public_reservas.log');
        }
        
        // Si no se encontraron horarios para este día, devolver array vacío
        return $horarios;
    }
    
    /**
     * Procesa la reserva de una cita
     */
    public function ctrProcesarReserva() {
        // Agregar registro para depuración
        error_log("ctrProcesarReserva: Recibiendo solicitud POST. guardarReserva=" . 
            (isset($_POST['guardarReserva']) ? 'true' : 'false'), 
            3, "c:/laragon/www/clinica/logs/public_reservas.log");
            
        if (isset($_POST)) {
            error_log("ctrProcesarReserva: Contenido de POST: " . json_encode($_POST), 
                3, "c:/laragon/www/clinica/logs/public_reservas.log");
        }
        
        if (isset($_POST['guardarReserva'])) {
            // Validar datos obligatorios para la reserva
            if (
                empty($_POST['fecha_reserva']) || 
                empty($_POST['servicio_id']) || 
                empty($_POST['doctor_id']) || 
                empty($_POST['horario'])
            ) {
                return [
                    'error' => true,
                    'mensaje' => 'Todos los campos marcados con * son obligatorios'
                ];
            }
            
            try {
                // Obtener el ID del paciente desde la sesión del usuario autenticado
                if (!AuthController::isAuthenticated()) {
                    return [
                        'error' => true,
                        'mensaje' => 'No hay usuario autenticado para realizar la reserva'
                    ];
                }
                
                // Obtener datos del usuario
                $userData = AuthController::ctrGetUserData();
                $pacienteId = $userData['person_id']; // Usar el ID del paciente asociado al usuario
                
                error_log("ctrProcesarReserva: Usando paciente con ID: " . $pacienteId . " de la sesión actual", 
                    3, "c:/laragon/www/clinica/logs/public_reservas.log");
                error_log("ctrProcesarReserva: Datos completos del usuario: " . json_encode($userData), 
                    3, "c:/laragon/www/clinica/logs/public_reservas.log");
                
                if (!$pacienteId) {
                    return [
                        'error' => true,
                        'mensaje' => 'El usuario no tiene un perfil de paciente asociado'
                    ];
                }
                
                // Procesar el horario (puede venir en formato simple "08:00" o completo "08:00 - 08:30")
                $horario = $_POST['horario'];
                error_log("ctrProcesarReserva: Procesando horario original: " . $horario, 
                    3, "c:/laragon/www/clinica/logs/public_reservas.log");
                
                // Verificar si el horario tiene el formato completo con hora inicio y fin
                if (strpos($horario, " - ") !== false) {
                    $horasParts = explode(" - ", $horario);
                    if (count($horasParts) != 2) {
                        error_log("ctrProcesarReserva: ERROR - Formato de horario inválido: " . $horario, 
                            3, "c:/laragon/www/clinica/logs/public_reservas.log");
                        return [
                            'error' => true,
                            'mensaje' => 'Formato de horario inválido'
                        ];
                    }
                    
                    $horaInicio = trim($horasParts[0]);
                    $horaFin = trim($horasParts[1]);
                } 
                // Si viene solo la hora de inicio, buscar la hora fin correspondiente
                else {
                    $horaInicio = trim($horario);
                    
                    error_log("ctrProcesarReserva: Recibido horario simple, buscando horario fin para: " . $horaInicio, 
                        3, "c:/laragon/www/clinica/logs/public_reservas.log");
                    
                    // Buscar en los horarios disponibles la hora fin correspondiente
                    $horarioEncontrado = false;
                    $horariosDisponibles = self::ctrObtenerHorariosDisponibles($_POST['fecha_reserva'], 
                        $_POST['servicio_id'], $_POST['doctor_id']);
                    
                    error_log("ctrProcesarReserva: Horarios disponibles encontrados: " . count($horariosDisponibles), 
                        3, "c:/laragon/www/clinica/logs/public_reservas.log");
                    
                    foreach ($horariosDisponibles as $slot) {
                        if ($slot['hora'] == $horaInicio) {
                            $horaFin = $slot['hora_fin'];
                            $horarioEncontrado = true;
                            error_log("ctrProcesarReserva: Horario completo encontrado - Inicio: " . $horaInicio . ", Fin: " . $horaFin, 
                                3, "c:/laragon/www/clinica/logs/public_reservas.log");
                            break;
                        }
                    }
                    
                    // Si no se encontró el horario, calcular hora fin sumando 30 min por defecto
                    if (!$horarioEncontrado) {
                        $horaObj = new DateTime($horaInicio);
                        $horaObj->modify('+30 minutes');
                        $horaFin = $horaObj->format('H:i');
                        error_log("ctrProcesarReserva: No se encontró el horario completo. Calculando hora fin: " . $horaFin, 
                            3, "c:/laragon/www/clinica/logs/public_reservas.log");
                    }
                }
                
                // Generar código de seguimiento único
                $codigoSeguimiento = 'RES' . date('YmdHis') . rand(100, 999);
                
                // Asegurar que las horas tengan formato HH:MM:SS
                $horaInicioFormateada = $horaInicio;
                $horaFinFormateada = $horaFin;
                
                // Verificar si las horas tienen el formato HH:MM y convertir a HH:MM:SS
                if (preg_match('/^\d{2}:\d{2}$/', $horaInicioFormateada)) {
                    $horaInicioFormateada .= ':00';
                    error_log("ctrProcesarReserva: Formateando hora_inicio de $horaInicio a $horaInicioFormateada", 
                        3, "c:/laragon/www/clinica/logs/public_reservas.log");
                }
                
                if (preg_match('/^\d{2}:\d{2}$/', $horaFinFormateada)) {
                    $horaFinFormateada .= ':00';
                    error_log("ctrProcesarReserva: Formateando hora_fin de $horaFin a $horaFinFormateada", 
                        3, "c:/laragon/www/clinica/logs/public_reservas.log");
                }
                
                // Preparar datos de la reserva
                $datosReserva = [
                    'servicio_id' => intval($_POST['servicio_id']),
                    'doctor_id' => intval($_POST['doctor_id']),
                    'paciente_id' => $pacienteId,
                    'fecha_reserva' => $_POST['fecha_reserva'],
                    'hora_inicio' => $horaInicioFormateada,
                    'hora_fin' => $horaFinFormateada,
                    'reserva_estado' => 'PENDIENTE',
                    'observaciones' => isset($_POST['observaciones']) ? $_POST['observaciones'] : '',
                    'codigo_seguimiento' => $codigoSeguimiento
                ];
                
                // Agregar seguro médico si está seleccionado
                if (!empty($_POST['seguro_id'])) {
                    $datosReserva['seguro_id'] = intval($_POST['seguro_id']);
                    error_log("ctrProcesarReserva: Agregando seguro_id: " . intval($_POST['seguro_id']), 
                        3, "c:/laragon/www/clinica/logs/public_reservas.log");
                }
                
                // Incluir controlador y modelo de servicios principal
                require_once dirname(__DIR__, 2) . "/controller/servicios.controller.php";
                require_once dirname(__DIR__, 2) . "/model/servicios.model.php";
                
                // Log para depuración
                error_log("ctrProcesarReserva: Usando ControladorServicios::ctrGuardarReserva con datos: " . 
                    json_encode($datosReserva), 3, "c:/laragon/www/clinica/logs/public_reservas.log");
                
                // Guardar la reserva usando el controlador principal
                $reservaId = ControladorServicios::ctrGuardarReserva($datosReserva);
                
                // Log del resultado
                error_log("ctrProcesarReserva: Resultado de guardar reserva: " . ($reservaId ? "ID: $reservaId" : "ERROR - No se guardó la reserva"),
                    3, "c:/laragon/www/clinica/logs/public_reservas.log");
                
                if ($reservaId) {
                    // Obtener los datos del paciente para la confirmación
                    $userData = AuthController::ctrGetUserData();
                    $nombrePaciente = $userData['nombre'] . ' ' . $userData['apellido'];
                    $emailPaciente = $userData['email'];
                    
                    // Obtener detalles del médico para el correo
                    $medicos = self::ctrObtenerMedicosDisponibles($_POST['fecha_reserva']);
                    $nombreMedico = 'Médico Asignado'; // Valor por defecto
                    foreach ($medicos as $medico) {
                        if ($medico['doctor_id'] == $datosReserva['doctor_id']) {
                            $nombreMedico = $medico['nombre'];
                            break;
                        }
                    }
                    
                    // Obtener detalles del servicio para el correo
                    $servicios = self::ctrObtenerServicios();
                    $nombreServicio = 'Servicio Reservado'; // Valor por defecto
                    foreach ($servicios as $servicio) {
                        if ($servicio['serv_id'] == $datosReserva['servicio_id']) {
                            $nombreServicio = $servicio['serv_descripcion'];
                            break;
                        }
                    }
                    
                    // Registrar información adicional para depuración
                    error_log("ctrProcesarReserva: Reserva creada exitosamente con ID: $reservaId", 
                        3, "c:/laragon/www/clinica/logs/public_reservas.log");
                    error_log("ctrProcesarReserva: Detalles de la reserva - Médico: $nombreMedico, Servicio: $nombreServicio", 
                        3, "c:/laragon/www/clinica/logs/public_reservas.log");
                    
                    // Enviar notificación por email simplificada (sin código de verificación)
                    $this->enviarEmailConfirmacion(
                        $emailPaciente,
                        $nombrePaciente,
                        $_POST['fecha_reserva'],
                        $_POST['horario'],
                        $codigoSeguimiento,
                        '', // Sin código de verificación
                        $nombreMedico,
                        $nombreServicio
                    );
                    
                    return [
                        'error' => false,
                        'mensaje' => 'Reserva creada exitosamente',
                        'codigo' => $codigoSeguimiento,
                        'email' => $emailPaciente
                    ];
                } else {
                    return [
                        'error' => true,
                        'mensaje' => 'Error al crear la reserva. Es posible que el horario ya no esté disponible.'
                    ];
                }
                
            } catch (Exception $e) {
                error_log("Error al procesar reserva: " . $e->getMessage());
                return [
                    'error' => true,
                    'mensaje' => 'Error al procesar la reserva: ' . $e->getMessage()
                ];
            }
        }
        
        return null;
    }
    
    /**
     * Envía email de confirmación al paciente
     * @param string $email Email del paciente
     * @param string $nombrePaciente Nombre completo del paciente
     * @param string $fecha Fecha de la reserva (YYYY-MM-DD)
     * @param string $horario Horario de la reserva
     * @param string $codigoSeguimiento Código único de seguimiento
     * @param string $codigoVerificacion Código de verificación para confirmar la reserva
     * @param string $nombreMedico Nombre del médico asignado (opcional)
     * @param string $nombreServicio Nombre del servicio reservado (opcional)
     */
    private function enviarEmailConfirmacion($email, $nombrePaciente, $fecha, $horario, $codigoSeguimiento, $codigoVerificacion, $nombreMedico = null, $nombreServicio = null) {
        // Formato de la fecha
        $fechaFormato = date('d/m/Y', strtotime($fecha));
        
        // Asunto del email
        $asunto = "Confirmación de Reserva - Código: $codigoSeguimiento";
        
        // Cuerpo del email
        $mensaje = "
        <html>
        <head>
            <title>Confirmación de Reserva</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; }
                .container { max-width: 600px; margin: 0 auto; border: 1px solid #ddd; padding: 20px; }
                .header { background-color: #3498db; color: white; padding: 10px; text-align: center; }
                .content { padding: 20px; }
                .code { background: #f8f9fa; padding: 10px; font-size: 18px; font-weight: bold; text-align: center; margin: 20px 0; letter-spacing: 5px; }
                .footer { background-color: #f8f9fa; padding: 10px; text-align: center; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>Confirmación de Reserva</h1>
                </div>
                <div class='content'>
                    <p>Estimado/a <b>$nombrePaciente</b>,</p>
                    <p>Su cita ha sido reservada correctamente con los siguientes detalles:</p>
                    <ul>
                        <li><b>Código de seguimiento:</b> $codigoSeguimiento</li>
                        <li><b>Fecha:</b> $fechaFormato</li>
                        <li><b>Horario:</b> $horario</li>";
                        
        if ($nombreMedico) {
            $mensaje .= "<li><b>Médico:</b> $nombreMedico</li>";
        }
        
        if ($nombreServicio) {
            $mensaje .= "<li><b>Servicio:</b> $nombreServicio</li>";
        }
                        
        $mensaje .= "
                    </ul>";
        
        // Solo mostrar el código de verificación si se proporcionó uno
        if (!empty($codigoVerificacion)) {
            $mensaje .= "
                    <p>Para confirmar su cita, utilice el siguiente código de verificación:</p>
                    <div class='code'>$codigoVerificacion</div>";
        }
        
        $mensaje .= "
                    <p>Puede verificar el estado de su reserva en cualquier momento ingresando a nuestro sistema con su código de seguimiento.</p>
                    <p>Si tiene alguna pregunta o necesita reprogramar su cita, por favor contáctenos lo antes posible.</p>
                    <p>¡Gracias por confiar en nosotros!</p>
                </div>
                <div class='footer'>
                    <p>© " . date('Y') . " Clínica. Todos los derechos reservados.</p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        // Cabeceras del email
        $cabeceras = "MIME-Version: 1.0" . "\r\n";
        $cabeceras .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $cabeceras .= "From: Clínica <noreply@clinica.com>" . "\r\n";
        
        // Enviar email
        mail($email, $asunto, $mensaje, $cabeceras);
        
        // Registrar en log
        error_log("Email de confirmación enviado a: $email para la reserva: $codigoSeguimiento");
    }
    
    /**
     * Obtiene los proveedores de seguro médico
     */
    static public function ctrObtenerSeguros() {
        return ReservasPublicModel::mdlObtenerSeguros();
    }
    
    /**
     * Busca una reserva por su código de seguimiento
     */
    static public function ctrBuscarReservaCodigo($codigo) {
        return ReservasPublicModel::mdlBuscarReservaCodigo($codigo);
    }
    
    /**
     * Verifica el código de verificación para un paciente
     */
    static public function ctrVerificarCodigo($pacienteId, $codigo) {
        return ReservasPublicModel::mdlVerificarCodigo($pacienteId, $codigo);
    }
}
?>
