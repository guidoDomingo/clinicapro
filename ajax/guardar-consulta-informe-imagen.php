<?php
// Incluir los archivos de depuración si existen
if (file_exists("../logs/debug_guardar.php")) {
    require_once "../logs/debug_guardar.php";
}
if (file_exists("../logs/debug_guardar_detallado.php")) {
    require_once "../logs/debug_guardar_detallado.php";
}

// Iniciar log de depuración
if (function_exists('debug_log')) {
    debug_log("Iniciando proceso de guardar consulta informe+imagen", ["POST" => $_POST], "[INFORME_IMAGEN]");
}
if (function_exists('debug_detallado')) {
    debug_detallado('INICIO', "Iniciando proceso de guardar datos de informe+imagen", ["POST" => $_POST], 'info');
}

require_once "../model/conexion.php";

/**
 * Clase para manejar el guardado de consultas de tipo Informe + Imagen
 */
class TableConsultaInformeImagen {
    
    /**
     * Obtiene los datos específicos de informe+imagen para una consulta
     * @param int $idConsulta - ID de la consulta
     * @return array|null - Datos de informe+imagen o null si no existe
     */
    public function obtenerInformeImagenConsulta($idConsulta) {
        if (!$idConsulta || !is_numeric($idConsulta)) {
            if (function_exists('debug_detallado')) {
                debug_detallado('OBTENER_INFORME_IMAGEN', "ID de consulta inválido", [
                    'id_consulta' => $idConsulta
                ], 'error');
            }
            return null;
        }
        
        try {
            $db = Conexion::conectar();
            $stmt = $db->prepare("
                SELECT 
                    id_consulta_informe_imagen, id_consulta, 
                    equipo_medico, descripcion_od, descripcion_oi,
                    emails_compartir, compartir_activo
                FROM consulta_informe_imagen 
                WHERE id_consulta = :id_consulta
                LIMIT 1
            ");
            $stmt->bindParam(":id_consulta", $idConsulta, PDO::PARAM_INT);
            $stmt->execute();
            
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (function_exists('debug_detallado')) {
                debug_detallado('OBTENER_INFORME_IMAGEN', "Consulta ejecutada", [
                    'id_consulta' => $idConsulta,
                    'encontrado' => $resultado !== false,
                    'datos' => $resultado ?: 'ninguno'
                ], 'info');
            }
            
            return $resultado ?: null;
            
        } catch (Exception $e) {
            if (function_exists('debug_detallado')) {
                debug_detallado('OBTENER_INFORME_IMAGEN', "Error en consulta", [
                    'id_consulta' => $idConsulta,
                    'error' => $e->getMessage()
                ], 'error');
            }
            return null;
        }
    }
    
    /**
     * Guarda o actualiza los datos específicos de informe+imagen
     * @param array $datos - Datos del formulario
     * @param int $idConsulta - ID de la consulta base
     * @return string|int - "actualizado" o ID del registro, o mensaje de error
     */
    public function guardarConsultaInformeImagen($datos, $idConsulta) {
        if (function_exists('debug_detallado')) {
            debug_detallado('GUARDAR_INFORME_IMAGEN', "Iniciando guardado", [
                'id_consulta' => $idConsulta,
                'datos_recibidos' => array_keys($datos)
            ], 'info');
        }
        
        try {
            $db = Conexion::conectar();
            
            // Verificar si ya existe un registro para esta consulta
            $registroExistente = $this->obtenerInformeImagenConsulta($idConsulta);
            
            // Preparar los datos
            $equipoMedico = isset($datos['equipoMedico']) ? trim($datos['equipoMedico']) : '';
            $descripcionOd = isset($datos['descripcion-od-textarea']) ? trim($datos['descripcion-od-textarea']) : '';
            $descripcionOi = isset($datos['descripcion-oi-textarea']) ? trim($datos['descripcion-oi-textarea']) : '';
            $emailsCompartir = isset($datos['txtEmailShare']) ? trim($datos['txtEmailShare']) : '';
            $compartirActivo = !empty($emailsCompartir) ? 1 : 0;
            
            if ($registroExistente) {
                // Actualizar registro existente
                $sql = "UPDATE consulta_informe_imagen SET 
                        equipo_medico = :equipo_medico,
                        descripcion_od = :descripcion_od,
                        descripcion_oi = :descripcion_oi,
                        emails_compartir = :emails_compartir,
                        compartir_activo = :compartir_activo,
                        fecha_actualizacion = CURRENT_TIMESTAMP
                        WHERE id_consulta = :id_consulta";
                
                $stmt = $db->prepare($sql);
                $stmt->bindParam(":equipo_medico", $equipoMedico, PDO::PARAM_STR);
                $stmt->bindParam(":descripcion_od", $descripcionOd, PDO::PARAM_STR);
                $stmt->bindParam(":descripcion_oi", $descripcionOi, PDO::PARAM_STR);
                $stmt->bindParam(":emails_compartir", $emailsCompartir, PDO::PARAM_STR);
                $stmt->bindParam(":compartir_activo", $compartirActivo, PDO::PARAM_INT);
                $stmt->bindParam(":id_consulta", $idConsulta, PDO::PARAM_INT);
                
                if ($stmt->execute()) {
                    if (function_exists('debug_detallado')) {
                        debug_detallado('GUARDAR_INFORME_IMAGEN', "Actualización exitosa", [
                            'id_consulta' => $idConsulta
                        ], 'success');
                    }
                    return "actualizado";
                } else {
                    throw new Exception("Error al actualizar datos de informe+imagen");
                }
                
            } else {
                // Crear nuevo registro
                $sql = "INSERT INTO consulta_informe_imagen 
                        (id_consulta, equipo_medico, descripcion_od, descripcion_oi, 
                         emails_compartir, compartir_activo, fecha_creacion, fecha_actualizacion) 
                        VALUES 
                        (:id_consulta, :equipo_medico, :descripcion_od, :descripcion_oi, 
                         :emails_compartir, :compartir_activo, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)";
                
                $stmt = $db->prepare($sql);
                $stmt->bindParam(":id_consulta", $idConsulta, PDO::PARAM_INT);
                $stmt->bindParam(":equipo_medico", $equipoMedico, PDO::PARAM_STR);
                $stmt->bindParam(":descripcion_od", $descripcionOd, PDO::PARAM_STR);
                $stmt->bindParam(":descripcion_oi", $descripcionOi, PDO::PARAM_STR);
                $stmt->bindParam(":emails_compartir", $emailsCompartir, PDO::PARAM_STR);
                $stmt->bindParam(":compartir_activo", $compartirActivo, PDO::PARAM_INT);
                
                if ($stmt->execute()) {
                    $nuevoId = $db->lastInsertId();
                    if (function_exists('debug_detallado')) {
                        debug_detallado('GUARDAR_INFORME_IMAGEN', "Inserción exitosa", [
                            'id_consulta' => $idConsulta,
                            'nuevo_id' => $nuevoId
                        ], 'success');
                    }
                    return $nuevoId;
                } else {
                    throw new Exception("Error al insertar datos de informe+imagen");
                }
            }
            
        } catch (Exception $e) {
            if (function_exists('debug_detallado')) {
                debug_detallado('GUARDAR_INFORME_IMAGEN', "Error en guardado", [
                    'id_consulta' => $idConsulta,
                    'error' => $e->getMessage()
                ], 'error');
            }
            return "Error al guardar datos específicos de informe+imagen: " . $e->getMessage();
        }
    }
}

/**
 * Función para guardar la consulta base (tabla consultas)
 * Reutiliza la lógica existente pero adaptada para informe+imagen
 */
function guardarConsultaBaseInformeImagen($datos) {
    if (function_exists('debug_detallado')) {
        debug_detallado('CONSULTA_BASE', "Iniciando guardado de consulta base", [
            'datos_keys' => array_keys($datos)
        ], 'info');
    }
    
    try {
        require_once "../controller/consultas.controller.php";
        
        // Preparar datos para la consulta base
        $datosConsulta = [
            "idPersona" => isset($datos["idPersona"]) ? $datos["idPersona"] : "",
            "motivo" => isset($datos["txtmotivo"]) ? $datos["txtmotivo"] : "",
            "descripcion" => isset($datos["consulta-textarea"]) ? $datos["consulta-textarea"] : "",
            "nota" => isset($datos["txtnota"]) ? $datos["txtnota"] : "",
            "proximaConsulta" => isset($datos["proximaconsulta"]) ? $datos["proximaconsulta"] : "",
            "whatsapp" => isset($datos["whatsapptxt"]) ? $datos["whatsapptxt"] : "",
            "email" => isset($datos["email"]) ? $datos["email"] : "",
            "idUser" => isset($datos["id_user"]) ? $datos["id_user"] : "",
            "idReserva" => isset($datos["id_reserva"]) ? $datos["id_reserva"] : "0",
            "medicoId" => isset($datos["medico_id"]) ? $datos["medico_id"] : "",
            "tipoFormulario" => "informe_imagen"
        ];
        
        // Si es actualización, incluir el ID
        if (isset($datos["id_consulta"]) && !empty($datos["id_consulta"])) {
            $datosConsulta["idConsulta"] = $datos["id_consulta"];
        }
        
        if (function_exists('debug_detallado')) {
            debug_detallado('CONSULTA_BASE', "Datos preparados para controlador", [
                'datos_consulta' => $datosConsulta
            ], 'info');
        }
        
        $resultado = ControllerConsulta::ctrSetConsulta($datosConsulta);
        
        if (function_exists('debug_detallado')) {
            debug_detallado('CONSULTA_BASE', "Resultado del controlador", [
                'resultado' => $resultado
            ], 'info');
        }
        
        return $resultado;
        
    } catch (Exception $e) {
        if (function_exists('debug_detallado')) {
            debug_detallado('CONSULTA_BASE', "Error en guardado base", [
                'error' => $e->getMessage()
            ], 'error');
        }
        return "Error al guardar consulta base: " . $e->getMessage();
    }
}

// Verificar que sea una solicitud POST con datos de informe+imagen
if ($_SERVER["REQUEST_METHOD"] == "POST" && 
    isset($_POST["form_type"]) && 
    $_POST["form_type"] == "informe_imagen" && 
    isset($_POST["idPersona"]) && 
    !empty($_POST["idPersona"])) {
    
    if (function_exists('debug_detallado')) {
        debug_detallado('DETECCION_TIPO', "Formulario de informe+imagen detectado", [
            'form_type' => $_POST["form_type"],
            'id_persona' => $_POST["idPersona"]
        ], 'info');
    }
    
    // Paso 1: Guardar la consulta base
    $esActualizacion = isset($_POST["id_consulta"]) && !empty($_POST["id_consulta"]);
    $idConsulta = 0;
    
    if ($esActualizacion) {
        $idConsulta = intval($_POST["id_consulta"]);
        if (function_exists('debug_detallado')) {
            debug_detallado('PROCESO', "Modo actualización detectado", [
                'id_consulta' => $idConsulta
            ], 'info');
        }
        
        $resultadoBase = guardarConsultaBaseInformeImagen($_POST);
        
        if (!is_numeric($resultadoBase) && !strpos($resultadoBase, 'actualizado')) {
            echo "error: " . $resultadoBase;
            exit;
        }
    } else {
        if (function_exists('debug_detallado')) {
            debug_detallado('PROCESO', "Modo inserción detectado", [], 'info');
        }
        
        $resultadoBase = guardarConsultaBaseInformeImagen($_POST);
        
        if (is_numeric($resultadoBase)) {
            $idConsulta = $resultadoBase;
        } else {
            echo "error: " . $resultadoBase;
            exit;
        }
    }
    
    // Paso 2: Guardar los datos específicos de informe+imagen
    $procesadorInformeImagen = new TableConsultaInformeImagen();
    $resultadoInformeImagen = $procesadorInformeImagen->guardarConsultaInformeImagen($_POST, $idConsulta);
    
    if ($resultadoInformeImagen === "actualizado" || is_numeric($resultadoInformeImagen)) {
        if (function_exists('debug_detallado')) {
            debug_detallado('PROCESO', "Guardado de informe+imagen exitoso", [
                'resultado_informe_imagen' => $resultadoInformeImagen,
                'id_consulta' => $idConsulta
            ], 'success');
        }
        
        if ($esActualizacion) {
            echo "actualizado id:" . $idConsulta;
        } else {
            echo "ok id:" . $idConsulta;
        }
    } else {
        if (function_exists('debug_detallado')) {
            debug_detallado('PROCESO', "Error al guardar datos específicos de informe+imagen", [
                'resultado' => $resultadoInformeImagen,
                'id_consulta' => $idConsulta
            ], 'error');
        }
        echo "error_informe_imagen: " . $resultadoInformeImagen . " (ID consulta: " . $idConsulta . ")";
    }
} else {
    if (function_exists('debug_detallado')) {
        debug_detallado('DETECCION_TIPO', "No es un formulario de informe+imagen o faltan datos", [
            'form_type' => $_POST["form_type"] ?? 'no_definido',
            'tiene_id_persona' => isset($_POST["idPersona"]),
            'post_data_keys' => array_keys($_POST)
        ], 'warning');
    }
    echo "error: Este endpoint es solo para formularios de informe+imagen";
}
?>
