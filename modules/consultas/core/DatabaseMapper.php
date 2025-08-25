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
     * SISTEMA GENÉRICO Y ESTÁNDAR PARA TODOS LOS FORMULARIOS
     */
    private const FORM_MAPPING = [
        'general' => [
            'main_table' => 'consultas',
            'fields' => [
                // Campos básicos comunes a todos los formularios
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
                'fecha_registro' => ['type' => 'timestamp', 'auto_fill' => true],
                'ultima_modificacion' => ['type' => 'timestamp', 'auto_fill' => true],
                'tipo_formulario' => ['type' => 'varchar', 'max_length' => 50, 'default' => 'general'],
                'datos_especificos' => ['type' => 'jsonb', 'nullable' => true]
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
                        'dnp_od' => ['type' => 'varchar', 'max_length' => 50, 'form_id' => 'od_dnp'],
                        'add_od' => ['type' => 'varchar', 'max_length' => 50, 'form_id' => 'od_adicion'],
                        'nota_od' => ['type' => 'text', 'form_id' => 'od_nota'],
                        'esfera_oi' => ['type' => 'varchar', 'max_length' => 50, 'form_id' => 'oi_esf'],
                        'cilindro_oi' => ['type' => 'varchar', 'max_length' => 50, 'form_id' => 'oi_cil'],
                        'eje_oi' => ['type' => 'varchar', 'max_length' => 50, 'form_id' => 'oi_eje'],
                        'dnp_oi' => ['type' => 'varchar', 'max_length' => 50, 'form_id' => 'oi_dnp'],
                        'add_oi' => ['type' => 'varchar', 'max_length' => 50, 'form_id' => 'oi_adicion'],
                        'nota_oi' => ['type' => 'text', 'form_id' => 'oi_nota'],
                        'altura_od' => ['type' => 'varchar', 'max_length' => 50],
                        'altura_oi' => ['type' => 'varchar', 'max_length' => 50],
                        'dist_interpupilar' => ['type' => 'varchar', 'max_length' => 50],
                        'notas_generales' => ['type' => 'text']
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
                        'otro_equipo' => ['type' => 'varchar', 'max_length' => 100, 'form_id' => 'otro_tipo_estudio'],
                        'resultados' => ['type' => 'text', 'form_id' => 'observaciones'],
                        'emails_compartir' => ['type' => 'text', 'form_id' => 'txtEmailShare-estudios'],
                        'compartir_activo' => ['type' => 'boolean', 'default' => false, 'form_id' => 'compartir_estudios']
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
                        'equipo_medico' => ['type' => 'varchar', 'max_length' => 100, 'form_id' => 'equipoMedico-informe-imagen'],
                        'descripcion_od' => ['type' => 'text', 'form_id' => 'descripcion-od-textarea-informe-imagen'],
                        'descripcion_oi' => ['type' => 'text', 'form_id' => 'descripcion-oi-textarea-informe-imagen'],
                        'emails_compartir' => ['type' => 'text', 'form_id' => 'txtEmailShare-informe-imagen'],
                        'compartir_activo' => ['type' => 'boolean', 'default' => false, 'form_id' => 'compartir_informe'],
                        'archivos_od' => ['type' => 'jsonb', 'nullable' => true],
                        'archivos_oi' => ['type' => 'jsonb', 'nullable' => true]
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
     * Método público para obtener información de estructura de tablas
     */
    public function getTableStructure($tableName) {
        $stmt = $this->pdo->prepare("
            SELECT column_name, data_type, is_nullable, column_default 
            FROM information_schema.columns 
            WHERE table_name = ? 
            ORDER BY ordinal_position
        ");
        $stmt->execute([$tableName]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
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
    /**
     * MAPEO HTML GENÉRICO Y ESTANDARIZADO PARA TODOS LOS FORMULARIOS
     * Este método mapea automáticamente los IDs de HTML a las columnas de BD
     */
    public function getHtmlFieldMapping($formType) {
        $htmlMappings = [
            'general' => [
                // Campos principales de la tabla consultas (mapeo directo)
                'txtmotivo' => 'txtmotivo',
                'visionod' => 'visionod',
                'visionoi' => 'visionoi',
                'tensionod' => 'tensionod',
                'tensionoi' => 'tensionoi',
                'txtnota' => 'txtnota',
                'proximaconsulta' => 'proximaconsulta',
                'whatsapptxt' => 'whatsapptxt',
                'email' => 'email',
                'motivoscomunes' => 'motivoscomunes',
                // Textareas específicos (HTML usa guiones, BD usa guiones bajos)
                'consulta-textarea' => 'consulta_textarea',
                'receta-textarea' => 'receta_textarea'
            ],
            
            'anteojos' => [
                // Campos principales (heredados de la tabla consultas)
                'txtmotivo' => 'txtmotivo',
                'visionod' => 'visionod',
                'visionoi' => 'visionoi',
                'tensionod' => 'tensionod',
                'tensionoi' => 'tensionoi',
                'txtnota' => 'txtnota',
                'proximaconsulta' => 'proximaconsulta',
                'whatsapptxt' => 'whatsapptxt',
                'email' => 'email',
                'motivoscomunes' => 'motivoscomunes',
                'consulta-textarea' => 'consulta_textarea',
                'receta-textarea' => 'receta_textarea',
                // Campos específicos de anteojos (tabla relacionada)
                'od_esf' => 'esfera_od',
                'od_cil' => 'cilindro_od',
                'od_eje' => 'eje_od',
                'od_dnp' => 'dnp_od',
                'od_adicion' => 'add_od',
                'od_nota' => 'nota_od',
                'oi_esf' => 'esfera_oi',
                'oi_cil' => 'cilindro_oi',
                'oi_eje' => 'eje_oi',
                'oi_dnp' => 'dnp_oi',
                'oi_adicion' => 'add_oi',
                'oi_nota' => 'nota_oi',
                'altura_od' => 'altura_od',
                'altura_oi' => 'altura_oi',
                'dist_interpupilar' => 'dist_interpupilar',
                'notas_anteojos' => 'notas_generales'
            ],
            
            'estudios' => [
                // Campos principales (heredados)
                'txtmotivo' => 'txtmotivo',
                'visionod' => 'visionod',
                'visionoi' => 'visionoi',
                'tensionod' => 'tensionod',
                'tensionoi' => 'tensionoi',
                'txtnota' => 'txtnota',
                'proximaconsulta' => 'proximaconsulta',
                'whatsapptxt' => 'whatsapptxt',
                'email' => 'email',
                'motivoscomunes' => 'motivoscomunes',
                'consulta-textarea' => 'consulta_textarea',
                'receta-textarea' => 'receta_textarea',
                // Campos específicos de estudios
                'tipo_estudio' => 'equipo_medico',
                'otro_tipo_estudio' => 'otro_equipo',
                'observaciones' => 'resultados',
                'txtEmailShare-estudios' => 'emails_compartir',
                'compartir_estudios' => 'compartir_activo',
                'fecha_realizacion' => 'fecha_estudio',
                'notas_estudios' => 'notas_adicionales'
            ],
            
            'informe_imagen' => [
                // Campos principales (heredados)
                'txtmotivo' => 'txtmotivo',
                'txtmotivo-informe-imagen' => 'txtmotivo',
                'visionod' => 'visionod',
                'visionoi' => 'visionoi',
                'tensionod' => 'tensionod',
                'tensionoi' => 'tensionoi',
                'txtnota' => 'txtnota',
                'txtnota-informe-imagen' => 'txtnota',
                'proximaconsulta' => 'proximaconsulta',
                'proximaconsulta-informe-imagen' => 'proximaconsulta',
                'whatsapptxt' => 'whatsapptxt',
                'whatsapptxt-informe-imagen' => 'whatsapptxt',
                'email' => 'email',
                'email-informe-imagen' => 'email',
                'motivoscomunes' => 'motivoscomunes',
                'motivoscomunes-informe-imagen' => 'motivoscomunes',
                'consulta-textarea' => 'consulta_textarea',
                'consulta-textarea-informe-imagen' => 'consulta_textarea',
                'receta-textarea' => 'receta_textarea',
                'receta-textarea-informe-imagen' => 'receta_textarea',
                // Campos específicos de informe imagen
                'equipoMedico-informe-imagen' => 'equipo_medico',
                'descripcion-od-textarea-informe-imagen' => 'descripcion_od',
                'descripcion-oi-textarea-informe-imagen' => 'descripcion_oi',
                'txtEmailShare-informe-imagen' => 'emails_compartir',
                'compartir_informe' => 'compartir_activo',
                'formatoConsulta-informe-imagen' => 'formato_consulta_id',
                'notas_informe' => 'notas_adicionales'
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
            
            // Agregar mapeo HTML para el frontend
            $result['html_mapping'] = $this->getHtmlFieldMapping($tipoFormulario);
            
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
                error_log("✅ Consulta principal actualizada: ID $idConsultaResult");
            } else {
                // Crear nueva consulta
                $idConsultaResult = $this->insertMainRecord($mainData);
                error_log("✅ Consulta principal creada: ID $idConsultaResult");
            }
            
            // Manejar tablas relacionadas si existen
            if (isset($this->config[$tipoFormulario]['related_tables'])) {
                foreach ($this->config[$tipoFormulario]['related_tables'] as $tableName => $tableConfig) {
                    try {
                        $relatedData = $this->prepareRelatedTableData($data, $tableConfig, $idConsultaResult);
                        
                        if (!empty($relatedData)) {
                            $this->saveRelatedRecord($tableName, $relatedData, $idConsultaResult, $isUpdate);
                            error_log("✅ Tabla relacionada $tableName guardada");
                        }
                    } catch (Exception $e) {
                        echo "❌ Error en tabla relacionada $tableName: " . $e->getMessage() . "\n";
                        throw $e; // Re-lanzar para hacer rollback
                    }
                }
            }
            
            $this->pdo->commit();
            error_log("✅ Transacción confirmada exitosamente");
            
            return [
                'success' => true,
                'id_consulta' => $idConsultaResult,
                'message' => $isUpdate ? 'Consulta actualizada exitosamente' : 'Consulta creada exitosamente',
                'operation' => $isUpdate ? 'update' : 'create'
            ];
            
        } catch (Exception $e) {
            $this->pdo->rollBack();
            echo "🔄 Rollback ejecutado por error: " . $e->getMessage() . "\n";
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
    /**
     * MÉTODO GENÉRICO MEJORADO - Preparar datos para tabla principal
     * Funciona automáticamente con CUALQUIER tipo de formulario
     */
    private function prepareMainTableData($data, $tipoFormulario) {
        // TODOS los formularios usan la tabla 'consultas' como tabla principal
        $mainFields = $this->config['general']['fields'];
        
        $preparedData = [];
        
        // IMPORTANTE: Obtener mapeo HTML para convertir nombres de campos del frontend
        $htmlMapping = $this->getHtmlFieldMapping($tipoFormulario);
        
        // Log para debugging
        error_log("🔧 prepareMainTableData() para tipo: $tipoFormulario");
        error_log("📋 Campos disponibles en configuración: " . count($mainFields));
        error_log("🗺️ Mapeo HTML disponible: " . count($htmlMapping));
        
        foreach ($mainFields as $fieldName => $fieldConfig) {
            // Saltar campos auto-incrementales
            if (isset($fieldConfig['auto_increment']) && $fieldConfig['auto_increment']) {
                continue; 
            }
            
            $fieldValue = null;
            
            // 1. Buscar directamente por nombre de campo de BD
            if (isset($data[$fieldName])) {
                $fieldValue = $data[$fieldName];
                error_log("✅ Campo directo encontrado: $fieldName = " . substr($fieldValue, 0, 50));
            } 
            // 2. Buscar usando mapeo inverso HTML (consulta-textarea -> consulta_textarea)
            else {
                foreach ($htmlMapping as $htmlKey => $dbKey) {
                    if ($dbKey === $fieldName && isset($data[$htmlKey])) {
                        $fieldValue = $data[$htmlKey];
                        error_log("🔄 Campo mapeado encontrado: $htmlKey -> $fieldName = " . substr($fieldValue, 0, 50));
                        break;
                    }
                }
            }
            
            // 3. Usar valor por defecto si no se encontró
            if ($fieldValue !== null && $fieldValue !== '') {
                $preparedData[$fieldName] = $fieldValue;
            } elseif (isset($fieldConfig['default'])) {
                $preparedData[$fieldName] = $fieldConfig['default'];
                error_log("🔧 Usando valor por defecto para $fieldName: " . $fieldConfig['default']);
            }
        }
        
        // Campos automáticos del sistema
        $preparedData['tipo_formulario'] = $tipoFormulario;
        $preparedData['ultima_modificacion'] = date('Y-m-d H:i:s');
        
        // Si es creación nueva, agregar fecha_registro
        if (!isset($data['id_consulta'])) {
            $preparedData['fecha_registro'] = date('Y-m-d H:i:s');
        }
        
        error_log("📤 Datos preparados para tabla principal: " . count($preparedData) . " campos");
        
        return $preparedData;
    }
    
    /**
     * MÉTODO GENÉRICO MEJORADO - Preparar datos para tablas relacionadas
     * Funciona automáticamente con CUALQUIER tipo de formulario y tabla relacionada
     */
    private function prepareRelatedTableData($data, $tableConfig, $idConsulta) {
        $preparedData = ['id_consulta' => $idConsulta];
        $htmlMapping = $this->getHtmlFieldMapping($this->getCurrentFormType($data));
        
        error_log("🔧 prepareRelatedTableData() - Procesando tabla relacionada");
        error_log("📋 Campos disponibles en tabla relacionada: " . count($tableConfig['fields']));
        
        foreach ($tableConfig['fields'] as $fieldName => $fieldConfig) {
            // Saltar campos auto-incrementales y foreign keys (ya manejados)
            if (isset($fieldConfig['auto_increment']) && $fieldConfig['auto_increment']) {
                continue;
            }
            if (isset($fieldConfig['foreign_key']) && $fieldConfig['foreign_key']) {
                continue;
            }
            
            $fieldValue = null;
            
            // 1. Usar form_id si está definido en la configuración
            if (isset($fieldConfig['form_id'])) {
                $sourceFieldName = $fieldConfig['form_id'];
                if (isset($data[$sourceFieldName])) {
                    $fieldValue = $data[$sourceFieldName];
                    error_log("✅ Campo via form_id encontrado: {$sourceFieldName} -> {$fieldName} = " . substr($fieldValue, 0, 50));
                }
            }
            
            // 2. Buscar directamente por nombre de campo
            if ($fieldValue === null && isset($data[$fieldName])) {
                $fieldValue = $data[$fieldName];
                error_log("✅ Campo directo encontrado: {$fieldName} = " . substr($fieldValue, 0, 50));
            }
            
            // 3. Buscar usando mapeo HTML
            if ($fieldValue === null) {
                foreach ($htmlMapping as $htmlKey => $dbKey) {
                    if ($dbKey === $fieldName && isset($data[$htmlKey])) {
                        $fieldValue = $data[$htmlKey];
                        error_log("🔄 Campo via mapeo HTML encontrado: {$htmlKey} -> {$fieldName} = " . substr($fieldValue, 0, 50));
                        break;
                    }
                }
            }
            
            // Debug específico para compartir_activo
            if ($fieldName === 'compartir_activo') {
                error_log("🐛 DEBUG compartir_activo - fieldValue: '" . ($fieldValue ?? 'NULL') . "' (" . gettype($fieldValue) . ")");
                error_log("🐛 DEBUG compartir_activo - fieldConfig: " . json_encode($fieldConfig));
            }
            
            // 4. Aplicar valor encontrado o por defecto
            if ($fieldValue !== null && $fieldValue !== '') {
                // Para campos boolean, convertir valores
                if (isset($fieldConfig['type']) && $fieldConfig['type'] === 'boolean') {
                    $convertedValue = $this->convertToBoolean($fieldValue);
                    $preparedData[$fieldName] = $convertedValue;
                    error_log("🔧 Campo boolean convertido {$fieldName}: '{$fieldValue}' -> " . ($convertedValue ? 'true' : 'false'));
                } else {
                    $preparedData[$fieldName] = $fieldValue;
                }
            } elseif (isset($fieldConfig['default'])) {
                $preparedData[$fieldName] = $fieldConfig['default'];
                error_log("🔧 Usando valor por defecto para {$fieldName}: " . $fieldConfig['default']);
            } elseif (isset($fieldConfig['type']) && $fieldConfig['type'] === 'boolean') {
                // Para campos boolean sin valor, usar false por defecto
                $preparedData[$fieldName] = false;
                error_log("🔧 Usando valor por defecto boolean para {$fieldName}: false");
            } else {
                error_log("⚠️ Campo sin valor ni defecto: {$fieldName}");
            }
        }
        
        error_log("📤 Datos preparados para tabla relacionada: " . count($preparedData) . " campos");
        
        return $preparedData;
    }
    
    /**
     * Convertir valor a boolean compatible con PostgreSQL
     */
    private function convertToBoolean($value) {
        if (is_bool($value)) {
            return $value;
        }
        
        if (is_string($value)) {
            $value = strtolower(trim($value));
            return in_array($value, ['true', '1', 'yes', 'on', 'checked']);
        }
        
        return (bool)$value;
    }
    
    /**
     * Método auxiliar para obtener el tipo de formulario actual
     */
    private function getCurrentFormType($data) {
        return $data['tipo_formulario'] ?? 'general';
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
        // Convertir valores boolean para PostgreSQL ANTES de cualquier operación
        foreach ($data as $key => $value) {
            if (is_bool($value)) {
                $data[$key] = $value ? 't' : 'f';
            }
        }
        
        if ($isUpdate) {
            // Intentar actualizar primero - SOLO si hay campos para actualizar
            $updateFields = array_diff(array_keys($data), ['id_consulta']);
            
            if (empty($updateFields)) {
                // No hay campos para actualizar, solo el id_consulta
                error_log("⚠️ No hay campos para actualizar en $tableName, omitiendo UPDATE");
                return;
            }
            
            $setClause = implode(', ', array_map(fn($field) => "$field = :$field", $updateFields));
            $sql = "UPDATE $tableName SET $setClause WHERE id_consulta = :id_consulta";
            
            // Debug para UPDATE
            error_log("🔧 saveRelatedRecord() UPDATE - Datos para $tableName:");
            foreach ($data as $field => $value) {
                $type = gettype($value);
                error_log("   $field: '$value' ($type)");
            }
            
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
        // Convertir valores boolean para PostgreSQL
        foreach ($data as $key => $value) {
            if (is_bool($value)) {
                $data[$key] = $value ? 't' : 'f';
            }
        }
        
        $fields = array_keys($data);
        $placeholders = ':' . implode(', :', $fields);
        
        $sql = "INSERT INTO $tableName (" . implode(', ', $fields) . ") VALUES ($placeholders)";
        
        // Debug: mostrar datos que van a ser insertados
        error_log("🔧 insertRelatedRecord() - Datos para $tableName:");
        foreach ($data as $field => $value) {
            $type = gettype($value);
            $display = (string)$value;
            error_log("   $field: '$display' ($type)");
        }
        
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
    /**
     * VALIDACIÓN GENÉRICA Y MEJORADA DE DATOS
     * Funciona automáticamente para todos los tipos de formularios
     */
    public function validateData($data, $tipoFormulario) {
        $errors = [];
        
        // Obtener configuración del formulario
        $formConfig = $this->config[$tipoFormulario] ?? $this->config['general'];
        
        // Para todos los formularios, usar los campos de 'general' como base (tabla consultas)
        $mainFields = $this->config['general']['fields'];
        
        error_log("🔍 validateData() para tipo: $tipoFormulario");
        error_log("📋 Validando contra " . count($mainFields) . " campos de tabla principal (consultas)");
        
        // Solo validar campos realmente requeridos (muy pocos)
        $requiredFields = ['id_persona']; // Solo este campo es realmente obligatorio
        
        foreach ($requiredFields as $fieldName) {
            $fieldFound = false;
            
            // Buscar el campo requerido
            if (isset($data[$fieldName]) && !empty($data[$fieldName])) {
                $fieldFound = true;
            }
            
            if (!$fieldFound) {
                $errors[] = "Campo requerido faltante: $fieldName";
                error_log("❌ Campo requerido faltante: $fieldName");
            }
        }
        
        // Validación de longitud simplificada (solo para campos críticos)
        $maxLengths = [
            'txtmotivo' => 255,
            'whatsapptxt' => 50,
            'email' => 100
        ];
        
        foreach ($data as $fieldName => $value) {
            if (is_string($value) && isset($maxLengths[$fieldName])) {
                $maxLength = $maxLengths[$fieldName];
                if (strlen($value) > $maxLength) {
                    $errors[] = "Campo $fieldName excede longitud máxima de $maxLength caracteres";
                    error_log("⚠️ Campo $fieldName excede longitud máxima");
                }
            }
        }
        
        $isValid = empty($errors);
        error_log($isValid ? "✅ Validación exitosa" : "❌ Errores de validación: " . implode(', ', $errors));
        
        return [
            'valid' => $isValid,
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