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
            $response = getMotivosComunes();
            break;
            
        case 'get_preformatos_consulta':
        case 'get_preformatos_receta':
        case 'getPreformatos':
            $response = getPreformatosConsulta();
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
    
    // Obtener datos del POST
    $data = [
        'id_persona' => $_POST['id_persona'] ?? null,
        'motivo' => $_POST['motivo'] ?? '',
        'consulta' => $_POST['consulta'] ?? '',
        'receta' => $_POST['receta'] ?? '',
        'vision_od' => $_POST['vision_od'] ?? '',
        'vision_oi' => $_POST['vision_oi'] ?? '',
        'tension_od' => $_POST['tension_od'] ?? '',
        'tension_oi' => $_POST['tension_oi'] ?? '',
        'proxima_consulta' => $_POST['proxima_consulta'] ?? null,
        'whatsapp' => $_POST['whatsapp'] ?? '',
        'email' => $_POST['email'] ?? '',
        'tipo_formulario' => $_POST['tipo_formulario'] ?? 'general',
        'id_usuario' => $_SESSION['user_id']
    ];
    
    // Validaciones
    if (empty($data['id_persona'])) {
        throw new Exception('Debe seleccionar un paciente');
    }
    
    if (empty($data['motivo']) && empty($data['consulta'])) {
        throw new Exception('Debe completar al menos el motivo o la consulta');
    }
    
    try {
        $conexion->beginTransaction();
        
        // Insertar consulta principal
        $stmt = $conexion->prepare("
            INSERT INTO consultas (
                id_persona, motivo, consulta, receta, vision_od, vision_oi,
                tension_od, tension_oi, proxima_consulta, whatsapp, email,
                tipo_formulario, id_usuario, fecha
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        
        $stmt->execute([
            $data['id_persona'], $data['motivo'], $data['consulta'], $data['receta'],
            $data['vision_od'], $data['vision_oi'], $data['tension_od'], $data['tension_oi'],
            $data['proxima_consulta'], $data['whatsapp'], $data['email'],
            $data['tipo_formulario'], $data['id_usuario']
        ]);
        
        $consultaId = $conexion->lastInsertId();
        
        // Guardar datos específicos según el tipo de formulario
        switch ($data['tipo_formulario']) {
            case 'anteojos':
                guardarDatosAnteojos($consultaId, $_POST);
                break;
            case 'estudios':
                guardarDatosEstudios($consultaId, $_POST);
                break;
            case 'informe_imagen':
                guardarDatosInformeImagen($consultaId, $_POST);
                break;
        }
        
        $conexion->commit();
        
        return [
            'success' => true,
            'message' => 'Consulta guardada exitosamente',
            'data' => [
                'consulta_id' => $consultaId,
                'paciente_id' => $data['id_persona']
            ]
        ];
        
    } catch (PDOException $e) {
        $conexion->rollBack();
        throw new Exception('Error guardando consulta: ' . $e->getMessage());
    }
}

// ================================
// FUNCIONES DE CONFIGURACIÓN
// ================================

function getMotivosComunes() {
    global $conexion;
    
    try {
        // Intentar primero con una consulta simple para verificar la tabla
        try {
            $stmt = $conexion->query("
                SELECT descripcion 
                FROM motivos_comunes 
                LIMIT 10
            ");
            $motivos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // Si la tabla no existe, devolver motivos por defecto
            $motivos = [
                ['descripcion' => 'Consulta general'],
                ['descripcion' => 'Dolor de cabeza'],
                ['descripcion' => 'Problemas de visión'],
                ['descripcion' => 'Revisión rutinaria'],
                ['descripcion' => 'Seguimiento']
            ];
        }
        
        return [
            'success' => true,
            'data' => $motivos,
            'motivos' => $motivos // Compatibilidad con frontend existente
        ];
        
    } catch (PDOException $e) {
        throw new Exception('Error obteniendo motivos comunes: ' . $e->getMessage());
    }
}

function getPreformatosConsulta() {
    global $conexion;
    
    try {
        try {
            $stmt = $conexion->query("
                SELECT id, nombre, texto, categoria 
                FROM preformatos_consulta 
                WHERE activo = true OR activo IS NULL
                ORDER BY nombre
                LIMIT 20
            ");
            $preformatos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // Si la tabla no existe, devolver preformatos por defecto
            $preformatos = [
                [
                    'id' => 1,
                    'nombre' => 'Consulta General',
                    'texto' => 'Paciente presenta...',
                    'categoria' => 'general'
                ],
                [
                    'id' => 2,
                    'nombre' => 'Revisión Oftalmológica',
                    'texto' => 'Examen oftalmológico completo...',
                    'categoria' => 'oftalmologia'
                ]
            ];
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

function getPreformatosReceta() {
    global $conexion;
    
    try {
        try {
            $stmt = $conexion->query("
                SELECT id, nombre, texto, categoria 
                FROM preformatos_receta 
                WHERE activo = true OR activo IS NULL
                ORDER BY nombre
                LIMIT 20
            ");
            $preformatos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // Si la tabla no existe, devolver preformatos por defecto
            $preformatos = [
                [
                    'id' => 1,
                    'nombre' => 'Receta Básica',
                    'texto' => 'Se prescribe...',
                    'categoria' => 'general'
                ]
            ];
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

// Funciones auxiliares para tipos específicos de consulta
function guardarDatosAnteojos($consultaId, $data) {
    // Implementar según estructura de tabla consulta_anteojos
    // Esta función se expandirá según los campos específicos de anteojos
}

function guardarDatosEstudios($consultaId, $data) {
    // Implementar según estructura de tabla consulta_estudios
    // Esta función se expandirá según los campos específicos de estudios
}

function guardarDatosInformeImagen($consultaId, $data) {
    // Implementar según estructura de tabla consulta_informe_imagen
    // Esta función se expandirá según los campos específicos de informe+imagen
}

// ================================
// FUNCIONES FALTANTES
// ================================

function getConsulta() {
    global $conexion;
    
    $consultaId = $_GET['consulta_id'] ?? '';
    if (empty($consultaId)) {
        throw new Exception('ID de consulta requerido');
    }
    
    try {
        $stmt = $conexion->prepare("
            SELECT c.*, p.nombres, p.apellidos, u.nombre as doctor_nombre
            FROM consultas c
            LEFT JOIN personas p ON c.id_persona = p.id
            LEFT JOIN usuarios u ON c.id_usuario = u.id
            WHERE c.id = ?
        ");
        $stmt->execute([$consultaId]);
        
        $consulta = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$consulta) {
            throw new Exception('Consulta no encontrada');
        }
        
        return [
            'success' => true,
            'data' => $consulta
        ];
        
    } catch (PDOException $e) {
        throw new Exception('Error obteniendo consulta: ' . $e->getMessage());
    }
}

function updateConsulta() {
    global $conexion;
    
    $consultaId = $_POST['consulta_id'] ?? '';
    if (empty($consultaId)) {
        throw new Exception('ID de consulta requerido');
    }
    
    // Similar a guardarConsulta pero con UPDATE
    $data = [
        'motivo' => $_POST['motivo'] ?? '',
        'consulta' => $_POST['consulta'] ?? '',
        'receta' => $_POST['receta'] ?? '',
        'vision_od' => $_POST['vision_od'] ?? '',
        'vision_oi' => $_POST['vision_oi'] ?? '',
        'tension_od' => $_POST['tension_od'] ?? '',
        'tension_oi' => $_POST['tension_oi'] ?? '',
        'proxima_consulta' => $_POST['proxima_consulta'] ?? null,
        'whatsapp' => $_POST['whatsapp'] ?? '',
        'email' => $_POST['email'] ?? '',
    ];
    
    try {
        $stmt = $conexion->prepare("
            UPDATE consultas SET 
                motivo = ?, consulta = ?, receta = ?, vision_od = ?, vision_oi = ?,
                tension_od = ?, tension_oi = ?, proxima_consulta = ?, whatsapp = ?, email = ?
            WHERE id = ?
        ");
        
        $stmt->execute([
            $data['motivo'], $data['consulta'], $data['receta'],
            $data['vision_od'], $data['vision_oi'], $data['tension_od'], $data['tension_oi'],
            $data['proxima_consulta'], $data['whatsapp'], $data['email'], $consultaId
        ]);
        
        return [
            'success' => true,
            'message' => 'Consulta actualizada exitosamente',
            'data' => ['consulta_id' => $consultaId]
        ];
        
    } catch (PDOException $e) {
        throw new Exception('Error actualizando consulta: ' . $e->getMessage());
    }
}

function deleteConsulta() {
    global $conexion;
    
    $consultaId = $_POST['consulta_id'] ?? $_GET['consulta_id'] ?? '';
    if (empty($consultaId)) {
        throw new Exception('ID de consulta requerido');
    }
    
    try {
        $stmt = $conexion->prepare("DELETE FROM consultas WHERE id = ?");
        $stmt->execute([$consultaId]);
        
        return [
            'success' => true,
            'message' => 'Consulta eliminada exitosamente'
        ];
        
    } catch (PDOException $e) {
        throw new Exception('Error eliminando consulta: ' . $e->getMessage());
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

?>
