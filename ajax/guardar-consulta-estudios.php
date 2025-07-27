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
    debug_log("Iniciando proceso de guardar consulta estudios", ["POST" => $_POST], "[ESTUDIOS]");
}
if (function_exists('debug_detallado')) {
    debug_detallado('INICIO', "Iniciando proceso de guardar datos de estudios", ["POST" => $_POST], 'info');
}

require_once "../model/conexion.php";

/**
 * Clase para manejar el guardado de consultas de estudios médicos
 */
class TableConsultaEstudios {
    
    /**
     * Obtiene los datos específicos de estudios para una consulta
     * @param int $idConsulta - ID de la consulta
     * @return array|null - Datos de estudios o null si no existe
     */
    public function obtenerEstudiosConsulta($idConsulta) {
        if (!$idConsulta || !is_numeric($idConsulta)) {
            if (function_exists('debug_detallado')) {
                debug_detallado('OBTENER_ESTUDIOS', "ID de consulta inválido", [
                    'id_consulta' => $idConsulta
                ], 'error');
            }
            return null;
        }
        
        try {
            $db = Conexion::conectar();
            $stmt = $db->prepare("
                SELECT 
                    id_consulta_estudios, id_consulta, 
                    equipo_medico, otro_equipo, resultados,
                    emails_compartir, compartir_activo
                FROM consulta_estudios 
                WHERE id_consulta = :id_consulta
                LIMIT 1
            ");
            $stmt->bindParam(":id_consulta", $idConsulta, PDO::PARAM_INT);
            $stmt->execute();
            
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (function_exists('debug_detallado')) {
                debug_detallado('OBTENER_ESTUDIOS', $resultado ? "Datos de estudios encontrados" : "No se encontraron datos de estudios", [
                    'id_consulta' => $idConsulta,
                    'encontrado' => (bool)$resultado
                ], $resultado ? 'info' : 'warning');
            }
            
            return $resultado;
        } catch (Exception $e) {
            if (function_exists('debug_detallado')) {
                debug_detallado('OBTENER_ESTUDIOS', "Error al obtener datos de estudios", [
                    'mensaje' => $e->getMessage(),
                    'id_consulta' => $idConsulta
                ], 'error');
            }
            return null;
        }
    }
    
    /**
     * Guarda o actualiza los datos específicos de estudios
     * @param array $datos - Datos del formulario de estudios
     * @param int $idConsulta - ID de la consulta asociada
     * @return string|int - ID del registro en caso de inserción, "actualizado" en caso de actualización, o mensaje de error
     */
    public function guardarConsultaEstudios($datos, $idConsulta) {
        if (!$idConsulta || !is_numeric($idConsulta) || $idConsulta <= 0) {
            if (function_exists('debug_detallado')) {
                debug_detallado('GUARDAR_ESTUDIOS', "ID de consulta inválido", [
                    'id_consulta' => $idConsulta
                ], 'error');
            }
            return "ID de consulta inválido o no proporcionado";
        }
        
        if (function_exists('debug_detallado')) {
            debug_detallado('GUARDAR_ESTUDIOS', "Guardando datos de estudios para consulta", [
                'id_consulta' => $idConsulta,
                'datos' => array_keys($datos)
            ], 'info');
        }

        try {
            // Verificar si la consulta existe en la tabla principal
            $db = Conexion::conectar();
            $consultaStmt = $db->prepare("SELECT id_consulta FROM consultas WHERE id_consulta = :id_consulta");
            $consultaStmt->bindParam(":id_consulta", $idConsulta, PDO::PARAM_INT);
            $consultaStmt->execute();
            
            if (!$consultaStmt->fetch(PDO::FETCH_ASSOC)) {
                if (function_exists('debug_detallado')) {
                    debug_detallado('GUARDAR_ESTUDIOS', "La consulta principal no existe", [
                        'id_consulta' => $idConsulta
                    ], 'error');
                }
                return "No existe una consulta con el ID proporcionado";
            }
            
            // Verificar si ya existe un registro para esta consulta
            $checkStmt = $db->prepare("SELECT id_consulta_estudios FROM consulta_estudios WHERE id_consulta = :id_consulta");
            $checkStmt->bindParam(":id_consulta", $idConsulta, PDO::PARAM_INT);
            $checkStmt->execute();
            $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);
            
            // Preparar los datos
            $equipo_medico = isset($datos['equipo_medico']) ? $datos['equipo_medico'] : '';
            $otro_equipo = isset($datos['otro_equipo']) ? $datos['otro_equipo'] : '';
            // El campo de descripción del formulario se llama 'consulta-textarea'
            $resultados = isset($datos['consulta-textarea']) ? $datos['consulta-textarea'] : '';
            $emails_compartir = isset($datos['txtEmailShare']) ? $datos['txtEmailShare'] : '';
            $compartir_activo = isset($datos['gridCheck']) ? 1 : 0; // El checkbox se llama 'gridCheck'
            
            if ($existing) {
                // Actualizar registro existente
                if (function_exists('debug_detallado')) {
                    debug_detallado('GUARDAR_ESTUDIOS', "Actualizando registro existente", [
                        'id_consulta_estudios' => $existing['id_consulta_estudios']
                    ], 'info');
                }
                
                $stmt = $db->prepare("
                    UPDATE consulta_estudios SET 
                        equipo_medico = :equipo_medico,
                        otro_equipo = :otro_equipo,
                        resultados = :resultados,
                        emails_compartir = :emails_compartir,
                        compartir_activo = :compartir_activo,
                        fecha_actualizacion = CURRENT_TIMESTAMP
                    WHERE id_consulta = :id_consulta
                ");
                
                $stmt->bindParam(":equipo_medico", $equipo_medico, PDO::PARAM_STR);
                $stmt->bindParam(":otro_equipo", $otro_equipo, PDO::PARAM_STR);
                $stmt->bindParam(":resultados", $resultados, PDO::PARAM_STR);
                $stmt->bindParam(":emails_compartir", $emails_compartir, PDO::PARAM_STR);
                $stmt->bindParam(":compartir_activo", $compartir_activo, PDO::PARAM_INT);
                $stmt->bindParam(":id_consulta", $idConsulta, PDO::PARAM_INT);
                
                if ($stmt->execute()) {
                    if (function_exists('debug_detallado')) {
                        debug_detallado('GUARDAR_ESTUDIOS', "Actualización exitosa", [
                            'id_consulta' => $idConsulta
                        ], 'success');
                    }
                    return "actualizado";
                } else {
                    $errorInfo = $stmt->errorInfo();
                    if (function_exists('debug_detallado')) {
                        debug_detallado('GUARDAR_ESTUDIOS', "Error en actualización", [
                            'error' => $errorInfo,
                            'id_consulta' => $idConsulta
                        ], 'error');
                    }
                    return "Error al actualizar datos de estudios: " . $errorInfo[2];
                }
            } else {
                // Insertar nuevo registro
                if (function_exists('debug_detallado')) {
                    debug_detallado('GUARDAR_ESTUDIOS', "Insertando nuevo registro", [
                        'id_consulta' => $idConsulta
                    ], 'info');
                }
                
                $stmt = $db->prepare("
                    INSERT INTO consulta_estudios 
                    (id_consulta, equipo_medico, otro_equipo, resultados, emails_compartir, compartir_activo)
                    VALUES 
                    (:id_consulta, :equipo_medico, :otro_equipo, :resultados, :emails_compartir, :compartir_activo)
                    RETURNING id_consulta_estudios
                ");
                
                $stmt->bindParam(":id_consulta", $idConsulta, PDO::PARAM_INT);
                $stmt->bindParam(":equipo_medico", $equipo_medico, PDO::PARAM_STR);
                $stmt->bindParam(":otro_equipo", $otro_equipo, PDO::PARAM_STR);
                $stmt->bindParam(":resultados", $resultados, PDO::PARAM_STR);
                $stmt->bindParam(":emails_compartir", $emails_compartir, PDO::PARAM_STR);
                $stmt->bindParam(":compartir_activo", $compartir_activo, PDO::PARAM_INT);
                
                if ($stmt->execute()) {
                    $result = $stmt->fetch(PDO::FETCH_ASSOC);
                    $id_consulta_estudios = null;
                    
                    if ($result && isset($result['id_consulta_estudios'])) {
                        $id_consulta_estudios = $result['id_consulta_estudios'];
                    } else {
                        // Si RETURNING no funciona, usar lastInsertId
                        $id_consulta_estudios = $db->lastInsertId();
                    }
                    
                    if ($id_consulta_estudios) {
                        if (function_exists('debug_detallado')) {
                            debug_detallado('GUARDAR_ESTUDIOS', "Inserción exitosa", [
                                'id_consulta_estudios' => $id_consulta_estudios,
                                'id_consulta' => $idConsulta
                            ], 'success');
                        }
                        return $id_consulta_estudios;
                    } else {
                        if (function_exists('debug_detallado')) {
                            debug_detallado('GUARDAR_ESTUDIOS', "Inserción realizada pero no se pudo obtener ID", [
                                'id_consulta' => $idConsulta
                            ], 'warning');
                        }
                        return "Registro insertado pero no se pudo obtener el ID";
                    }
                } else {
                    $errorInfo = $stmt->errorInfo();
                    if (function_exists('debug_detallado')) {
                        debug_detallado('GUARDAR_ESTUDIOS', "Error en inserción", [
                            'error' => $errorInfo,
                            'id_consulta' => $idConsulta,
                            'sql_estado' => $errorInfo[0]
                        ], 'error');
                    }
                    return "Error al insertar datos de estudios: " . $errorInfo[2];
                }
            }
        } catch (PDOException $e) {
            if (function_exists('debug_detallado')) {
                debug_detallado('GUARDAR_ESTUDIOS', "Excepción PDO", [
                    'mensaje' => $e->getMessage(),
                    'codigo' => $e->getCode(),
                    'id_consulta' => $idConsulta
                ], 'error');
            }
            return "Error de base de datos: " . $e->getMessage();
        } catch (Exception $e) {
            if (function_exists('debug_detallado')) {
                debug_detallado('GUARDAR_ESTUDIOS', "Excepción general", [
                    'mensaje' => $e->getMessage(),
                    'id_consulta' => $idConsulta
                ], 'error');
            }
            return "Error general: " . $e->getMessage();
        }
    }
}

/**
 * Función auxiliar para guardar la consulta base usando el modelo existente
 * @param array $datos - Datos del formulario
 * @return string|int - Resultado del guardado
 */
function guardarConsultaBaseEstudios($datos) {
    // Incluir el controlador y modelo de consultas si no están incluidos
    if (!class_exists('ControllerConsulta')) {
        require_once "../controller/consultas.controller.php";
    }
    if (!class_exists('ModelConsulta')) {
        require_once "../model/consultas.model.php";
    }
    
    if (function_exists('debug_detallado')) {
        debug_detallado('GUARDAR_BASE_ESTUDIOS', "Guardando consulta base para estudios", [], 'info');
    }
    
    try {
        // Asegurar que el tipo de formulario sea 'estudios'
        $datos['form_type'] = 'estudios';
        
        // Usar el controlador existente para guardar la consulta base
        $resultado = ControllerConsulta::ctrSetConsulta($datos);
        
        if (function_exists('debug_detallado')) {
            debug_detallado('GUARDAR_BASE_ESTUDIOS', "Resultado del modelo", [
                'resultado' => $resultado
            ], 'info');
        }
        
        return $resultado;
    } catch (Exception $e) {
        if (function_exists('debug_detallado')) {
            debug_detallado('GUARDAR_BASE_ESTUDIOS', "Error al guardar consulta base", [
                'mensaje' => $e->getMessage()
            ], 'error');
        }
        return "Error al guardar consulta base: " . $e->getMessage();
    }
}

// Procesar la solicitud si viene del formulario de consulta de estudios
if (isset($_POST["idPersona"]) && isset($_POST["form_type"]) && $_POST["form_type"] === "estudios") {
    
    // Log detallado de todos los datos recibidos
    if (function_exists('debug_detallado')) {
        debug_detallado('DETECCION_TIPO', "Formulario de estudios detectado correctamente", [
            'form_type' => $_POST["form_type"],
            'tiene_equipo_medico' => isset($_POST["equipo_medico"]),
            'equipo_medico_valor' => $_POST["equipo_medico"] ?? 'no_definido',
            'consulta_textarea' => isset($_POST["consulta-textarea"]) ? 'definido' : 'no_definido',
            'todos_los_campos' => array_keys($_POST)
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
        
        $resultadoBase = guardarConsultaBaseEstudios($_POST);
        
        if (!is_numeric($resultadoBase) && !strpos($resultadoBase, 'actualizado')) {
            echo "error: " . $resultadoBase;
            exit;
        }
    } else {
        if (function_exists('debug_detallado')) {
            debug_detallado('PROCESO', "Modo inserción detectado", [], 'info');
        }
        
        $resultadoBase = guardarConsultaBaseEstudios($_POST);
        
        if (is_numeric($resultadoBase)) {
            $idConsulta = $resultadoBase;
        } else {
            echo "error: " . $resultadoBase;
            exit;
        }
    }
    
    // Paso 2: Guardar los datos específicos de estudios
    $procesadorEstudios = new TableConsultaEstudios();
    $resultadoEstudios = $procesadorEstudios->guardarConsultaEstudios($_POST, $idConsulta);
    
    if ($resultadoEstudios === "actualizado" || is_numeric($resultadoEstudios)) {
        if (function_exists('debug_detallado')) {
            debug_detallado('PROCESO', "Guardado de estudios exitoso", [
                'resultado_estudios' => $resultadoEstudios,
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
            debug_detallado('PROCESO', "Error al guardar datos específicos de estudios", [
                'resultado' => $resultadoEstudios,
                'id_consulta' => $idConsulta
            ], 'error');
        }
        echo "error_estudios: " . $resultadoEstudios . " (ID consulta: " . $idConsulta . ")";
    }
} else {
    if (function_exists('debug_detallado')) {
        debug_detallado('DETECCION_TIPO', "No es un formulario de estudios o faltan datos", [
            'form_type' => $_POST["form_type"] ?? 'no_definido',
            'tiene_id_persona' => isset($_POST["idPersona"]),
            'post_data_keys' => array_keys($_POST)
        ], 'warning');
    }
    echo "error: Este endpoint es solo para formularios de estudios";
}
?>
