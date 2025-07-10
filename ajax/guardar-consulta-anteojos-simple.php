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
    debug_log("Iniciando proceso de guardar consulta anteojos (método simple)", ["POST" => $_POST], "[ANTEOJOS_SIMPLE]");
}

require_once "../model/conexion.php";

/**
 * Guarda los datos de una consulta base directamente en la base de datos
 * @param array $datos - Datos del formulario
 * @return int|string - ID de la consulta insertada o mensaje de error
 */
function guardarConsultaBaseDirectamente($datos) {
    if (function_exists('debug_detallado')) {
        debug_detallado('GUARDAR_BASE_SIMPLE', "Guardando consulta base directamente", [], 'info');
    }
    
    try {
        $db = Conexion::conectar();
        
        // Determinar si es inserción o actualización
        $esActualizacion = isset($datos["id_consulta"]) && !empty($datos["id_consulta"]);
        
        if ($esActualizacion) {
            // ACTUALIZAR CONSULTA EXISTENTE
            $sql = "UPDATE consultas SET 
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
                    ultima_modificacion = CURRENT_TIMESTAMP,
                    tipo_formulario = :tipo_formulario
                WHERE id_consulta = :id_consulta";
            
            $stmt = $db->prepare($sql);
            $stmt->bindParam(":id_consulta", $datos["id_consulta"], PDO::PARAM_INT);
        } else {
            // INSERTAR NUEVA CONSULTA
            $sql = "INSERT INTO consultas (
                    motivoscomunes, txtmotivo, visionod, visionoi, tensionod, tensionoi,
                    consulta_textarea, receta_textarea, txtnota, proximaconsulta,
                    whatsapptxt, email, id_user, id_reserva, id_persona, 
                    tipo_formulario
                ) VALUES (
                    :motivoscomunes, :txtmotivo, :visionod, :visionoi, :tensionod, :tensionoi,
                    :consulta_textarea, :receta_textarea, :txtnota, :proximaconsulta,
                    :whatsapptxt, :email, :id_user, :id_reserva, :id_persona,
                    :tipo_formulario
                ) RETURNING id_consulta";
            
            $stmt = $db->prepare($sql);
        }
        
        // Asignar valores a los parámetros
        $motivoscomunes = isset($datos["motivoscomunes"]) ? $datos["motivoscomunes"] : '';
        $txtmotivo = isset($datos["txtmotivo"]) ? $datos["txtmotivo"] : '';
        $visionod = isset($datos["visionod"]) ? $datos["visionod"] : '';
        $visionoi = isset($datos["visionoi"]) ? $datos["visionoi"] : '';
        $tensionod = isset($datos["tensionod"]) ? $datos["tensionod"] : '';
        $tensionoi = isset($datos["tensionoi"]) ? $datos["tensionoi"] : '';
        $consulta_textarea = isset($datos["consulta-textarea"]) ? $datos["consulta-textarea"] : '';
        $receta_textarea = isset($datos["receta-textarea"]) ? $datos["receta-textarea"] : '';
        $txtnota = isset($datos["txtnota"]) ? $datos["txtnota"] : '';
        $tipo_formulario = isset($datos["form_type"]) ? $datos["form_type"] : 'anteojos';
        
        // Bind de parámetros básicos
        $stmt->bindParam(":motivoscomunes", $motivoscomunes, PDO::PARAM_STR);
        $stmt->bindParam(":txtmotivo", $txtmotivo, PDO::PARAM_STR);
        $stmt->bindParam(":visionod", $visionod, PDO::PARAM_STR);
        $stmt->bindParam(":visionoi", $visionoi, PDO::PARAM_STR);
        $stmt->bindParam(":tensionod", $tensionod, PDO::PARAM_STR);
        $stmt->bindParam(":tensionoi", $tensionoi, PDO::PARAM_STR);
        $stmt->bindParam(":consulta_textarea", $consulta_textarea, PDO::PARAM_STR);
        $stmt->bindParam(":receta_textarea", $receta_textarea, PDO::PARAM_STR);
        $stmt->bindParam(":txtnota", $txtnota, PDO::PARAM_STR);
        $stmt->bindParam(":tipo_formulario", $tipo_formulario, PDO::PARAM_STR);
        
        // Manejo de proximaconsulta (puede ser NULL)
        if (empty($datos["proximaconsulta"])) {
            $stmt->bindValue(":proximaconsulta", null, PDO::PARAM_NULL);
        } else {
            $proximaconsulta = $datos["proximaconsulta"];
            $stmt->bindParam(":proximaconsulta", $proximaconsulta, PDO::PARAM_STR);
        }
        
        $whatsapptxt = isset($datos["whatsapptxt"]) ? $datos["whatsapptxt"] : '';
        $email = isset($datos["email"]) ? $datos["email"] : '';
        $stmt->bindParam(":whatsapptxt", $whatsapptxt, PDO::PARAM_STR);
        $stmt->bindParam(":email", $email, PDO::PARAM_STR);
        
        // Solo para inserción, estos campos no se actualizan
        if (!$esActualizacion) {
            $id_user = isset($datos["id_user"]) ? intval($datos["id_user"]) : 1;
            $id_reserva = isset($datos["id_reserva"]) ? intval($datos["id_reserva"]) : 0;
            $id_persona = isset($datos["idPersona"]) ? intval($datos["idPersona"]) : 0;
            
            $stmt->bindParam(":id_user", $id_user, PDO::PARAM_INT);
            $stmt->bindParam(":id_reserva", $id_reserva, PDO::PARAM_INT);
            $stmt->bindParam(":id_persona", $id_persona, PDO::PARAM_INT);
        }
        
        // Ejecutar la consulta
        $stmt->execute();
        
        if ($esActualizacion) {
            if ($stmt->rowCount() > 0) {
                if (function_exists('debug_detallado')) {
                    debug_detallado('GUARDAR_BASE_SIMPLE', "Consulta actualizada correctamente", [
                        'id_consulta' => $datos["id_consulta"]
                    ], 'success');
                }
                return $datos["id_consulta"];
            } else {
                if (function_exists('debug_detallado')) {
                    debug_detallado('GUARDAR_BASE_SIMPLE', "No se actualizó ningún registro", [
                        'id_consulta' => $datos["id_consulta"]
                    ], 'warning');
                }
                return "No se actualizó ningún registro";
            }
        } else {
            // Para PostgreSQL, podemos usar RETURNING para obtener el ID
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($resultado && isset($resultado['id_consulta'])) {
                $id_consulta = $resultado['id_consulta'];
            } else {
                // Si RETURNING no funciona, intentar con lastInsertId
                $id_consulta = $db->lastInsertId();
            }
            
            if ($id_consulta) {
                if (function_exists('debug_detallado')) {
                    debug_detallado('GUARDAR_BASE_SIMPLE', "Consulta insertada correctamente", [
                        'id_consulta' => $id_consulta
                    ], 'success');
                }
                return $id_consulta;
            } else {
                if (function_exists('debug_detallado')) {
                    debug_detallado('GUARDAR_BASE_SIMPLE', "Error al obtener el ID de la consulta insertada", [], 'error');
                }
                return "Error al obtener el ID de la consulta insertada";
            }
        }
    } catch (PDOException $e) {
        if (function_exists('debug_detallado')) {
            debug_detallado('GUARDAR_BASE_SIMPLE', "Error de base de datos", [
                'mensaje' => $e->getMessage(),
                'codigo' => $e->getCode()
            ], 'error');
        }
        return "Error de base de datos: " . $e->getMessage();
    } catch (Exception $e) {
        if (function_exists('debug_detallado')) {
            debug_detallado('GUARDAR_BASE_SIMPLE', "Error general", [
                'mensaje' => $e->getMessage()
            ], 'error');
        }
        return "Error general: " . $e->getMessage();
    }
}

/**
 * Guarda los datos específicos de anteojos en la tabla consulta_anteojos
 * @param array $datos - Datos del formulario
 * @param int $idConsulta - ID de la consulta base
 * @return int|string - ID del registro o mensaje de error
 */
function guardarDatosAnteojosDirectamente($datos, $idConsulta) {
    if (function_exists('debug_detallado')) {
        debug_detallado('GUARDAR_ANTEOJOS_SIMPLE', "Guardando datos de anteojos", [
            'id_consulta' => $idConsulta
        ], 'info');
    }
    
    try {
        $db = Conexion::conectar();
        
        // Verificar si ya existe un registro para esta consulta
        $checkStmt = $db->prepare("SELECT id_consulta_anteojos FROM consulta_anteojos WHERE id_consulta = :id_consulta");
        $checkStmt->bindParam(":id_consulta", $idConsulta, PDO::PARAM_INT);
        $checkStmt->execute();
        $existente = $checkStmt->fetch(PDO::FETCH_ASSOC);
        
        // Preparar los valores
        $esfera_od = isset($datos["od_esf"]) ? $datos["od_esf"] : '';
        $cilindro_od = isset($datos["od_cil"]) ? $datos["od_cil"] : '';
        $eje_od = isset($datos["ejeod"]) ? $datos["ejeod"] : '';
        $dnp_od = isset($datos["dnpod"]) ? $datos["dnpod"] : '';
        $add_od = isset($datos["od_adicion"]) ? $datos["od_adicion"] : '';
        $nota_od = isset($datos["notaod"]) ? $datos["notaod"] : '';
        
        $esfera_oi = isset($datos["oi_esf"]) ? $datos["oi_esf"] : '';
        $cilindro_oi = isset($datos["oi_cil"]) ? $datos["oi_cil"] : '';
        $eje_oi = isset($datos["ejeoi"]) ? $datos["ejeoi"] : '';
        $dnp_oi = isset($datos["dnpoi"]) ? $datos["dnpoi"] : '';
        $add_oi = isset($datos["oi_adicion"]) ? $datos["oi_adicion"] : '';
        $nota_oi = isset($datos["notaoi"]) ? $datos["notaoi"] : '';
        
        $dist_interpupilar = isset($datos["dist_interpupilar"]) ? $datos["dist_interpupilar"] : '';
        $altura_od = isset($datos["altura_od"]) ? $datos["altura_od"] : '';
        $altura_oi = isset($datos["altura_oi"]) ? $datos["altura_oi"] : '';
        
        if ($existente) {
            // ACTUALIZAR REGISTRO EXISTENTE
            $sql = "UPDATE consulta_anteojos SET 
                    esfera_od = :esfera_od,
                    cilindro_od = :cilindro_od,
                    eje_od = :eje_od,
                    dnp_od = :dnp_od,
                    add_od = :add_od,
                    nota_od = :nota_od,
                    esfera_oi = :esfera_oi,
                    cilindro_oi = :cilindro_oi,
                    eje_oi = :eje_oi,
                    dnp_oi = :dnp_oi,
                    add_oi = :add_oi,
                    nota_oi = :nota_oi,
                    dist_interpupilar = :dist_interpupilar,
                    altura_od = :altura_od,
                    altura_oi = :altura_oi
                WHERE id_consulta = :id_consulta";
            
            $stmt = $db->prepare($sql);
            $stmt->bindParam(":id_consulta", $idConsulta, PDO::PARAM_INT);
            
            // Bind de los demás parámetros
            $stmt->bindParam(":esfera_od", $esfera_od, PDO::PARAM_STR);
            $stmt->bindParam(":cilindro_od", $cilindro_od, PDO::PARAM_STR);
            $stmt->bindParam(":eje_od", $eje_od, PDO::PARAM_STR);
            $stmt->bindParam(":dnp_od", $dnp_od, PDO::PARAM_STR);
            $stmt->bindParam(":add_od", $add_od, PDO::PARAM_STR);
            $stmt->bindParam(":nota_od", $nota_od, PDO::PARAM_STR);
            
            $stmt->bindParam(":esfera_oi", $esfera_oi, PDO::PARAM_STR);
            $stmt->bindParam(":cilindro_oi", $cilindro_oi, PDO::PARAM_STR);
            $stmt->bindParam(":eje_oi", $eje_oi, PDO::PARAM_STR);
            $stmt->bindParam(":dnp_oi", $dnp_oi, PDO::PARAM_STR);
            $stmt->bindParam(":add_oi", $add_oi, PDO::PARAM_STR);
            $stmt->bindParam(":nota_oi", $nota_oi, PDO::PARAM_STR);
            
            $stmt->bindParam(":dist_interpupilar", $dist_interpupilar, PDO::PARAM_STR);
            $stmt->bindParam(":altura_od", $altura_od, PDO::PARAM_STR);
            $stmt->bindParam(":altura_oi", $altura_oi, PDO::PARAM_STR);
            
            // Ejecutar la consulta
            if ($stmt->execute()) {
                if (function_exists('debug_detallado')) {
                    debug_detallado('GUARDAR_ANTEOJOS_SIMPLE', "Registro de anteojos actualizado", [
                        'id_consulta_anteojos' => $existente['id_consulta_anteojos']
                    ], 'success');
                }
                return "actualizado";
            } else {
                $errorInfo = $stmt->errorInfo();
                if (function_exists('debug_detallado')) {
                    debug_detallado('GUARDAR_ANTEOJOS_SIMPLE', "Error al actualizar", [
                        'error' => $errorInfo
                    ], 'error');
                }
                return "Error al actualizar: " . $errorInfo[2];
            }
        } else {
            // INSERTAR NUEVO REGISTRO
            $sql = "INSERT INTO consulta_anteojos (
                    id_consulta, esfera_od, cilindro_od, eje_od, dnp_od, add_od, nota_od,
                    esfera_oi, cilindro_oi, eje_oi, dnp_oi, add_oi, nota_oi,
                    dist_interpupilar, altura_od, altura_oi
                ) VALUES (
                    :id_consulta, :esfera_od, :cilindro_od, :eje_od, :dnp_od, :add_od, :nota_od,
                    :esfera_oi, :cilindro_oi, :eje_oi, :dnp_oi, :add_oi, :nota_oi,
                    :dist_interpupilar, :altura_od, :altura_oi
                ) RETURNING id_consulta_anteojos";
            
            $stmt = $db->prepare($sql);
            $stmt->bindParam(":id_consulta", $idConsulta, PDO::PARAM_INT);
            
            // Bind de los demás parámetros
            $stmt->bindParam(":esfera_od", $esfera_od, PDO::PARAM_STR);
            $stmt->bindParam(":cilindro_od", $cilindro_od, PDO::PARAM_STR);
            $stmt->bindParam(":eje_od", $eje_od, PDO::PARAM_STR);
            $stmt->bindParam(":dnp_od", $dnp_od, PDO::PARAM_STR);
            $stmt->bindParam(":add_od", $add_od, PDO::PARAM_STR);
            $stmt->bindParam(":nota_od", $nota_od, PDO::PARAM_STR);
            
            $stmt->bindParam(":esfera_oi", $esfera_oi, PDO::PARAM_STR);
            $stmt->bindParam(":cilindro_oi", $cilindro_oi, PDO::PARAM_STR);
            $stmt->bindParam(":eje_oi", $eje_oi, PDO::PARAM_STR);
            $stmt->bindParam(":dnp_oi", $dnp_oi, PDO::PARAM_STR);
            $stmt->bindParam(":add_oi", $add_oi, PDO::PARAM_STR);
            $stmt->bindParam(":nota_oi", $nota_oi, PDO::PARAM_STR);
            
            $stmt->bindParam(":dist_interpupilar", $dist_interpupilar, PDO::PARAM_STR);
            $stmt->bindParam(":altura_od", $altura_od, PDO::PARAM_STR);
            $stmt->bindParam(":altura_oi", $altura_oi, PDO::PARAM_STR);
            
            // Ejecutar la consulta
            if ($stmt->execute()) {
                // Intentar obtener el ID con RETURNING
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                $id_consulta_anteojos = null;
                
                if ($result && isset($result['id_consulta_anteojos'])) {
                    $id_consulta_anteojos = $result['id_consulta_anteojos'];
                } else {
                    // Si RETURNING no funciona, usar lastInsertId
                    $id_consulta_anteojos = $db->lastInsertId();
                }
                
                if ($id_consulta_anteojos) {
                    if (function_exists('debug_detallado')) {
                        debug_detallado('GUARDAR_ANTEOJOS_SIMPLE', "Registro de anteojos insertado", [
                            'id_consulta_anteojos' => $id_consulta_anteojos
                        ], 'success');
                    }
                    return $id_consulta_anteojos;
                } else {
                    if (function_exists('debug_detallado')) {
                        debug_detallado('GUARDAR_ANTEOJOS_SIMPLE', "No se pudo obtener ID", [], 'warning');
                    }
                    return "No se pudo obtener el ID del registro insertado";
                }
            } else {
                $errorInfo = $stmt->errorInfo();
                if (function_exists('debug_detallado')) {
                    debug_detallado('GUARDAR_ANTEOJOS_SIMPLE', "Error al insertar", [
                        'error' => $errorInfo
                    ], 'error');
                }
                return "Error al insertar: " . $errorInfo[2];
            }
        }
    } catch (PDOException $e) {
        if (function_exists('debug_detallado')) {
            debug_detallado('GUARDAR_ANTEOJOS_SIMPLE', "Error de base de datos", [
                'mensaje' => $e->getMessage(),
                'codigo' => $e->getCode()
            ], 'error');
        }
        return "Error de base de datos: " . $e->getMessage();
    } catch (Exception $e) {
        if (function_exists('debug_detallado')) {
            debug_detallado('GUARDAR_ANTEOJOS_SIMPLE', "Error general", [
                'mensaje' => $e->getMessage()
            ], 'error');
        }
        return "Error general: " . $e->getMessage();
    }
}

// Procesar la solicitud
if (isset($_POST["idPersona"]) && isset($_POST["txtmotivo"])) {
    // Verificar que el tipo de formulario sea "anteojos"
    $tipo_formulario = isset($_POST["form_type"]) ? $_POST["form_type"] : '';
    
    if ($tipo_formulario == 'anteojos' || strpos($_SERVER['HTTP_REFERER'] ?? '', 'form_type=anteojos') !== false) {
        // Asegurarnos de que form_type está establecido correctamente
        $_POST["form_type"] = 'anteojos';
        
        // Paso 1: Guardar la consulta base
        $idConsulta = 0;
        $esActualizacion = isset($_POST["id_consulta"]) && !empty($_POST["id_consulta"]);
        
        if ($esActualizacion) {
            $idConsulta = intval($_POST["id_consulta"]);
            $resultadoBase = guardarConsultaBaseDirectamente($_POST);
            
            if (!is_numeric($resultadoBase)) {
                echo "error: " . $resultadoBase;
                exit;
            }
        } else {
            $resultadoBase = guardarConsultaBaseDirectamente($_POST);
            
            if (is_numeric($resultadoBase)) {
                $idConsulta = $resultadoBase;
            } else {
                echo "error: " . $resultadoBase;
                exit;
            }
        }
        
        // Paso 2: Guardar los datos específicos de anteojos si existen
        if (isset($_POST["od_esf"]) || isset($_POST["oi_esf"])) {
            $resultadoAnteojos = guardarDatosAnteojosDirectamente($_POST, $idConsulta);
            
            if ($resultadoAnteojos === "actualizado" || is_numeric($resultadoAnteojos)) {
                if ($esActualizacion) {
                    echo "actualizado id:" . $idConsulta;
                } else {
                    echo "ok id:" . $idConsulta;
                }
            } else {
                echo "error_anteojos: " . $resultadoAnteojos . " (ID consulta: " . $idConsulta . ")";
            }
        } else {
            // No hay datos específicos de anteojos, pero la consulta base se guardó
            if ($esActualizacion) {
                echo "actualizado id:" . $idConsulta;
            } else {
                echo "ok id:" . $idConsulta;
            }
        }
    } else {
        echo "error: Este endpoint es solo para formularios de anteojos";
    }
} else {
    echo "error: Faltan datos obligatorios en el formulario";
}
?>
