<?php

/**
 * DatabaseMapper - Sistema de mapeo directo con base de datos
 * 
 * Esta clase maneja el mapeo completo entre formularios y tablas de la base de datos,
 * proporcionando operaciones CRUD robustas para todas las consultas médicas.
 * 
 * @author Sistema de Consultas
 * @version 2.0.0
 */

class DatabaseMapper {
    
    private $pdo;
    private $config;
    
    // Configuración de conexión de base de datos
    private const DB_CONFIG = [
        'host' => 'localhost',
        'port' => '5432', 
        'dbname' => 'clinica',
        'user' => 'postgres',
        'password' => 'admin'
    ];
    
    /**
     * Configuración completa de mapeo de formularios a tablas
     */
    private const FORM_MAPPING = [
        'general' => [
            'main_table' => 'consultas',
            'fields' => [
                // Campos básicos comunes
                'id_consulta' => ['type' => 'integer', 'auto_increment' => true],
                'id_persona' => ['type' => 'integer', 'required' => true],
                'motivoscomunes' => ['type' => 'varchar', 'max_length' => 255],
                'txtmotivo' => ['type' => 'varchar', 'max_length' => 255],
                'visionod' => ['type' => 'varchar', 'max_length' => 50],
                'visionoi' => ['type' => 'varchar', 'max_length' => 50], 
                'tensionod' => ['type' => 'varchar', 'max_length' => 50],
                'tensionoi' => ['type' => 'varchar', 'max_length' => 50],
                'consulta_textarea' => ['type' => 'text'],
                'receta_textarea' => ['type' => 'text'],
                'txtnota' => ['type' => 'text'],
                'proximaconsulta' => ['type' => 'date'],
                'whatsapptxt' => ['type' => 'varchar', 'max_length' => 50],
                'email' => ['type' => 'varchar', 'max_length' => 100],
                'id_user' => ['type' => 'integer'],
                'id_reserva' => ['type' => 'integer', 'default' => 0],
                'tipo_formulario' => ['type' => 'varchar', 'max_length' => 50, 'default' => 'general']
            ]
        ],
        
        'anteojos' => [
            'main_table' => 'consultas',
            'related_tables' => [
                'consulta_anteojos' => [
                    'foreign_key' => 'id_consulta',
                    'fields' => [
                        'id_consulta_anteojos' => ['type' => 'integer', 'auto_increment' => true],
                        'id_consulta' => ['type' => 'integer', 'foreign_key' => true],
                        'esfera_od' => ['type' => 'varchar', 'max_length' => 50, 'form_id' => 'od_esf'],
                        'cilindro_od' => ['type' => 'varchar', 'max_length' => 50, 'form_id' => 'od_cil'],
                        'eje_od' => ['type' => 'varchar', 'max_length' => 50, 'form_id' => 'od_eje'],
                        'dnp_od' => ['type' => 'varchar', 'max_length' => 50],
                        'add_od' => ['type' => 'varchar', 'max_length' => 50, 'form_id' => 'od_adicion'],
                        'nota_od' => ['type' => 'text'],
                        'esfera_oi' => ['type' => 'varchar', 'max_length' => 50, 'form_id' => 'oi_esf'],
                        'cilindro_oi' => ['type' => 'varchar', 'max_length' => 50, 'form_id' => 'oi_cil'],
                        'eje_oi' => ['type' => 'varchar', 'max_length' => 50, 'form_id' => 'oi_eje'],
                        'dnp_oi' => ['type' => 'varchar', 'max_length' => 50],
                        'add_oi' => ['type' => 'varchar', 'max_length' => 50, 'form_id' => 'oi_adicion'],
                        'nota_oi' => ['type' => 'text'],
                        'altura_od' => ['type' => 'varchar', 'max_length' => 50],
                        'altura_oi' => ['type' => 'varchar', 'max_length' => 50],
                        'dist_interpupilar' => ['type' => 'varchar', 'max_length' => 50],
                        'notas' => ['type' => 'text']
                    ]
                ]
            ]
        ],
        
        'estudios' => [
            'main_table' => 'consultas',
            'related_tables' => [
                'consulta_estudios' => [
                    'foreign_key' => 'id_consulta',
                    'fields' => [
                        'id_consulta_estudios' => ['type' => 'integer', 'auto_increment' => true],
                        'id_consulta' => ['type' => 'integer', 'foreign_key' => true],
                        'equipo_medico' => ['type' => 'varchar', 'max_length' => 100, 'form_id' => 'tipo_estudio'],
                        'otro_equipo' => ['type' => 'varchar', 'max_length' => 100],
                        'resultados' => ['type' => 'text', 'form_id' => 'observaciones'],
                        'emails_compartir' => ['type' => 'text'],
                        'compartir_activo' => ['type' => 'boolean', 'default' => false]
                    ]
                ]
            ]
        ],
        
        'informe_imagen' => [
            'main_table' => 'consultas', 
            'related_tables' => [
                'consulta_informe_imagen' => [
                    'foreign_key' => 'id_consulta',
                    'fields' => [
                        'id_consulta_informe_imagen' => ['type' => 'integer', 'auto_increment' => true],
                        'id_consulta' => ['type' => 'integer', 'foreign_key' => true],
                        'equipo_medico' => ['type' => 'varchar', 'max_length' => 100],
                        'descripcion_od' => ['type' => 'text'],
                        'descripcion_oi' => ['type' => 'text'],
                        'emails_compartir' => ['type' => 'text'],
                        'compartir_activo' => ['type' => 'boolean', 'default' => false],
                        'archivos_od' => ['type' => 'json'],
                        'archivos_oi' => ['type' => 'json']
                    ]
                ]
            ]
        ]
    ];
    
    public function __construct() {
        $this->initializeDatabase();
        $this->config = self::FORM_MAPPING;
    }
    
    /**
     * Inicializar conexión a la base de datos
     */
    private function initializeDatabase() {
        try {
            $dsn = sprintf(
                'pgsql:host=%s;port=%s;dbname=%s',
                self::DB_CONFIG['host'],
                self::DB_CONFIG['port'],
                self::DB_CONFIG['dbname']
            );
            
            $this->pdo = new PDO($dsn, self::DB_CONFIG['user'], self::DB_CONFIG['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]);
            
        } catch (PDOException $e) {
            throw new Exception("Error de conexión a base de datos: " . $e->getMessage());
        }
    }
    
    /**
     * Obtener mapeo de campos HTML para cada tipo de formulario
     * @param string $formType Tipo de formulario
     * @return array Mapeo de campos HTML a columnas de BD
     */
    public function getHtmlFieldMapping($formType) {
        $htmlMappings = [
            'general' => [
                // Campos principales
                'txtmotivo' => 'motivo_consulta',
                'visionod' => 'vision_od',
                'visionoi' => 'vision_oi', 
                'tensionod' => 'tension_od',
                'tensionoi' => 'tension_oi',
                'txtnota' => 'nota_consulta',
                'proximaconsulta' => 'proxima_consulta',
                'whatsapptxt' => 'mensaje_whatsapp',
                'email' => 'email_paciente',
                'motivoscomunes' => 'motivo_comun_id'
            ],
            'anteojos' => [
                // Campos principales
                'txtmotivo' => 'motivo_consulta',
                'txtnota' => 'nota_consulta',
                'proximaconsulta' => 'proxima_consulta',
                'whatsapptxt' => 'mensaje_whatsapp',
                'email' => 'email_paciente',
                'motivoscomunes' => 'motivo_comun_id',
                // Campos específicos de anteojos
                'od_esf' => 'esfera_od',
                'od_cil' => 'cilindro_od',
                'od_eje' => 'eje_od',
                'od_adicion' => 'add_od',
                'oi_esf' => 'esfera_oi',
                'oi_cil' => 'cilindro_oi',
                'oi_eje' => 'eje_oi',
                'oi_adicion' => 'add_oi',
                'dist_interpupilar' => 'dist_interpupilar',
                'altura_od' => 'altura_od',
                'altura_oi' => 'altura_oi'
            ],
            'estudios' => [
                // Campos principales
                'txtmotivo' => 'motivo_consulta',
                'txtnota' => 'nota_consulta',
                'proximaconsulta' => 'proxima_consulta',
                'whatsapptxt' => 'mensaje_whatsapp',
                'email' => 'email_paciente',
                'motivoscomunes' => 'motivo_comun_id',
                // Campos específicos de estudios
                'tipo_estudio' => 'equipo_medico',
                'observaciones' => 'resultados',
                'txtEmailShare-estudios' => 'emails_compartir'
            ],
            'informe_imagen' => [
                // Campos principales
                'txtmotivo-informe-imagen' => 'txtmotivo',  // Campo de motivo específico
                'txtnota-informe-imagen' => 'nota_consulta',
                'proximaconsulta-informe-imagen' => 'proxima_consulta',
                'whatsapptxt-informe-imagen' => 'mensaje_whatsapp',
                'email-informe-imagen' => 'email_paciente',
                'motivoscomunes-informe-imagen' => 'motivo_comun_id',
                // Campos específicos de informe imagen con IDs correctos del HTML
                'txtEmailShare-informe-imagen' => 'emails_compartir',
                'equipoMedico-informe-imagen' => 'equipo_medico',
                'descripcion-od-textarea-informe-imagen' => 'descripcion_od',
                'descripcion-oi-textarea-informe-imagen' => 'descripcion_oi',
                'consulta-textarea-informe-imagen' => 'consulta_textarea', // Descripción general desde main
                'formatoConsulta-informe-imagen' => 'formato_consulta_id',
                // Mapeos alternativos comunes (para compatibilidad)
                'txtmotivo' => 'txtmotivo',  // Campo directo
                'txtnota' => 'nota_consulta',
                'proximaconsulta' => 'proxima_consulta',
                'whatsapptxt' => 'mensaje_whatsapp',
                'email' => 'email_paciente',
                'motivoscomunes' => 'motivo_comun_id'
            ]
        ];
        
        return $htmlMappings[$formType] ?? [];
    }
    
    /**
     * Obtener una consulta completa con todos sus datos relacionados
     * 
     * @param int $idConsulta ID de la consulta
     * @return array Datos completos de la consulta
     */
    public function getConsulta($idConsulta) {
        try {
            // Obtener datos principales de la consulta
            $stmt = $this->pdo->prepare("SELECT * FROM consultas WHERE id_consulta = ?");
            $stmt->execute([$idConsulta]);
            $consultaMain = $stmt->fetch();
            
            if (!$consultaMain) {
                throw new Exception("Consulta no encontrada con ID: $idConsulta");
            }
            
            $tipoFormulario = $consultaMain['tipo_formulario'] ?? 'general';
            $result = ['main' => $consultaMain, 'type' => $tipoFormulario, 'tipo_formulario' => $tipoFormulario];
            
            // Obtener datos relacionados según el tipo de formulario
            if (isset($this->config[$tipoFormulario]['related_tables'])) {
                foreach ($this->config[$tipoFormulario]['related_tables'] as $tableName => $tableConfig) {
                    $stmt = $this->pdo->prepare("SELECT * FROM $tableName WHERE id_consulta = ?");
                    $stmt->execute([$idConsulta]);
                    $relatedData = $stmt->fetch();
                    
                    if ($relatedData) {
                        $result['related'][$tableName] = $relatedData;
                    }
                }
            }
            
            return [
                'success' => true,
                'data' => $result,
                'message' => 'Consulta obtenida exitosamente'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error obteniendo consulta: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Guardar una consulta completa (crear o actualizar)
     * 
     * @param array $data Datos del formulario
     * @param string $tipoFormulario Tipo de formulario
     * @param int|null $idConsulta ID para actualización (null para crear)
     * @return array Resultado de la operación
     */
    public function saveConsulta($data, $tipoFormulario, $idConsulta = null) {
        try {
            $this->pdo->beginTransaction();
            
            $isUpdate = $idConsulta !== null;
            
            // Preparar datos para tabla principal
            $mainData = $this->prepareMainTableData($data, $tipoFormulario);
            
            if ($isUpdate) {
                // Actualizar consulta existente
                $idConsultaResult = $this->updateMainRecord($idConsulta, $mainData);
            } else {
                // Crear nueva consulta
                $idConsultaResult = $this->insertMainRecord($mainData);
            }
            
            // Manejar tablas relacionadas si existen
            if (isset($this->config[$tipoFormulario]['related_tables'])) {
                foreach ($this->config[$tipoFormulario]['related_tables'] as $tableName => $tableConfig) {
                    $relatedData = $this->prepareRelatedTableData($data, $tableConfig, $idConsultaResult);
                    
                    if (!empty($relatedData)) {
                        $this->saveRelatedRecord($tableName, $relatedData, $idConsultaResult, $isUpdate);
                    }
                }
            }
            
            $this->pdo->commit();
            
            return [
                'success' => true,
                'id_consulta' => $idConsultaResult,
                'message' => $isUpdate ? 'Consulta actualizada exitosamente' : 'Consulta creada exitosamente',
                'operation' => $isUpdate ? 'update' : 'create'
            ];
            
        } catch (Exception $e) {
            $this->pdo->rollBack();
            return [
                'success' => false,
                'message' => 'Error guardando consulta: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Eliminar una consulta completa
     * 
     * @param int $idConsulta ID de la consulta a eliminar
     * @return array Resultado de la operación
     */
    public function deleteConsulta($idConsulta) {
        try {
            $this->pdo->beginTransaction();
            
            // Las tablas relacionadas se eliminan automáticamente por CASCADE
            $stmt = $this->pdo->prepare("DELETE FROM consultas WHERE id_consulta = ?");
            $stmt->execute([$idConsulta]);
            
            $rowsAffected = $stmt->rowCount();
            
            $this->pdo->commit();
            
            if ($rowsAffected > 0) {
                return [
                    'success' => true,
                    'message' => 'Consulta eliminada exitosamente'
                ];
            } else {
                return [
                    'success' => false, 
                    'message' => 'No se encontró la consulta a eliminar'
                ];
            }
            
        } catch (Exception $e) {
            $this->pdo->rollBack();
            return [
                'success' => false,
                'message' => 'Error eliminando consulta: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Preparar datos para la tabla principal (consultas)
     */
    private function prepareMainTableData($data, $tipoFormulario) {
        $mainFields = $this->config['general']['fields'];
        $preparedData = [];
        
        foreach ($mainFields as $fieldName => $fieldConfig) {
            if (isset($fieldConfig['auto_increment']) && $fieldConfig['auto_increment']) {
                continue; // Skip auto increment fields
            }
            
            if (isset($data[$fieldName])) {
                $preparedData[$fieldName] = $data[$fieldName];
            } elseif (isset($fieldConfig['default'])) {
                $preparedData[$fieldName] = $fieldConfig['default'];
            }
        }
        
        // Asegurar que tipo_formulario esté correcto
        $preparedData['tipo_formulario'] = $tipoFormulario;
        $preparedData['ultima_modificacion'] = date('Y-m-d H:i:s');
        
        return $preparedData;
    }
    
    /**
     * Preparar datos para tablas relacionadas
     */
    private function prepareRelatedTableData($data, $tableConfig, $idConsulta) {
        $preparedData = ['id_consulta' => $idConsulta];
        
        foreach ($tableConfig['fields'] as $fieldName => $fieldConfig) {
            if (isset($fieldConfig['auto_increment']) && $fieldConfig['auto_increment']) {
                continue;
            }
            
            if (isset($fieldConfig['foreign_key']) && $fieldConfig['foreign_key']) {
                continue; // Ya se maneja arriba
            }
            
            // Mapear desde form_id si existe
            $sourceFieldName = $fieldConfig['form_id'] ?? $fieldName;
            
            if (isset($data[$sourceFieldName])) {
                $preparedData[$fieldName] = $data[$sourceFieldName];
            } elseif (isset($fieldConfig['default'])) {
                $preparedData[$fieldName] = $fieldConfig['default'];
            }
        }
        
        return $preparedData;
    }
    
    /**
     * Insertar registro principal
     */
    private function insertMainRecord($data) {
        $fields = array_keys($data);
        $placeholders = ':' . implode(', :', $fields);
        
        $sql = "INSERT INTO consultas (" . implode(', ', $fields) . ") VALUES ($placeholders) RETURNING id_consulta";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($data);
        
        return $stmt->fetchColumn();
    }
    
    /**
     * Actualizar registro principal
     */
    private function updateMainRecord($idConsulta, $data) {
        $fields = array_keys($data);
        $setClause = implode(', ', array_map(fn($field) => "$field = :$field", $fields));
        
        $sql = "UPDATE consultas SET $setClause WHERE id_consulta = :id_consulta";
        $data['id_consulta'] = $idConsulta;
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($data);
        
        return $idConsulta;
    }
    
    /**
     * Guardar registro relacionado
     */
    private function saveRelatedRecord($tableName, $data, $idConsulta, $isUpdate) {
        if ($isUpdate) {
            // Intentar actualizar primero
            $updateFields = array_diff(array_keys($data), ['id_consulta']);
            $setClause = implode(', ', array_map(fn($field) => "$field = :$field", $updateFields));
            
            $sql = "UPDATE $tableName SET $setClause WHERE id_consulta = :id_consulta";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($data);
            
            // Si no se actualizó ninguna fila, insertar
            if ($stmt->rowCount() === 0) {
                $this->insertRelatedRecord($tableName, $data);
            }
        } else {
            $this->insertRelatedRecord($tableName, $data);
        }
    }
    
    /**
     * Insertar registro relacionado
     */
    private function insertRelatedRecord($tableName, $data) {
        $fields = array_keys($data);
        $placeholders = ':' . implode(', :', $fields);
        
        $sql = "INSERT INTO $tableName (" . implode(', ', $fields) . ") VALUES ($placeholders)";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($data);
    }
    
    /**
     * Obtener estructura de formulario para mapeo en frontend
     * 
     * @param string $tipoFormulario Tipo de formulario
     * @return array Estructura de mapeo
     */
    public function getFormMapping($tipoFormulario) {
        return $this->config[$tipoFormulario] ?? $this->config['general'];
    }
    
    /**
     * Obtener mapeo completo de todos los tipos de formulario
     * 
     * @return array Configuración completa
     */
    public function getAllFormMappings() {
        return $this->config;
    }
    
    /**
     * Validar datos antes de guardar
     * 
     * @param array $data Datos a validar
     * @param string $tipoFormulario Tipo de formulario
     * @return array Resultado de validación
     */
    public function validateData($data, $tipoFormulario) {
        $errors = [];
        $mapping = $this->getFormMapping($tipoFormulario);
        
        // Validar campos requeridos de tabla principal
        foreach ($mapping['fields'] as $fieldName => $fieldConfig) {
            if (isset($fieldConfig['required']) && $fieldConfig['required']) {
                if (empty($data[$fieldName])) {
                    $errors[] = "Campo requerido faltante: $fieldName";
                }
            }
        }
        
        // Validar longitud de campos
        foreach ($data as $fieldName => $value) {
            if (isset($mapping['fields'][$fieldName]['max_length'])) {
                $maxLength = $mapping['fields'][$fieldName]['max_length'];
                if (strlen($value) > $maxLength) {
                    $errors[] = "Campo $fieldName excede longitud máxima de $maxLength caracteres";
                }
            }
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
    
    /**
     * Obtener archivos asociados a una consulta
     */
    public function getArchivosConsulta($idConsulta) {
        try {
            // Usar los campos JSON de la tabla consulta_informe_imagen
            $stmt = $this->pdo->prepare("
                SELECT archivos_od, archivos_oi 
                FROM consulta_informe_imagen 
                WHERE id_consulta = :id_consulta
            ");
            
            $stmt->execute([':id_consulta' => $idConsulta]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$result) {
                return [
                    'od' => [],
                    'oi' => [],
                    'general' => []
                ];
            }
            
            // Decodificar JSON
            $archivosOD = $result['archivos_od'] ? json_decode($result['archivos_od'], true) : [];
            $archivosOI = $result['archivos_oi'] ? json_decode($result['archivos_oi'], true) : [];
            
            // Asegurar que sean arrays
            if (!is_array($archivosOD)) $archivosOD = [];
            if (!is_array($archivosOI)) $archivosOI = [];
            
            return [
                'od' => $archivosOD,
                'oi' => $archivosOI,
                'general' => []
            ];
            
        } catch (Exception $e) {
            error_log("Error obteniendo archivos de consulta: " . $e->getMessage());
            return [
                'od' => [],
                'oi' => [],
                'general' => []
            ];
        }
    }
}

?>