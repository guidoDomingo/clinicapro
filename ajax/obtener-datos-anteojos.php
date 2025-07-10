<?php
/**
 * Archivo para obtener datos de anteojos para una consulta existente
 * Versión simplificada que evita posibles errores de sintaxis o formato JSON
 * Actualizada para manejar datos del paciente cuando se cambia entre formularios
 */

// Prevenir cualquier salida antes del JSON
ob_start();

// Establecer el tipo de contenido a JSON
header('Content-Type: application/json');

// Incluir archivo de conexión a la base de datos
$pathBase = dirname(dirname(__FILE__));
require_once $pathBase . "/model/conexion.php";
    
    // Verificar si estamos solamente consultando la existencia de datos para un paciente
    if (isset($_GET["verificar_existencia"]) && isset($_GET["paciente_id"]) && !empty($_GET["paciente_id"])) {
        $idPaciente = intval($_GET["paciente_id"]);
        
        if ($idPaciente <= 0) {
            echo json_encode([
                'status' => 'error',
                'message' => 'ID de paciente inválido'
            ]);
            exit;
        }
        
        try {
            $db = Conexion::conectar();
            
            // Verificar si la tabla consulta_anteojos existe
            $tableExistsStmt = $db->prepare("
                SELECT EXISTS (
                    SELECT FROM information_schema.tables 
                    WHERE table_schema = 'public' 
                    AND table_name = 'consulta_anteojos'
                )
            ");
            $tableExistsStmt->execute();
            $tableExists = $tableExistsStmt->fetchColumn();
            
            if (!$tableExists) {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'La tabla de anteojos no existe en la base de datos'
                ]);
                exit;
            }
            
            // Buscar datos de anteojos para el paciente a través de la tabla de consultas
            $stmt = $db->prepare("
                SELECT ca.id_consulta_anteojos, ca.id_consulta 
                FROM consulta_anteojos ca 
                INNER JOIN consultas c ON ca.id_consulta = c.id_consulta 
                WHERE c.id_persona = :id_paciente 
                ORDER BY c.fecha_registro DESC 
                LIMIT 1
            ");
            $stmt->bindParam(':id_paciente', $idPaciente, PDO::PARAM_INT);
            $stmt->execute();
            
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($data) {
                echo json_encode([
                    'status' => 'success',
                    'tiene_datos' => true,
                    'id_consulta' => $data['id_consulta'],
                    'id_consulta_anteojos' => $data['id_consulta_anteojos']
                ]);
            } else {
                echo json_encode([
                    'status' => 'success',
                    'tiene_datos' => false,
                    'message' => 'No se encontraron datos de anteojos previos para este paciente'
                ]);
            }
        } catch (PDOException $e) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Error de base de datos: ' . $e->getMessage()
            ]);
        }
        exit;
    }
    // Procesar la solicitud para obtener datos de anteojos por ID de consulta
    else if (isset($_GET["id_consulta"]) && !empty($_GET["id_consulta"])) {
        $idConsulta = intval($_GET["id_consulta"]);
        // Capturar el ID del paciente si está presente
        $idPaciente = isset($_GET["paciente_id"]) ? intval($_GET["paciente_id"]) : 0;
    }
    // Procesar la solicitud para obtener datos de anteojos más recientes para un paciente
    else if (isset($_GET["paciente_id"]) && !empty($_GET["paciente_id"])) {
        $idPaciente = intval($_GET["paciente_id"]);
        
        if ($idPaciente <= 0) {
            echo json_encode([
                'status' => 'error',
                'message' => 'ID de paciente inválido'
            ]);
            exit;
        }
        
        try {
            $db = Conexion::conectar();
            
            // Buscar la consulta más reciente con datos de anteojos para este paciente
            $stmtConsulta = $db->prepare("
                SELECT ca.id_consulta 
                FROM consulta_anteojos ca 
                INNER JOIN consultas c ON ca.id_consulta = c.id_consulta 
                WHERE c.id_persona = :id_paciente 
                ORDER BY c.fecha_registro DESC 
                LIMIT 1
            ");
            $stmtConsulta->bindParam(':id_paciente', $idPaciente, PDO::PARAM_INT);
            $stmtConsulta->execute();
            
            $consultaData = $stmtConsulta->fetch(PDO::FETCH_ASSOC);
            
            if ($consultaData) {
                $idConsulta = $consultaData['id_consulta'];
            } else {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'No se encontraron datos de anteojos previos para este paciente'
                ]);
                exit;
            }
        } catch (PDOException $e) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Error de base de datos: ' . $e->getMessage()
            ]);
            exit;
        }
    }
    else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Falta el ID de consulta o el ID de paciente'
        ]);
        exit;
    }
    
    if ($idConsulta <= 0) {
            echo json_encode([
                'status' => 'error',
                'message' => 'ID de consulta inválido'
            ]);
            exit;
    }
    
    // Si no tenemos ID de paciente, intentar obtenerlo desde la consulta
    if ($idPaciente <= 0) {
        try {
            $db = Conexion::conectar(); // Obtener conexión a la base de datos
            $consulta = $db->prepare("SELECT id_persona FROM consultas WHERE id_consulta = :id_consulta");
            $consulta->bindParam(':id_consulta', $idConsulta, PDO::PARAM_INT);
            $consulta->execute();
            $resultConsulta = $consulta->fetch(PDO::FETCH_ASSOC);
            
            if ($resultConsulta && isset($resultConsulta['id_persona'])) {
                $idPaciente = intval($resultConsulta['id_persona']);
            }
        } catch (PDOException $e) {
            // Si hay error, continuar sin ID de paciente
            if (function_exists('debug_detallado')) {
                debug_detallado('OBTENER_ANTEOJOS', "Error al obtener ID de paciente desde consulta", [
                    'mensaje' => $e->getMessage(),
                    'id_consulta' => $idConsulta
                ], 'warning');
            }
        }
    }
        
        try {
            $db = Conexion::conectar();
            
            // Verificar si la tabla consulta_anteojos existe
            $tableExistsStmt = $db->prepare("
                SELECT EXISTS (
                    SELECT FROM information_schema.tables 
                    WHERE table_schema = 'public' 
                    AND table_name = 'consulta_anteojos'
                )
            ");
            $tableExistsStmt->execute();
            $tableExists = $tableExistsStmt->fetchColumn();
            
            if (!$tableExists) {
                if (function_exists('debug_detallado')) {
                    debug_detallado('OBTENER_ANTEOJOS', "Tabla consulta_anteojos no existe", [], 'error');
                }
                
                echo json_encode([
                    'status' => 'error',
                    'message' => 'La tabla de anteojos no existe en la base de datos'
                ]);
                exit;
            }
            
            // Obtener la lista de columnas que existen en la tabla
            $columnsStmt = $db->prepare("
                SELECT column_name 
                FROM information_schema.columns 
                WHERE table_schema = 'public' 
                AND table_name = 'consulta_anteojos'
            ");
            $columnsStmt->execute();
            
            $existingColumns = [];
            while ($row = $columnsStmt->fetch(PDO::FETCH_ASSOC)) {
                $existingColumns[] = $row['column_name'];
            }
            
            if (function_exists('debug_detallado')) {
                debug_detallado('OBTENER_ANTEOJOS', "Columnas encontradas en la tabla", [
                    'columnas' => $existingColumns
                ], 'info');
            }
            
            // Construir la consulta SQL dinámicamente con las columnas que existen
            $selectColumns = ['id_consulta_anteojos', 'id_consulta'];
            $allColumns = [
                'esfera_od', 'cilindro_od', 'eje_od', 'dnp_od', 'add_od', 'nota_od',
                'esfera_oi', 'cilindro_oi', 'eje_oi', 'dnp_oi', 'add_oi', 'nota_oi',
                'dist_interpupilar', 'altura_od', 'altura_oi'
            ];
            
            foreach ($allColumns as $col) {
                if (in_array($col, $existingColumns)) {
                    $selectColumns[] = $col;
                }
            }
            
            $sql = "SELECT " . implode(', ', $selectColumns) . " FROM consulta_anteojos WHERE id_consulta = :id_consulta LIMIT 1";
            
            // if (function_exists('debug_detallado')) {
            //     debug_detallado('OBTENER_ANTEOJOS', "SQL generada", [], 'debug');
            // }
            
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':id_consulta', $idConsulta, PDO::PARAM_INT);
            $stmt->execute();
            
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($data) {
                // Preparar respuesta con verificación para cada campo
                $response = [
                    'status' => 'success',
                    'id_consulta_anteojos' => $data['id_consulta_anteojos'] ?? '',
                    'id_consulta' => $data['id_consulta'] ?? $idConsulta
                ];
                
                // Si tenemos un ID de paciente, obtener los datos del paciente
                if ($idPaciente > 0) {
                    $response['paciente_id'] = $idPaciente;
                    
                    // Obtener los datos del paciente de la tabla correcta (rh_person)
                    try {
                        $pacienteStmt = $db->prepare("
                            SELECT 
                                p.person_id AS id_persona, 
                                p.first_name AS nombres, 
                                p.last_name AS apellidos, 
                                p.document_number AS cedula,
                                p.birth_date AS fecha_nacimiento, 
                                p.record_number AS nro_ficha,
                                p.phone_number AS telefono,
                                p.email
                            FROM 
                                rh_person p
                            WHERE 
                                p.person_id = :id_paciente
                            LIMIT 1
                        ");
                        $pacienteStmt->bindParam(':id_paciente', $idPaciente, PDO::PARAM_INT);
                        $pacienteStmt->execute();
                        
                        $pacienteData = $pacienteStmt->fetch(PDO::FETCH_ASSOC);
                        if ($pacienteData) {
                            $response['paciente_info'] = $pacienteData;
                        }
                    } catch (PDOException $ex) {
                        // Si hay un error, simplemente continuar sin los datos del paciente
                        if (function_exists('debug_detallado')) {
                            debug_detallado('OBTENER_ANTEOJOS', "Error al obtener datos del paciente", [
                                'mensaje' => $ex->getMessage(),
                                'paciente_id' => $idPaciente
                            ], 'warning');
                        }
                    }
                }
                
                // Mapeo de nombres de columnas a nombres de campos del formulario
                $fieldMapping = [
                    'esfera_od' => 'od_esf',
                    'cilindro_od' => 'od_cil',
                    'eje_od' => 'ejeod',
                    'dnp_od' => 'dnpod',
                    'add_od' => 'od_adicion',
                    'nota_od' => 'notaod',
                    'esfera_oi' => 'oi_esf',
                    'cilindro_oi' => 'oi_cil',
                    'eje_oi' => 'ejeoi',
                    'dnp_oi' => 'dnpoi',
                    'add_oi' => 'oi_adicion',
                    'nota_oi' => 'notaoi',
                    'dist_interpupilar' => 'dist_interpupilar',
                    'altura_od' => 'altura_od',
                    'altura_oi' => 'altura_oi'
                ];
                
                // Añadir campos con verificación de existencia
                foreach ($fieldMapping as $dbField => $formField) {
                    $response[$formField] = isset($data[$dbField]) ? $data[$dbField] : '';
                }
                
                if (function_exists('debug_detallado')) {
                    debug_detallado('OBTENER_ANTEOJOS', "Datos encontrados", ['response' => $response], 'success');
                }
                
                echo json_encode($response);
            } else {
                if (function_exists('debug_detallado')) {
                    debug_detallado('OBTENER_ANTEOJOS', "No se encontraron datos", ['id_consulta' => $idConsulta], 'warning');
                }
                
                echo json_encode([
                    'status' => 'error',
                    'message' => 'No se encontraron datos de anteojos para esta consulta'
                ]);
            }
        } catch (PDOException $e) {
            if (function_exists('debug_detallado')) {
                debug_detallado('OBTENER_ANTEOJOS', "Error de base de datos", [
                    'mensaje' => $e->getMessage(),
                    'id_consulta' => $idConsulta
                ], 'error');
            }
            
            echo json_encode([
                'status' => 'error',
                'message' => 'Error de base de datos: ' . $e->getMessage()
            ]);
        }
    
    

?>
