<?php
/**
 * Sistema Livewire CRUD Genérico Multi-Formulario
 * Maneja todos los tipos de formularios dinámicamente
 * 
 * Autor: Sistema Automatizado
 * Fecha: 2025-08-28
 */

// Headers para CORS y JSON
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Inicializar sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar autenticación
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Usuario no autenticado',
        'code' => 'UNAUTHORIZED'
    ]);
    exit;
}

require_once __DIR__ . '/../../../config/config.php';

/**
 * Sistema CRUD Multi-Formulario Genérico
 */
class MultiFormCRUDSystem {
    
    private $db;
    private $userId;
    private $debug;
    private $formConfig;
    
    public function __construct() {
        $this->db = \Api\Core\Database::getConnection();
        $this->userId = $_SESSION['user_id'];
        $this->debug = true;
        
        // Cargar configuración de formularios
        $this->formConfig = require '../../../config/formularios_config.php';
        
        $this->log("Sistema Multi-Form CRUD iniciado", 'INFO');
    }
    
    /**
     * Manejar la petición principal
     */
    public function handleRequest() {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $action = $input['action'] ?? $_GET['action'] ?? 'list';
            
            // Anti-cache
            header('Cache-Control: no-cache, no-store, must-revalidate');
            header('Pragma: no-cache');
            header('Expires: 0');
            
            $this->log("Acción solicitada: {$action}", 'INFO');
            
            switch($action) {
                case 'list':
                    return $this->listConsultas($input);
                case 'read':
                    return $this->readConsulta($input);
                case 'create':
                    return $this->createConsulta($input);
                case 'update':
                    return $this->updateConsulta($input);
                case 'delete':
                    return $this->deleteConsulta($input);
                case 'get_form_config':
                    return $this->getFormConfig($input);
                default:
                    throw new Exception("Acción no válida: {$action}");
            }
            
        } catch(Exception $e) {
            $this->log("Error en handleRequest: " . $e->getMessage(), 'ERROR');
            return $this->errorResponse($e->getMessage());
        }
    }
    
    /**
     * Obtener configuración de formulario
     */
    public function getFormConfig($input) {
        $tipo = $input['tipo'] ?? 'general';
        
        if (!isset($this->formConfig[$tipo])) {
            throw new Exception("Tipo de formulario no válido: {$tipo}");
        }
        
        return $this->successResponse([
            'config' => $this->formConfig[$tipo],
            'tipos_disponibles' => array_keys($this->formConfig)
        ], "Configuración de formulario obtenida", 'get_form_config');
    }
    
    /**
     * Listar consultas con filtros
     */
    public function listConsultas($input) {
        $page = max(1, $input['page'] ?? 1);
        $limit = min(50, max(5, $input['limit'] ?? 20));
        $search = $input['search'] ?? '';
        $tipo_formulario = $input['tipo_formulario'] ?? null;
        $offset = ($page - 1) * $limit;
        
        $whereConditions = [];
        $params = [];
        
        if (!empty($search)) {
            $whereConditions[] = "(
                p.first_name ILIKE :search OR 
                p.last_name ILIKE :search OR 
                p.document_number ILIKE :search OR
                c.txtmotivo ILIKE :search OR
                c.consulta_textarea ILIKE :search
            )";
            $params['search'] = "%{$search}%";
        }
        
        if ($tipo_formulario) {
            $whereConditions[] = "c.tipo_formulario = :tipo_formulario";
            $params['tipo_formulario'] = $tipo_formulario;
        }
        
        $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
        
        // Query principal con JOIN a persona
        $sql = "
            SELECT 
                c.*,
                p.first_name,
                p.last_name,
                p.document_number,
                p.phone,
                c.fecha_registro as fecha_consulta
            FROM consultas c
            LEFT JOIN rh_person p ON c.id_persona = p.person_id
            {$whereClause}
            ORDER BY c.fecha_registro DESC
            LIMIT :limit OFFSET :offset
        ";
        
        $params['limit'] = $limit;
        $params['offset'] = $offset;
        
        $stmt = $this->db->prepare($sql);
        foreach($params as $key => $value) {
            $stmt->bindValue(":{$key}", $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $stmt->execute();
        $consultas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Contar total
        $countSql = "
            SELECT COUNT(*) as total
            FROM consultas c
            LEFT JOIN rh_person p ON c.id_persona = p.person_id
            {$whereClause}
        ";
        
        $countParams = $params;
        unset($countParams['limit'], $countParams['offset']);
        
        $countStmt = $this->db->prepare($countSql);
        foreach($countParams as $key => $value) {
            $countStmt->bindValue(":{$key}", $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $countStmt->execute();
        $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        return $this->successResponse($consultas, "Listado obtenido exitosamente", 'list', [
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => intval($total),
                'pages' => ceil($total / $limit)
            ],
            'filters' => [
                'search' => $search,
                'tipo_formulario' => $tipo_formulario
            ]
        ]);
    }
    
    /**
     * Leer una consulta con todos sus datos relacionados
     */
    public function readConsulta($input) {
        $id = $input['id'] ?? null;
        if (!$id) {
            throw new Exception("ID requerido para leer consulta");
        }
        
        // Obtener consulta principal
        $consulta = $this->getConsultaBase($id);
        if (!$consulta) {
            throw new Exception("Consulta no encontrada");
        }
        
        $tipo = $consulta['tipo_formulario'] ?? 'general';
        
        // Cargar datos relacionados según el tipo
        if ($tipo !== 'general' && isset($this->formConfig[$tipo]['tablas']['detalle'])) {
            foreach($this->formConfig[$tipo]['tablas']['detalle'] as $tabla => $config) {
                $consulta[$tabla] = $this->getRelatedData($tabla, $config['foreign_key'], $id);
            }
        }
        
        return $this->successResponse($consulta, "Registro cargado exitosamente", 'read');
    }
    
    /**
     * Crear nueva consulta
     */
    public function createConsulta($input) {
        $data = $input['data'] ?? [];
        $related = $input['related'] ?? [];
        
        if (empty($data['person_id'])) {
            throw new Exception("person_id requerido");
        }
        
        $this->db->beginTransaction();
        
        try {
            // Limpiar campos de fecha vacíos
            $data = $this->cleanDateFields($data);
            
            // Datos principales para consultas
            $consultaData = $this->prepareConsultaData($data);
            $consultaData['id_persona'] = $data['person_id'];
            $consultaData['id_user'] = $this->userId;
            $consultaData['fecha_registro'] = 'NOW()';
            
            // Insertar consulta principal
            $consultaId = $this->insertConsulta($consultaData);
            
            // Insertar datos relacionados
            if (!empty($related)) {
                $this->insertRelatedData($consultaId, $related, $data['tipo_formulario'] ?? 'general');
            }
            
            $this->db->commit();
            
            return $this->successResponse([
                'id_consulta' => $consultaId,
                'message' => 'Consulta creada exitosamente'
            ], "Consulta creada exitosamente", 'create');
            
        } catch(Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
    
    /**
     * Actualizar consulta existente
     */
    public function updateConsulta($input) {
        $id = $input['id'] ?? null;
        $data = $input['data'] ?? [];
        $related = $input['related'] ?? [];
        
        if (!$id) {
            throw new Exception("ID requerido para actualizar");
        }
        
        $this->db->beginTransaction();
        
        try {
            // Limpiar campos de fecha vacíos
            $data = $this->cleanDateFields($data);
            
            // Actualizar tabla principal
            $consultaData = $this->prepareConsultaData($data);
            $consultaData['ultima_modificacion'] = 'NOW()';
            
            $this->updateConsultaBase($id, $consultaData);
            
            // Actualizar/insertar datos relacionados
            if (!empty($related)) {
                $tipo = $data['tipo_formulario'] ?? 'general';
                $this->updateRelatedData($id, $related, $tipo);
            }
            
            $this->db->commit();
            
            return $this->successResponse([
                'id_consulta' => $id,
                'updated' => true
            ], "Consulta actualizada exitosamente", 'update');
            
        } catch(Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
    
    /**
     * Eliminar consulta existente
     */
    public function deleteConsulta($input) {
        $id = $input['id'] ?? null;
        
        if (!$id) {
            throw new Exception("ID requerido para eliminar consulta");
        }
        
        $this->db->beginTransaction();
        
        try {
            // Obtener información de la consulta antes de eliminarla
            $consulta = $this->getConsultaBase($id);
            if (!$consulta) {
                throw new Exception("Consulta no encontrada");
            }
            
            $tipo = $consulta['tipo_formulario'] ?? 'general';
            
            // Eliminar datos relacionados primero (para mantener integridad referencial)
            if ($tipo !== 'general' && isset($this->formConfig[$tipo]['tablas']['detalle'])) {
                foreach($this->formConfig[$tipo]['tablas']['detalle'] as $tabla => $config) {
                    $this->deleteRelatedData($tabla, $config['foreign_key'], $id);
                }
            }
            
            // Eliminar consulta principal
            $sql = "DELETE FROM consultas WHERE id_consulta = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $affected = $stmt->execute();
            
            if (!$affected) {
                throw new Exception("No se pudo eliminar la consulta");
            }
            
            $this->db->commit();
            
            return $this->successResponse([
                'id_consulta' => $id,
                'tipo' => $tipo,
                'deleted' => true
            ], "Consulta eliminada exitosamente", 'delete');
            
        } catch(Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
    
    /**
     * Eliminar datos relacionados
     */
    private function deleteRelatedData($tabla, $foreignKey, $consultaId) {
        $sql = "DELETE FROM {$tabla} WHERE {$foreignKey} = :consulta_id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':consulta_id', $consultaId, PDO::PARAM_INT);
        $stmt->execute();
        
        $this->log("Datos eliminados de {$tabla} para consulta {$consultaId}", 'INFO');
    }
    
    /**
     * Obtener consulta base con información de persona
     */
    private function getConsultaBase($id) {
        $sql = "
            SELECT 
                c.*,
                p.first_name,
                p.last_name,
                p.document_number,
                p.phone,
                p.email as person_email
            FROM consultas c
            LEFT JOIN rh_person p ON c.id_persona = p.person_id
            WHERE c.id_consulta = :id
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener datos relacionados
     */
    private function getRelatedData($tabla, $foreignKey, $consultaId) {
        $sql = "SELECT * FROM {$tabla} WHERE {$foreignKey} = :consulta_id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':consulta_id', $consultaId, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }
    
    /**
     * Preparar datos de consulta principal
     */
    private function prepareConsultaData($data) {
        $consultaFields = [
            'motivoscomunes', 'txtmotivo', 'visionod', 'visionoi', 
            'tensionod', 'tensionoi', 'consulta_textarea', 'receta_textarea',
            'txtnota', 'proximaconsulta', 'whatsapptxt', 'email', 'tipo_formulario'
        ];
        
        $consultaData = [];
        foreach($consultaFields as $field) {
            if (isset($data[$field])) {
                $consultaData[$field] = $data[$field];
            }
        }
        
        return $consultaData;
    }
    
    /**
     * Insertar consulta principal
     */
    private function insertConsulta($data) {
        $fields = array_keys($data);
        $placeholders = array_map(function($f) { return ":$f"; }, $fields);
        
        $sql = "INSERT INTO consultas (" . implode(', ', $fields) . ") VALUES (" . implode(', ', $placeholders) . ")";
        
        $stmt = $this->db->prepare($sql);
        foreach($data as $key => $value) {
            if ($value === 'NOW()') {
                $stmt->bindValue(":$key", date('Y-m-d H:i:s'));
            } else {
                $stmt->bindValue(":$key", $value);
            }
        }
        $stmt->execute();
        
        return $this->db->lastInsertId();
    }
    
    /**
     * Actualizar consulta base
     */
    private function updateConsultaBase($id, $data) {
        $setFields = array_map(function($f) { return "$f = :$f"; }, array_keys($data));
        $sql = "UPDATE consultas SET " . implode(', ', $setFields) . " WHERE id_consulta = :id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        
        foreach($data as $key => $value) {
            if ($value === 'NOW()') {
                $stmt->bindValue(":$key", date('Y-m-d H:i:s'));
            } else {
                $stmt->bindValue(":$key", $value);
            }
        }
        
        return $stmt->execute();
    }
    
    /**
     * Insertar datos relacionados
     */
    private function insertRelatedData($consultaId, $related, $tipo) {
        if (!isset($this->formConfig[$tipo]['tablas']['detalle'])) {
            return;
        }
        
        foreach($this->formConfig[$tipo]['tablas']['detalle'] as $tabla => $config) {
            if (isset($related[$tabla])) {
                $data = $related[$tabla];
                $data[$config['foreign_key']] = $consultaId;
                
                $fields = array_keys($data);
                $placeholders = array_map(function($f) { return ":$f"; }, $fields);
                
                $sql = "INSERT INTO {$tabla} (" . implode(', ', $fields) . ") VALUES (" . implode(', ', $placeholders) . ")";
                
                $stmt = $this->db->prepare($sql);
                foreach($data as $key => $value) {
                    $stmt->bindValue(":$key", $value);
                }
                $stmt->execute();
            }
        }
    }
    
    /**
     * Actualizar datos relacionados
     */
    private function updateRelatedData($consultaId, $related, $tipo) {
        if (!isset($this->formConfig[$tipo]['tablas']['detalle'])) {
            return;
        }
        
        foreach($this->formConfig[$tipo]['tablas']['detalle'] as $tabla => $config) {
            if (isset($related[$tabla])) {
                $data = $related[$tabla];
                
                // Verificar si existe registro
                $checkSql = "SELECT COUNT(*) FROM {$tabla} WHERE {$config['foreign_key']} = :consulta_id";
                $checkStmt = $this->db->prepare($checkSql);
                $checkStmt->bindValue(':consulta_id', $consultaId, PDO::PARAM_INT);
                $checkStmt->execute();
                
                $exists = $checkStmt->fetchColumn() > 0;
                
                if ($exists) {
                    // Actualizar
                    $setFields = array_map(function($f) { return "$f = :$f"; }, array_keys($data));
                    $sql = "UPDATE {$tabla} SET " . implode(', ', $setFields) . " WHERE {$config['foreign_key']} = :consulta_id";
                    
                    $stmt = $this->db->prepare($sql);
                    $stmt->bindValue(':consulta_id', $consultaId, PDO::PARAM_INT);
                    
                } else {
                    // Insertar
                    $data[$config['foreign_key']] = $consultaId;
                    $fields = array_keys($data);
                    $placeholders = array_map(function($f) { return ":$f"; }, $fields);
                    
                    $sql = "INSERT INTO {$tabla} (" . implode(', ', $fields) . ") VALUES (" . implode(', ', $placeholders) . ")";
                    $stmt = $this->db->prepare($sql);
                }
                
                foreach($data as $key => $value) {
                    $stmt->bindValue(":$key", $value);
                }
                $stmt->execute();
            }
        }
    }
    
    /**
     * Limpiar campos de fecha vacíos
     */
    private function cleanDateFields($data) {
        $dateFields = ['proximaconsulta', 'proxima_consulta', 'fecha_nacimiento'];
        
        foreach($dateFields as $field) {
            if (isset($data[$field]) && $data[$field] === '') {
                $data[$field] = null;
            }
        }
        
        return $data;
    }
    
    /**
     * Respuesta de éxito
     */
    private function successResponse($data, $message, $action = null, $meta = []) {
        $response = [
            'success' => true,
            'data' => $data,
            'message' => $message,
            'timestamp' => time()
        ];
        
        if ($action) {
            $response['action'] = $action;
        }
        
        if (!empty($meta)) {
            $response['meta'] = $meta;
        }
        
        return $response;
    }
    
    /**
     * Respuesta de error
     */
    private function errorResponse($message, $code = 'SYSTEM_ERROR', $httpCode = 400) {
        http_response_code($httpCode);
        
        return [
            'success' => false,
            'message' => $message,
            'code' => $code,
            'timestamp' => time(),
            'debug' => $this->debug ? debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3) : null
        ];
    }
    
    /**
     * Log de sistema
     */
    private function log($message, $level = 'INFO') {
        if ($this->debug) {
            $timestamp = date('Y-m-d H:i:s');
            error_log("[{$timestamp}] [{$level}] MultiFormCRUD: {$message}", 3, '/tmp/multiform-crud.log');
        }
    }
}

// Procesar petición
try {
    $system = new MultiFormCRUDSystem();
    $response = $system->handleRequest();
    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
} catch(Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'code' => 'SYSTEM_ERROR',
        'timestamp' => time()
    ], JSON_UNESCAPED_UNICODE);
}
?>