<?php
require_once "conexion.php"; // Archivo para la conexión a la base de datos

class ModelConsulta {
    // Eliminamos las propiedades y constructor que causaban conflictos
    // con los métodos estáticos
      public static function mdlSetConsulta($datos) {
        // Verificar si existe la función de logs detallados
        $log_function_exists = function_exists('debug_detallado');
        
        if ($log_function_exists) {
            debug_detallado('MODELO', "Iniciando mdlSetConsulta", [
                'datos' => array_keys($datos),
                'es_actualizacion' => isset($datos["id_consulta"]) && !empty($datos["id_consulta"])
            ], 'info');
        }
        
        // Verificar si es una actualización o una nueva consulta
        if (isset($datos["id_consulta"]) && !empty($datos["id_consulta"])) {
            if ($log_function_exists) {
                debug_detallado('MODELO', "Redirigiendo a actualización de consulta", [
                    'id_consulta' => $datos["id_consulta"]
                ], 'info');
            }
            return self::mdlActualizarConsulta($datos);
        }
        
        // Obtener la conexión antes de usarla y verificar
        if ($log_function_exists) {
            debug_detallado('MODELO', "Intentando obtener conexión a BD", [], 'info');
        }
        
        $db = Conexion::conectar();
        if ($db === null) {
            $mensaje = "Error: No se pudo establecer conexión en mdlSetConsulta";
            error_log($mensaje);
            
            if ($log_function_exists) {
                debug_detallado('MODELO', $mensaje, [
                    'error' => error_get_last()
                ], 'error');
            }
            
            return "error_conexion";
        }
        
        if ($log_function_exists) {
            debug_detallado('MODELO', "Conexión establecida correctamente", [], 'success');
            
            // Verificar información del paciente
            try {
                $checkStmt = $db->prepare("SELECT person_id, first_name, last_name FROM rh_person WHERE person_id = :id_persona");
                $checkStmt->bindParam(":id_persona", $datos["idPersona"], PDO::PARAM_INT);
                $checkStmt->execute();
                $persona = $checkStmt->fetch(PDO::FETCH_ASSOC);
                
                if ($persona) {
                    debug_detallado('MODELO', "Paciente encontrado para la consulta", [
                        'person_id' => $persona['person_id'],
                        'nombre' => $persona['first_name'] . ' ' . $persona['last_name']
                    ], 'info');
                } else {
                    debug_detallado('MODELO', "No se encontró el paciente en la base de datos", [
                        'id_persona' => $datos["idPersona"]
                    ], 'warning');
                }
            } catch (Exception $e) {
                debug_detallado('MODELO', "Error al verificar paciente", [
                    'error' => $e->getMessage()
                ], 'error');
            }
        }
        
        // Verificar cada campo obligatorio y su tipo
        if ($log_function_exists) {
            $campos_obligatorios = [
                'motivoscomunes' => isset($datos["motivoscomunes"]) ? 'OK' : 'FALTA',
                'txtmotivo' => isset($datos["txtmotivo"]) ? 'OK' : 'FALTA',
                'id_persona' => isset($datos["idPersona"]) ? 'OK' : 'FALTA'
            ];
            
            debug_detallado('MODELO', "Verificando campos obligatorios", $campos_obligatorios, 'info');
        }
        
        // Preparar la consulta SQL para inserción
        if ($log_function_exists) {
            debug_detallado('MODELO', "Preparando consulta SQL para inserción", [], 'info');
        }
        
        try {
            // Determinar si hay un tipo de formulario específico y datos asociados
            $tipo_formulario = isset($datos["form_type"]) ? $datos["form_type"] : "general";
            
            // Preparar datos específicos para JSON (si corresponde)
            $datos_especificos = null;
            if ($tipo_formulario != "general") {
                // Convertir datos específicos del formulario a JSON
                $datos_json = [];
                foreach ($datos as $key => $value) {
                    // Excluir campos que ya se guardan en columnas propias
                    if (!in_array($key, [
                        'motivoscomunes', 'txtmotivo', 'visionod', 'visionoi', 'tensionod', 'tensionoi',
                        'consulta-textarea', 'receta-textarea', 'txtnota', 'proximaconsulta',
                        'whatsapptxt', 'email', 'id_user', 'id_reserva', 'idPersona', 'id_consulta',
                        'form_type'
                    ])) {
                        $datos_json[$key] = $value;
                    }
                }
                $datos_especificos = !empty($datos_json) ? json_encode($datos_json) : null;
            }
            
            if ($log_function_exists) {
                debug_detallado('MODELO', "Tipo de formulario detectado", [
                    'tipo_formulario' => $tipo_formulario,
                    'datos_especificos' => !empty($datos_json) ? count($datos_json) : 0
                ], 'info');
            }
            
            $stmt = $db->prepare(
                "INSERT INTO consultas (
                    motivoscomunes, txtmotivo, visionod, visionoi, tensionod, tensionoi,
                    consulta_textarea, receta_textarea, txtnota, proximaconsulta,
                    whatsapptxt, email, id_user, id_reserva, id_persona, 
                    tipo_formulario, datos_especificos
                ) VALUES (
                    :motivoscomunes, :txtmotivo, :visionod, :visionoi, :tensionod, :tensionoi,
                    :consulta_textarea, :receta_textarea, :txtnota, :proximaconsulta,
                    :whatsapptxt, :email, :id_user, :id_reserva, :id_persona,
                    :tipo_formulario, :datos_especificos
                )"
            );
            
            if ($log_function_exists) {
                debug_detallado('MODELO', "Consulta SQL preparada correctamente", [], 'success');
            }
        } catch (PDOException $e) {
            $mensaje = "Error al preparar la consulta SQL: " . $e->getMessage();
            
            if ($log_function_exists) {
                debug_detallado('MODELO', $mensaje, [
                    'error_code' => $e->getCode(),
                    'sql_state' => $e->errorInfo[0] ?? 'desconocido'
                ], 'error');
            }
            
            return "error_sql: " . $e->getMessage();
        }        // Bind de parámetros
        if ($log_function_exists) {
            debug_detallado('MODELO', "Comenzando bind de parámetros", [], 'info');
        }
        
        try {
            // Crear un array con todos los binds para loguear errores específicos
            $bind_params = [
                ":motivoscomunes" => ["valor" => $datos["motivoscomunes"] ?? null, "tipo" => PDO::PARAM_STR],
                ":txtmotivo" => ["valor" => $datos["txtmotivo"] ?? null, "tipo" => PDO::PARAM_STR],
                ":visionod" => ["valor" => $datos["visionod"] ?? null, "tipo" => PDO::PARAM_STR],
                ":visionoi" => ["valor" => $datos["visionoi"] ?? null, "tipo" => PDO::PARAM_STR],
                ":tensionod" => ["valor" => $datos["tensionod"] ?? null, "tipo" => PDO::PARAM_STR],
                ":tensionoi" => ["valor" => $datos["tensionoi"] ?? null, "tipo" => PDO::PARAM_STR],
                ":consulta_textarea" => ["valor" => $datos["consulta-textarea"] ?? null, "tipo" => PDO::PARAM_STR],
                ":receta_textarea" => ["valor" => $datos["receta-textarea"] ?? null, "tipo" => PDO::PARAM_STR],
                ":txtnota" => ["valor" => $datos["txtnota"] ?? null, "tipo" => PDO::PARAM_STR],
                ":whatsapptxt" => ["valor" => $datos["whatsapptxt"] ?? null, "tipo" => PDO::PARAM_STR],
                ":email" => ["valor" => $datos["email"] ?? null, "tipo" => PDO::PARAM_STR],
                ":id_user" => ["valor" => $datos["id_user"] ?? null, "tipo" => PDO::PARAM_INT],
                ":id_reserva" => ["valor" => $datos["id_reserva"] ?? null, "tipo" => PDO::PARAM_INT],
                ":id_persona" => ["valor" => $datos["idPersona"] ?? null, "tipo" => PDO::PARAM_INT]
            ];
            
            // Loguear información sobre los parámetros
            if ($log_function_exists) {
                debug_detallado('MODELO', "Valores para bind de parámetros", $bind_params, 'debug');
            }
              // Realizar los binds uno por uno para detectar posibles errores
            // En bindParam debemos usar variables, no expresiones, porque espera una referencia
            $motivoscomunes = isset($datos["motivoscomunes"]) ? $datos["motivoscomunes"] : '';
            $txtmotivo = isset($datos["txtmotivo"]) ? $datos["txtmotivo"] : '';
            $visionod = isset($datos["visionod"]) ? $datos["visionod"] : '';
            $visionoi = isset($datos["visionoi"]) ? $datos["visionoi"] : '';
            $tensionod = isset($datos["tensionod"]) ? $datos["tensionod"] : '';
            $tensionoi = isset($datos["tensionoi"]) ? $datos["tensionoi"] : '';
            
            $stmt->bindParam(":motivoscomunes", $motivoscomunes, PDO::PARAM_STR);
            $stmt->bindParam(":txtmotivo", $txtmotivo, PDO::PARAM_STR);
            $stmt->bindParam(":visionod", $visionod, PDO::PARAM_STR);
            $stmt->bindParam(":visionoi", $visionoi, PDO::PARAM_STR);
            $stmt->bindParam(":tensionod", $tensionod, PDO::PARAM_STR);
            $stmt->bindParam(":tensionoi", $tensionoi, PDO::PARAM_STR);
              // Verificar si existe la clave "consulta-textarea"
            if (isset($datos["consulta-textarea"])) {
                $consulta_textarea = $datos["consulta-textarea"];
                $stmt->bindParam(":consulta_textarea", $consulta_textarea, PDO::PARAM_STR);
            } else {
                if ($log_function_exists) {
                    debug_detallado('MODELO', "Campo consulta-textarea no encontrado", [], 'warning');
                }
                $stmt->bindValue(":consulta_textarea", '', PDO::PARAM_STR);
            }
            
            // Verificar si existe la clave "receta-textarea"
            if (isset($datos["receta-textarea"])) {
                $receta_textarea = $datos["receta-textarea"];
                $stmt->bindParam(":receta_textarea", $receta_textarea, PDO::PARAM_STR);
            } else {
                if ($log_function_exists) {
                    debug_detallado('MODELO', "Campo receta-textarea no encontrado", [], 'warning');
                }
                $stmt->bindValue(":receta_textarea", '', PDO::PARAM_STR);
            }
            
            $txtnota = isset($datos["txtnota"]) ? $datos["txtnota"] : '';
            $stmt->bindParam(":txtnota", $txtnota, PDO::PARAM_STR);
              // Manejo de proximaconsulta (puede ser NULL)
            if (empty($datos["proximaconsulta"])) {
                if ($log_function_exists) {
                    debug_detallado('MODELO', "Campo proximaconsulta está vacío, asignando NULL", [], 'info');
                }
                $stmt->bindValue(":proximaconsulta", null, PDO::PARAM_NULL);
            } else {
                if ($log_function_exists) {
                    debug_detallado('MODELO', "Campo proximaconsulta tiene valor", [
                        'valor' => $datos["proximaconsulta"]
                    ], 'info');
                }
                $proximaconsulta = $datos["proximaconsulta"];
                $stmt->bindParam(":proximaconsulta", $proximaconsulta, PDO::PARAM_STR);
            }
            
            $whatsapptxt = isset($datos["whatsapptxt"]) ? $datos["whatsapptxt"] : '';
            $email = isset($datos["email"]) ? $datos["email"] : '';
            $stmt->bindParam(":whatsapptxt", $whatsapptxt, PDO::PARAM_STR);
            $stmt->bindParam(":email", $email, PDO::PARAM_STR);
            
            // Verificar valores de los IDs (deben ser enteros)
            $id_user = isset($datos["id_user"]) ? intval($datos["id_user"]) : 1;
            $id_reserva = isset($datos["id_reserva"]) ? intval($datos["id_reserva"]) : 0;
            $id_persona = isset($datos["idPersona"]) ? intval($datos["idPersona"]) : 0;
            
            if ($log_function_exists) {
                debug_detallado('MODELO', "IDs después de convertir a enteros", [
                    'id_user' => $id_user,
                    'id_reserva' => $id_reserva,
                    'id_persona' => $id_persona
                ], 'info');
            }
            
            $stmt->bindParam(":id_user", $id_user, PDO::PARAM_INT);
            $stmt->bindParam(":id_reserva", $id_reserva, PDO::PARAM_INT);
            $stmt->bindParam(":id_persona", $id_persona, PDO::PARAM_INT);
            
            // Bind de los nuevos parámetros
            $stmt->bindParam(":tipo_formulario", $tipo_formulario, PDO::PARAM_STR);
            if ($datos_especificos !== null) {
                $stmt->bindParam(":datos_especificos", $datos_especificos, PDO::PARAM_STR);
            } else {
                $stmt->bindValue(":datos_especificos", null, PDO::PARAM_NULL);
            }
            
            if ($log_function_exists) {
                debug_detallado('MODELO', "Todos los parámetros bindeados correctamente", [], 'success');
            }
            
        } catch (PDOException $e) {
            $mensaje = "Error en bind de parámetros: " . $e->getMessage();
            if ($log_function_exists) {
                debug_detallado('MODELO', $mensaje, [
                    'exception' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ], 'error');
            }
            return "error_bind: " . $e->getMessage();
        }
        
        // Ejecutar la consulta
        try {
            if ($log_function_exists) {
                debug_detallado('MODELO', "Intentando ejecutar la consulta SQL", [], 'info');
            }
            
            if ($stmt->execute()) {
                // Obtener el id del registro insertado usando la misma conexión
                $id_consulta = $db->lastInsertId();
                
                if ($log_function_exists) {
                    debug_detallado('MODELO', "Consulta ejecutada correctamente", [
                        'id_consulta' => $id_consulta
                    ], 'success');
                }
                
                return $id_consulta; // Devuelve el ID de la consulta insertada
            } else {
                $errorInfo = $stmt->errorInfo();
                $mensaje = "Error SQL en mdlSetConsulta: " . json_encode($errorInfo);
                error_log($mensaje);
                
                if ($log_function_exists) {
                    debug_detallado('MODELO', $mensaje, [
                        'error_code' => $errorInfo[0],
                        'sql_state' => $errorInfo[1],
                        'mensaje' => $errorInfo[2]
                    ], 'error');
                }
                
                return "error_sql: " . $errorInfo[2];
            }
        } catch (PDOException $e) {
            $mensaje = "Excepción PDO en mdlSetConsulta: " . $e->getMessage();
            error_log($mensaje);
            
            if ($log_function_exists) {
                debug_detallado('MODELO', $mensaje, [
                    'exception' => $e->getMessage(),
                    'code' => $e->getCode(),
                    'trace' => $e->getTraceAsString()
                ], 'error');
            }
            
            return "error_pdo: " . $e->getMessage();
        } catch (Exception $e) {
            $mensaje = "Excepción general en mdlSetConsulta: " . $e->getMessage();
            error_log($mensaje);
            
            if ($log_function_exists) {
                debug_detallado('MODELO', $mensaje, [
                    'exception' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ], 'error');
            }
            
            return "error_general: " . $e->getMessage();
        }
    }
    
    public static function mdlActualizarConsulta($datos) {
        // Determinar si hay un tipo de formulario específico y datos asociados
        $tipo_formulario = isset($datos["form_type"]) ? $datos["form_type"] : "general";
        
        // Preparar datos específicos para JSON (si corresponde)
        $datos_especificos = null;
        if ($tipo_formulario != "general") {
            // Convertir datos específicos del formulario a JSON
            $datos_json = [];
            foreach ($datos as $key => $value) {
                // Excluir campos que ya se guardan en columnas propias
                if (!in_array($key, [
                    'motivoscomunes', 'txtmotivo', 'visionod', 'visionoi', 'tensionod', 'tensionoi',
                    'consulta-textarea', 'receta-textarea', 'txtnota', 'proximaconsulta',
                    'whatsapptxt', 'email', 'id_user', 'id_reserva', 'idPersona', 'id_consulta',
                    'form_type'
                ])) {
                    $datos_json[$key] = $value;
                }
            }
            $datos_especificos = !empty($datos_json) ? json_encode($datos_json) : null;
        }
        
        // Preparar la consulta SQL para actualización
        $stmt = Conexion::conectar()->prepare(
            "UPDATE consultas SET 
                motivoscomunes = :motivoscomunes, 
                txtmotivo = :txtmotivo, 
                visionod = :visionod, 
                visionoi = :visionoi, 
                tensionod = :tensionod, 
                tensionoi = :tensionoi,
                consulta_textarea = :consulta_textarea, 
                receta_textarea = :receta_textarea, 
                txtnota = :txtnota, 
                proximaconsulta = :proximaconsulta,
                whatsapptxt = :whatsapptxt, 
                email = :email, 
                tipo_formulario = :tipo_formulario,
                datos_especificos = :datos_especificos,
                ultima_modificacion = NOW() 
            WHERE id_consulta = :id_consulta"
        );

        // Bind de parámetros
        $stmt->bindParam(":motivoscomunes", $datos["motivoscomunes"], PDO::PARAM_STR);
        $stmt->bindParam(":txtmotivo", $datos["txtmotivo"], PDO::PARAM_STR);
        $stmt->bindParam(":visionod", $datos["visionod"], PDO::PARAM_STR);
        $stmt->bindParam(":visionoi", $datos["visionoi"], PDO::PARAM_STR);
        $stmt->bindParam(":tensionod", $datos["tensionod"], PDO::PARAM_STR);
        $stmt->bindParam(":tensionoi", $datos["tensionoi"], PDO::PARAM_STR);
        $stmt->bindParam(":consulta_textarea", $datos["consulta-textarea"], PDO::PARAM_STR);
        $stmt->bindParam(":receta_textarea", $datos["receta-textarea"], PDO::PARAM_STR);
        $stmt->bindParam(":txtnota", $datos["txtnota"], PDO::PARAM_STR);
        // Manejo de proximaconsulta (puede ser NULL)
        if (empty($datos["proximaconsulta"])) {
            $stmt->bindValue(":proximaconsulta", null, PDO::PARAM_NULL); // Asignar NULL
        } else {
            $stmt->bindParam(":proximaconsulta", $datos["proximaconsulta"], PDO::PARAM_STR);
        }
        $stmt->bindParam(":whatsapptxt", $datos["whatsapptxt"], PDO::PARAM_STR);
        $stmt->bindParam(":email", $datos["email"], PDO::PARAM_STR);
        $stmt->bindParam(":tipo_formulario", $tipo_formulario, PDO::PARAM_STR);
        
        if ($datos_especificos !== null) {
            $stmt->bindParam(":datos_especificos", $datos_especificos, PDO::PARAM_STR);
        } else {
            $stmt->bindValue(":datos_especificos", null, PDO::PARAM_NULL);
        }
        
        $stmt->bindParam(":id_consulta", $datos["id_consulta"], PDO::PARAM_INT);

        // Ejecutar la consulta
        if ($stmt->execute()) {
            return "actualizado";
        } else {
            return "error";
        }
    }

    public static function mdlEliminarConsulta($id) {
        $stmt = Conexion::conectar()->prepare("DELETE FROM consultas WHERE id_consulta = :id");
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            return "ok";
        } else {
            return "error";
        }
    }
    public static function mdlGetConsultaResumen($datos) {
        $operacion = $datos["operacion"];
        $sql = "SELECT COUNT(id_consulta) AS cantidad_consultas,  to_char(MAX(fecha_registro::date),'dd/mm/yyyy') AS maxima_fecha_registro FROM   public.consultas where id_persona = :id_persona";
        if ($operacion === "resumenConsulta") {
            $stmt = Conexion::conectar()->prepare($sql);
        }
        
        $stmt->bindParam(":id_persona", $datos["id_persona"], PDO::PARAM_INT);
        
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public static function mdlGetConsultaPersona($persona) {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT 
                    c.id_consulta, c.motivoscomunes, c.txtmotivo, c.visionod, 
                    c.visionoi, c.tensionod, c.tensionoi, c.consulta_textarea, 
                    c.receta_textarea, c.txtnota, c.proximaconsulta, c.whatsapptxt, 
                    c.email, c.id_user, c.id_reserva, c.fecha_registro, 
                    c.ultima_modificacion, c.id_persona, c.tipo_formulario,
                    -- Información del doctor
                    -- rd.specialty AS especialidad_doctor,
                    rp.first_name AS nombre_doctor,
                    rp.last_name AS apellido_doctor,
                    rp.document_number AS documento_doctor
                FROM public.consultas c
                -- Unión con tablas para obtener información del doctor
                LEFT JOIN person_system_user psu ON c.id_user = psu.system_user_id
                LEFT JOIN rh_doctors rd ON psu.person_id = rd.person_id
                LEFT JOIN rh_person rp ON rp.person_id = rd.person_id
                WHERE c.id_persona = :id_persona
                ORDER BY c.fecha_registro DESC
            ");
            $stmt->bindParam(":id_persona", $persona, PDO::PARAM_INT);
            $stmt->execute();
    
            $consultas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
            if (empty($consultas)) {
                return json_encode([
                    'status' => 'warning',
                    'message' => 'No se encontraron consultas para esta persona.'
                ]);
            } else {
                return json_encode([
                    'status' => 'success',
                    'data' => $consultas
                ]);
            }
        } catch (PDOException $e) {
            return json_encode([
                'status' => 'error',
                'message' => 'Error al obtener las consultas: ' . $e->getMessage()
            ]);
        }
    }
    
    public static function mdlGetDetalleConsulta($idConsulta) {
        try {
            $stmt = Conexion::conectar()->prepare("SELECT id_consulta, motivoscomunes as motivo, txtmotivo, visionod, visionoi, tensionod, tensionoi, 
                consulta_textarea as diagnostico, receta_textarea, txtnota as observaciones, proximaconsulta, 
                whatsapptxt, email, id_user, id_reserva, fecha_registro, ultima_modificacion, id_persona, 
                tipo_formulario, datos_especificos
                FROM public.consultas WHERE id_consulta = :id_consulta");
            $stmt->bindParam(":id_consulta", $idConsulta, PDO::PARAM_INT);
            $stmt->execute();
            
            $consulta = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (empty($consulta)) {
                return json_encode([
                    'status' => 'warning',
                    'message' => 'No se encontró la consulta solicitada.'
                ]);
            } else {
                // Verificar si existe una entrada en la tabla de anteojos para esta consulta
                if ($consulta['tipo_formulario'] == 'anteojos') {
                    try {
                        $stmtAnteojos = Conexion::conectar()->prepare("SELECT COUNT(*) as tiene_anteojos FROM consulta_anteojos WHERE id_consulta = :id_consulta");
                        $stmtAnteojos->bindParam(":id_consulta", $idConsulta, PDO::PARAM_INT);
                        $stmtAnteojos->execute();
                        $resultAnteojos = $stmtAnteojos->fetch(PDO::FETCH_ASSOC);
                        
                        // Agregar información sobre la disponibilidad de datos de anteojos
                        $consulta['tiene_datos_anteojos'] = ($resultAnteojos && $resultAnteojos['tiene_anteojos'] > 0);
                    } catch (Exception $e) {
                        // Si hay un error, asumir que no tiene datos de anteojos
                        $consulta['tiene_datos_anteojos'] = false;
                        error_log("Error al verificar datos de anteojos: " . $e->getMessage());
                    }
                }
                
                // Verificar si existe una entrada en la tabla de informe_imagen para esta consulta
                if ($consulta['tipo_formulario'] == 'informe_imagen') {
                    try {
                        $stmtInformeImagen = Conexion::conectar()->prepare("
                            SELECT 
                                equipo_medico,
                                descripcion_od,
                                descripcion_oi,
                                emails_compartir,
                                compartir_activo,
                                archivos_od,
                                archivos_oi
                            FROM consulta_informe_imagen 
                            WHERE id_consulta = :id_consulta
                        ");
                        $stmtInformeImagen->bindParam(":id_consulta", $idConsulta, PDO::PARAM_INT);
                        $stmtInformeImagen->execute();
                        $datosInformeImagen = $stmtInformeImagen->fetch(PDO::FETCH_ASSOC);
                        
                        if ($datosInformeImagen) {
                            // Agregar datos específicos de informe+imagen al resultado
                            $consulta['equipoMedico'] = $datosInformeImagen['equipo_medico'];
                            $consulta['descripcion_od'] = $datosInformeImagen['descripcion_od'];
                            $consulta['descripcion_oi'] = $datosInformeImagen['descripcion_oi'];
                            $consulta['emails_compartir'] = $datosInformeImagen['emails_compartir'];
                            $consulta['compartir_activo'] = $datosInformeImagen['compartir_activo'];
                            $consulta['archivos_od'] = $datosInformeImagen['archivos_od'];
                            $consulta['archivos_oi'] = $datosInformeImagen['archivos_oi'];
                            $consulta['tiene_datos_informe_imagen'] = true;
                        } else {
                            $consulta['tiene_datos_informe_imagen'] = false;
                        }
                    } catch (Exception $e) {
                        // Si hay un error, asumir que no tiene datos de informe+imagen
                        $consulta['tiene_datos_informe_imagen'] = false;
                        error_log("Error al verificar datos de informe+imagen: " . $e->getMessage());
                    }
                }
                
                // Devolver directamente los datos de la consulta para que el frontend pueda procesarlos
                return json_encode($consulta);
            }
        } catch (PDOException $e) {
            error_log("Error en mdlGetDetalleConsulta: " . $e->getMessage() . " - SQL: " . $e->getCode());
            return json_encode([
                'status' => 'error',
                'message' => 'Error al obtener el detalle de la consulta: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Método para obtener todas las consultas con datos básicos del paciente
     * 
     * @return array Arreglo con todas las consultas y datos básicos de pacientes
     */
    public static function mdlGetAllConsultas($tipoFormulario = null) {
        try {
            // Logging para depuración
            $startTime = microtime(true);
            error_log("Iniciando consulta mdlGetAllConsultas con tipo: " . ($tipoFormulario ?? 'todos'));
            
            // Construir la consulta base
            $sql = "
                SELECT 
                    c.id_consulta,
                    c.id_persona,
                    rh.document_number AS documento,
                    rh.first_name AS nombre,
                    rh.last_name AS apellido,
                    c.motivoscomunes,
                    c.consulta_textarea,
                    c.fecha_registro,
                    c.tipo_formulario
                FROM public.consultas c
                JOIN public.rh_person rh ON c.id_persona = rh.person_id
            ";
            
            // Agregar filtro por tipo si se especifica
            if ($tipoFormulario && trim($tipoFormulario) !== '') {
                $sql .= " WHERE c.tipo_formulario = :tipo_formulario";
            }
            
            $sql .= " ORDER BY c.fecha_registro DESC";
            
            $stmt = Conexion::conectar()->prepare($sql);
            
            // Bind del parámetro si se especifica tipo
            if ($tipoFormulario && trim($tipoFormulario) !== '') {
                $stmt->bindParam(":tipo_formulario", $tipoFormulario, PDO::PARAM_STR);
            }
            
            $stmt->execute();
            $consultas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $endTime = microtime(true);
            $executionTime = ($endTime - $startTime) * 1000; // convertir a milisegundos
            error_log("Consulta mdlGetAllConsultas completada en {$executionTime}ms. Registros encontrados: " . count($consultas) . " (filtro: " . ($tipoFormulario ?? 'ninguno') . ")");
            
            // Si no hay datos, loguear para depuración
            if (empty($consultas)) {
                error_log("No se encontraron consultas en la base de datos" . ($tipoFormulario ? " para tipo: " . $tipoFormulario : ""));
            } else {
                // Mostrar un ejemplo del primer registro para verificación
                error_log("Muestra del primer registro: " . print_r($consultas[0], true));
            }
            
            return $consultas;
        } catch (PDOException $e) {
            error_log("Error en mdlGetAllConsultas: " . $e->getMessage() . " - SQL: " . $e->getCode());
            // Cambiado para evitar salida no JSON en la respuesta AJAX
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Método para obtener las consultas de un paciente específico con datos básicos
     * 
     * @param int $idPersona ID de la persona/paciente
     * @return array Arreglo con las consultas y datos básicos del paciente
     */
    public static function mdlGetConsultasByPaciente($idPersona, $tipoFormulario = null) {
        try {
            // Logging para depuración
            $startTime = microtime(true);
            error_log("Iniciando consulta mdlGetConsultasByPaciente para ID: " . $idPersona . " con tipo: " . ($tipoFormulario ?? 'todos'));
            
            // Construir la consulta base
            $sql = "
                SELECT 
                    c.id_consulta,
                    c.id_persona,
                    rh.document_number AS documento,
                    rh.first_name AS nombre,
                    rh.last_name AS apellido,
                    c.motivoscomunes,
                    c.consulta_textarea,
                    c.fecha_registro,
                    c.tipo_formulario
                FROM public.consultas c
                JOIN public.rh_person rh ON c.id_persona = rh.person_id
                WHERE c.id_persona = :id_persona
            ";
            
            // Agregar filtro por tipo si se especifica
            if ($tipoFormulario && trim($tipoFormulario) !== '') {
                $sql .= " AND c.tipo_formulario = :tipo_formulario";
            }
            
            $sql .= " ORDER BY c.fecha_registro DESC";
            
            $stmt = Conexion::conectar()->prepare($sql);
            
            // Bind de parámetros
            $stmt->bindParam(":id_persona", $idPersona, PDO::PARAM_INT);
            if ($tipoFormulario && trim($tipoFormulario) !== '') {
                $stmt->bindParam(":tipo_formulario", $tipoFormulario, PDO::PARAM_STR);
            }
            
            $stmt->execute();
            $consultas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $endTime = microtime(true);
            $executionTime = ($endTime - $startTime) * 1000; // convertir a milisegundos
            error_log("Consulta mdlGetConsultasByPaciente completada en {$executionTime}ms. Registros encontrados: " . count($consultas) . " (filtro: " . ($tipoFormulario ?? 'ninguno') . ")");
            
            // Si no hay datos, loguear para depuración
            if (empty($consultas)) {
                error_log("No se encontraron consultas para el paciente con ID: " . $idPersona . ($tipoFormulario ? " para tipo: " . $tipoFormulario : ""));
            } else {
                // Mostrar un ejemplo del primer registro para verificación
                error_log("Muestra del primer registro: " . print_r($consultas[0], true));
            }
            
            return $consultas;
        } catch (PDOException $e) {
            error_log("Error en mdlGetConsultasByPaciente: " . $e->getMessage() . " - SQL: " . $e->getCode());
            // Devolver error en formato consistente
            return ['error' => $e->getMessage()];
        }
    }    /**
     * Obtiene los datos de una consulta específica por su ID
     * @param int $id_consulta - ID de la consulta
     * @return array - Datos de la consulta
     */
    public function obtenerConsulta($id_consulta) {
        try {
            // Usar conexión estática para compatibilidad con el resto del código
            $stmt = Conexion::conectar()->prepare("
                SELECT 
                    c.*,
                    CONCAT(p.first_name, ' ', p.last_name) AS nombre_paciente,
                    p.document_number,
                    p.birth_date,
                    p.person_id AS id_persona
                FROM 
                    consultas c
                LEFT JOIN 
                    rh_person p ON c.id_persona = p.person_id
                WHERE 
                    c.id_consulta = :id_consulta
            ");
            $stmt->bindParam(':id_consulta', $id_consulta, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en obtenerConsulta: " . $e->getMessage());
            return false;
        }
    }    /**
     * Obtiene los datos de una persona por su ID
     * @param int $id_persona - ID de la persona
     * @return array - Datos de la persona
     */
    public function obtenerDatosPersona($id_persona) {
        try {
            // Usar conexión estática para compatibilidad con el resto del código
            $stmt = Conexion::conectar()->prepare("
                SELECT 
                    person_id,
                    first_name AS nombres, 
                    last_name AS apellidos, 
                    document_number AS documento, 
                    record_number AS nro_ficha,
                    birth_date AS fecha_nacimiento,
                    phone_number AS telefono,
                    email
                FROM 
                    rh_person 
                WHERE 
                    person_id = :id_persona
            ");
            $stmt->bindParam(':id_persona', $id_persona, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en obtenerDatosPersona: " . $e->getMessage());
            return false;
        }
    }
      /**
     * Obtiene los diagnósticos asociados a una consulta
     * Como la tabla diagnósticos no existe, devolvemos un array vacío
     * @param int $id_consulta - ID de la consulta
     * @return array - Lista de diagnósticos (vacío)
     */
    public function obtenerDiagnosticos($id_consulta) {
        // Como la tabla diagnosticos no existe en la base de datos,
        // devolvemos un array vacío
        return [];
    }
}