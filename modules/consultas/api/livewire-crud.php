<?php
/**
 * Endpoint para sistema CRUD inspirado en Livewire
 * Manejo eficiente de formularios reactivos
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Inicializar sesión si no está activa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar autenticación
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autenticado']);
    exit;
}

// Cargar configuración de base de datos
require_once '../../../config/config.php';
require_once '../../../controller/consultas.controller.php';
require_once '../../../model/personas.model.php';

class LivewireCRUDHandler {
    
    private $db;
    private $userId;
    private $consultasController;
    private $personasModel;
    
    public function __construct() {
        global $pdo;
        $this->db = $pdo;
        $this->userId = $_SESSION['user_id'];
        $this->consultasController = new ControllerConsulta();
        $this->personasModel = new ModelPersonas();
    }
    
    /**
     * Procesar petición principal
     */
    public function handleRequest() {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input || !isset($input['method'])) {
                throw new Exception('Método no especificado');
            }
            
            $method = $input['method'];
            $args = $input['args'] ?? [];
            $state = $input['state'] ?? [];
            $formType = $input['formType'] ?? 'general';
            
            // Validar método
            if (!method_exists($this, $method)) {
                throw new Exception("Método '{$method}' no existe");
            }
            
            // Ejecutar método
            $result = $this->$method($state, $formType, ...$args);
            
            echo json_encode([
                'success' => true,
                'data' => $result['data'] ?? null,
                'message' => $result['message'] ?? null,
                'errors' => $result['errors'] ?? null,
                'hooks' => $result['hooks'] ?? null
            ]);
            
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage(),
                'errors' => ['general' => [$e->getMessage()]]
            ]);
        }
    }
    
    /**
     * Guardar consulta (crear o actualizar)
     */
    public function save($state, $formType) {
        try {
            // Validar datos básicos
            $validation = $this->validateData($state, $formType);
            if (!$validation['valid']) {
                return [
                    'success' => false,
                    'errors' => $validation['errors'],
                    'message' => 'Datos de validación incompletos'
                ];
            }
            
            $this->db->beginTransaction();
            
            // Determinar si es creación o actualización
            $isUpdate = !empty($state['id_consulta']);
            
            if ($isUpdate) {
                $result = $this->updateConsulta($state, $formType);
                $message = 'Consulta actualizada exitosamente';
            } else {
                $result = $this->createConsulta($state, $formType);
                $message = 'Consulta creada exitosamente';
            }
            
            $this->db->commit();
            
            return [
                'data' => ['id_consulta' => $result['id_consulta']],
                'message' => $message,
                'hooks' => [
                    ['type' => 'emit', 'event' => 'consultaSaved', 'data' => $result]
                ]
            ];
            
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
    
    /**
     * Crear nueva consulta
     */
    private function createConsulta($state, $formType) {
        // Datos básicos de la consulta
        $consultaData = [
            'id_persona' => $state['id_persona'],
            'motivo' => $state['txtmotivo'],
            'vision_od' => $state['visionod'] ?? null,
            'vision_oi' => $state['visionoi'] ?? null,
            'tension_od' => $state['tensionod'] ?? null,
            'tension_oi' => $state['tensionoi'] ?? null,
            'consulta' => $state['consulta_textarea'] ?? null,
            'receta' => $state['receta_textarea'] ?? null,
            'nota' => $state['txtnota'] ?? null,
            'proxima_consulta' => $state['proximaconsulta'] ?? null,
            'whatsapp' => $state['whatsapptxt'] ?? null,
            'email' => $state['email'] ?? null,
            'id_usuario' => $this->userId,
            'fecha_consulta' => date('Y-m-d H:i:s'),
            'tipo_formulario' => $formType
        ];
        
        // Insertar consulta principal
        $sql = "INSERT INTO consultas (id_persona, motivo, vision_od, vision_oi, tension_od, tension_oi, 
                                     consulta, receta, nota, proxima_consulta, whatsapp, email, 
                                     id_usuario, fecha_consulta, tipo_formulario) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(array_values($consultaData));
        $idConsulta = $this->db->lastInsertId();
        
        // Guardar datos específicos del formulario
        switch ($formType) {
            case 'anteojos':
                $this->saveAnteojos($idConsulta, $state);
                break;
            case 'estudios':
                $this->saveEstudios($idConsulta, $state);
                break;
            case 'informe_imagen':
                $this->saveInformeImagen($idConsulta, $state);
                break;
        }
        
        return ['id_consulta' => $idConsulta];
    }
    
    /**
     * Actualizar consulta existente
     */
    private function updateConsulta($state, $formType) {
        $idConsulta = $state['id_consulta'];
        
        // Actualizar datos básicos
        $sql = "UPDATE consultas SET 
                    motivo = ?, vision_od = ?, vision_oi = ?, tension_od = ?, tension_oi = ?,
                    consulta = ?, receta = ?, nota = ?, proxima_consulta = ?, 
                    whatsapp = ?, email = ?, tipo_formulario = ?, 
                    ultima_modificacion = NOW()
                WHERE id_consulta = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $state['txtmotivo'],
            $state['visionod'] ?? null,
            $state['visionoi'] ?? null,
            $state['tensionod'] ?? null,
            $state['tensionoi'] ?? null,
            $state['consulta_textarea'] ?? null,
            $state['receta_textarea'] ?? null,
            $state['txtnota'] ?? null,
            $state['proximaconsulta'] ?? null,
            $state['whatsapptxt'] ?? null,
            $state['email'] ?? null,
            $formType,
            $idConsulta
        ]);
        
        // Actualizar datos específicos del formulario
        switch ($formType) {
            case 'anteojos':
                $this->updateAnteojos($idConsulta, $state);
                break;
            case 'estudios':
                $this->updateEstudios($idConsulta, $state);
                break;
            case 'informe_imagen':
                $this->updateInformeImagen($idConsulta, $state);
                break;
        }
        
        return ['id_consulta' => $idConsulta];
    }
    
    /**
     * Guardar datos específicos de anteojos
     */
    private function saveAnteojos($idConsulta, $state) {
        $anteojos = [
            'id_consulta' => $idConsulta,
            'od_esf' => $state['od_esf'] ?? null,
            'od_cil' => $state['od_cil'] ?? null,
            'od_eje' => $state['od_eje'] ?? null,
            'od_add' => $state['od_add'] ?? null,
            'od_av' => $state['od_av'] ?? null,
            'oi_esf' => $state['oi_esf'] ?? null,
            'oi_cil' => $state['oi_cil'] ?? null,
            'oi_eje' => $state['oi_eje'] ?? null,
            'oi_add' => $state['oi_add'] ?? null,
            'oi_av' => $state['oi_av'] ?? null,
            'observaciones' => $state['observaciones_anteojos'] ?? null
        ];
        
        $sql = "INSERT INTO consulta_anteojos (id_consulta, od_esf, od_cil, od_eje, od_add, od_av,
                                             oi_esf, oi_cil, oi_eje, oi_add, oi_av, observaciones)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(array_values($anteojos));
    }
    
    /**
     * Actualizar datos de anteojos
     */
    private function updateAnteojos($idConsulta, $state) {
        $sql = "UPDATE consulta_anteojos SET 
                    od_esf = ?, od_cil = ?, od_eje = ?, od_add = ?, od_av = ?,
                    oi_esf = ?, oi_cil = ?, oi_eje = ?, oi_add = ?, oi_av = ?,
                    observaciones = ?
                WHERE id_consulta = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $state['od_esf'] ?? null,
            $state['od_cil'] ?? null,
            $state['od_eje'] ?? null,
            $state['od_add'] ?? null,
            $state['od_av'] ?? null,
            $state['oi_esf'] ?? null,
            $state['oi_cil'] ?? null,
            $state['oi_eje'] ?? null,
            $state['oi_add'] ?? null,
            $state['oi_av'] ?? null,
            $state['observaciones_anteojos'] ?? null,
            $idConsulta
        ]);
    }
    
    /**
     * Guardar datos específicos de estudios
     */
    private function saveEstudios($idConsulta, $state) {
        $estudios = [
            'id_consulta' => $idConsulta,
            'tipo_estudio' => $state['tipo_estudio'] ?? null,
            'descripcion' => $state['descripcion_estudio'] ?? null,
            'resultado' => $state['resultado_estudio'] ?? null,
            'observaciones' => $state['observaciones_estudio'] ?? null
        ];
        
        $sql = "INSERT INTO consulta_estudios (id_consulta, tipo_estudio, descripcion, resultado, observaciones)
                VALUES (?, ?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(array_values($estudios));
    }
    
    /**
     * Actualizar datos de estudios
     */
    private function updateEstudios($idConsulta, $state) {
        $sql = "UPDATE consulta_estudios SET 
                    tipo_estudio = ?, descripcion = ?, resultado = ?, observaciones = ?
                WHERE id_consulta = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $state['tipo_estudio'] ?? null,
            $state['descripcion_estudio'] ?? null,
            $state['resultado_estudio'] ?? null,
            $state['observaciones_estudio'] ?? null,
            $idConsulta
        ]);
    }
    
    /**
     * Guardar datos específicos de informe e imagen
     */
    private function saveInformeImagen($idConsulta, $state) {
        $informe = [
            'id_consulta' => $idConsulta,
            'informe_texto' => $state['informe_texto'] ?? null,
            'observaciones' => $state['observaciones_informe'] ?? null
        ];
        
        $sql = "INSERT INTO consulta_informe_imagen (id_consulta, informe_texto, observaciones)
                VALUES (?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(array_values($informe));
    }
    
    /**
     * Actualizar datos de informe e imagen
     */
    private function updateInformeImagen($idConsulta, $state) {
        $sql = "UPDATE consulta_informe_imagen SET 
                    informe_texto = ?, observaciones = ?
                WHERE id_consulta = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $state['informe_texto'] ?? null,
            $state['observaciones_informe'] ?? null,
            $idConsulta
        ]);
    }
    
    /**
     * Cargar consulta para edición
     */
    public function loadConsulta($state, $formType, $idConsulta) {
        $sql = "SELECT c.*, p.nombre, p.apellido, p.documento 
                FROM consultas c 
                INNER JOIN personas p ON c.id_persona = p.id_persona 
                WHERE c.id_consulta = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$idConsulta]);
        $consulta = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$consulta) {
            throw new Exception('Consulta no encontrada');
        }
        
        // Cargar datos específicos según tipo de formulario
        $specificData = [];
        switch ($consulta['tipo_formulario'] ?? 'general') {
            case 'anteojos':
                $specificData = $this->loadAnteojos($idConsulta);
                break;
            case 'estudios':
                $specificData = $this->loadEstudios($idConsulta);
                break;
            case 'informe_imagen':
                $specificData = $this->loadInformeImagen($idConsulta);
                break;
        }
        
        // Mapear datos para el frontend
        $data = array_merge($consulta, $specificData);
        
        return [
            'data' => $this->mapToFrontend($data),
            'hooks' => [
                ['type' => 'emit', 'event' => 'consultaLoaded', 'data' => ['id' => $idConsulta]]
            ]
        ];
    }
    
    /**
     * Cargar datos específicos de anteojos
     */
    private function loadAnteojos($idConsulta) {
        $sql = "SELECT * FROM consulta_anteojos WHERE id_consulta = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$idConsulta]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?? [];
    }
    
    /**
     * Cargar datos específicos de estudios
     */
    private function loadEstudios($idConsulta) {
        $sql = "SELECT * FROM consulta_estudios WHERE id_consulta = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$idConsulta]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?? [];
    }
    
    /**
     * Cargar datos específicos de informe e imagen
     */
    private function loadInformeImagen($idConsulta) {
        $sql = "SELECT * FROM consulta_informe_imagen WHERE id_consulta = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$idConsulta]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?? [];
    }
    
    /**
     * Mapear datos de BD a formato frontend
     */
    private function mapToFrontend($data) {
        return [
            'id_consulta' => $data['id_consulta'] ?? null,
            'id_persona' => $data['id_persona'] ?? null,
            'txtmotivo' => $data['motivo'] ?? '',
            'visionod' => $data['vision_od'] ?? '',
            'visionoi' => $data['vision_oi'] ?? '',
            'tensionod' => $data['tension_od'] ?? '',
            'tensionoi' => $data['tension_oi'] ?? '',
            'consulta_textarea' => $data['consulta'] ?? '',
            'receta_textarea' => $data['receta'] ?? '',
            'txtnota' => $data['nota'] ?? '',
            'proximaconsulta' => $data['proxima_consulta'] ?? '',
            'whatsapptxt' => $data['whatsapp'] ?? '',
            'email' => $data['email'] ?? '',
            
            // Datos específicos de anteojos
            'od_esf' => $data['od_esf'] ?? '',
            'od_cil' => $data['od_cil'] ?? '',
            'od_eje' => $data['od_eje'] ?? '',
            'od_add' => $data['od_add'] ?? '',
            'od_av' => $data['od_av'] ?? '',
            'oi_esf' => $data['oi_esf'] ?? '',
            'oi_cil' => $data['oi_cil'] ?? '',
            'oi_eje' => $data['oi_eje'] ?? '',
            'oi_add' => $data['oi_add'] ?? '',
            'oi_av' => $data['oi_av'] ?? '',
            'observaciones_anteojos' => $data['observaciones'] ?? '',
            
            // Datos específicos de estudios
            'tipo_estudio' => $data['tipo_estudio'] ?? '',
            'descripcion_estudio' => $data['descripcion'] ?? '',
            'resultado_estudio' => $data['resultado'] ?? '',
            'observaciones_estudio' => $data['observaciones'] ?? '',
            
            // Datos específicos de informe e imagen
            'informe_texto' => $data['informe_texto'] ?? '',
            'observaciones_informe' => $data['observaciones'] ?? '',
            
            // Datos del paciente
            'paciente_nombre' => $data['nombre'] ?? '',
            'paciente_apellido' => $data['apellido'] ?? '',
            'paciente_documento' => $data['documento'] ?? ''
        ];
    }
    
    /**
     * Cambiar tipo de formulario
     */
    public function changeFormType($state, $formType, $newType) {
        return [
            'data' => ['formType' => $newType],
            'hooks' => [
                ['type' => 'emit', 'event' => 'formTypeChanged', 'data' => ['type' => $newType]]
            ]
        ];
    }
    
    /**
     * Buscar pacientes
     */
    public function searchPatients($state, $formType, $query) {
        $sql = "SELECT id_persona, nombre, apellido, documento, telefono 
                FROM personas 
                WHERE nombre ILIKE ? OR apellido ILIKE ? OR documento ILIKE ?
                ORDER BY nombre, apellido
                LIMIT 10";
        
        $searchTerm = "%{$query}%";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$searchTerm, $searchTerm, $searchTerm]);
        $patients = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return [
            'data' => ['patients' => $patients]
        ];
    }
    
    /**
     * Validar datos del formulario
     */
    private function validateData($state, $formType) {
        $errors = [];
        
        // Validaciones básicas
        if (empty($state['id_persona'])) {
            $errors['id_persona'] = ['Debe seleccionar un paciente'];
        }
        
        if (empty($state['txtmotivo']) || strlen(trim($state['txtmotivo'])) < 5) {
            $errors['txtmotivo'] = ['El motivo de consulta es requerido (mínimo 5 caracteres)'];
        }
        
        // Validaciones específicas por tipo de formulario
        switch ($formType) {
            case 'anteojos':
                if (empty($state['od_esf'])) {
                    $errors['od_esf'] = ['La esfera del ojo derecho es requerida'];
                }
                if (empty($state['oi_esf'])) {
                    $errors['oi_esf'] = ['La esfera del ojo izquierdo es requerida'];
                }
                break;
                
            case 'estudios':
                if (empty($state['tipo_estudio'])) {
                    $errors['tipo_estudio'] = ['El tipo de estudio es requerido'];
                }
                break;
                
            case 'informe_imagen':
                if (empty($state['informe_texto'])) {
                    $errors['informe_texto'] = ['El texto del informe es requerido'];
                }
                break;
        }
        
        return [
            'valid' => count($errors) === 0,
            'errors' => $errors
        ];
    }
    
    /**
     * Refrescar datos del componente
     */
    public function refresh($state, $formType) {
        return [
            'data' => $state,
            'message' => 'Componente actualizado'
        ];
    }
}

// Procesar petición
$handler = new LivewireCRUDHandler();
$handler->handleRequest();
?>