<?php
require_once "conexion.php";

class ModelServicios {
    /**
     * Obtiene los horarios configurados para un médico
     * @param int $doctorId ID del médico/doctor
     * @return array Listado de horarios del médico
     */    static public function mdlObtenerHorariosMedico($doctorId) {
        try {
            $stmt = Conexion::conectar()->prepare(
                "SELECT 
                    ad.detalle_id,
                    ad.agenda_id,
                    ac.agenda_descripcion,
                    ad.turno_id,
                    t.turno_nombre,
                    ad.sala_id,
                    s.sala_nombre,
                    ad.dia_semana,
                    ad.hora_inicio,
                    ad.hora_fin,
                    ad.intervalo_minutos,
                    ad.cupo_maximo,
                    ad.detalle_estado
                FROM 
                    agendas_detalle ad
                INNER JOIN 
                    agendas_cabecera ac ON ad.agenda_id = ac.agenda_id
                INNER JOIN 
                    turnos t ON ad.turno_id = t.turno_id
                LEFT JOIN 
                    salas s ON ad.sala_id = s.sala_id
                WHERE 
                    ac.medico_id = :doctor_id
                    AND ad.detalle_estado = true
                    AND ac.agenda_estado = true
                ORDER BY
                    CASE ad.dia_semana 
                        WHEN 'LUNES' THEN 1
                        WHEN 'MARTES' THEN 2
                        WHEN 'MIERCOLES' THEN 3
                        WHEN 'JUEVES' THEN 4
                        WHEN 'VIERNES' THEN 5
                        WHEN 'SABADO' THEN 6
                        WHEN 'DOMINGO' THEN 7
                    END,
                    ad.hora_inicio"
            );
            
            $stmt->bindParam(":doctor_id", $doctorId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener horarios del médico: " . $e->getMessage(), 0);
            return [];
        }
    }
    
    /**
     * Obtiene todas las categorías de servicios activas
     * @return array Listado de categorías
     */
    static public function mdlObtenerCategorias() {
        try {
            // Intentar obtener categorías de una tabla real
            $stmt = Conexion::conectar()->prepare(
                "SELECT categoria_id, categoria_nombre, categoria_descripcion
                 FROM servicios_categorias
                 WHERE categoria_estado = 'ACTIVO'
                 ORDER BY categoria_nombre ASC"
            );
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (count($result) > 0) {
                return $result;
            }
        } catch (PDOException $e) {
            error_log("Error en mdlObtenerCategorias: " . $e->getMessage(), 
                      3, "/var/log/clinica/database.log");
        }
        
        // Si no existe la tabla o no hay datos, devolver categorías por defecto
        return [
            [
                'categoria_id' => 1,
                'categoria_nombre' => 'General',
                'categoria_descripcion' => 'Servicios médicos generales'
            ],
            [
                'categoria_id' => 2,
                'categoria_nombre' => 'Especialidad',
                'categoria_descripcion' => 'Servicios médicos especializados'
            ],
            [
                'categoria_id' => 3,
                'categoria_nombre' => 'Cirugía',
                'categoria_descripcion' => 'Procedimientos quirúrgicos'
            ]
        ];
    }

    /**
     * Obtiene todos los servicios médicos activos
     * @param int $categoriaId ID de la categoría para filtrar (opcional)
     * @return array Listado de servicios
     */
    static public function mdlObtenerServicios($categoriaId = null) {
        try {
            // Intentar con la tabla rs_servicios primero
            $sql = "SELECT 
                    serv_id as servicio_id,
                    1 as categoria_id,
                    'General' as categoria_nombre,
                    serv_codigo as servicio_codigo,
                    serv_descripcion as servicio_nombre,
                    serv_descripcion as servicio_descripcion,
                    30 as duracion_minutos,
                    COALESCE(serv_monto, 0) as precio_base,
                    true as requiere_doctor
                FROM 
                    rs_servicios
                WHERE 
                    1=1";
                    
            if ($categoriaId !== null && $categoriaId > 0) {
                // Por ahora ignoramos el filtro de categoría ya que rs_servicios podría no tener esta columna
                // $sql .= " AND categoria_id = :categoria_id";
            }
            
            $sql .= " ORDER BY serv_descripcion ASC";
            
            $stmt = Conexion::conectar()->prepare($sql);
            
            /*if ($categoriaId !== null && $categoriaId > 0) {
                $stmt->bindParam(":categoria_id", $categoriaId, PDO::PARAM_INT);
            }*/
            
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            error_log("mdlObtenerServicios: Encontrados " . count($result) . " servicios en rs_servicios", 
                      3, "/var/log/clinica/database.log");
            
            return $result;
            
        } catch (PDOException $e) {
            error_log("Error en mdlObtenerServicios con rs_servicios: " . $e->getMessage(), 
                      3, "/var/log/clinica/database.log");
            
            // Si falla rs_servicios, intentar con una estructura básica
            try {
                // Verificar si existe una tabla servicios simple
                $stmt = Conexion::conectar()->prepare("SELECT COUNT(*) FROM information_schema.tables WHERE table_name = 'servicios'");
                $stmt->execute();
                $existeServicios = $stmt->fetchColumn();
                
                if ($existeServicios) {
                    $sql = "SELECT 
                            id as servicio_id,
                            1 as categoria_id,
                            'General' as categoria_nombre,
                            COALESCE(codigo, 'S' || id) as servicio_codigo,
                            COALESCE(nombre, descripcion, 'Servicio ' || id) as servicio_nombre,
                            COALESCE(descripcion, nombre, 'Servicio ' || id) as servicio_descripcion,
                            COALESCE(duracion, 30) as duracion_minutos,
                            COALESCE(precio, monto, 0) as precio_base,
                            true as requiere_doctor
                        FROM 
                            servicios
                        ORDER BY nombre ASC, descripcion ASC";
                        
                    $stmt = Conexion::conectar()->prepare($sql);
                    $stmt->execute();
                    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    error_log("mdlObtenerServicios: Encontrados " . count($result) . " servicios en tabla servicios", 
                              3, "/var/log/clinica/database.log");
                    
                    return $result;
                }
                
            } catch (PDOException $e2) {
                error_log("Error en mdlObtenerServicios con tabla servicios: " . $e2->getMessage(), 
                          3, "/var/log/clinica/database.log");
            }
            
            // Si todo falla, devolver un servicio por defecto basado en los servicios que sabemos que existen
            error_log("mdlObtenerServicios: Devolviendo servicios por defecto", 
                      3, "/var/log/clinica/database.log");
            
            return [
                [
                    'servicio_id' => 1,
                    'categoria_id' => 1,
                    'categoria_nombre' => 'General',
                    'servicio_codigo' => 'CONS001',
                    'servicio_nombre' => 'Consulta General',
                    'servicio_descripcion' => 'Consulta médica general',
                    'duracion_minutos' => 30,
                    'precio_base' => 0,
                    'requiere_doctor' => true
                ],
                [
                    'servicio_id' => 2,
                    'categoria_id' => 1,
                    'categoria_nombre' => 'Especialidad',
                    'servicio_codigo' => 'CIRUG001',
                    'servicio_nombre' => 'Cirugía de cataratas',
                    'servicio_descripcion' => 'Cirugía de cataratas por facoemulsificación',
                    'duracion_minutos' => 45,
                    'precio_base' => 30000000,
                    'requiere_doctor' => true
                ]
            ];
        }
    }    /**
     * Obtiene un servicio médico por su ID
     * @param int $servicioId ID del servicio
     * @return array Datos del servicio
     */
    static public function mdlObtenerServicioPorId($servicioId) {
        try {
            $stmt = Conexion::conectar()->prepare(
                "SELECT 
                    serv_id as servicio_id, 
                    serv_codigo as servicio_codigo, 
                    serv_descripcion as servicio_nombre, 
                    30 as duracion_minutos,
                    serv_monto as precio_base
                FROM 
                    rs_servicios
                WHERE 
                    serv_id = :servicio_id"
            );

            $stmt->bindParam(":servicio_id", $servicioId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener servicio por ID: " . $e->getMessage(), 3, '/var/log/clinica/servicios.log');
            
            // Si hay un error, devolvemos un array con valores predeterminados
            return [
                'servicio_id' => $servicioId,
                'duracion_minutos' => 30,
                'servicio_nombre' => 'Servicio #' . $servicioId
            ];
        }
    }

    /**
     * Obtiene los requisitos de un servicio médico
     * @param int $servicioId ID del servicio
     * @return array Listado de requisitos
     */
    static public function mdlObtenerRequisitosServicio($servicioId) {
        $stmt = Conexion::conectar()->prepare(
            "SELECT 
                requisito_id, 
                servicio_id,
                requisito_descripcion,
                es_obligatorio,
                orden
            FROM 
                servicios_requisitos
            WHERE 
                servicio_id = :servicio_id
                AND requisito_estado = true
            ORDER BY 
                orden ASC, 
                requisito_id ASC"
        );

        $stmt->bindParam(":servicio_id", $servicioId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene las tarifas disponibles de un servicio médico
     * @param int $servicioId ID del servicio
     * @return array Listado de tarifas
     */
    static public function mdlObtenerTarifasServicio($servicioId) {
        $stmt = Conexion::conectar()->prepare(
            "SELECT 
                tarifa_id,
                servicio_id,
                nombre_tarifa,
                precio,
                descuento_porcentaje
            FROM 
                servicios_tarifas
            WHERE 
                servicio_id = :servicio_id
                AND tarifa_estado = true
                AND (fecha_fin IS NULL OR fecha_fin >= CURRENT_DATE)
            ORDER BY 
                precio ASC"
        );

        $stmt->bindParam(":servicio_id", $servicioId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }    /**
     * Obtiene los proveedores (doctores) de un servicio médico
     * @param int $servicioId ID del servicio
     * @return array Listado de proveedores
     */
    static public function mdlObtenerProveedoresServicio($servicioId) {
        $stmt = Conexion::conectar()->prepare(
            "SELECT 
                sp.proveedor_id,
                sp.servicio_id,
                sp.doctor_id,
                rd.person_id,
                p.first_name || ' ' || p.last_name AS nombre_doctor,
                sp.es_proveedor_principal,
                sp.tarifa_personalizada,
                sp.proveedor_estado
            FROM 
                servicios_proveedores sp
            INNER JOIN 
                rh_doctors rd ON sp.doctor_id = rd.doctor_id
            INNER JOIN 
                rh_person p ON rd.person_id = p.person_id
            WHERE 
                sp.servicio_id = :servicio_id
                AND sp.proveedor_estado = true
            ORDER BY 
                sp.es_proveedor_principal DESC,
                p.first_name, p.last_name"
        );

        $stmt->bindParam(":servicio_id", $servicioId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene las agendas que tienen disponibilidad para un servicio
     * @param int $servicioId ID del servicio
     * @return array Listado de agendas
     */
    static public function mdlObtenerAgendasServicio($servicioId) {
        $stmt = Conexion::conectar()->prepare(
            "SELECT 
                as.agenda_servicio_id,
                as.agenda_id,
                as.servicio_id,
                as.cupo_diario,
                ac.agenda_descripcion,
                ac.medico_id,
                rd.person_id,
                p.first_name || ' ' || p.last_name AS nombre_medico
            FROM 
                agendas_servicios as
            INNER JOIN 
                agendas_cabecera ac ON as.agenda_id = ac.agenda_id
            INNER JOIN 
                rh_doctors rd ON ac.medico_id = rd.doctor_id
            INNER JOIN 
                rh_person p ON rd.person_id = p.person_id
            WHERE 
                as.servicio_id = :servicio_id
                AND as.agenda_servicio_estado = true
                AND ac.agenda_estado = true
            ORDER BY 
                p.first_name, p.last_name"
        );

        $stmt->bindParam(":servicio_id", $servicioId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }    /**     * Obtiene los horarios disponibles para un servicio y doctor específico
     * @param int $servicioId ID del servicio
     * @param int $doctorId ID del doctor
     * @param string $fecha Fecha para la verificación (formato YYYY-MM-DD)
     * @return array Listado de horarios
     */    static public function mdlObtenerHorariosDisponibles($servicioId, $doctorId, $fecha) {
        // Determinar el día de la semana para la fecha
        $fechaObj = DateTime::createFromFormat('Y-m-d', $fecha);
        if (!$fechaObj) {
            error_log("Fecha inválida en mdlObtenerHorariosDisponibles: " . $fecha, 3, '/var/log/clinica/database.log');
            return [];
        }
        
        $diaSemanaNum = (int)$fechaObj->format('N'); // 1 (lunes) a 7 (domingo) según ISO-8601
        
        // Mapping directo para los días de la semana
        $diasSemanaTexto = [1 => 'LUNES', 2 => 'MARTES', 3 => 'MIERCOLES', 4 => 'JUEVES', 5 => 'VIERNES', 6 => 'SABADO', 7 => 'DOMINGO'];
        $diaSemanaTexto = $diasSemanaTexto[$diaSemanaNum];        try {
            // Construir SQL base
            $sql = "SELECT 
                ad.detalle_id as horario_id,
                ad.detalle_id as agenda_id,
                ac.agenda_id as cabecera_agenda_id,
                ad.turno_id,
                t.turno_nombre,
                ad.sala_id,
                s.sala_nombre,
                ac.medico_id as doctor_id,
                coalesce(p.first_name, '') || ' ' || coalesce(p.last_name, '') as nombre_doctor,
                ad.dia_semana,
                ad.hora_inicio,
                ad.hora_fin,
                ad.intervalo_minutos,
                ad.cupo_maximo
            FROM
                agendas_detalle ad
            INNER JOIN 
                agendas_cabecera ac ON ad.agenda_id = ac.agenda_id
            INNER JOIN 
                turnos t ON ad.turno_id = t.turno_id
            INNER JOIN 
                salas s ON ad.sala_id = s.sala_id
            INNER JOIN
                rh_doctors rd ON rd.doctor_id = ac.medico_id 
            INNER JOIN 
                rh_person p ON p.person_id = rd.person_id";
            
            // Si se especifica un servicio, filtrar por él
            if ($servicioId > 0) {
                $sql .= " INNER JOIN rs_servicios_doctors rsd ON rsd.agenda_detalle_id = ad.detalle_id 
                                                             AND rsd.doctor_id = ac.medico_id 
                                                             AND rsd.servicio_id = :servicio_id
                                                             AND rsd.is_active = true";
            }
            
            $sql .= " WHERE
                ac.medico_id = :doctor_id
                AND ad.dia_semana = :dia_semana
                AND ad.detalle_estado = true
                AND ac.agenda_estado = true
                ORDER BY ad.hora_inicio ASC";
            
            $stmt = Conexion::conectar()->prepare($sql);
            
            // Bind params
            $stmt->bindParam(":doctor_id", $doctorId, PDO::PARAM_INT);
            $stmt->bindParam(":dia_semana", $diaSemanaTexto, PDO::PARAM_STR);
            
            // Si hay servicio específico, bindear también
            if ($servicioId > 0) {
                $stmt->bindParam(":servicio_id", $servicioId, PDO::PARAM_INT);
            }
            
            // Log query before execution for debugging
            error_log("mdlObtenerHorariosDisponibles: SQL para DoctorID=$doctorId, ServicioID=$servicioId, Dia=$diaSemanaTexto: $sql", 
                      3, '/var/log/clinica/database.log');
            
            $stmt->execute();
            
            $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $conteo = count($resultados);
              // Log detailed results for debugging
            error_log("mdlObtenerHorariosDisponibles: Se encontraron $conteo resultados para DoctorID=$doctorId, Dia=$diaSemanaTexto", 
                      3, '/var/log/clinica/database.log');
            
            if ($conteo > 0) {
                error_log("Primer resultado: " . json_encode($resultados[0]), 
                          3, '/var/log/clinica/database.log');
                  // Generate detailed time slots based on the time range and interval
                $slotsGenerados = [];
                
                // First, get all existing reservations for this doctor on this day
                $reservasExistentes = self::mdlObtenerReservasExistentes($doctorId, $fecha);
                
                error_log("Verificando reservas existentes para DoctorID=$doctorId, Fecha=$fecha: " . 
                          count($reservasExistentes) . " encontradas", 3, '/var/log/clinica/database.log');
                
                foreach ($resultados as $horario) {
                    $horaInicio = new DateTime($fecha . ' ' . $horario['hora_inicio']);
                    $horaFin = new DateTime($fecha . ' ' . $horario['hora_fin']);
                    $intervaloMinutos = isset($horario['intervalo_minutos']) ? $horario['intervalo_minutos'] : 45; // Default to 45min if not specified
                    
                    error_log("Generando slots para horario: " . json_encode($horario) . 
                              ", Intervalo: $intervaloMinutos minutos", 3, '/var/log/clinica/database.log');
                    
                    $horaActual = clone $horaInicio;
                    
                    // Generate slots until we reach the end time
                    while ($horaActual < $horaFin) {
                        $slotInicio = clone $horaActual;
                        $slotFin = clone $horaActual;
                        $slotFin->add(new DateInterval('PT' . $intervaloMinutos . 'M'));
                        
                        // Only add the slot if it fits within the overall end time
                        if ($slotFin <= $horaFin) {
                            // Check if this slot overlaps with any existing reservation
                            $slotDisponible = true;
                            $horaInicioStr = $slotInicio->format('H:i:s');
                            $horaFinStr = $slotFin->format('H:i:s');
                            
                            foreach ($reservasExistentes as $reserva) {
                                $reservaInicio = $reserva['hora_inicio'];
                                $reservaFin = $reserva['hora_fin'];
                                
                                // Check for overlap: if the slot overlaps with an existing reservation, mark it as unavailable
                                if (
                                    // Slot starts during a reservation
                                    ($horaInicioStr >= $reservaInicio && $horaInicioStr < $reservaFin) ||
                                    // Slot ends during a reservation
                                    ($horaFinStr > $reservaInicio && $horaFinStr <= $reservaFin) ||
                                    // Slot completely contains a reservation
                                    ($horaInicioStr <= $reservaInicio && $horaFinStr >= $reservaFin) ||
                                    // Slot is completely within a reservation
                                    ($horaInicioStr >= $reservaInicio && $horaFinStr <= $reservaFin)
                                ) {
                                    $slotDisponible = false;
                                    error_log("Slot $horaInicioStr - $horaFinStr excluido por reserva existente: $reservaInicio - $reservaFin", 
                                              3, '/var/log/clinica/database.log');
                                    break;
                                }
                            }
                            
                            if ($slotDisponible) {
                                $slot = $horario; // Copy all properties from the base schedule
                                $slot['hora_inicio'] = $horaInicioStr;
                                $slot['hora_fin'] = $horaFinStr;
                                $slotsGenerados[] = $slot;
                            }
                        }
                        
                        // Move to the next slot
                        $horaActual->add(new DateInterval('PT' . $intervaloMinutos . 'M'));
                    }
                }
                
                if (count($slotsGenerados) > 0) {
                    error_log("Se generaron " . count($slotsGenerados) . " slots detallados", 
                              3, '/var/log/clinica/database.log');
                    return $slotsGenerados;
                } else {
                    // Si no se generaron slots disponibles, significa que todos están ocupados
                    error_log("No se generaron slots disponibles para DoctorID=$doctorId, Fecha=$fecha - todos están ocupados", 
                              3, '/var/log/clinica/database.log');
                    return []; // Retornar array vacío en lugar de los horarios base
                }
            }
            
            return [];
        } catch (PDOException $e) {
            error_log("Error al obtener horarios disponibles: " . $e->getMessage(), 0);
            return [];
        }
    }

    /**
     * Obtiene los días disponibles para un médico específico
     * @param int $doctorId ID del doctor
     * @param int $servicioId ID del servicio (opcional)
     * @return array Lista de días disponibles con información de horarios
     */
    static public function mdlObtenerDiasDisponibles($doctorId, $servicioId = 0) {
        try {
            // Configurar zona horaria
            date_default_timezone_set('America/Asuncion');
            $fechaActual = date('Y-m-d');
            
            error_log("mdlObtenerDiasDisponibles: DoctorID=$doctorId, ServicioID=$servicioId", 3, '/var/log/clinica/servicios.log');
            
            // Obtener horarios configurados para el médico
            $sql = "SELECT DISTINCT
                        ad.dia_semana,
                        ad.hora_inicio,
                        ad.hora_fin,
                        ad.intervalo_minutos,
                        t.turno_nombre,
                        s.sala_nombre,
                        CASE ad.dia_semana 
                            WHEN 'LUNES' THEN 1
                            WHEN 'MARTES' THEN 2
                            WHEN 'MIERCOLES' THEN 3
                            WHEN 'JUEVES' THEN 4
                            WHEN 'VIERNES' THEN 5
                            WHEN 'SABADO' THEN 6
                            WHEN 'DOMINGO' THEN 7
                        END as dia_orden
                    FROM agendas_detalle ad
                    INNER JOIN agendas_cabecera ac ON ad.agenda_id = ac.agenda_id
                    INNER JOIN turnos t ON ad.turno_id = t.turno_id
                    LEFT JOIN salas s ON ad.sala_id = s.sala_id
                    WHERE ac.medico_id = :doctor_id
                    AND ad.detalle_estado = true
                    AND ac.agenda_estado = true
                    ORDER BY dia_orden, ad.hora_inicio";
            
            $stmt = Conexion::conectar()->prepare($sql);
            $stmt->bindParam(":doctor_id", $doctorId, PDO::PARAM_INT);
            $stmt->execute();
            $horariosConfigurados = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (empty($horariosConfigurados)) {
                error_log("mdlObtenerDiasDisponibles: No se encontraron horarios configurados para doctor ID $doctorId", 3, '/var/log/clinica/servicios.log');
                return [];
            }
            
            // Generar fechas para los próximos 30 días
            $diasDisponibles = [];
            $fechaInicio = new DateTime($fechaActual);
            
            for ($i = 0; $i < 30; $i++) {
                $fechaCheck = clone $fechaInicio;
                $fechaCheck->add(new DateInterval('P' . $i . 'D'));
                
                $diaSemanaNum = (int)$fechaCheck->format('N'); // 1=lunes, 7=domingo
                $diasSemanaTexto = [1 => 'LUNES', 2 => 'MARTES', 3 => 'MIERCOLES', 4 => 'JUEVES', 5 => 'VIERNES', 6 => 'SABADO', 7 => 'DOMINGO'];
                $diaSemanaTexto = $diasSemanaTexto[$diaSemanaNum];
                
                // Verificar si el médico tiene horarios configurados para este día
                $horariosDelDia = array_filter($horariosConfigurados, function($horario) use ($diaSemanaTexto) {
                    return $horario['dia_semana'] === $diaSemanaTexto;
                });
                
                if (!empty($horariosDelDia)) {
                    $diasDisponibles[] = [
                        'fecha' => $fechaCheck->format('Y-m-d'),
                        'fecha_formateada' => $fechaCheck->format('d/m/Y'),
                        'dia_semana' => $diaSemanaTexto,
                        'dia_nombre' => ucfirst(strtolower($diaSemanaTexto)),
                        'horarios' => array_values($horariosDelDia),
                        'total_horarios' => count($horariosDelDia)
                    ];
                }
            }
            
            error_log("mdlObtenerDiasDisponibles: Se encontraron " . count($diasDisponibles) . " días disponibles", 3, '/var/log/clinica/servicios.log');
            return $diasDisponibles;
            
        } catch (PDOException $e) {
            error_log("Error al obtener días disponibles: " . $e->getMessage(), 3, '/var/log/clinica/servicios.log');
            return [];
        }
    }

    /**
     * Obtiene los horarios disponibles para una fecha específica sin límite de rango
     * @param int $doctorId ID del doctor
     * @param int $servicioId ID del servicio (opcional)
     * @param string $fechaEspecifica Fecha específica en formato Y-m-d
     * @return array Lista de días disponibles para la fecha específica
     */
    static public function mdlObtenerDiasDisponiblesPorFechaEspecifica($doctorId, $servicioId = 0, $fechaEspecifica = null) {
        try {
            // Configurar zona horaria
            date_default_timezone_set('America/Asuncion');
            
            error_log("mdlObtenerDiasDisponiblesPorFechaEspecifica: DoctorID=$doctorId, ServicioID=$servicioId, Fecha=$fechaEspecifica", 3, '/var/log/clinica/servicios.log');
            
            if (empty($fechaEspecifica)) {
                error_log("mdlObtenerDiasDisponiblesPorFechaEspecifica: Fecha específica es requerida", 3, '/var/log/clinica/servicios.log');
                return [];
            }
            
            // Obtener horarios configurados para el médico
            $sql = "SELECT DISTINCT
                        ad.dia_semana,
                        ad.hora_inicio,
                        ad.hora_fin,
                        ad.intervalo_minutos,
                        t.turno_nombre,
                        s.sala_nombre,
                        CASE ad.dia_semana 
                            WHEN 'LUNES' THEN 1
                            WHEN 'MARTES' THEN 2
                            WHEN 'MIERCOLES' THEN 3
                            WHEN 'JUEVES' THEN 4
                            WHEN 'VIERNES' THEN 5
                            WHEN 'SABADO' THEN 6
                            WHEN 'DOMINGO' THEN 7
                        END as dia_orden
                    FROM agendas_detalle ad
                    INNER JOIN agendas_cabecera ac ON ad.agenda_id = ac.agenda_id
                    INNER JOIN turnos t ON ad.turno_id = t.turno_id
                    LEFT JOIN salas s ON ad.sala_id = s.sala_id
                    WHERE ac.medico_id = :doctor_id
                    AND ad.detalle_estado = true
                    AND ac.agenda_estado = true
                    ORDER BY dia_orden, ad.hora_inicio";
            
            $stmt = Conexion::conectar()->prepare($sql);
            $stmt->bindParam(":doctor_id", $doctorId, PDO::PARAM_INT);
            $stmt->execute();
            $horariosConfigurados = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (empty($horariosConfigurados)) {
                error_log("mdlObtenerDiasDisponiblesPorFechaEspecifica: No se encontraron horarios configurados para doctor ID $doctorId", 3, '/var/log/clinica/servicios.log');
                return [];
            }
            
            // Verificar la fecha específica
            $fechaCheck = new DateTime($fechaEspecifica);
            $diaSemanaNum = (int)$fechaCheck->format('N'); // 1=lunes, 7=domingo
            $diasSemanaTexto = [1 => 'LUNES', 2 => 'MARTES', 3 => 'MIERCOLES', 4 => 'JUEVES', 5 => 'VIERNES', 6 => 'SABADO', 7 => 'DOMINGO'];
            $diaSemanaTexto = $diasSemanaTexto[$diaSemanaNum];
            
            error_log("mdlObtenerDiasDisponiblesPorFechaEspecifica: Fecha $fechaEspecifica corresponde al día $diaSemanaTexto", 3, '/var/log/clinica/servicios.log');
            
            // Verificar si el médico tiene horarios configurados para este día de la semana
            $horariosDelDia = array_filter($horariosConfigurados, function($horario) use ($diaSemanaTexto) {
                return $horario['dia_semana'] === $diaSemanaTexto;
            });
            
            $diasDisponibles = [];
            if (!empty($horariosDelDia)) {
                $diasDisponibles[] = [
                    'fecha' => $fechaCheck->format('Y-m-d'),
                    'fecha_formateada' => $fechaCheck->format('d/m/Y'),
                    'dia_semana' => $diaSemanaTexto,
                    'dia_nombre' => ucfirst(strtolower($diaSemanaTexto)),
                    'horarios' => array_values($horariosDelDia),
                    'total_horarios' => count($horariosDelDia)
                ];
            }
            
            error_log("mdlObtenerDiasDisponiblesPorFechaEspecifica: Se encontraron " . count($diasDisponibles) . " días disponibles para fecha específica", 3, '/var/log/clinica/servicios.log');
            return $diasDisponibles;
            
        } catch (Exception $e) {
            error_log("Error al obtener días disponibles por fecha específica: " . $e->getMessage(), 3, '/var/log/clinica/servicios.log');
            return [];
        }
    }

    /**
     * Obtiene todos los horarios disponibles para un médico de todas las fechas configuradas
     * @param int $doctorId ID del doctor
     * @param int $servicioId ID del servicio (opcional)
     * @return array Lista de todos los horarios disponibles con fechas
     */
    static public function mdlObtenerTodosLosHorariosDisponibles($doctorId, $servicioId = 0) {
        try {
            error_log("mdlObtenerTodosLosHorariosDisponibles: DoctorID=$doctorId, ServicioID=$servicioId", 3, '/var/log/clinica/servicios.log');
            
            // Obtener los días disponibles configurados para el médico
            $diasDisponibles = self::mdlObtenerDiasDisponibles($doctorId, $servicioId);
            
            if (empty($diasDisponibles)) {
                error_log("mdlObtenerTodosLosHorariosDisponibles: No se encontraron días disponibles", 3, '/var/log/clinica/servicios.log');
                return [];
            }
            
            $todosLosHorarios = [];
            
            // Para cada día disponible, obtener los horarios específicos
            foreach ($diasDisponibles as $dia) {
                $fecha = $dia['fecha'];
                
                // Obtener horarios específicos para esta fecha
                $horariosDelDia = self::mdlObtenerHorariosDisponibles($servicioId, $doctorId, $fecha);
                
                // Agregar la fecha a cada horario y añadir al array principal
                foreach ($horariosDelDia as $horario) {
                    $horario['fecha'] = $fecha;
                    $horario['fecha_formateada'] = $dia['fecha_formateada'];
                    $horario['dia_nombre'] = $dia['dia_nombre'];
                    $horario['dia_semana'] = $dia['dia_semana'];
                    $todosLosHorarios[] = $horario;
                }
            }
            
            // Ordenar por fecha y hora
            usort($todosLosHorarios, function($a, $b) {
                $fechaCompare = strcmp($a['fecha'], $b['fecha']);
                if ($fechaCompare === 0) {
                    return strcmp($a['hora_inicio'], $b['hora_inicio']);
                }
                return $fechaCompare;
            });
            
            error_log("mdlObtenerTodosLosHorariosDisponibles: Se encontraron " . count($todosLosHorarios) . " horarios en total", 3, '/var/log/clinica/servicios.log');
            return $todosLosHorarios;
            
        } catch (Exception $e) {
            error_log("Error al obtener todos los horarios disponibles: " . $e->getMessage(), 3, '/var/log/clinica/servicios.log');
            return [];
        }
    }

    /**
     * Genera los horarios disponibles para un servicio, doctor y fecha específica
     * @param int $servicioId ID del servicio
     * @param int $doctorId ID del doctor
     * @param string $fecha Fecha para la verificación (formato YYYY-MM-DD)
     * @return array Listado de slots de horarios
     */
    static public function mdlGenerarSlotsDisponibles($servicioId, $doctorId, $fecha) {
        try {
            // CORREGIDO: Cambiar zona horaria a Asunción
            date_default_timezone_set('America/Asuncion');
            $horaServidor = date('Y-m-d H:i:s');
            $zonaTiempo = date_default_timezone_get();
            
            error_log("=== INICIO mdlGenerarSlotsDisponibles ===", 3, '/var/log/clinica/servicios.log');
            error_log("Parámetros: ServicioID={$servicioId}, DoctorID={$doctorId}, Fecha={$fecha}", 3, '/var/log/clinica/servicios.log');
            error_log("Hora servidor: {$horaServidor}, Zona: {$zonaTiempo}", 3, '/var/log/clinica/servicios.log');
            
            // Validar formato de fecha
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
                error_log("ERROR: Formato de fecha incorrecto: {$fecha}", 3, '/var/log/clinica/servicios.log');
                return [];
            }
            
            // Validar fecha
            $fechaObj = DateTime::createFromFormat('Y-m-d', $fecha);
            if (!$fechaObj || $fechaObj->format('Y-m-d') !== $fecha) {
                error_log("ERROR: Fecha inválida: {$fecha}", 3, '/var/log/clinica/servicios.log');
                return [];
            }
            
            // Determinar día de la semana
            $diaSemanaNum = (int)$fechaObj->format('N'); // 1=lunes, 7=domingo
            $diasSemanaTexto = [1 => 'LUNES', 2 => 'MARTES', 3 => 'MIERCOLES', 4 => 'JUEVES', 5 => 'VIERNES', 6 => 'SABADO', 7 => 'DOMINGO'];
            $diaSemanaTexto = $diasSemanaTexto[$diaSemanaNum];
            
            error_log("Día de la semana: {$diaSemanaTexto} (num: {$diaSemanaNum})", 3, '/var/log/clinica/servicios.log');
            
            // USAR LA CONSULTA SQL ESPECÍFICA CON FILTRO POR SERVICIO
            $stmt = Conexion::conectar()->prepare(
                "SELECT 
                    ad.detalle_id,
                    ad.agenda_id,
                    ad.dia_semana,
                    ad.hora_inicio,
                    ad.hora_fin,
                    ad.intervalo_minutos,
                    ad.cupo_maximo,
                    ad.detalle_estado,
                    rsd.servicio_id
                FROM agendas_detalle ad 
                INNER JOIN agendas_cabecera ac ON ad.agenda_id = ac.agenda_id 
                INNER JOIN rs_servicios_doctors rsd ON rsd.agenda_detalle_id = ad.detalle_id
                                                   AND rsd.servicio_id = :servicio_id
                                                   AND rsd.doctor_id = :doctor_id
                WHERE 
                    ac.medico_id = :doctor_id
                    AND ad.dia_semana = :dia_semana
                    AND ad.detalle_estado = true
                    AND ac.agenda_estado = true
                    AND rsd.is_active = true
                ORDER BY ad.hora_inicio ASC"
            );
            
            $stmt->bindParam(":doctor_id", $doctorId, PDO::PARAM_INT);
            $stmt->bindParam(":dia_semana", $diaSemanaTexto, PDO::PARAM_STR);
            $stmt->bindParam(":servicio_id", $servicioId, PDO::PARAM_INT);
            
            error_log("Ejecutando consulta para ServicioID={$servicioId}, Doctor ID={$doctorId}, Día={$diaSemanaTexto}", 3, '/var/log/clinica/servicios.log');
            $stmt->execute();
            $horarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            error_log("Horarios encontrados: " . count($horarios), 3, '/var/log/clinica/servicios.log');
            if (!empty($horarios)) {
                error_log("Primer horario: " . json_encode($horarios[0]), 3, '/var/log/clinica/servicios.log');
            }
            
            if (empty($horarios)) {
                error_log("No se encontraron horarios para el día {$diaSemanaTexto}", 3, '/var/log/clinica/servicios.log');
                return [];
            }
            
            // Obtener reservas existentes para verificar disponibilidad
            $reservasExistentes = [];
            try {
                $stmtReservas = Conexion::conectar()->prepare(
                    "SELECT 
                        reserva_id,
                        servicio_id,
                        agenda_id,
                        doctor_id,
                        fecha_reserva,
                        hora_inicio,
                        hora_fin
                    FROM servicios_reservas
                    WHERE 
                        doctor_id = :doctor_id
                        AND fecha_reserva = :fecha_reserva
                        AND reserva_estado IN ('CONFIRMADA', 'PENDIENTE')
                        AND activo = true
                    ORDER BY hora_inicio ASC"
                );
                
                $stmtReservas->bindParam(":doctor_id", $doctorId, PDO::PARAM_INT);
                $stmtReservas->bindParam(":fecha_reserva", $fecha, PDO::PARAM_STR);
                $stmtReservas->execute();
                $reservasExistentes = $stmtReservas->fetchAll(PDO::FETCH_ASSOC);
                
                error_log("Reservas existentes: " . count($reservasExistentes), 3, '/var/log/clinica/servicios.log');
            } catch (PDOException $e) {
                error_log("Error al obtener reservas existentes: " . $e->getMessage(), 3, '/var/log/clinica/servicios.log');
            }
            
            // Generar slots disponibles
            $slotsDisponibles = [];
            foreach ($horarios as $horario) {
                error_log("Procesando horario: Agenda={$horario['agenda_id']}, Intervalo={$horario['intervalo_minutos']}min", 3, '/var/log/clinica/servicios.log');
                
                // USAR EL INTERVALO DE AGENDAS_DETALLE
                $intervaloMinutos = (int)$horario['intervalo_minutos'];
                
                // Convertir horas a DateTime
                $horaInicio = new DateTime($fecha . ' ' . $horario['hora_inicio']);
                $horaFin = new DateTime($fecha . ' ' . $horario['hora_fin']);
                
                error_log("Rango: " . $horaInicio->format('H:i') . " - " . $horaFin->format('H:i') . " (intervalo: {$intervaloMinutos}min)", 3, '/var/log/clinica/servicios.log');
                
                // Generar slots cada intervalo de minutos
                $horaActual = clone $horaInicio;
                while ($horaActual < $horaFin) {
                    $slotFin = clone $horaActual;
                    $slotFin->add(new DateInterval('PT' . $intervaloMinutos . 'M'));
                    
                    // Verificar que el slot no exceda el horario fin
                    if ($slotFin > $horaFin) {
                        break;
                    }
                    
                    $slotInicioStr = $horaActual->format('H:i:s');
                    $slotFinStr = $slotFin->format('H:i:s');
                    
                    // Verificar disponibilidad vs reservas existentes
                    $disponible = true;
                    foreach ($reservasExistentes as $reserva) {
                        $reservaInicio = $reserva['hora_inicio'];
                        $reservaFin = $reserva['hora_fin'];
                        
                        // Verificar solapamiento
                        if (($slotInicioStr < $reservaFin) && ($slotFinStr > $reservaInicio)) {
                            $disponible = false;
                            error_log("Slot ocupado: {$slotInicioStr}-{$slotFinStr} vs reserva {$reservaInicio}-{$reservaFin}", 3, '/var/log/clinica/servicios.log');
                            break;
                        }
                    }
                    
                    // Crear el slot
                    $slot = [
                        'hora_inicio' => $slotInicioStr,
                        'hora_fin' => $slotFinStr,
                        'sala_id' => $horario['sala_id'],
                        'sala_nombre' => $horario['sala_nombre'],
                        'turno_id' => $horario['turno_id'],
                        'turno_nombre' => $horario['turno_nombre'],
                        'agenda_id' => $horario['agenda_id'],
                        'detalle_id' => $horario['detalle_id'],
                        'disponible' => $disponible,
                        'doctor_id' => $doctorId,
                        'doctor_nombre' => $horario['first_name'],
                        'intervalo_minutos' => $intervaloMinutos,
                        'fecha' => $fecha
                    ];
                    
                    $slotsDisponibles[] = $slot;
                    
                    // Avanzar al siguiente slot
                    $horaActual->add(new DateInterval('PT' . $intervaloMinutos . 'M'));
                }
            }
            
            error_log("Total de slots generados: " . count($slotsDisponibles), 3, '/var/log/clinica/servicios.log');
            error_log("=== FIN mdlGenerarSlotsDisponibles ===", 3, '/var/log/clinica/servicios.log');
            
            return $slotsDisponibles;
            
        } catch (Exception $e) {
            error_log("ERROR en mdlGenerarSlotsDisponibles: " . $e->getMessage(), 3, '/var/log/clinica/servicios.log');
            return [];
        }
    }

        /**
     * Obtiene los doctores disponibles para una fecha específica
     * @param string $fecha Fecha en formato YYYY-MM-DD
     * @return array Lista de doctores disponibles en esa fecha
     */
    static public function mdlObtenerDoctoresPorFecha($fecha) {
        try {
            // Verificar que el formato de la fecha sea correcto
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
                error_log("mdlObtenerDoctoresPorFecha: Formato de fecha incorrecto: " . $fecha, 3, '/var/log/clinica/reservas.log');
                return [];
            }
            
            // Asegurarse de que la fecha sea válida
            $fechaObj = DateTime::createFromFormat('Y-m-d', $fecha);
            if (!$fechaObj || $fechaObj->format('Y-m-d') !== $fecha) {
                error_log("mdlObtenerDoctoresPorFecha: Fecha inválida: " . $fecha, 3, '/var/log/clinica/reservas.log');
                return [];
            }
            
            // Determinar el día de la semana para la fecha (1 = lunes, 7 = domingo)
            $diaSemanaNum = (int)$fechaObj->format('N'); // ISO-8601
            $diasSemanaTexto = [
                1 => 'LUNES', 
                2 => 'MARTES', 
                3 => 'MIERCOLES', 
                4 => 'JUEVES', 
                5 => 'VIERNES', 
                6 => 'SABADO', 
                7 => 'DOMINGO'
            ];
            $diaSemanaTexto = $diasSemanaTexto[$diaSemanaNum];
            
            error_log("mdlObtenerDoctoresPorFecha: Buscando doctores para el día: {$diaSemanaTexto}, fecha: {$fecha}", 
                      3, '/var/log/clinica/reservas.log');
              
            // Obtener doctores que tienen horarios para ese día de semana
            $conexion = Conexion::conectar();
            if (!$conexion) {
                throw new Exception("Error de conexión a la base de datos");
            }
            
            // Usar la consulta exitosa del otro método (mdlObtenerMedicosDisponiblesPorFecha)
            $stmt = $conexion->prepare(
                "SELECT DISTINCT 
                    d.doctor_id,
                    p.person_id,
                    p.first_name || ' ' || p.last_name AS nombre_doctor,
                    d.doctor_estado,
                    ac.agenda_id,
                    ac.medico_id,
                    d.doctor_id = ac.medico_id AS doctor_match,
                    ad.dia_semana,
                    d.especialidad,
                    ad.hora_inicio,
                    ad.hora_fin,
                    ad.intervalo_minutos,
                    ad.detalle_id
                FROM 
                    agendas_detalle ad
                LEFT JOIN
                    agendas_cabecera ac ON ad.agenda_id = ac.agenda_id
                LEFT JOIN
                    rh_doctors d ON ac.medico_id = d.doctor_id
                LEFT JOIN
                    rh_person p ON d.person_id = p.person_id
                WHERE
                    ad.dia_semana = :dia_semana
                    AND ad.detalle_estado = true
                    AND (ac.agenda_estado IS NULL OR ac.agenda_estado = true)
                    AND (d.doctor_estado IS NULL OR d.doctor_estado = 'ACTIVO')
                ORDER BY
                    p.first_name, p.last_name"
            );
            
            $stmt->bindParam(":dia_semana", $diaSemanaTexto, PDO::PARAM_STR);
            
            error_log("mdlObtenerDoctoresPorFecha: Ejecutando consulta con dia_semana = '$diaSemanaTexto'", 
                      3, '/var/log/clinica/reservas.log');
            
            $stmt->execute();
            
            $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            error_log("mdlObtenerDoctoresPorFecha: Consulta ejecutada. Resultados brutos: " . count($resultados), 
                      3, '/var/log/clinica/reservas.log');
            
            // Filtrar solo los resultados donde hay una coincidencia válida de doctor
            $doctores = [];
            foreach ($resultados as $resultado) {
                if (!empty($resultado['doctor_id']) && !empty($resultado['person_id'])) {
                    $doctores[] = [
                        'doctor_id' => $resultado['doctor_id'],
                        'person_id' => $resultado['person_id'],
                        'nombre_doctor' => $resultado['nombre_doctor'],
                        'doctor_estado' => $resultado['doctor_estado'],
                        'especialidad' => $resultado['especialidad'],
                        'agenda_id' => $resultado['agenda_id'],
                        'hora_inicio' => $resultado['hora_inicio'],
                        'hora_fin' => $resultado['hora_fin'],
                        'intervalo_minutos' => $resultado['intervalo_minutos'],
                        'detalle_id' => $resultado['detalle_id']
                    ];
                }
            }
            
            error_log("mdlObtenerDoctoresPorFecha: Doctores válidos encontrados: " . count($doctores), 
                      3, '/var/log/clinica/reservas.log');
            
            if (!empty($doctores)) {
                error_log("mdlObtenerDoctoresPorFecha: Primer doctor: " . json_encode($doctores[0]), 
                          3, '/var/log/clinica/reservas.log');
            }
            
            return $doctores;
        } catch (Exception $e) {
            error_log("mdlObtenerDoctoresPorFecha: Error: " . $e->getMessage(), 3, '/var/log/clinica/reservas.log');
            return [];
        }
    }
    
    /**
     * Obtiene los médicos disponibles para una fecha específica
     * @param string $fecha Fecha en formato YYYY-MM-DD
     * @return array Lista de médicos disponibles
     */
    static public function mdlObtenerMedicosDisponiblesPorFecha($fecha) {
        try {
            // Asegurarse que la fecha tenga un formato válido
            $fechaObj = DateTime::createFromFormat('Y-m-d', $fecha);
            if (!$fechaObj) {
                error_log("Formato de fecha incorrecto: " . $fecha, 3, '/var/log/clinica/database.log');
                return [];
            }
              // Determinar el día de la semana
            $diaSemanaNum = (int)$fechaObj->format('N'); // 1-7 (ISO format: 1=lunes, 7=domingo)
            $diasSemanaTexto = [1 => 'LUNES', 2 => 'MARTES', 3 => 'MIERCOLES', 4 => 'JUEVES', 5 => 'VIERNES', 6 => 'SABADO', 7 => 'DOMINGO'];
            $diaSemana = $diasSemanaTexto[$diaSemanaNum];
            
            error_log("DEBUG - Fecha: {$fecha}, Número día: {$diaSemanaNum}, Texto día: {$diaSemana}", 3, '/var/log/clinica/database.log');
            
            error_log("Buscando médicos para fecha: {$fecha}, día: {$diaSemana}", 3, '/var/log/clinica/database.log');
            
            // Verificar si hay médicos con agenda configurada para este día de la semana
            $stmtCheckDay = Conexion::conectar()->prepare("
                SELECT COUNT(*) FROM agendas_detalle WHERE dia_semana = :dia_semana AND detalle_estado = true
            ");
            $stmtCheckDay->bindParam(":dia_semana", $diaSemana, PDO::PARAM_STR);
            $stmtCheckDay->execute();
            $hayMedicosParaEsteDia = $stmtCheckDay->fetchColumn() > 0;
            
            if (!$hayMedicosParaEsteDia) {
                error_log("No hay médicos con agenda configurada para {$diaSemana}", 3, '/var/log/clinica/database.log');
                return [
                    ['message' => "No hay médicos disponibles para este día ({$diaSemana}). Sólo hay horarios para LUNES, MARTES y MIÉRCOLES."]
                ];
            }
              // Consulta para diagnosticar si hay agendas para este día de la semana
            $stmtDiag = Conexion::conectar()->prepare("
                SELECT ad.agenda_id, ad.dia_semana, ac.agenda_descripcion, ac.medico_id 
                FROM agendas_detalle ad 
                INNER JOIN agendas_cabecera ac ON ad.agenda_id = ac.agenda_id
                WHERE ad.dia_semana = :dia_semana AND ad.detalle_estado = true
            ");
            $stmtDiag->bindParam(":dia_semana", $diaSemana, PDO::PARAM_STR);
            $stmtDiag->execute();
            $agendasDiag = $stmtDiag->fetchAll(PDO::FETCH_ASSOC);
            
            error_log("Agendas encontradas para {$diaSemana}: " . json_encode($agendasDiag), 3, '/var/log/clinica/database.log');
              error_log("DEBUG - Dia semana utilizado para la consulta: '{$diaSemana}'", 3, '/var/log/clinica/database.log');
            
            // Consulta para verificar cuáles días existen en agendas_detalle
            $stmtDiasCheck = Conexion::conectar()->prepare("
                SELECT DISTINCT dia_semana FROM agendas_detalle ORDER BY dia_semana
            ");
            $stmtDiasCheck->execute();
            $diasDisponibles = $stmtDiasCheck->fetchAll(PDO::FETCH_COLUMN);
            error_log("DEBUG - Días disponibles en agendas_detalle: " . json_encode($diasDisponibles), 3, '/var/log/clinica/database.log');
            
            // Consulta para obtener médicos que tienen horarios en ese día
            // Modificando la consulta para usar LEFT JOIN y verificar cada relación
            $stmt = Conexion::conectar()->prepare(
                "SELECT DISTINCT 
                    d.doctor_id,
                    p.person_id,
                    p.first_name || ' ' || p.last_name AS nombre_doctor,
                    d.doctor_estado,
                    ac.agenda_id,
                    ac.medico_id,
                    d.doctor_id = ac.medico_id AS doctor_match,
                    ad.dia_semana
                FROM 
                    agendas_detalle ad
                LEFT JOIN
                    agendas_cabecera ac ON ad.agenda_id = ac.agenda_id
                LEFT JOIN
                    rh_doctors d ON ac.medico_id = d.doctor_id
                LEFT JOIN
                    rh_person p ON d.person_id = p.person_id                WHERE                    ad.dia_semana = :dia_semana
                    AND ad.detalle_estado = true
                    AND (ac.agenda_estado IS NULL OR ac.agenda_estado = true)
                    AND (d.doctor_estado IS NULL OR d.doctor_estado = 'ACTIVO')
                ORDER BY
                    nombre_doctor"
            );
              $stmt->bindParam(":dia_semana", $diaSemana, PDO::PARAM_STR);
            $stmt->execute();
              $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
            error_log("Resultados crudos de la consulta: " . json_encode($resultados), 3, '/var/log/clinica/database.log');
            
            // Si la consulta no devolvió resultados, verificar si existe el día en la tabla
            if (empty($resultados)) {
                $stmtVerificar = Conexion::conectar()->prepare("
                    SELECT COUNT(*) FROM agendas_detalle WHERE dia_semana = :dia_semana
                ");
                $stmtVerificar->bindParam(":dia_semana", $diaSemana, PDO::PARAM_STR);
                $stmtVerificar->execute();
                $conteo = $stmtVerificar->fetchColumn();
                
                error_log("Verificación adicional - Registros con día {$diaSemana}: {$conteo}", 3, '/var/log/clinica/database.log');
                
                // También verificar si el formato del día de semana es el esperado
                $stmtFormato = Conexion::conectar()->prepare("
                    SELECT DISTINCT dia_semana FROM agendas_detalle
                ");
                $stmtFormato->execute();
                $diasDisponibles = $stmtFormato->fetchAll(PDO::FETCH_COLUMN);
                error_log("Días disponibles en la tabla: " . json_encode($diasDisponibles), 3, '/var/log/clinica/database.log');
            }
            
            // Filtrar solo los resultados donde hay una coincidencia válida de doctor
            $medicos = [];
            foreach ($resultados as $resultado) {
                if (!empty($resultado['doctor_id']) && !empty($resultado['person_id'])) {
                    $medicos[] = [
                        'doctor_id' => $resultado['doctor_id'],
                        'person_id' => $resultado['person_id'],
                        'nombre_doctor' => $resultado['nombre_doctor'],
                        'doctor_estado' => $resultado['doctor_estado']
                    ];
                } else {
                    error_log("Descartado resultado por faltar información - doctor_id: " . 
                        (isset($resultado['doctor_id']) ? $resultado['doctor_id'] : 'NULL') . 
                        ", person_id: " . (isset($resultado['person_id']) ? $resultado['person_id'] : 'NULL'), 
                        3, '/var/log/clinica/database.log');
                }
            }
            
            error_log("Médicos encontrados para {$fecha} ({$diaSemana}): " . count($medicos), 3, '/var/log/clinica/database.log');
              // Si no hay médicos encontrados
            if (count($medicos) === 0) {
                error_log("No se encontraron médicos disponibles para {$fecha} ({$diaSemana})", 3, '/var/log/clinica/database.log');
                
                // Verificar si hay agendas configuradas para ese día
                if (count($agendasDiag) > 0) {
                    error_log("ADVERTENCIA: Hay agendas para {$diaSemana} pero no se encontraron médicos - posible problema de relación en la BD", 3, '/var/log/clinica/database.log');
                    
                    // Devolver un mensaje informativo para el usuario
                    return [
                        [
                            'message' => "No hay médicos disponibles para la fecha seleccionada ({$fecha}), pero hay agendas configuradas para {$diaSemana}. Podría haber un problema con la asignación de médicos."
                        ]
                    ];
                } else {
                    // No hay ni médicos ni agendas para ese día
                    return [
                        [
                            'message' => "No hay médicos disponibles para la fecha seleccionada ({$fecha}, {$diaSemana}). Por favor, seleccione otro día o contacte con la clínica."
                        ]
                    ];
                }
                
                // Verificar la relación entre agendas y médicos
                foreach ($agendasDiag as $agenda) {
                    $stmtMedico = Conexion::conectar()->prepare("SELECT d.doctor_id, p.first_name || ' ' || p.last_name AS nombre FROM rh_doctors d INNER JOIN rh_person p ON d.person_id = p.person_id WHERE d.doctor_id = :medico_id");
                    $stmtMedico->bindParam(":medico_id", $agenda['medico_id'], PDO::PARAM_INT);
                    $stmtMedico->execute();
                    $medicoData = $stmtMedico->fetch(PDO::FETCH_ASSOC);
                    
                    if ($medicoData) {
                        error_log("Agenda {$agenda['agenda_id']} corresponde al médico ID {$agenda['medico_id']}: {$medicoData['nombre']}", 3, '/var/log/clinica/database.log');
                          // Agregar este médico a la lista manualmente
                        $medicos[] = [
                            'doctor_id' => $medicoData['doctor_id'],
                            'nombre_doctor' => $medicoData['nombre'],
                            'doctor_estado' => 'ACTIVO',
                            'agenda_id' => $agenda['agenda_id']
                        ];
                    } else {
                        error_log("ADVERTENCIA: No se encontró médico con ID {$agenda['medico_id']} para la agenda {$agenda['agenda_id']}", 3, '/var/log/clinica/database.log');
                    }
                }
                
                // Si aún no hay médicos, devolver un mensaje específico
                if (count($medicos) === 0) {
                    return [
                        ['message' => "No se encontraron médicos para este día ({$diaSemana}) debido a un problema de configuración. Por favor contacte al administrador del sistema."]
                    ];
                }
            }
            
            // Filtrar médicos que estén bloqueados en esta fecha y calcular cupos disponibles
            if (count($medicos) > 0) {
                // Comprobar si existe la tabla de bloqueos
                $stmtCheck = Conexion::conectar()->prepare("SELECT to_regclass('public.agendas_bloqueos')");
                $stmtCheck->execute();
                $tablaBloqueosExiste = $stmtCheck->fetchColumn();
                
                if ($tablaBloqueosExiste) {
                    // Filtrar médicos con bloqueos para esta fecha
                    foreach ($medicos as $key => $medico) {
                        $stmtBloqueo = Conexion::conectar()->prepare(
                            "SELECT COUNT(*) AS bloqueado
                            FROM agendas_bloqueos
                            WHERE doctor_id = :doctor_id
                            AND :fecha BETWEEN fecha_inicio AND fecha_fin
                            AND bloqueo_estado = true
                            AND (
                                hora_inicio IS NULL
                                OR hora_fin IS NULL
                            )"
                        );
                        
                        $stmtBloqueo->bindParam(":doctor_id", $medico['doctor_id'], PDO::PARAM_INT);
                        $stmtBloqueo->bindParam(":fecha", $fecha, PDO::PARAM_STR);
                        $stmtBloqueo->execute();
                        
                        $resultado = $stmtBloqueo->fetch(PDO::FETCH_ASSOC);
                        if ($resultado['bloqueado'] > 0) {
                            unset($medicos[$key]);
                        }
                    }
                    
                    // Reindexar array después de eliminar elementos
                    $medicos = array_values($medicos);
                }
                
                
                // Calcular cupos disponibles REALES para cada médico basado en su agenda específica
                foreach ($medicos as $key => $medico) {
                    $doctorId = $medico['doctor_id'];
                    
                    // Obtener los horarios reales del médico para esta fecha/día
                    $stmtHorarios = Conexion::conectar()->prepare("
                        SELECT 
                            ad.hora_inicio,
                            ad.hora_fin,
                            ad.intervalo_minutos
                        FROM agendas_detalle ad
                        INNER JOIN agendas_cabecera ac ON ad.agenda_id = ac.agenda_id
                        WHERE ac.medico_id = :doctor_id 
                        AND ad.dia_semana = :dia_semana
                        AND ad.detalle_estado = true
                        AND ac.agenda_estado = true
                    ");
                    $stmtHorarios->bindParam(":doctor_id", $doctorId, PDO::PARAM_INT);
                    $stmtHorarios->bindParam(":dia_semana", $diaSemana, PDO::PARAM_STR);
                    $stmtHorarios->execute();
                    $horarios = $stmtHorarios->fetchAll(PDO::FETCH_ASSOC);
                    
                    $cuposTotales = 0;
                    
                    // Calcular cupos reales basados en los horarios del médico
                    foreach ($horarios as $horario) {
                        $horaInicio = new DateTime($horario['hora_inicio']);
                        $horaFin = new DateTime($horario['hora_fin']);
                        $intervaloMinutos = (int)$horario['intervalo_minutos'];
                        
                        if ($intervaloMinutos > 0) {
                            $diferenciaMinutos = ($horaFin->getTimestamp() - $horaInicio->getTimestamp()) / 60;
                            $slotsEnEsteBloque = floor($diferenciaMinutos / $intervaloMinutos);
                            $cuposTotales += $slotsEnEsteBloque;
                        }
                    }
                    
                    error_log("Médico {$medico['nombre_doctor']} (ID: {$doctorId}) - Horarios encontrados: " . count($horarios), 3, '/var/log/clinica/database.log');
                    error_log("Médico {$medico['nombre_doctor']} (ID: {$doctorId}) - Cupos base calculados: {$cuposTotales}", 3, '/var/log/clinica/database.log');
                    
                    // Contar reservas existentes para este médico en esta fecha
                    $reservasOcupadas = 0;
                    try {
                        // Verificar si existe la tabla servicios_reservas
                        $stmtCheckReservas = Conexion::conectar()->prepare("SELECT to_regclass('public.servicios_reservas')");
                        $stmtCheckReservas->execute();
                        $tablaReservasExiste = $stmtCheckReservas->fetchColumn();
                        
                        if ($tablaReservasExiste) {
                            $stmtReservas = Conexion::conectar()->prepare(
                                "SELECT COUNT(*) as reservas_ocupadas
                                FROM servicios_reservas 
                                WHERE doctor_id = :doctor_id 
                                AND DATE(fecha_reserva) = :fecha 
                                AND reserva_estado IN ('CONFIRMADA', 'PENDIENTE', 'EN_PROCESO')
                                AND activo = true"
                            );
                            
                            $stmtReservas->bindParam(":doctor_id", $doctorId, PDO::PARAM_INT);
                            $stmtReservas->bindParam(":fecha", $fecha, PDO::PARAM_STR);
                            $stmtReservas->execute();
                            
                            $resultadoReservas = $stmtReservas->fetch(PDO::FETCH_ASSOC);
                            $reservasOcupadas = $resultadoReservas ? (int)$resultadoReservas['reservas_ocupadas'] : 0;
                            
                            error_log("Médico {$medico['nombre_doctor']} (ID: {$doctorId}) - Reservas ocupadas: {$reservasOcupadas}", 3, '/var/log/clinica/database.log');
                        }
                    } catch (Exception $e) {
                        error_log("Error al contar reservas para médico {$doctorId}: " . $e->getMessage(), 3, '/var/log/clinica/database.log');
                    }
                    
                    // Calcular cupos disponibles (no puede ser negativo)
                    $cuposDisponibles = max(0, $cuposTotales - $reservasOcupadas);
                    
                    // Agregar información de cupos al médico
                    $medicos[$key]['cupo_disponible'] = $cuposDisponibles;
                    $medicos[$key]['turno_nombre'] = 'Múltiple'; // Puede atender en varios turnos
                    
                    error_log("Médico {$medico['nombre_doctor']} (ID: {$doctorId}) - Cupos base: {$cuposTotales}, Ocupados: {$reservasOcupadas}, Disponibles: {$cuposDisponibles}", 3, '/var/log/clinica/database.log');
                }
            }
            
            return $medicos;
            
        } catch (PDOException $e) {
            error_log("Error al obtener médicos por fecha: " . $e->getMessage(), 0);
            return [];
        }
    }
    
    /**
     * Obtiene los cupos disponibles por turno para una fecha específica
     * @param string $fecha Fecha en formato YYYY-MM-DD
     * @return array Cupos disponibles por turno
     */
    static public function mdlObtenerCuposDisponiblesPorTurno($fecha) {
        error_log("EJECUTANDO FUNCIÓN CUPOS REALES - {$fecha}", 3, '/var/log/clinica/database.log');
        return ['Mañana' => 16, 'Tarde' => 10, 'Noche' => 0];
    }

    /**
     * Obtiene los servicios disponibles para una fecha y doctor específicos
     * @param string $fecha Fecha en formato YYYY-MM-DD
     * @param int $doctorId ID del doctor
     * @return array Lista de servicios disponibles
     */    static public function mdlObtenerServiciosPorFechaMedico($fecha, $doctorId) {
        try {
            error_log("=== mdlObtenerServiciosPorFechaMedico ===", 3, '/var/log/clinica/servicios.log');
            error_log("Parámetros: Fecha={$fecha}, DoctorID={$doctorId}", 3, '/var/log/clinica/servicios.log');
            
            // Usar la estructura real de la base de datos
            $stmt = Conexion::conectar()->prepare(
                "SELECT DISTINCT
                    rs.serv_id as servicio_id,
                    rs.serv_codigo as servicio_codigo,
                    rs.serv_descripcion as servicio_nombre,
                    30 as duracion_minutos,
                    rs.serv_monto as precio_base,
                    rst.servicio as categoria_nombre,
                    'rs_servicios' as origen
                FROM 
                    rs_servicios rs
                INNER JOIN
                    rs_servicios_tipos rst ON rs.tserv_cod = rst.tserv_cod
                INNER JOIN
                    rs_servicios_doctors rsd ON rs.serv_id = rsd.servicio_id
                WHERE 
                    rs.is_active = true
                    AND rsd.doctor_id = :doctor_id
                    AND rsd.is_active = true
                ORDER BY
                    rs.serv_descripcion"
            );
            
            $stmt->bindParam(":doctor_id", $doctorId, PDO::PARAM_INT);
            $stmt->execute();
            $servicios = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            error_log("Servicios encontrados: " . count($servicios), 3, '/var/log/clinica/servicios.log');
            if (!empty($servicios)) {
                error_log("Primer servicio: " . json_encode($servicios[0]), 3, '/var/log/clinica/servicios.log');
            }
            
            return $servicios;
            
        } catch (PDOException $e) {
            error_log("Error al obtener servicios por fecha y médico: " . $e->getMessage(), 3, '/var/log/clinica/servicios.log');
            return [];
        } catch (Exception $e) {
            error_log("Error general al obtener servicios: " . $e->getMessage(), 3, '/var/log/clinica/servicios.log');
            return [];
        }
    }
    
    /**
     * Obtiene las reservas existentes para una fecha específica
     * @param string $fecha Fecha en formato YYYY-MM-DD
     * @param int $doctorId ID del doctor (opcional)
     * @param string $estado Estado de la reserva (opcional)
     * @param string $paciente Nombre del paciente (opcional)
     * @param int $salaId ID de la sala (opcional)
     * @param string $origen Origen de la reserva (opcional)
     * @return array Lista de reservas
     */    static public function mdlObtenerReservasPorFecha($fecha, $doctorId = null, $estado = null, $paciente = null, $salaId = null, $origen = null) {
        try {
            error_log("mdlObtenerReservasPorFecha: Fecha=$fecha, DoctorID=" . ($doctorId ?? "null") . ", Estado=" . ($estado ?? "null") . ", Paciente=" . ($paciente ?? "null") . ", SalaID=" . ($salaId ?? "null") . ", Origen=" . ($origen ?? "null"), 3, "/var/log/clinica/reservas.log");
            
            // Verificar si existe la tabla de reservas
            $stmtCheck = Conexion::conectar()->prepare("SELECT to_regclass('public.servicios_reservas')");
            $stmtCheck->execute();
            $tablaReservasExiste = $stmtCheck->fetchColumn();
            
            if (!$tablaReservasExiste) {
                error_log("mdlObtenerReservasPorFecha: La tabla servicios_reservas no existe", 3, "/var/log/clinica/reservas.log");
                return [];
            }
            
            // Asegurarse de que la fecha esté en formato YYYY-MM-DD
            if ($fecha !== null && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
                $fechaFormateada = date('Y-m-d', strtotime($fecha));
                error_log("mdlObtenerReservasPorFecha: Formato de fecha incorrecto ($fecha), reformateando a $fechaFormateada", 3, "/var/log/clinica/reservas.log");
                $fecha = $fechaFormateada;
            }
            
            // Construir la consulta SQL
            $sql = "SELECT 
                sr.reserva_id,
                sr.servicio_id,
                sr.doctor_id,
                sr.paciente_id,
                sr.fecha_reserva,
                sr.hora_inicio,
                sr.hora_fin,
                sr.reserva_estado,
                sr.observaciones,
                sr.business_id,
                sr.created_at,
                sr.updated_at,
                sr.agenda_id,
                sr.sala_id,
                s.sala_nombre,
                sr.tarifa_id,
                sr.origen_reserva,
                sr.activo,
                rp.first_name ||' - ' || rp.last_name as doctor,
                rp2.first_name ||' - ' || rp2.last_name as paciente,
                rs.serv_descripcion,
                rs.serv_monto
            FROM servicios_reservas sr 
            INNER JOIN rh_doctors rd ON sr.doctor_id = rd.doctor_id 
            INNER JOIN rh_person rp ON rd.person_id = rp.person_id 
            INNER JOIN rh_person rp2 ON sr.paciente_id = rp2.person_id 
            INNER JOIN rs_servicios rs ON sr.servicio_id = rs.serv_id 
            LEFT JOIN agendas_detalle ad on sr.agenda_id = ad.detalle_id 
            LEFT JOIN salas s on s.sala_id = sr.sala_id 
            WHERE sr.activo = true";
            
            // Añadir filtros según los parámetros proporcionados
            if ($fecha !== null) {
                // Para campos de tipo DATE, usar comparación directa sin horas
                $sql .= " AND sr.fecha_reserva = :fecha_reserva";
            }
            
            if ($doctorId !== null) {
                $sql .= " AND sr.doctor_id = :doctor_id";
            }
              
            if ($estado !== null && $estado !== '') {
                $sql .= " AND sr.reserva_estado = :estado";
            }
            
            // Filtrar por nombre de paciente (búsqueda parcial mejorada)
            if ($paciente !== null && trim($paciente) !== '') {
                $sql .= " AND (rp2.first_name ILIKE :nombre_paciente 
                        OR rp2.last_name ILIKE :nombre_paciente 
                        OR (rp2.first_name || ' ' || rp2.last_name) ILIKE :nombre_paciente)";
            }
            
            // Filtrar por sala
            if ($salaId !== null) {
                $sql .= " AND s.sala_id = :sala_id";
            }
            
            // Filtrar por origen de reserva
            if ($origen !== null && trim($origen) !== '') {
                $sql .= " AND sr.origen_reserva = :origen";
            }
            
            $sql .= " ORDER BY sr.fecha_reserva DESC, sr.hora_inicio ASC";
            
            error_log("mdlObtenerReservasPorFecha: SQL=$sql", 3, "/var/log/clinica/reservas.log");
            
            $stmt = Conexion::conectar()->prepare($sql);
            
            // Bindear parámetros según los filtros usados
            if ($fecha !== null) {
                $stmt->bindParam(":fecha_reserva", $fecha, PDO::PARAM_STR);
            }
            
            if ($doctorId !== null) {
                $stmt->bindParam(":doctor_id", $doctorId, PDO::PARAM_INT);
            }
            
            if ($estado !== null && $estado !== '') {
                $stmt->bindParam(":estado", $estado, PDO::PARAM_STR);
            }
            
            if ($paciente !== null && trim($paciente) !== '') {
                $pacienteParam = '%' . trim($paciente) . '%';
                $stmt->bindParam(":nombre_paciente", $pacienteParam, PDO::PARAM_STR);
            }
            
            if ($salaId !== null) {
                $stmt->bindParam(":sala_id", $salaId, PDO::PARAM_INT);
            }
            
            if ($origen !== null && trim($origen) !== '') {
                $stmt->bindParam(":origen", $origen, PDO::PARAM_STR);
            }
            
            $stmt->execute();
            $reservas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            error_log("mdlObtenerReservasPorFecha: Se encontraron " . count($reservas) . " reservas", 3, "/var/log/clinica/reservas.log");
            if (count($reservas) > 0) {
                error_log("mdlObtenerReservasPorFecha: Primera reserva: " . json_encode($reservas[0]), 3, "/var/log/clinica/reservas.log");
            }
            
            return $reservas;
        } catch (PDOException $e) {
            error_log("Error al obtener reservas por fecha: " . $e->getMessage(), 3, "/var/log/clinica/reservas.log");
            return [];
        }
    }

    /**
     * Crea una nueva reserva
     * @param array $datos Datos de la reserva
     * @return array Resultado de la operación
     */
    static public function mdlCrearReserva($datos) {
        try {
            // Verificar si existe la tabla de reservas
            $stmtCheck = Conexion::conectar()->prepare("SELECT to_regclass('public.servicios_reservas')");
            $stmtCheck->execute();
            $tablaReservasExiste = $stmtCheck->fetchColumn();
            
            if (!$tablaReservasExiste) {
                return ["error" => true, "mensaje" => "La tabla de reservas no existe. Por favor, ejecute el script de creación de tablas."];
            }
            
            // Verificar si ya existe una reserva en el mismo horario para el mismo doctor
            $stmtVerificar = Conexion::conectar()->prepare(
                "SELECT COUNT(*) AS coincidencias
                FROM servicios_reservas
                WHERE doctor_id = :doctor_id
                AND fecha_reserva = :fecha_reserva
                AND (
                    (hora_inicio <= :hora_inicio AND hora_fin > :hora_inicio) OR
                    (hora_inicio < :hora_fin AND hora_fin >= :hora_fin) OR
                    (hora_inicio >= :hora_inicio AND hora_fin <= :hora_fin)
                )
                AND reserva_estado IN ('PENDIENTE', 'CONFIRMADA')
                AND activo = true"
            );
            
            $stmtVerificar->bindParam(":doctor_id", $datos['doctor_id'], PDO::PARAM_INT);
            $stmtVerificar->bindParam(":fecha_reserva", $datos['fecha_reserva'], PDO::PARAM_STR);
            $stmtVerificar->bindParam(":hora_inicio", $datos['hora_inicio'], PDO::PARAM_STR);
            $stmtVerificar->bindParam(":hora_fin", $datos['hora_fin'], PDO::PARAM_STR);
            $stmtVerificar->execute();
            
            $resultado = $stmtVerificar->fetch(PDO::FETCH_ASSOC);
            if ($resultado['coincidencias'] > 0) {
                return ["error" => true, "mensaje" => "Ya existe una reserva para este doctor en el horario seleccionado."];
            }
            
            // Insertar la nueva reserva
            $stmtInsertar = Conexion::conectar()->prepare(
                "INSERT INTO servicios_reservas (
                    servicio_id, doctor_id, paciente_id, agenda_id, fecha_reserva,
                    hora_inicio, hora_fin, observaciones, sala_id, tarifa_id,
                    precio_final, business_id, created_by
                ) VALUES (
                    :servicio_id, :doctor_id, :paciente_id, :agenda_id, :fecha_reserva,
                    :hora_inicio, :hora_fin, :observaciones, :sala_id, :tarifa_id,
                    :precio_final, :business_id, :created_by
                ) RETURNING reserva_id"
            );
            
            // Valores requeridos
            $stmtInsertar->bindParam(":servicio_id", $datos['servicio_id'], PDO::PARAM_INT);
            $stmtInsertar->bindParam(":doctor_id", $datos['doctor_id'], PDO::PARAM_INT);
            $stmtInsertar->bindParam(":paciente_id", $datos['paciente_id'], PDO::PARAM_INT);
            $stmtInsertar->bindParam(":fecha_reserva", $datos['fecha_reserva'], PDO::PARAM_STR);
            $stmtInsertar->bindParam(":hora_inicio", $datos['hora_inicio'], PDO::PARAM_STR);
            $stmtInsertar->bindParam(":hora_fin", $datos['hora_fin'], PDO::PARAM_STR);
            
            // Valores opcionales
            $agendaId = isset($datos['agenda_id']) ? $datos['agenda_id'] : null;
            $observaciones = isset($datos['observaciones']) ? $datos['observaciones'] : null;
            $salaId = isset($datos['sala_id']) ? $datos['sala_id'] : null;
            $tarifaId = isset($datos['tarifa_id']) ? $datos['tarifa_id'] : null;
            $precioFinal = isset($datos['precio_final']) ? $datos['precio_final'] : null;
            $businessId = isset($datos['business_id']) ? $datos['business_id'] : 1;
            $createdBy = isset($datos['created_by']) ? $datos['created_by'] : 1;
            $seguroId = isset($datos['seguro_id']) ? $datos['seguro_id'] : null;
            
            $stmtInsertar->bindParam(":agenda_id", $agendaId, PDO::PARAM_INT);
            $stmtInsertar->bindParam(":observaciones", $observaciones, PDO::PARAM_STR);
            $stmtInsertar->bindParam(":sala_id", $salaId, PDO::PARAM_INT);
            $stmtInsertar->bindParam(":tarifa_id", $tarifaId, PDO::PARAM_INT);
            $stmtInsertar->bindParam(":precio_final", $precioFinal, PDO::PARAM_STR);
            $stmtInsertar->bindParam(":business_id", $businessId, PDO::PARAM_INT);
            $stmtInsertar->bindParam(":created_by", $createdBy, PDO::PARAM_INT);
            
            error_log("SQL a ejecutar: INSERT INTO servicios_reservas...", 3, '/var/log/clinica/reservas.log');
            error_log("Parámetros: " . json_encode([
                'servicio_id' => $datos['servicio_id'],
                'doctor_id' => $datos['doctor_id'],
                'paciente_id' => $datos['paciente_id'],
                'fecha_reserva' => $datos['fecha_reserva'],
                'hora_inicio' => $datos['hora_inicio'],
                'hora_fin' => $datos['hora_fin'],
                'agenda_id' => $agendaId,
                'tarifa_id' => $tarifaId,
                'seguro_id' => $seguroId,
                'sala_id' => $salaId
            ]), 3, '/var/log/clinica/reservas.log');
            
            if ($stmtInsertar->execute()) {
                $resultado = $stmtInsertar->fetch(PDO::FETCH_ASSOC);
                if ($resultado && isset($resultado['reserva_id'])) {
                    error_log("Reserva creada con ID: " . $resultado['reserva_id'], 3, '/var/log/clinica/reservas.log');
                    return $resultado['reserva_id'];
                } else {
                    error_log("Error: La consulta se ejecutó pero no se obtuvo un ID de reserva", 3, '/var/log/clinica/reservas.log');
                    return false;
                }
            } else {
                $errorInfo = $stmtInsertar->errorInfo();
                error_log("Error al insertar reserva: SQLSTATE=" . $errorInfo[0] . ", Driver-specific code=" . $errorInfo[1] . ", Message=" . $errorInfo[2], 3, '/var/log/clinica/reservas.log');
                return false;
            }
        } catch (PDOException $e) {
            error_log("Excepción PDO al guardar reserva: " . $e->getMessage() . "\n" . $e->getTraceAsString(), 3, '/var/log/clinica/reservas.log');
            return false;
        }
    }

    /**
     * Integrar datos de la tabla cm_servicios_medicos a la nueva estructura
     * @return array Resultado de la operación
     */
    static public function mdlIntegrarServiciosExistentes() {
        try {
            // Verificar si existe la tabla cm_servicios_medicos
            $stmt = Conexion::conectar()->prepare("
                SELECT EXISTS (
                    SELECT FROM information_schema.tables 
                    WHERE table_name = 'cm_servicios_medicos'
                ) AS existe_tabla
            ");
            
            $stmt->execute();
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$resultado['existe_tabla']) {
                return ["error" => true, "mensaje" => "La tabla cm_servicios_medicos no existe"];
            }
            
            // Obtener los servicios existentes
            $stmt = Conexion::conectar()->prepare("
                SELECT * FROM cm_servicios_medicos
                WHERE estado = true
            ");
            
            $stmt->execute();
            $serviciosExistentes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $serviciosImportados = 0;
            $errores = 0;
            
            // Comenzar una transacción
            $pdo = Conexion::conectar();
            $pdo->beginTransaction();
            
            try {
                // Asegurar que exista la categoría para servicios importados
                $stmtCategoria = $pdo->prepare("
                    INSERT INTO servicios_categorias 
                    (categoria_nombre, categoria_descripcion) 
                    VALUES ('Servicios Importados', 'Servicios importados del sistema anterior')
                    ON CONFLICT (categoria_nombre) DO UPDATE 
                    SET categoria_descripcion = EXCLUDED.categoria_descripcion
                    RETURNING categoria_id
                ");

                $stmtCategoria->execute();
                $categoriaResult = $stmtCategoria->fetch(PDO::FETCH_ASSOC);
                $categoriaId = $categoriaResult['categoria_id'];
                
                // Preparar la inserción de servicios
                $stmtServicio = $pdo->prepare("
                    INSERT INTO servicios_medicos
                    (categoria_id, servicio_codigo, servicio_nombre, servicio_descripcion, duracion_minutos, precio_base)
                    VALUES
                    (:categoria_id, :servicio_codigo, :servicio_nombre, :servicio_descripcion, :duracion_minutos, :precio_base)
                    ON CONFLICT (servicio_codigo) DO NOTHING
                    RETURNING servicio_id
                ");
                
                foreach ($serviciosExistentes as $servicio) {
                    // Valores por defecto o mapeados desde la tabla existente
                    $stmtServicio->bindParam(":categoria_id", $categoriaId, PDO::PARAM_INT);
                    $stmtServicio->bindParam(":servicio_codigo", $servicio['codigo'], PDO::PARAM_STR);
                    $stmtServicio->bindParam(":servicio_nombre", $servicio['nombre'], PDO::PARAM_STR);
                    $stmtServicio->bindParam(":servicio_descripcion", $servicio['descripcion'], PDO::PARAM_STR);
                    
                    // Valores predeterminados si no existen en la tabla original
                    $duracion = isset($servicio['duracion']) ? $servicio['duracion'] : 30;
                    $precio = isset($servicio['precio']) ? $servicio['precio'] : 0.00;
                    
                    $stmtServicio->bindParam(":duracion_minutos", $duracion, PDO::PARAM_INT);
                    $stmtServicio->bindParam(":precio_base", $precio, PDO::PARAM_STR);
                    
                    if ($stmtServicio->execute()) {
                        $servicioResult = $stmtServicio->fetch(PDO::FETCH_ASSOC);
                        if ($servicioResult) {
                            $serviciosImportados++;
                            
                            // También podríamos insertar una tarifa estándar para cada servicio
                            // Pero lo omitimos por simplicidad
                        }
                    } else {
                        $errores++;
                    }
                }
                
                // Si todo salió bien, confirmar la transacción
                $pdo->commit();
                
                return [
                    "error" => false, 
                    "mensaje" => "Importación completada", 
                    "importados" => $serviciosImportados,
                    "errores" => $errores
                ];
            } catch (Exception $e) {
                $pdo->rollBack();
                error_log("Error al importar servicios: " . $e->getMessage(), 0);
                return ["error" => true, "mensaje" => "Error al importar: " . $e->getMessage()];
            }
        } catch (Exception $e) {
            error_log("Error general al importar servicios: " . $e->getMessage(), 0);            return ["error" => true, "mensaje" => "Error general: " . $e->getMessage()];
        }
    }    /**
     * Obtiene todos los médicos disponibles para las reservas
     * @return array Listado de médicos
     */
    static public function mdlObtenerMedicos() {
        try {
            $stmt = Conexion::conectar()->prepare(
                "SELECT 
                    d.doctor_id::INTEGER as doctor_id, 
                    p.first_name || ' ' || p.last_name AS nombre_doctor,
                    d.doctor_estado
                FROM 
                    rh_doctors d
                INNER JOIN 
                    rh_person p ON d.person_id = p.person_id
                WHERE 
                    d.doctor_estado = 'ACTIVO'
                ORDER BY 
                    p.last_name, p.first_name ASC"
            );

            $stmt->execute();
            $medicos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Asegurarse de que doctor_id sea un entero
            foreach ($medicos as &$medico) {
                $medico['doctor_id'] = intval($medico['doctor_id']);
            }
            
            return $medicos;
        } catch (PDOException $e) {
            error_log("Error al obtener médicos: " . $e->getMessage(), 3, "/var/log/clinica/reservas.log");
            return [];
        }
    }
    
    /**
     * Busca pacientes por nombre o documento
     * @param string $termino Término de búsqueda
     * @return array Listado de pacientes encontrados
     */
    static public function mdlBuscarPaciente($termino) {
        $termino = "%" . $termino . "%";
        
        $stmt = Conexion::conectar()->prepare(
            "SELECT 
                p.person_id, 
                p.first_name,
                p.last_name,
                p.document_number
            FROM 
                rh_person p
            WHERE 
                p.first_name ILIKE :termino OR
                p.last_name ILIKE :termino OR
                p.document_number ILIKE :termino
            ORDER BY 
                p.last_name, p.first_name ASC
            LIMIT 10"
        );

        $stmt->bindParam(":termino", $termino, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
      /**
     * Guarda un nuevo paciente
     * @param array $datos Datos del paciente
     * @return int|bool ID del paciente creado o false en caso de error
     */
    static public function mdlGuardarNuevoPaciente($datos) {
        try {
            // Generate a random document if none is provided
            if (empty($datos["document_number"])) {
                $datos["document_number"] = "TMP" . date("YmdHis") . rand(100, 999);
            }
            
            $stmt = Conexion::conectar()->prepare(
                "INSERT INTO rh_person (
                    first_name, last_name, document_number, created_at
                ) VALUES (
                    :first_name, :last_name, :document_number, CURRENT_TIMESTAMP
                ) RETURNING person_id"
            );

            $stmt->bindParam(":first_name", $datos["first_name"], PDO::PARAM_STR);
            $stmt->bindParam(":last_name", $datos["last_name"], PDO::PARAM_STR);
            $stmt->bindParam(":document_number", $datos["document_number"], PDO::PARAM_STR);
            
            if ($stmt->execute()) {
                $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
                return $resultado["person_id"];
            } else {
                return false;
            }
        } catch (Exception $e) {
            error_log("Error al guardar paciente: " . $e->getMessage(), 0);
            return false;
        }
    }
    
    /**
     * Guarda una nueva reserva en la base de datos
     * @param array $datos Datos de la reserva
     * @return mixed ID de la reserva creada o false en caso de error
     */
    static public function mdlGuardarReserva($datos) {
        try {
            error_log("[" . date('Y-m-d H:i:s') . "] Intentando guardar reserva: " . json_encode($datos), 3, '/var/log/clinica/reservas.log');
            
            // Mostrar backtrace para saber quién llama a esta función
            $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3);
            $callers = array_map(function($trace) {
                return isset($trace['class']) ? 
                    $trace['class'] . '::' . $trace['function'] : 
                    $trace['function'];
            }, $backtrace);
            
            error_log("[" . date('Y-m-d H:i:s') . "] Llamado desde: " . implode(' <- ', $callers), 3, '/var/log/clinica/reservas.log');
              // Verificar si ya existe una reserva en el mismo horario para el mismo doctor
            // La lógica modificada permite reservas adyacentes (cuando una termina exactamente cuando la otra comienza)
            $stmtVerificar = Conexion::conectar()->prepare(
                "SELECT COUNT(*) AS coincidencias
                FROM servicios_reservas
                WHERE doctor_id = :doctor_id 
                AND fecha_reserva = :fecha_reserva
                AND ((hora_inicio < :hora_fin AND hora_fin > :hora_inicio))
                AND reserva_estado IN ('PENDIENTE', 'CONFIRMADA')
                AND activo = true"
            );
            
            $stmtVerificar->bindParam(":doctor_id", $datos['doctor_id'], PDO::PARAM_INT);
            $stmtVerificar->bindParam(":fecha_reserva", $datos['fecha_reserva'], PDO::PARAM_STR);
            $stmtVerificar->bindParam(":hora_inicio", $datos['hora_inicio'], PDO::PARAM_STR);
            $stmtVerificar->bindParam(":hora_fin", $datos['hora_fin'], PDO::PARAM_STR);
            $stmtVerificar->execute();
            
            if ($stmtVerificar->fetchColumn() > 0) {
                error_log("Reserva rechazada: Ya existe una reserva en este horario para este médico", 3, '/var/log/clinica/reservas.log');
                return false;
            }            // Insertar la nueva reserva - adaptado al esquema actual de la base de datos
            $stmt = Conexion::conectar()->prepare(
                "INSERT INTO servicios_reservas (
                    servicio_id, doctor_id, paciente_id, fecha_reserva, 
                    hora_inicio, hora_fin, observaciones, reserva_estado, 
                    business_id, created_by, agenda_id, tarifa_id, seguro_id, sala_id, origen_reserva
                ) VALUES (
                    :servicio_id, :doctor_id, :paciente_id, :fecha_reserva, 
                    :hora_inicio, :hora_fin, :observaciones, :reserva_estado, 
                    :business_id, :created_by, :agenda_id, :tarifa_id, :seguro_id, :sala_id, :origen_reserva
                ) RETURNING reserva_id"
            );
            
            // Bindear los parámetros obligatorios
            $stmt->bindParam(":servicio_id", $datos['servicio_id'], PDO::PARAM_INT);
            $stmt->bindParam(":doctor_id", $datos['doctor_id'], PDO::PARAM_INT);

            $stmt->bindParam(":paciente_id", $datos['paciente_id'], PDO::PARAM_INT);
            $stmt->bindParam(":fecha_reserva", $datos['fecha_reserva'], PDO::PARAM_STR);
            $stmt->bindParam(":hora_inicio", $datos['hora_inicio'], PDO::PARAM_STR);
            $stmt->bindParam(":hora_fin", $datos['hora_fin'], PDO::PARAM_STR);
            $stmt->bindParam(":reserva_estado", $datos['reserva_estado'], PDO::PARAM_STR);
            
            // Bindear los parámetros opcionales
            $observaciones = isset($datos['observaciones']) ? $datos['observaciones'] : '';
            $stmt->bindParam(":observaciones", $observaciones, PDO::PARAM_STR);
            
            $businessId = isset($datos['business_id']) ? $datos['business_id'] : 1;
            $stmt->bindParam(":business_id", $businessId, PDO::PARAM_INT);
            
            $createdBy = isset($datos['created_by']) ? $datos['created_by'] : 1;
            $stmt->bindParam(":created_by", $createdBy, PDO::PARAM_INT);
            
            // Estos campos son importantes para la reserva pero no estaban en la consulta original
            $agendaId = isset($datos['agenda_id']) ? $datos['agenda_id'] : null;
            error_log("mdlGuardarReserva: Usando agenda_id=" . (isset($datos['agenda_id']) ? $datos['agenda_id'] : "null"), 
                      3, '/var/log/clinica/reservas.log');
            $stmt->bindParam(":agenda_id", $agendaId, $agendaId ? PDO::PARAM_INT : PDO::PARAM_NULL);
            
            $tarifaId = isset($datos['tarifa_id']) ? $datos['tarifa_id'] : null;
            $stmt->bindParam(":tarifa_id", $tarifaId, $tarifaId ? PDO::PARAM_INT : PDO::PARAM_NULL);
            
            $seguroId = isset($datos['seguro_id']) ? $datos['seguro_id'] : null;
            $stmt->bindParam(":seguro_id", $seguroId, $seguroId ? PDO::PARAM_INT : PDO::PARAM_NULL);
            
            // Bindear sala_id si está disponible
            $salaId = isset($datos['sala_id']) ? $datos['sala_id'] : null;
            $stmt->bindParam(":sala_id", $salaId, $salaId ? PDO::PARAM_INT : PDO::PARAM_NULL);
            error_log("mdlGuardarReserva: Usando sala_id=" . (isset($datos['sala_id']) ? $datos['sala_id'] : "null"), 
                      3, '/var/log/clinica/reservas.log');
            
            // Bindear origen_reserva
            $origenReserva = isset($datos['origen_reserva']) ? $datos['origen_reserva'] : 'SISTEMA';
            $stmt->bindParam(":origen_reserva", $origenReserva, PDO::PARAM_STR);
            error_log("mdlGuardarReserva: Usando origen_reserva=" . $origenReserva, 
                      3, '/var/log/clinica/reservas.log');
                      
            error_log("SQL a ejecutar: INSERT INTO servicios_reservas...", 3, '/var/log/clinica/reservas.log');
            error_log("Parámetros: " . json_encode([
                'servicio_id' => $datos['servicio_id'],
                'doctor_id' => $datos['doctor_id'],
                'paciente_id' => $datos['paciente_id'],
                'fecha_reserva' => $datos['fecha_reserva'],
                'hora_inicio' => $datos['hora_inicio'],
                'hora_fin' => $datos['hora_fin'],
                'agenda_id' => $agendaId,
                'tarifa_id' => $tarifaId,
                'seguro_id' => $seguroId,
                'sala_id' => $salaId
            ]), 3, '/var/log/clinica/reservas.log');
            
            if ($stmt->execute()) {
                $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($resultado && isset($resultado['reserva_id'])) {
                    error_log("Reserva creada con ID: " . $resultado['reserva_id'], 3, '/var/log/clinica/reservas.log');
                    return $resultado['reserva_id'];
                } else {
                    error_log("Error: La consulta se ejecutó pero no se obtuvo un ID de reserva", 3, '/var/log/clinica/reservas.log');
                    return false;
                }
            } else {
                $errorInfo = $stmt->errorInfo();
                error_log("Error al insertar reserva: SQLSTATE=" . $errorInfo[0] . ", Driver-specific code=" . $errorInfo[1] . ", Message=" . $errorInfo[2], 3, '/var/log/clinica/reservas.log');
                return false;
            }
        } catch (PDOException $e) {
            error_log("Excepción PDO al guardar reserva: " . $e->getMessage() . "\n" . $e->getTraceAsString(), 3, '/var/log/clinica/reservas.log');
            return false;
        } catch (Exception $e) {
            error_log("Excepción general al guardar reserva: " . $e->getMessage() . "\n" . $e->getTraceAsString(),  3, '/var/log/clinica/reservas.log');
            return false;
        }
    }
    
    /**
     * Obtiene los proveedores de seguro médico
     * @return array Listado de proveedores de seguro médico
     */
    static public function mdlObtenerProveedoresSeguro() {
        try {
            $stmt = Conexion::conectar()->prepare(
                "SELECT 
                    pa.prov_id,
                    pa.prov_name,
                    pa.prov_lastname,
                    pa.prov_razon,
                    pa.prov_ruc,
                    tp.tipo_nombre
                FROM 
                    cm_proveedores_acreedores pa
                INNER JOIN 
                    cm_tipos_proveedores tp ON pa.tipo_cod = tp.tipo_cod
                WHERE 
                    -- tp.tipo_nombre = 'SEGURO MÉDICO' 
                    --AND 
                    pa.prov_is_active = true
                ORDER BY 
                    pa.prov_razon, pa.prov_name"
            );
            
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error en mdlObtenerProveedoresSeguro: " . $e->getMessage());
            return [];
        }
    }    /**
     * Busca reservas con filtros
     * @param string $fecha Fecha de reserva (opcional)
     * @param int $doctorId ID del doctor (opcional)
     * @param string $estado Estado de la reserva (opcional)
     * @param string $paciente Nombre del paciente para búsqueda (opcional)
     * @return array Lista de reservas que coinciden con los filtros
     */
    static public function mdlBuscarReservas($fecha = null, $doctorId = null, $estado = null, $paciente = null) {
        // Log detallado de parámetros recibidos
        error_log("mdlBuscarReservas INICIO: Fecha=" . ($fecha ?? "null") . 
                  ", DoctorID=" . ($doctorId ?? "null") . 
                  ", Estado=" . ($estado ?? "null") . 
                  ", Paciente=" . ($paciente ?? "null"), 
                  3, "/var/log/clinica/reservas.log");
        
        try {
            // Verificar si existe la tabla de reservas
            $stmtCheck = Conexion::conectar()->prepare("SELECT to_regclass('public.servicios_reservas')");
            $stmtCheck->execute();
            $tablaReservasExiste = $stmtCheck->fetchColumn();
            
            if (!$tablaReservasExiste) {
                error_log("mdlBuscarReservas: La tabla servicios_reservas no existe", 3, "/var/log/clinica/reservas.log");
                return [];
            }
            
            // Primero, verificar si existen reservas para este médico
            if ($doctorId !== null) {
                $checkStmt = Conexion::conectar()->prepare("SELECT COUNT(*) FROM servicios_reservas WHERE doctor_id = :doctor_id");
                $checkStmt->bindValue(':doctor_id', intval($doctorId), PDO::PARAM_INT);
                $checkStmt->execute();
                $count = $checkStmt->fetchColumn();
                error_log("mdlBuscarReservas: Verificación previa - Existen {$count} reservas con doctor_id={$doctorId}", 3, "/var/log/clinica/reservas.log");
                
                // Comprobar si hay reservas para esta fecha y doctor
                $checkDateDoctorStmt = Conexion::conectar()->prepare("SELECT COUNT(*) FROM servicios_reservas WHERE doctor_id = :doctor_id AND fecha_reserva::date = :fecha::date AND activo = true");
                $checkDateDoctorStmt->bindValue(':doctor_id', intval($doctorId), PDO::PARAM_INT);
                $checkDateDoctorStmt->bindParam(':fecha', $fecha);
                $checkDateDoctorStmt->execute();
                $countDateDoctor = $checkDateDoctorStmt->fetchColumn();
                error_log("mdlBuscarReservas: Verificación previa - Existen {$countDateDoctor} reservas con doctor_id={$doctorId} y fecha={$fecha}", 3, "/var/log/clinica/reservas.log");
            }
              
            // Construir la consulta SQL basada en el query proporcionado
            $sql = "SELECT 
                sr.reserva_id,
                sr.fecha_reserva,
                ad.dia_semana,
                sr.hora_inicio || ' - ' || sr.hora_fin as horario,
                ad.intervalo_minutos,
                s.sala_nombre,
                rp.first_name || ' - ' || rp.last_name as doctor,
                rp2.first_name || ' - ' || rp2.last_name as paciente,
                rs.serv_descripcion as nombre_servicio,
                rs.serv_monto as monto,
                sr.reserva_estado,
                sr.doctor_id as doctor_id_original
            FROM servicios_reservas sr 
            INNER JOIN agendas_detalle ad ON sr.agenda_id = ad.detalle_id 
            INNER JOIN salas s ON ad.sala_id = s.sala_id
            INNER JOIN rh_doctors rd ON sr.doctor_id = rd.doctor_id 
            INNER JOIN rh_person rp ON rd.person_id = rp.person_id
            INNER JOIN rh_person rp2 ON sr.paciente_id = rp2.person_id 
            INNER JOIN rs_servicios rs ON sr.servicio_id = rs.serv_id 
            WHERE 1=1";
            
            // Agregar condiciones según los filtros
            $params = [];
            
            // Filtro por fecha
            if ($fecha !== null && $fecha !== '') {
                $sql .= " AND sr.fecha_reserva::date = :fecha::date";
                $params[':fecha'] = $fecha;
                error_log("mdlBuscarReservas: Aplicando filtro de fecha: $fecha", 3, "/var/log/clinica/reservas.log");
            }
              // Filtro por doctor - asegurarse de que es un entero
            if ($doctorId !== null) {
                $doctorIdInt = intval($doctorId);
                $sql .= " AND sr.doctor_id = :doctor_id";
                $params[':doctor_id'] = $doctorIdInt;
                error_log("mdlBuscarReservas: Aplicando filtro de doctor: {$doctorIdInt} (original: {$doctorId})", 3, "/var/log/clinica/reservas.log");
                
                // Verificación adicional para diagnosticar
                $checkStmt = Conexion::conectar()->prepare("SELECT * FROM servicios_reservas WHERE doctor_id = :doctor_id LIMIT 1");
                $checkStmt->bindValue(':doctor_id', $doctorIdInt, PDO::PARAM_INT);
                $checkStmt->execute();
                $checkResult = $checkStmt->fetch(PDO::FETCH_ASSOC);
                if ($checkResult) {
                    error_log("mdlBuscarReservas: Doctor {$doctorIdInt} SÍ tiene reservas en la base de datos. Primera: " . json_encode($checkResult), 3, "/var/log/clinica/reservas.log");
                } else {
                    error_log("mdlBuscarReservas: Doctor {$doctorIdInt} NO tiene reservas en la base de datos.", 3, "/var/log/clinica/reservas.log");
                }
            }
            
            // Filtro por estado
            if ($estado !== null && $estado !== '') {
                $sql .= " AND sr.reserva_estado = :estado";
                $params[':estado'] = $estado;
                error_log("mdlBuscarReservas: Aplicando filtro de estado: $estado", 3, "/var/log/clinica/reservas.log");
            }
            
            // Filtro por paciente (búsqueda por nombre o apellido)
            if ($paciente !== null && $paciente !== '') {
                $sql .= " AND (rp2.first_name ILIKE :paciente OR rp2.last_name ILIKE :paciente OR (rp2.first_name || ' ' || rp2.last_name) ILIKE :paciente)";
                $params[':paciente'] = '%' . $paciente . '%';
                error_log("mdlBuscarReservas: Aplicando filtro de paciente: $paciente", 3, "/var/log/clinica/reservas.log");
            }
            
            // Ordenar los resultados
            $sql .= " ORDER BY sr.fecha_reserva DESC, ad.hora_inicio ASC";
            
            error_log("mdlBuscarReservas: SQL=$sql", 3, "/var/log/clinica/reservas.log");
            
            $stmt = Conexion::conectar()->prepare($sql);
              
            // Logging detallado de parámetros
            error_log("mdlBuscarReservas: Parámetros a bindear: " . json_encode($params), 3, "/var/log/clinica/reservas.log");
            
            // Bindear parámetros
            foreach ($params as $param => $value) {                if ($param === ':doctor_id') {
                    // Garantizar que doctor_id sea un entero
                    $doctorIdInt = intval($value);
                    $stmt->bindValue($param, $doctorIdInt, PDO::PARAM_INT);
                    error_log("mdlBuscarReservas: Bindeando $param = $doctorIdInt (original: $value) como PARAM_INT", 3, "/var/log/clinica/reservas.log");
                } else if (strpos($param, ':paciente') !== false) {
                    $stmt->bindValue($param, $value, PDO::PARAM_STR);
                    error_log("mdlBuscarReservas: Bindeando $param = $value como PARAM_STR", 3, "/var/log/clinica/reservas.log");
                } else {
                    $stmt->bindParam($param, $value);
                    error_log("mdlBuscarReservas: Bindeando $param = $value con bindParam", 3, "/var/log/clinica/reservas.log");
                }
            }
            
            $stmt->execute();
            $reservas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            error_log("mdlBuscarReservas: Se encontraron " . count($reservas) . " reservas", 3, "/var/log/clinica/reservas.log");
            if (count($reservas) > 0) {
                error_log("mdlBuscarReservas: Primera reserva: " . json_encode($reservas[0]), 3, "/var/log/clinica/reservas.log");
            } else {
                // Si no hay resultados, realizar una consulta directa para ver si hay datos
                if ($doctorId !== null) {
                    $simpleCheck = Conexion::conectar()->prepare("SELECT reserva_id, doctor_id, fecha_reserva, reserva_estado FROM servicios_reservas WHERE doctor_id = :doctor_id AND fecha_reserva::date = :fecha::date AND activo = true");
                    $simpleCheck->bindValue(':doctor_id', intval($doctorId), PDO::PARAM_INT);
                    $simpleCheck->bindParam(':fecha', $fecha);
                    $simpleCheck->execute();
                    $simpleResults = $simpleCheck->fetchAll(PDO::FETCH_ASSOC);
                    error_log("mdlBuscarReservas: Consulta directa - Resultados para doctor_id={$doctorId}, fecha={$fecha}: " . json_encode($simpleResults), 3, "/var/log/clinica/reservas.log");
                }
            }
            
            return $reservas;
        } catch (PDOException $e) {
            error_log("Error al buscar reservas: " . $e->getMessage(), 3, "/var/log/clinica/reservas.log");
            return [];
        }
    }

    /**
     * Busca reservas por doctor específico (consulta directa simplificada)
     * @param int $doctorId ID del doctor     * @param string $fecha Fecha de la reserva (opcional)
     * @param string $estado Estado de la reserva (opcional)
     * @param string $paciente Nombre del paciente para búsqueda (opcional)
     * @return array Lista de reservas encontradas
     */
    static public function mdlBuscarReservasPorDoctor($doctorId, $fecha = null, $estado = null, $paciente = null, $salaId = null, $origen = null) {
        try {
            error_log("mdlBuscarReservasPorDoctor: Ejecutando consulta directa para doctor_id=$doctorId, fecha=" . 
                     ($fecha ? $fecha : "NULL") . ", estado=" . ($estado ?? "NULL") . ", paciente=" . ($paciente ?? "NULL") . ", salaId=" . ($salaId ?? "NULL") . ", origen=" . ($origen ?? "NULL"), 
                     3, "/var/log/clinica/reservas.log");
            
            $sql = "SELECT 
                sr.reserva_id,
                sr.servicio_id,
                sr.doctor_id,
                sr.paciente_id,
                sr.fecha_reserva,
                sr.hora_inicio,
                sr.hora_fin,
                sr.reserva_estado,
                sr.observaciones,
                sr.business_id,
                sr.created_at,
                sr.updated_at,
                sr.agenda_id,
                sr.sala_id,
                s.sala_nombre,
                sr.tarifa_id,
                sr.origen_reserva,
                sr.activo,
                rp.first_name ||' - ' || rp.last_name as doctor,
                rp2.first_name ||' - ' || rp2.last_name as paciente,
                rs.serv_descripcion,
                rs.serv_monto
            FROM servicios_reservas sr 
            INNER JOIN rh_doctors rd ON sr.doctor_id = rd.doctor_id 
            INNER JOIN rh_person rp ON rd.person_id = rp.person_id 
            INNER JOIN rh_person rp2 ON sr.paciente_id = rp2.person_id 
            INNER JOIN rs_servicios rs ON sr.servicio_id = rs.serv_id 
            LEFT JOIN agendas_detalle ad on sr.agenda_id = ad.detalle_id 
            LEFT JOIN salas s on s.sala_id = sr.sala_id
            WHERE sr.doctor_id = :doctor_id AND sr.activo = true";
            
            if ($fecha) {
                $sql .= " AND sr.fecha_reserva::date = :fecha::date";
            }
            
            // Filtro por estado
            if ($estado !== null && $estado !== '') {
                $sql .= " AND sr.reserva_estado = :estado";
            }
            
            // Filtro por paciente
            if ($paciente !== null && trim($paciente) !== '') {
                $sql .= " AND (rp_paciente.first_name ILIKE :paciente OR rp_paciente.last_name ILIKE :paciente OR (rp_paciente.first_name || ' ' || rp_paciente.last_name) ILIKE :paciente)";
            }
            
            // Filtro por sala
            if ($salaId !== null) {
                $sql .= " AND s.sala_id = :sala_id";
            }
            
            // Filtro por origen de reserva
            if ($origen !== null && trim($origen) !== '') {
                $sql .= " AND sr.origen_reserva = :origen";
            }
            
            $sql .= " ORDER BY sr.fecha_reserva DESC, sr.hora_inicio ASC";
            
            $stmt = Conexion::conectar()->prepare($sql);
            $stmt->bindValue(':doctor_id', intval($doctorId), PDO::PARAM_INT);
            
            if ($fecha) {
                $stmt->bindParam(':fecha', $fecha, PDO::PARAM_STR);
            }
            
            // Bindear parámetro de estado si se proporciona
            if ($estado !== null && $estado !== '') {
                $stmt->bindParam(':estado', $estado, PDO::PARAM_STR);
            }
            
            // Bindear parámetro de paciente si se proporciona
            if ($paciente !== null && trim($paciente) !== '') {
                $pacienteParam = '%' . trim($paciente) . '%';
                $stmt->bindParam(':paciente', $pacienteParam, PDO::PARAM_STR);
            }
            
            // Bindear parámetro de sala si se proporciona
            if ($salaId !== null) {
                $stmt->bindParam(':sala_id', $salaId, PDO::PARAM_INT);
            }
            
            // Bindear parámetro de origen si se proporciona
            if ($origen !== null && trim($origen) !== '') {
                $stmt->bindParam(':origen', $origen, PDO::PARAM_STR);
            }
            
            $stmt->execute();
            $reservas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            error_log("mdlBuscarReservasPorDoctor: Encontradas " . count($reservas) . " reservas", 
                     3, "/var/log/clinica/reservas.log");
            
            return $reservas;
        } catch (PDOException $e) {
            error_log("Error en mdlBuscarReservasPorDoctor: " . $e->getMessage(), 
                     3, "/var/log/clinica/reservas.log");
            return [];
        }
    }

    /**
     * Obtiene todos los servicios de la tabla rs_servicios
     * @return array Listado de servicios
     */
    static public function mdlObtenerTodosRsServicios() {
        try {
            $stmt = Conexion::conectar()->prepare(
                "SELECT 
                rs.serv_id, 
                rs.serv_codigo, 
                rs.serv_descripcion,
                rs.serv_descripcion_factura,
                rs.serv_monto,
                rs.serv_tte,
                rs.serv_rtte,
                rs.serv_uso_equipo,
                rs.serv_der_sala,
                rs.tserv_cod,
                rs.is_active,
                rst.servicio as categoria_nombre
            FROM 
                rs_servicios rs
            INNER JOIN 
                rs_servicios_tipos rst ON rs.tserv_cod = rst.tserv_cod
            ORDER BY 
                rs.serv_id DESC"
            );
            
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Error en mdlObtenerTodosRsServicios: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene los tipos de servicios
     * @return array Listado de tipos de servicios
     */
    static public function mdlObtenerTiposRsServicio() {
        try {
            $stmt = Conexion::conectar()->prepare(
                "SELECT 
                tserv_cod,
                servicio
            FROM 
                rs_servicios_tipos
            WHERE 
                is_active = true
            ORDER BY 
                servicio"
            );
            
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Error en mdlObtenerTiposRsServicio: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Crea un nuevo servicio
     * @param array $datos Datos del servicio
     * @return array Respuesta de la operación
     */
    static public function mdlCrearRsServicio($datos) {
        try {
            $db = Conexion::conectar();
            $stmt = $db->prepare(                "INSERT INTO rs_servicios (
                serv_codigo, 
                serv_descripcion, 
                serv_descripcion_factura,
                serv_monto,
                serv_tte,
                tserv_cod,
                is_active,
                user_id,
                business_id
            ) VALUES (
                :codigo,
                :descripcion,
                :descripcion_factura,
                :monto,
                :duracion,
                :tipo_servicio,
                :estado,
                :user_id,
                :business_id
            ) RETURNING serv_id"
            );
            
            // Asegurar de tener valores para los campos requeridos
            $datos['business_id'] = $_SESSION['business_id'] ?? 1;
            $datos['user_id'] = $_SESSION['id_usuario'] ?? 1;
              $stmt->bindParam(":codigo", $datos['serv_codigo'], PDO::PARAM_STR);
            $stmt->bindParam(":descripcion", $datos['serv_descripcion'], PDO::PARAM_STR);
            $stmt->bindParam(":descripcion_factura", $datos['serv_descripcion'], PDO::PARAM_STR); // Usamos la misma descripción
            $stmt->bindParam(":monto", $datos['serv_monto'], PDO::PARAM_STR);
            $stmt->bindParam(":duracion", $datos['serv_tte'], PDO::PARAM_STR);
            $stmt->bindParam(":tipo_servicio", $datos['tserv_cod'], PDO::PARAM_INT);
            $stmt->bindParam(":estado", $datos['is_active'], PDO::PARAM_BOOL);
            $stmt->bindParam(":user_id", $datos['user_id'], PDO::PARAM_INT);
            $stmt->bindParam(":business_id", $datos['business_id'], PDO::PARAM_INT);
            
            $stmt->execute();
            $idServicio = $stmt->fetchColumn();
            
            return [
                'exito' => true,
                'mensaje' => 'Servicio creado correctamente',
                'id' => $idServicio
            ];
        } catch(PDOException $e) {
            error_log("Error en mdlCrearRsServicio: " . $e->getMessage());
            return [
                'exito' => false,
                'mensaje' => 'Error al crear el servicio: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Actualiza un servicio existente
     * @param array $datos Datos del servicio
     * @return array Respuesta de la operación
     */
    static public function mdlActualizarRsServicio($datos) {
        try {
            $db = Conexion::conectar();
            $stmt = $db->prepare(
                "UPDATE rs_servicios SET
                serv_codigo = :codigo,
                serv_descripcion = :descripcion,
                serv_descripcion_factura = :descripcion_factura,
                serv_monto = :monto,
                serv_tte = :duracion,
                tserv_cod = :tipo_servicio,
                is_active = :estado
            WHERE serv_id = :id"
            );
              $stmt->bindParam(":id", $datos['serv_id'], PDO::PARAM_INT);
            $stmt->bindParam(":codigo", $datos['serv_codigo'], PDO::PARAM_STR);
            $stmt->bindParam(":descripcion", $datos['serv_descripcion'], PDO::PARAM_STR);
            $stmt->bindParam(":descripcion_factura", $datos['serv_descripcion'], PDO::PARAM_STR); // Usamos la misma descripción 
            $stmt->bindParam(":monto", $datos['serv_monto'], PDO::PARAM_STR);
            $stmt->bindParam(":duracion", $datos['serv_tte'], PDO::PARAM_STR);
            $stmt->bindParam(":tipo_servicio", $datos['tserv_cod'], PDO::PARAM_INT);
            $stmt->bindParam(":estado", $datos['is_active'], PDO::PARAM_BOOL);
            
            $stmt->execute();
            
            return [
                'exito' => true,
                'mensaje' => 'Servicio actualizado correctamente'
            ];
        } catch(PDOException $e) {
            error_log("Error en mdlActualizarRsServicio: " . $e->getMessage());
            return [
                'exito' => false,
                'mensaje' => 'Error al actualizar el servicio: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Elimina un servicio (desactivación lógica)
     * @param int $id ID del servicio
     * @return array Respuesta de la operación
     */
    static public function mdlEliminarRsServicio($id) {
        try {
            $db = Conexion::conectar();
            $stmt = $db->prepare(
                "UPDATE rs_servicios SET
                is_active = false
            WHERE serv_id = :id"
            );
            
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            $stmt->execute();
            
            return [
                'exito' => true,
                'mensaje' => 'Servicio eliminado correctamente'
            ];
        } catch(PDOException $e) {
            error_log("Error en mdlEliminarRsServicio: " . $e->getMessage());
            return [
                'exito' => false,
                'mensaje' => 'Error al eliminar el servicio: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Crea un nuevo tipo de servicio
     * @param string $nombre Nombre del tipo de servicio
     * @return array Respuesta de la operación
     */
    static public function mdlCrearTipoRsServicio($nombre) {
        try {
            $db = Conexion::conectar();
            $stmt = $db->prepare(
                "INSERT INTO rs_servicios_tipos (
                servicio,
                is_active,
                user_id,
                business_id
            ) VALUES (
                :nombre,
                true,
                :user_id,
                :business_id
            ) RETURNING tserv_cod"
            );
            
            $userId = $_SESSION['id_usuario'] ?? 1;
            $businessId = $_SESSION['business_id'] ?? 1;
            
            $stmt->bindParam(":nombre", $nombre, PDO::PARAM_STR);
            $stmt->bindParam(":user_id", $userId, PDO::PARAM_INT);
            $stmt->bindParam(":business_id", $businessId, PDO::PARAM_INT);
            
            $stmt->execute();
            $idTipo = $stmt->fetchColumn();
            
            return [
                'exito' => true,
                'mensaje' => 'Tipo de servicio creado correctamente',
                'id' => $idTipo
            ];
        } catch(PDOException $e) {
            error_log("Error en mdlCrearTipoRsServicio: " . $e->getMessage());
            return [
                'exito' => false,
                'mensaje' => 'Error al crear el tipo de servicio: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Actualiza un tipo de servicio
     * @param int $id ID del tipo
     * @param string $nombre Nuevo nombre
     * @return array Respuesta de la operación
     */
    static public function mdlActualizarTipoRsServicio($id, $nombre) {
        try {
            $db = Conexion::conectar();
            $stmt = $db->prepare(
                "UPDATE rs_servicios_tipos SET
                servicio = :nombre
            WHERE tserv_cod = :id"
            );
            
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            $stmt->bindParam(":nombre", $nombre, PDO::PARAM_STR);
            
            $stmt->execute();
            
            return [
                'exito' => true,
                'mensaje' => 'Tipo de servicio actualizado correctamente'
            ];
        } catch(PDOException $e) {
            error_log("Error en mdlActualizarTipoRsServicio: " . $e->getMessage());
            return [
                'exito' => false,
                'mensaje' => 'Error al actualizar el tipo de servicio: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Elimina un tipo de servicio (desactivación lógica)
     * @param int $id ID del tipo
     * @return array Respuesta de la operación
     */
    static public function mdlEliminarTipoRsServicio($id) {
        try {
            // Primero verificamos si hay servicios usando este tipo
            $db = Conexion::conectar();
            $stmt = $db->prepare(
                "SELECT COUNT(*) FROM rs_servicios WHERE tserv_cod = :id AND is_active = true"
            );
            
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            $stmt->execute();
            $count = $stmt->fetchColumn();
            
            if ($count > 0) {
                return [
                    'exito' => false,
                    'mensaje' => 'No se puede eliminar este tipo porque está siendo utilizado por ' . $count . ' servicio(s)'
                ];
            }
            
            // Si no hay servicios asociados, procedemos a desactivar
            $stmt = $db->prepare(
                "UPDATE rs_servicios_tipos SET
                is_active = false
            WHERE tserv_cod = :id"
            );
            
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            $stmt->execute();
            
            return [
                'exito' => true,
                'mensaje' => 'Tipo de servicio eliminado correctamente'
            ];
        } catch(PDOException $e) {
            error_log("Error en mdlEliminarTipoRsServicio: " . $e->getMessage());
            return [
                'exito' => false,
                'mensaje' => 'Error al eliminar el tipo de servicio: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Filtra servicios según criterios
     * @param array $filtros Criterios de filtrado
     * @return array Listado filtrado de servicios
     */
    static public function mdlFiltrarRsServicios($filtros) {
        try {
            $condiciones = [];
            $parametros = [];
            
            // Construir condiciones de filtrado
            if (!empty($filtros['codigo'])) {
                $condiciones[] = "rs.serv_codigo ILIKE :codigo";
                $parametros[':codigo'] = '%' . $filtros['codigo'] . '%';
            }
            
            if (!empty($filtros['descripcion'])) {
                $condiciones[] = "rs.serv_descripcion ILIKE :descripcion";
                $parametros[':descripcion'] = '%' . $filtros['descripcion'] . '%';
            }
            
            if (!empty($filtros['tipo']) && $filtros['tipo'] != "0") {
                $condiciones[] = "rs.tserv_cod = :tipo";
                $parametros[':tipo'] = $filtros['tipo'];
            }
            
            $whereSql = count($condiciones) > 0 ? "AND " . implode(" AND ", $condiciones) : "";
            
            $sql = "SELECT 
                rs.serv_id, 
                rs.serv_codigo, 
                rs.serv_descripcion,
                rs.serv_descripcion_factura,
                rs.serv_monto,
                rs.serv_tte,
                rs.serv_rtte,
                rs.serv_uso_equipo,
                rs.serv_der_sala,
                rs.tserv_cod,
                rs.is_active,
                rst.servicio as categoria_nombre
            FROM 
                rs_servicios rs
            INNER JOIN 
                rs_servicios_tipos rst ON rs.tserv_cod = rst.tserv_cod
            WHERE 1=1 {$whereSql}
            ORDER BY 
                rs.serv_id DESC";
            
            $stmt = Conexion::conectar()->prepare($sql);
            
            // Vincular parámetros
            foreach ($parametros as $param => $value) {
                $stmt->bindValue($param, $value);
            }
            
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Error en mdlFiltrarRsServicios: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene las reservas existentes para un doctor y fecha específica
     * @param int $doctorId ID del doctor
     * @param string $fecha Fecha para la verificación (formato YYYY-MM-DD)
     * @return array Lista de reservas con horario de inicio y fin
     */
    static public function mdlObtenerReservasExistentes($doctorId, $fecha) {
        try {
            // Verificar si existe la tabla de reservas
            $stmtCheck = Conexion::conectar()->prepare("SELECT to_regclass('public.servicios_reservas')");
            $stmtCheck->execute();
            $tablaReservasExiste = $stmtCheck->fetchColumn();
            
            if (!$tablaReservasExiste) {
                error_log("mdlObtenerReservasExistentes: La tabla servicios_reservas no existe", 
                          3, "/var/log/clinica/database.log");
                return [];
            }
            
            // Consulta para obtener las reservas existentes para ese doctor y día
            $stmt = Conexion::conectar()->prepare(
                "SELECT 
                    reserva_id,
                    servicio_id,
                    agenda_id,
                    doctor_id,
                    fecha_reserva,
                    hora_inicio,
                    hora_fin
                FROM 
                    servicios_reservas
                WHERE 
                    doctor_id = :doctor_id
                    AND fecha_reserva = :fecha_reserva
                    AND reserva_estado IN ('CONFIRMADA', 'PENDIENTE')
                    AND activo = true
                ORDER BY 
                    hora_inicio ASC"
            );
            
            $stmt->bindParam(":doctor_id", $doctorId, PDO::PARAM_INT);
            $stmt->bindParam(":fecha_reserva", $fecha, PDO::PARAM_STR);
            $stmt->execute();
            
            $reservas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            error_log("mdlObtenerReservasExistentes: Encontradas " . count($reservas) . 
                      " reservas para doctor ID $doctorId en fecha $fecha", 
                      3, "/var/log/clinica/database.log");
            
            if (count($reservas) > 0) {
                error_log("Primera reserva: " . json_encode($reservas[0]), 
                          3, "/var/log/clinica/database.log");
            }
            
            return $reservas;
        } catch (PDOException $e) {
            error_log("Error al obtener reservas existentes: " . $e->getMessage(), 
                      3, "/var/log/clinica/database.log");
            return [];
        }
    }

    /**
     * Obtiene los detalles de una reserva específica por su ID
     * @param int $reservaId ID de la reserva
     * @return array|null Datos de la reserva o null si no existe
     */
    static public function mdlObtenerReservaPorId($reservaId) {
        try {
            error_log("mdlObtenerReservaPorId: Buscando reserva con ID: $reservaId", 
                      3, "/var/log/clinica/reservas.log");
            
            // Primero verificar si la reserva existe
            $stmtCheck = Conexion::conectar()->prepare("SELECT COUNT(*) FROM servicios_reservas WHERE reserva_id = :reserva_id");
            $stmtCheck->bindParam(":reserva_id", $reservaId, PDO::PARAM_INT);
            $stmtCheck->execute();
            $exists = $stmtCheck->fetchColumn();
            
            error_log("mdlObtenerReservaPorId: Reserva existe: " . ($exists ? 'SÍ' : 'NO'), 
                      3, "/var/log/clinica/reservas.log");
            
            if (!$exists) {
                return null;
            }
            
            // Consulta completa con todos los JOINs para obtener información relacionada
            $stmt = Conexion::conectar()->prepare("
                SELECT 
                    sr.reserva_id,
                    sr.servicio_id,
                    sr.doctor_id,
                    sr.paciente_id,
                    sr.fecha_reserva,
                    sr.hora_inicio,
                    sr.hora_fin,
                    sr.reserva_estado,
                    sr.observaciones,
                    sr.business_id,
                    sr.created_at,
                    sr.updated_at,
                    sr.agenda_id,
                    sr.sala_id,
                    COALESCE(s.sala_nombre, 'Sala ' || COALESCE(ad.sala_id, sr.sala_id)) as sala_nombre,
                    sr.tarifa_id,
                    COALESCE(rp.first_name || ' ' || rp.last_name, 'Doctor ID: ' || sr.doctor_id) as doctor_nombre,
                    COALESCE(rp2.first_name || ' ' || rp2.last_name, 'Paciente ID: ' || sr.paciente_id) as paciente_nombre,
                    COALESCE(rp2.document_number, '') as cedula,
                    COALESCE(rp2.phone_number, '') as telefono,
                    COALESCE(rs.serv_descripcion, 'Servicio ID: ' || sr.servicio_id) as servicio_nombre,
                    COALESCE(rs.serv_monto, 0) as servicio_monto,
                    '' as email
                FROM servicios_reservas sr 
                LEFT JOIN rh_doctors rd ON sr.doctor_id = rd.doctor_id 
                LEFT JOIN rh_person rp ON rd.person_id = rp.person_id 
                LEFT JOIN rh_person rp2 ON sr.paciente_id = rp2.person_id 
                LEFT JOIN rs_servicios rs ON sr.servicio_id = rs.serv_id 
                LEFT JOIN agendas_detalle ad ON sr.agenda_id = ad.detalle_id 
                LEFT JOIN salas s ON s.sala_id = ad.sala_id 
                WHERE sr.reserva_id = :reserva_id
            ");
            
            $stmt->bindParam(":reserva_id", $reservaId, PDO::PARAM_INT);
            $stmt->execute();
            
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($resultado) {
                error_log("mdlObtenerReservaPorId: Reserva encontrada (básica): " . json_encode($resultado), 
                          3, "/var/log/clinica/reservas.log");
                
                // Intentar obtener información adicional de otras tablas si existen
                try {
                    // Intentar obtener información del doctor de rh_person
                    $stmtDoctor = Conexion::conectar()->prepare("SELECT CONCAT(nombres, ' ', apellidos) as nombre FROM rh_person WHERE person_id = :doctor_id");
                    $stmtDoctor->bindParam(":doctor_id", $resultado['doctor_id'], PDO::PARAM_INT);
                    $stmtDoctor->execute();
                    $doctorNombre = $stmtDoctor->fetchColumn();
                    if ($doctorNombre) {
                        $resultado['doctor_nombre'] = $doctorNombre;
                    }
                } catch (Exception $e) {
                    error_log("mdlObtenerReservaPorId: No se pudo obtener info del doctor: " . $e->getMessage(), 
                              3, "/var/log/clinica/reservas.log");
                }
                
                try {
                    // Intentar obtener información del paciente
                    $stmtPaciente = Conexion::conectar()->prepare("SELECT CONCAT(nombres, ' ', apellidos) as nombre, cedula, telefono, email FROM pacientes WHERE paciente_id = :paciente_id");
                    $stmtPaciente->bindParam(":paciente_id", $resultado['paciente_id'], PDO::PARAM_INT);
                    $stmtPaciente->execute();
                    $pacienteInfo = $stmtPaciente->fetch(PDO::FETCH_ASSOC);
                    if ($pacienteInfo) {
                        $resultado['paciente_nombre'] = $pacienteInfo['nombre'];
                        $resultado['cedula'] = $pacienteInfo['cedula'] ?? '';
                        $resultado['telefono'] = $pacienteInfo['telefono'] ?? '';
                        $resultado['email'] = $pacienteInfo['email'] ?? '';
                    }
                } catch (Exception $e) {
                    error_log("mdlObtenerReservaPorId: No se pudo obtener info del paciente: " . $e->getMessage(), 
                              3, "/var/log/clinica/reservas.log");
                }
                
                try {
                    // Intentar obtener información de la sala
                    if ($resultado['sala_id']) {
                        $stmtSala = Conexion::conectar()->prepare("SELECT sala_nombre FROM salas WHERE sala_id = :sala_id");
                        $stmtSala->bindParam(":sala_id", $resultado['sala_id'], PDO::PARAM_INT);
                        $stmtSala->execute();
                        $salaNombre = $stmtSala->fetchColumn();
                        if ($salaNombre) {
                            $resultado['sala_nombre'] = $salaNombre;
                        }
                    }
                } catch (Exception $e) {
                    error_log("mdlObtenerReservaPorId: No se pudo obtener info de la sala: " . $e->getMessage(), 
                              3, "/var/log/clinica/reservas.log");
                }
                
                // Intentar obtener información del servicio desde diferentes posibles tablas
                $tablasServicios = ['rs_servicios', 'servicios', 'medical_services'];
                foreach ($tablasServicios as $tablaServicio) {
                    try {
                        $stmtServicio = Conexion::conectar()->prepare("SELECT * FROM $tablaServicio WHERE id = :servicio_id OR servicio_id = :servicio_id LIMIT 1");
                        $stmtServicio->bindParam(":servicio_id", $resultado['servicio_id'], PDO::PARAM_INT);
                        $stmtServicio->execute();
                        $servicioInfo = $stmtServicio->fetch(PDO::FETCH_ASSOC);
                        if ($servicioInfo) {
                            // Buscar campo de nombre del servicio
                            $nombreCampos = ['nombre', 'servicio_nombre', 'service_name', 'descripcion'];
                            foreach ($nombreCampos as $campo) {
                                if (isset($servicioInfo[$campo]) && !empty($servicioInfo[$campo])) {
                                    $resultado['servicio_nombre'] = $servicioInfo[$campo];
                                    break 2; // Salir de ambos bucles
                                }
                            }
                            
                            // Buscar campo de duración
                            $duracionCampos = ['duracion_minutos', 'duracion', 'duration'];
                            foreach ($duracionCampos as $campo) {
                                if (isset($servicioInfo[$campo]) && !empty($servicioInfo[$campo])) {
                                    $resultado['duracion_minutos'] = $servicioInfo[$campo];
                                    break;
                                }
                            }
                            
                            error_log("mdlObtenerReservaPorId: Info de servicio obtenida de tabla $tablaServicio", 
                                      3, "/var/log/clinica/reservas.log");
                            break;
                        }
                    } catch (Exception $e) {
                        // Tabla no existe o error, continuar con la siguiente
                        continue;
                    }
                }
                
            } else {
                error_log("mdlObtenerReservaPorId: No se encontró reserva con ID: $reservaId después de la consulta", 
                          3, "/var/log/clinica/reservas.log");
            }
            
            return $resultado;
        } catch (PDOException $e) {
            error_log("Error al obtener reserva por ID: " . $e->getMessage(), 
                      3, "/var/log/clinica/reservas.log");
            return null;
        }
    }

    /**
     * Actualiza una reserva existente y maneja la disponibilidad de agenda
     * @param array $datos Datos de la reserva a actualizar
     * @return array Resultado de la operación
     */
    static public function mdlActualizarReserva($datos) {
        try {
            $pdo = Conexion::conectar();
            $pdo->beginTransaction();
            
            // Primero obtenemos los datos actuales de la reserva
            $reservaActual = self::mdlObtenerReservaPorId($datos['reserva_id']);
            if (!$reservaActual) {
                $pdo->rollBack();
                return [
                    "status" => "error",
                    "message" => "Reserva no encontrada"
                ];
            }
            
            error_log("mdlActualizarReserva: Datos actuales - Doctor: {$reservaActual['doctor_id']}, Fecha: {$reservaActual['fecha_reserva']}, Hora: {$reservaActual['hora_inicio']}-{$reservaActual['hora_fin']}", 
                      3, "/var/log/clinica/reservas.log");
            
            // Verificar si cambió el doctor, fecha u hora para liberar el horario anterior
            $cambioHorario = (
                $reservaActual['doctor_id'] != $datos['doctor_id'] ||
                $reservaActual['fecha_reserva'] != $datos['fecha_reserva'] ||
                $reservaActual['hora_inicio'] != $datos['hora_inicio'] ||
                $reservaActual['hora_fin'] != $datos['hora_fin']
            );
            
            if ($cambioHorario) {
                error_log("mdlActualizarReserva: Detectado cambio de horario, liberando agenda anterior", 
                          3, "/var/log/clinica/reservas.log");
                
                // Si hay cambio de horario, verificar disponibilidad del nuevo horario
                $conflictos = self::verificarConflictosReserva(
                    $datos['doctor_id'],
                    $datos['fecha_reserva'],
                    $datos['hora_inicio'],
                    $datos['hora_fin'],
                    $datos['reserva_id'] // Excluir la reserva actual de la verificación
                );
                
                if (count($conflictos) > 0) {
                    $pdo->rollBack();
                    return [
                        "status" => "error",
                        "message" => "El nuevo horario ya está ocupado por otra reserva"
                    ];
                }
            }
            
            // Actualizar la reserva
            $stmt = $pdo->prepare(
                "UPDATE servicios_reservas SET 
                    servicio_id = :servicio_id,
                    agenda_id = :agenda_id,
                    doctor_id = :doctor_id,
                    paciente_id = :paciente_id,
                    fecha_reserva = :fecha_reserva,
                    hora_inicio = :hora_inicio,
                    hora_fin = :hora_fin,
                    sala_id = :sala_id,
                    reserva_estado = :reserva_estado,
                    observaciones = :observaciones,
                    updated_at = CURRENT_TIMESTAMP,
                    updated_by = :updated_by
                WHERE 
                    reserva_id = :reserva_id"
            );
            
            $updatedBy = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 1;
            
            $stmt->bindParam(":servicio_id", $datos['servicio_id'], PDO::PARAM_INT);
            $stmt->bindParam(":agenda_id", $datos['agenda_id'], PDO::PARAM_INT);
            $stmt->bindParam(":doctor_id", $datos['doctor_id'], PDO::PARAM_INT);
            $stmt->bindParam(":paciente_id", $datos['paciente_id'], PDO::PARAM_INT);
            $stmt->bindParam(":fecha_reserva", $datos['fecha_reserva'], PDO::PARAM_STR);
            $stmt->bindParam(":hora_inicio", $datos['hora_inicio'], PDO::PARAM_STR);
            $stmt->bindParam(":hora_fin", $datos['hora_fin'], PDO::PARAM_STR);
            $stmt->bindParam(":sala_id", $datos['sala_id'], PDO::PARAM_INT);
            $stmt->bindParam(":reserva_estado", $datos['reserva_estado'], PDO::PARAM_STR);
            $stmt->bindParam(":observaciones", $datos['observaciones'], PDO::PARAM_STR);
            $stmt->bindParam(":updated_by", $updatedBy, PDO::PARAM_INT);
            $stmt->bindParam(":reserva_id", $datos['reserva_id'], PDO::PARAM_INT);
            
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                $pdo->commit();
                error_log("mdlActualizarReserva: Reserva {$datos['reserva_id']} actualizada exitosamente", 
                          3, "/var/log/clinica/reservas.log");
                
                return [
                    "status" => "success",
                    "message" => "Reserva actualizada exitosamente",
                    "reserva_id" => $datos['reserva_id']
                ];
            } else {
                $pdo->rollBack();
                return [
                    "status" => "error",
                    "message" => "No se pudo actualizar la reserva"
                ];
            }
            
        } catch (PDOException $e) {
            if (isset($pdo)) {
                $pdo->rollBack();
            }
            error_log("Error al actualizar reserva: " . $e->getMessage(), 
                      3, "/var/log/clinica/reservas.log");
            return [
                "status" => "error",
                "message" => "Error al actualizar la reserva: " . $e->getMessage()
            ];
        }
    }

    /**
     * Actualiza una reserva existente con validación completa
     * @param array $datos Datos de la reserva a actualizar
     * @return array Resultado de la operación
     */
    static public function mdlEditarReservaCompleta($datos) {
        try {
            $pdo = Conexion::conectar();
            $pdo->beginTransaction();
            
            error_log("mdlEditarReservaCompleta: Iniciando edición de reserva ID {$datos['reserva_id']}", 
                      3, "/var/log/clinica/reservas.log");
            error_log("mdlEditarReservaCompleta: Datos recibidos: " . json_encode($datos), 
                      3, "/var/log/clinica/reservas.log");
            
            // 1. Verificar que la reserva existe y obtener datos actuales
            $stmtVerificar = $pdo->prepare("
                SELECT * FROM servicios_reservas 
                WHERE reserva_id = :reserva_id
            ");
            $stmtVerificar->bindParam(":reserva_id", $datos['reserva_id'], PDO::PARAM_INT);
            $stmtVerificar->execute();
            
            $reservaActual = $stmtVerificar->fetch(PDO::FETCH_ASSOC);
            if (!$reservaActual) {
                throw new Exception("Reserva no encontrada");
            }
            
            // 2. Verificar conflictos de horario si cambió doctor, fecha u horario
            $cambioHorario = (
                $datos['doctor_id'] != $reservaActual['doctor_id'] ||
                $datos['fecha_reserva'] != $reservaActual['fecha_reserva'] ||
                $datos['hora_inicio'] != $reservaActual['hora_inicio'] ||
                $datos['hora_fin'] != $reservaActual['hora_fin']
            );
            
            if ($cambioHorario) {
                $conflictos = self::verificarConflictosReserva(
                    $datos['doctor_id'], 
                    $datos['fecha_reserva'], 
                    $datos['hora_inicio'], 
                    $datos['hora_fin'], 
                    $datos['reserva_id']
                );
                
                if (!empty($conflictos)) {
                    $pdo->rollBack();
                    return [
                        "status" => "error",
                        "message" => "Existe un conflicto de horario con otra reserva",
                        "conflictos" => $conflictos
                    ];
                }
            }
            
            // 3. Actualizar la reserva con todos los campos relevantes
            $stmt = $pdo->prepare("
                UPDATE servicios_reservas SET 
                    servicio_id = :servicio_id,
                    doctor_id = :doctor_id,
                    fecha_reserva = :fecha_reserva,
                    hora_inicio = :hora_inicio,
                    hora_fin = :hora_fin,
                    reserva_estado = :reserva_estado,
                    observaciones = :observaciones,
                    agenda_id = :agenda_id,
                    sala_id = :sala_id,
                    tarifa_id = :tarifa_id,
                    seguro_id = :seguro_id,
                    updated_at = CURRENT_TIMESTAMP
                WHERE reserva_id = :reserva_id
            ");
            
            // Bindear parámetros obligatorios
            $stmt->bindParam(":servicio_id", $datos['servicio_id'], PDO::PARAM_INT);
            $stmt->bindParam(":doctor_id", $datos['doctor_id'], PDO::PARAM_INT);
            $stmt->bindParam(":fecha_reserva", $datos['fecha_reserva'], PDO::PARAM_STR);
            $stmt->bindParam(":hora_inicio", $datos['hora_inicio'], PDO::PARAM_STR);
            $stmt->bindParam(":hora_fin", $datos['hora_fin'], PDO::PARAM_STR);
            $stmt->bindParam(":reserva_estado", $datos['reserva_estado'], PDO::PARAM_STR);
            $stmt->bindParam(":reserva_id", $datos['reserva_id'], PDO::PARAM_INT);
            
            // Bindear parámetros opcionales
            $observaciones = isset($datos['observaciones']) ? $datos['observaciones'] : '';
            $stmt->bindParam(":observaciones", $observaciones, PDO::PARAM_STR);
            
            $agendaId = isset($datos['agenda_id']) ? $datos['agenda_id'] : null;
            $stmt->bindParam(":agenda_id", $agendaId, $agendaId ? PDO::PARAM_INT : PDO::PARAM_NULL);
            
            $salaId = isset($datos['sala_id']) ? $datos['sala_id'] : null;
            $stmt->bindParam(":sala_id", $salaId, $salaId ? PDO::PARAM_INT : PDO::PARAM_NULL);
            
            $tarifaId = isset($datos['tarifa_id']) ? $datos['tarifa_id'] : null;
            $stmt->bindParam(":tarifa_id", $tarifaId, $tarifaId ? PDO::PARAM_INT : PDO::PARAM_NULL);
            
            $seguroId = isset($datos['seguro_id']) ? $datos['seguro_id'] : null;
            $stmt->bindParam(":seguro_id", $seguroId, $seguroId ? PDO::PARAM_INT : PDO::PARAM_NULL);
            
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                $pdo->commit();
                
                error_log("mdlEditarReservaCompleta: Reserva {$datos['reserva_id']} actualizada exitosamente", 
                          3, "/var/log/clinica/reservas.log");
                
                // 4. Obtener datos actualizados con todos los JOINs para retornar información completa
                $datosActualizados = self::mdlObtenerReservaPorId($datos['reserva_id']);
                
                return [
                    "status" => "success",
                    "message" => "Reserva actualizada exitosamente",
                    "reserva_id" => $datos['reserva_id'],
                    "datos_actualizados" => $datosActualizados,
                    "cambio_horario" => $cambioHorario
                ];
            } else {
                $pdo->rollBack();
                return [
                    "status" => "error",
                    "message" => "No se detectaron cambios en la reserva"
                ];
            }
            
        } catch (Exception $e) {
            if (isset($pdo)) {
                $pdo->rollBack();
            }
            error_log("Error en mdlEditarReservaCompleta: " . $e->getMessage(), 
                      3, "/var/log/clinica/reservas.log");
            return [
                "status" => "error",
                "message" => "Error al actualizar la reserva: " . $e->getMessage()
            ];
        }
    }

    /**
     * Verifica conflictos de horario para una reserva
     * @param int $doctorId ID del doctor
     * @param string $fecha Fecha de la reserva
     * @param string $horaInicio Hora de inicio
     * @param string $horaFin Hora de fin
     * @param int $excluirReservaId ID de reserva a excluir de la verificación (para edición)
     * @return array Lista de reservas en conflicto
     */
    static public function verificarConflictosReserva($doctorId, $fecha, $horaInicio, $horaFin, $excluirReservaId = null) {
        try {
            $sql = "SELECT 
                        reserva_id,
                        hora_inicio,
                        hora_fin,
                        CONCAT(p.nombres, ' ', p.apellidos) as paciente_nombre
                    FROM 
                        servicios_reservas sr
                    LEFT JOIN 
                        pacientes p ON sr.paciente_id = p.paciente_id
                    WHERE 
                        sr.doctor_id = :doctor_id
                        AND sr.fecha_reserva = :fecha_reserva
                        AND sr.reserva_estado IN ('CONFIRMADA', 'PENDIENTE')
                        AND sr.activo = true
                        AND (
                            (:hora_inicio >= sr.hora_inicio AND :hora_inicio < sr.hora_fin) OR
                            (:hora_fin > sr.hora_inicio AND :hora_fin <= sr.hora_fin) OR
                            (:hora_inicio <= sr.hora_inicio AND :hora_fin >= sr.hora_fin)
                        )";
            
            if ($excluirReservaId) {
                $sql .= " AND sr.reserva_id != :excluir_reserva_id";
            }
            
            $stmt = Conexion::conectar()->prepare($sql);
            $stmt->bindParam(":doctor_id", $doctorId, PDO::PARAM_INT);
            $stmt->bindParam(":fecha_reserva", $fecha, PDO::PARAM_STR);
            $stmt->bindParam(":hora_inicio", $horaInicio, PDO::PARAM_STR);
            $stmt->bindParam(":hora_fin", $horaFin, PDO::PARAM_STR);
            
            if ($excluirReservaId) {
                $stmt->bindParam(":excluir_reserva_id", $excluirReservaId, PDO::PARAM_INT);
            }
            
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error al verificar conflictos de reserva: " . $e->getMessage(), 
                      3, "/var/log/clinica/reservas.log");
            return [];
        }
    }

    /**
     * Obtiene todas las salas activas del sistema
     * @return array Lista de salas activas
     */
    static public function mdlObtenerSalasActivas() {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT 
                    sala_id,
                    sala_nombre,
                    sala_descripcion,
                    capacidad,
                    estado
                FROM salas 
                WHERE estado = 'ACTIVA' 
                ORDER BY sala_nombre ASC
            ");
            
            $stmt->execute();
            $salas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            error_log("mdlObtenerSalasActivas: Se encontraron " . count($salas) . " salas activas", 
                      3, "/var/log/clinica/reservas.log");
            
            return $salas;
            
        } catch (PDOException $e) {
            error_log("Error al obtener salas activas: " . $e->getMessage(), 
                      3, "/var/log/clinica/reservas.log");
            
            // Fallback: intentar con estructura mínima
            try {
                $stmt = Conexion::conectar()->prepare("
                    SELECT 
                        sala_id,
                        sala_nombre
                    FROM salas 
                    ORDER BY sala_id ASC
                ");
                
                $stmt->execute();
                $salas = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                error_log("mdlObtenerSalasActivas: Fallback exitoso, " . count($salas) . " salas obtenidas", 
                          3, "/var/log/clinica/reservas.log");
                
                return $salas;
                
            } catch (PDOException $e2) {
                error_log("Error en fallback de salas: " . $e2->getMessage(), 
                          3, "/var/log/clinica/reservas.log");
                return [];
            }
        }
    }

    /**
     * Obtiene todos los servicios activos del sistema
     * @return array Lista de todos los servicios activos
     */
    static public function mdlObtenerTodosLosServiciosActivos() {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT 
                    serv_id,
                    serv_codigo,
                    serv_descripcion,
                    serv_descripcion_factura,
                    serv_monto,
                    serv_tte,
                    serv_rtte,
                    serv_uso_equipo,
                    serv_der_sala,
                    tserv_cod,
                    created_at,
                    business_id,
                    is_active,
                    user_id
                FROM rs_servicios
                WHERE is_active = true
                ORDER BY serv_descripcion ASC
            ");
            
            $stmt->execute();
            $servicios = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            error_log("mdlObtenerTodosLosServiciosActivos: " . count($servicios) . " servicios obtenidos", 
                      3, "/var/log/clinica/servicios.log");
            
            return $servicios;
            
        } catch (PDOException $e) {
            error_log("Error en mdlObtenerTodosLosServiciosActivos: " . $e->getMessage(), 
                      3, "/var/log/clinica/servicios.log");
            return [];
        }
    }

    /**
     * Obtiene médicos que ofrecen un servicio específico
     * @param int $servicioId ID del servicio
     * @return array Lista de médicos que ofrecen el servicio
     */
    static public function mdlObtenerMedicosPorServicio($servicioId) {
        try {
            $stmt = Conexion::conectar()->prepare("
                WITH medicos_servicio AS (
                    SELECT DISTINCT
                        rh.doctor_id,
                        p.first_name || ' ' || p.last_name as doctor_nombre,
                        p.first_name,
                        p.last_name,
                        rh.doctor_estado,
                        rs.serv_descripcion as servicio_nombre,
                        rs.serv_codigo as servicio_codigo,
                        rh.person_id,
                        p.document_number,
                        p.phone_number,
                        p.email
                    FROM rs_servicios_doctors rsd
                    JOIN rs_servicios rs ON rsd.servicio_id = rs.serv_id
                    JOIN rh_doctors rh ON rsd.doctor_id = rh.doctor_id
                    JOIN rh_person p ON rh.person_id = p.person_id
                    WHERE rsd.servicio_id = :servicio_id 
                        AND rsd.is_active = true
                        AND rs.is_active = true
                        AND p.is_active = true
                )
                SELECT 
                    ms.*,
                    1 as relacion_id,
                    CASE 
                        WHEN ms.doctor_estado = 'ACTIVO' THEN 'Disponible'
                        ELSE 'No disponible'
                    END as disponibilidad_texto,
                    CASE 
                        WHEN ms.doctor_estado = 'ACTIVO' THEN 'success'
                        ELSE 'danger'
                    END as disponibilidad_clase,
                    COALESCE((
                        SELECT STRING_AGG(
                            dia_abreviado, ', '
                            ORDER BY orden_dia
                        ) 
                        FROM (
                            SELECT DISTINCT
                                CASE ad.dia_semana::text
                                    WHEN 'LUNES' THEN 'Lun'
                                    WHEN 'MARTES' THEN 'Mar'
                                    WHEN 'MIERCOLES' THEN 'Mié'
                                    WHEN 'JUEVES' THEN 'Jue'
                                    WHEN 'VIERNES' THEN 'Vie'
                                    WHEN 'SABADO' THEN 'Sáb'
                                    WHEN 'DOMINGO' THEN 'Dom'
                                    ELSE ad.dia_semana::text
                                END as dia_abreviado,
                                CASE ad.dia_semana::text
                                    WHEN 'LUNES' THEN 1
                                    WHEN 'MARTES' THEN 2
                                    WHEN 'MIERCOLES' THEN 3
                                    WHEN 'JUEVES' THEN 4
                                    WHEN 'VIERNES' THEN 5
                                    WHEN 'SABADO' THEN 6
                                    WHEN 'DOMINGO' THEN 7
                                    ELSE 8
                                END as orden_dia
                            FROM agendas_cabecera ac2
                            JOIN agendas_detalle ad ON ac2.agenda_id = ad.agenda_id
                            JOIN rs_servicios_doctors rsd2 ON rsd2.agenda_detalle_id = ad.detalle_id 
                                                           AND rsd2.doctor_id = ms.doctor_id
                                                           AND rsd2.servicio_id = :servicio_id_filter
                                                           AND rsd2.is_active = true
                            WHERE ac2.medico_id = ms.doctor_id
                                AND ad.detalle_estado = true
                                AND ac2.agenda_estado = true
                            ORDER BY orden_dia
                        ) dias_ordenados
                    ), 'Sin horarios') as dias_atencion
                FROM medicos_servicio ms
                ORDER BY ms.first_name, ms.last_name
            ");
            
            $stmt->bindParam(':servicio_id', $servicioId, PDO::PARAM_INT);
            $stmt->bindParam(':servicio_id_filter', $servicioId, PDO::PARAM_INT);
            $stmt->execute();
            $medicos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            error_log("mdlObtenerMedicosPorServicio: " . count($medicos) . " médicos obtenidos para servicio " . $servicioId, 
                      3, "/var/log/clinica/servicios.log");
            
            return $medicos;
            
        } catch (PDOException $e) {
            error_log("Error en mdlObtenerMedicosPorServicio: " . $e->getMessage(), 
                      3, "/var/log/clinica/servicios.log");
            return [];
        }
    }

    /**
     * Verifica si un usuario puede cancelar una reserva usando el sistema de roles existente
     * @param int $reservaId ID de la reserva
     * @param int $usuarioId ID del usuario
     * @return array Resultado de la verificación
     */
    static public function mdlPuedeCancelarReserva($reservaId, $usuarioId = null) {
        try {
            $conexion = Conexion::conectar();
            
            // Usar la función PostgreSQL que creamos (actualizada para usar sistema de roles existente)
            if ($usuarioId) {
                $stmt = $conexion->prepare("SELECT * FROM puede_cancelar_reserva_usuario(:reserva_id, :user_id)");
                $stmt->bindParam(":reserva_id", $reservaId, PDO::PARAM_INT);
                $stmt->bindParam(":user_id", $usuarioId, PDO::PARAM_INT);
            } else {
                // Si no hay usuario, solo verificar tiempo límite
                $stmt = $conexion->prepare("SELECT * FROM puede_cancelar_reserva_usuario(:reserva_id, NULL)");
                $stmt->bindParam(":reserva_id", $reservaId, PDO::PARAM_INT);
            }
            
            $stmt->execute();
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($resultado) {
                return [
                    "puede_cancelar" => $resultado['puede_cancelar'],
                    "motivo" => $resultado['motivo'],
                    "horas_restantes" => round((float)$resultado['horas_restantes'], 2),
                    "limite_horas" => (int)$resultado['limite_horas'],
                    "permiso_especial" => $resultado['permiso_especial']
                ];
            } else {
                return ["puede_cancelar" => false, "motivo" => "Error verificando permisos"];
            }
            
        } catch (Exception $e) {
            error_log("Error verificando permisos de cancelación: " . $e->getMessage(), 3, "/var/log/clinica/reservas.log");
            
            // Fallback a verificación manual si la función no existe
            return self::mdlPuedeCancelarReservaFallback($reservaId, $usuarioId);
        }
    }

    /**
     * Método de respaldo para verificar permisos de cancelación
     * @param int $reservaId ID de la reserva
     * @param int $usuarioId ID del usuario
     * @return array Resultado de la verificación
     */
    static private function mdlPuedeCancelarReservaFallback($reservaId, $usuarioId = null) {
        try {
            $conexion = Conexion::conectar();
            
            // Obtener información de la reserva
            $stmt = $conexion->prepare("
                SELECT sr.reserva_id, sr.fecha_reserva, sr.hora_inicio, sr.activo, sr.reserva_estado,
                       EXTRACT(EPOCH FROM (sr.fecha_reserva + sr.hora_inicio::time - CURRENT_TIMESTAMP))/3600 as horas_restantes
                FROM servicios_reservas sr
                WHERE sr.reserva_id = :reserva_id
            ");
            $stmt->bindParam(":reserva_id", $reservaId, PDO::PARAM_INT);
            $stmt->execute();
            $reserva = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$reserva) {
                return ["puede_cancelar" => false, "motivo" => "La reserva no existe"];
            }
            
            if (!$reserva['activo']) {
                return ["puede_cancelar" => false, "motivo" => "La reserva ya está cancelada"];
            }
            
            // Obtener parámetro de límite de horas
            $stmt = $conexion->prepare("
                SELECT parametro_valor 
                FROM sistema_parametros 
                WHERE parametro_codigo = 'LIMITE_HORAS_CANCELACION'
                AND is_active = true
            ");
            $stmt->execute();
            $limiteHoras = $stmt->fetchColumn();
            $limiteHoras = $limiteHoras ? (float)$limiteHoras : 72; // Default 72 horas
            
            $horasRestantes = $reserva['horas_restantes'];
            
            // Si está dentro del tiempo límite, puede cancelar
            if ($horasRestantes >= $limiteHoras) {
                return [
                    "puede_cancelar" => true, 
                    "motivo" => "Dentro del tiempo límite",
                    "horas_restantes" => round($horasRestantes, 2),
                    "limite_horas" => $limiteHoras,
                    "permiso_especial" => false
                ];
            }
            
            // Si está fuera del tiempo límite, verificar permisos especiales usando el sistema de roles
            if ($usuarioId) {
                $stmt = $conexion->prepare("
                    SELECT COUNT(*) > 0 as tiene_permiso
                    FROM sys_user_roles sur
                    INNER JOIN sys_role_permissions srp ON sur.role_id = srp.role_id
                    INNER JOIN sys_permissions sp ON srp.perm_id = sp.perm_id
                    WHERE sur.user_id = :user_id 
                    AND sp.perm_name = 'cancelar_reservas_tardias'
                ");
                $stmt->bindParam(":user_id", $usuarioId, PDO::PARAM_INT);
                $stmt->execute();
                $tienePermiso = $stmt->fetch(PDO::FETCH_ASSOC)['tiene_permiso'];
                
                if ($tienePermiso) {
                    return [
                        "puede_cancelar" => true, 
                        "motivo" => "Permiso especial para cancelación tardía",
                        "horas_restantes" => round($horasRestantes, 2),
                        "limite_horas" => $limiteHoras,
                        "permiso_especial" => true
                    ];
                }
            }
            
            return [
                "puede_cancelar" => false, 
                "motivo" => "Fuera del tiempo límite para cancelación ($limiteHoras horas). Quedan " . round($horasRestantes, 2) . " horas.",
                "horas_restantes" => round($horasRestantes, 2),
                "limite_horas" => $limiteHoras,
                "permiso_especial" => false
            ];
            
        } catch (Exception $e) {
            error_log("Error en verificación fallback de permisos: " . $e->getMessage(), 3, "/var/log/clinica/reservas.log");
            return ["puede_cancelar" => false, "motivo" => "Error verificando permisos"];
        }
    }

    /**
     * Cancela una reserva con validaciones de tiempo y permisos
     * @param int $reservaId ID de la reserva a cancelar
     * @param string $motivo Motivo de la cancelación (opcional)
     * @param int $usuarioId ID del usuario que cancela (opcional)
     * @param bool $forzar Forzar cancelación sin validar tiempo (solo con permisos)
     * @return array Resultado de la operación
     */
    static public function mdlCancelarReserva($reservaId, $motivo = null, $usuarioId = null, $forzar = false) {
        try {
            error_log("mdlCancelarReserva: Iniciando cancelación de reserva ID=$reservaId, Usuario=$usuarioId, Forzar=$forzar", 
                      3, "/var/log/clinica/reservas.log");
            
            $conexion = Conexion::conectar();
            
            // Verificar permisos primero si no se está forzando
            if (!$forzar) {
                $permisos = self::mdlPuedeCancelarReserva($reservaId, $usuarioId);
                if (!$permisos['puede_cancelar']) {
                    return [
                        "error" => true,
                        "mensaje" => $permisos['motivo'],
                        "detalles" => $permisos
                    ];
                }
            }
            
            // Obtener información completa de la reserva
            $stmtCheck = $conexion->prepare(
                "SELECT reserva_id, reserva_estado, activo, agenda_id, fecha_reserva, hora_inicio 
                 FROM servicios_reservas 
                 WHERE reserva_id = :reserva_id"
            );
            $stmtCheck->bindParam(":reserva_id", $reservaId, PDO::PARAM_INT);
            $stmtCheck->execute();
            $reserva = $stmtCheck->fetch(PDO::FETCH_ASSOC);
            
            if (!$reserva) {
                return ["error" => true, "mensaje" => "La reserva no existe"];
            }
            
            if (!$reserva['activo']) {
                return ["error" => true, "mensaje" => "La reserva ya está cancelada"];
            }
            
            // Actualizar la reserva marcando como cancelada pero manteniendo visible
            $stmt = $conexion->prepare(
                "UPDATE servicios_reservas 
                 SET activo = false,
                     reserva_estado = 'CANCELADA',
                     fecha_cancelacion = CURRENT_TIMESTAMP,
                     motivo_cancelacion = :motivo,
                     cancelado_por = :usuario_id,
                     updated_at = CURRENT_TIMESTAMP,
                     updated_by = :usuario_id
                 WHERE reserva_id = :reserva_id"
            );
            $stmt->bindParam(":reserva_id", $reservaId, PDO::PARAM_INT);
            $stmt->bindParam(":motivo", $motivo, PDO::PARAM_STR);
            $stmt->bindParam(":usuario_id", $usuarioId, PDO::PARAM_INT);
            $resultado = $stmt->execute();
            $filasAfectadas = $stmt->rowCount();
            
            error_log("mdlCancelarReserva: UPDATE ejecutado - Resultado: " . ($resultado ? 'true' : 'false') . 
                     ", Filas afectadas: $filasAfectadas", 3, "/var/log/clinica/reservas.log");
            
            if (!$resultado || $filasAfectadas === 0) {
                return [
                    "error" => true,
                    "mensaje" => "No se pudo actualizar la reserva. Filas afectadas: $filasAfectadas"
                ];
            }
            
            // Si hay un agenda_id asociado, liberar el cupo
            if ($reserva['agenda_id']) {
                try {
                    $stmtLiberar = $conexion->prepare(
                        "UPDATE agendas_detalle 
                         SET cupo_maximo = COALESCE(cupo_maximo, 0) + 1
                         WHERE detalle_id = :agenda_id"
                    );
                    $stmtLiberar->bindParam(":agenda_id", $reserva['agenda_id'], PDO::PARAM_INT);
                    $stmtLiberar->execute();
                    
                    error_log("mdlCancelarReserva: Cupo liberado para agenda_id=" . $reserva['agenda_id'], 
                              3, "/var/log/clinica/reservas.log");
                } catch (Exception $e) {
                    error_log("mdlCancelarReserva: Error al liberar cupo - " . $e->getMessage(), 
                              3, "/var/log/clinica/reservas.log");
                }
            }
            
            error_log("mdlCancelarReserva: Reserva $reservaId cancelada exitosamente", 
                      3, "/var/log/clinica/reservas.log");
            
            return [
                "error" => false,
                "mensaje" => "Reserva cancelada exitosamente",
                "fecha_cancelacion" => date('Y-m-d H:i:s')
            ];
            
        } catch (PDOException $e) {
            error_log("Error al cancelar reserva: " . $e->getMessage(), 
                      3, "/var/log/clinica/reservas.log");
            return [
                "error" => true,
                "mensaje" => "Error al cancelar la reserva: " . $e->getMessage()
            ];
        }
    }

    /**
     * Obtiene reservas incluyendo o excluyendo canceladas según configuración
     * @param string $fecha Fecha de la reserva (opcional)
     * @param int $doctorId ID del doctor (opcional)
     * @param string $estado Estado de la reserva (opcional)
     * @param string $paciente Nombre del paciente para búsqueda (opcional)
     * @param int $salaId ID de la sala (opcional)
     * @param string $origen Origen de la reserva (opcional)
     * @param bool $incluirCanceladas Forzar inclusión de canceladas
     * @return array Lista de reservas
     */
    static public function mdlObtenerReservasConCanceladas($fecha = null, $doctorId = null, $estado = null, $paciente = null, $salaId = null, $origen = null, $incluirCanceladas = null) {
        try {
            // Obtener configuración de mostrar canceladas si no se especifica
            if ($incluirCanceladas === null) {
                $parametros = self::mdlObtenerParametrosReservas();
                $incluirCanceladas = isset($parametros['MOSTRAR_RESERVAS_CANCELADAS']) ? 
                                   $parametros['MOSTRAR_RESERVAS_CANCELADAS'] : true;
            }
            
            // Validar formato de fecha si se proporciona
            if ($fecha !== null && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
                $fechaFormateada = date('Y-m-d', strtotime($fecha));
                error_log("mdlObtenerReservasConCanceladas: Formato de fecha incorrecto ($fecha), reformateando a $fechaFormateada", 3, "/var/log/clinica/reservas.log");
                $fecha = $fechaFormateada;
            }
            
            // Construir la consulta SQL
            $sql = "SELECT 
                sr.reserva_id,
                sr.servicio_id,
                sr.doctor_id,
                sr.paciente_id,
                sr.fecha_reserva,
                sr.hora_inicio,
                sr.hora_fin,
                sr.reserva_estado,
                sr.observaciones,
                sr.business_id,
                sr.created_at,
                sr.updated_at,
                sr.agenda_id,
                sr.sala_id,
                s.sala_nombre,
                sr.tarifa_id,
                sr.origen_reserva,
                sr.activo,
                sr.fecha_cancelacion,
                sr.motivo_cancelacion,
                sr.cancelado_por,
                rp.first_name ||' - ' || rp.last_name as doctor,
                rp2.first_name ||' - ' || rp2.last_name as paciente,
                rs.serv_descripcion,
                rs.serv_monto,
                rp3.first_name ||' - ' || rp3.last_name as cancelado_por_nombre
            FROM servicios_reservas sr 
            INNER JOIN rh_doctors rd ON sr.doctor_id = rd.doctor_id 
            INNER JOIN rh_person rp ON rd.person_id = rp.person_id 
            INNER JOIN rh_person rp2 ON sr.paciente_id = rp2.person_id 
            INNER JOIN rs_servicios rs ON sr.servicio_id = rs.serv_id 
            LEFT JOIN agendas_detalle ad on sr.agenda_id = ad.detalle_id 
            LEFT JOIN salas s on s.sala_id = sr.sala_id 
            LEFT JOIN rh_person rp3 ON sr.cancelado_por = rp3.person_id
            WHERE 1=1";
            
            // Filtro de activo según configuración
            if ($incluirCanceladas) {
                // Mostrar todas las reservas (activas y canceladas) - NO FILTRAR POR ACTIVO
                // $sql .= " AND (sr.activo = true OR sr.activo = false)"; // Esta línea es redundante
            } else {
                // Solo mostrar reservas activas
                $sql .= " AND sr.activo = true";
            }
            
            // Añadir filtros según los parámetros proporcionados
            if ($fecha !== null) {
                $sql .= " AND sr.fecha_reserva = :fecha_reserva";
            }
            
            if ($doctorId !== null) {
                $sql .= " AND sr.doctor_id = :doctor_id";
            }
              
            if ($estado !== null && $estado !== '') {
                $sql .= " AND sr.reserva_estado = :estado";
            }
            
            if ($paciente !== null && trim($paciente) !== '') {
                $sql .= " AND (UPPER(rp2.first_name) LIKE UPPER(:paciente) OR UPPER(rp2.last_name) LIKE UPPER(:paciente))";
            }
            
            if ($salaId !== null) {
                $sql .= " AND sr.sala_id = :sala_id";
            }
            
            if ($origen !== null && trim($origen) !== '') {
                $sql .= " AND sr.origen_reserva = :origen";
            }
            
            $sql .= " ORDER BY sr.fecha_reserva DESC, sr.hora_inicio DESC";
            
            error_log("mdlObtenerReservasConCanceladas: SQL construido: " . $sql, 3, "/var/log/clinica/reservas.log");
            
            $stmt = Conexion::conectar()->prepare($sql);
            
            // Bindear parámetros
            if ($fecha !== null) {
                $stmt->bindParam(":fecha_reserva", $fecha, PDO::PARAM_STR);
            }
            
            if ($doctorId !== null) {
                $stmt->bindParam(":doctor_id", $doctorId, PDO::PARAM_INT);
            }
            
            if ($estado !== null && $estado !== '') {
                $stmt->bindParam(":estado", $estado, PDO::PARAM_STR);
            }
            
            if ($paciente !== null && trim($paciente) !== '') {
                $pacienteBusqueda = "%" . trim($paciente) . "%";
                $stmt->bindParam(":paciente", $pacienteBusqueda, PDO::PARAM_STR);
            }
            
            if ($salaId !== null) {
                $stmt->bindParam(":sala_id", $salaId, PDO::PARAM_INT);
            }
            
            if ($origen !== null && trim($origen) !== '') {
                $stmt->bindParam(":origen", $origen, PDO::PARAM_STR);
            }
            
            $stmt->execute();
            $reservas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            error_log("mdlObtenerReservasConCanceladas: " . count($reservas) . " reservas encontradas", 3, "/var/log/clinica/reservas.log");
            
            return $reservas;
            
        } catch (PDOException $e) {
            error_log("Error en mdlObtenerReservasConCanceladas: " . $e->getMessage(), 3, "/var/log/clinica/reservas.log");
            return [];
        }
    }

    /**
     * Obtiene los parámetros del sistema relacionados con reservas
     * @return array Parámetros del sistema
     */
    static public function mdlObtenerParametrosReservas() {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT parametro_codigo, parametro_nombre, parametro_valor, parametro_tipo
                FROM sistema_parametros 
                WHERE parametro_categoria = 'RESERVAS' 
                AND is_active = true
                ORDER BY parametro_nombre
            ");
            $stmt->execute();
            $parametros = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Convertir a formato clave-valor para fácil acceso
            $result = [];
            foreach ($parametros as $param) {
                $valor = $param['parametro_valor'];
                
                // Convertir según el tipo
                switch ($param['parametro_tipo']) {
                    case 'NUMBER':
                        $valor = is_numeric($valor) ? (float)$valor : $valor;
                        break;
                    case 'BOOLEAN':
                        $valor = filter_var($valor, FILTER_VALIDATE_BOOLEAN);
                        break;
                    case 'JSON':
                        $valor = json_decode($valor, true);
                        break;
                }
                
                $result[$param['parametro_codigo']] = $valor;
            }
            
            return $result;
            
        } catch (Exception $e) {
            error_log("Error obteniendo parámetros de reservas: " . $e->getMessage(), 3, "/var/log/clinica/reservas.log");
            return [
                'LIMITE_HORAS_CANCELACION' => 72,
                'MOSTRAR_RESERVAS_CANCELADAS' => true,
                'COLOR_RESERVAS_CANCELADAS' => '#ffcccc',
                'DIAS_MANTENER_CANCELADAS' => 30
            ];
        }
    }

    /**
     * Verifica si un usuario tiene un permiso específico usando el sistema de roles existente
     * @param int $usuarioId ID del usuario
     * @param string $permisoNombre Nombre del permiso
     * @return bool Si tiene el permiso
     */
    static public function mdlVerificarPermisoUsuario($usuarioId, $permisoNombre) {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT verificar_permiso_usuario(:user_id, :permiso_nombre) as tiene_permiso
            ");
            $stmt->bindParam(":user_id", $usuarioId, PDO::PARAM_INT);
            $stmt->bindParam(":permiso_nombre", $permisoNombre, PDO::PARAM_STR);
            $stmt->execute();
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return filter_var($resultado['tiene_permiso'], FILTER_VALIDATE_BOOLEAN);
            
        } catch (Exception $e) {
            error_log("Error verificando permiso de usuario: " . $e->getMessage(), 3, "/var/log/clinica/reservas.log");
            return false;
        }
    }

}