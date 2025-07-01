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
            // Validar datos obligatorios
            if (
                empty($_POST['fecha_reserva']) || 
                empty($_POST['servicio_id']) || 
                empty($_POST['doctor_id']) || 
                empty($_POST['horario']) || 
                empty($_POST['nombre_paciente']) || 
                empty($_POST['apellido_paciente']) || 
                empty($_POST['documento_paciente']) || 
                empty($_POST['email_paciente']) || 
                empty($_POST['telefono_paciente'])
            ) {
                return [
                    'error' => true,
                    'mensaje' => 'Todos los campos marcados con * son obligatorios'
                ];
            }
            
            // Validar email
            if (!filter_var($_POST['email_paciente'], FILTER_VALIDATE_EMAIL)) {
                return [
                    'error' => true,
                    'mensaje' => 'El email ingresado no es válido'
                ];
            }
            
            try {
                // Guardar datos del paciente primero
                $datosPaciente = [
                    'first_name' => $_POST['nombre_paciente'],
                    'last_name' => $_POST['apellido_paciente'],
                    'document_number' => $_POST['documento_paciente'],
                    'email' => $_POST['email_paciente'],
                    'phone' => $_POST['telefono_paciente']
                ];
                
                // Guardar o recuperar paciente
                $pacienteId = ReservasPublicModel::mdlGuardarPaciente($datosPaciente);
                
                if (!$pacienteId) {
                    return [
                        'error' => true,
                        'mensaje' => 'No se pudo registrar al paciente'
                    ];
                }
                
                // Procesar el horario (formato: "08:00 - 08:30")
                $horasParts = explode(" - ", $_POST['horario']);
                if (count($horasParts) != 2) {
                    return [
                        'error' => true,
                        'mensaje' => 'Formato de horario inválido'
                    ];
                }
                
                $horaInicio = trim($horasParts[0]);
                $horaFin = trim($horasParts[1]);
                
                // Generar código de seguimiento único
                $codigoSeguimiento = 'RES' . date('YmdHis') . rand(100, 999);
                
                // Preparar datos de la reserva
                $datosReserva = [
                    'servicio_id' => intval($_POST['servicio_id']),
                    'doctor_id' => intval($_POST['doctor_id']),
                    'paciente_id' => $pacienteId,
                    'fecha_reserva' => $_POST['fecha_reserva'],
                    'hora_inicio' => $horaInicio,
                    'hora_fin' => $horaFin,
                    'reserva_estado' => 'PENDIENTE',
                    'observaciones' => isset($_POST['observaciones']) ? $_POST['observaciones'] : '',
                    'codigo_seguimiento' => $codigoSeguimiento
                ];
                
                // Agregar seguro médico si está seleccionado
                if (!empty($_POST['seguro_id'])) {
                    $datosReserva['seguro_id'] = intval($_POST['seguro_id']);
                }
                
                // Guardar la reserva
                $reservaId = ReservasPublicModel::mdlGuardarReserva($datosReserva);
                
                if ($reservaId) {
                    // Generar código de verificación
                    $codigoVerificacion = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 6));
                    
                    // Guardar código de verificación
                    ReservasPublicModel::mdlGuardarCodigoVerificacion(
                        $pacienteId,
                        $codigoVerificacion,
                        $_POST['email_paciente']
                    );
                    
                    // Enviar notificación por email
                    $this->enviarEmailConfirmacion(
                        $_POST['email_paciente'],
                        $_POST['nombre_paciente'] . ' ' . $_POST['apellido_paciente'],
                        $_POST['fecha_reserva'],
                        $_POST['horario'],
                        $codigoSeguimiento,
                        $codigoVerificacion
                    );
                    
                    return [
                        'error' => false,
                        'mensaje' => 'Reserva creada exitosamente',
                        'codigo' => $codigoSeguimiento,
                        'email' => $_POST['email_paciente']
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
     */
    private function enviarEmailConfirmacion($email, $nombrePaciente, $fecha, $horario, $codigoSeguimiento, $codigoVerificacion) {
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
                        <li><b>Horario:</b> $horario</li>
                    </ul>
                    <p>Para confirmar su cita, utilice el siguiente código de verificación:</p>
                    <div class='code'>$codigoVerificacion</div>
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
