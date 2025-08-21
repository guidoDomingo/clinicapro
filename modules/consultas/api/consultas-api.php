<?php
/**
 * ENDPOINT PRINCIPAL PARA EL SISTEMA DE CONSULTAS REFACTORIZADO
 * 
 * Este archivo maneja todas las operaciones AJAX del nuevo sistema modular
 * Consolida y estandariza las respuestas para el frontend refactorizado
 * 
 * Rutas disponibles:
 * - GET /api/consultas - Obtener consultas
 * - POST /api/consultas - Crear consulta
 * - PUT /api/consultas/{id} - Actualizar consulta
 * - DELETE /api/consultas/{id} - Eliminar consulta
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Manejar preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Incluir archivos necesarios
require_once __DIR__ . '/../../../model/conexion.php';

// Obtener conexión a la base de datos
$conexion = Conexion::conectar();

// Inicializar variables de respuesta
$response = [
    'success' => false,
    'message' => '',
    'data' => null,
    'timestamp' => date('Y-m-d H:i:s')
];

try {
    // Obtener la acción solicitada
    $action = $_GET['action'] ?? $_POST['action'] ?? '';
    $method = $_SERVER['REQUEST_METHOD'];
    
    // Log de debug si está habilitado
    if (defined('DEBUG_MODE')) {
        error_log("Consultas API: {$method} - Action: {$action}");
    }
    
    // Verificar sesión de usuario
    session_start();
    if (!isset($_SESSION['user_id']) && $action !== 'verify_session') {
        throw new Exception('Sesión no válida', 401);
    }
    
    // Debug: Log de la acción recibida
    error_log("DEBUG: Action recibida: '$action'");
    error_log("DEBUG: POST data: " . json_encode($_POST));
    error_log("DEBUG: GET data: " . json_encode($_GET));
    
    // Router principal
    switch ($action) {
        
        // ================================
        // OPERACIONES DE PACIENTES
        // ================================
        
        case 'buscar_paciente':
            $response = buscarPaciente();
            break;
            
        case 'get_patient_info':
            $response = getPatientInfo();
            break;
            
        case 'get_patient_history':
            $response = getPatientHistory();
            break;
            
        // ================================
        // OPERACIONES DE CONSULTAS
        // ================================
        
        case 'guardar_consulta':
            $response = guardarConsulta();
            break;
            
        case 'get_consulta':
            $response = getConsulta();
            break;
            
        case 'update_consulta':
            $response = updateConsulta();
            break;
            
        case 'delete_consulta':
            $response = deleteConsulta();
            break;
            
        case 'get_consultas_list':
            $response = getConsultasList();
            break;
            
        // ================================
        // DATOS DE CONFIGURACIÓN
        // ================================
        
        case 'get_motivos_comunes':
        case 'getMotivosComunes':
            $tipoFormulario = $_GET['tipo_formulario'] ?? $_POST['tipo_formulario'] ?? 'general';
            $response = getMotivosComunes($tipoFormulario);
            break;
            
        case 'get_referenciales_anteojos':
            $tipo = $_GET['tipo'] ?? $_POST['tipo'] ?? null;
            $response = getReferencialesAnteojos($tipo);
            break;
            
        case 'get_equipos_medicos':
            $response = getEquiposMedicos();
            break;
            
        case 'get_preformatos_consulta':
        case 'get_preformatos_receta':
        case 'getPreformatos':
            $tipoFormulario = $_GET['tipo_formulario'] ?? $_POST['tipo_formulario'] ?? 'general';
            $userId = $_GET['usuario_id'] ?? $_POST['usuario_id'] ?? null;
            $tipoPreformato = $_GET['tipo'] ?? $_POST['tipo'] ?? null; // Nuevo filtro por tipo específico
            
            // DEBUG: Log de parámetros recibidos (solo si se necesita)
            if (isset($_GET['debug']) || isset($_POST['debug'])) {
                error_log("PREFORMATOS DEBUG - Action: " . $action);
                error_log("PREFORMATOS DEBUG - tipo_formulario: " . $tipoFormulario);
                error_log("PREFORMATOS DEBUG - usuario_id: " . ($userId ?? 'NULL'));
                error_log("PREFORMATOS DEBUG - tipo: " . ($tipoPreformato ?? 'NULL'));
            }
            
            $response = getPreformatosConsulta($tipoFormulario, $userId, $tipoPreformato);
            break;
            
        case 'get_preformato_content':
            error_log("DEBUG: Ejecutando get_preformato_content");
            $preformatoId = $_GET['preformato_id'] ?? $_POST['preformato_id'] ?? null;
            $userId = $_GET['usuario_id'] ?? $_POST['usuario_id'] ?? null;
            $tipoFormulario = $_GET['tipo_formulario'] ?? $_POST['tipo_formulario'] ?? 'general';
            
            error_log("DEBUG: Parámetros recibidos - preformatoId: $preformatoId, userId: $userId, tipoFormulario: $tipoFormulario");
            
            if (!$preformatoId) {
                throw new Exception('ID de preformato requerido');
            }
            
            $response = getPreformatoContent($preformatoId, $userId, $tipoFormulario);
            error_log("DEBUG: Respuesta generada: " . json_encode($response));
            break;
            
        case 'get_form_config':
            $response = getFormConfig();
            break;
            
        // ================================
        // GESTIÓN DE ARCHIVOS
        // ================================
        
        case 'upload_file':
            $response = uploadFile();
            break;
            
        case 'get_patient_files':
            $response = getPatientFiles();
            break;
            
        case 'delete_file':
            $response = deleteFile();
            break;
            
        // ================================
        // UTILIDADES
        // ================================
        
        case 'verify_session':
            $response = verifySession();
            break;
            
        case 'get_system_status':
            $response = getSystemStatus();
            break;
            
        default:
            throw new Exception('Acción no válida: ' . $action, 400);
    }
    
} catch (Exception $e) {
    $response = [
        'success' => false,
        'message' => $e->getMessage(),
        'error_code' => $e->getCode(),
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    // Log del error
    error_log("Consultas API Error: " . $e->getMessage());
    
    // Código de respuesta HTTP apropiado
    $httpCode = $e->getCode() ?: 500;
    http_response_code($httpCode);
}

// Enviar respuesta
echo json_encode($response, JSON_UNESCAPED_UNICODE);
exit;

// ================================
// FUNCIONES DE PACIENTES
// ================================

function buscarPaciente() {
    global $conexion; // Asume conexión a base de datos disponible
    
    $tipo = $_POST['tipo'] ?? '';
    $valor = trim($_POST['valor'] ?? '');
    
    if (empty($valor)) {
        throw new Exception('Valor de búsqueda requerido');
    }
    
    $pacientes = [];
    
    try {
        switch ($tipo) {
            case 'documento':
                $stmt = $conexion->prepare("SELECT * FROM personas WHERE ci = ? LIMIT 20");
                $stmt->execute([$valor]);
                break;
                
            case 'ficha':
                $stmt = $conexion->prepare("SELECT * FROM personas WHERE ficha = ? LIMIT 20");
                $stmt->execute([$valor]);
                break;
                
            case 'nombre':
                $valor = "%{$valor}%";
                $stmt = $conexion->prepare("SELECT * FROM personas WHERE nombres ILIKE ? OR apellidos ILIKE ? LIMIT 20");
                $stmt->execute([$valor, $valor]);
                break;
                
            default:
                throw new Exception('Tipo de búsqueda no válido');
        }
        
        $pacientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Enriquecer datos de pacientes
        foreach ($pacientes as &$paciente) {
            $paciente['nombre_completo'] = trim($paciente['nombres'] . ' ' . $paciente['apellidos']);
            $paciente['edad'] = calcularEdad($paciente['fecha_nacimiento']);
            
            // Obtener estadísticas básicas
            $stmtStats = $conexion->prepare("SELECT COUNT(*) as total_consultas FROM consultas WHERE id_persona = ?");
            $stmtStats->execute([$paciente['id']]);
            $stats = $stmtStats->fetch(PDO::FETCH_ASSOC);
            $paciente['total_consultas'] = $stats['total_consultas'];
        }
        
        return [
            'success' => true,
            'message' => count($pacientes) . ' pacientes encontrados',
            'data' => $pacientes
        ];
        
    } catch (PDOException $e) {
        throw new Exception('Error en la búsqueda: ' . $e->getMessage());
    }
}

function getPatientInfo() {
    global $conexion;
    
    $patientId = $_GET['patient_id'] ?? '';
    if (empty($patientId)) {
        throw new Exception('ID de paciente requerido');
    }
    
    try {
        $stmt = $conexion->prepare("
            SELECT p.*, 
                   COUNT(c.id) as total_consultas,
                   MAX(c.fecha) as ultima_consulta,
                   COALESCE(mb.valor, 0) as cuota_mb
            FROM personas p 
            LEFT JOIN consultas c ON p.id = c.id_persona 
            LEFT JOIN mutual_beneficiarios mb ON p.id = mb.id_persona
            WHERE p.id = ?
            GROUP BY p.id, mb.valor
        ");
        $stmt->execute([$patientId]);
        
        $paciente = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$paciente) {
            throw new Exception('Paciente no encontrado');
        }
        
        $paciente['edad'] = calcularEdad($paciente['fecha_nacimiento']);
        $paciente['nombre_completo'] = trim($paciente['nombres'] . ' ' . $paciente['apellidos']);
        
        return [
            'success' => true,
            'data' => $paciente
        ];
        
    } catch (PDOException $e) {
        throw new Exception('Error obteniendo información del paciente: ' . $e->getMessage());
    }
}

function getPatientHistory() {
    global $conexion;
    
    $patientId = $_GET['patient_id'] ?? '';
    if (empty($patientId)) {
        throw new Exception('ID de paciente requerido');
    }
    
    try {
        $stmt = $conexion->prepare("
            SELECT c.*, u.nombre as doctor_nombre,
                   CASE 
                       WHEN c.tipo_formulario IS NOT NULL THEN c.tipo_formulario
                       ELSE 'general'
                   END as form_type
            FROM consultas c
            LEFT JOIN usuarios u ON c.id_usuario = u.id
            WHERE c.id_persona = ?
            ORDER BY c.fecha DESC, c.id DESC
            LIMIT 50
        ");
        $stmt->execute([$patientId]);
        
        $consultas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Formatear fechas y datos adicionales
        foreach ($consultas as &$consulta) {
            $consulta['fecha_formato'] = date('d/m/Y H:i', strtotime($consulta['fecha']));
            $consulta['resumen'] = substr(strip_tags($consulta['consulta']), 0, 100) . '...';
        }
        
        return [
            'success' => true,
            'data' => $consultas
        ];
        
    } catch (PDOException $e) {
        throw new Exception('Error obteniendo historial: ' . $e->getMessage());
    }
}

// ================================
// FUNCIONES DE CONSULTAS
// ================================

function guardarConsulta() {
    global $conexion;
    
    // Configuración de tipos de formularios y sus tablas específicas
    $formConfig = [
        'general' => [
            'table' => null, // Solo tabla principal
            'fields' => []
        ],
        'anteojos' => [
            'table' => 'consulta_anteojos',
            'fields' => ['esfera_od', 'cilindro_od', 'eje_od', 'dnp_od', 'add_od', 'nota_od',
                        'esfera_oi', 'cilindro_oi', 'eje_oi', 'dnp_oi', 'add_oi', 'nota_oi',
                        'dist_interpupilar', 'altura_od', 'altura_oi']
        ],
        'estudios' => [
            'table' => 'consulta_estudios',
            'fields' => ['equipo_medico', 'otro_equipo', 'resultados', 'emails_compartir', 'compartir_activo']
        ],
        'informe_imagen' => [
            'table' => 'consulta_informe_imagen',
            'fields' => ['equipo_medico', 'descripcion_od', 'descripcion_oi', 'emails_compartir', 'compartir_activo']
        ]
    ];
    
    try {
        // Obtener y validar datos básicos
        $tipoFormulario = $_POST['tipo_formulario'] ?? 'general';
        $idPersona = $_POST['id_persona'] ?? null;
        $idUsuario = $_SESSION['user_id'] ?? null;
        
        // Validaciones básicas
        if (empty($idPersona)) {
            throw new Exception('Debe seleccionar un paciente');
        }
        
        if (empty($idUsuario)) {
            throw new Exception('Sesión de usuario no válida');
        }
        
        if (!isset($formConfig[$tipoFormulario])) {
            throw new Exception('Tipo de formulario no válido: ' . $tipoFormulario);
        }
        
        // Preparar datos para tabla principal con mapeo de campos
        $datosConsulta = [
            'id_persona' => $idPersona,
            'tipo_formulario' => $tipoFormulario,
            'id_user' => $idUsuario,
            
            // Mapear campos con nombres alternativos para compatibilidad
            'motivoscomunes' => $_POST['motivoscomunes'] ?? $_POST['motivo'] ?? '',
            'txtmotivo' => $_POST['txtmotivo'] ?? $_POST['motivo_personalizado'] ?? '',
            'consulta_textarea' => $_POST['consulta_textarea'] ?? $_POST['consulta'] ?? '',
            'receta_textarea' => $_POST['receta_textarea'] ?? $_POST['receta'] ?? '',
            'visionod' => $_POST['visionod'] ?? $_POST['vision_od'] ?? '',
            'visionoi' => $_POST['visionoi'] ?? $_POST['vision_oi'] ?? '',
            'tensionod' => $_POST['tensionod'] ?? $_POST['tension_od'] ?? '',
            'tensionoi' => $_POST['tensionoi'] ?? $_POST['tension_oi'] ?? '',
            'txtnota' => $_POST['txtnota'] ?? $_POST['notas'] ?? '',
            'proximaconsulta' => $_POST['proximaconsulta'] ?? $_POST['proxima_consulta'] ?? null,
            'whatsapptxt' => $_POST['whatsapptxt'] ?? $_POST['whatsapp'] ?? '',
            'email' => $_POST['email'] ?? ''
        ];
        
        // Iniciar transacción
        $conexion->beginTransaction();
        
        // === PASO 1: INSERTAR EN TABLA PRINCIPAL ===
        $sqlConsulta = "
            INSERT INTO consultas (
                id_persona, tipo_formulario, id_user,
                motivoscomunes, txtmotivo, consulta_textarea, receta_textarea,
                visionod, visionoi, tensionod, tensionoi, txtnota,
                proximaconsulta, whatsapptxt, email, fecha_registro
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ";
        
        $stmtConsulta = $conexion->prepare($sqlConsulta);
        $stmtConsulta->execute([
            $datosConsulta['id_persona'],
            $datosConsulta['tipo_formulario'], 
            $datosConsulta['id_user'],
            $datosConsulta['motivoscomunes'],
            $datosConsulta['txtmotivo'],
            $datosConsulta['consulta_textarea'],
            $datosConsulta['receta_textarea'],
            $datosConsulta['visionod'],
            $datosConsulta['visionoi'],
            $datosConsulta['tensionod'],
            $datosConsulta['tensionoi'],
            $datosConsulta['txtnota'],
            $datosConsulta['proximaconsulta'],
            $datosConsulta['whatsapptxt'],
            $datosConsulta['email']
        ]);
        
        $consultaId = $conexion->lastInsertId();
        
        // === PASO 2: INSERTAR DATOS ESPECÍFICOS SI ES NECESARIO ===
        if ($formConfig[$tipoFormulario]['table']) {
            $tableName = $formConfig[$tipoFormulario]['table'];
            $specificFields = $formConfig[$tipoFormulario]['fields'];
            
            // Preparar datos específicos
            $datosEspecificos = ['id_consulta' => $consultaId];
            foreach ($specificFields as $field) {
                $datosEspecificos[$field] = $_POST[$field] ?? null;
            }
            
            // Construir SQL dinámicamente
            $campos = array_keys($datosEspecificos);
            $placeholders = array_fill(0, count($campos), '?');
            
            $sqlEspecifico = "INSERT INTO {$tableName} (" . implode(', ', $campos) . ") VALUES (" . implode(', ', $placeholders) . ")";
            
            $stmtEspecifico = $conexion->prepare($sqlEspecifico);
            $stmtEspecifico->execute(array_values($datosEspecificos));
            
            error_log("DEBUG: Datos específicos guardados en {$tableName} para consulta {$consultaId}");
        }
        
        // === PASO 3: GUARDAR DATOS ADICIONALES EN JSONB (OPCIONAL) ===
        $datosAdicionales = [];
        foreach ($_POST as $key => $value) {
            // Guardar campos que no están en la estructura estándar
            if (!in_array($key, ['id_persona', 'tipo_formulario', 'id_user', 'action', 
                                'motivoscomunes', 'txtmotivo', 'consulta_textarea', 'receta_textarea',
                                'visionod', 'visionoi', 'tensionod', 'tensionoi', 'txtnota',
                                'proximaconsulta', 'whatsapptxt', 'email']) 
                && (!isset($formConfig[$tipoFormulario]['fields']) || 
                    !in_array($key, $formConfig[$tipoFormulario]['fields']))) {
                
                $datosAdicionales[$key] = $value;
            }
        }
        
        if (!empty($datosAdicionales)) {
            $sqlJsonb = "UPDATE consultas SET datos_especificos = ? WHERE id_consulta = ?";
            $stmtJsonb = $conexion->prepare($sqlJsonb);
            $stmtJsonb->execute([json_encode($datosAdicionales), $consultaId]);
            
            error_log("DEBUG: Datos adicionales guardados en JSONB: " . json_encode($datosAdicionales));
        }
        
        // Confirmar transacción
        $conexion->commit();
        
        // Registrar éxito
        error_log("SUCCESS: Consulta {$tipoFormulario} guardada con ID {$consultaId} para paciente {$idPersona}");
        
        return [
            'success' => true,
            'message' => 'Consulta guardada exitosamente',
            'data' => [
                'consulta_id' => $consultaId,
                'paciente_id' => $idPersona,
                'tipo_formulario' => $tipoFormulario,
                'tabla_especifica' => $formConfig[$tipoFormulario]['table'] ?? null
            ]
        ];
        
    } catch (PDOException $e) {
        $conexion->rollBack();
        error_log("ERROR SQL: " . $e->getMessage());
        throw new Exception('Error en base de datos: ' . $e->getMessage());
    } catch (Exception $e) {
        if ($conexion->inTransaction()) {
            $conexion->rollBack();
        }
        error_log("ERROR: " . $e->getMessage());
        throw new Exception($e->getMessage());
    }
}

// ================================
// FUNCIONES DE CONFIGURACIÓN
// ================================

function getMotivosComunes($tipoFormulario = 'general') {
    global $conexion;
    
    try {
        // Intentar consulta con filtro por tipo de formulario
        try {
            $stmt = $conexion->prepare("
                SELECT id_motivo as id, nombre, descripcion, activo
                FROM motivos_comunes 
                WHERE activo = true AND tipo_formulario = :tipo_formulario
                ORDER BY nombre ASC
            ");
            $stmt->bindParam(':tipo_formulario', $tipoFormulario, PDO::PARAM_STR);
            $stmt->execute();
            $motivos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Si no hay motivos para este tipo específico, obtener los generales
            if (empty($motivos) && $tipoFormulario !== 'general') {
                $stmt = $conexion->prepare("
                    SELECT id_motivo as id, nombre, descripcion, activo
                    FROM motivos_comunes 
                    WHERE activo = true AND tipo_formulario = 'general'
                    ORDER BY nombre ASC
                ");
                $stmt->execute();
                $motivos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            
        } catch (PDOException $e) {
            // Si la tabla no existe o hay problemas con tipo_formulario, usar consulta simple
            $stmt = $conexion->prepare("
                SELECT id, descripcion, 'general' as tipo_formulario
                FROM motivos_comunes 
                WHERE activo = true
                ORDER BY descripcion ASC
                LIMIT 10
            ");
            $stmt->execute();
            $motivos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        
        return [
            'success' => true,
            'data' => $motivos,
            'motivos' => $motivos, // Compatibilidad con frontend existente
            'tipo_formulario' => $tipoFormulario
        ];
        
    } catch (PDOException $e) {
        throw new Exception('Error obteniendo motivos comunes: ' . $e->getMessage());
    }
}

function getPreformatosConsulta($tipo_formulario = 'general', $userId = null, $tipo_preformato = null) {
    global $conexion;
    
    // DEBUG: Log de entrada
    error_log("=== GETPREFORMATOS DEBUG ===");
    error_log("Función llamada con: tipo_formulario=$tipo_formulario, userId=$userId, tipo_preformato=$tipo_preformato");
    
    try {
        try {
            // Si se proporciona userId, buscar preformatos del doctor asociado
            if ($userId) {
                error_log("DEBUG: Buscando con userId: $userId");
                
                // Construir la consulta base usando la estructura correcta de tablas
                $baseQuery = "
                    SELECT p.id_preformato as id, p.nombre, p.contenido, p.tipo as categoria 
                    FROM sys_users su 
                    INNER JOIN person_system_user psu ON su.user_id = psu.system_user_id 
                    INNER JOIN rh_person rp ON rp.person_id = psu.person_id 
                    INNER JOIN rh_doctors rd ON rd.person_id = rp.person_id 
                    INNER JOIN preformatos p ON p.creado_por = rd.doctor_id 
                    WHERE su.user_id = :user_id 
                      AND p.activo = true
                      AND (p.tipo_formulario = :tipo_formulario OR p.tipo_formulario = 'general')
                ";
                
                // Agregar filtro por tipo si se proporciona
                if ($tipo_preformato) {
                    $baseQuery .= " AND p.tipo = :tipo_preformato";
                    error_log("DEBUG: Agregando filtro por tipo: $tipo_preformato");
                }
                
                $baseQuery .= "
                    ORDER BY 
                      CASE WHEN p.tipo_formulario = :tipo_formulario THEN 0 ELSE 1 END,
                      p.nombre
                    LIMIT 20
                ";
                
                error_log("DEBUG: Query construida: " . str_replace([':user_id', ':tipo_formulario', ':tipo_preformato'], [$userId, $tipo_formulario, $tipo_preformato], $baseQuery));
                
                $stmt = $conexion->prepare($baseQuery);
                $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
                $stmt->bindParam(':tipo_formulario', $tipo_formulario, PDO::PARAM_STR);
                
                if ($tipo_preformato) {
                    $stmt->bindParam(':tipo_preformato', $tipo_preformato, PDO::PARAM_STR);
                }
                
                $stmt->execute();
                $preformatos = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                error_log("DEBUG: Encontrados " . count($preformatos) . " preformatos específicos del usuario");
                error_log("DEBUG: Preformatos encontrados: " . json_encode($preformatos));
                
                // Si no hay resultados específicos del usuario, buscar preformatos globales
                if (empty($preformatos)) {
                    error_log("DEBUG: Sin preformatos del usuario, buscando preformatos globales");
                    
                    $globalQuery = "
                        SELECT id_preformato as id, nombre, contenido, tipo as categoria 
                        FROM preformatos 
                        WHERE activo = true 
                          AND tipo_formulario = :tipo_formulario
                    ";
                    
                    // Agregar filtro por tipo si se proporciona
                    if ($tipo_preformato) {
                        $globalQuery .= " AND tipo = :tipo_preformato";
                    }
                    
                    $globalQuery .= " ORDER BY nombre LIMIT 20";
                    
                    error_log("DEBUG: Query global: " . str_replace([':tipo_formulario', ':tipo_preformato'], [$tipo_formulario, $tipo_preformato], $globalQuery));
                    
                    $stmt = $conexion->prepare($globalQuery);
                    $stmt->bindParam(':tipo_formulario', $tipo_formulario, PDO::PARAM_STR);
                    
                    if ($tipo_preformato) {
                        $stmt->bindParam(':tipo_preformato', $tipo_preformato, PDO::PARAM_STR);
                    }
                    
                    $stmt->execute();
                    $preformatos = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    error_log("DEBUG: Encontrados " . count($preformatos) . " preformatos globales");
                }
                
                // Si todavía no hay resultados específicos del usuario, buscar solo generales del usuario
                if (empty($preformatos) && $tipo_formulario !== 'general') {
                    error_log("DEBUG: Sin resultados específicos, buscando generales para userId: $userId");
                    
                    $fallbackQuery = "
                        SELECT p.id_preformato as id, p.nombre, p.contenido, p.tipo as categoria 
                        FROM sys_users su 
                        INNER JOIN person_system_user psu ON su.user_id = psu.system_user_id 
                        INNER JOIN rh_person rp ON rp.person_id = psu.person_id 
                        INNER JOIN rh_doctors rd ON rd.person_id = rp.person_id 
                        INNER JOIN preformatos p ON p.creado_por = rd.doctor_id 
                        WHERE su.user_id = :user_id 
                          AND p.activo = true
                          AND p.tipo_formulario = 'general'
                    ";
                    
                    // Agregar filtro por tipo en el fallback también
                    if ($tipo_preformato) {
                        $fallbackQuery .= " AND p.tipo = :tipo_preformato";
                    }
                    
                    $fallbackQuery .= " ORDER BY p.nombre LIMIT 20";
                    
                    $stmt = $conexion->prepare($fallbackQuery);
                    $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
                    
                    if ($tipo_preformato) {
                        $stmt->bindParam(':tipo_preformato', $tipo_preformato, PDO::PARAM_STR);
                    }
                    
                    $stmt->execute();
                    $preformatos = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    error_log("DEBUG: Encontrados " . count($preformatos) . " preformatos generales del usuario");
                }
            } else {
                error_log("DEBUG: Sin userId, buscando preformatos globales");
                
                // Si no hay userId, buscar preformatos globales (comportamiento anterior como fallback)
                $globalQuery = "
                    SELECT id_preformato as id, nombre, contenido, tipo as categoria 
                    FROM preformatos 
                    WHERE (activo = true OR activo IS NULL) 
                      AND (tipo_formulario = :tipo_formulario OR tipo_formulario = 'general')
                ";
                
                // Agregar filtro por tipo
                if ($tipo_preformato) {
                    $globalQuery .= " AND tipo = :tipo_preformato";
                    error_log("DEBUG: Agregando filtro global por tipo: $tipo_preformato");
                }
                
                $globalQuery .= "
                    ORDER BY 
                      CASE WHEN tipo_formulario = :tipo_formulario THEN 0 ELSE 1 END,
                      nombre
                    LIMIT 20
                ";
                
                error_log("DEBUG: Query global: " . str_replace([':tipo_formulario', ':tipo_preformato'], [$tipo_formulario, $tipo_preformato], $globalQuery));
                
                $stmt = $conexion->prepare($globalQuery);
                $stmt->bindParam(':tipo_formulario', $tipo_formulario, PDO::PARAM_STR);
                
                if ($tipo_preformato) {
                    $stmt->bindParam(':tipo_preformato', $tipo_preformato, PDO::PARAM_STR);
                }
                
                $stmt->execute();
                $preformatos = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                error_log("DEBUG: Encontrados " . count($preformatos) . " preformatos globales");
                
                // Si no hay resultados específicos, usar generales como fallback
                if (empty($preformatos) && $tipo_formulario !== 'general') {
                    error_log("DEBUG: Sin resultados específicos globales, buscando generales");
                    
                    $generalFallback = "
                        SELECT id_preformato as id, nombre, contenido, tipo as categoria 
                        FROM preformatos 
                        WHERE (activo = true OR activo IS NULL) 
                          AND (tipo_formulario = 'general' OR tipo_formulario IS NULL)
                    ";
                    
                    // Mantener el filtro por tipo en el fallback general
                    if ($tipo_preformato) {
                        $generalFallback .= " AND tipo = :tipo_preformato";
                    }
                    
                    $generalFallback .= " ORDER BY nombre LIMIT 20";
                    
                    $stmt = $conexion->prepare($generalFallback);
                    
                    if ($tipo_preformato) {
                        $stmt->bindParam(':tipo_preformato', $tipo_preformato, PDO::PARAM_STR);
                    }
                    
                    $stmt->execute();
                    $preformatos = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    error_log("DEBUG: Encontrados " . count($preformatos) . " preformatos generales fallback");
                }
            }
            
            // FALLBACK FINAL ESPECÍFICO PARA ANTEOJOS: Buscar cualquier preformato de anteojos en la base de datos
            if (empty($preformatos) && $tipo_formulario === 'anteojos') {
                error_log("DEBUG: Ejecutando fallback final para anteojos - buscar cualquier preformato de anteojos");
                
                $anteojosGlobalFallback = "
                    SELECT id_preformato as id, nombre, contenido, tipo as categoria 
                    FROM preformatos 
                    WHERE (activo = true OR activo IS NULL) 
                      AND (tipo_formulario = 'anteojos' OR tipo_formulario = 'Anteojos')
                ";
                
                if ($tipo_preformato) {
                    $anteojosGlobalFallback .= " AND tipo = :tipo_preformato";
                }
                
                $anteojosGlobalFallback .= " ORDER BY nombre LIMIT 20";
                
                error_log("DEBUG: Query fallback anteojos: " . $anteojosGlobalFallback);
                
                $stmt = $conexion->prepare($anteojosGlobalFallback);
                
                if ($tipo_preformato) {
                    $stmt->bindParam(':tipo_preformato', $tipo_preformato, PDO::PARAM_STR);
                }
                
                $stmt->execute();
                $preformatos = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                error_log("DEBUG: Fallback anteojos global encontró " . count($preformatos) . " preformatos");
            }
            
            // LOG de resultados finales
            error_log("DEBUG: Resultados finales: " . count($preformatos) . " preformatos");
            foreach ($preformatos as $p) {
                error_log("DEBUG: - ID: {$p['id']}, Nombre: {$p['nombre']}, Categoria: {$p['categoria']}");
            }
            
        } catch (PDOException $e) {
            // Si hay error de base de datos, devolver array vacío - NO datos ficticios
            $preformatos = [];
        }
        
        return [
            'success' => true,
            'data' => $preformatos,
            'preformatos' => $preformatos // Compatibilidad
        ];
        
    } catch (PDOException $e) {
        throw new Exception('Error obteniendo preformatos de consulta: ' . $e->getMessage());
    }
}

/**
 * Obtener el contenido específico de un preformato
 */
function getPreformatoContent($preformatoId, $userId = null, $tipoFormulario = 'general') {
    global $conexion;
    
    error_log("=== GET PREFORMATO CONTENT DEBUG ===");
    error_log("Función llamada con: preformatoId=$preformatoId, userId=$userId, tipoFormulario=$tipoFormulario");
    
    try {
        // Query para obtener el contenido del preformato específico
        $query = "
            SELECT p.id_preformato as id, p.nombre, p.contenido, p.tipo as categoria 
            FROM preformatos p 
            WHERE p.id_preformato = :preformato_id 
              AND (p.activo = true OR p.activo IS NULL)
        ";
        
        // Si se proporciona userId, verificar que el preformato pertenece al doctor o es general
        if ($userId) {
            $query = "
                SELECT p.id_preformato as id, p.nombre, p.contenido, p.tipo as categoria 
                FROM preformatos p 
                LEFT JOIN sys_users su ON su.user_id = :user_id
                LEFT JOIN person_system_user psu ON su.user_id = psu.system_user_id 
                LEFT JOIN rh_person rp ON rp.person_id = psu.person_id 
                LEFT JOIN rh_doctors rd ON rd.person_id = rp.person_id 
                WHERE p.id_preformato = :preformato_id 
                  AND (p.activo = true OR p.activo IS NULL)
                  AND (p.creado_por = rd.doctor_id OR p.creado_por IS NULL OR p.tipo_formulario = 'general')
            ";
        }
        
        error_log("DEBUG: Query construida: " . str_replace([':preformato_id', ':user_id'], [$preformatoId, $userId], $query));
        
        $stmt = $conexion->prepare($query);
        $stmt->bindParam(':preformato_id', $preformatoId, PDO::PARAM_INT);
        
        if ($userId) {
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        }
        
        $stmt->execute();
        $preformato = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$preformato) {
            error_log("DEBUG: No se encontró preformato con ID: $preformatoId");
            return [
                'success' => false,
                'message' => 'Preformato no encontrado',
                'contenido' => null
            ];
        }
        
        error_log("DEBUG: Preformato encontrado: ID={$preformato['id']}, Nombre={$preformato['nombre']}");
        error_log("DEBUG: Contenido length: " . strlen($preformato['contenido']));
        
        return [
            'success' => true,
            'contenido' => $preformato['contenido'],
            'nombre' => $preformato['nombre'],
            'categoria' => $preformato['categoria']
        ];
        
    } catch (PDOException $e) {
        error_log("ERROR: Error obteniendo contenido de preformato: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Error obteniendo contenido de preformato: ' . $e->getMessage(),
            'contenido' => null
        ];
    }
}

function getPreformatosReceta() {
    global $conexion;
    
    try {
        try {
            $stmt = $conexion->query("
                SELECT id, nombre, texto as contenido, categoria 
                FROM preformatos_receta 
                WHERE activo = true OR activo IS NULL
                ORDER BY nombre
                LIMIT 20
            ");
            $preformatos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // Si hay error de base de datos, devolver array vacío - NO datos ficticios
            $preformatos = [];
        }
        
        return [
            'success' => true,
            'data' => $preformatos,
            'preformatos' => $preformatos // Compatibilidad
        ];
        
    } catch (PDOException $e) {
        throw new Exception('Error obteniendo preformatos de receta: ' . $e->getMessage());
    }
}

// ================================
// FUNCIONES DE UTILIDAD
// ================================

function verifySession() {
    $valid = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    
    return [
        'success' => true,
        'valid' => $valid,
        'user_id' => $_SESSION['user_id'] ?? null,
        'username' => $_SESSION['username'] ?? null
    ];
}

function getSystemStatus() {
    return [
        'success' => true,
        'data' => [
            'version' => '2.0.0',
            'status' => 'operational',
            'timestamp' => date('Y-m-d H:i:s'),
            'server_time' => time(),
            'php_version' => PHP_VERSION,
            'memory_usage' => memory_get_usage(true),
            'peak_memory' => memory_get_peak_usage(true)
        ]
    ];
}

function calcularEdad($fechaNacimiento) {
    if (empty($fechaNacimiento)) return null;
    
    $fecha = new DateTime($fechaNacimiento);
    $hoy = new DateTime();
    $edad = $hoy->diff($fecha);
    
    return $edad->y;
}

/**
 * ============================================
 * FUNCIONES GENÉRICAS DE CONSULTAS - IMPLEMENTADAS
 * ============================================
 * 
 * El sistema ahora maneja automáticamente todos los tipos de formularios:
 * - general: Solo tabla principal
 * - anteojos: Tabla principal + consulta_anteojos  
 * - estudios: Tabla principal + consulta_estudios
 * - informe_imagen: Tabla principal + consulta_informe_imagen
 * 
 * Las funciones auxiliares específicas ya no son necesarias.
 * Todo se maneja dinámicamente según la configuración en $formConfig.
 * 
 * ✅ guardarConsulta() - Genérica para todos los tipos
 * ✅ updateConsulta() - Genérica para todos los tipos  
 * ✅ getConsulta() - Genérica para todos los tipos
 * ✅ deleteConsulta() - Genérica para todos los tipos
 */

function getConsulta() {
    global $conexion;
    
    // Configuración de tipos de formularios (misma que en otras funciones)
    $formConfig = [
        'general' => [
            'table' => null,
            'fields' => []
        ],
        'anteojos' => [
            'table' => 'consulta_anteojos',
            'fields' => ['esfera_od', 'cilindro_od', 'eje_od', 'dnp_od', 'add_od', 'nota_od',
                        'esfera_oi', 'cilindro_oi', 'eje_oi', 'dnp_oi', 'add_oi', 'nota_oi',
                        'dist_interpupilar', 'altura_od', 'altura_oi']
        ],
        'estudios' => [
            'table' => 'consulta_estudios',
            'fields' => ['equipo_medico', 'otro_equipo', 'resultados', 'emails_compartir', 'compartir_activo']
        ],
        'informe_imagen' => [
            'table' => 'consulta_informe_imagen',
            'fields' => ['equipo_medico', 'descripcion_od', 'descripcion_oi', 'emails_compartir', 'compartir_activo']
        ]
    ];
    
    try {
        $consultaId = $_GET['consulta_id'] ?? '';
        if (empty($consultaId)) {
            throw new Exception('ID de consulta requerido');
        }
        
        // === PASO 1: OBTENER DATOS DE TABLA PRINCIPAL ===
        $sqlPrincipal = "
            SELECT c.*, p.nombres, p.apellidos, p.documento, p.nro_ficha,
                   u.nombre as doctor_nombre, u.apellidos as doctor_apellidos
            FROM consultas c
            LEFT JOIN personas p ON c.id_persona = p.id_persona
            LEFT JOIN usuarios u ON c.id_user = u.id
            WHERE c.id_consulta = ?
        ";
        
        $stmtPrincipal = $conexion->prepare($sqlPrincipal);
        $stmtPrincipal->execute([$consultaId]);
        
        $consulta = $stmtPrincipal->fetch(PDO::FETCH_ASSOC);
        
        if (!$consulta) {
            throw new Exception('Consulta no encontrada');
        }
        
        $tipoFormulario = $consulta['tipo_formulario'] ?? 'general';
        
        // === PASO 2: OBTENER DATOS ESPECÍFICOS SI EXISTEN ===
        if ($formConfig[$tipoFormulario]['table']) {
            $tableName = $formConfig[$tipoFormulario]['table'];
            
            $sqlEspecifico = "SELECT * FROM {$tableName} WHERE id_consulta = ?";
            $stmtEspecifico = $conexion->prepare($sqlEspecifico);
            $stmtEspecifico->execute([$consultaId]);
            
            $datosEspecificos = $stmtEspecifico->fetch(PDO::FETCH_ASSOC);
            
            if ($datosEspecificos) {
                // Remover el id de la tabla específica del resultado
                unset($datosEspecificos['id_consulta']);
                unset($datosEspecificos['fecha_creacion']);
                unset($datosEspecificos['fecha_actualizacion']);
                
                // Fusionar datos específicos con los datos principales
                $consulta = array_merge($consulta, $datosEspecificos);
                
                error_log("DEBUG: Datos específicos cargados desde {$tableName} para consulta {$consultaId}");
            }
        }
        
        // === PASO 3: PROCESAR DATOS ADICIONALES DE JSONB ===
        if (!empty($consulta['datos_especificos'])) {
            $datosAdicionales = json_decode($consulta['datos_especificos'], true);
            if (is_array($datosAdicionales)) {
                $consulta = array_merge($consulta, $datosAdicionales);
                error_log("DEBUG: Datos adicionales cargados desde JSONB: " . json_encode($datosAdicionales));
            }
        }
        
        // === PASO 4: FORMATEAR Y MAPEAR DATOS PARA COMPATIBILIDAD ===
        // Crear aliases para compatibilidad con diferentes versiones del frontend
        $consulta['motivo'] = $consulta['motivoscomunes'] ?? $consulta['txtmotivo'] ?? '';
        $consulta['consulta'] = $consulta['consulta_textarea'] ?? '';
        $consulta['receta'] = $consulta['receta_textarea'] ?? '';
        $consulta['vision_od'] = $consulta['visionod'] ?? '';
        $consulta['vision_oi'] = $consulta['visionoi'] ?? '';
        $consulta['tension_od'] = $consulta['tensionod'] ?? '';
        $consulta['tension_oi'] = $consulta['tensionoi'] ?? '';
        $consulta['notas'] = $consulta['txtnota'] ?? '';
        $consulta['proxima_consulta'] = $consulta['proximaconsulta'] ?? '';
        $consulta['whatsapp'] = $consulta['whatsapptxt'] ?? '';
        
        // Formatear fechas
        if ($consulta['fecha_registro']) {
            $consulta['fecha_formato'] = date('d/m/Y H:i', strtotime($consulta['fecha_registro']));
        }
        if ($consulta['proximaconsulta']) {
            $consulta['proxima_consulta_formato'] = date('d/m/Y', strtotime($consulta['proximaconsulta']));
        }
        
        // Información del paciente
        $consulta['paciente_nombre_completo'] = trim(($consulta['nombres'] ?? '') . ' ' . ($consulta['apellidos'] ?? ''));
        
        // Información del doctor
        if ($consulta['doctor_nombre']) {
            $consulta['doctor_nombre_completo'] = trim($consulta['doctor_nombre'] . ' ' . ($consulta['doctor_apellidos'] ?? ''));
        }
        
        // === PASO 5: OBTENER ARCHIVOS ASOCIADOS (SI EXISTEN) ===
        $sqlArchivos = "SELECT * FROM archivos_consulta WHERE id_consulta = ? ORDER BY fecha_subida DESC";
        $stmtArchivos = $conexion->prepare($sqlArchivos);
        $stmtArchivos->execute([$consultaId]);
        $archivos = $stmtArchivos->fetchAll(PDO::FETCH_ASSOC);
        $consulta['archivos'] = $archivos;
        
        error_log("SUCCESS: Consulta {$tipoFormulario} cargada con ID {$consultaId}");
        
        return [
            'success' => true,
            'data' => $consulta,
            'metadata' => [
                'tipo_formulario' => $tipoFormulario,
                'tabla_especifica' => $formConfig[$tipoFormulario]['table'] ?? null,
                'tiene_datos_especificos' => isset($datosEspecificos) && !empty($datosEspecificos),
                'tiene_archivos' => count($archivos) > 0,
                'campos_disponibles' => array_keys($consulta)
            ]
        ];
        
    } catch (PDOException $e) {
        error_log("ERROR SQL: " . $e->getMessage());
        throw new Exception('Error en base de datos: ' . $e->getMessage());
    } catch (Exception $e) {
        error_log("ERROR: " . $e->getMessage());
        throw new Exception($e->getMessage());
    }
}

/**
        if ($consulta['fecha_registro']) {
            $consulta['fecha_formato'] = date('d/m/Y H:i', strtotime($consulta['fecha_registro']));
        }
        if ($consulta['proximaconsulta']) {
            $consulta['proxima_consulta_formato'] = date('d/m/Y', strtotime($consulta['proximaconsulta']));
        }
        
        // Información del paciente
        $consulta['paciente_nombre_completo'] = trim(($consulta['nombres'] ?? '') . ' ' . ($consulta['apellidos'] ?? ''));
        
        // Información del doctor
        if ($consulta['doctor_nombre']) {
            $consulta['doctor_nombre_completo'] = trim($consulta['doctor_nombre'] . ' ' . ($consulta['doctor_apellidos'] ?? ''));
        }
        
        // === PASO 5: OBTENER ARCHIVOS ASOCIADOS (SI EXISTEN) ===
        $sqlArchivos = "SELECT * FROM archivos_consulta WHERE id_consulta = ? ORDER BY fecha_subida DESC";
        $stmtArchivos = $conexion->prepare($sqlArchivos);
        $stmtArchivos->execute([$consultaId]);
        $archivos = $stmtArchivos->fetchAll(PDO::FETCH_ASSOC);
        $consulta['archivos'] = $archivos;
        
        error_log("SUCCESS: Consulta {$tipoFormulario} cargada con ID {$consultaId}");
        
        return [
            'success' => true,
            'data' => $consulta,
            'metadata' => [
                'tipo_formulario' => $tipoFormulario,
                'tabla_especifica' => $formConfig[$tipoFormulario]['table'] ?? null,
                'tiene_datos_especificos' => isset($datosEspecificos) && !empty($datosEspecificos),
                'tiene_archivos' => count($archivos) > 0,
                'campos_disponibles' => array_keys($consulta)
            ]
        ];
        
    } catch (PDOException $e) {
        error_log("ERROR SQL: " . $e->getMessage());
        throw new Exception('Error en base de datos: ' . $e->getMessage());
    } catch (Exception $e) {
        error_log("ERROR: " . $e->getMessage());
        throw new Exception($e->getMessage());
    }
}

function updateConsulta() {
    global $conexion;
    
    // Configuración de tipos de formularios (misma que en guardarConsulta)
    $formConfig = [
        'general' => [
            'table' => null,
            'fields' => []
        ],
        'anteojos' => [
            'table' => 'consulta_anteojos',
            'fields' => ['esfera_od', 'cilindro_od', 'eje_od', 'dnp_od', 'add_od', 'nota_od',
                        'esfera_oi', 'cilindro_oi', 'eje_oi', 'dnp_oi', 'add_oi', 'nota_oi',
                        'dist_interpupilar', 'altura_od', 'altura_oi']
        ],
        'estudios' => [
            'table' => 'consulta_estudios',
            'fields' => ['equipo_medico', 'otro_equipo', 'resultados', 'emails_compartir', 'compartir_activo']
        ],
        'informe_imagen' => [
            'table' => 'consulta_informe_imagen',
            'fields' => ['equipo_medico', 'descripcion_od', 'descripcion_oi', 'emails_compartir', 'compartir_activo']
        ]
    ];
    
    try {
        $consultaId = $_POST['consulta_id'] ?? $_GET['consulta_id'] ?? '';
        if (empty($consultaId)) {
            throw new Exception('ID de consulta requerido');
        }
        
        // Obtener tipo de formulario actual
        $stmtTipo = $conexion->prepare("SELECT tipo_formulario FROM consultas WHERE id_consulta = ?");
        $stmtTipo->execute([$consultaId]);
        $consultaActual = $stmtTipo->fetch(PDO::FETCH_ASSOC);
        
        if (!$consultaActual) {
            throw new Exception('Consulta no encontrada');
        }
        
        $tipoFormulario = $_POST['tipo_formulario'] ?? $consultaActual['tipo_formulario'];
        
        if (!isset($formConfig[$tipoFormulario])) {
            throw new Exception('Tipo de formulario no válido: ' . $tipoFormulario);
        }
        
        // Preparar datos para tabla principal
        $datosConsulta = [
            'tipo_formulario' => $tipoFormulario,
            'motivoscomunes' => $_POST['motivoscomunes'] ?? $_POST['motivo'] ?? '',
            'txtmotivo' => $_POST['txtmotivo'] ?? $_POST['motivo_personalizado'] ?? '',
            'consulta_textarea' => $_POST['consulta_textarea'] ?? $_POST['consulta'] ?? '',
            'receta_textarea' => $_POST['receta_textarea'] ?? $_POST['receta'] ?? '',
            'visionod' => $_POST['visionod'] ?? $_POST['vision_od'] ?? '',
            'visionoi' => $_POST['visionoi'] ?? $_POST['vision_oi'] ?? '',
            'tensionod' => $_POST['tensionod'] ?? $_POST['tension_od'] ?? '',
            'tensionoi' => $_POST['tensionoi'] ?? $_POST['tension_oi'] ?? '',
            'txtnota' => $_POST['txtnota'] ?? $_POST['notas'] ?? '',
            'proximaconsulta' => $_POST['proximaconsulta'] ?? $_POST['proxima_consulta'] ?? null,
            'whatsapptxt' => $_POST['whatsapptxt'] ?? $_POST['whatsapp'] ?? '',
            'email' => $_POST['email'] ?? ''
        ];
        
        // Iniciar transacción
        $conexion->beginTransaction();
        
        // === PASO 1: ACTUALIZAR TABLA PRINCIPAL ===
        $sqlConsulta = "
            UPDATE consultas SET 
                tipo_formulario = ?, motivoscomunes = ?, txtmotivo = ?, consulta_textarea = ?, 
                receta_textarea = ?, visionod = ?, visionoi = ?, tensionod = ?, tensionoi = ?, 
                txtnota = ?, proximaconsulta = ?, whatsapptxt = ?, email = ?, ultima_modificacion = NOW()
            WHERE id_consulta = ?
        ";
        
        $stmtConsulta = $conexion->prepare($sqlConsulta);
        $stmtConsulta->execute([
            $datosConsulta['tipo_formulario'],
            $datosConsulta['motivoscomunes'],
            $datosConsulta['txtmotivo'],
            $datosConsulta['consulta_textarea'],
            $datosConsulta['receta_textarea'],
            $datosConsulta['visionod'],
            $datosConsulta['visionoi'],
            $datosConsulta['tensionod'],
            $datosConsulta['tensionoi'],
            $datosConsulta['txtnota'],
            $datosConsulta['proximaconsulta'],
            $datosConsulta['whatsapptxt'],
            $datosConsulta['email'],
            $consultaId
        ]);
        
        // === PASO 2: ACTUALIZAR/INSERTAR DATOS ESPECÍFICOS ===
        if ($formConfig[$tipoFormulario]['table']) {
            $tableName = $formConfig[$tipoFormulario]['table'];
            $specificFields = $formConfig[$tipoFormulario]['fields'];
            
            // Verificar si ya existen datos específicos
            $checkStmt = $conexion->prepare("SELECT COUNT(*) FROM {$tableName} WHERE id_consulta = ?");
            $checkStmt->execute([$consultaId]);
            $exists = $checkStmt->fetchColumn() > 0;
            
            // Preparar datos específicos
            $datosEspecificos = [];
            foreach ($specificFields as $field) {
                $datosEspecificos[$field] = $_POST[$field] ?? null;
            }
            
            if ($exists) {
                // ACTUALIZAR datos existentes
                $setClauses = [];
                $values = [];
                foreach ($datosEspecificos as $field => $value) {
                    $setClauses[] = "{$field} = ?";
                    $values[] = $value;
                }
                $values[] = $consultaId; // WHERE id_consulta = ?
                
                $sqlUpdate = "UPDATE {$tableName} SET " . implode(', ', $setClauses) . " WHERE id_consulta = ?";
                $stmtUpdate = $conexion->prepare($sqlUpdate);
                $stmtUpdate->execute($values);
                
                error_log("DEBUG: Datos específicos actualizados en {$tableName} para consulta {$consultaId}");
            } else {
                // INSERTAR nuevos datos
                $datosEspecificos['id_consulta'] = $consultaId;
                $campos = array_keys($datosEspecificos);
                $placeholders = array_fill(0, count($campos), '?');
                
                $sqlInsert = "INSERT INTO {$tableName} (" . implode(', ', $campos) . ") VALUES (" . implode(', ', $placeholders) . ")";
                $stmtInsert = $conexion->prepare($sqlInsert);
                $stmtInsert->execute(array_values($datosEspecificos));
                
                error_log("DEBUG: Datos específicos insertados en {$tableName} para consulta {$consultaId}");
            }
        }
        
        // === PASO 3: ACTUALIZAR DATOS ADICIONALES EN JSONB ===
        $datosAdicionales = [];
        foreach ($_POST as $key => $value) {
            if (!in_array($key, ['consulta_id', 'tipo_formulario', 'action',
                                'motivoscomunes', 'txtmotivo', 'consulta_textarea', 'receta_textarea',
                                'visionod', 'visionoi', 'tensionod', 'tensionoi', 'txtnota',
                                'proximaconsulta', 'whatsapptxt', 'email']) 
                && (!isset($formConfig[$tipoFormulario]['fields']) || 
                    !in_array($key, $formConfig[$tipoFormulario]['fields']))) {
                
                $datosAdicionales[$key] = $value;
            }
        }
        
        if (!empty($datosAdicionales)) {
            $sqlJsonb = "UPDATE consultas SET datos_especificos = ? WHERE id_consulta = ?";
            $stmtJsonb = $conexion->prepare($sqlJsonb);
            $stmtJsonb->execute([json_encode($datosAdicionales), $consultaId]);
            
            error_log("DEBUG: Datos adicionales actualizados en JSONB: " . json_encode($datosAdicionales));
        }
        
        // Confirmar transacción
        $conexion->commit();
        
        error_log("SUCCESS: Consulta {$tipoFormulario} actualizada con ID {$consultaId}");
        
        return [
            'success' => true,
            'message' => 'Consulta actualizada exitosamente',
            'data' => [
                'consulta_id' => $consultaId,
                'tipo_formulario' => $tipoFormulario,
                'tabla_especifica' => $formConfig[$tipoFormulario]['table'] ?? null
            ]
        ];
        
    } catch (PDOException $e) {
        $conexion->rollBack();
        error_log("ERROR SQL: " . $e->getMessage());
        throw new Exception('Error en base de datos: ' . $e->getMessage());
    } catch (Exception $e) {
        if ($conexion->inTransaction()) {
            $conexion->rollBack();
        }
        error_log("ERROR: " . $e->getMessage());
        throw new Exception($e->getMessage());
    }
}

function deleteConsulta() {
    global $conexion;
    
    try {
        $consultaId = $_POST['consulta_id'] ?? $_GET['consulta_id'] ?? '';
        if (empty($consultaId)) {
            throw new Exception('ID de consulta requerido');
        }
        
        // Verificar que la consulta existe y obtener información
        $stmtCheck = $conexion->prepare("SELECT id_consulta, tipo_formulario, id_persona FROM consultas WHERE id_consulta = ?");
        $stmtCheck->execute([$consultaId]);
        $consulta = $stmtCheck->fetch(PDO::FETCH_ASSOC);
        
        if (!$consulta) {
            throw new Exception('Consulta no encontrada');
        }
        
        $tipoFormulario = $consulta['tipo_formulario'];
        $idPersona = $consulta['id_persona'];
        
        // Iniciar transacción
        $conexion->beginTransaction();
        
        // === PASO 1: ELIMINAR ARCHIVOS ASOCIADOS (SI EXISTEN) ===
        $stmtArchivos = $conexion->prepare("SELECT ruta_archivo FROM archivos_consulta WHERE id_consulta = ?");
        $stmtArchivos->execute([$consultaId]);
        $archivos = $stmtArchivos->fetchAll(PDO::FETCH_ASSOC);
        
        // Eliminar archivos físicos
        foreach ($archivos as $archivo) {
            if (!empty($archivo['ruta_archivo']) && file_exists($archivo['ruta_archivo'])) {
                unlink($archivo['ruta_archivo']);
                error_log("DEBUG: Archivo físico eliminado: " . $archivo['ruta_archivo']);
            }
        }
        
        // Eliminar registros de archivos
        $stmtDeleteArchivos = $conexion->prepare("DELETE FROM archivos_consulta WHERE id_consulta = ?");
        $stmtDeleteArchivos->execute([$consultaId]);
        
        // === PASO 2: LAS TABLAS ESPECÍFICAS SE ELIMINAN AUTOMÁTICAMENTE ===
        // Gracias a las claves foráneas ON DELETE CASCADE en:
        // - consulta_anteojos
        // - consulta_estudios  
        // - consulta_informe_imagen
        
        // === PASO 3: ELIMINAR CONSULTA PRINCIPAL ===
        $stmtDelete = $conexion->prepare("DELETE FROM consultas WHERE id_consulta = ?");
        $stmtDelete->execute([$consultaId]);
        
        if ($stmtDelete->rowCount() === 0) {
            throw new Exception('No se pudo eliminar la consulta');
        }
        
        // Confirmar transacción
        $conexion->commit();
        
        error_log("SUCCESS: Consulta {$tipoFormulario} eliminada con ID {$consultaId} (paciente {$idPersona})");
        
        return [
            'success' => true,
            'message' => 'Consulta eliminada exitosamente',
            'data' => [
                'consulta_id' => $consultaId,
                'tipo_formulario' => $tipoFormulario,
                'paciente_id' => $idPersona,
                'archivos_eliminados' => count($archivos)
            ]
        ];
        
    } catch (PDOException $e) {
        $conexion->rollBack();
        error_log("ERROR SQL: " . $e->getMessage());
        throw new Exception('Error en base de datos: ' . $e->getMessage());
    } catch (Exception $e) {
        if ($conexion->inTransaction()) {
            $conexion->rollBack();
        }
        error_log("ERROR: " . $e->getMessage());
        throw new Exception($e->getMessage());
    }
}

function getConsultasList() {
    global $conexion;
    
    try {
        $stmt = $conexion->query("
            SELECT c.*, p.nombres, p.apellidos, u.nombre as doctor_nombre
            FROM consultas c
            LEFT JOIN personas p ON c.id_persona = p.id
            LEFT JOIN usuarios u ON c.id_usuario = u.id
            ORDER BY c.fecha DESC
            LIMIT 100
        ");
        
        $consultas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return [
            'success' => true,
            'data' => $consultas
        ];
        
    } catch (PDOException $e) {
        throw new Exception('Error obteniendo lista de consultas: ' . $e->getMessage());
    }
}

function getFormConfig() {
    return [
        'success' => true,
        'data' => [
            'form_types' => [
                'general' => ['label' => 'General', 'icon' => 'notes-medical'],
                'anteojos' => ['label' => 'Anteojos', 'icon' => 'glasses'],
                'estudios' => ['label' => 'Estudios', 'icon' => 'x-ray'],
                'informe_imagen' => ['label' => 'Informe + Imagen', 'icon' => 'images']
            ],
            'default_form' => 'general'
        ]
    ];
}

function uploadFile() {
    if (!isset($_FILES['files'])) {
        throw new Exception('No se han enviado archivos');
    }
    
    $uploadDir = '../view/uploads/consultas/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    $uploadedFiles = [];
    $files = $_FILES['files'];
    
    // Manejar múltiples archivos
    if (is_array($files['name'])) {
        for ($i = 0; $i < count($files['name']); $i++) {
            if ($files['error'][$i] === UPLOAD_ERR_OK) {
                $fileName = uniqid() . '_' . $files['name'][$i];
                $uploadPath = $uploadDir . $fileName;
                
                if (move_uploaded_file($files['tmp_name'][$i], $uploadPath)) {
                    $uploadedFiles[] = [
                        'name' => $files['name'][$i],
                        'path' => $uploadPath,
                        'size' => $files['size'][$i]
                    ];
                }
            }
        }
    } else {
        // Un solo archivo
        if ($files['error'] === UPLOAD_ERR_OK) {
            $fileName = uniqid() . '_' . $files['name'];
            $uploadPath = $uploadDir . $fileName;
            
            if (move_uploaded_file($files['tmp_name'], $uploadPath)) {
                $uploadedFiles[] = [
                    'name' => $files['name'],
                    'path' => $uploadPath,
                    'size' => $files['size']
                ];
            }
        }
    }
    
    return [
        'success' => true,
        'message' => count($uploadedFiles) . ' archivos subidos',
        'data' => $uploadedFiles
    ];
}

function getPatientFiles() {
    global $conexion;
    
    $patientId = $_GET['patient_id'] ?? '';
    if (empty($patientId)) {
        throw new Exception('ID de paciente requerido');
    }
    
    try {
        $stmt = $conexion->prepare("
            SELECT * FROM consulta_archivos 
            WHERE id_persona = ? 
            ORDER BY fecha_subida DESC
        ");
        $stmt->execute([$patientId]);
        
        $archivos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return [
            'success' => true,
            'data' => $archivos
        ];
        
    } catch (PDOException $e) {
        throw new Exception('Error obteniendo archivos del paciente: ' . $e->getMessage());
    }
}

function deleteFile() {
    global $conexion;
    
    $fileId = $_POST['file_id'] ?? $_GET['file_id'] ?? '';
    if (empty($fileId)) {
        throw new Exception('ID de archivo requerido');
    }
    
    try {
        // Obtener información del archivo antes de eliminarlo
        $stmt = $conexion->prepare("SELECT ruta FROM consulta_archivos WHERE id = ?");
        $stmt->execute([$fileId]);
        $archivo = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($archivo) {
            // Eliminar archivo físico
            if (file_exists($archivo['ruta'])) {
                unlink($archivo['ruta']);
            }
            
            // Eliminar registro de la base de datos
            $stmt = $conexion->prepare("DELETE FROM consulta_archivos WHERE id = ?");
            $stmt->execute([$fileId]);
        }
        
        return [
            'success' => true,
            'message' => 'Archivo eliminado exitosamente'
        ];
        
    } catch (PDOException $e) {
        throw new Exception('Error eliminando archivo: ' . $e->getMessage());
    }
}

/**
 * Obtener referenciales para anteojos (esfera, cilindro, eje)
 */
function getReferencialesAnteojos($tipo = null) {
    global $conexion;
    
    try {
        // Definir valores estándar para cada tipo de referencial
        $referenciales = [];
        
        switch($tipo) {
            case 'esfera':
                // Valores de esfera de -20.00 a +20.00 en incrementos de 0.25
                for ($i = -20.00; $i <= 20.00; $i += 0.25) {
                    $valor = number_format($i, 2);
                    $referenciales[] = ['valor' => $valor];
                }
                break;
                
            case 'cilindro':
                // Valores de cilindro de -6.00 a +6.00 en incrementos de 0.25
                for ($i = -6.00; $i <= 6.00; $i += 0.25) {
                    $valor = number_format($i, 2);
                    $referenciales[] = ['valor' => $valor];
                }
                break;
                
            case 'eje':
                // Valores de eje de 0 a 180 en incrementos de 1
                for ($i = 0; $i <= 180; $i++) {
                    $referenciales[] = ['valor' => (string)$i];
                }
                break;
                
            case 'adicion':
                // Valores de adición de +0.50 a +4.00 en incrementos de 0.25
                for ($i = 0.50; $i <= 4.00; $i += 0.25) {
                    $valor = '+' . number_format($i, 2);
                    $referenciales[] = ['valor' => $valor];
                }
                break;
                
            default:
                // Si no se especifica tipo, devolver error
                return [
                    'success' => false,
                    'message' => 'Tipo de referencial no especificado o inválido'
                ];
        }
        
        return [
            'success' => true,
            'data' => $referenciales,
            'tipo' => $tipo,
            'count' => count($referenciales)
        ];
        
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => 'Error obteniendo referenciales: ' . $e->getMessage()
        ];
    }
}

/**
 * Obtener equipos médicos desde la tabla de referenciales
 * Usando la misma estructura que los referenciales de anteojos pero desde BD
 */
function getEquiposMedicos() {
    global $conexion;
    
    // Si la conexión global no está disponible, crear una directa
    if (!$conexion) {
        try {
            $conexion = new PDO('pgsql:host=localhost;port=5432;dbname=clinica', 'postgres', 'admin');
            $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Error de conexión a la base de datos: ' . $e->getMessage()
            ];
        }
    }
    
    try {
        // Buscar el referencial de equipos médicos
        $stmt = $conexion->prepare("
            SELECT r.id, r.codigo, r.nombre as nombre_referencial 
            FROM referenciales r 
            WHERE (r.codigo = 'equipos_medicos' OR r.codigo = 'estudios_medicos') 
            AND r.activo = 1
            LIMIT 1
        ");
        $stmt->execute();
        $referencial = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$referencial) {
            // Si no existe el referencial, devolver lista vacía
            return [
                'success' => true,
                'data' => [],
                'count' => 0,
                'message' => 'Referencial de equipos médicos no encontrado. Ejecuta poblar_equipos_medicos.php'
            ];
        }
        
        // Obtener los valores del referencial
        $stmt = $conexion->prepare("
            SELECT rv.id, rv.valor, rv.etiqueta as texto, rv.orden_visualizacion as orden, rv.activo, rv.descripcion
            FROM referencial_valores rv
            WHERE rv.referencial_id = :referencial_id 
            AND rv.activo = 1
            ORDER BY rv.orden_visualizacion ASC, rv.etiqueta ASC
        ");
        $stmt->bindParam(':referencial_id', $referencial['id'], PDO::PARAM_INT);
        $stmt->execute();
        $valores = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Formatear para compatibilidad con el frontend
        $equipos = array_map(function($valor) {
            return [
                'id' => $valor['id'],
                'codigo' => $valor['valor'],
                'nombre' => $valor['texto'],
                'valor' => $valor['valor'],
                'texto' => $valor['texto'],
                'descripcion' => $valor['descripcion'] ?? ''
            ];
        }, $valores);
        
        return [
            'success' => true,
            'data' => $equipos,
            'count' => count($equipos),
            'referencial_id' => $referencial['id'],
            'referencial_codigo' => $referencial['codigo']
        ];
        
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => 'Error obteniendo equipos médicos: ' . $e->getMessage()
        ];
    }
}

?>
