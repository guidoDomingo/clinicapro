<?php

/**
 * API moderna para consultas médicas con mapeo directo de base de datos
 * 
 * Esta API utiliza el DatabaseMapper para operaciones CRUD robustas
 * y mapeo directo entre formularios y base de datos.
 * 
 * @version 2.0.0
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

// Autoload del DatabaseMapper
require_once __DIR__ . '/../core/DatabaseMapper.php';

try {
    // Inicializar mapper
    $mapper = new DatabaseMapper();
    
    // Obtener método y acción
    $method = $_SERVER['REQUEST_METHOD'];
    $action = $_GET['action'] ?? '';
    
    // Router principal
    switch ($method) {
        case 'GET':
            handleGet($mapper, $action);
            break;
            
        case 'POST':
            handlePost($mapper, $action);
            break;
            
        case 'PUT':
            handlePut($mapper, $action);
            break;
            
        case 'DELETE':
            handleDelete($mapper, $action);
            break;
            
        default:
            sendError('Método HTTP no soportado', 405);
    }
    
} catch (Exception $e) {
    sendError('Error interno del servidor: ' . $e->getMessage(), 500);
}

/**
 * Manejar peticiones GET
 */
function handleGet($mapper, $action) {
    switch ($action) {
        case 'get_consulta':
            $idConsulta = $_GET['id'] ?? null;
            
            if (!$idConsulta || !is_numeric($idConsulta)) {
                sendError('ID de consulta requerido y debe ser numérico', 400);
                return;
            }
            
            $result = $mapper->getConsulta($idConsulta);
            
            if ($result['success']) {
                // Enriquecer con mapeo HTML si el tipo está disponible
                $data = $result['data'];
                if (isset($data['tipo_formulario'])) {
                    $htmlMapping = $mapper->getHtmlFieldMapping($data['tipo_formulario']);
                    $data['html_mapping'] = $htmlMapping;
                    
                    // Crear mapeo de datos para campos HTML
                    $htmlData = [];
                    foreach ($htmlMapping as $htmlField => $dbColumn) {
                        // Mapeo especial para campos que no coinciden exactamente
                        $value = null;
                        
                        // Casos especiales primero
                        if ($dbColumn === 'motivo_consulta') {
                            // Buscar en txtmotivo o consulta_textarea
                            $value = $data['main']['txtmotivo'] ?? $data['main']['consulta_textarea'] ?? null;
                        } elseif ($dbColumn === 'nota_consulta') {
                            $value = $data['main']['txtnota'] ?? null;
                        } elseif ($dbColumn === 'proxima_consulta') {
                            $value = $data['main']['proximaconsulta'] ?? null;
                        } elseif ($dbColumn === 'mensaje_whatsapp') {
                            $value = $data['main']['whatsapptxt'] ?? null;
                        } elseif ($dbColumn === 'email_paciente') {
                            $value = $data['main']['email'] ?? null;
                        } elseif ($dbColumn === 'motivo_comun_id') {
                            $value = $data['main']['motivoscomunes'] ?? null;
                        }
                        // Buscar directamente en datos principales (campos que coinciden exactamente)
                        elseif (isset($data['main'][$dbColumn])) {
                            $value = $data['main'][$dbColumn];
                        }
                        // Buscar en datos relacionados si no se encontró en main
                        elseif (isset($data['related']) && is_array($data['related'])) {
                            foreach ($data['related'] as $table => $tableData) {
                                if (isset($tableData[$dbColumn])) {
                                    $value = $tableData[$dbColumn];
                                    break;
                                }
                            }
                        }
                        
                        if ($value !== null && $value !== '') {
                            $htmlData[$htmlField] = $value;
                        }
                    }
                    $data['html_data'] = $htmlData;
                }
                
                // Agregar archivos asociados
                try {
                    $archivos = $mapper->getArchivosConsulta($idConsulta);
                    $data['archivos'] = $archivos;
                } catch (Exception $e) {
                    error_log("Error obteniendo archivos: " . $e->getMessage());
                    $data['archivos'] = [];
                }
                
                sendSuccess($data, $result['message']);
            } else {
                sendError($result['message'], 404);
            }
            break;
            
        case 'get_form_mapping':
            $tipoFormulario = $_GET['type'] ?? 'general';
            $mapping = $mapper->getFormMapping($tipoFormulario);
            sendSuccess($mapping, "Mapeo de formulario $tipoFormulario obtenido");
            break;
            
        case 'get_all_mappings':
            $mappings = $mapper->getAllFormMappings();
            sendSuccess($mappings, 'Todos los mapeos obtenidos');
            break;
            
        case 'validate_data':
            $data = json_decode($_GET['data'] ?? '{}', true);
            $tipoFormulario = $_GET['type'] ?? 'general';
            
            $validation = $mapper->validateData($data, $tipoFormulario);
            sendSuccess($validation, 'Validación completada');
            break;
            
        default:
            sendError('Acción no reconocida para GET', 400);
    }
}

/**
 * Manejar peticiones POST (crear)
 */
function handlePost($mapper, $action) {
    $input = getJsonInput();
    
    switch ($action) {
        case 'create_consulta':
            $tipoFormulario = $input['tipo_formulario'] ?? 'general';
            
            // Validar datos primero
            $validation = $mapper->validateData($input, $tipoFormulario);
            if (!$validation['valid']) {
                sendError('Datos inválidos: ' . implode(', ', $validation['errors']), 400);
                return;
            }
            
            $result = $mapper->saveConsulta($input, $tipoFormulario);
            
            if ($result['success']) {
                sendSuccess([
                    'id_consulta' => $result['id_consulta'],
                    'operation' => $result['operation']
                ], $result['message'], 201);
            } else {
                sendError($result['message'], 500);
            }
            break;
            
        default:
            sendError('Acción no reconocida para POST', 400);
    }
}

/**
 * Manejar peticiones PUT (actualizar)
 */
function handlePut($mapper, $action) {
    $input = getJsonInput();
    
    switch ($action) {
        case 'update_consulta':
            $idConsulta = $input['id_consulta'] ?? $_GET['id'] ?? null;
            $tipoFormulario = $input['tipo_formulario'] ?? 'general';
            
            if (!$idConsulta || !is_numeric($idConsulta)) {
                sendError('ID de consulta requerido para actualización', 400);
                return;
            }
            
            // Validar datos
            $validation = $mapper->validateData($input, $tipoFormulario);
            if (!$validation['valid']) {
                sendError('Datos inválidos: ' . implode(', ', $validation['errors']), 400);
                return;
            }
            
            $result = $mapper->saveConsulta($input, $tipoFormulario, $idConsulta);
            
            if ($result['success']) {
                sendSuccess([
                    'id_consulta' => $result['id_consulta'],
                    'operation' => $result['operation']
                ], $result['message']);
            } else {
                sendError($result['message'], 500);
            }
            break;
            
        default:
            sendError('Acción no reconocida para PUT', 400);
    }
}

/**
 * Manejar peticiones DELETE (eliminar)
 */
function handleDelete($mapper, $action) {
    switch ($action) {
        case 'delete_consulta':
            $idConsulta = $_GET['id'] ?? null;
            
            if (!$idConsulta || !is_numeric($idConsulta)) {
                sendError('ID de consulta requerido para eliminación', 400);
                return;
            }
            
            $result = $mapper->deleteConsulta($idConsulta);
            
            if ($result['success']) {
                sendSuccess(['id_consulta' => $idConsulta], $result['message']);
            } else {
                sendError($result['message'], 404);
            }
            break;
            
        default:
            sendError('Acción no reconocida para DELETE', 400);
    }
}

/**
 * Obtener input JSON
 */
function getJsonInput() {
    $input = file_get_contents('php://input');
    $decoded = json_decode($input, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        sendError('JSON inválido en el cuerpo de la petición', 400);
        exit;
    }
    
    return $decoded ?: [];
}

/**
 * Enviar respuesta de éxito
 */
function sendSuccess($data, $message = 'Operación exitosa', $httpCode = 200) {
    http_response_code($httpCode);
    echo json_encode([
        'success' => true,
        'message' => $message,
        'data' => $data,
        'timestamp' => date('Y-m-d H:i:s')
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Enviar respuesta de error
 */
function sendError($message, $httpCode = 400, $errorCode = null) {
    http_response_code($httpCode);
    echo json_encode([
        'success' => false,
        'message' => $message,
        'error_code' => $errorCode,
        'timestamp' => date('Y-m-d H:i:s')
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

?>