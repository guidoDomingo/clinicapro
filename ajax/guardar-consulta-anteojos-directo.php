<?php
// Incluir los archivos de depuración si existen
if (file_exists("../logs/debug_guardar.php")) {
    require_once "../logs/debug_guardar.php";
}
if (file_exists("../logs/debug_guardar_detallado.php")) {
    require_once "../logs/debug_guardar_detallado.php";
}

// Iniciar log de depuración con información detallada
if (function_exists('debug_log')) {
    debug_log("Iniciando proceso de guardar consulta anteojos (directo)", ["POST" => $_POST], "[ANTEOJOS_DIRECTO]");
}
if (function_exists('debug_detallado')) {
    debug_detallado('INICIO', "Iniciando proceso de guardar datos de anteojos (modo directo)", ["POST" => $_POST], 'info');
}

// Incluir dependencias necesarias
require_once "../model/conexion.php";
require_once "../model/consultas.model.php";

/**
 * Clase para manejar el proceso de guardado directo de una consulta de anteojos
 * Este enfoque guarda primero la consulta base y luego los datos específicos de anteojos
 * sin necesidad de usar cURL o file_get_contents
 */
class ConsultaAnteojosProcesador {
    
    /**
     * Guardar la consulta base usando el modelo de consultas
     * @param array $datos - Datos del formulario
     * @return array - Resultado con status y mensaje/id
     */
    public function guardarConsultaBase($datos) {
        if (function_exists('debug_detallado')) {
            debug_detallado('GUARDAR_BASE', "Guardando consulta base", [], 'info');
        }
        
        try {
            // Asegurarse de que el formulario es de tipo anteojos
            if (!isset($datos['form_type'])) {
                $datos['form_type'] = 'anteojos';
            }
            
            // Usar el modelo de consultas para guardar
            if (isset($datos['id_consulta']) && !empty($datos['id_consulta'])) {
                // Si hay ID, es una actualización
                $resultado = ModelConsulta::mdlActualizarConsulta($datos);
                
                if ($resultado == "ok") {
                    if (function_exists('debug_detallado')) {
                        debug_detallado('GUARDAR_BASE', "Consulta base actualizada correctamente", [
                            'id_consulta' => $datos['id_consulta']
                        ], 'success');
                    }
                    return [
                        'status' => 'success',
                        'message' => 'actualizado',
                        'id_consulta' => $datos['id_consulta']
                    ];
                } else {
                    if (function_exists('debug_detallado')) {
                        debug_detallado('GUARDAR_BASE', "Error al actualizar consulta base", [
                            'resultado' => $resultado
                        ], 'error');
                    }
                    return [
                        'status' => 'error',
                        'message' => $resultado
                    ];
                }
            } else {
                // Si no hay ID, es una inserción
                $resultado = ModelConsulta::mdlSetConsulta($datos);
                
                // Si el resultado es numérico, se trata del ID de la consulta insertada
                if (is_numeric($resultado)) {
                    if (function_exists('debug_detallado')) {
                        debug_detallado('GUARDAR_BASE', "Consulta base guardada correctamente", [
                            'id_consulta' => $resultado
                        ], 'success');
                    }
                    return [
                        'status' => 'success',
                        'message' => 'ok',
                        'id_consulta' => $resultado
                    ];
                } else {
                    if (function_exists('debug_detallado')) {
                        debug_detallado('GUARDAR_BASE', "Error al guardar consulta base", [
                            'resultado' => $resultado
                        ], 'error');
                    }
                    return [
                        'status' => 'error',
                        'message' => $resultado
                    ];
                }
            }
        } catch (Exception $e) {
            if (function_exists('debug_detallado')) {
                debug_detallado('GUARDAR_BASE', "Excepción al guardar consulta base", [
                    'mensaje' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ], 'error');
            }
            return [
                'status' => 'error',
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Guardar los datos específicos de anteojos
     * @param array $datos - Datos del formulario
     * @param int $idConsulta - ID de la consulta base
     * @return array - Resultado con status y mensaje
     */
    public function guardarDatosAnteojos($datos, $idConsulta) {
        if (function_exists('debug_detallado')) {
            debug_detallado('GUARDAR_ANTEOJOS_DIRECTO', "Guardando datos específicos de anteojos", [
                'id_consulta' => $idConsulta
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
                    debug_detallado('GUARDAR_ANTEOJOS_DIRECTO', "La consulta principal no existe", [
                        'id_consulta' => $idConsulta
                    ], 'error');
                }
                return [
                    'status' => 'error',
                    'message' => 'No existe una consulta con el ID proporcionado'
                ];
            }
            
            // Verificar si ya existe un registro para esta consulta
            $checkStmt = $db->prepare("SELECT id_consulta_anteojos FROM consulta_anteojos WHERE id_consulta = :id_consulta");
            $checkStmt->bindParam(":id_consulta", $idConsulta, PDO::PARAM_INT);
            $checkStmt->execute();
            $existente = $checkStmt->fetch(PDO::FETCH_ASSOC);
            
            if ($existente) {
                // Actualizar registro existente
                if (function_exists('debug_detallado')) {
                    debug_detallado('GUARDAR_ANTEOJOS_DIRECTO', "Actualizando registro existente", [
                        'id_consulta_anteojos' => $existente['id_consulta_anteojos']
                    ], 'info');
                }
                
                $stmt = $db->prepare("
                    UPDATE consulta_anteojos SET 
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
                    WHERE id_consulta = :id_consulta
                ");
                
                // Bind del ID de la consulta
                $stmt->bindParam(":id_consulta", $idConsulta, PDO::PARAM_INT);
                
                // Bind de los demás parámetros
                $this->bindAnteojoParams($stmt, $datos);
                
                if ($stmt->execute()) {
                    if (function_exists('debug_detallado')) {
                        debug_detallado('GUARDAR_ANTEOJOS_DIRECTO', "Actualización exitosa", [
                            'id_consulta_anteojos' => $existente['id_consulta_anteojos'],
                            'id_consulta' => $idConsulta
                        ], 'success');
                    }
                    return [
                        'status' => 'success',
                        'message' => 'actualizado',
                        'id_consulta_anteojos' => $existente['id_consulta_anteojos']
                    ];
                } else {
                    $errorInfo = $stmt->errorInfo();
                    if (function_exists('debug_detallado')) {
                        debug_detallado('GUARDAR_ANTEOJOS_DIRECTO', "Error en actualización", [
                            'error' => $errorInfo
                        ], 'error');
                    }
                    return [
                        'status' => 'error',
                        'message' => 'Error al actualizar datos de anteojos: ' . $errorInfo[2]
                    ];
                }
            } else {
                // Crear nuevo registro
                if (function_exists('debug_detallado')) {
                    debug_detallado('GUARDAR_ANTEOJOS_DIRECTO', "Creando nuevo registro de anteojos", [], 'info');
                }
                
                $stmt = $db->prepare("
                    INSERT INTO consulta_anteojos (
                        id_consulta, esfera_od, cilindro_od, eje_od, dnp_od, add_od, nota_od,
                        esfera_oi, cilindro_oi, eje_oi, dnp_oi, add_oi, nota_oi,
                        dist_interpupilar, altura_od, altura_oi
                    ) VALUES (
                        :id_consulta, :esfera_od, :cilindro_od, :eje_od, :dnp_od, :add_od, :nota_od,
                        :esfera_oi, :cilindro_oi, :eje_oi, :dnp_oi, :add_oi, :nota_oi,
                        :dist_interpupilar, :altura_od, :altura_oi
                    ) RETURNING id_consulta_anteojos
                ");
                
                // Bind del ID de la consulta
                $stmt->bindParam(":id_consulta", $idConsulta, PDO::PARAM_INT);
                
                // Bind de los demás parámetros
                $this->bindAnteojoParams($stmt, $datos);
                
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
                            debug_detallado('GUARDAR_ANTEOJOS_DIRECTO', "Inserción exitosa", [
                                'id_consulta_anteojos' => $id_consulta_anteojos,
                                'id_consulta' => $idConsulta
                            ], 'success');
                        }
                        return [
                            'status' => 'success',
                            'message' => 'ok',
                            'id_consulta_anteojos' => $id_consulta_anteojos
                        ];
                    } else {
                        if (function_exists('debug_detallado')) {
                            debug_detallado('GUARDAR_ANTEOJOS_DIRECTO', "Inserción realizada pero no se pudo obtener ID", [], 'warning');
                        }
                        return [
                            'status' => 'warning',
                            'message' => 'Registro insertado pero no se pudo obtener el ID'
                        ];
                    }
                } else {
                    $errorInfo = $stmt->errorInfo();
                    if (function_exists('debug_detallado')) {
                        debug_detallado('GUARDAR_ANTEOJOS_DIRECTO', "Error en inserción", [
                            'error' => $errorInfo
                        ], 'error');
                    }
                    return [
                        'status' => 'error',
                        'message' => 'Error al insertar datos de anteojos: ' . $errorInfo[2]
                    ];
                }
            }
        } catch (PDOException $e) {
            if (function_exists('debug_detallado')) {
                debug_detallado('GUARDAR_ANTEOJOS_DIRECTO', "Excepción PDO", [
                    'mensaje' => $e->getMessage(),
                    'codigo' => $e->getCode(),
                    'trace' => $e->getTraceAsString()
                ], 'error');
            }
            return [
                'status' => 'error',
                'message' => 'Error de base de datos: ' . $e->getMessage()
            ];
        } catch (Exception $e) {
            if (function_exists('debug_detallado')) {
                debug_detallado('GUARDAR_ANTEOJOS_DIRECTO', "Excepción general", [
                    'mensaje' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ], 'error');
            }
            return [
                'status' => 'error',
                'message' => 'Error general: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Método auxiliar para hacer el bind de parámetros
     * @param PDOStatement $stmt - Prepared statement
     * @param array $datos - Datos del formulario
     */
    private function bindAnteojoParams($stmt, $datos) {
        // OD - Ojo derecho
        $esfera_od = isset($datos["od_esf"]) ? $datos["od_esf"] : '';
        $cilindro_od = isset($datos["od_cil"]) ? $datos["od_cil"] : '';
        $eje_od = isset($datos["ejeod"]) ? $datos["ejeod"] : '';
        $dnp_od = isset($datos["dnpod"]) ? $datos["dnpod"] : '';
        $add_od = isset($datos["od_adicion"]) ? $datos["od_adicion"] : '';
        $nota_od = isset($datos["notaod"]) ? $datos["notaod"] : '';
        
        // OI - Ojo izquierdo
        $esfera_oi = isset($datos["oi_esf"]) ? $datos["oi_esf"] : '';
        $cilindro_oi = isset($datos["oi_cil"]) ? $datos["oi_cil"] : '';
        $eje_oi = isset($datos["ejeoi"]) ? $datos["ejeoi"] : '';
        $dnp_oi = isset($datos["dnpoi"]) ? $datos["dnpoi"] : '';
        $add_oi = isset($datos["oi_adicion"]) ? $datos["oi_adicion"] : '';
        $nota_oi = isset($datos["notaoi"]) ? $datos["notaoi"] : '';
        
        // Otros datos
        $dist_interpupilar = isset($datos["dist_interpupilar"]) ? $datos["dist_interpupilar"] : '';
        $altura_od = isset($datos["altura_od"]) ? $datos["altura_od"] : '';
        $altura_oi = isset($datos["altura_oi"]) ? $datos["altura_oi"] : '';
        
        // Bind de los parámetros
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
        
        if (function_exists('debug_detallado')) {
            debug_detallado('BIND_PARAMETROS_DIRECTO', "Parámetros vinculados", [
                'od' => [
                    'esfera' => $esfera_od,
                    'cilindro' => $cilindro_od,
                    'eje' => $eje_od,
                    'dnp' => $dnp_od,
                    'adicion' => $add_od,
                    'nota' => $nota_od
                ],
                'oi' => [
                    'esfera' => $esfera_oi,
                    'cilindro' => $cilindro_oi,
                    'eje' => $eje_oi,
                    'dnp' => $dnp_oi,
                    'adicion' => $add_oi,
                    'nota' => $nota_oi
                ],
                'otros' => [
                    'dist_interpupilar' => $dist_interpupilar,
                    'altura_od' => $altura_od,
                    'altura_oi' => $altura_oi
                ]
            ], 'debug');
        }
    }
    
    /**
     * Método principal que coordina el proceso completo
     * @param array $datos - Datos del formulario
     * @return string - Respuesta formatada para el cliente
     */
    public function procesarFormulario($datos) {
        if (!isset($datos["idPersona"]) || !isset($datos["txtmotivo"])) {
            return "error: Faltan datos obligatorios en el formulario";
        }
        
        // Verificar que es formulario de anteojos
        $tipo_formulario = isset($datos["form_type"]) ? $datos["form_type"] : '';
        if ($tipo_formulario !== 'anteojos' && strpos($_SERVER['HTTP_REFERER'] ?? '', 'form_type=anteojos') === false) {
            return "error: Este endpoint es solo para formularios de anteojos";
        }
        
        // Asegurar que el tipo de formulario es anteojos
        $datos["form_type"] = 'anteojos';
        
        // Paso 1: Guardar la consulta base
        $resultadoBase = $this->guardarConsultaBase($datos);
        
        if ($resultadoBase['status'] !== 'success') {
            return "error: " . $resultadoBase['message'];
        }
        
        $idConsulta = $resultadoBase['id_consulta'];
        $esActualizacion = isset($datos['id_consulta']) && !empty($datos['id_consulta']);
        
        // Paso 2: Si hay datos de anteojos, guardarlos
        if (isset($datos["od_esf"]) || isset($datos["oi_esf"])) {
            $resultadoAnteojos = $this->guardarDatosAnteojos($datos, $idConsulta);
            
            if ($resultadoAnteojos['status'] !== 'success') {
                return "error_anteojos: " . $resultadoAnteojos['message'] . " (ID consulta: " . $idConsulta . ")";
            }
            
            // Si todo fue exitoso, formatear respuesta
            if ($esActualizacion) {
                return "actualizado id:" . $idConsulta;
            } else {
                return "ok id:" . $idConsulta;
            }
        } else {
            // Si no hay datos específicos de anteojos, solo devolver el resultado base
            if ($esActualizacion) {
                return "actualizado id:" . $idConsulta;
            } else {
                return "ok id:" . $idConsulta;
            }
        }
    }
}

// Procesar la solicitud
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $procesador = new ConsultaAnteojosProcesador();
    echo $procesador->procesarFormulario($_POST);
} else {
    echo "error: Método no permitido";
}
?>
