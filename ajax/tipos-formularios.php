<?php
/**
 * AJAX para gesti        case 'listar':
            $tipos = $controlador->obtenerTodosJson(false); // Incluir inactivos para administración
            echo json_encode(['success' => true, 'data' => $tipos]);
            break;
            
        case 'obtener':
        case 'obtener_por_id':
            $id = $_GET['id'] ?? $_POST['id'] ?? 0;
            $tipo = $controlador->obtenerPorId($id);
            if ($tipo) {
                echo json_encode(['success' => true, 'data' => $tipo]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Tipo de formulario no encontrado']);
            }
            break;mularios
 */
session_start();
require_once __DIR__ . '/../model/conexion.php';
require_once __DIR__ . '/../controller/ControladorTipoFormularios.php';

// Obtener conexión
$conexion = Conexion::conectar();

if (!$conexion) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error de conexión a la base de datos']);
    exit;
}

// Verificar que el usuario esté autenticado
if (!isset($_SESSION['usuario_id'])) {
    // Temporalmente, asignar un usuario por defecto para pruebas
    $_SESSION['usuario_id'] = 1;
    // En producción, descomentar la siguiente línea:
    // http_response_code(401);
    // echo json_encode(['success' => false, 'message' => 'No autorizado']);
    // exit;
}

$controlador = new ControladorTipoFormularios($conexion);
$accion = $_POST['accion'] ?? $_GET['accion'] ?? '';
$usuarioId = $_SESSION['usuario_id'];

try {
    switch ($accion) {
        case 'listar':
            $tipos = $controlador->modelo->obtenerTodos(false); // Incluir inactivos para administración
            echo json_encode(['success' => true, 'data' => $tipos]);
            break;
            
        case 'obtener_para_select':
            echo $controlador->obtenerParaSelect();
            break;
            
        case 'obtener_por_id':
            $id = $_GET['id'] ?? 0;
            $tipo = $controlador->obtenerPorId($id);
            if ($tipo) {
                echo json_encode(['success' => true, 'data' => $tipo]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Tipo de formulario no encontrado']);
            }
            break;
            
        case 'crear':
            $datos = [
                'nombre' => trim($_POST['nombre'] ?? ''),
                'descripcion' => trim($_POST['descripcion'] ?? ''),
                'codigo' => trim($_POST['codigo'] ?? ''),
                'creado_por' => $usuarioId
            ];
            
            // Si no se proporciona código, generarlo automáticamente
            if (empty($datos['codigo']) && !empty($datos['nombre'])) {
                $datos['codigo'] = $controlador->generarCodigo($datos['nombre']);
            }
            
            $resultado = $controlador->crear($datos);
            echo json_encode($resultado);
            break;
            
        case 'actualizar':
            $id = $_POST['id'] ?? 0;
            $datos = [
                'nombre' => trim($_POST['nombre'] ?? ''),
                'descripcion' => trim($_POST['descripcion'] ?? ''),
                'codigo' => trim($_POST['codigo'] ?? ''),
                'modificado_por' => $usuarioId
            ];
            
            $resultado = $controlador->actualizar($id, $datos);
            echo json_encode($resultado);
            break;
            
        case 'activar':
            $id = $_POST['id'] ?? 0;
            $resultado = $controlador->cambiarEstado($id, true, $usuarioId);
            echo json_encode($resultado);
            break;
            
        case 'desactivar':
            $id = $_POST['id'] ?? 0;
            $resultado = $controlador->cambiarEstado($id, false, $usuarioId);
            echo json_encode($resultado);
            break;
            
        case 'eliminar':
            $id = $_POST['id'] ?? 0;
            $resultado = $controlador->eliminar($id);
            echo json_encode($resultado);
            break;
            
        case 'cambiar_estado':
            $id = $_POST['id'] ?? 0;
            $activo = $_POST['activo'] === 'true';
            
            $resultado = $controlador->cambiarEstado($id, $activo, $usuarioId);
            echo json_encode($resultado);
            break;
            
        case 'generar_codigo':
            $nombre = $_POST['nombre'] ?? '';
            if (!empty($nombre)) {
                $codigo = $controlador->generarCodigo($nombre);
                echo json_encode(['success' => true, 'codigo' => $codigo]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Nombre requerido']);
            }
            break;
            
        case 'obtener_estadisticas':
            $estadisticas = $controlador->obtenerEstadisticas();
            echo json_encode(['success' => true, 'data' => $estadisticas]);
            break;
            
        case 'validar_codigo':
            $codigo = $_POST['codigo'] ?? '';
            $id = $_POST['id'] ?? null;
            
            if (!empty($codigo)) {
                // Usar el modelo del controlador para validación
                $existe = $controlador->modelo->codigoExiste($codigo, $id);
                echo json_encode(['existe' => $existe]);
            } else {
                echo json_encode(['existe' => false]);
            }
            break;
            
        case 'validar_nombre':
            $nombre = $_POST['nombre'] ?? '';
            $id = $_POST['id'] ?? null;
            
            if (!empty($nombre)) {
                // Usar el modelo del controlador para validación
                $existe = $controlador->modelo->nombreExiste($nombre, $id);
                echo json_encode(['existe' => $existe]);
            } else {
                echo json_encode(['existe' => false]);
            }
            break;
            
        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Acción no válida']);
            break;
    }
} catch (Exception $e) {
    error_log("Error en AJAX tipos de formularios: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error interno del servidor']);
}
?>
