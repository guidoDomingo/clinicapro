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
    debug_log("Iniciando proceso de guardar consulta anteojos", ["POST" => $_POST], "[ANTEOJOS]");
}
if (function_exists('debug_detallado')) {
    debug_detallado('INICIO', "Iniciando proceso de guardar datos de anteojos", ["POST" => $_POST], 'info');
}

require_once "../model/conexion.php";

class TableConsultaAnteojos {
    
    /**
     * Obtiene los datos de anteojos para una consulta específica
     * @param int $idConsulta - ID de la consulta
     * @return array|null - Datos de anteojos o null si no existe
     */
    public function obtenerAnteojosConsulta($idConsulta) {
        if (!$idConsulta || !is_numeric($idConsulta)) {
            if (function_exists('debug_detallado')) {
                debug_detallado('OBTENER_ANTEOJOS', "ID de consulta inválido", [
                    'id_consulta' => $idConsulta
                ], 'error');
            }
            return null;
        }
        
        try {
            $db = Conexion::conectar();
            $stmt = $db->prepare("
                SELECT 
                    id_consulta_anteojos, id_consulta, 
                    esfera_od, cilindro_od, eje_od, dnp_od, add_od, nota_od,
                    esfera_oi, cilindro_oi, eje_oi, dnp_oi, add_oi, nota_oi,
                    dist_interpupilar, altura_od, altura_oi
                FROM consulta_anteojos 
                WHERE id_consulta = :id_consulta
                LIMIT 1
            ");
            $stmt->bindParam(":id_consulta", $idConsulta, PDO::PARAM_INT);
            $stmt->execute();
            
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (function_exists('debug_detallado')) {
                debug_detallado('OBTENER_ANTEOJOS', $resultado ? "Datos de anteojos encontrados" : "No se encontraron datos de anteojos", [
                    'id_consulta' => $idConsulta,
                    'encontrado' => (bool)$resultado
                ], $resultado ? 'info' : 'warning');
            }
            
            return $resultado;
        } catch (Exception $e) {
            if (function_exists('debug_detallado')) {
                debug_detallado('OBTENER_ANTEOJOS', "Error al obtener datos de anteojos", [
                    'mensaje' => $e->getMessage(),
                    'id_consulta' => $idConsulta
                ], 'error');
            }
            return null;
        }
    }
    /**
     * Guarda o actualiza los datos de anteojos asociados a una consulta
     * @param array $datos - Datos del formulario de anteojos
     * @param int $idConsulta - ID de la consulta asociada
     * @return string|int - ID del registro en caso de inserción, "actualizado" en caso de actualización, o mensaje de error
     */
    public function guardarConsultaAnteojos($datos, $idConsulta) {
        if (!$idConsulta || !is_numeric($idConsulta) || $idConsulta <= 0) {
            if (function_exists('debug_detallado')) {
                debug_detallado('GUARDAR_ANTEOJOS', "ID de consulta inválido", [
                    'id_consulta' => $idConsulta
                ], 'error');
            }
            return "ID de consulta inválido o no proporcionado";
        }
        
        if (function_exists('debug_detallado')) {
            debug_detallado('GUARDAR_ANTEOJOS', "Guardando datos de anteojos para consulta", [
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
                    debug_detallado('GUARDAR_ANTEOJOS', "La consulta principal no existe", [
                        'id_consulta' => $idConsulta
                    ], 'error');
                }
                return "No existe una consulta con el ID proporcionado";
            }
            
            // Verificar si ya existe un registro para esta consulta
            $checkStmt = $db->prepare("SELECT id_consulta_anteojos FROM consulta_anteojos WHERE id_consulta = :id_consulta");
            $checkStmt->bindParam(":id_consulta", $idConsulta, PDO::PARAM_INT);
            $checkStmt->execute();
            $existente = $checkStmt->fetch(PDO::FETCH_ASSOC);
            
            if ($existente) {
                // Actualizar registro existente
                if (function_exists('debug_detallado')) {
                    debug_detallado('GUARDAR_ANTEOJOS', "Actualizando registro existente", [
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
                        debug_detallado('GUARDAR_ANTEOJOS', "Actualización exitosa", [
                            'id_consulta_anteojos' => $existente['id_consulta_anteojos'],
                            'id_consulta' => $idConsulta,
                            'filas_afectadas' => $stmt->rowCount()
                        ], 'success');
                    }
                    return "actualizado";
                } else {
                    $errorInfo = $stmt->errorInfo();
                    if (function_exists('debug_detallado')) {
                        debug_detallado('GUARDAR_ANTEOJOS', "Error en actualización", [
                            'error' => $errorInfo,
                            'id_consulta' => $idConsulta
                        ], 'error');
                    }
                    return "Error al actualizar datos de anteojos: " . $errorInfo[2];
                }
            } else {
                // Crear nuevo registro
                if (function_exists('debug_detallado')) {
                    debug_detallado('GUARDAR_ANTEOJOS', "Creando nuevo registro de anteojos", [
                        'id_consulta' => $idConsulta
                    ], 'info');
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
                            debug_detallado('GUARDAR_ANTEOJOS', "Inserción exitosa", [
                                'id_consulta_anteojos' => $id_consulta_anteojos,
                                'id_consulta' => $idConsulta
                            ], 'success');
                        }
                        return $id_consulta_anteojos;
                    } else {
                        if (function_exists('debug_detallado')) {
                            debug_detallado('GUARDAR_ANTEOJOS', "Inserción realizada pero no se pudo obtener ID", [
                                'id_consulta' => $idConsulta
                            ], 'warning');
                        }
                        return "Registro insertado pero no se pudo obtener el ID";
                    }
                } else {
                    $errorInfo = $stmt->errorInfo();
                    if (function_exists('debug_detallado')) {
                        debug_detallado('GUARDAR_ANTEOJOS', "Error en inserción", [
                            'error' => $errorInfo,
                            'id_consulta' => $idConsulta,
                            'sql_estado' => $errorInfo[0]
                        ], 'error');
                    }
                    return "Error al insertar datos de anteojos: " . $errorInfo[2];
                }
            }
        } catch (PDOException $e) {
            if (function_exists('debug_detallado')) {
                debug_detallado('GUARDAR_ANTEOJOS', "Excepción PDO", [
                    'mensaje' => $e->getMessage(),
                    'codigo' => $e->getCode(),
                    'trace' => $e->getTraceAsString(),
                    'id_consulta' => $idConsulta
                ], 'error');
            }
            return "Error de base de datos: " . $e->getMessage();
        } catch (Exception $e) {
            if (function_exists('debug_detallado')) {
                debug_detallado('GUARDAR_ANTEOJOS', "Excepción general", [
                    'mensaje' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'id_consulta' => $idConsulta
                ], 'error');
            }
            return "Error general: " . $e->getMessage();
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
            debug_detallado('BIND_PARAMETROS', "Parámetros vinculados", [
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
}

// Procesar la solicitud si viene del formulario de consulta de anteojos
if (isset($_POST["idPersona"]) && isset($_POST["txtmotivo"])) {
    // Verificar que el tipo de formulario sea "anteojos"
    $tipo_formulario = isset($_POST["form_type"]) ? $_POST["form_type"] : '';
    
    if ($tipo_formulario == 'anteojos' || strpos($_SERVER['HTTP_REFERER'] ?? '', 'form_type=anteojos') !== false) {
        // Establecer una bandera para controlar si debemos usar el método directo
        $usarMetodoDirecto = false;
        $baseResponse = null;
        
        // Primer intento: Método tradicional (cURL, file_get_contents o include)
        if (!$usarMetodoDirecto) {
            // Guardar la consulta base a través de una inclusión directa o file_get_contents
            $consultaData = $_POST;
            
            try {
                // Verificar si la extensión cURL está disponible
                if (function_exists('curl_init')) {
                    // Método 1: Usar cURL si está disponible
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, "http://localhost/clinica/ajax/guardar-consulta.ajax.php");
                    curl_setopt($ch, CURLOPT_POST, 1);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($consultaData));
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    
                    // Ejecutar la solicitud para guardar la consulta base
                    $baseResponse = curl_exec($ch);
                    $curlError = curl_error($ch);
                    curl_close($ch);
                    
                    if (!empty($curlError)) {
                        if (function_exists('debug_detallado')) {
                            debug_detallado('PROCESO', "Error en cURL", [
                                'error' => $curlError
                            ], 'error');
                        }
                        $usarMetodoDirecto = true;
                    }
                } else {
                    // Método 2: Usar file_get_contents con stream context si cURL no está disponible
                    if (function_exists('debug_detallado')) {
                        debug_detallado('PROCESO', "cURL no está disponible, usando método alternativo", [], 'warning');
                    }
                    $usarMetodoDirecto = true;
                    
                    // Intentamos file_get_contents como último recurso antes de ir al método directo
                    if (function_exists('file_get_contents') && function_exists('stream_context_create')) {
                        // Opciones para crear un contexto HTTP POST
                        $opts = [
                            'http' => [
                                'method' => 'POST',
                                'header' => 'Content-Type: application/x-www-form-urlencoded',
                                'content' => http_build_query($consultaData)
                            ]
                        ];
                        
                        // Crear el contexto HTTP
                        $context = stream_context_create($opts);
                        
                        // Realizar la petición POST
                        try {
                            $baseResponse = @file_get_contents("http://localhost/clinica/ajax/guardar-consulta.ajax.php", false, $context);
                            if ($baseResponse !== false) {
                                $usarMetodoDirecto = false; // Si funcionó, no necesitamos el método directo
                            }
                        } catch (Exception $e) {
                            if (function_exists('debug_detallado')) {
                                debug_detallado('PROCESO', "Error en file_get_contents", [
                                    'mensaje' => $e->getMessage()
                                ], 'error');
                            }
                        }
                    }
                }
                
                // Si los métodos anteriores fallaron, intentar con include y buffer
                if ($baseResponse === false || $baseResponse === null) {
                    if (function_exists('debug_detallado')) {
                        debug_detallado('PROCESO', "Métodos HTTP fallaron, intentando con include", [], 'warning');
                    }
                    
                    try {
                        // Guardar una copia de $_POST para restaurarla después
                        $original_post = $_POST;
                        
                        // Capturar la salida del include
                        ob_start();
                        // Vamos a usar directamente el archivo, simulando una solicitud separada
                        include_once "guardar-consulta.ajax.php";
                        $baseResponse = ob_get_clean();
                        
                        // Restaurar $_POST original
                        $_POST = $original_post;
                        
                        if (empty($baseResponse)) {
                            $usarMetodoDirecto = true;
                        } else {
                            $usarMetodoDirecto = false;
                        }
                    } catch (Exception $e) {
                        if (function_exists('debug_detallado')) {
                            debug_detallado('PROCESO', "Error en include", [
                                'mensaje' => $e->getMessage()
                            ], 'error');
                        }
                        $usarMetodoDirecto = true;
                    }
                }
            } catch (Exception $e) {
                if (function_exists('debug_detallado')) {
                    debug_detallado('PROCESO', "Excepción general en métodos tradicionales", [
                        'mensaje' => $e->getMessage()
                    ], 'error');
                }
                $usarMetodoDirecto = true;
            }
        }
        
        // Si todos los métodos tradicionales fallaron, usar el método directo
        if ($usarMetodoDirecto) {
            if (function_exists('debug_detallado')) {
                debug_detallado('PROCESO', "Usando método directo para guardar consulta", [], 'warning');
            }
            
            // Intentar varios métodos alternativos en orden de complejidad
            
            // Método 1: Usar la clase de proceso directo si está disponible
            if (file_exists("guardar-consulta-anteojos-directo.php")) {
                try {
                    // Incluir el modelo de consultas directamente si es necesario
                    if (!class_exists('ModelConsulta')) {
                        if (file_exists("../model/consultas.model.php")) {
                            require_once "../model/consultas.model.php";
                        }
                    }
                    
                    // Guardar una copia de $_POST para restaurarla después
                    $original_post = $_POST;
                    
                    // Capturar la salida del include
                    ob_start();
                    include_once "guardar-consulta-anteojos-directo.php";
                    $baseResponse = ob_get_clean();
                    
                    // Restaurar $_POST original
                    $_POST = $original_post;
                    
                    // Si la respuesta indica éxito, terminar la ejecución
                    if (strpos($baseResponse, 'ok id:') !== false || strpos($baseResponse, 'actualizado id:') !== false) {
                        echo $baseResponse;
                        exit;
                    } else {
                        throw new Exception("Respuesta no válida del método directo: " . $baseResponse);
                    }
                } catch (Exception $e) {
                    if (function_exists('debug_detallado')) {
                        debug_detallado('PROCESO', "Error en método directo", [
                            'error' => $e->getMessage()
                        ], 'error');
                    }
                    // Continuar con el siguiente método
                }
            }
            
            // Método 2: Usar la versión simple (sin dependencias de modelos)
            if (file_exists("guardar-consulta-anteojos-simple.php")) {
                try {
                    // Guardar una copia de $_POST para restaurarla después
                    $original_post = $_POST;
                    
                    // Capturar la salida del include
                    ob_start();
                    include_once "guardar-consulta-anteojos-simple.php";
                    $baseResponse = ob_get_clean();
                    
                    // Restaurar $_POST original
                    $_POST = $original_post;
                    
                    // Si la respuesta indica éxito, terminar la ejecución
                    if (strpos($baseResponse, 'ok id:') !== false || strpos($baseResponse, 'actualizado id:') !== false) {
                        echo $baseResponse;
                        exit;
                    } else {
                        throw new Exception("Respuesta no válida del método simple: " . $baseResponse);
                    }
                } catch (Exception $e) {
                    if (function_exists('debug_detallado')) {
                        debug_detallado('PROCESO', "Error en método simple", [
                            'error' => $e->getMessage()
                        ], 'error');
                    }
                    // Continuar con el último método
                }
            }
            
            // Método 3: Último recurso - Inserción directa usando consultas SQL
            try {
                require_once "../model/conexion.php";
                
                // Mostrar un mensaje de error detallado
                if (function_exists('debug_detallado')) {
                    debug_detallado('PROCESO', "Todos los métodos alternativos fallaron", [], 'error');
                }
                
                echo "error_anteojos: No se pudo procesar la consulta. Contacte al administrador del sistema.";
                exit;
            } catch (Exception $e) {
                if (function_exists('debug_detallado')) {
                    debug_detallado('PROCESO', "Error fatal en todos los métodos", [
                        'error' => $e->getMessage()
                    ], 'error');
                }
                echo "error_fatal: " . $e->getMessage();
                exit;
            }
        }
        
        if (function_exists('debug_detallado')) {
            debug_detallado('PROCESO', "Respuesta del guardado de la consulta base", [
                'response' => $baseResponse,
                'metodo_directo' => $usarMetodoDirecto
            ], 'info');
        }
        
        // Ahora procesar los datos específicos de anteojos
        if (isset($_POST["od_esf"]) || isset($_POST["oi_esf"])) {
            // Intentar obtener el ID de la consulta que se acaba de crear/actualizar
            $idConsulta = 0;
            
            if (isset($_POST["id_consulta"]) && !empty($_POST["id_consulta"])) {
                $idConsulta = $_POST["id_consulta"];
            } else {
                // Intentar obtener el ID de la última consulta insertada
                if (strpos($baseResponse, 'id:') !== false) {
                    $partes = explode('id:', $baseResponse);
                    if (count($partes) > 1) {
                        $idConsulta = trim($partes[1]);
                    }
                }
                
                // Si aún no tenemos ID, buscar la última consulta insertada para este paciente
                if ($idConsulta == 0) {
                    try {
                        $db = Conexion::conectar();
                        $stmt = $db->prepare("SELECT id_consulta FROM consultas WHERE id_persona = :id_persona ORDER BY fecha_registro DESC LIMIT 1");
                        $stmt->bindParam(":id_persona", $_POST["idPersona"], PDO::PARAM_INT);
                        $stmt->execute();
                        $result = $stmt->fetch(PDO::FETCH_ASSOC);
                        
                        if ($result && isset($result['id_consulta'])) {
                            $idConsulta = $result['id_consulta'];
                            
                            if (function_exists('debug_detallado')) {
                                debug_detallado('PROCESO', "ID de consulta obtenido de la base de datos", [
                                    'id_consulta' => $idConsulta
                                ], 'success');
                            }
                        }
                    } catch (Exception $e) {
                        if (function_exists('debug_detallado')) {
                            debug_detallado('PROCESO', "Error al buscar la última consulta", [
                                'error' => $e->getMessage()
                            ], 'error');
                        }
                    }
                }
            }
            
            if ($idConsulta > 0) {
                if (function_exists('debug_detallado')) {
                    debug_detallado('PROCESO', "ID de consulta obtenido correctamente", [
                        'id_consulta' => $idConsulta
                    ], 'success');
                }
                
                // Ahora guardar los datos específicos de anteojos
                $anteojos = new TableConsultaAnteojos();
                $resultado = $anteojos->guardarConsultaAnteojos($_POST, $idConsulta);
                
                if (is_numeric($resultado)) {
                    if (function_exists('debug_detallado')) {
                        debug_detallado('PROCESO', "Datos de anteojos guardados exitosamente (nuevo registro)", [
                            'id_consulta_anteojos' => $resultado,
                            'id_consulta' => $idConsulta
                        ], 'success');
                    }
                    
                    // Si la respuesta base no incluye el ID de la consulta, asegurarnos de que lo devolvemos
                    if (strpos($baseResponse, 'id:') === false) {
                        echo "ok id:" . $idConsulta;
                    } else {
                        // Devolver la respuesta exitosa con el ID
                        echo $baseResponse;
                    }
                } else if ($resultado === "actualizado") {
                    if (function_exists('debug_detallado')) {
                        debug_detallado('PROCESO', "Datos de anteojos actualizados exitosamente", [
                            'id_consulta' => $idConsulta
                        ], 'success');
                    }
                    
                    // Si la respuesta base no indica éxito, asegurarnos de devolver una respuesta clara
                    if (strpos($baseResponse, 'ok') === false && strpos($baseResponse, 'actualizado') === false) {
                        echo "actualizado id:" . $idConsulta;
                    } else {
                        echo $baseResponse;
                    }
                } else {
                    if (function_exists('debug_detallado')) {
                        debug_detallado('PROCESO', "Error al guardar datos de anteojos", [
                            'resultado' => $resultado,
                            'id_consulta' => $idConsulta
                        ], 'error');
                    }
                    // Enviar respuesta de error más detallada
                    echo "error_anteojos: " . $resultado . " (ID consulta: " . $idConsulta . ")";
                }
            } else {
                if (function_exists('debug_detallado')) {
                    debug_detallado('PROCESO', "No se pudo obtener el ID de consulta", [], 'error');
                }
                echo "error_anteojos: No se pudo obtener el ID de la consulta principal";
            }
        } else {
            if (function_exists('debug_detallado')) {
                debug_detallado('PROCESO', "No se encontraron datos específicos de anteojos", [], 'warning');
            }
            // Solo pasamos la respuesta base
            echo $baseResponse;
        }
    } else {
        // No es un formulario de anteojos, incluir el archivo normal de guardar consulta
        include_once "guardar-consulta.ajax.php";
    }
} else {
    if (function_exists('debug_detallado')) {
        debug_detallado('VALIDACION', "Faltan datos básicos del formulario", [], 'error');
    }
    echo "error: Faltan datos obligatorios en el formulario";
}
?>
