<?php
require_once "../controller/preformatos.controller.php";

class PreformatosAjax {
    /**
     * Obtiene todos los motivos comunes activos
     * @param string $tipo_formulario Tipo de formulario ('general', 'anteojos', etc.)
     */
    public function ajaxGetMotivosComunes($tipo_formulario = 'general') {
        $motivos = ControllerPreformatos::ctrGetMotivosComunes($tipo_formulario);
        echo json_encode([
            'status' => 'success',
            'data' => $motivos
        ]);
    }
    
    /**
     * Obtiene todos los preformatos activos de un tipo específico
     * @param string $tipo Tipo de preformato ('consulta', 'receta', etc.)
     * @param integer $userId ID del usuario conectado (opcional)
     */
    public function ajaxGetPreformatos($tipo, $userId = null) {
        // Registrar información para depuración
        error_log("ajaxGetPreformatos - Tipo: " . $tipo . ", User ID: " . ($userId ? $userId : 'ninguno'));
        
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
                        AND p.tipo = :tipo
                        AND p.activo = true
                        ORDER BY p.nombre ASC";
                
                error_log("SQL para obtener preformatos: " . $sql);
                
                $stmt = $db->prepare($sql);
                $stmt->bindParam(":user_id", $userId, PDO::PARAM_INT);
                $stmt->bindParam(":tipo", $tipo, PDO::PARAM_STR);
                $stmt->execute();
                
                $preformatos = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Registrar información de depuración
                error_log("Preformatos encontrados: " . count($preformatos));
                
                echo json_encode([
                    'status' => 'success',
                    'data' => $preformatos
                ]);
                return;
            } catch (PDOException $e) {
                error_log("Error al obtener preformatos por usuario: " . $e->getMessage());
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Error al obtener preformatos: ' . $e->getMessage()
                ]);
                return;
            }
        }
        
        // Si no se proporciona usuario_id, usar el método estándar para obtener todos los preformatos del tipo
        $preformatos = ControllerPreformatos::ctrGetPreformatos($tipo);
        echo json_encode([
            'status' => 'success',
            'data' => $preformatos
        ]);
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
    
    switch ($_POST['operacion']) {
        case 'getMotivosComunes':
            $tipo_formulario = isset($_POST['tipo_formulario']) ? $_POST['tipo_formulario'] : 'general';
            $preformatos->ajaxGetMotivosComunes($tipo_formulario);
            break;
            
        case 'getPreformatosConsulta':
            $doctorId = isset($_POST['usuario_id']) ? $_POST['usuario_id'] : null;
            $preformatos->ajaxGetPreformatos('consulta', $doctorId);
            break;
            
        case 'getPreformatosReceta':
            $doctorId = isset($_POST['usuario_id']) ? $_POST['usuario_id'] : null;
            $preformatos->ajaxGetPreformatos('receta', $doctorId);
            break;
            
        case 'getPreformatosRecetaAnteojos':
            $doctorId = isset($_POST['usuario_id']) ? $_POST['usuario_id'] : null;
            $preformatos->ajaxGetPreformatos('receta_anteojos', $doctorId);
            break;
            
        case 'getPreformatosOrdenEstudios':
            $doctorId = isset($_POST['usuario_id']) ? $_POST['usuario_id'] : null;
            $preformatos->ajaxGetPreformatos('orden_estudios', $doctorId);
            break;
            
        case 'getPreformatosOrdenCirugias':
            $doctorId = isset($_POST['usuario_id']) ? $_POST['usuario_id'] : null;
            $preformatos->ajaxGetPreformatos('orden_cirugias', $doctorId);
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