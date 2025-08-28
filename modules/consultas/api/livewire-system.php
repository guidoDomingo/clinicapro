<?php
/**
 * Sistema Livewire CRUD Genérico Completo
 * Maneja CREATE, READ, UPDATE, DELETE con datos reales
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

require_once '../../../model/conexion.php';

/**
 * Clase principal del sistema Livewire CRUD
 */
class LivewireCRUDSystem {
    
    private $db;
    private $userId;
    private $debug;
    
    // Configuración de tablas y campos
    private $tableConfig = [
        'consultas' => [
            'primaryKey' => 'id_consulta',
            'displayName' => 'Consulta',
            'fields' => [
                'id_consulta' => ['type' => 'int', 'primary' => true, 'auto' => true],
                'id_persona' => ['type' => 'int', 'required' => true, 'foreign' => 'rh_person.person_id'],
                'motivoscomunes' => ['type' => 'varchar', 'label' => 'Motivos Comunes'],
                'txtmotivo' => ['type' => 'varchar', 'label' => 'Motivo de Consulta'],
                'visionod' => ['type' => 'varchar', 'label' => 'Visión OD'],
                'visionoi' => ['type' => 'varchar', 'label' => 'Visión OI'],
                'tensionod' => ['type' => 'varchar', 'label' => 'Tensión OD'],
                'tensionoi' => ['type' => 'varchar', 'label' => 'Tensión OI'],
                'consulta_textarea' => ['type' => 'text', 'label' => 'Consulta'],
                'receta_textarea' => ['type' => 'text', 'label' => 'Receta'],
                'txtnota' => ['type' => 'text', 'label' => 'Notas'],
                'proximaconsulta' => ['type' => 'date', 'label' => 'Próxima Consulta'],
                'whatsapptxt' => ['type' => 'varchar', 'label' => 'Mensaje WhatsApp'],
                'email' => ['type' => 'varchar', 'label' => 'Email'],
                'id_user' => ['type' => 'int', 'label' => 'Usuario'],
                'id_reserva' => ['type' => 'int', 'default' => 0, 'label' => 'ID Reserva'],
                'fecha_registro' => ['type' => 'timestamp', 'auto' => true, 'default' => 'CURRENT_TIMESTAMP'],
                'ultima_modificacion' => ['type' => 'timestamp', 'label' => 'Última Modificación'],
                'tipo_formulario' => ['type' => 'varchar', 'default' => 'general', 'label' => 'Tipo Formulario'],
                'datos_especificos' => ['type' => 'json', 'label' => 'Datos Específicos']
            ],
            'relations' => [
                'persona' => 'rh_person.person_id',
                'anteojos' => 'consulta_anteojos.id_consulta'
            ]
        ],
        
        'consulta_anteojos' => [
            'primaryKey' => 'id_consulta_anteojos',
            'displayName' => 'Anteojos',
            'fields' => [
                'id_consulta_anteojos' => ['type' => 'int', 'primary' => true, 'auto' => true],
                'id_consulta' => ['type' => 'int', 'required' => true, 'foreign' => 'consultas.id_consulta'],
                'esfera_od' => ['type' => 'varchar', 'label' => 'Esfera OD'],
                'cilindro_od' => ['type' => 'varchar', 'label' => 'Cilindro OD'],
                'eje_od' => ['type' => 'varchar', 'label' => 'Eje OD'],
                'dnp_od' => ['type' => 'varchar', 'label' => 'DNP OD'],
                'add_od' => ['type' => 'varchar', 'label' => 'Adición OD'],
                'altura_od' => ['type' => 'varchar', 'label' => 'Altura OD'],
                'nota_od' => ['type' => 'text', 'label' => 'Nota OD'],
                'esfera_oi' => ['type' => 'varchar', 'label' => 'Esfera OI'],
                'cilindro_oi' => ['type' => 'varchar', 'label' => 'Cilindro OI'],
                'eje_oi' => ['type' => 'varchar', 'label' => 'Eje OI'],
                'dnp_oi' => ['type' => 'varchar', 'label' => 'DNP OI'],
                'add_oi' => ['type' => 'varchar', 'label' => 'Adición OI'],
                'altura_oi' => ['type' => 'varchar', 'label' => 'Altura OI'],
                'nota_oi' => ['type' => 'text', 'label' => 'Nota OI'],
                'dist_interpupilar' => ['type' => 'varchar', 'label' => 'Distancia Interpupilar'],
                'notas' => ['type' => 'text', 'label' => 'Notas Generales'],
                'fecha_creacion' => ['type' => 'timestamp', 'auto' => true, 'default' => 'CURRENT_TIMESTAMP']
            ]
        ],
        
        'rh_person' => [
            'primaryKey' => 'person_id',
            'displayName' => 'Persona',
            'fields' => [
                'person_id' => ['type' => 'int', 'primary' => true, 'auto' => true],
                'document_number' => ['type' => 'varchar', 'required' => true, 'unique' => true, 'label' => 'Documento'],
                'first_name' => ['type' => 'varchar', 'required' => true, 'label' => 'Nombre'],
                'last_name' => ['type' => 'varchar', 'required' => true, 'label' => 'Apellido'],
                'phone_number' => ['type' => 'varchar', 'label' => 'Teléfono'],
                'email' => ['type' => 'varchar', 'label' => 'Email'],
                'birth_date' => ['type' => 'date', 'label' => 'Fecha de Nacimiento'],
                'address' => ['type' => 'text', 'label' => 'Dirección'],
                'gender' => ['type' => 'varchar', 'label' => 'Género'],
                'is_active' => ['type' => 'bool', 'default' => true, 'label' => 'Activo']
            ],
            'searchable' => ['first_name', 'last_name', 'document_number'],
            'displayField' => 'CONCAT(first_name, \' \', last_name)',
            'listFields' => ['document_number', 'first_name', 'last_name', 'phone_number', 'email']
        ]
    ];
    
    public function __construct($debug = false) {
        $this->debug = $debug;
        $this->userId = $_SESSION['user_id'];
        
        try {
            $this->db = Conexion::conectar();
            if (!$this->db) {
                throw new Exception('Error de conexión a la base de datos');
            }
            
            if ($this->debug) {
                error_log("LivewireCRUD: Conexión establecida exitosamente");
            }
            
        } catch (Exception $e) {
            $this->logError('Constructor', $e);
            throw new Exception('Error al inicializar el sistema: ' . $e->getMessage());
        }
    }
    
    /**
     * Punto de entrada principal del sistema
     */
    public function handleRequest() {
        try {
            $input = $this->getInput();
            $action = $input['action'] ?? $input['method'] ?? null;
            
            // Debug detallado: Log de input completo para updates
            if ($this->debug && $action === 'update') {
                $logFile = __DIR__ . '/../../../logs/debug_realtime.log';
                error_log("=== LIVEWIRE UPDATE DEBUG " . date('Y-m-d H:i:s') . " ===", 3, $logFile);
                error_log("Full input: " . json_encode($input, JSON_PRETTY_PRINT), 3, $logFile);
                if (isset($input['data'])) {
                    error_log("Data keys: " . implode(', ', array_keys($input['data'])), 3, $logFile);
                    error_log("Data values preview: " . json_encode(array_map(function($v) { 
                        return is_string($v) && strlen($v) > 50 ? substr($v, 0, 50) . '...' : $v; 
                    }, $input['data'])), 3, $logFile);
                }
            }
            
            if (!$action) {
                throw new Exception('Acción no especificada');
            }
            
            $this->logDebug('HandleRequest', [
                'action' => $action,
                'input_keys' => array_keys($input)
            ]);
            
            $result = $this->executeAction($action, $input);
            
            echo json_encode([
                'success' => true,
                'action' => $action,
                'data' => $result['data'] ?? null,
                'message' => $result['message'] ?? 'Operación exitosa',
                'meta' => $result['meta'] ?? null
            ]);
            
        } catch (Exception $e) {
            $this->handleError($e);
        }
    }
    
    /**
     * Ejecutar acción específica
     */
    private function executeAction($action, $input) {
        switch ($action) {
            case 'create':
                return $this->create($input);
                
            case 'read':
            case 'load':
            case 'get':
                return $this->read($input);
                
            case 'update':
            case 'save':
                return $this->update($input);
                
            case 'delete':
            case 'destroy':
                return $this->delete($input);
                
            case 'list':
            case 'index':
                return $this->list($input);
                
            case 'search':
                return $this->search($input);
                
            case 'validate':
                return $this->validate($input);
                
            default:
                throw new Exception("Acción no soportada: $action");
        }
    }
    
    /**
     * CREAR nuevo registro
     */
    public function create($input) {
        $table = $input['table'] ?? 'consultas';
        $data = $input['data'] ?? $input['state'] ?? [];
        
        if (!isset($this->tableConfig[$table])) {
            throw new Exception("Tabla no configurada: $table");
        }
        
        $config = $this->tableConfig[$table];
        
        // Validar datos
        $validationResult = $this->validateData($table, $data, 'create');
        if (!$validationResult['valid']) {
            throw new Exception('Datos inválidos: ' . implode(', ', $validationResult['errors']));
        }
        
        $this->db->beginTransaction();
        
        try {
            // Preparar datos para inserción
            $insertData = $this->prepareDataForInsert($table, $data);
            
            // Construir query INSERT
            $fields = array_keys($insertData);
            $placeholders = array_map(function($field) { return ":$field"; }, $fields);
            
            $sql = "INSERT INTO $table (" . implode(', ', $fields) . ") 
                    VALUES (" . implode(', ', $placeholders) . ")";
            
            $stmt = $this->db->prepare($sql);
            
            // Bind parameters
            foreach ($insertData as $field => $value) {
                $stmt->bindValue(":$field", $value);
            }
            
            $stmt->execute();
            $newId = $this->db->lastInsertId();
            
            $this->logDebug('Create', [
                'table' => $table,
                'new_id' => $newId,
                'data' => $insertData
            ]);
            
            // Si es una consulta y tiene datos relacionados, crearlos también
            if ($table === 'consultas' && isset($input['related'])) {
                $this->handleRelatedData('create', $newId, $input['related']);
            }
            
            $this->db->commit();
            
            // Cargar el registro completo creado
            $createdRecord = $this->loadRecord($table, $newId);
            
            return [
                'data' => $createdRecord,
                'message' => $config['displayName'] . ' creado exitosamente',
                'meta' => ['id' => $newId, 'table' => $table]
            ];
            
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
    
    /**
     * LEER registro(s)
     */
    public function read($input) {
        $table = $input['table'] ?? 'consultas';
        $id = $input['id'] ?? null;
        $with = $input['with'] ?? []; // Relaciones a incluir
        
        if (!isset($this->tableConfig[$table])) {
            throw new Exception("Tabla no configurada: $table");
        }
        
        if (!$id) {
            throw new Exception('ID requerido para operación READ');
        }
        
        $record = $this->loadRecord($table, $id, $with);
        
        if (!$record) {
            throw new Exception('Registro no encontrado');
        }
        
        return [
            'data' => $record,
            'message' => 'Registro cargado exitosamente',
            'meta' => ['id' => $id, 'table' => $table]
        ];
    }
    
    /**
     * ACTUALIZAR registro
     */
    public function update($input) {
        $table = $input['table'] ?? 'consultas';
        $id = $input['id'] ?? $input['consultaId'] ?? null;
        $data = $input['data'] ?? $input['state'] ?? [];
        
        // Debug: Log de datos recibidos
        if ($this->debug) {
            error_log("UPDATE DEBUG - Table: $table, ID: $id");
            error_log("UPDATE DEBUG - Data received: " . json_encode($data));
            error_log("UPDATE DEBUG - Full input: " . json_encode($input));
        }
        
        if (!isset($this->tableConfig[$table])) {
            throw new Exception("Tabla no configurada: $table");
        }
        
        if (!$id) {
            // Si no hay ID, intentar crear
            return $this->create($input);
        }
        
        // Verificar que el registro existe
        $existing = $this->loadRecord($table, $id);
        if (!$existing) {
            throw new Exception('Registro no encontrado para actualizar');
        }
        
        // Validar datos
        $validationResult = $this->validateData($table, $data, 'update');
        if (!$validationResult['valid']) {
            throw new Exception('Datos inválidos: ' . implode(', ', $validationResult['errors']));
        }
        
        $this->db->beginTransaction();
        
        try {
            // Preparar datos para actualización
            $updateData = $this->prepareDataForUpdate($table, $data);
            
            // Debug: Log de datos preparados
            if ($this->debug) {
                $logFile = __DIR__ . '/../../../logs/debug_realtime.log';
                error_log("UPDATE DEBUG - Prepared data: " . json_encode($updateData), 3, $logFile);
                error_log("UPDATE DEBUG - Prepared data count: " . count($updateData), 3, $logFile);
            }
            
            if (empty($updateData)) {
                throw new Exception('No hay datos para actualizar');
            }
            
            // Construir query UPDATE
            $setParts = array_map(function($field) { return "$field = :$field"; }, array_keys($updateData));
            $config = $this->tableConfig[$table];
            $primaryKey = $config['primaryKey'];
            
            $sql = "UPDATE $table SET " . implode(', ', $setParts) . " WHERE $primaryKey = :id";
            
            $stmt = $this->db->prepare($sql);
            
            // Bind parameters
            foreach ($updateData as $field => $value) {
                $stmt->bindValue(":$field", $value);
            }
            $stmt->bindValue(':id', $id);
            
            $stmt->execute();
            
            // Debug: Log de ejecución SQL
            if ($this->debug) {
                $logFile = __DIR__ . '/../../../logs/debug_realtime.log';
                error_log("UPDATE DEBUG - SQL executed: " . $sql, 3, $logFile);
                error_log("UPDATE DEBUG - Affected rows: " . $stmt->rowCount(), 3, $logFile);
                if ($stmt->rowCount() === 0) {
                    error_log("UPDATE DEBUG - WARNING: No rows affected!", 3, $logFile);
                }
            }
            
            $this->logDebug('Update', [
                'table' => $table,
                'id' => $id,
                'data' => $updateData,
                'affected_rows' => $stmt->rowCount()
            ]);
            
            // Manejar datos relacionados
            if (isset($input['related'])) {
                $this->handleRelatedData('update', $id, $input['related']);
            }
            
            $this->db->commit();
            
            // Cargar el registro actualizado
            $updatedRecord = $this->loadRecord($table, $id);
            
            return [
                'data' => $updatedRecord,
                'message' => $config['displayName'] . ' actualizado exitosamente',
                'meta' => ['id' => $id, 'table' => $table]
            ];
            
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
    
    /**
     * ELIMINAR registro
     */
    public function delete($input) {
        $table = $input['table'] ?? 'consultas';
        $id = $input['id'] ?? null;
        
        if (!isset($this->tableConfig[$table])) {
            throw new Exception("Tabla no configurada: $table");
        }
        
        if (!$id) {
            throw new Exception('ID requerido para operación DELETE');
        }
        
        // Verificar que el registro existe
        $existing = $this->loadRecord($table, $id);
        if (!$existing) {
            throw new Exception('Registro no encontrado para eliminar');
        }
        
        $this->db->beginTransaction();
        
        try {
            $config = $this->tableConfig[$table];
            $primaryKey = $config['primaryKey'];
            
            // Eliminar registros relacionados primero (CASCADE manual)
            if ($table === 'consultas') {
                $this->db->prepare("DELETE FROM consulta_anteojos WHERE id_consulta = ?")->execute([$id]);
                // Agregar otras tablas relacionadas aquí
            }
            
            // Eliminar registro principal
            $sql = "DELETE FROM $table WHERE $primaryKey = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            
            $this->logDebug('Delete', [
                'table' => $table,
                'id' => $id,
                'deleted_record' => $existing
            ]);
            
            $this->db->commit();
            
            return [
                'data' => $existing,
                'message' => $config['displayName'] . ' eliminado exitosamente',
                'meta' => ['id' => $id, 'table' => $table]
            ];
            
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
    
    /**
     * LISTAR registros con paginación
     */
    public function list($input) {
        $table = $input['table'] ?? 'consultas';
        $page = max(1, intval($input['page'] ?? 1));
        $limit = min(100, max(10, intval($input['limit'] ?? 20)));
        $offset = ($page - 1) * $limit;
        $search = $input['search'] ?? '';
        $filters = $input['filters'] ?? [];
        $orderBy = $input['orderBy'] ?? null;
        $orderDir = strtoupper($input['orderDir'] ?? 'DESC');
        
        if (!in_array($orderDir, ['ASC', 'DESC'])) {
            $orderDir = 'DESC';
        }
        
        if (!isset($this->tableConfig[$table])) {
            throw new Exception("Tabla no configurada: $table");
        }
        
        $config = $this->tableConfig[$table];
        
        // Construir query base
        $baseQuery = "FROM $table";
        $whereConditions = [];
        $params = [];
        
        // Joins para mostrar datos relacionados
        if ($table === 'consultas') {
            $baseQuery = "FROM consultas c LEFT JOIN rh_person p ON c.id_persona = p.person_id";
        }
        
        // Filtros de búsqueda
        if (!empty($search)) {
            $searchConditions = $this->buildSearchConditions($table, $search);
            if (!empty($searchConditions['where'])) {
                $whereConditions[] = "(" . implode(' OR ', $searchConditions['where']) . ")";
                $params = array_merge($params, $searchConditions['params']);
            }
        }
        
        // Filtros adicionales
        foreach ($filters as $field => $value) {
            if (!empty($value)) {
                $whereConditions[] = "$field = :filter_$field";
                $params[":filter_$field"] = $value;
            }
        }
        
        $whereClause = empty($whereConditions) ? '' : 'WHERE ' . implode(' AND ', $whereConditions);
        
        // Order by
        $orderClause = '';
        if ($orderBy && $this->isValidField($table, $orderBy)) {
            $orderClause = "ORDER BY $orderBy $orderDir";
        } else {
            $primaryKey = $config['primaryKey'];
            $orderClause = "ORDER BY $primaryKey $orderDir";
        }
        
        // Contar total
        $countQuery = "SELECT COUNT(*) as total $baseQuery $whereClause";
        $stmt = $this->db->prepare($countQuery);
        $stmt->execute($params);
        $total = $stmt->fetch()['total'];
        
        // Obtener registros
        $fields = $this->getSelectFields($table);
        $dataQuery = "SELECT $fields $baseQuery $whereClause $orderClause LIMIT $limit OFFSET $offset";
        
        $stmt = $this->db->prepare($dataQuery);
        $stmt->execute($params);
        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Procesar registros
        $processedRecords = array_map(function($record) use ($table) {
            return $this->processRecord($table, $record);
        }, $records);
        
        return [
            'data' => $processedRecords,
            'meta' => [
                'table' => $table,
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'pages' => ceil($total / $limit),
                'search' => $search,
                'filters' => $filters
            ],
            'message' => "Listado de {$config['displayName']} obtenido exitosamente"
        ];
    }
    
    /**
     * BUSCAR registros
     */
    public function search($input) {
        $input['limit'] = min(50, intval($input['limit'] ?? 10));
        return $this->list($input);
    }
    
    /**
     * VALIDAR datos
     */
    public function validate($input) {
        $table = $input['table'] ?? 'consultas';
        $data = $input['data'] ?? [];
        $operation = $input['operation'] ?? 'create';
        
        $result = $this->validateData($table, $data, $operation);
        
        return [
            'data' => [
                'valid' => $result['valid'],
                'errors' => $result['errors']
            ],
            'message' => $result['valid'] ? 'Datos válidos' : 'Datos inválidos'
        ];
    }
    
    // Métodos auxiliares...
    
    private function getInput() {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        
        if (strpos($contentType, 'application/json') !== false) {
            $input = json_decode(file_get_contents('php://input'), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('JSON inválido: ' . json_last_error_msg());
            }
        } else {
            $input = $_REQUEST;
        }
        
        return $input ?: [];
    }
    
    private function loadRecord($table, $id, $with = []) {
        $config = $this->tableConfig[$table];
        $primaryKey = $config['primaryKey'];
        
        // Query base
        if ($table === 'consultas') {
            $sql = "SELECT c.*, p.first_name, p.last_name, p.document_number, p.phone_number, p.email
                    FROM consultas c 
                    LEFT JOIN rh_person p ON c.id_persona = p.person_id 
                    WHERE c.$primaryKey = ?";
        } else {
            $sql = "SELECT * FROM $table WHERE $primaryKey = ?";
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        $record = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$record) {
            return null;
        }
        
        // Cargar datos relacionados
        if ($table === 'consultas' && (empty($with) || in_array('anteojos', $with))) {
            $stmt = $this->db->prepare("SELECT * FROM consulta_anteojos WHERE id_consulta = ?");
            $stmt->execute([$id]);
            $anteojos = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($anteojos) {
                $record['anteojos'] = $anteojos;
            }
        }
        
        return $this->processRecord($table, $record);
    }
    
    private function processRecord($table, $record) {
        // Procesar campos especiales, formatear fechas, etc.
        if (isset($record['fecha_consulta'])) {
            $record['fecha_consulta_formatted'] = date('d/m/Y H:i', strtotime($record['fecha_consulta']));
        }
        
        return $record;
    }
    
    private function prepareDataForInsert($table, $data) {
        $config = $this->tableConfig[$table];
        $insertData = [];
        
        foreach ($config['fields'] as $field => $fieldConfig) {
            // Saltar campos auto-generados
            if (isset($fieldConfig['auto']) && $fieldConfig['auto']) {
                continue;
            }
            
            // Valor por defecto
            if (isset($fieldConfig['default'])) {
                if ($fieldConfig['default'] === 'session.user_id') {
                    $insertData[$field] = $this->userId;
                } else {
                    $insertData[$field] = $fieldConfig['default'];
                }
            }
            
            // Valor del input
            if (isset($data[$field])) {
                $insertData[$field] = $data[$field];
            }
            
            // Campos requeridos
            if (isset($fieldConfig['required']) && $fieldConfig['required'] && !isset($insertData[$field])) {
                throw new Exception("Campo requerido: $field");
            }
        }
        
        return $insertData;
    }
    
    private function prepareDataForUpdate($table, $data) {
        $config = $this->tableConfig[$table];
        $updateData = [];
        
        foreach ($data as $field => $value) {
            if (isset($config['fields'][$field])) {
                $fieldConfig = $config['fields'][$field];
                
                // No actualizar campos auto o primarios
                if ((isset($fieldConfig['auto']) && $fieldConfig['auto']) ||
                    (isset($fieldConfig['primary']) && $fieldConfig['primary'])) {
                    continue;
                }
                
                $updateData[$field] = $value;
            }
        }
        
        return $updateData;
    }
    
    private function validateData($table, $data, $operation) {
        $config = $this->tableConfig[$table];
        $errors = [];
        
        foreach ($config['fields'] as $field => $fieldConfig) {
            $value = $data[$field] ?? null;
            
            // Campos requeridos
            if (isset($fieldConfig['required']) && $fieldConfig['required']) {
                if (empty($value) && $value !== '0') {
                    $label = $fieldConfig['label'] ?? $field;
                    $errors[] = "$label es requerido";
                }
            }
            
            // Validaciones por tipo
            if (!empty($value) || $value === '0' || $value === 0) {
                switch ($fieldConfig['type']) {
                    case 'int':
                        if (!is_numeric($value) && $value !== '') {
                            $errors[] = "$field debe ser numérico";
                        }
                        break;
                    case 'decimal':
                        if (!is_numeric($value) && $value !== '') {
                            $errors[] = "$field debe ser decimal";
                        }
                        break;
                    case 'email':
                        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                            $errors[] = "$field debe ser un email válido";
                        }
                        break;
                }
            }
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
    
    private function handleRelatedData($operation, $parentId, $relatedData) {
        // Manejar datos de anteojos para consultas
        if (isset($relatedData['anteojos'])) {
            $anteojosData = $relatedData['anteojos'];
            $anteojosData['id_consulta'] = $parentId;
            
            if ($operation === 'create') {
                // Usar método interno sin transacción
                $this->createInternal('consulta_anteojos', $anteojosData);
            } else {
                // Verificar si existe registro de anteojos
                $stmt = $this->db->prepare("SELECT id_consulta_anteojos FROM consulta_anteojos WHERE id_consulta = ?");
                $stmt->execute([$parentId]);
                $existing = $stmt->fetch();
                
                if ($existing) {
                    // Usar método interno sin transacción
                    $this->updateInternal('consulta_anteojos', $existing['id_consulta_anteojos'], $anteojosData);
                } else {
                    // Usar método interno sin transacción
                    $this->createInternal('consulta_anteojos', $anteojosData);
                }
            }
        }
    }
    
    private function getSelectFields($table) {
        if ($table === 'consultas') {
            return "c.*, p.first_name, p.last_name, p.document_number, p.phone_number as paciente_telefono, p.email as paciente_email";
        }
        
        return "*";
    }
    
    private function buildSearchConditions($table, $search) {
        $conditions = [];
        $params = [];
        $searchTerm = "%$search%";
        
        if ($table === 'consultas') {
            $conditions[] = "c.txtmotivo ILIKE :search1";
            $conditions[] = "c.consulta_textarea ILIKE :search2";
            $conditions[] = "c.receta_textarea ILIKE :search3";
            $conditions[] = "p.first_name ILIKE :search4";
            $conditions[] = "p.last_name ILIKE :search5";
            $conditions[] = "p.document_number ILIKE :search6";
            
            $params = [
                ':search1' => $searchTerm,
                ':search2' => $searchTerm,
                ':search3' => $searchTerm,
                ':search4' => $searchTerm,
                ':search5' => $searchTerm,
                ':search6' => $searchTerm,
            ];
        }
        
        return ['where' => $conditions, 'params' => $params];
    }
    
    private function isValidField($table, $field) {
        return isset($this->tableConfig[$table]['fields'][$field]);
    }
    
    private function logDebug($context, $data) {
        if ($this->debug) {
            error_log("LivewireCRUD [$context]: " . json_encode($data));
        }
    }
    
    private function logError($context, $exception) {
        error_log("LivewireCRUD ERROR [$context]: " . $exception->getMessage() . " in " . $exception->getFile() . ":" . $exception->getLine());
    }
    
    private function handleError($exception) {
        $this->logError('HandleRequest', $exception);
        
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => $exception->getMessage(),
            'code' => 'SYSTEM_ERROR',
            'debug' => $this->debug ? [
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString()
            ] : null
        ]);
    }
    
    /**
     * Método interno para crear registros sin iniciar transacción
     * Se usa cuando ya hay una transacción activa
     */
    private function createInternal($table, $data) {
        // Validar datos
        $validationResult = $this->validateData($table, $data, 'create');
        if (!$validationResult['valid']) {
            throw new Exception('Datos inválidos: ' . implode(', ', $validationResult['errors']));
        }
        
        // Preparar datos para inserción
        $insertData = $this->prepareDataForInsert($table, $data);
        
        if (empty($insertData)) {
            throw new Exception('No hay datos válidos para insertar');
        }
        
        // Construir query INSERT
        $fields = array_keys($insertData);
        $placeholders = array_map(function($field) { return ":$field"; }, $fields);
        
        $sql = "INSERT INTO $table (" . implode(', ', $fields) . ") VALUES (" . implode(', ', $placeholders) . ")";
        
        $stmt = $this->db->prepare($sql);
        
        // Bind parameters
        foreach ($insertData as $field => $value) {
            $stmt->bindValue(":$field", $value);
        }
        
        $stmt->execute();
        
        return $this->db->lastInsertId();
    }
    
    /**
     * Método interno para actualizar registros sin iniciar transacción
     * Se usa cuando ya hay una transacción activa
     */
    private function updateInternal($table, $id, $data) {
        // Verificar que el registro existe
        $config = $this->tableConfig[$table];
        $primaryKey = $config['primaryKey'];
        
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM $table WHERE $primaryKey = ?");
        $stmt->execute([$id]);
        
        if (!$stmt->fetchColumn()) {
            throw new Exception('Registro no encontrado para actualizar');
        }
        
        // Validar datos
        $validationResult = $this->validateData($table, $data, 'update');
        if (!$validationResult['valid']) {
            throw new Exception('Datos inválidos: ' . implode(', ', $validationResult['errors']));
        }
        
        // Preparar datos para actualización
        $updateData = $this->prepareDataForUpdate($table, $data);
        
        if (empty($updateData)) {
            throw new Exception('No hay datos para actualizar');
        }
        
        // Construir query UPDATE
        $setParts = array_map(function($field) { return "$field = :$field"; }, array_keys($updateData));
        
        $sql = "UPDATE $table SET " . implode(', ', $setParts) . " WHERE $primaryKey = :id";
        
        $stmt = $this->db->prepare($sql);
        
        // Bind parameters
        foreach ($updateData as $field => $value) {
            $stmt->bindValue(":$field", $value);
        }
        $stmt->bindValue(':id', $id);
        
        $stmt->execute();
        
        return $stmt->rowCount();
    }
}

// Inicializar y ejecutar sistema
try {
    $debug = isset($_GET['debug']) || (isset($_SESSION['debug']) && $_SESSION['debug']);
    $system = new LivewireCRUDSystem($debug);
    $system->handleRequest();
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error del sistema: ' . $e->getMessage(),
        'code' => 'FATAL_ERROR'
    ]);
}
?>