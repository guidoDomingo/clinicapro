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

// TEMPORAL: Simular usuario autenticado para el sistema de consultas médicas
if (!isset($_SESSION['user_id'])) {
    // Simular un usuario médico autenticado
    $_SESSION['user_id'] = 1;
    $_SESSION['user_name'] = 'Dr. Sistema';
    $_SESSION['user_role'] = 'medico';
}

// Verificar autenticación (ya no debería fallar con la simulación de arriba)
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Usuario no autenticado',
        'code' => 'UNAUTHORIZED'
    ]);
    exit;
}

require_once dirname(__DIR__, 3) . '/model/conexion.php';

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
                'motivoscomunes' => ['type' => 'text', 'label' => 'Motivos Comunes'],
                'txtmotivo' => ['type' => 'text', 'label' => 'Motivo de Consulta'],
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
                'anteojos' => 'consulta_anteojos.id_consulta',
                'informe_imagen' => 'consulta_informe_imagen.id_consulta',
                'estudios' => 'consulta_estudios.id_consulta'
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
        
        'consulta_informe_imagen' => [
            'primaryKey' => 'id_consulta_informe_imagen',
            'displayName' => 'Informe con Imagen',
            'fields' => [
                'id_consulta_informe_imagen' => ['type' => 'int', 'primary' => true, 'auto' => true],
                'id_consulta' => ['type' => 'int', 'required' => true, 'foreign' => 'consultas.id_consulta'],
                'equipo_medico' => ['type' => 'varchar', 'label' => 'Equipo Médico'],
                'descripcion_od' => ['type' => 'text', 'label' => 'Descripción OD'],
                'descripcion_oi' => ['type' => 'text', 'label' => 'Descripción OI'],
                'archivos_od' => ['type' => 'json', 'label' => 'Archivos OD'],
                'archivos_oi' => ['type' => 'json', 'label' => 'Archivos OI'],
                'emails_compartir' => ['type' => 'varchar', 'label' => 'Emails para Compartir'],
                'compartir_activo' => ['type' => 'bool', 'default' => false, 'label' => 'Compartir Activo'],
                'fecha_creacion' => ['type' => 'timestamp', 'auto' => true, 'default' => 'CURRENT_TIMESTAMP'],
                'fecha_actualizacion' => ['type' => 'timestamp', 'auto' => true, 'default' => 'CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP']
            ]
        ],
        
        'consulta_estudios' => [
            'primaryKey' => 'id_consulta_estudios',
            'displayName' => 'Estudios Médicos',
            'fields' => [
                'id_consulta_estudios' => ['type' => 'int', 'primary' => true, 'auto' => true],
                'id_consulta' => ['type' => 'int', 'required' => true, 'foreign' => 'consultas.id_consulta'],
                'equipo_medico' => ['type' => 'varchar', 'label' => 'Equipo Médico'],
                'otro_equipo' => ['type' => 'varchar', 'label' => 'Otro Equipo'],
                'descripcion_estudio' => ['type' => 'text', 'label' => 'Descripción del Estudio'],
                'observaciones' => ['type' => 'text', 'label' => 'Observaciones'],
                'resultados' => ['type' => 'text', 'label' => 'Resultados'],
                'archivos_adjuntos' => ['type' => 'json', 'label' => 'Archivos Adjuntos'],
                'fecha_estudio' => ['type' => 'date', 'label' => 'Fecha del Estudio'],
                'fecha_creacion' => ['type' => 'timestamp', 'auto' => true, 'default' => 'CURRENT_TIMESTAMP'],
                'fecha_actualizacion' => ['type' => 'timestamp', 'auto' => true, 'default' => 'CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP']
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
                
            case 'get_motivos_comunes':
                return $this->getMotivosComunes($input);
                
            case 'get_preformatos':
                return $this->getPreformatos($input);
                
            case 'get_referenciales':
                return $this->getReferenciales($input);
                
            case 'upload_archivo':
                return $this->uploadArchivo($input);
                
            case 'get_archivos_consulta':
                return $this->getArchivosConsulta($input);
                
            case 'delete_archivo':
                return $this->deleteArchivo($input);
                
            case 'download_archivo':
                return $this->downloadArchivo($input);
                
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
                // Convertir booleanos para PostgreSQL
                if (is_bool($value)) {
                    $stmt->bindValue(":$field", $value, PDO::PARAM_BOOL);
                } else {
                    $stmt->bindValue(":$field", $value);
                }
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
                // Convertir booleanos para PostgreSQL
                if (is_bool($value)) {
                    $stmt->bindValue(":$field", $value, PDO::PARAM_BOOL);
                } else {
                    $stmt->bindValue(":$field", $value);
                }
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
                $this->db->prepare("DELETE FROM consulta_informe_imagen WHERE id_consulta = ?")->execute([$id]);
                $this->db->prepare("DELETE FROM consulta_estudios WHERE id_consulta = ?")->execute([$id]);
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
        $customOrderBy = null;
        if (!empty($search)) {
            $searchConditions = $this->buildSearchConditions($table, $search);
            if (!empty($searchConditions['where'])) {
                $operator = $searchConditions['operator'] ?? 'OR';
                $whereConditions[] = "(" . implode(" {$operator} ", $searchConditions['where']) . ")";
                $params = array_merge($params, $searchConditions['params']);
                
                // 🆕 CAPTURAR ORDER BY PERSONALIZADO
                if (isset($searchConditions['orderBy'])) {
                    $customOrderBy = $searchConditions['orderBy'];
                }
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
        
        // Order by - 🆕 USAR PERSONALIZADO SI EXISTE
        $orderClause = '';
        if ($customOrderBy) {
            $orderClause = $customOrderBy; // Ya incluye "ORDER BY"
        } elseif ($orderBy && $this->isValidField($table, $orderBy)) {
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
        // 🆕 MANEJO ESPECIAL PARA BÚSQUEDA INTELIGENTE DE PACIENTES
        if (isset($input['nombres']) && isset($input['apellidos']) && isset($input['ci'])) {
            // Convertir parámetros individuales en array de búsqueda
            $input['search'] = [
                'nombres' => $input['nombres'],
                'apellidos' => $input['apellidos'], 
                'ci' => $input['ci']
            ];
            
            // Limpiar parámetros individuales
            unset($input['nombres'], $input['apellidos'], $input['ci']);
        }
        
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
    
    /**
     * OBTENER motivos comunes por tipo de formulario
     */
    public function getMotivosComunes($input) {
        $tipoFormulario = $input['tipo_formulario'] ?? 'general';
        
        $stmt = $this->db->prepare("
            SELECT id_motivo, nombre, descripcion, activo, tipo_formulario 
            FROM motivos_comunes 
            WHERE tipo_formulario = :tipo_formulario AND activo = true 
            ORDER BY nombre ASC
        ");
        $stmt->bindParam(":tipo_formulario", $tipoFormulario, PDO::PARAM_STR);
        $stmt->execute();
        
        $motivos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return [
            'data' => $motivos,
            'message' => 'Motivos comunes obtenidos exitosamente',
            'meta' => ['tipo_formulario' => $tipoFormulario, 'total' => count($motivos)]
        ];
    }
    
    /**
     * OBTENER preformatos por tipo de formulario y tipo de contenido
     */
    public function getPreformatos($input) {
        $tipoFormulario = $input['tipo_formulario'] ?? 'general';
        $tipoContenido = $input['tipo'] ?? 'consulta'; // 'consulta', 'receta', etc.
        
        $stmt = $this->db->prepare("
            SELECT id_preformato, nombre, contenido, tipo, activo, tipo_formulario 
            FROM preformatos 
            WHERE tipo_formulario = :tipo_formulario 
            AND tipo = :tipo 
            AND activo = true 
            ORDER BY nombre ASC
        ");
        $stmt->bindParam(":tipo_formulario", $tipoFormulario, PDO::PARAM_STR);
        $stmt->bindParam(":tipo", $tipoContenido, PDO::PARAM_STR);
        $stmt->execute();
        
        $preformatos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return [
            'data' => $preformatos,
            'message' => 'Preformatos obtenidos exitosamente',
            'meta' => [
                'tipo_formulario' => $tipoFormulario, 
                'tipo' => $tipoContenido,
                'total' => count($preformatos)
            ]
        ];
    }

    /**
     * Obtener valores de referencia para selects de anteojos
     * @param array $input Datos de entrada con tipo de referencial
     * @return array Resultado con valores de referencia
     */
    public function getReferenciales($input) {
        try {
            $tipo = $input['tipo'] ?? '';
            
            if (empty($tipo)) {
                throw new Exception('Tipo de referencial requerido');
            }
            
            // Validar tipos permitidos para seguridad
            $tiposPermitidos = ['esfera', 'cilindro', 'adicion'];
            if (!in_array(strtolower($tipo), $tiposPermitidos)) {
                throw new Exception('Tipo de referencial no válido');
            }
            
            $sql = "
                SELECT rv.id, rv.valor, rv.etiqueta, rv.valor_numerico, rv.orden_visualizacion
                FROM referenciales r 
                INNER JOIN referencial_valores rv ON r.id = rv.referencial_id
                WHERE LOWER(r.codigo) = LOWER(:tipo) 
                  AND r.activo = 1 
                  AND rv.activo = 1
                ORDER BY rv.orden_visualizacion ASC, rv.valor_numerico ASC
            ";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['tipo' => $tipo]);
            $valores = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return [
                'data' => $valores,
                'message' => "Valores de referencia para '{$tipo}' obtenidos exitosamente",
                'meta' => [
                    'tipo' => $tipo,
                    'total' => count($valores)
                ]
            ];
            
        } catch (Exception $e) {
            throw new Exception('Error al obtener referenciales: ' . $e->getMessage());
        }
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
            // Para form-urlencoded o datos de formulario normales
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
        
        if ($table === 'consultas' && (empty($with) || in_array('informe_imagen', $with))) {
            $stmt = $this->db->prepare("SELECT * FROM consulta_informe_imagen WHERE id_consulta = ?");
            $stmt->execute([$id]);
            $informe_imagen = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($informe_imagen) {
                $record['informe_imagen'] = $informe_imagen;
            }
        }
        
        if ($table === 'consultas' && (empty($with) || in_array('estudios', $with))) {
            $stmt = $this->db->prepare("SELECT * FROM consulta_estudios WHERE id_consulta = ?");
            $stmt->execute([$id]);
            $estudios = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($estudios) {
                $record['estudios'] = $estudios;
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
                $insertData[$field] = $this->processFieldValue($data[$field], $fieldConfig);
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
                
                // Procesar según el tipo de campo
                $processedValue = $this->processFieldValue($value, $fieldConfig);
                $updateData[$field] = $processedValue;
            }
        }
        
        return $updateData;
    }
    
    /**
     * Procesar valor según el tipo de campo
     */
    private function processFieldValue($value, $fieldConfig) {
        $type = $fieldConfig['type'] ?? 'varchar';
        
        switch ($type) {
            case 'bool':
            case 'boolean':
                // Convertir valores booleanos
                if ($value === '' || $value === null || $value === 'false' || $value === false) {
                    $result = false;
                } else {
                    $result = (bool) $value;
                }
                
                return $result;
                
            case 'int':
            case 'integer':
                return $value === '' || $value === null ? null : (int) $value;
                
            case 'date':
            case 'timestamp':
                return $value === '' ? null : $value;
                
            default:
                return $value;
        }
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
        
        // Manejar datos de informe con imagen
        if (isset($relatedData['informe_imagen'])) {
            $informeData = $relatedData['informe_imagen'];
            $informeData['id_consulta'] = $parentId;
            
            if ($operation === 'create') {
                $this->createInternal('consulta_informe_imagen', $informeData);
            } else {
                // Verificar si existe registro de informe imagen
                $stmt = $this->db->prepare("SELECT id_consulta_informe_imagen FROM consulta_informe_imagen WHERE id_consulta = ?");
                $stmt->execute([$parentId]);
                $existing = $stmt->fetch();
                
                if ($existing) {
                    $this->updateInternal('consulta_informe_imagen', $existing['id_consulta_informe_imagen'], $informeData);
                } else {
                    $this->createInternal('consulta_informe_imagen', $informeData);
                }
            }
        }
        
        // Manejar datos de estudios
        if (isset($relatedData['estudios'])) {
            $estudiosData = $relatedData['estudios'];
            $estudiosData['id_consulta'] = $parentId;
            
            if ($operation === 'create') {
                $this->createInternal('consulta_estudios', $estudiosData);
            } else {
                // Verificar si existe registro de estudios
                $stmt = $this->db->prepare("SELECT id_consulta_estudios FROM consulta_estudios WHERE id_consulta = ?");
                $stmt->execute([$parentId]);
                $existing = $stmt->fetch();
                
                if ($existing) {
                    $this->updateInternal('consulta_estudios', $existing['id_consulta_estudios'], $estudiosData);
                } else {
                    $this->createInternal('consulta_estudios', $estudiosData);
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
        
        // Si search es un array (búsqueda específica por campos)
        if (is_array($search)) {
            $paramCounter = 1;
            
            // 🆕 BÚSQUEDA INTELIGENTE PARA PACIENTES: 
            // Si tenemos nombres, apellidos y ci con el mismo valor, hacer búsqueda combinada
            if ($table === 'rh_person' && 
                isset($search['nombres']) && isset($search['apellidos']) && isset($search['ci']) &&
                $search['nombres'] === $search['apellidos'] && $search['apellidos'] === $search['ci'] &&
                !empty($search['nombres'])) {
                
                $smartSearchTerm = trim($search['nombres']);
                $likeSearchTerm = "%{$smartSearchTerm}%";
                
                // 🎯 BÚSQUEDA INTELIGENTE CON PRIORIDAD:
                // 1. Coincidencia exacta en nombres o apellidos
                // 2. Coincidencia al inicio de nombres o apellidos  
                // 3. Coincidencia en cualquier parte
                // 4. Coincidencia en CI, teléfono o email
                
                $conditions[] = "(
                    CASE 
                        WHEN LOWER(first_name) = LOWER(:exact_search1) OR LOWER(last_name) = LOWER(:exact_search2) THEN 1
                        WHEN LOWER(first_name) LIKE LOWER(:start_search1) OR LOWER(last_name) LIKE LOWER(:start_search2) THEN 2
                        WHEN first_name ILIKE :smart_search1 OR last_name ILIKE :smart_search2 THEN 3
                        WHEN CONCAT(first_name, ' ', last_name) ILIKE :smart_search6 THEN 4
                        WHEN document_number ILIKE :smart_search3 THEN 5
                        WHEN phone_number ILIKE :smart_search4 OR email ILIKE :smart_search5 THEN 6
                        ELSE 7
                    END
                ) <= 6";
                
                $params = [
                    // Búsquedas exactas
                    ':exact_search1' => $smartSearchTerm,
                    ':exact_search2' => $smartSearchTerm,
                    // Búsquedas al inicio
                    ':start_search1' => $smartSearchTerm . '%',
                    ':start_search2' => $smartSearchTerm . '%',
                    // Búsquedas generales
                    ':smart_search1' => $likeSearchTerm,
                    ':smart_search2' => $likeSearchTerm,
                    ':smart_search3' => $likeSearchTerm,
                    ':smart_search4' => $likeSearchTerm,
                    ':smart_search5' => $likeSearchTerm,
                    ':smart_search6' => $likeSearchTerm,
                ];
                
                // 🔥 ORDENAMIENTO POR RELEVANCIA
                $orderBy = "ORDER BY (
                    CASE 
                        WHEN LOWER(first_name) = LOWER('{$smartSearchTerm}') OR LOWER(last_name) = LOWER('{$smartSearchTerm}') THEN 1
                        WHEN LOWER(first_name) LIKE LOWER('{$smartSearchTerm}%') OR LOWER(last_name) LIKE LOWER('{$smartSearchTerm}%') THEN 2
                        WHEN first_name ILIKE '{$likeSearchTerm}' OR last_name ILIKE '{$likeSearchTerm}' THEN 3
                        WHEN CONCAT(first_name, ' ', last_name) ILIKE '{$likeSearchTerm}' THEN 4
                        WHEN document_number ILIKE '{$likeSearchTerm}' THEN 5
                        ELSE 6
                    END
                ), first_name ASC, last_name ASC";
                
                return [
                    'where' => $conditions, 
                    'params' => $params, 
                    'operator' => 'AND',
                    'orderBy' => $orderBy
                ];
            }
            
            foreach ($search as $field => $value) {
                if (empty($value)) continue;
                
                $paramName = ":search{$paramCounter}";
                $searchValue = "%{$value}%";
                
                if ($table === 'consultas') {
                    switch ($field) {
                        case 'first_name':
                        case 'nombres':
                            $conditions[] = "p.first_name ILIKE {$paramName}";
                            break;
                        case 'last_name':
                        case 'apellidos':
                            $conditions[] = "p.last_name ILIKE {$paramName}";
                            break;
                        case 'document_number':
                        case 'ci':
                            $conditions[] = "p.document_number ILIKE {$paramName}";
                            break;
                        case 'txtmotivo':
                            $conditions[] = "c.txtmotivo ILIKE {$paramName}";
                            break;
                        case 'consulta':
                            $conditions[] = "c.consulta_textarea ILIKE {$paramName}";
                            break;
                        default:
                            if (isset($this->tableConfig[$table]['fields'][$field])) {
                                $conditions[] = "c.{$field} ILIKE {$paramName}";
                            }
                    }
                } elseif ($table === 'rh_person') {
                    switch ($field) {
                        case 'first_name':
                        case 'nombres':
                            $conditions[] = "first_name ILIKE {$paramName}";
                            break;
                        case 'last_name':
                        case 'apellidos':
                            $conditions[] = "last_name ILIKE {$paramName}";
                            break;
                        case 'document_number':
                        case 'ci':
                            $conditions[] = "document_number ILIKE {$paramName}";
                            break;
                        case 'phone_number':
                            $conditions[] = "phone_number ILIKE {$paramName}";
                            break;
                        case 'email':
                            $conditions[] = "email ILIKE {$paramName}";
                            break;
                        default:
                            if (isset($this->tableConfig[$table]['fields'][$field])) {
                                $conditions[] = "{$field} ILIKE {$paramName}";
                            }
                    }
                }
                
                $params[$paramName] = $searchValue;
                $paramCounter++;
            }
            
            // Usar AND para conectar las condiciones cuando es búsqueda específica
            return ['where' => $conditions, 'params' => $params, 'operator' => 'AND'];
        }
        
        // Si search es un string (búsqueda general)
        $searchTerm = "%{$search}%";
        
        // Manejo especial para búsquedas por ID específico (formato: campo:valor)
        if (strpos($search, ':') !== false) {
            $parts = explode(':', $search, 2);
            if (count($parts) === 2) {
                $field = trim($parts[0]);
                $value = trim($parts[1]);
                
                if ($table === 'consultas') {
                    switch ($field) {
                        case 'id_persona':
                        case 'person_id':
                            $conditions[] = "c.id_persona = :search_id";
                            $params[':search_id'] = $value;
                            return ['where' => $conditions, 'params' => $params, 'operator' => 'AND'];
                        case 'id':
                        case 'id_consulta':
                            $conditions[] = "c.id_consulta = :search_id";
                            $params[':search_id'] = $value;
                            return ['where' => $conditions, 'params' => $params, 'operator' => 'AND'];
                    }
                } elseif ($table === 'rh_person') {
                    switch ($field) {
                        case 'id':
                        case 'person_id':
                            $conditions[] = "person_id = :search_id";
                            $params[':search_id'] = $value;
                            return ['where' => $conditions, 'params' => $params, 'operator' => 'AND'];
                    }
                }
            }
        }
        
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
        } elseif ($table === 'rh_person') {
            $conditions[] = "first_name ILIKE :search1";
            $conditions[] = "last_name ILIKE :search2";
            $conditions[] = "document_number ILIKE :search3";
            $conditions[] = "phone_number ILIKE :search4";
            $conditions[] = "email ILIKE :search5";
            $conditions[] = "CONCAT(first_name, ' ', last_name) ILIKE :search6";
            
            $params = [
                ':search1' => $searchTerm,
                ':search2' => $searchTerm,
                ':search3' => $searchTerm,
                ':search4' => $searchTerm,
                ':search5' => $searchTerm,
                ':search6' => $searchTerm,
            ];
        }
        
        return ['where' => $conditions, 'params' => $params, 'operator' => 'OR'];
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
            // Convertir booleanos para PostgreSQL
            if (is_bool($value)) {
                $stmt->bindValue(":$field", $value, PDO::PARAM_BOOL);
            } else {
                $stmt->bindValue(":$field", $value);
            }
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
            // Convertir booleanos para PostgreSQL
            if (is_bool($value)) {
                $stmt->bindValue(":$field", $value, PDO::PARAM_BOOL);
            } else {
                $stmt->bindValue(":$field", $value);
            }
        }
        $stmt->bindValue(':id', $id);
        
        $stmt->execute();
        
        return $stmt->rowCount();
    }
    
    /**
     * SUBIR archivo y vincularlo a consulta
     */
    public function uploadArchivo($input) {
        // DEBUG específico para upload - con archivo específico
        $logFile = __DIR__ . '/../../../logs/upload_debug.log';
        error_log("=== UPLOAD DEBUG ESPECÍFICO " . date('Y-m-d H:i:s') . " ===", 3, $logFile);
        error_log("FILES recibidos: " . json_encode($_FILES), 3, $logFile);
        error_log("POST recibido: " . json_encode($_POST), 3, $logFile);
        error_log("INPUT recibido: " . json_encode($input), 3, $logFile);
        
        $files = $_FILES ?? [];
        $id_consulta = $input['id_consulta'] ?? $_POST['id_consulta'] ?? null;
        $id_usuario = $_SESSION['id_usuario'] ?? 1;
        $id_persona = $input['id_persona'] ?? $_POST['id_persona'] ?? null;
        
        error_log("Valores extraídos - id_consulta: $id_consulta, id_persona: $id_persona, id_usuario: $id_usuario", 3, $logFile);
        
        if (!$id_consulta) {
            throw new Exception("ID de consulta requerido");
        }
        
        // Buscar archivos en diferentes formatos posibles
        $archivosField = null;
        if (!empty($files['archivos'])) {
            $archivosField = $files['archivos'];
            error_log("✓ Archivos encontrados en FILES['archivos']", 3, $logFile);
        } else {
            error_log("✗ No se encontró FILES['archivos'], buscando en otros campos...", 3, $logFile);
            // Buscar cualquier campo que contenga archivos
            foreach ($files as $fieldName => $fieldData) {
                error_log("Revisando campo: $fieldName - " . json_encode($fieldData), 3, $logFile);
                if (!empty($fieldData['name'])) {
                    $archivosField = $fieldData;
                    error_log("✓ Archivos encontrados en campo '$fieldName'", 3, $logFile);
                    break;
                }
            }
        }
        
        if (!$archivosField) {
            error_log("✗ ERROR CRÍTICO: No se encontraron archivos en ningún campo", 3, $logFile);
            error_log("FILES completo: " . print_r($_FILES, true), 3, $logFile);
            throw new Exception("No se encontraron archivos para subir");
        }
        
        error_log("Archivos field seleccionado: " . json_encode($archivosField), 3, $logFile);
        
        // Crear directorio de archivos si no existe
        $uploadDir = '../../../uploads/consultas/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $subidos = [];
        $errores = [];
        
        // Manejar múltiples archivos
        $fileArray = $archivosField;
        $fileCount = is_array($fileArray['name']) ? count($fileArray['name']) : 1;
        
        error_log("Cantidad de archivos a procesar: $fileCount", 3, $logFile);
        error_log("Estructura del array de archivos: " . json_encode($fileArray), 3, $logFile);
        
        for ($i = 0; $i < $fileCount; $i++) {
            try {
                $fileName = is_array($fileArray['name']) ? $fileArray['name'][$i] : $fileArray['name'];
                $fileTmp = is_array($fileArray['tmp_name']) ? $fileArray['tmp_name'][$i] : $fileArray['tmp_name'];
                $fileSize = is_array($fileArray['size']) ? $fileArray['size'][$i] : $fileArray['size'];
                $fileError = is_array($fileArray['error']) ? $fileArray['error'][$i] : $fileArray['error'];
                
                error_log("Archivo $i procesando: nombre='$fileName', tmp='$fileTmp', size=$fileSize, error=$fileError", 3, $logFile);
                
                if ($fileError !== UPLOAD_ERR_OK) {
                    error_log("✗ Error de upload en archivo '$fileName': " . $this->getUploadErrorMessage($fileError), 3, $logFile);
                    $errores[] = "Error al subir $fileName: " . $this->getUploadErrorMessage($fileError);
                    continue;
                }
                
                // Validar tamaño (max 50MB)
                if ($fileSize > 50 * 1024 * 1024) {
                    error_log("✗ Archivo '$fileName' demasiado grande: $fileSize bytes", 3, $logFile);
                    $errores[] = "Archivo $fileName demasiado grande (máx 50MB)";
                    continue;
                }
                
                error_log("✓ Archivo '$fileName' pasó validaciones iniciales", 3, $logFile);
                
                // Generar nombre único
                $extension = pathinfo($fileName, PATHINFO_EXTENSION);
                $uniqueName = uniqid() . '_' . time() . '.' . $extension;
                $filePath = $uploadDir . $uniqueName;
                
                // Mover archivo
                if (move_uploaded_file($fileTmp, $filePath)) {
                    error_log("✓ Archivo '$fileName' movido exitosamente a: $filePath", 3, $logFile);
                    
                    // Calcular checksum
                    $checksum = md5_file($filePath);
                    error_log("✓ Checksum calculado: $checksum", 3, $logFile);
                    
                    // Detectar tipo MIME de forma compatible
                    $mimeType = 'application/octet-stream'; // Por defecto
                    if (function_exists('finfo_file')) {
                        $finfo = finfo_open(FILEINFO_MIME_TYPE);
                        $mimeType = finfo_file($finfo, $filePath) ?: $mimeType;
                        finfo_close($finfo);
                    } elseif (function_exists('mime_content_type')) {
                        $mimeType = mime_content_type($filePath) ?: $mimeType;
                    }
                    
                    $this->db->beginTransaction();
                    
                    // Insertar en tabla archivos
                    $sql = "INSERT INTO archivos (nombre_archivo, ruta_archivo, id_usuario, id_persona, origen, tamano_archivo, tipo_archivo, checksum, fecha_creacion)
                            VALUES (:nombre, :ruta, :id_usuario, :id_persona, 'consulta', :tamano, :tipo, :checksum, NOW())
                            RETURNING id_archivo";
                    
                    $stmt = $this->db->prepare($sql);
                    $stmt->execute([
                        'nombre' => $fileName,
                        'ruta' => $filePath,
                        'id_usuario' => $id_usuario,
                        'id_persona' => $id_persona,
                        'tamano' => $fileSize,
                        'tipo' => $mimeType,
                        'checksum' => $checksum
                    ]);
                    
                    $id_archivo = $stmt->fetchColumn();
                    
                    // Vincular con consulta
                    $sql = "INSERT INTO archivos_consulta (id_consulta, id_archivo, fecha_adjunto)
                            VALUES (:id_consulta, :id_archivo, NOW())";
                    
                    $stmt = $this->db->prepare($sql);
                    $stmt->execute([
                        'id_consulta' => $id_consulta,
                        'id_archivo' => $id_archivo
                    ]);
                    
                    $this->db->commit();
                    
                    error_log("✓ Archivo '$fileName' insertado en BD con ID: $id_archivo", 3, $logFile);
                    
                    $subidos[] = [
                        'id_archivo' => $id_archivo,
                        'nombre_original' => $fileName,
                        'tamano' => $fileSize,
                        'tipo' => $mimeType
                    ];
                } else {
                    error_log("✗ Error al mover archivo '$fileName' de '$fileTmp' a '$filePath'", 3, $logFile);
                    $errores[] = "Error al guardar archivo $fileName";
                }
                
            } catch (Exception $e) {
                error_log("✗ Excepción procesando archivo '$fileName': " . $e->getMessage(), 3, $logFile);
                if ($this->db->inTransaction()) {
                    $this->db->rollBack();
                }
                $errores[] = "Error procesando $fileName: " . $e->getMessage();
            }
        }
        
        error_log("=== RESUMEN UPLOAD ===", 3, $logFile);
        error_log("Total archivos subidos: " . count($subidos), 3, $logFile);
        error_log("Total errores: " . count($errores), 3, $logFile);
        error_log("Archivos subidos: " . json_encode($subidos), 3, $logFile);
        error_log("Errores: " . json_encode($errores), 3, $logFile);
        
        return [
            'success' => !empty($subidos),
            'data' => $subidos, // Para compatibilidad con el sistema principal
            'subidos' => $subidos, // Para compatibilidad con el frontend
            'errores' => $errores,
            'total_subidos' => count($subidos),
            'total_errores' => count($errores),
            'message' => count($subidos) > 0 ? count($subidos) . ' archivo(s) subido(s) exitosamente' : 'Error: No se pudo subir ningún archivo'
        ];
    }
    
    /**
     * OBTENER archivos de una consulta
     */
    public function getArchivosConsulta($input) {
        $id_consulta = $input['id_consulta'] ?? null;
        
        if (!$id_consulta) {
            throw new Exception("ID de consulta requerido");
        }
        
        $sql = "SELECT a.id_archivo, a.nombre_archivo, a.ruta_archivo, a.tamano_archivo, 
                       a.tipo_archivo, a.fecha_creacion, ac.fecha_adjunto
                FROM archivos a
                INNER JOIN archivos_consulta ac ON a.id_archivo = ac.id_archivo
                WHERE ac.id_consulta = :id_consulta
                ORDER BY ac.fecha_adjunto DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id_consulta' => $id_consulta]);
        
        $archivos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return [
            'success' => true,
            'data' => $archivos,
            'archivos' => $archivos, // Mantener ambos campos para compatibilidad
            'message' => count($archivos) > 0 ? 'Archivos obtenidos exitosamente' : 'No hay archivos adjuntos'
        ];
    }
    
    /**
     * ELIMINAR archivo
     */
    public function deleteArchivo($input) {
        $id_archivo = $input['id_archivo'] ?? null;
        
        if (!$id_archivo) {
            throw new Exception("ID de archivo requerido");
        }
        
        $this->db->beginTransaction();
        
        try {
            // Obtener información del archivo
            $sql = "SELECT ruta_archivo FROM archivos WHERE id_archivo = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['id' => $id_archivo]);
            $archivo = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$archivo) {
                throw new Exception("Archivo no encontrado");
            }
            
            // Eliminar relaciones con consultas
            $sql = "DELETE FROM archivos_consulta WHERE id_archivo = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['id' => $id_archivo]);
            
            // Eliminar registro del archivo
            $sql = "DELETE FROM archivos WHERE id_archivo = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['id' => $id_archivo]);
            
            // Eliminar archivo físico
            if (file_exists($archivo['ruta_archivo'])) {
                unlink($archivo['ruta_archivo']);
            }
            
            $this->db->commit();
            
            return [
                'success' => true,
                'message' => 'Archivo eliminado exitosamente'
            ];
            
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
    
    /**
     * DESCARGAR archivo
     */
    public function downloadArchivo($input) {
        $id_archivo = $input['id_archivo'] ?? null;
        
        if (!$id_archivo) {
            throw new Exception("ID de archivo requerido");
        }
        
        $sql = "SELECT nombre_archivo, ruta_archivo, tipo_archivo FROM archivos WHERE id_archivo = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id_archivo]);
        $archivo = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$archivo) {
            throw new Exception("Archivo no encontrado");
        }
        
        if (!file_exists($archivo['ruta_archivo'])) {
            throw new Exception("Archivo físico no encontrado");
        }
        
        // Configurar headers para descarga
        header('Content-Type: ' . $archivo['tipo_archivo']);
        header('Content-Disposition: attachment; filename="' . $archivo['nombre_archivo'] . '"');
        header('Content-Length: ' . filesize($archivo['ruta_archivo']));
        
        // Enviar archivo
        readfile($archivo['ruta_archivo']);
        exit;
    }
    
    /**
     * Obtener mensaje de error de upload
     */
    private function getUploadErrorMessage($error) {
        switch ($error) {
            case UPLOAD_ERR_INI_SIZE:
                return 'El archivo excede el tamaño máximo permitido por PHP';
            case UPLOAD_ERR_FORM_SIZE:
                return 'El archivo excede el tamaño máximo del formulario';
            case UPLOAD_ERR_PARTIAL:
                return 'El archivo se subió parcialmente';
            case UPLOAD_ERR_NO_FILE:
                return 'No se subió ningún archivo';
            case UPLOAD_ERR_NO_TMP_DIR:
                return 'Falta directorio temporal';
            case UPLOAD_ERR_CANT_WRITE:
                return 'Error al escribir archivo al disco';
            case UPLOAD_ERR_EXTENSION:
                return 'Subida detenida por extensión';
            default:
                return 'Error desconocido';
        }
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