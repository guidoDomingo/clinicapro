<?php
// Configurar manejo de errores para AJAX
error_reporting(E_ALL);
ini_set('display_errors', 0); // No mostrar errores en pantalla
ini_set('log_errors', 1);
ini_set('error_log', '../logs/preformatos_ajax.log');

// Iniciar buffer de salida para capturar errores
ob_start();

// Configurar header JSON
header('Content-Type: application/json');

try {
    require_once "../controller/preformatos.controller.php";
    require_once "../model/conexion.php";
} catch (Exception $e) {
    ob_clean();
    echo json_encode([
        'status' => 'error',
        'message' => 'Error al cargar dependencias: ' . $e->getMessage()
    ]);
    exit;
}

class PreformatosAjax {
    /**
     * Obtiene todos los motivos comunes activos
     * @param string $tipo_formulario Tipo de formulario ('general', 'anteojos', etc.)
     */
    public function ajaxGetMotivosComunes($tipo_formulario = 'general') {
        try {
            $motivos = ControllerPreformatos::ctrGetMotivosComunes($tipo_formulario);
            echo json_encode([
                'status' => 'success',
                'data' => $motivos
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Error al obtener motivos comunes: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Obtiene todos los preformatos activos de un tipo específico
     * @param string $tipo Tipo de preformato ('consulta', 'receta', etc.)
     * @param integer $userId ID del usuario conectado (opcional)
     * @param string $tipoFormulario Tipo de formulario ('general', 'anteojos', etc.)
     */
    public function ajaxGetPreformatos($tipo, $userId = null, $tipoFormulario = 'general') {
        // Registrar información para depuración
        error_log("ajaxGetPreformatos - Tipo: " . $tipo . ", User ID: " . ($userId ? $userId : 'ninguno') . ", Tipo Formulario: " . $tipoFormulario);
        
        // Si se proporciona un ID de usuario, buscar sus preformatos usando la relación correcta
        if ($userId) {
            try {
                // Conectar a la base de datos
                $db = Conexion::conectar();
                
                // Consulta para obtener los preformatos del doctor asociado al usuario
                $sql = "SELECT 
                            p.*
                        FROM person_system_user psu 
                        INNER JOIN rh_doctors rd 
                        ON psu.person_id = rd.person_id 
                        INNER JOIN preformatos p 
                        ON p.creado_por = rd.doctor_id 
                        WHERE psu.system_user_id = :user_id 
                        AND p.activo = true
                        AND p.tipo_formulario = :tipo_formulario
                        ORDER BY p.nombre ASC";
                
                // Log SQL query to database.log for debugging
                file_put_contents('logs/database.log', 
                    date('[Y-m-d H:i:s] ') . 
                    "SQL para obtener preformatos (user): " . $sql . 
                    " | Tipo: " . $tipo . 
                    " | User ID: " . $userId . 
                    " | Tipo Formulario: " . $tipoFormulario . 
                    PHP_EOL, 
                    FILE_APPEND);
                
                $stmt = $db->prepare($sql);
                $stmt->bindParam(":user_id", $userId, PDO::PARAM_INT);
                $stmt->bindParam(":tipo_formulario", $tipoFormulario, PDO::PARAM_STR);
                $stmt->execute();
                
                $preformatos = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Registrar información de depuración
                file_put_contents('logs/database.log', 
                    date('[Y-m-d H:i:s] ') . 
                    "Preformatos encontrados (user): " . count($preformatos) . 
                    " | Tipo: " . $tipo . 
                    " | User ID: " . $userId . 
                    " | Tipo Formulario: " . $tipoFormulario . 
                    PHP_EOL, 
                    FILE_APPEND);
                
                echo json_encode([
                    'status' => 'success',
                    'data' => $preformatos
                ]);
                return;
            } catch (PDOException $e) {
                // Log error to database.log
                file_put_contents('logs/database.log', 
                    date('[Y-m-d H:i:s] ') . 
                    "ERROR al obtener preformatos por usuario: " . $e->getMessage() . 
                    " | Tipo: " . $tipo . 
                    " | User ID: " . $userId . 
                    " | Tipo Formulario: " . $tipoFormulario . 
                    PHP_EOL, 
                    FILE_APPEND);
                
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Error al obtener preformatos: ' . $e->getMessage()
                ]);
                return;
            }
        }
        
        // Si no se proporciona usuario_id, usar el método estándar para obtener todos los preformatos del tipo
        try {
            // Conectar a la base de datos
            $db = Conexion::conectar();
            
            $sql = "SELECT 
                    p.*
                FROM preformatos p
                WHERE p.activo = true 
                AND p.tipo_formulario = :tipo_formulario
                ORDER BY p.nombre ASC";
                
            // Log SQL query to database.log for debugging
            file_put_contents('logs/database.log', 
                date('[Y-m-d H:i:s] ') . 
                "SQL para obtener preformatos (sin user): " . $sql . 
                " | Tipo: " . $tipo . 
                " | Tipo Formulario: " . $tipoFormulario . 
                PHP_EOL, 
                FILE_APPEND);
            
            $stmt = $db->prepare($sql);
            $stmt->bindParam(":tipo_formulario", $tipoFormulario, PDO::PARAM_STR);
            $stmt->execute();
            
            $preformatos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Log results to database.log
            file_put_contents('logs/database.log', 
                date('[Y-m-d H:i:s] ') . 
                "Preformatos encontrados (sin user): " . count($preformatos) . 
                " | Tipo: " . $tipo . 
                " | Tipo Formulario: " . $tipoFormulario . 
                PHP_EOL, 
                FILE_APPEND);
            
            echo json_encode([
                'status' => 'success',
                'data' => $preformatos
            ]);
        } catch (PDOException $e) {
            // Log error to database.log
            file_put_contents('logs/database.log', 
                date('[Y-m-d H:i:s] ') . 
                "ERROR al obtener preformatos (sin user): " . $e->getMessage() . 
                " | Tipo: " . $tipo . 
                " | Tipo Formulario: " . $tipoFormulario . 
                PHP_EOL, 
                FILE_APPEND);
                
            echo json_encode([
                'status' => 'error',
                'message' => 'Error al obtener preformatos: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Obtiene todos los preformatos con opciones de filtrado
     * @param array $filtros Filtros a aplicar (tipo, propietario, título)
     */
    public function ajaxGetAllPreformatos($filtros = []) {
        $preformatos = ControllerPreformatos::ctrGetAllPreformatos($filtros);
        // Añadir información de diagnóstico
        echo json_encode([
            'status' => 'success',
            'data' => $preformatos,
            'debug_info' => [
                'filtros_aplicados' => $filtros,
                'total_registros' => count($preformatos)
            ]
        ]);
    }
    
    /**
     * Obtiene la lista de usuarios para el selector de propietarios
     */
    public function ajaxGetUsuarios() {
        $usuarios = ControllerPreformatos::ctrGetUsuarios();
        echo json_encode([
            'status' => 'success',
            'data' => $usuarios
        ]);
    }
    
    /**
     * Obtiene un preformato por su ID
     * @param int $idPreformato ID del preformato a obtener
     */
    public function ajaxGetPreformatoById($idPreformato) {
        $preformato = ControllerPreformatos::ctrGetPreformatoById($idPreformato);
        
        if ($preformato) {
            echo json_encode([
                'status' => 'success',
                'data' => $preformato
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'No se pudo obtener la información del preformato'
            ]);
        }
    }
    
    /**
     * Crea un nuevo preformato
     * @param array $datos Datos del preformato
     */
    public function ajaxCrearPreformato($datos) {
        $resultado = ControllerPreformatos::ctrCrearPreformato($datos);
        
        if ($resultado === "ok") {
            echo json_encode([
                'status' => 'success',
                'message' => 'Preformato creado correctamente'
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Error al crear el preformato'
            ]);
        }
    }
    
    /**
     * Actualiza un preformato existente
     * @param array $datos Datos del preformato
     */
    public function ajaxActualizarPreformato($datos) {
        $resultado = ControllerPreformatos::ctrActualizarPreformato($datos);
        
        if ($resultado === "ok") {
            echo json_encode([
                'status' => 'success',
                'message' => 'Preformato actualizado correctamente'
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Error al actualizar el preformato'
            ]);
        }
    }
    
    /**
     * Elimina un preformato
     * @param int $idPreformato ID del preformato a eliminar
     */
    public function ajaxEliminarPreformato($idPreformato) {
        $resultado = ControllerPreformatos::ctrEliminarPreformato($idPreformato);
        
        if ($resultado === "ok") {
            echo json_encode([
                'status' => 'success',
                'message' => 'Preformato eliminado correctamente'
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Error al eliminar el preformato'
            ]);
        }
    }
    
    /**
     * Obtiene los datos de un doctor por su ID de usuario
     * @param int $userId ID del usuario
     */
    public function ajaxGetDoctorByUserId($userId) {
        try {
            // Registrar la operación para depuración
            error_log("Buscando doctor para el usuario ID: " . $userId);
            
            // Conectar a la base de datos
            $db = Conexion::conectar();
            
            // Consultar los datos utilizando las relaciones más directas
            $stmt = $db->prepare(
                "SELECT 
                    d.doctor_id,
                    d.person_id,
                    rp.first_name,
                    rp.last_name,
                    b.business_name,
                    b.business_id,
                    CONCAT(rp.last_name, ', ', rp.first_name) as nombre_completo
                FROM person_system_user psu 
                JOIN rh_person rp ON psu.person_id = rp.person_id
                JOIN rh_doctors d ON rp.person_id = d.person_id
                LEFT JOIN sys_business b ON d.business_id = b.business_id
                WHERE psu.system_user_id = :user_id
                LIMIT 1"
            );
            
            $stmt->bindParam(":user_id", $userId, PDO::PARAM_INT);
            $stmt->execute();
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($resultado) {
                // Si se encontró el doctor, devolver sus datos
                error_log("Doctor encontrado para usuario ID " . $userId . ": " . json_encode($resultado));
                echo json_encode([
                    'status' => 'success',
                    'data' => $resultado
                ]);
            } else {
                // Si no se encontró, intentar obtener directamente por id del doctor si el usuario es un doctor
                $stmt = $db->prepare("SELECT doctor_id FROM rh_doctors WHERE doctor_id = :user_id LIMIT 1");
                $stmt->bindParam(":user_id", $userId, PDO::PARAM_INT);
                $stmt->execute();
                $doctorDirecto = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($doctorDirecto) {
                    error_log("Doctor encontrado directamente con ID " . $userId);
                    echo json_encode([
                        'status' => 'success',
                        'data' => [
                            'doctor_id' => $doctorDirecto['doctor_id'],
                            'nombre_completo' => 'Doctor ID: ' . $doctorDirecto['doctor_id']
                        ]
                    ]);
                } else {
                    error_log("No se encontró doctor para el usuario ID: " . $userId);
                    echo json_encode([
                        'status' => 'error',
                        'message' => 'No se encontró un doctor asociado a este usuario'
                    ]);
                }
            }
        } catch (PDOException $e) {
            error_log("Error al buscar doctor por ID de usuario: " . $e->getMessage());
            echo json_encode([
                'status' => 'error',
                'message' => 'Error al consultar los datos del doctor: ' . $e->getMessage()
            ]);
        }
    }
}

// Procesar solicitudes AJAX
if (isset($_POST['operacion'])) {
    $preformatos = new PreformatosAjax();
    
    // Guardar información de diagnóstico
    error_log("Operación solicitada: " . $_POST['operacion']);
    
    // Log request parameters
    file_put_contents('logs/database.log', 
        date('[Y-m-d H:i:s] ') . 
        "REQUEST PREFORMATOS - Operación: " . $_POST['operacion'] . 
        " | Parámetros: " . json_encode($_POST) . 
        PHP_EOL, 
        FILE_APPEND);
    
    switch ($_POST['operacion']) {
        case 'getMotivosComunes':
            $tipo_formulario = isset($_POST['tipo_formulario']) ? $_POST['tipo_formulario'] : 'general';
            $preformatos->ajaxGetMotivosComunes($tipo_formulario);
            break;
            
        case 'getPreformatosConsulta':
            $doctorId = isset($_POST['usuario_id']) ? $_POST['usuario_id'] : null;
            $tipoFormulario = isset($_POST['tipo_formulario']) ? $_POST['tipo_formulario'] : 'general';
            $preformatos->ajaxGetPreformatos('consulta', $doctorId, $tipoFormulario);
            break;
            
        case 'getPreformatosReceta':
            $doctorId = isset($_POST['usuario_id']) ? $_POST['usuario_id'] : null;
            $tipoFormulario = isset($_POST['tipo_formulario']) ? $_POST['tipo_formulario'] : 'general';
            $preformatos->ajaxGetPreformatos('receta', $doctorId, $tipoFormulario);
            break;
            
        case 'getPreformatosRecetaAnteojos':
            $doctorId = isset($_POST['usuario_id']) ? $_POST['usuario_id'] : null;
            $tipoFormulario = isset($_POST['tipo_formulario']) ? $_POST['tipo_formulario'] : 'anteojos';
            $preformatos->ajaxGetPreformatos('receta_anteojos', $doctorId, $tipoFormulario);
            break;
            
        case 'getPreformatosOrdenEstudios':
            $doctorId = isset($_POST['usuario_id']) ? $_POST['usuario_id'] : null;
            $tipoFormulario = isset($_POST['tipo_formulario']) ? $_POST['tipo_formulario'] : 'general';
            $preformatos->ajaxGetPreformatos('orden_estudios', $doctorId, $tipoFormulario);
            break;
            
        case 'getPreformatosOrdenCirugias':
            $doctorId = isset($_POST['usuario_id']) ? $_POST['usuario_id'] : null;
            $tipoFormulario = isset($_POST['tipo_formulario']) ? $_POST['tipo_formulario'] : 'general';
            $preformatos->ajaxGetPreformatos('orden_cirugias', $doctorId, $tipoFormulario);
            break;
            
        case 'getAllPreformatos':
            // Obtener el usuario_id del request si está disponible
            $filtros = isset($_POST['filtros']) ? $_POST['filtros'] : [];
            
            // Si se envió directamente el usuario_id, añadirlo a los filtros
            if (isset($_POST['usuario_id']) && !empty($_POST['usuario_id'])) {
                $filtros['creado_por'] = $_POST['usuario_id'];
                error_log("Filtrando preformatos por usuario_id: " . $_POST['usuario_id']);
            }
            
            error_log("Filtros aplicados: " . json_encode($filtros));
            $preformatos->ajaxGetAllPreformatos($filtros);
            break;
            
        case 'getUsuarios':
            $preformatos->ajaxGetUsuarios();
            break;
            
        case 'getPreformatoById':
            if (isset($_POST['id_preformato'])) {
                $preformatos->ajaxGetPreformatoById($_POST['id_preformato']);
            }
            break;
            
        case 'crearPreformato':
            $datos = [
                'nombre' => $_POST['nombre'],
                'contenido' => $_POST['contenido'],
                'tipo' => $_POST['tipo'],
                'tipo_formulario' => isset($_POST['tipo_formulario']) ? $_POST['tipo_formulario'] : 'general',
                'creado_por' => $_POST['creado_por']
            ];
            $preformatos->ajaxCrearPreformato($datos);
            break;
            
        case 'actualizarPreformato':
            $datos = [
                'id_preformato' => $_POST['id_preformato'],
                'nombre' => $_POST['nombre'],
                'contenido' => $_POST['contenido'],
                'tipo' => $_POST['tipo'],
                'tipo_formulario' => isset($_POST['tipo_formulario']) ? $_POST['tipo_formulario'] : 'general',
                'creado_por' => $_POST['creado_por']
            ];
            $preformatos->ajaxActualizarPreformato($datos);
            break;
            
        case 'eliminarPreformato':
            if (isset($_POST['id_preformato'])) {
                $preformatos->ajaxEliminarPreformato($_POST['id_preformato']);
            }
            break;
            
        case 'getDoctorByUserId':
            if (isset($_POST['user_id'])) {
                $preformatos->ajaxGetDoctorByUserId($_POST['user_id']);
            } else {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'ID de usuario no especificado'
                ]);
            }
            break;
            
        default:
            echo json_encode([
                'status' => 'error',
                'message' => 'Operación no reconocida'
            ]);
    }
} else {
    // Si no hay operación especificada pero se accede directamente a la URL, mostrar todos los preformatos
    $preformatos = new PreformatosAjax();
    $preformatos->ajaxGetAllPreformatos([]);
}

// Limpiar cualquier salida no deseada antes del JSON
$content = ob_get_clean();
if (!empty($content) && !json_decode($content)) {
    // Si hay contenido y no es JSON válido, loguearlo y devolver error
    error_log("Salida no JSON capturada en preformatos.ajax.php: " . $content);
    echo json_encode([
        'status' => 'error',
        'message' => 'Error de formato en respuesta del servidor'
    ]);
} else {
    // Si el contenido es JSON válido o está vacío, mostrarlo
    echo $content;
}