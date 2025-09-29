<?php
/**
 * Controlador de Servicios Médicos
 * 
 * Este controlador maneja la lógica para la gestión de servicios médicos y reservas
 */

$rutaBase = dirname(__FILE__, 2); // Obtiene la ruta del directorio raíz (dos niveles arriba)
require_once $rutaBase . "/model/servicios.model.php";

class ControladorServicios {
    
    /**
         /**
     * Busca reservas según los filtros especificados
     * @param string $fecha Fecha de la reserva (opcional)
     * @param int $doctorId ID del doctor (opcional)
     * @param string $estado Estado de la reserva (opcional)
     * @param string $paciente Nombre del paciente para búsqueda (opcional)
     * @param int $salaId ID de la sala (opcional)
     * @param string $origen Origen de la reserva (opcional)
     * @return array Lista de reservas que coinciden con los filtros
     */
    static public function ctrBuscarReservas($fecha = null, $doctorId = null, $estado = null, $paciente = null, $salaId = null, $origen = null) {
        error_log("ctrBuscarReservas: Llamada con Fecha=" . ($fecha ?? "null") . 
                 ", DoctorID=" . ($doctorId ?? "null") . 
                 ", Estado=" . ($estado ?? "null") . 
                 ", Paciente=" . ($paciente ?? "null") . 
                 ", SalaID=" . ($salaId ?? "null") .
                 ", Origen=" . ($origen ?? "null"),
                 3, "/var/log/clinica/reservas.log");
          // Si se está filtrando por doctor, usamos la consulta optimizada
        if ($doctorId !== null) {
            error_log("ctrBuscarReservas: Usando consulta específica para doctor_id=$doctorId", 
                     3, "/var/log/clinica/reservas.log");
            // Pasar todos los parámetros para filtrado completo
            return ModelServicios::mdlBuscarReservasPorDoctor($doctorId, $fecha, $estado, $paciente, $salaId, $origen);
        }
        
        // Si se está filtrando solo por estado
        if ($estado !== null && $estado !== '' && $doctorId === null) {
            error_log("ctrBuscarReservas: Filtrando específicamente por estado=$estado", 
                     3, "/var/log/clinica/reservas.log");
            return ModelServicios::mdlObtenerReservasPorFecha($fecha, $doctorId, $estado, $paciente, $salaId, $origen);
        }
        
        // Si se está filtrando solo por paciente
        if ($paciente !== null && trim($paciente) !== '' && $doctorId === null && ($estado === null || $estado === '')) {
            error_log("ctrBuscarReservas: Filtrando específicamente por paciente=$paciente", 
                     3, "/var/log/clinica/reservas.log");
            return ModelServicios::mdlObtenerReservasPorFecha($fecha, $doctorId, $estado, $paciente, $salaId, $origen);
        }
        
        // En caso contrario, usamos la consulta general con todos los filtros
        return ModelServicios::mdlObtenerReservasPorFecha($fecha, $doctorId, $estado, $paciente, $salaId, $origen);
    }
    
    /**
     * Obtiene todos los médicos disponibles
     * @return array Lista de médicos
     */
    static public function ctrObtenerMedicos() {
        return ModelServicios::mdlObtenerMedicos();
    }
      /**
     * Obtiene los horarios disponibles para un doctor específico en una fecha
     * @param int $servicioId ID del servicio (opcional)
     * @param int $doctorId ID del doctor
     * @param string $fecha Fecha para la verificación (formato YYYY-MM-DD)
     * @return array Listado de horarios disponibles
     */
    static public function ctrObtenerHorariosDisponibles($servicioId, $doctorId, $fecha) {
        try {
            // Verificar que los parámetros necesarios sean válidos
            // El servicio ID ahora es opcional
            if (empty($doctorId) || empty($fecha)) {
                error_log("ctrObtenerHorariosDisponibles: Parámetros incompletos. DoctorID=$doctorId, Fecha=$fecha", 3, '/var/log/clinica/servicios.log');
                return [];
            }

            // Validar formato de la fecha
            $fechaObj = DateTime::createFromFormat('Y-m-d', $fecha);
            if (!$fechaObj) {
                error_log("ctrObtenerHorariosDisponibles: Formato de fecha inválido: $fecha", 3, '/var/log/clinica/servicios.log');
                return [];
            }

            // Obtener los horarios disponibles del modelo
            $horarios = ModelServicios::mdlObtenerHorariosDisponibles($servicioId, $doctorId, $fecha);
            
            // Registrar cuántos horarios se encontraron
            error_log("ctrObtenerHorariosDisponibles: Se encontraron " . count($horarios) . " horarios disponibles", 3, '/var/log/clinica/servicios.log');
            
            return $horarios;
            
        } catch (Exception $e) {
            error_log("ERROR en ctrObtenerHorariosDisponibles: " . $e->getMessage(), 3, '/var/log/clinica/servicios.log');
            return [];
        }
    }
    
    /**
     * Obtiene los días disponibles para un médico específico
     * @param int $doctorId ID del doctor
     * @param int $servicioId ID del servicio (opcional)
     * @return array Lista de días disponibles con información de horarios
     */
    static public function ctrObtenerDiasDisponibles($doctorId, $servicioId = 0) {
        try {
            // Verificar que el doctor ID sea válido
            if (empty($doctorId)) {
                error_log("ctrObtenerDiasDisponibles: Doctor ID es requerido", 3, '/var/log/clinica/servicios.log');
                return [];
            }

            // Obtener los días disponibles del modelo
            $diasDisponibles = ModelServicios::mdlObtenerDiasDisponibles($doctorId, $servicioId);
            
            // Registrar cuántos días se encontraron
            error_log("ctrObtenerDiasDisponibles: Se encontraron " . count($diasDisponibles) . " días disponibles para doctor ID $doctorId", 3, '/var/log/clinica/servicios.log');
            
            return $diasDisponibles;
            
        } catch (Exception $e) {
            error_log("ERROR en ctrObtenerDiasDisponibles: " . $e->getMessage(), 3, '/var/log/clinica/servicios.log');
            return [];
        }
    }
    
    /**
     * Obtiene días disponibles filtrados por fecha específica (nueva funcionalidad)
     * @param int $doctorId ID del doctor
     * @param int $servicioId ID del servicio (opcional)
     * @param string $fechaEspecifica Fecha específica en formato dd/mm/yyyy
     * @return array Lista de días disponibles para la fecha específica
     */
    static public function ctrObtenerDiasPorFechaEspecifica($doctorId, $servicioId = 0, $fechaEspecifica = null) {
        try {
            // Verificar que el doctor ID sea válido
            if (empty($doctorId)) {
                error_log("ctrObtenerDiasPorFechaEspecifica: Doctor ID es requerido", 3, '/var/log/clinica/servicios.log');
                return [];
            }

            // Verificar que se proporcione una fecha específica
            if (empty($fechaEspecifica)) {
                error_log("ctrObtenerDiasPorFechaEspecifica: Fecha específica es requerida", 3, '/var/log/clinica/servicios.log');
                return [];
            }

            // Convertir formato de fecha a Y-m-d
            $fechaFiltro = null;
            
            // Detectar y convertir formato de fecha
            if (strpos($fechaEspecifica, '/') !== false) {
                // Formato dd/mm/yyyy
                $fechaParts = explode('/', $fechaEspecifica);
                if (count($fechaParts) == 3) {
                    $fechaFiltro = $fechaParts[2] . '-' . $fechaParts[1] . '-' . $fechaParts[0];
                }
            } elseif (strpos($fechaEspecifica, '-') !== false) {
                // Formato yyyy-mm-dd (ya está en el formato correcto)
                $fechaFiltro = $fechaEspecifica;
            }
            
            if (!$fechaFiltro) {
                error_log("ctrObtenerDiasPorFechaEspecifica: Formato de fecha no válido: $fechaEspecifica", 3, '/var/log/clinica/servicios.log');
                return [];
            }

            // Usar el nuevo método del modelo que busca específicamente en cualquier fecha
            error_log("ctrObtenerDiasPorFechaEspecifica: Buscando horarios para fecha específica $fechaEspecifica -> $fechaFiltro", 3, '/var/log/clinica/servicios.log');
            $diasDisponibles = ModelServicios::mdlObtenerDiasDisponiblesPorFechaEspecifica($doctorId, $servicioId, $fechaFiltro);
            
            error_log("ctrObtenerDiasPorFechaEspecifica: Encontrados " . count($diasDisponibles) . " días para fecha específica", 3, '/var/log/clinica/servicios.log');
            return $diasDisponibles;
            
        } catch (Exception $e) {
            error_log("ERROR en ctrObtenerDiasPorFechaEspecifica: " . $e->getMessage(), 3, '/var/log/clinica/servicios.log');
            return [];
        }
    }
    
    /**
     * Obtiene todos los horarios disponibles para un médico de todas las fechas
     * @param int $doctorId ID del doctor
     * @param int $servicioId ID del servicio (opcional)
     * @return array Lista de todos los horarios disponibles con fechas
     */
    static public function ctrObtenerTodosLosHorariosDisponibles($doctorId, $servicioId = 0) {
        try {
            // Verificar que el doctor ID sea válido
            if (empty($doctorId)) {
                error_log("ctrObtenerTodosLosHorariosDisponibles: Doctor ID es requerido", 3, '/var/log/clinica/servicios.log');
                return [];
            }

            // Obtener todos los horarios disponibles del modelo
            $todosLosHorarios = ModelServicios::mdlObtenerTodosLosHorariosDisponibles($doctorId, $servicioId);
            
            // Registrar cuántos horarios se encontraron
            error_log("ctrObtenerTodosLosHorariosDisponibles: Se encontraron " . count($todosLosHorarios) . " horarios disponibles para doctor ID $doctorId", 3, '/var/log/clinica/servicios.log');
            
            return $todosLosHorarios;
            
        } catch (Exception $e) {
            error_log("ERROR en ctrObtenerTodosLosHorariosDisponibles: " . $e->getMessage(), 3, '/var/log/clinica/servicios.log');
            return [];
        }
    }
    
    /**
     * Obtiene las categorías de servicios
     * @return array Lista de categorías
     */
    static public function ctrObtenerCategorias() {
        return ModelServicios::mdlObtenerCategorias();
    }
    
    /**
     * Obtiene todos los servicios médicos
     * @param int $categoriaId ID de la categoría para filtrar (opcional)
     * @return array Lista de servicios
     */
    static public function ctrObtenerServicios($categoriaId = null) {
        return ModelServicios::mdlObtenerServicios($categoriaId);
    }
    
    /**
     * Obtiene un servicio médico por su ID
     * @param int $servicioId ID del servicio
     * @return array Datos del servicio
     */
    static public function ctrObtenerServicioPorId($servicioId) {
        $servicio = ModelServicios::mdlObtenerServicioPorId($servicioId);
        $requisitos = ModelServicios::mdlObtenerRequisitosServicio($servicioId);
        $tarifas = ModelServicios::mdlObtenerTarifasServicio($servicioId);
        $proveedores = ModelServicios::mdlObtenerProveedoresServicio($servicioId);
        
        return [
            "servicio" => $servicio,
            "requisitos" => $requisitos,
            "tarifas" => $tarifas,
            "proveedores" => $proveedores
        ];
    }
    
    /**
     * Obtiene los horarios de un médico
     * @param int $doctorId ID del médico
     * @return array Lista de horarios
     */
    static public function ctrObtenerHorariosMedico($doctorId) {
        return ModelServicios::mdlObtenerHorariosMedico($doctorId);
    }
    
    /**
     * Genera los slots disponibles para un servicio, doctor y fecha específica
     * @param int $servicioId ID del servicio
     * @param int $doctorId ID del doctor
     * @param string $fecha Fecha en formato YYYY-MM-DD
     * @return array Lista de slots de horarios
     */
    static public function ctrGenerarSlotsDisponibles($servicioId, $doctorId, $fecha) {
        return ModelServicios::mdlGenerarSlotsDisponibles($servicioId, $doctorId, $fecha);
    }
    
    /**
     * Obtiene los médicos disponibles para una fecha específica
     * @param string $fecha Fecha en formato YYYY-MM-DD
     * @return array Lista de médicos disponibles
     */
    static public function ctrObtenerMedicosDisponiblesPorFecha($fecha) {
        return ModelServicios::mdlObtenerMedicosDisponiblesPorFecha($fecha);
    }
    
    /**
     * Obtiene los servicios disponibles para una fecha y doctor específicos
     * @param string $fecha Fecha en formato YYYY-MM-DD
     * @param int $doctorId ID del doctor
     * @return array Lista de servicios disponibles
     */
    static public function ctrObtenerServiciosPorFechaMedico($fecha, $doctorId) {
        return ModelServicios::mdlObtenerServiciosPorFechaMedico($fecha, $doctorId);
    }
    
    /**
     * Obtiene las reservas existentes para una fecha específica
     * @param string $fecha Fecha en formato YYYY-MM-DD
     * @param int $doctorId ID del doctor (opcional)
     * @param string $estado Estado de la reserva (opcional)
     * @return array Lista de reservas
     */
    static public function ctrObtenerReservasPorFecha($fecha, $doctorId = null, $estado = null) {
        return ModelServicios::mdlObtenerReservasPorFecha($fecha, $doctorId, $estado);
    }
    
    /**
     * Obtiene los cupos disponibles por turno para una fecha específica
     * @param string $fecha Fecha en formato YYYY-MM-DD
     * @return array Cupos disponibles por turno
     */
    static public function ctrObtenerCuposDisponiblesPorTurno($fecha) {
        return ModelServicios::mdlObtenerCuposDisponiblesPorTurno($fecha);
    }
    
    /**
     * Crea una nueva reserva
     * @param array $datos Datos de la reserva
     * @return array Resultado de la operación
     */
    static public function ctrCrearReserva($datos) {
        // Validar datos requeridos
        if (
            empty($datos['servicio_id']) || 
            empty($datos['doctor_id']) || 
            empty($datos['paciente_id']) || 
            empty($datos['fecha_reserva']) || 
            empty($datos['hora_inicio']) || 
            empty($datos['hora_fin'])
        ) {
            return [
                "error" => true, 
                "mensaje" => "Faltan datos obligatorios para la reserva."
            ];
        }
        
        // Validar formato de fecha (YYYY-MM-DD)
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $datos['fecha_reserva'])) {
            return [
                "error" => true, 
                "mensaje" => "El formato de la fecha debe ser YYYY-MM-DD."
            ];
        }
        
        // Validar que la fecha no sea pasada
        $hoy = date('Y-m-d');
        if ($datos['fecha_reserva'] < $hoy) {
            return [
                "error" => true, 
                "mensaje" => "No se puede crear una reserva en una fecha pasada."
            ];
        }
        
        // Validar formato de horas (HH:MM:SS)
        if (!preg_match('/^\d{2}:\d{2}:\d{2}$/', $datos['hora_inicio']) || 
            !preg_match('/^\d{2}:\d{2}:\d{2}$/', $datos['hora_fin'])) {
            return [
                "error" => true, 
                "mensaje" => "El formato de las horas debe ser HH:MM:SS."
            ];
        }
        
        // Validar que hora_fin sea mayor que hora_inicio
        if ($datos['hora_inicio'] >= $datos['hora_fin']) {
            return [
                "error" => true, 
                "mensaje" => "La hora de fin debe ser mayor que la hora de inicio."
            ];
        }
        
        // Si todo es válido, crear la reserva
        return ModelServicios::mdlCrearReserva($datos);
    }
    
    /**
     * Cambia el estado de una reserva
     * @param int $reservaId ID de la reserva
     * @param string $nuevoEstado Nuevo estado de la reserva
     * @return array Resultado de la operación
     */
    static public function ctrCambiarEstadoReserva($reservaId, $nuevoEstado) {
        // Verificar que el estado sea válido
        $estadosValidos = ['PENDIENTE', 'CONFIRMADA', 'CANCELADA', 'COMPLETADA'];
        if (!in_array($nuevoEstado, $estadosValidos)) {
            return [
                "error" => true, 
                "mensaje" => "Estado de reserva no válido."
            ];
        }
        
        // Actualizar el estado en la base de datos
        try {
            $stmt = Conexion::conectar()->prepare(
                "UPDATE servicios_reservas SET 
                    reserva_estado = :estado,
                    updated_at = CURRENT_TIMESTAMP,
                    updated_by = :updated_by
                WHERE reserva_id = :reserva_id"
            );
            
            $updatedBy = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 1;
            
            $stmt->bindParam(":estado", $nuevoEstado, PDO::PARAM_STR);
            $stmt->bindParam(":updated_by", $updatedBy, PDO::PARAM_INT);
            $stmt->bindParam(":reserva_id", $reservaId, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                return [
                    "error" => false, 
                    "mensaje" => "Estado de la reserva actualizado correctamente."
                ];
            } else {
                return [
                    "error" => true, 
                    "mensaje" => "Error al actualizar el estado de la reserva."
                ];
            }
        } catch (PDOException $e) {
            return [
                "error" => true, 
                "mensaje" => "Error en la base de datos: " . $e->getMessage()
            ];
        }
    }
    
    /**
     * Busca pacientes por nombre o documento
     * @param string $termino Término de búsqueda
     * @return array Lista de pacientes encontrados
     */
    static public function ctrBuscarPaciente($termino) {
        try {
            $stmt = Conexion::conectar()->prepare(                "SELECT 
                    p.person_id,
                    p.first_name,
                    p.last_name,
                    p.document_number,
                    p.email,
                    p.phone_number
                FROM 
                    rh_person p
                WHERE 
                    (p.first_name ILIKE :termino OR 
                    p.last_name ILIKE :termino OR 
                    p.document_number ILIKE :termino)
                    AND p.is_active = true
                ORDER BY 
                    p.first_name, p.last_name
                LIMIT 10"
            );
            
            $termino = "%" . $termino . "%";
            $stmt->bindParam(":termino", $termino, PDO::PARAM_STR);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error al buscar paciente: " . $e->getMessage(), 0);
            return [];
        }
    }
    
    /**
     * Busca un paciente específico por su ID
     * @param int $pacienteId ID del paciente
     * @return array|null Datos del paciente encontrado o null si no existe
     */
    static public function ctrBuscarPacientePorId($pacienteId) {
        try {
            $stmt = Conexion::conectar()->prepare(
                "SELECT 
                    p.person_id,
                    p.first_name,
                    p.last_name,
                    p.document_number,
                    p.email,
                    p.phone_number
                FROM 
                    rh_person p
                WHERE 
                    p.person_id = :paciente_id
                    AND p.is_active = true"
            );
            
            $stmt->bindParam(":paciente_id", $pacienteId, PDO::PARAM_INT);
            $stmt->execute();
            
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            return $resultado ? [$resultado] : []; // Devolver array para mantener consistencia con buscarPaciente
            
        } catch (PDOException $e) {
            error_log("Error al buscar paciente por ID: " . $e->getMessage(), 0);
            return [];
        }
    }
    
    /**
     * Guarda una nueva reserva médica
     * @param array $datos Datos de la reserva
     * @return mixed ID de la reserva creada o false en caso de error
     */
    static public function ctrGuardarReserva($datos) {
        // Validar datos requeridos
        if (
            empty($datos['doctor_id']) || 
            empty($datos['servicio_id']) || 
            empty($datos['paciente_id']) || 
            empty($datos['fecha_reserva']) || 
            empty($datos['hora_inicio']) || 
            empty($datos['hora_fin'])
        ) {
            error_log("Error en ctrGuardarReserva: Faltan datos requeridos.", 3, '/var/log/clinica/reservas.log');
            return false;
        }

        // Preparar datos para el modelo
        $datosReserva = [
            'servicio_id' => $datos['servicio_id'],
            'doctor_id' => $datos['doctor_id'],
            'paciente_id' => $datos['paciente_id'],
            'fecha_reserva' => $datos['fecha_reserva'],
            'hora_inicio' => $datos['hora_inicio'],
            'hora_fin' => $datos['hora_fin'],
            'observaciones' => $datos['observaciones'] ?? '',
            'reserva_estado' => 'PENDIENTE',
            'business_id' => isset($_SESSION['business_id']) ? $_SESSION['business_id'] : 1,
            'created_by' => isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 1,
            'origen_reserva' => $datos['origen_reserva'] ?? 'SISTEMA' // Default a SISTEMA si no se especifica
        ];
          // Incluir campos opcionales si están presentes en la petición
        if (!empty($datos['agenda_id'])) {
            $datosReserva['agenda_id'] = $datos['agenda_id'];
        }
        
        if (!empty($datos['tarifa_id'])) {
            $datosReserva['tarifa_id'] = $datos['tarifa_id'];
        }
        
        if (!empty($datos['sala_id'])) {
            $datosReserva['sala_id'] = $datos['sala_id'];
        }
        
        if (!empty($datos['seguro_id'])) {
            $datosReserva['seguro_id'] = $datos['seguro_id'];
        }
        
        error_log("ctrGuardarReserva: Enviando datos al modelo: " . json_encode($datosReserva), 3, '/var/log/clinica/reservas.log');
        return ModelServicios::mdlGuardarReserva($datosReserva);
    }
    
    /**
     * Obtiene los proveedores de seguro médico
     * @return array Lista de proveedores de seguro médico
     */
    static public function ctrObtenerProveedoresSeguro() {
        return ModelServicios::mdlObtenerProveedoresSeguro();
    }
    /**
     * Envía un mensaje de WhatsApp utilizando la API externa
     * @param string $telefono Número de teléfono con código de país (sin el +)
     * @param string $mensaje Mensaje a enviar
     * @return array Resultado de la operación
     */
    static public function ctrEnviarWhatsApp($telefono, $mensaje) {
        try {
          
            // Configuración
            $endpoint = 'http://181.122.125.143:8082/send.php';
            $user     = 'admin';
            $pass     = 'admin123';

            // Datos a enviar (formato E.164 para el número: +595983123456)
            $to   = '+'.$telefono;
            $text = $mensaje;

            // JSON del body
            $payload = json_encode(
                ['to' => $to, 'text' => $text],
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
            );

            // Preparar cURL
            $ch = curl_init($endpoint);
            curl_setopt_array($ch, [
                CURLOPT_POST           => true,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER     => [
                    'Authorization: Basic ' . base64_encode("$user:$pass"),
                    'Content-Type: application/json; charset=utf-8',
                ],
                CURLOPT_POSTFIELDS     => $payload,
                CURLOPT_TIMEOUT        => 20,
            ]);

            // Ejecutar
            $response = curl_exec($ch);
            $errNo    = curl_errno($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            // Manejo simple de respuesta
            if ($errNo) {
                die("Error cURL (#$errNo): " . ($response ?: 'sin respuesta'));
            }

            // echo "HTTP $httpCode\n";
            // echo $response . "\n";
            return [
                'status' => 'success',
                'mensaje' => 'Mensaje enviado correctamente',
                'response' => $response
            ];
            
        } catch (Exception $e) {
            error_log("Error al enviar WhatsApp: " . $e->getMessage(), 3, dirname(__FILE__, 2) . '/logs/whatsapp_api.log');
            
            return [
                'status' => 'error',
                'mensaje' => 'Error al enviar WhatsApp: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Obtiene los detalles de una reserva específica
     * @param int $reservaId ID de la reserva
     * @return array|null Datos de la reserva
     */
    static public function ctrObtenerReservaPorId($reservaId) {
        return ModelServicios::mdlObtenerReservaPorId($reservaId);
    }

    /**
     * Actualiza una reserva existente
     * @param array $datos Datos de la reserva a actualizar
     * @return array Resultado de la operación
     */
    static public function ctrActualizarReserva($datos) {
        // Validar datos requeridos
        $camposRequeridos = ['reserva_id', 'servicio_id', 'doctor_id', 'paciente_id', 'fecha_reserva', 'hora_inicio', 'hora_fin'];
        
        foreach ($camposRequeridos as $campo) {
            if (!isset($datos[$campo]) || empty($datos[$campo])) {
                return [
                    "status" => "error",
                    "message" => "El campo $campo es requerido"
                ];
            }
        }

        // Validar formato de fecha
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $datos['fecha_reserva'])) {
            return [
                "status" => "error",
                "message" => "Formato de fecha inválido"
            ];
        }

        // Validar formato de hora
        if (!preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $datos['hora_inicio']) || 
            !preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $datos['hora_fin'])) {
            return [
                "status" => "error",
                "message" => "Formato de hora inválido"
            ];
        }

        // Asegurar valores por defecto para campos opcionales
        $datos['agenda_id'] = $datos['agenda_id'] ?? null;
        $datos['sala_id'] = $datos['sala_id'] ?? null;
        $datos['reserva_estado'] = $datos['reserva_estado'] ?? 'PENDIENTE';
        $datos['observaciones'] = $datos['observaciones'] ?? '';

        return ModelServicios::mdlActualizarReserva($datos);
    }

    /**
     * Edita una reserva con validación completa y gestión de agenda
     * @param array $datos Datos de la reserva a editar
     * @return array Resultado de la operación
     */
    static public function ctrEditarReservaCompleta($datos) {
        // Validar datos requeridos
        $camposRequeridos = ['reserva_id', 'servicio_id', 'doctor_id', 'fecha_reserva', 'hora_inicio', 'hora_fin'];
        
        foreach ($camposRequeridos as $campo) {
            if (!isset($datos[$campo]) || empty($datos[$campo])) {
                return [
                    "status" => "error",
                    "message" => "El campo $campo es requerido para la edición"
                ];
            }
        }

        // Validaciones de formato
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $datos['fecha_reserva'])) {
            return [
                "status" => "error", 
                "message" => "Formato de fecha inválido (debe ser YYYY-MM-DD)"
            ];
        }

        if (!preg_match('/^\d{2}:\d{2}$/', $datos['hora_inicio']) || 
            !preg_match('/^\d{2}:\d{2}$/', $datos['hora_fin'])) {
            return [
                "status" => "error",
                "message" => "Formato de hora inválido (debe ser HH:MM)"
            ];
        }

        // Validar que la hora de fin sea posterior a la de inicio
        $horaInicio = DateTime::createFromFormat('H:i', $datos['hora_inicio']);
        $horaFin = DateTime::createFromFormat('H:i', $datos['hora_fin']);
        
        if ($horaInicio >= $horaFin) {
            return [
                "status" => "error",
                "message" => "La hora de fin debe ser posterior a la hora de inicio"
            ];
        }

        // Validar que la fecha no sea anterior a hoy
        $fechaReserva = DateTime::createFromFormat('Y-m-d', $datos['fecha_reserva']);
        $hoy = new DateTime();
        $hoy->setTime(0, 0, 0);
        
        if ($fechaReserva < $hoy) {
            return [
                "status" => "error",
                "message" => "No se puede programar una reserva en una fecha pasada"
            ];
        }

        // Asegurar valores por defecto para campos opcionales
        $datos['reserva_estado'] = $datos['reserva_estado'] ?? 'PENDIENTE';
        $datos['observaciones'] = $datos['observaciones'] ?? '';
        $datos['agenda_id'] = $datos['agenda_id'] ?? null;
        $datos['sala_id'] = $datos['sala_id'] ?? null;
        $datos['tarifa_id'] = $datos['tarifa_id'] ?? null;
        $datos['seguro_id'] = $datos['seguro_id'] ?? null;

        // Convertir a formato de 24 horas con segundos si es necesario
        if (strlen($datos['hora_inicio']) == 5) {
            $datos['hora_inicio'] .= ':00';
        }
        if (strlen($datos['hora_fin']) == 5) {
            $datos['hora_fin'] .= ':00';
        }

        error_log("ctrEditarReservaCompleta: Procesando edición de reserva ID {$datos['reserva_id']}", 
                  3, "/var/log/clinica/reservas.log");
        error_log("ctrEditarReservaCompleta: Datos validados: " . json_encode($datos), 
                  3, "/var/log/clinica/reservas.log");

        return ModelServicios::mdlEditarReservaCompleta($datos);
    }

    /**
     * Verifica conflictos de horario para edición de reservas
     * @param array $datos Datos para verificar conflictos
     * @return array Resultado de la verificación
     */
    static public function ctrVerificarConflictosEdicion($datos) {
        $camposRequeridos = ['doctor_id', 'fecha_reserva', 'hora_inicio', 'hora_fin'];
        
        foreach ($camposRequeridos as $campo) {
            if (!isset($datos[$campo]) || empty($datos[$campo])) {
                return [
                    "status" => "error",
                    "message" => "El campo $campo es requerido para verificar conflictos"
                ];
            }
        }

        $excluirReservaId = isset($datos['reserva_id']) ? $datos['reserva_id'] : null;
        
        $conflictos = ModelServicios::verificarConflictosReserva(
            $datos['doctor_id'],
            $datos['fecha_reserva'],
            $datos['hora_inicio'],
            $datos['hora_fin'],
            $excluirReservaId
        );

        return [
            "status" => "success",
            "conflictos" => $conflictos,
            "tiene_conflictos" => !empty($conflictos)
        ];
    }

    /**
     * Obtiene todas las salas activas del sistema
     * @return array Lista de salas activas
     */
    static public function ctrObtenerSalasActivas() {
        try {
            return ModelServicios::mdlObtenerSalasActivas();
        } catch (Exception $e) {
            error_log("Error en ctrObtenerSalasActivas: " . $e->getMessage(), 
                      3, "/var/log/clinica/reservas.log");
            return [];
        }
    }

    /**
     * Obtiene doctores disponibles para una fecha específica
     * @param string $fecha Fecha en formato Y-m-d
     * @return array Lista de doctores con horarios disponibles
     */
    static public function ctrObtenerDoctoresPorFecha($fecha) {
        try {
            // Validar que se proporcione la fecha
            if (empty($fecha)) {
                throw new Exception("Fecha es requerida");
            }
            
            // Validar formato de fecha
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
                throw new Exception("Formato de fecha inválido. Use YYYY-MM-DD");
            }
            
            error_log("ctrObtenerDoctoresPorFecha: Obteniendo doctores para fecha: " . $fecha, 
                      3, "/var/log/clinica/reservas.log");
            
            $doctores = ModelServicios::mdlObtenerDoctoresPorFecha($fecha);
            
            error_log("ctrObtenerDoctoresPorFecha: Encontrados " . count($doctores) . " doctores", 
                      3, "/var/log/clinica/reservas.log");
            
            return $doctores;
            
        } catch (Exception $e) {
            error_log("Error en ctrObtenerDoctoresPorFecha: " . $e->getMessage(), 
                      3, "/var/log/clinica/reservas.log");
            return [];
        }
    }

    /**
     * Obtiene todos los servicios activos del sistema
     * @return array Lista de todos los servicios activos
     */
    static public function ctrObtenerTodosLosServiciosActivos() {
        try {
            error_log("ctrObtenerTodosLosServiciosActivos: Obteniendo todos los servicios activos", 
                      3, "/var/log/clinica/servicios.log");
                      
            $servicios = ModelServicios::mdlObtenerTodosLosServiciosActivos();
            
            error_log("ctrObtenerTodosLosServiciosActivos: Encontrados " . count($servicios) . " servicios", 
                      3, "/var/log/clinica/servicios.log");
            
            return $servicios;
            
        } catch (Exception $e) {
            error_log("Error en ctrObtenerTodosLosServiciosActivos: " . $e->getMessage(), 
                      3, "/var/log/clinica/servicios.log");
            return [];
        }
    }

    /**
     * Obtiene médicos que ofrecen un servicio específico
     * @param int $servicioId ID del servicio
     * @return array Lista de médicos que ofrecen el servicio
     */
    static public function ctrObtenerMedicosPorServicio($servicioId) {
        try {
            // Validar que se proporcione el ID del servicio
            if (empty($servicioId) || !is_numeric($servicioId)) {
                throw new Exception("ID de servicio inválido");
            }
            
            error_log("ctrObtenerMedicosPorServicio: Obteniendo médicos para servicio ID: " . $servicioId, 
                      3, "/var/log/clinica/servicios.log");
                      
            $medicos = ModelServicios::mdlObtenerMedicosPorServicio($servicioId);
            
            error_log("ctrObtenerMedicosPorServicio: Encontrados " . count($medicos) . " médicos", 
                      3, "/var/log/clinica/servicios.log");
            
            return $medicos;
            
        } catch (Exception $e) {
            error_log("Error en ctrObtenerMedicosPorServicio: " . $e->getMessage(), 
                      3, "/var/log/clinica/servicios.log");
            return [];
        }
    }

    /**
     * Verifica si un usuario puede cancelar una reserva específica
     * @param int $reservaId ID de la reserva
     * @return array Resultado de la verificación
     */
    static public function ctrVerificarPermisoCancelacion($reservaId) {
        try {
            // Validar que se proporcione el ID de la reserva
            if (empty($reservaId) || !is_numeric($reservaId)) {
                return [
                    "puede_cancelar" => false,
                    "motivo" => "ID de reserva inválido"
                ];
            }

            // Obtener el ID del usuario de la sesión
            $usuarioId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 
                        (isset($_SESSION['usuario_id']) ? $_SESSION['usuario_id'] : null);

            error_log("ctrVerificarPermisoCancelacion: Verificando reserva ID=$reservaId, Usuario=$usuarioId", 
                     3, "/var/log/clinica/reservas.log");

            // Llamar al modelo para verificar permisos
            $resultado = ModelServicios::mdlPuedeCancelarReserva($reservaId, $usuarioId);

            error_log("ctrVerificarPermisoCancelacion: Resultado - " . json_encode($resultado), 
                     3, "/var/log/clinica/reservas.log");

            return $resultado;

        } catch (Exception $e) {
            error_log("Error en ctrVerificarPermisoCancelacion: " . $e->getMessage(), 
                     3, "/var/log/clinica/reservas.log");
            return [
                "puede_cancelar" => false,
                "motivo" => "Error interno verificando permisos: " . $e->getMessage()
            ];
        }
    }

    /**
     * Obtiene reservas incluyendo canceladas según configuración del sistema
     * @param string $fecha Fecha de la reserva (opcional)
     * @param int $doctorId ID del doctor (opcional)
     * @param string $estado Estado de la reserva (opcional)
     * @param string $paciente Nombre del paciente para búsqueda (opcional)
     * @param int $salaId ID de la sala (opcional)
     * @param string $origen Origen de la reserva (opcional)
     * @param bool $incluirCanceladas Forzar inclusión de canceladas
     * @return array Lista de reservas
     */
    static public function ctrBuscarReservasConCanceladas($fecha = null, $doctorId = null, $estado = null, $paciente = null, $salaId = null, $origen = null, $incluirCanceladas = null) {
        try {
            error_log("ctrBuscarReservasConCanceladas: Iniciando búsqueda con parámetros - " .
                     "Fecha=" . ($fecha ?? "null") . 
                     ", DoctorID=" . ($doctorId ?? "null") . 
                     ", Estado=" . ($estado ?? "null") . 
                     ", Paciente=" . ($paciente ?? "null") . 
                     ", SalaID=" . ($salaId ?? "null") .
                     ", Origen=" . ($origen ?? "null") .
                     ", IncluirCanceladas=" . ($incluirCanceladas ?? "null"),
                     3, "/var/log/clinica/reservas.log");
            
            return ModelServicios::mdlObtenerReservasConCanceladas($fecha, $doctorId, $estado, $paciente, $salaId, $origen, $incluirCanceladas);
            
        } catch (Exception $e) {
            error_log("Error en ctrBuscarReservasConCanceladas: " . $e->getMessage(), 
                     3, "/var/log/clinica/reservas.log");
            return [];
        }
    }

    /**
     * Obtiene los parámetros del sistema para reservas
     * @return array Parámetros del sistema
     */
    static public function ctrObtenerParametrosReservas() {
        try {
            return ModelServicios::mdlObtenerParametrosReservas();
        } catch (Exception $e) {
            error_log("Error en ctrObtenerParametrosReservas: " . $e->getMessage(), 
                     3, "/var/log/clinica/reservas.log");
            return [
                'LIMITE_HORAS_CANCELACION' => 72,
                'MOSTRAR_RESERVAS_CANCELADAS' => true,
                'COLOR_RESERVAS_CANCELADAS' => '#ffcccc',
                'DIAS_MANTENER_CANCELADAS' => 30
            ];
        }
    }

    /**
     * Verifica si un usuario tiene un permiso específico
     * @param int $usuarioId ID del usuario
     * @param string $permisoNombre Nombre del permiso
     * @return bool Si tiene el permiso
     */
    static public function ctrVerificarPermisoUsuario($usuarioId, $permisoNombre) {
        try {
            error_log("ctrVerificarPermisoUsuario: Verificando permiso '$permisoNombre' para usuario $usuarioId", 
                     3, "/var/log/clinica/reservas.log");
            
            $tienePermiso = ModelServicios::mdlVerificarPermisoUsuario($usuarioId, $permisoNombre);
            
            error_log("ctrVerificarPermisoUsuario: Usuario $usuarioId " . ($tienePermiso ? 'SÍ' : 'NO') . " tiene permiso '$permisoNombre'", 
                     3, "/var/log/clinica/reservas.log");
            
            return $tienePermiso;
            
        } catch (Exception $e) {
            error_log("Error en ctrVerificarPermisoUsuario: " . $e->getMessage(), 
                     3, "/var/log/clinica/reservas.log");
            return false;
        }
    }

    /**
     * Cancela una reserva médica con validaciones de tiempo y permisos
     * @param int $reservaId ID de la reserva a cancelar
     * @param string $motivo Motivo de la cancelación (opcional)
     * @param bool $forzar Forzar cancelación sin validar tiempo (solo con permisos especiales)
     * @return array Resultado de la operación
     */
    static public function ctrCancelarReserva($reservaId, $motivo = null, $forzar = false) {
        try {
            // Validar que se proporcione el ID de la reserva
            if (empty($reservaId) || !is_numeric($reservaId)) {
                return [
                    "error" => true,
                    "mensaje" => "ID de reserva inválido"
                ];
            }

            // Obtener el ID del usuario de la sesión
            $usuarioId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 
                        (isset($_SESSION['usuario_id']) ? $_SESSION['usuario_id'] : null);

            error_log("ctrCancelarReserva: Cancelando reserva ID=$reservaId, Usuario=$usuarioId, Motivo=" . 
                     ($motivo ?? "Sin motivo") . ", Forzar=$forzar", 3, "/var/log/clinica/reservas.log");

            // Si no se está forzando, verificar permisos primero
            if (!$forzar) {
                $permisos = ModelServicios::mdlPuedeCancelarReserva($reservaId, $usuarioId);
                if (!$permisos['puede_cancelar']) {
                    error_log("ctrCancelarReserva: Permisos insuficientes - " . $permisos['motivo'], 
                             3, "/var/log/clinica/reservas.log");
                    return [
                        "error" => true,
                        "mensaje" => $permisos['motivo'],
                        "detalles" => $permisos
                    ];
                }
                
                // Si se detectó que necesita permiso especial, logearlo
                if (isset($permisos['permiso_especial']) && $permisos['permiso_especial']) {
                    error_log("ctrCancelarReserva: Usando permiso especial para cancelación tardía", 
                             3, "/var/log/clinica/reservas.log");
                }
            }

            // Llamar al modelo para cancelar la reserva
            $resultado = ModelServicios::mdlCancelarReserva($reservaId, $motivo, $usuarioId, $forzar);

            // Log del resultado
            if ($resultado['error']) {
                error_log("ctrCancelarReserva: Error al cancelar reserva - " . $resultado['mensaje'], 
                         3, "/var/log/clinica/reservas.log");
            } else {
                error_log("ctrCancelarReserva: Reserva cancelada exitosamente", 
                         3, "/var/log/clinica/reservas.log");
            }

            return $resultado;

        } catch (Exception $e) {
            error_log("Error en ctrCancelarReserva: " . $e->getMessage(), 
                     3, "/var/log/clinica/reservas.log");
            return [
                "error" => true,
                "mensaje" => "Error interno al cancelar la reserva: " . $e->getMessage()
            ];
        }
    }
}

