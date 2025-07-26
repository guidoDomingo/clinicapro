<?php
// Nuevo archivo AJAX funcional para preformatos

// Configurar manejo de errores
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', '../logs/preformatos_nuevo.log');

// Iniciar buffer de salida
ob_start();

// Configurar header JSON
header('Content-Type: application/json');

// Incluir dependencias
try {
    require_once "../model/conexion.php";
    require_once "../model/preformatos.model.php";
    require_once "../controller/preformatos.controller.php";
} catch (Exception $e) {
    ob_clean();
    echo json_encode([
        'status' => 'error',
        'message' => 'Error al cargar dependencias: ' . $e->getMessage()
    ]);
    exit;
}

/**
 * Clase simplificada para manejo de AJAX de preformatos
 */
class PreformatosAjaxNuevo {
    
    public function getDoctorByUserId($userId) {
        try {
            $db = Conexion::conectar();
            
            // Consulta directa y simple
            $sql = "SELECT 
                        d.doctor_id,
                        d.person_id,
                        rp.first_name,
                        rp.last_name,
                        CONCAT(rp.last_name, ', ', rp.first_name) as nombre_completo
                    FROM person_system_user psu 
                    JOIN rh_person rp ON psu.person_id = rp.person_id
                    JOIN rh_doctors d ON rp.person_id = d.person_id
                    WHERE psu.system_user_id = :user_id
                    LIMIT 1";
            
            $stmt = $db->prepare($sql);
            $stmt->bindParam(":user_id", $userId, PDO::PARAM_INT);
            $stmt->execute();
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($resultado) {
                return [
                    'status' => 'success',
                    'data' => $resultado
                ];
            } else {
                // Intentar buscar directamente como doctor_id
                $sql2 = "SELECT doctor_id, person_id FROM rh_doctors WHERE doctor_id = :user_id LIMIT 1";
                $stmt2 = $db->prepare($sql2);
                $stmt2->bindParam(":user_id", $userId, PDO::PARAM_INT);
                $stmt2->execute();
                $doctorDirecto = $stmt2->fetch(PDO::FETCH_ASSOC);
                
                if ($doctorDirecto) {
                    return [
                        'status' => 'success',
                        'data' => [
                            'doctor_id' => $doctorDirecto['doctor_id'],
                            'nombre_completo' => 'Doctor ID: ' . $doctorDirecto['doctor_id']
                        ]
                    ];
                } else {
                    return [
                        'status' => 'error',
                        'message' => 'No se encontró un doctor asociado a este usuario'
                    ];
                }
            }
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Error al consultar datos del doctor: ' . $e->getMessage()
            ];
        }
    }
    
    public function getPreformatos($tipo, $userId = null, $tipoFormulario = 'general') {
        try {
            $db = Conexion::conectar();
            
            $sql = "SELECT 
                        p.id_preformato,
                        p.nombre,
                        p.contenido,
                        p.tipo,
                        p.tipo_formulario,
                        p.creado_por
                    FROM preformatos p
                    WHERE p.activo = true
                    AND p.tipo = :tipo
                    AND p.tipo_formulario = :tipo_formulario";
            
            $params = [
                ':tipo' => $tipo,
                ':tipo_formulario' => $tipoFormulario
            ];
            
            // Si se especifica usuario, intentar filtrar por doctor
            if ($userId) {
                // Primero obtener el doctor_id del usuario
                $sqlDoctor = "SELECT d.doctor_id 
                             FROM person_system_user psu 
                             JOIN rh_doctors d ON psu.person_id = d.person_id
                             WHERE psu.system_user_id = :user_id LIMIT 1";
                
                $stmtDoctor = $db->prepare($sqlDoctor);
                $stmtDoctor->bindParam(":user_id", $userId, PDO::PARAM_INT);
                $stmtDoctor->execute();
                $doctorResult = $stmtDoctor->fetch(PDO::FETCH_ASSOC);
                
                if ($doctorResult) {
                    $sql .= " AND p.creado_por = :doctor_id";
                    $params[':doctor_id'] = $doctorResult['doctor_id'];
                }
            }
            
            $sql .= " ORDER BY p.nombre ASC";
            
            $stmt = $db->prepare($sql);
            foreach ($params as $param => $value) {
                $stmt->bindValue($param, $value);
            }
            $stmt->execute();
            
            $preformatos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return [
                'status' => 'success',
                'data' => $preformatos
            ];
            
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Error al obtener preformatos: ' . $e->getMessage()
            ];
        }
    }
}

// Procesar peticiones
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['operacion'])) {
    $ajax = new PreformatosAjaxNuevo();
    $response = null;
    
    switch ($_POST['operacion']) {
        case 'getDoctorByUserId':
            if (isset($_POST['user_id'])) {
                $response = $ajax->getDoctorByUserId($_POST['user_id']);
            } else {
                $response = [
                    'status' => 'error',
                    'message' => 'ID de usuario no especificado'
                ];
            }
            break;
            
        case 'getPreformatosConsulta':
            $userId = isset($_POST['usuario_id']) ? $_POST['usuario_id'] : null;
            $tipoFormulario = isset($_POST['tipo_formulario']) ? $_POST['tipo_formulario'] : 'general';
            $response = $ajax->getPreformatos('consulta', $userId, $tipoFormulario);
            break;
            
        case 'getPreformatosReceta':
            $userId = isset($_POST['usuario_id']) ? $_POST['usuario_id'] : null;
            $tipoFormulario = isset($_POST['tipo_formulario']) ? $_POST['tipo_formulario'] : 'general';
            $response = $ajax->getPreformatos('receta', $userId, $tipoFormulario);
            break;
            
        default:
            $response = [
                'status' => 'error',
                'message' => 'Operación no reconocida: ' . $_POST['operacion']
            ];
    }
    
    // Limpiar buffer y enviar respuesta
    $buffer = ob_get_clean();
    if (!empty($buffer)) {
        error_log("Buffer no vacío en preformatos_nuevo: " . $buffer);
    }
    
    echo json_encode($response);
    
} else {
    ob_clean();
    echo json_encode([
        'status' => 'error',
        'message' => 'Método no permitido o datos faltantes'
    ]);
}
?>
