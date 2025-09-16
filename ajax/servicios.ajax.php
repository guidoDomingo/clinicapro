<?php
/**
 * Archivo para procesar peticiones AJAX relacionadas con servicios médicos
 */

// Aseguramos que todas las rutas sean relativas al directorio raíz
$rutaBase = dirname(__FILE__, 2); // Obtiene la ruta del directorio raíz (dos niveles arriba)
require_once $rutaBase . "/controller/servicios.controller.php";
require_once $rutaBase . "/model/servicios.model.php";
require_once $rutaBase . "/model/conexion.php";

// Configurar cabeceras para JSON
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');
header('Expires: 0');
date_default_timezone_set('America/Caracas');

// Iniciar sesión si no está iniciada
if (!isset($_SESSION)) {
    session_start();
}

// Procesar la acción solicitada
if (isset($_POST['action'])) {
    $action = $_POST['action'];
    
    switch ($action) {
        case 'obtenerCategorias':
            $categorias = ControladorServicios::ctrObtenerCategorias();
            echo json_encode([
                "status" => "success",
                "data" => $categorias
            ]);
            break;
            
        case 'obtenerServicios':
            $categoriaId = isset($_POST['categoria_id']) ? $_POST['categoria_id'] : null;
            $servicios = ControladorServicios::ctrObtenerServicios($categoriaId);
            echo json_encode([
                "status" => "success",
                "data" => $servicios
            ]);
            break;
            
        case 'obtenerTodosLosServicios':
            // Nuevo endpoint para obtener todos los servicios activos
            try {
                $servicios = ControladorServicios::ctrObtenerTodosLosServiciosActivos();
                echo json_encode([
                    "success" => true,
                    "status" => "success",
                    "data" => $servicios,
                    "total" => count($servicios),
                    "message" => "Servicios obtenidos exitosamente"
                ]);
            } catch (Exception $e) {
                echo json_encode([
                    "success" => false,
                    "status" => "error",
                    "message" => "Error al obtener servicios: " . $e->getMessage(),
                    "data" => []
                ]);
            }
            break;
            
        case 'obtenerMedicosPorServicio':
            // Nuevo endpoint para obtener médicos que ofrecen un servicio específico
            if (isset($_POST['servicio_id'])) {
                try {
                    $servicioId = $_POST['servicio_id'];
                    $medicos = ControladorServicios::ctrObtenerMedicosPorServicio($servicioId);
                    echo json_encode([
                        "success" => true,
                        "status" => "success",
                        "data" => $medicos,
                        "total" => count($medicos),
                        "message" => "Médicos obtenidos exitosamente"
                    ]);
                } catch (Exception $e) {
                    echo json_encode([
                        "success" => false,
                        "status" => "error",
                        "message" => "Error al obtener médicos: " . $e->getMessage(),
                        "data" => []
                    ]);
                }
            } else {
                echo json_encode([
                    "success" => false,
                    "status" => "error",
                    "message" => "ID de servicio no proporcionado",
                    "data" => []
                ]);
            }
            break;
            
        case 'obtenerCuposPorServicio':
            // Endpoint para obtener cupos disponibles específicos para un servicio
            if (isset($_POST['servicio_id'])) {
                try {
                    $servicioId = $_POST['servicio_id'];
                    $fecha = isset($_POST['fecha']) ? $_POST['fecha'] : date('Y-m-d');
                    $medicoId = isset($_POST['medico_id']) ? $_POST['medico_id'] : null;
                    $modoSemana = isset($_POST['modo_semana']) && $_POST['modo_semana'] == '1';
                    
                    error_log("Obteniendo cupos para servicio {$servicioId}, fecha {$fecha}, médico {$medicoId}, modo semana: " . ($modoSemana ? 'SI' : 'NO'), 3, "/var/log/clinica/servicios.log");
                    
                    $pdo = Conexion::conectar();
                    
                    if ($modoSemana) {
                        // MODO SEMANA: Obtener cupos de todos los días que atiende el médico
                        $sqlCondicionFecha = "1=1"; // Sin filtro de día específico
                        $parametroFecha = "";
                        
                        if ($medicoId) {
                            $sqlHorarios = "
                                SELECT DISTINCT
                                    ad.hora_inicio,
                                    ad.hora_fin,
                                    ad.intervalo_minutos,
                                    ad.cupo_maximo,
                                    ad.dia_semana,
                                    ac.medico_id,
                                    rh.doctor_id,
                                    p.first_name || ' ' || p.last_name as doctor_nombre,
                                    CASE 
                                        WHEN EXTRACT(HOUR FROM ad.hora_inicio::time) BETWEEN 6 AND 11 THEN 'Mañana'
                                        WHEN EXTRACT(HOUR FROM ad.hora_inicio::time) BETWEEN 12 AND 17 THEN 'Tarde'
                                        ELSE 'Noche'
                                    END as turno
                                FROM agendas_detalle ad
                                INNER JOIN agendas_cabecera ac ON ad.agenda_id = ac.agenda_id
                                INNER JOIN rs_servicios_doctors rsd ON rsd.agenda_detalle_id = ad.detalle_id 
                                                                    AND ac.medico_id = rsd.doctor_id
                                INNER JOIN rh_doctors rh ON rsd.doctor_id = rh.doctor_id
                                INNER JOIN rh_person p ON rh.person_id = p.person_id
                                WHERE ac.agenda_estado = true 
                                AND ad.detalle_estado = true
                                AND rsd.servicio_id = :servicio_id
                                AND rsd.doctor_id = :medico_id
                                AND rsd.is_active = true
                                AND rh.doctor_estado = 'ACTIVO'
                                AND p.is_active = true
                                ORDER BY ad.hora_inicio, ac.medico_id
                            ";
                        } else {
                            // Sin médico específico, obtener todos los médicos del servicio
                            $sqlHorarios = "
                                SELECT DISTINCT
                                    ad.hora_inicio,
                                    ad.hora_fin,
                                    ad.intervalo_minutos,
                                    ad.cupo_maximo,
                                    ad.dia_semana,
                                    ac.medico_id,
                                    rh.doctor_id,
                                    p.first_name || ' ' || p.last_name as doctor_nombre,
                                    CASE 
                                        WHEN EXTRACT(HOUR FROM ad.hora_inicio::time) BETWEEN 6 AND 11 THEN 'Mañana'
                                        WHEN EXTRACT(HOUR FROM ad.hora_inicio::time) BETWEEN 12 AND 17 THEN 'Tarde'
                                        ELSE 'Noche'
                                    END as turno
                                FROM agendas_detalle ad
                                INNER JOIN agendas_cabecera ac ON ad.agenda_id = ac.agenda_id
                                INNER JOIN rs_servicios_doctors rsd ON rsd.agenda_detalle_id = ad.detalle_id 
                                                                    AND ac.medico_id = rsd.doctor_id
                                INNER JOIN rh_doctors rh ON rsd.doctor_id = rh.doctor_id
                                INNER JOIN rh_person p ON rh.person_id = p.person_id
                                WHERE ac.agenda_estado = true 
                                AND ad.detalle_estado = true
                                AND rsd.servicio_id = :servicio_id
                                AND rsd.is_active = true
                                AND rh.doctor_estado = 'ACTIVO'
                                AND p.is_active = true
                                ORDER BY ad.hora_inicio, ac.medico_id
                            ";
                        }
                        
                        // Obtener reservas de toda la semana actual
                        $fechaInicioSemana = date('Y-m-d', strtotime('monday this week', strtotime($fecha)));
                        $fechaFinSemana = date('Y-m-d', strtotime('sunday this week', strtotime($fecha)));
                        
                        $sqlReservas = "
                            SELECT 
                                sr.hora_inicio,
                                sr.hora_fin,
                                sr.doctor_id,
                                sr.reserva_estado,
                                sr.fecha_reserva
                            FROM servicios_reservas sr
                            INNER JOIN agendas_cabecera ac ON sr.agenda_id = ac.agenda_id
                            INNER JOIN rs_servicios_doctors rsd ON rsd.doctor_id = sr.doctor_id
                            WHERE sr.fecha_reserva::date BETWEEN :fecha_inicio::date AND :fecha_fin::date
                            AND rsd.servicio_id = :servicio_id
                            AND rsd.is_active = true
                            AND sr.reserva_estado IN ('CONFIRMADA', 'EN_PROCESO', 'PENDIENTE')
                        ";
                        
                        if ($medicoId) {
                            $sqlReservas .= " AND sr.doctor_id = :medico_id";
                        }
                        
                    } else {
                        // MODO FECHA ESPECÍFICA: Lógica original
                        $diasSemana = [
                            1 => 'LUNES', 2 => 'MARTES', 3 => 'MIERCOLES',
                            4 => 'JUEVES', 5 => 'VIERNES', 6 => 'SABADO', 0 => 'DOMINGO'
                        ];
                        
                        $fechaObj = new DateTime($fecha);
                        $numeroDia = (int)$fechaObj->format('w');
                        $diaSemana = $diasSemana[$numeroDia];
                        
                        if ($medicoId) {
                            $sqlHorarios = "
                                SELECT DISTINCT
                                    ad.hora_inicio,
                                    ad.hora_fin,
                                    ad.intervalo_minutos,
                                    ad.cupo_maximo,
                                    ac.medico_id,
                                    rh.doctor_id,
                                    p.first_name || ' ' || p.last_name as doctor_nombre,
                                    CASE 
                                        WHEN EXTRACT(HOUR FROM ad.hora_inicio::time) BETWEEN 6 AND 11 THEN 'Mañana'
                                        WHEN EXTRACT(HOUR FROM ad.hora_inicio::time) BETWEEN 12 AND 17 THEN 'Tarde'
                                        ELSE 'Noche'
                                    END as turno
                                FROM agendas_detalle ad
                                INNER JOIN agendas_cabecera ac ON ad.agenda_id = ac.agenda_id
                                INNER JOIN rs_servicios_doctors rsd ON rsd.agenda_detalle_id = ad.detalle_id 
                                                                    AND ac.medico_id = rsd.doctor_id
                                INNER JOIN rh_doctors rh ON rsd.doctor_id = rh.doctor_id
                                INNER JOIN rh_person p ON rh.person_id = p.person_id
                                WHERE ac.agenda_estado = true 
                                AND ad.detalle_estado = true
                                AND ad.dia_semana = :dia_semana
                                AND rsd.servicio_id = :servicio_id
                                AND rsd.doctor_id = :medico_id
                                AND rsd.is_active = true
                                AND rh.doctor_estado = 'ACTIVO'
                                AND p.is_active = true
                                ORDER BY ad.hora_inicio, ac.medico_id
                            ";
                        } else {
                            $sqlHorarios = "
                                SELECT DISTINCT
                                    ad.hora_inicio,
                                    ad.hora_fin,
                                    ad.intervalo_minutos,
                                    ad.cupo_maximo,
                                    ac.medico_id,
                                    rh.doctor_id,
                                    p.first_name || ' ' || p.last_name as doctor_nombre,
                                    CASE 
                                        WHEN EXTRACT(HOUR FROM ad.hora_inicio::time) BETWEEN 6 AND 11 THEN 'Mañana'
                                        WHEN EXTRACT(HOUR FROM ad.hora_inicio::time) BETWEEN 12 AND 17 THEN 'Tarde'
                                        ELSE 'Noche'
                                    END as turno
                                FROM agendas_detalle ad
                                INNER JOIN agendas_cabecera ac ON ad.agenda_id = ac.agenda_id
                                INNER JOIN rs_servicios_doctors rsd ON rsd.agenda_detalle_id = ad.detalle_id 
                                                                    AND ac.medico_id = rsd.doctor_id
                                INNER JOIN rh_doctors rh ON rsd.doctor_id = rh.doctor_id
                                INNER JOIN rh_person p ON rh.person_id = p.person_id
                                WHERE ac.agenda_estado = true 
                                AND ad.detalle_estado = true
                                AND ad.dia_semana = :dia_semana
                                AND rsd.servicio_id = :servicio_id
                                AND rsd.is_active = true
                                AND rh.doctor_estado = 'ACTIVO'
                                AND p.is_active = true
                                ORDER BY ad.hora_inicio, ac.medico_id
                            ";
                        }
                        
                        $sqlReservas = "
                            SELECT 
                                sr.hora_inicio,
                                sr.hora_fin,
                                sr.doctor_id,
                                sr.reserva_estado
                            FROM servicios_reservas sr
                            INNER JOIN agendas_cabecera ac ON sr.agenda_id = ac.agenda_id
                            INNER JOIN rs_servicios_doctors rsd ON rsd.doctor_id = sr.doctor_id
                            WHERE sr.fecha_reserva::date = :fecha::date
                            AND rsd.servicio_id = :servicio_id
                            AND rsd.is_active = true
                            AND sr.reserva_estado IN ('CONFIRMADA', 'EN_PROCESO', 'PENDIENTE')
                        ";
                        
                        if ($medicoId) {
                            $sqlReservas .= " AND sr.doctor_id = :medico_id";
                        }
                    }
                    
                    // Ejecutar consulta de horarios
                    $stmtHorarios = $pdo->prepare($sqlHorarios);
                    $stmtHorarios->bindParam(":servicio_id", $servicioId, PDO::PARAM_INT);
                    
                    if ($modoSemana) {
                        if ($medicoId) {
                            $stmtHorarios->bindParam(":medico_id", $medicoId, PDO::PARAM_INT);
                        }
                    } else {
                        $stmtHorarios->bindParam(":dia_semana", $diaSemana, PDO::PARAM_STR);
                        if ($medicoId) {
                            $stmtHorarios->bindParam(":medico_id", $medicoId, PDO::PARAM_INT);
                        }
                    }
                    
                    $stmtHorarios->execute();
                    $horariosDisponibles = $stmtHorarios->fetchAll(PDO::FETCH_ASSOC);
                    
                    // Ejecutar consulta de reservas
                    $stmtReservas = $pdo->prepare($sqlReservas);
                    $stmtReservas->bindParam(":servicio_id", $servicioId, PDO::PARAM_INT);
                    
                    if ($modoSemana) {
                        $stmtReservas->bindParam(":fecha_inicio", $fechaInicioSemana, PDO::PARAM_STR);
                        $stmtReservas->bindParam(":fecha_fin", $fechaFinSemana, PDO::PARAM_STR);
                        if ($medicoId) {
                            $stmtReservas->bindParam(":medico_id", $medicoId, PDO::PARAM_INT);
                        }
                    } else {
                        $stmtReservas->bindParam(":fecha", $fecha, PDO::PARAM_STR);
                        if ($medicoId) {
                            $stmtReservas->bindParam(":medico_id", $medicoId, PDO::PARAM_INT);
                        }
                    }
                    
                    $stmtReservas->execute();
                    $reservasExistentes = $stmtReservas->fetchAll(PDO::FETCH_ASSOC);
                    
                    // Calcular cupos por turno
                    $cuposPorTurno = ['Mañana' => 0, 'Tarde' => 0, 'Noche' => 0];
                    $reservasPorTurno = ['Mañana' => 0, 'Tarde' => 0, 'Noche' => 0];
                    $totalCupos = 0;
                    $totalReservas = 0;
                    
                    // Contar cupos totales disponibles por turno
                    foreach ($horariosDisponibles as $horario) {
                        $horaInicio = new DateTime($horario['hora_inicio']);
                        $horaFin = new DateTime($horario['hora_fin']);
                        $intervaloMinutos = (int)$horario['intervalo_minutos'];
                        $cupoMaximo = (int)$horario['cupo_maximo'];
                        $turno = $horario['turno'];
                        
                        if ($intervaloMinutos > 0) {
                            $horaActual = clone $horaInicio;
                            while ($horaActual < $horaFin) {
                                // Verificar que el slot completo quepa dentro del horario
                                $horaFinSlot = clone $horaActual;
                                $horaFinSlot->add(new DateInterval('PT' . $intervaloMinutos . 'M'));
                                
                                if ($horaFinSlot <= $horaFin) {
                                    $cuposPorTurno[$turno] += $cupoMaximo;
                                    $totalCupos += $cupoMaximo;
                                } else {
                                    // El slot no cabe completo, salir del bucle
                                    break;
                                }
                                
                                $horaActual->add(new DateInterval('PT' . $intervaloMinutos . 'M'));
                            }
                        }
                    }
                    
                    // Contar reservas por turno
                    foreach ($reservasExistentes as $reserva) {
                        $horaInicio = new DateTime($reserva['hora_inicio']);
                        $hora = (int)$horaInicio->format('H');
                        
                        if ($hora >= 6 && $hora <= 11) {
                            $reservasPorTurno['Mañana']++;
                        } elseif ($hora >= 12 && $hora <= 17) {
                            $reservasPorTurno['Tarde']++;
                        } else {
                            $reservasPorTurno['Noche']++;
                        }
                        $totalReservas++;
                    }
                    
                    // Calcular cupos disponibles restando las reservas
                    $cuposDisponibles = [
                        'Mañana' => max(0, $cuposPorTurno['Mañana'] - $reservasPorTurno['Mañana']),
                        'Tarde' => max(0, $cuposPorTurno['Tarde'] - $reservasPorTurno['Tarde']),
                        'Noche' => max(0, $cuposPorTurno['Noche'] - $reservasPorTurno['Noche'])
                    ];
                    
                    // El total debe ser la suma de los cupos disponibles por turno
                    $cuposDisponibles['Total'] = $cuposDisponibles['Mañana'] + $cuposDisponibles['Tarde'] + $cuposDisponibles['Noche'];
                    
                    // Calcular cupos reservados para mostrar información completa
                    $cuposReservados = [
                        'Mañana' => $reservasPorTurno['Mañana'],
                        'Tarde' => $reservasPorTurno['Tarde'], 
                        'Noche' => $reservasPorTurno['Noche'],
                        'Total' => $totalReservas
                    ];
                    
                    error_log("Cupos calculados para servicio {$servicioId}: " . json_encode($cuposDisponibles), 3, "/var/log/clinica/servicios.log");
                    
                    echo json_encode([
                        "success" => true,
                        "status" => "success",
                        "data" => $cuposDisponibles,
                        "reservados" => $cuposReservados,
                        "totales" => $cuposPorTurno,
                        "message" => "Cupos obtenidos exitosamente"
                    ]);
                    
                } catch (Exception $e) {
                    error_log("Error al obtener cupos por servicio: " . $e->getMessage(), 3, "/var/log/clinica/servicios.log");
                    echo json_encode([
                        "success" => false,
                        "status" => "error",
                        "message" => "Error al obtener cupos: " . $e->getMessage(),
                        "data" => ['Mañana' => 0, 'Tarde' => 0, 'Noche' => 0, 'Total' => 0]
                    ]);
                }
            } else {
                echo json_encode([
                    "success" => false,
                    "status" => "error",
                    "message" => "ID de servicio no proporcionado",
                    "data" => ['Mañana' => 0, 'Tarde' => 0, 'Noche' => 0, 'Total' => 0]
                ]);
            }
            break;
            
        case 'obtenerServicioPorId':
            if (isset($_POST['servicio_id'])) {
                $servicioId = $_POST['servicio_id'];
                $datos = ControladorServicios::ctrObtenerServicioPorId($servicioId);
                echo json_encode([
                    "status" => "success",
                    "data" => $datos
                ]);
            } else {
                echo json_encode([
                    "status" => "error",
                    "message" => "ID de servicio no proporcionado"
                ]);
            }
            break;
            
        case 'obtenerHorariosMedico':
            if (isset($_POST['doctor_id'])) {
                $doctorId = $_POST['doctor_id'];
                $horarios = ControladorServicios::ctrObtenerHorariosMedico($doctorId);
                echo json_encode([
                    "status" => "success",
                    "data" => $horarios
                ]);
            } else {
                echo json_encode([
                    "status" => "error",
                    "message" => "ID de doctor no proporcionado"
                ]);
            }
            break;
            
        case 'generarSlotsDisponibles':
            if (isset($_POST['servicio_id']) && isset($_POST['doctor_id']) && isset($_POST['fecha'])) {
                $servicioId = $_POST['servicio_id'];
                $doctorId = $_POST['doctor_id'];
                $fecha = $_POST['fecha'];

               //agregar un log para verificar los datos recibidos
                error_log("AJAX generarSlotsDisponibles: ServicioID=$servicioId, DoctorID=$doctorId, Fecha=$fecha", 3, '/var/log/clinica/slots.log');
                
                $slots = ControladorServicios::ctrGenerarSlotsDisponibles($servicioId, $doctorId, $fecha);
                echo json_encode([
                    "status" => "success",
                    "data" => $slots
                ]);
            } else {
                echo json_encode([
                    "status" => "error",
                    "message" => "Faltan parámetros requeridos"
                ]);
            }
            break;
            
        case 'obtenerMedicosPorFecha':
            if (isset($_POST['fecha'])) {
                $fecha = $_POST['fecha'];
                $medicos = ControladorServicios::ctrObtenerMedicosDisponiblesPorFecha($fecha);
                echo json_encode([
                    "status" => "success",
                    "data" => $medicos
                ]);
            } else {
                echo json_encode([
                    "status" => "error",
                    "message" => "Fecha no proporcionada"
                ]);
            }
            break;
            
        case 'obtenerCuposDisponiblesPorTurno':
            error_log("AJAX obtenerCuposDisponiblesPorTurno llamado", 3, '/var/log/clinica/database.log');
            if (isset($_POST['fecha'])) {
                $fecha = $_POST['fecha'];
                error_log("AJAX procesando fecha: {$fecha}", 3, '/var/log/clinica/database.log');
                
                try {
                    $pdo = Conexion::conectar();
                    if ($pdo) {
                        // Obtener el día de la semana en español para la fecha
                        $diasSemana = [
                            1 => 'LUNES',
                            2 => 'MARTES', 
                            3 => 'MIERCOLES',
                            4 => 'JUEVES',
                            5 => 'VIERNES',
                            6 => 'SABADO',
                            0 => 'DOMINGO'
                        ];
                        
                        $fechaObj = new DateTime($fecha);
                        $numeroDia = (int)$fechaObj->format('w'); // 0=domingo, 1=lunes, etc
                        $diaSemana = $diasSemana[$numeroDia];
                        
                        error_log("Calculando cupos para fecha {$fecha}, día: {$diaSemana}", 3, '/var/log/clinica/database.log');
                        
                        // Calcular cupos disponibles por turno basado en intervalos de tiempo específicos
                        $sqlHorarios = "SELECT DISTINCT
                                            ad.hora_inicio,
                                            ad.hora_fin,
                                            ad.intervalo_minutos,
                                            ac.medico_id,
                                            CASE 
                                                WHEN EXTRACT(HOUR FROM ad.hora_inicio::time) BETWEEN 6 AND 11 THEN 'Mañana'
                                                WHEN EXTRACT(HOUR FROM ad.hora_inicio::time) BETWEEN 12 AND 19 THEN 'Tarde'
                                                ELSE 'Noche'
                                            END as turno
                                        FROM agendas_detalle ad
                                        INNER JOIN agendas_cabecera ac ON ad.agenda_id = ac.agenda_id
                                        WHERE ac.agenda_estado = true 
                                        AND ad.dia_semana = :dia_semana
                                        AND ad.dia_semana IS NOT NULL
                                        AND ad.hora_inicio IS NOT NULL 
                                        AND ad.hora_fin IS NOT NULL
                                        AND ad.intervalo_minutos > 0
                                        ORDER BY ad.hora_inicio, ac.medico_id";
                        
                        $stmtHorarios = $pdo->prepare($sqlHorarios);
                        $stmtHorarios->bindParam(":dia_semana", $diaSemana, PDO::PARAM_STR);
                        $stmtHorarios->execute();
                        $horariosDisponibles = $stmtHorarios->fetchAll(PDO::FETCH_ASSOC);
                        
                        error_log("Horarios encontrados para {$fecha} ({$diaSemana}): " . json_encode($horariosDisponibles), 3, '/var/log/clinica/database.log');
                        
                        // Generar todos los intervalos de tiempo disponibles agrupados por turno
                        $intervalosDisponibles = ['Mañana' => [], 'Tarde' => [], 'Noche' => []];
                        
                        foreach ($horariosDisponibles as $horario) {
                            $horaInicio = new DateTime($horario['hora_inicio']);
                            $horaFin = new DateTime($horario['hora_fin']);
                            $intervaloMinutos = (int)$horario['intervalo_minutos'];
                            $medicoId = $horario['medico_id'];
                            $turno = $horario['turno'];
                            
                            if ($intervaloMinutos > 0) {
                                // Generar todos los intervalos específicos para este médico y horario
                                $horaActual = clone $horaInicio;
                                while ($horaActual < $horaFin) {
                                    $horaFinalIntervalo = clone $horaActual;
                                    $horaFinalIntervalo->add(new DateInterval('PT' . $intervaloMinutos . 'M'));
                                    
                                    if ($horaFinalIntervalo <= $horaFin) {
                                        $claveIntervalo = $horaActual->format('H:i') . '-' . $horaFinalIntervalo->format('H:i') . '_medico_' . $medicoId;
                                        $intervalosDisponibles[$turno][] = $claveIntervalo;
                                        
                                        error_log("Intervalo generado: {$claveIntervalo} en turno {$turno}", 3, '/var/log/clinica/database.log');
                                    }
                                    
                                    $horaActual->add(new DateInterval('PT' . $intervaloMinutos . 'M'));
                                }
                            }
                        }
                        
                        // Contar cupos totales por turno
                        $cuposTotales = [
                            'Mañana' => count($intervalosDisponibles['Mañana']),
                            'Tarde' => count($intervalosDisponibles['Tarde']),
                            'Noche' => count($intervalosDisponibles['Noche'])
                        ];
                        
                        error_log("Cupos totales calculados: " . json_encode($cuposTotales), 3, '/var/log/clinica/database.log');
                        
                        // Verificar si existe la tabla servicios_reservas y contar reservas ocupadas
                        $stmtCheck = $pdo->prepare("SELECT to_regclass('public.servicios_reservas')");
                        $stmtCheck->execute();
                        $tablaExiste = $stmtCheck->fetchColumn();
                        
                        if ($tablaExiste) {
                            // Contar reservas específicas por turno, médico y horario para la fecha específica
                            $sql = "SELECT 
                                        sr.doctor_id,
                                        sr.hora_inicio,
                                        sr.hora_fin,
                                        CASE 
                                            WHEN EXTRACT(HOUR FROM sr.hora_inicio::time) BETWEEN 6 AND 11 THEN 'Mañana'
                                            WHEN EXTRACT(HOUR FROM sr.hora_inicio::time) BETWEEN 12 AND 19 THEN 'Tarde'
                                            ELSE 'Noche'
                                        END as turno
                                    FROM servicios_reservas sr
                                    WHERE DATE(sr.fecha_reserva) = :fecha 
                                    AND sr.reserva_estado IN ('CONFIRMADA', 'PENDIENTE', 'EN_PROCESO')";
                            
                            $stmtReservas = $pdo->prepare($sql);
                            $stmtReservas->bindParam(":fecha", $fecha, PDO::PARAM_STR);
                            $stmtReservas->execute();
                            $reservasOcupadas = $stmtReservas->fetchAll(PDO::FETCH_ASSOC);
                            
                            error_log("Reservas encontradas para {$fecha}: " . json_encode($reservasOcupadas), 3, '/var/log/clinica/database.log');
                            
                            // Marcar intervalos ocupados específicos
                            $intervalosOcupados = ['Mañana' => [], 'Tarde' => [], 'Noche' => []];
                            foreach ($reservasOcupadas as $reserva) {
                                $horaInicio = new DateTime($reserva['hora_inicio']);
                                $horaFin = new DateTime($reserva['hora_fin']);
                                $doctorId = $reserva['doctor_id'];
                                $turno = $reserva['turno'];
                                
                                $claveReserva = $horaInicio->format('H:i') . '-' . $horaFin->format('H:i') . '_medico_' . $doctorId;
                                $intervalosOcupados[$turno][] = $claveReserva;
                                
                                error_log("Intervalo ocupado: {$claveReserva} en turno {$turno}", 3, '/var/log/clinica/database.log');
                            }
                            
                            // Calcular cupos disponibles restando intervalos ocupados específicos
                            $cupos = [];
                            foreach ($cuposTotales as $turno => $total) {
                                $ocupadas = count($intervalosOcupados[$turno]);
                                $cupos[$turno] = max(0, $total - $ocupadas); // No puede ser negativo
                                error_log("Turno {$turno}: Total={$total}, Ocupadas={$ocupadas}, Disponibles={$cupos[$turno]}", 3, '/var/log/clinica/database.log');
                            }
                        } else {
                            error_log("Tabla servicios_reservas no existe, usando valores totales calculados", 3, '/var/log/clinica/database.log');
                            $cupos = $cuposTotales;
                        }
                    } else {
                        error_log("No se pudo conectar a la BD", 3, '/var/log/clinica/database.log');
                        $cupos = ['Mañana' => 0, 'Tarde' => 0, 'Noche' => 0];
                    }
                } catch (Exception $e) {
                    error_log("Error al calcular cupos disponibles: " . $e->getMessage(), 3, '/var/log/clinica/database.log');
                    // En caso de error, devolver ceros
                    $cupos = ['Mañana' => 0, 'Tarde' => 0, 'Noche' => 0];
                }
                
                error_log("AJAX retornando cupos disponibles: " . json_encode($cupos), 3, '/var/log/clinica/database.log');
                
                echo json_encode([
                    "status" => "success",
                    "data" => $cupos
                ]);
            } else {
                echo json_encode([
                    "status" => "error",
                    "message" => "Fecha no proporcionada"
                ]);
            }
            break;
            
        case 'obtenerServiciosPorFechaMedico':
            if (isset($_POST['fecha']) && isset($_POST['doctor_id'])) {
                $fecha = $_POST['fecha'];
                $doctorId = $_POST['doctor_id'];
                $servicios = ControladorServicios::ctrObtenerServiciosPorFechaMedico($fecha, $doctorId);
                echo json_encode([
                    "status" => "success",
                    "data" => $servicios
                ]);
            } else {
                echo json_encode([
                    "status" => "error",
                    "message" => "Faltan parámetros requeridos"
                ]);
            }
            break;
            
        case 'obtenerReservas':
            $fecha = isset($_POST['fecha']) ? $_POST['fecha'] : date('Y-m-d');
            $doctorId = isset($_POST['doctor_id']) ? intval($_POST['doctor_id']) : null;
            $estado = isset($_POST['estado']) ? $_POST['estado'] : null;
            
            error_log("AJAX obtenerReservas: Fecha=$fecha, DoctorID=" . ($doctorId ?? "null") . ", Estado=" . ($estado ?? "null"), 3, '/var/log/clinica/reservas.log');
            
            try {
                $reservas = ControladorServicios::ctrObtenerReservasPorFecha($fecha, $doctorId, $estado);
                
                error_log("AJAX obtenerReservas: Se encontraron " . count($reservas) . " reservas", 3, '/var/log/clinica/reservas.log');
                if (count($reservas) > 0) {
                    error_log("AJAX obtenerReservas: Primera reserva: " . json_encode($reservas[0]), 3, '/var/log/clinica/reservas.log');
                } else {
                    error_log("AJAX obtenerReservas: No se encontraron reservas para esta fecha", 3, '/var/log/clinica/reservas.log');
                }
                
                echo json_encode([
                    "status" => "success",
                    "data" => $reservas
                ]);
            } catch (Exception $e) {
                error_log("AJAX obtenerReservas ERROR: " . $e->getMessage(), 3, '/var/log/clinica/reservas.log');
                echo json_encode([
                    "status" => "error",
                    "message" => "Error al obtener reservas: " . $e->getMessage(),
                    "data" => []
                ]);
            }
            break;
            
        case 'crearReserva':
            // Recopilar datos desde la petición POST
            $datos = [
                'servicio_id' => $_POST['servicio_id'] ?? null,
                'doctor_id' => $_POST['doctor_id'] ?? null,
                'paciente_id' => $_POST['persona_id'] ?? null,
                'fecha_reserva' => $_POST['fecha_reserva'] ?? null,
                'hora_inicio' => $_POST['hora_inicio'] ?? null,
                'hora_fin' => $_POST['hora_fin'] ?? null
            ];
            
            // Datos opcionales
            if (isset($_POST['agenda_id'])) $datos['agenda_id'] = $_POST['agenda_id'];
            if (isset($_POST['observaciones'])) $datos['observaciones'] = $_POST['observaciones'];
            if (isset($_POST['sala_id'])) $datos['sala_id'] = $_POST['sala_id'];
            if (isset($_POST['tarifa_id'])) $datos['tarifa_id'] = $_POST['tarifa_id'];
            if (isset($_POST['precio_final'])) $datos['precio_final'] = $_POST['precio_final'];
            
            // Datos de usuario
            if (isset($_SESSION['user_id'])) {
                $datos['created_by'] = $_SESSION['user_id'];
            }
            if (isset($_SESSION['business_id'])) {
                $datos['business_id'] = $_SESSION['business_id'];
            }
            
            $resultado = ControladorServicios::ctrCrearReserva($datos);
            echo json_encode($resultado);
            break;
              case 'cambiarEstadoReserva':
            if (isset($_POST['reserva_id']) && isset($_POST['nuevo_estado'])) {
                $reservaId = $_POST['reserva_id'];
                $estado = $_POST['nuevo_estado'];
                
                $resultado = ControladorServicios::ctrCambiarEstadoReserva($reservaId, $estado);
                
                // Adaptar la respuesta al formato esperado por el cliente
                echo json_encode([
                    "status" => $resultado["error"] ? "error" : "success",
                    "mensaje" => $resultado["mensaje"]
                ]);
            } else {
                echo json_encode([
                    "status" => "error",
                    "mensaje" => "Faltan parámetros requeridos"
                ]);
            }
            break;
            
        case 'buscarPaciente':
            if (isset($_POST['termino'])) {
                $termino = $_POST['termino'];
                $pacientes = ControladorServicios::ctrBuscarPaciente($termino);
                
                // Debug info
                error_log("Búsqueda de paciente: " . $termino . " - Resultados: " . count($pacientes));
                
                echo json_encode([
                    "status" => "success",
                    "data" => $pacientes
                ]);
            } else {
                echo json_encode([
                    "status" => "error",
                    "message" => "Término de búsqueda no proporcionado"
                ]);
            }
            break;
            
        case 'buscarPacientePorId':
            if (isset($_POST['paciente_id'])) {
                $pacienteId = $_POST['paciente_id'];
                $paciente = ControladorServicios::ctrBuscarPacientePorId($pacienteId);
                
                // Debug info
                error_log("Búsqueda de paciente por ID: " . $pacienteId . " - Resultados: " . count($paciente));
                
                echo json_encode([
                    "status" => "success",
                    "data" => $paciente
                ]);
            } else {
                echo json_encode([
                    "status" => "error",
                    "message" => "ID de paciente no proporcionado"
                ]);
            }
            break;
            
        case 'guardarReserva':
            if (isset($_POST['doctor_id']) && isset($_POST['servicio_id']) && isset($_POST['paciente_id']) && 
                isset($_POST['fecha_reserva']) && isset($_POST['hora_inicio']) && isset($_POST['hora_fin'])) {
                
                // Capturar datos de la reserva
                $datos = [
                    'doctor_id' => intval($_POST['doctor_id']),
                    'servicio_id' => intval($_POST['servicio_id']),
                    'paciente_id' => intval($_POST['paciente_id']),
                    'fecha_reserva' => $_POST['fecha_reserva'],
                    'hora_inicio' => $_POST['hora_inicio'],
                    'hora_fin' => $_POST['hora_fin'],
                    'seguro_id' => isset($_POST['seguro_id']) && !empty($_POST['seguro_id']) && $_POST['seguro_id'] != '0' ? intval($_POST['seguro_id']) : null,
                    'observaciones' => isset($_POST['observaciones']) ? $_POST['observaciones'] : ''
                ];
                
                // Agregar campos opcionales
                if (isset($_POST['agenda_id']) && !empty($_POST['agenda_id'])) {
                    $datos['agenda_id'] = intval($_POST['agenda_id']);
                }
                
                if (isset($_POST['tarifa_id']) && !empty($_POST['tarifa_id'])) {
                    $datos['tarifa_id'] = intval($_POST['tarifa_id']);
                }
                
                if (isset($_POST['sala_id']) && !empty($_POST['sala_id'])) {
                    $datos['sala_id'] = intval($_POST['sala_id']);
                }
                
                // Debug - verificar el seguro_id recibido
                error_log("AJAX guardarReserva: seguro_id POST = " . (isset($_POST['seguro_id']) ? $_POST['seguro_id'] : 'NO_SET'), 3, '/var/log/clinica/reservas.log');
                
                // Registrar intento de guardar reserva
                error_log("AJAX guardarReserva: Datos recibidos = " . json_encode($datos), 3, '/var/log/clinica/reservas.log');
                
                try {
                    // Guardar la reserva
                    $resultado = ControladorServicios::ctrGuardarReserva($datos);
                    
                    if ($resultado) {
                        echo json_encode([
                            "status" => "success",
                            "message" => "Reserva guardada exitosamente",
                            "reserva_id" => $resultado
                        ]);
                        error_log("AJAX guardarReserva: Reserva creada con ID " . $resultado, 3, '/var/log/clinica/reservas.log');
                    } else {
                        echo json_encode([
                            "status" => "error",
                            "message" => "No se pudo guardar la reserva. Verifique que no haya conflictos de horarios."
                        ]);
                        error_log("AJAX guardarReserva: No se pudo guardar la reserva (resultado=false)", 3, '/var/log/clinica/reservas.log');
                    }
                } catch (Exception $e) {
                    error_log("AJAX guardarReserva: Excepción - " . $e->getMessage(), 3, '/var/log/clinica/reservas.log');
                    echo json_encode([
                        "status" => "error",
                        "message" => "Error al guardar la reserva: " . $e->getMessage()
                    ]);
                }
            } else {
                $camposFaltantes = [];
                $camposRequeridos = ['doctor_id', 'servicio_id', 'paciente_id', 'fecha_reserva', 'hora_inicio', 'hora_fin'];
                
                foreach ($camposRequeridos as $campo) {
                    if (!isset($_POST[$campo]) || empty($_POST[$campo])) {
                        $camposFaltantes[] = $campo;
                    }
                }
                
                $mensaje = "Faltan datos requeridos para guardar la reserva: " . implode(", ", $camposFaltantes);
                error_log("AJAX guardarReserva: " . $mensaje, 3, '/var/log/clinica/reservas.log');
                
                echo json_encode([
                    "status" => "error",
                    "message" => $mensaje,
                    "campos_faltantes" => $camposFaltantes
                ]);
            }
            break;
            
        case 'obtenerProveedoresSeguro':
            try {
                $proveedores = ControladorServicios::ctrObtenerProveedoresSeguro();
                echo json_encode([
                    "status" => "success",
                    "data" => $proveedores
                ]);
            } catch (Exception $e) {
                echo json_encode([
                    "status" => "error",
                    "message" => "Error al obtener proveedores de seguro: " . $e->getMessage()
                ]);
            }
            break;
              case 'buscarReservas':
            // Detectar si estamos en el módulo de citas
            $esCitas = isset($_POST['modulo']) && $_POST['modulo'] === 'citas';
            
            // Procesar todos los parámetros de filtro
            $fecha = isset($_POST['fecha']) && !empty($_POST['fecha']) ? $_POST['fecha'] : date('Y-m-d');
            
            // Asegurar que doctorId sea tratado correctamente como entero o null
            $doctorId = null;
            
            // Si estamos en el módulo de citas, forzar el filtro por doctor logueado
            if ($esCitas) {
                // Obtener el doctor logueado
                $doctorIdSesion = null;
                
                // Prioridad 1: doctor_id de sesión
                if (isset($_SESSION['doctor_id']) && $_SESSION['doctor_id']) {
                    $doctorIdSesion = $_SESSION['doctor_id'];
                } 
                // Prioridad 2: Buscar doctor_id usando user_id de sesión
                elseif (isset($_SESSION['user_id']) && $_SESSION['user_id']) {
                    try {
                        $userId = $_SESSION['user_id'];
                        $stmt = Conexion::conectar()->prepare("
                            SELECT d.doctor_id 
                            FROM rh_doctors d
                            INNER JOIN rh_person p ON d.person_id = p.person_id
                            INNER JOIN sys_users u ON u.user_email = p.email
                            WHERE u.user_id = :user_id
                            LIMIT 1
                        ");
                        $stmt->bindParam(":user_id", $userId, PDO::PARAM_INT);
                        $stmt->execute();
                        $result = $stmt->fetch(PDO::FETCH_ASSOC);
                        
                        if ($result && isset($result['doctor_id'])) {
                            $doctorIdSesion = $result['doctor_id'];
                        }
                    } catch (Exception $e) {
                        // Silenciar errores en producción
                    }
                }
                // Prioridad 3: usuario_id de sesión como fallback
                elseif (isset($_SESSION['usuario_id']) && $_SESSION['usuario_id']) {
                    $doctorIdSesion = $_SESSION['usuario_id'];
                }
                
                if ($doctorIdSesion) {
                    $doctorId = intval($doctorIdSesion);
                }
                
                // Forzar estado CONFIRMADA para el módulo de citas
                $estado = 'CONFIRMADA';
                // Comportamiento normal para servicios
                if (isset($_POST['doctor_id']) && $_POST['doctor_id'] !== '0' && $_POST['doctor_id'] !== '') {
                    $doctorId = intval($_POST['doctor_id']);
                }
            }
            
            // Procesar filtro de estado
            $estado = null;
            
            // Si estamos en el módulo de citas, forzar estado CONFIRMADA
            if ($esCitas) {
                $estado = 'CONFIRMADA';
            } else {
                // Comportamiento normal para servicios
                if (isset($_POST['estado']) && $_POST['estado'] !== '0' && $_POST['estado'] !== '') {
                    $estado = trim($_POST['estado']);
                }
            }
            
            // Procesar filtro de paciente
            $paciente = null;
            if (isset($_POST['paciente']) && !empty($_POST['paciente'])) {
                $paciente = trim($_POST['paciente']);
            }
            
            // Procesar filtro de sala
            $salaId = null;
            if (isset($_POST['sala_id']) && $_POST['sala_id'] !== '0' && $_POST['sala_id'] !== '') {
                $salaId = intval($_POST['sala_id']);
            }
            
            // Procesar filtro de origen
            $origen = null;
            if (isset($_POST['origen']) && $_POST['origen'] !== '0' && $_POST['origen'] !== '') {
                $origen = trim($_POST['origen']);
            }
            
            try {
                // Obtener reservas según los filtros
                $reservas = ControladorServicios::ctrBuscarReservas($fecha, $doctorId, $estado, $paciente, $salaId, $origen);
                
                // Enviar respuesta
                echo json_encode([
                    "status" => "success",
                    "data" => $reservas
                ]);
            } catch (Exception $e) {
                echo json_encode([
                    "status" => "error",
                    "mensaje" => "Error al buscar reservas: " . $e->getMessage()
                ]);
            }
            break;
            
        case 'obtenerMedicos':
            try {
                $medicos = ControladorServicios::ctrObtenerMedicos();
                
                echo json_encode([
                    "status" => "success",
                    "data" => $medicos
                ]);
            } catch (Exception $e) {
                error_log("AJAX obtenerMedicos ERROR: " . $e->getMessage(), 3, '/var/log/clinica/reservas.log');
                echo json_encode([
                    "status" => "error",
                    "mensaje" => "Error al obtener médicos: " . $e->getMessage()
                ]);
            }
            break;
              case 'obtenerHorariosDisponibles':
            if (isset($_POST['doctor_id']) && isset($_POST['fecha'])) {
                // El servicio_id ahora es obligatorio para filtrado correcto
                $servicioId = isset($_POST['servicio_id']) ? $_POST['servicio_id'] : 0;
                $doctorId = $_POST['doctor_id'];
                $fecha = $_POST['fecha'];
                
                error_log("AJAX obtenerHorariosDisponibles: ServicioID=$servicioId, DoctorID=$doctorId, Fecha=$fecha", 3, '/var/log/clinica/slots.log');
                
                try {
                    // Llamar al método del controlador CON filtro por servicio
                    $horarios = ControladorServicios::ctrObtenerHorariosDisponibles($servicioId, $doctorId, $fecha);
                    
                    // Ya no buscamos horarios alternativos - respetamos el filtro por servicio
                    error_log("Horarios encontrados para ServicioID=$servicioId: " . count($horarios), 3, '/var/log/clinica/slots.log');
                    
                    echo json_encode([
                        "status" => "success",
                        "data" => $horarios
                    ]);
                } catch (Exception $e) {
                    error_log("AJAX obtenerHorariosDisponibles ERROR: " . $e->getMessage(), 3, '/var/log/clinica/slots.log');
                    echo json_encode([
                        "status" => "error",
                        "message" => "Error al obtener horarios disponibles: " . $e->getMessage()
                    ]);
                }
            } else {
                echo json_encode([
                    "status" => "error",
                    "message" => "Faltan parámetros requeridos: doctor_id y fecha son obligatorios"
                ]);
            }
            break;
            
        case 'obtenerDiasDisponibles':
            if (isset($_POST['doctor_id'])) {
                $doctorId = $_POST['doctor_id'];
                $servicioId = isset($_POST['servicio_id']) ? $_POST['servicio_id'] : 0;
                
                error_log("AJAX obtenerDiasDisponibles: DoctorID=$doctorId, ServicioID=$servicioId", 3, '/var/log/clinica/slots.log');
                
                try {
                    // Llamar al método del controlador para obtener días disponibles
                    $diasDisponibles = ControladorServicios::ctrObtenerDiasDisponibles($doctorId, $servicioId);
                    
                    echo json_encode([
                        "status" => "success",
                        "data" => $diasDisponibles
                    ]);
                } catch (Exception $e) {
                    error_log("AJAX obtenerDiasDisponibles ERROR: " . $e->getMessage(), 3, '/var/log/clinica/slots.log');
                    echo json_encode([
                        "status" => "error",
                        "message" => "Error al obtener días disponibles: " . $e->getMessage()
                    ]);
                }
            } else {
                echo json_encode([
                    "status" => "error",
                    "message" => "Falta parámetro requerido: doctor_id es obligatorio"
                ]);
            }
            break;
            
        case 'obtenerTodosLosHorariosDisponibles':
            if (isset($_POST['doctor_id'])) {
                $doctorId = $_POST['doctor_id'];
                $servicioId = isset($_POST['servicio_id']) ? $_POST['servicio_id'] : 0;
                
                error_log("AJAX obtenerTodosLosHorariosDisponibles: DoctorID=$doctorId, ServicioID=$servicioId", 3, '/var/log/clinica/slots.log');
                
                try {
                    // Llamar al método del controlador para obtener todos los horarios disponibles
                    $todosLosHorarios = ControladorServicios::ctrObtenerTodosLosHorariosDisponibles($doctorId, $servicioId);
                    
                    echo json_encode([
                        "status" => "success",
                        "data" => $todosLosHorarios
                    ]);
                } catch (Exception $e) {
                    error_log("AJAX obtenerTodosLosHorariosDisponibles ERROR: " . $e->getMessage(), 3, '/var/log/clinica/slots.log');
                    echo json_encode([
                        "status" => "error",
                        "message" => "Error al obtener todos los horarios disponibles: " . $e->getMessage()
                    ]);
                }
            } else {
                echo json_encode([
                    "status" => "error",
                    "message" => "Falta parámetro requerido: doctor_id es obligatorio"
                ]);
            }
            break;
            
        // Añadir nuevo caso para obtener detalles de reserva
        case 'obtenerDetallesReserva':
            if (isset($_POST['reserva_id'])) {
                $reserva_id = $_POST['reserva_id'];
                
                try {
                    // Incluir el modelo de reservas si no está incluido ya
                    if (!class_exists('ReservasModel')) {
                        require_once $rutaBase . "/model/reservas.model.php";
                    }
                    
                    $modelo = new ReservasModel();
                    $detalles = $modelo->obtenerReservaPorId($reserva_id);
                    
                    if ($detalles) {
                        echo json_encode([
                            "status" => "success",
                            "data" => $detalles
                        ]);
                    } else {
                        echo json_encode([
                            "status" => "error",
                            "message" => "No se encontró la reserva con ID: " . $reserva_id
                        ]);
                    }
                } catch (Exception $e) {
                    echo json_encode([
                        "status" => "error",
                        "message" => "Error al obtener detalles de la reserva: " . $e->getMessage()
                    ]);
                }
            } else {
                echo json_encode([
                    "status" => "error",
                    "message" => "No se proporcionó ID de reserva"
                ]);
            }
            break;
            
        case 'obtenerReservaPorId':
            if (isset($_POST['reserva_id'])) {
                $reservaId = $_POST['reserva_id'];
                
                try {
                    $reserva = ControladorServicios::ctrObtenerReservaPorId($reservaId);
                    
                    if ($reserva) {
                        echo json_encode([
                            "status" => "success",
                            "data" => $reserva
                        ]);
                    } else {
                        echo json_encode([
                            "status" => "error",
                            "message" => "No se encontró la reserva con ID: " . $reservaId
                        ]);
                    }
                } catch (Exception $e) {
                    echo json_encode([
                        "status" => "error",
                        "message" => "Error al obtener la reserva: " . $e->getMessage()
                    ]);
                }
            } else {
                echo json_encode([
                    "status" => "error",
                    "message" => "ID de reserva no proporcionado"
                ]);
            }
            break;

        case 'actualizarReserva':
            try {
                // Validar que se proporcionaron los datos necesarios
                $camposRequeridos = ['reserva_id', 'servicio_id', 'doctor_id', 'paciente_id', 'fecha_reserva', 'hora_inicio', 'hora_fin'];
                $camposFaltantes = [];
                
                foreach ($camposRequeridos as $campo) {
                    if (!isset($_POST[$campo]) || empty($_POST[$campo])) {
                        $camposFaltantes[] = $campo;
                    }
                }
                
                if (!empty($camposFaltantes)) {
                    echo json_encode([
                        "status" => "error",
                        "message" => "Faltan campos requeridos: " . implode(", ", $camposFaltantes)
                    ]);
                    break;
                }
                
                // Preparar los datos para la actualización
                $datos = [
                    'reserva_id' => $_POST['reserva_id'],
                    'servicio_id' => $_POST['servicio_id'],
                    'agenda_id' => isset($_POST['agenda_id']) ? $_POST['agenda_id'] : null,
                    'doctor_id' => $_POST['doctor_id'],
                    'paciente_id' => $_POST['paciente_id'],
                    'fecha_reserva' => $_POST['fecha_reserva'],
                    'hora_inicio' => $_POST['hora_inicio'],
                    'hora_fin' => $_POST['hora_fin'],
                    'sala_id' => isset($_POST['sala_id']) ? $_POST['sala_id'] : null,
                    'reserva_estado' => isset($_POST['reserva_estado']) ? $_POST['reserva_estado'] : 'PENDIENTE',
                    'observaciones' => isset($_POST['observaciones']) ? $_POST['observaciones'] : ''
                ];
                
                error_log("AJAX actualizarReserva: Datos recibidos: " . json_encode($datos), 
                         3, '/var/log/clinica/reservas.log');
                
                $resultado = ControladorServicios::ctrActualizarReserva($datos);
                echo json_encode($resultado);
                
            } catch (Exception $e) {
                error_log("AJAX actualizarReserva ERROR: " . $e->getMessage(), 
                         3, '/var/log/clinica/reservas.log');
                echo json_encode([
                    "status" => "error",
                    "message" => "Error al actualizar la reserva: " . $e->getMessage()
                ]);
            }
            break;

        case 'editarReservaCompleta':
            try {
                // Validar que se proporcionaron los datos necesarios para edición completa
                $camposRequeridos = ['reserva_id', 'servicio_id', 'doctor_id', 'fecha_reserva', 'hora_inicio', 'hora_fin'];
                $camposFaltantes = [];
                
                foreach ($camposRequeridos as $campo) {
                    if (!isset($_POST[$campo]) || empty($_POST[$campo])) {
                        $camposFaltantes[] = $campo;
                    }
                }
                
                if (!empty($camposFaltantes)) {
                    echo json_encode([
                        "status" => "error",
                        "message" => "Faltan campos requeridos para edición: " . implode(", ", $camposFaltantes)
                    ]);
                    break;
                }
                
                // Preparar los datos completos para la edición
                $datos = [
                    'reserva_id' => intval($_POST['reserva_id']),
                    'servicio_id' => intval($_POST['servicio_id']),
                    'doctor_id' => intval($_POST['doctor_id']),
                    'fecha_reserva' => $_POST['fecha_reserva'],
                    'hora_inicio' => $_POST['hora_inicio'],
                    'hora_fin' => $_POST['hora_fin'],
                    'reserva_estado' => isset($_POST['reserva_estado']) ? $_POST['reserva_estado'] : 'PENDIENTE',
                    'observaciones' => isset($_POST['observaciones']) ? $_POST['observaciones'] : '',
                    'agenda_id' => isset($_POST['agenda_id']) && $_POST['agenda_id'] !== '' ? intval($_POST['agenda_id']) : null,
                    'sala_id' => isset($_POST['sala_id']) && $_POST['sala_id'] !== '' ? intval($_POST['sala_id']) : null,
                    'tarifa_id' => isset($_POST['tarifa_id']) && $_POST['tarifa_id'] !== '' ? intval($_POST['tarifa_id']) : null,
                    'seguro_id' => isset($_POST['seguro_id']) && $_POST['seguro_id'] !== '' ? intval($_POST['seguro_id']) : null
                ];
                
                error_log("AJAX editarReservaCompleta: Datos recibidos: " . json_encode($datos), 
                         3, '/var/log/clinica/reservas.log');
                
                $resultado = ControladorServicios::ctrEditarReservaCompleta($datos);
                echo json_encode($resultado);
                
            } catch (Exception $e) {
                error_log("AJAX editarReservaCompleta ERROR: " . $e->getMessage(), 
                         3, '/var/log/clinica/reservas.log');
                echo json_encode([
                    "status" => "error",
                    "message" => "Error al editar la reserva: " . $e->getMessage()
                ]);
            }
            break;

        case 'verificarConflictosEdicion':
            try {
                // Validar datos para verificación de conflictos
                $camposRequeridos = ['doctor_id', 'fecha_reserva', 'hora_inicio', 'hora_fin'];
                $camposFaltantes = [];
                
                foreach ($camposRequeridos as $campo) {
                    if (!isset($_POST[$campo]) || empty($_POST[$campo])) {
                        $camposFaltantes[] = $campo;
                    }
                }
                
                if (!empty($camposFaltantes)) {
                    echo json_encode([
                        "status" => "error",
                        "message" => "Faltan campos para verificar conflictos: " . implode(", ", $camposFaltantes)
                    ]);
                    break;
                }
                
                $datos = [
                    'doctor_id' => intval($_POST['doctor_id']),
                    'fecha_reserva' => $_POST['fecha_reserva'],
                    'hora_inicio' => $_POST['hora_inicio'],
                    'hora_fin' => $_POST['hora_fin'],
                    'reserva_id' => isset($_POST['reserva_id']) ? intval($_POST['reserva_id']) : null
                ];
                
                error_log("AJAX verificarConflictosEdicion: Verificando conflictos: " . json_encode($datos), 
                         3, '/var/log/clinica/reservas.log');
                
                $resultado = ControladorServicios::ctrVerificarConflictosEdicion($datos);
                echo json_encode($resultado);
                
            } catch (Exception $e) {
                error_log("AJAX verificarConflictosEdicion ERROR: " . $e->getMessage(), 
                         3, '/var/log/clinica/reservas.log');
                echo json_encode([
                    "status" => "error",
                    "message" => "Error al verificar conflictos: " . $e->getMessage()
                ]);
            }
            break;

        case 'enviarWhatsApp':
            if (isset($_POST['telefono']) && isset($_POST['mensaje'])) {
                $telefono = $_POST['telefono'];
                $mensaje = $_POST['mensaje'];
                
                // Registrar datos antes de procesar
                error_log("AJAX enviarWhatsApp: Enviando a teléfono {$telefono}, mensaje: " . substr($mensaje, 0, 50) . "...", 
                         3, '/var/log/clinica/whatsapp.log');
                
                try {
                    $resultado = ControladorServicios::ctrEnviarWhatsApp($telefono, $mensaje);
                    echo json_encode($resultado);
                } catch (Exception $e) {
                    error_log("AJAX enviarWhatsApp ERROR: " . $e->getMessage(), 3, '/var/log/clinica/whatsapp.log');
                    echo json_encode([
                        "status" => "error",
                        "message" => "Error al enviar mensaje: " . $e->getMessage()
                    ]);
                }
            } else {
                echo json_encode([
                    "status" => "error",
                    "message" => "Faltan parámetros: teléfono y mensaje son obligatorios"
                ]);
            }
            break;

        case 'obtenerSalasActivas':
            try {
                // Obtener todas las salas activas
                $salas = ControladorServicios::ctrObtenerSalasActivas();
                
                if ($salas) {
                    echo json_encode([
                        "status" => "success",
                        "data" => $salas
                    ]);
                } else {
                    echo json_encode([
                        "status" => "success",
                        "data" => [],
                        "message" => "No se encontraron salas activas"
                    ]);
                }
            } catch (Exception $e) {
                error_log("AJAX obtenerSalasActivas ERROR: " . $e->getMessage(), 
                         3, '/var/log/clinica/reservas.log');
                echo json_encode([
                    "status" => "error",
                    "message" => "Error al obtener salas: " . $e->getMessage()
                ]);
            }
            break;

        case 'obtenerDoctoresPorFecha':
            try {
                // Validar que se proporcione la fecha
                if (!isset($_POST['fecha']) || empty($_POST['fecha'])) {
                    echo json_encode([
                        "status" => "error",
                        "message" => "Fecha es requerida"
                    ]);
                    break;
                }
                
                $fecha = $_POST['fecha'];
                
                error_log("AJAX obtenerDoctoresPorFecha: Obteniendo doctores para fecha: " . $fecha, 
                         3, '/var/log/clinica/reservas.log');
                
                // USAR EL MISMO MÉTODO QUE FUNCIONA PARA NUEVA RESERVA
                $doctores = ControladorServicios::ctrObtenerMedicosDisponiblesPorFecha($fecha);
                
                error_log("AJAX obtenerDoctoresPorFecha: Doctores obtenidos usando método de nueva reserva: " . count($doctores), 
                         3, '/var/log/clinica/reservas.log');
                
                if ($doctores !== false) {
                    echo json_encode([
                        "status" => "success",
                        "data" => $doctores,
                        "total" => count($doctores)
                    ]);
                } else {
                    echo json_encode([
                        "status" => "error",
                        "message" => "Error al obtener doctores"
                    ]);
                }
                
            } catch (Exception $e) {
                error_log("AJAX obtenerDoctoresPorFecha ERROR: " . $e->getMessage(), 
                         3, '/var/log/clinica/reservas.log');
                echo json_encode([
                    "status" => "error",
                    "message" => "Error al obtener doctores: " . $e->getMessage()
                ]);
            }
            break;
            
        default:
            echo json_encode([
                "status" => "error",
                "message" => "Acción no reconocida: " . $action
            ]);
            break;
    }
} else {
    echo json_encode([
        "status" => "error",
        "message" => "No se especificó una acción"
    ]);
}
