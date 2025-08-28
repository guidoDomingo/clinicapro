<?php
// Debug version of the API endpoint with real database connection
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Start output buffering to catch any unexpected output
ob_start();

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Cargar configuración de base de datos
$connection_path = __DIR__ . '/../../../model/conexion.php';
error_log("Intentando cargar conexión desde: $connection_path");
error_log("¿Archivo existe? " . (file_exists($connection_path) ? 'SÍ' : 'NO'));

if (!file_exists($connection_path)) {
    // Intentar rutas alternativas
    $alt_paths = [
        __DIR__ . '/../../model/conexion.php',
        __DIR__ . '/../model/conexion.php',
        dirname(__DIR__, 3) . '/model/conexion.php',
    ];
    
    foreach ($alt_paths as $path) {
        error_log("Probando ruta alternativa: $path - " . (file_exists($path) ? 'EXISTE' : 'NO EXISTE'));
        if (file_exists($path)) {
            $connection_path = $path;
            break;
        }
    }
}

require_once $connection_path;

// Configurar conexión usando la clase Conexion que funciona
try {
    $pdo = Conexion::conectar();
    error_log("✅ Conexión establecida usando Conexion::conectar()");
} catch (PDOException $e) {
    error_log("Database connection error: " . $e->getMessage());
    $pdo = null;
}

// Function to search real patients in database
function buscarPacientesReales($pdo, $query) {
    if (!$pdo) {
        error_log("No database connection available for patient search");
        return [];
    }
    
    try {
        // Search in rh_person table (the real table structure)
        $sql = "SELECT person_id as id, 
                       CONCAT(first_name, ' ', last_name) as nombre, 
                       document_number as dni,
                       record_number as ficha
                FROM rh_person 
                WHERE is_active = true 
                AND (first_name ILIKE :query 
                     OR last_name ILIKE :query 
                     OR document_number ILIKE :query 
                     OR record_number ILIKE :query)
                ORDER BY first_name, last_name
                LIMIT 10";
        
        $searchPattern = '%' . $query . '%';
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['query' => $searchPattern]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $patients = [];
        foreach ($results as $patient) {
            $patients[] = [
                'id' => $patient['id'],
                'nombre' => trim($patient['nombre']),
                'dni' => $patient['dni'] ?? '',
                'ficha' => $patient['ficha'] ?? ''
            ];
        }
        
        return $patients;
        
    } catch (Exception $e) {
        error_log("Error searching patients: " . $e->getMessage());
        return [];
    }
}

// Function to load real patient data
function cargarDatosPacienteReal($pdo, $id) {
    if (!$pdo) {
        error_log("No database connection available for patient loading");
        return null;
    }
    
    try {
        // Load from rh_person table (the real table structure)
        $sql = "SELECT person_id as id,
                       first_name,
                       last_name,
                       document_number,
                       email,
                       phone_number as whatsapp,
                       record_number as ficha,
                       birth_date as fecha_nacimiento,
                       address,
                       gender
                FROM rh_person 
                WHERE person_id = :id AND is_active = true";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        $patient = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($patient) {
            // Format the patient data according to the expected structure
            $formattedPatient = [
                'id' => $patient['id'],
                'nombre' => trim($patient['first_name'] . ' ' . ($patient['last_name'] ?? '')),
                'documento' => $patient['document_number'] ?? '',
                'ficha' => $patient['ficha'] ?? 'F' . str_pad($id, 3, '0', STR_PAD_LEFT),
                'email' => $patient['email'] ?? '',
                'whatsapp' => $patient['whatsapp'] ?? '',
                'fecha_nacimiento' => $patient['fecha_nacimiento'] ?? '',
                'direccion' => $patient['address'] ?? '',
                'genero' => $patient['gender'] ?? ''
            ];
            
            return $formattedPatient;
        }
        
        return null; // Patient not found
        
    } catch (Exception $e) {
        error_log("Error loading patient: " . $e->getMessage());
        return null;
    }
}

// Load patient historical data (consultas, timeline, archivos, etc.)
function cargarDatosHistoricosPaciente($pdo, $patientId) {
    try {
        // DEBUG
        error_log("cargarDatosHistoricosPaciente called with ID: $patientId");
        
        $result = [
            'consultas' => 0,
            'cuota_mb' => 0,
            'historial' => [],
            'timeline' => [],
            'archivos' => []
        ];
        
        // Contar consultas del paciente
        $sql = "SELECT COUNT(*) as total FROM consultas WHERE id_persona = :patient_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['patient_id' => $patientId]);
        $result['consultas'] = (int) $stmt->fetchColumn();
        
        // DEBUG
        error_log("Consultas found: " . $result['consultas']);
        
        // Calcular cuota MB (ejemplo basado en archivos si existe la tabla)
        try {
            $sql = "SELECT COALESCE(SUM(CASE WHEN archivo_tamaño IS NOT NULL THEN archivo_tamaño ELSE 0 END), 0) as total_mb 
                    FROM consultas_archivos 
                    WHERE id_persona = :patient_id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['patient_id' => $patientId]);
            $totalBytes = $stmt->fetchColumn() ?: 0;
            $result['cuota_mb'] = round($totalBytes / (1024 * 1024), 2); // Convert to MB
        } catch (Exception $e) {
            // Tabla de archivos no existe, usar 0
            $result['cuota_mb'] = 0;
            error_log("Archivos table error: " . $e->getMessage());
        }
        
        // Cargar historial reciente (últimas 10 consultas)
        $sql = "SELECT 
                    c.id_consulta,
                    c.fecha_registro,
                    c.txtmotivo as motivo_consulta,
                    c.consulta_textarea as diagnostico,
                    c.receta_textarea as tratamiento,
                    c.tipo_formulario,
                    u.nombre as medico_nombre
                FROM consultas c
                LEFT JOIN usuarios u ON c.id_user = u.id_usuario
                WHERE c.id_persona = :patient_id 
                ORDER BY c.fecha_registro DESC 
                LIMIT 10";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['patient_id' => $patientId]);
        $result['historial'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Cargar timeline (actividades recientes de consultas)
        $sql = "SELECT 
                    'consulta' as tipo,
                    fecha_registro as fecha,
                    CONCAT('Consulta: ', COALESCE(LEFT(txtmotivo, 50), 'Sin motivo'), 
                           CASE WHEN LENGTH(txtmotivo) > 50 THEN '...' ELSE '' END) as descripcion,
                    id_consulta as referencia_id,
                    tipo_formulario
                FROM consultas 
                WHERE id_persona = :patient_id
                ORDER BY fecha_registro DESC 
                LIMIT 15";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['patient_id' => $patientId]);
        $result['timeline'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Intentar cargar archivos si la tabla existe
        try {
            $sql = "SELECT 
                        id,
                        nombre_archivo,
                        tipo_archivo,
                        archivo_tamaño,
                        fecha_subida,
                        descripcion
                    FROM consultas_archivos 
                    WHERE id_persona = :patient_id 
                    ORDER BY fecha_subida DESC 
                    LIMIT 20";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['patient_id' => $patientId]);
            $result['archivos'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            // Tabla de archivos no existe
            $result['archivos'] = [];
        }
        
        return $result;
        
    } catch (Exception $e) {
        error_log("Error loading patient history: " . $e->getMessage());
        return [
            'consultas' => 0,
            'cuota_mb' => 0,
            'historial' => [],
            'timeline' => [],
            'archivos' => []
        ];
    }
}

try {
    // Clear any previous output
    if (ob_get_level()) {
        ob_clean();
    }
    
    // Get raw input
    $rawInput = file_get_contents('php://input');
    
    // Log for debugging
    error_log("Livewire API - Raw input: " . $rawInput);
    error_log("Livewire API - Request method: " . $_SERVER['REQUEST_METHOD']);
    error_log("Livewire API - Content type: " . ($_SERVER['CONTENT_TYPE'] ?? 'not set'));
    
    // Parse JSON input
    $input = json_decode($rawInput, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('JSON decode error: ' . json_last_error_msg() . '. Raw input: ' . substr($rawInput, 0, 100));
    }
    
    if (!$input) {
        throw new Exception('Empty input after JSON decode. Raw input: ' . substr($rawInput, 0, 100));
    }
    
    $action = $input['action'] ?? '';
    $data = $input['data'] ?? [];
    
    error_log("Livewire API - Action: $action");
    error_log("Livewire API - Data: " . json_encode($data));
    
    // Simple response based on action
    $response = ['success' => false, 'data' => null, 'message' => ''];
    
    switch ($action) {
        case 'validateField':
            $property = $data['property'] ?? '';
            $value = $data['value'] ?? '';
            $formType = $data['formType'] ?? 'general';
            
            if ($property === 'search_nombre' && strlen($value) >= 2) {
                // Search real patients from database
                $patients = buscarPacientesReales($pdo, $value);
                
                $response = [
                    'success' => true,
                    'data' => [
                        'patients' => $patients,
                        'count' => count($patients),
                        'showSuggestions' => true
                    ],
                    'message' => 'Resultados de búsqueda'
                ];
            } elseif ($property === 'search_documento' && strlen($value) >= 3) {
                // Search by document number
                $patients = buscarPacientesReales($pdo, $value);
                
                $response = [
                    'success' => true,
                    'data' => [
                        'patients' => $patients,
                        'count' => count($patients),
                        'showSuggestions' => true
                    ],
                    'message' => 'Resultados de búsqueda por documento'
                ];
            } elseif ($property === 'search_ficha' && strlen($value) >= 1) {
                // Search by record number
                $patients = buscarPacientesReales($pdo, $value);
                
                $response = [
                    'success' => true,
                    'data' => [
                        'patients' => $patients,
                        'count' => count($patients),
                        'showSuggestions' => true
                    ],
                    'message' => 'Resultados de búsqueda por ficha'
                ];
            } else {
                $response = [
                    'success' => true,
                    'data' => ['valid' => true, 'showSuggestions' => false],
                    'message' => 'Campo válido'
                ];
            }
            break;
            
        case 'loadPatient':
            $id = $data['id'] ?? 0;
            
            // Load real patient data from database
            $patientData = cargarDatosPacienteReal($pdo, $id);
            
            if ($patientData) {
                $response = [
                    'success' => true,
                    'data' => $patientData,
                    'message' => 'Datos del paciente cargados exitosamente'
                ];
            } else {
                $response = [
                    'success' => false,
                    'data' => null,
                    'message' => 'Paciente no encontrado'
                ];
            }
            break;
            
        case 'searchPatients':
            $query = $data['query'] ?? '';
            $response = [
                'success' => true,
                'data' => ['patients' => []],
                'message' => 'Búsqueda completada (mock)'
            ];
            break;
            
        case 'loadPatientHistory':
            $id = $data['id'] ?? 0;
            
            // DEBUG: Log the request
            error_log("=== LOAD PATIENT HISTORY DEBUG ===");
            error_log("Patient ID: " . $id);
            
            // Load patient historical data
            $historyData = cargarDatosHistoricosPaciente($pdo, $id);
            
            // DEBUG: Log the result
            error_log("History data consultas: " . $historyData['consultas']);
            error_log("History data historial count: " . count($historyData['historial']));
            
            $response = [
                'success' => true,
                'data' => $historyData,
                'message' => 'Datos históricos del paciente cargados exitosamente',
                'debug' => [
                    'patient_id' => $id,
                    'consultas_count' => $historyData['consultas'],
                    'historial_count' => count($historyData['historial'])
                ]
            ];
            break;
            
        case 'save':
            $response = [
                'success' => true,
                'data' => ['id' => time()],
                'message' => 'Datos guardados exitosamente (mock)'
            ];
            break;
            
        case 'load':
            $response = [
                'success' => true,
                'data' => ['id' => $data['id'] ?? 0, 'loaded' => true],
                'message' => 'Datos cargados exitosamente (mock)'
            ];
            break;
            
        default:
            throw new Exception('Action not supported: ' . $action);
    }
    
    // Clear any output buffer before sending response
    if (ob_get_level()) {
        ob_clean();
    }
    
    echo json_encode($response);
    
} catch (Exception $e) {
    // Clear any output buffer
    if (ob_get_level()) {
        ob_clean();
    }
    
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => true,
        'message' => $e->getMessage(),
        'data' => null,
        'debug' => [
            'request_method' => $_SERVER['REQUEST_METHOD'] ?? 'unknown',
            'content_type' => $_SERVER['CONTENT_TYPE'] ?? 'not set',
            'raw_input_length' => strlen(file_get_contents('php://input'))
        ]
    ]);
}
?>