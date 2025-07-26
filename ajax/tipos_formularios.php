<?php
// Incluir archivos necesarios
require_once '../model/conexion.php';
require_once '../model/TipoFormularios.php';
require_once '../controller/TipoFormulariosController.php';

// Configurar headers para JSON
header('Content-Type: application/json');

try {
    $accion = $_POST['accion'] ?? $_GET['accion'] ?? '';
    
    switch ($accion) {
        case 'listar':
            $tipos = TipoFormulariosController::listarTodos();
            echo json_encode([
                'success' => true,
                'data' => $tipos
            ]);
            break;
            
        case 'obtener':
            $id = $_POST['id'] ?? $_GET['id'] ?? 0;
            if ($id > 0) {
                $tipo = TipoFormulariosController::obtenerPorId($id);
                echo json_encode([
                    'success' => true,
                    'data' => $tipo
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'ID no válido'
                ]);
            }
            break;
            
        case 'crear':
            $datos = [
                'nombre' => $_POST['nombre'] ?? '',
                'descripcion' => $_POST['descripcion'] ?? '',
                'codigo' => $_POST['codigo'] ?? '',
                'activo' => isset($_POST['activo']) ? (bool)$_POST['activo'] : true,
                'creado_por' => $_SESSION['usuario_id'] ?? 1
            ];
            
            if (empty($datos['nombre']) || empty($datos['codigo'])) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Nombre y código son obligatorios'
                ]);
                break;
            }
            
            $resultado = TipoFormulariosController::crear($datos);
            if ($resultado) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Tipo de formulario creado exitosamente'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Error al crear el tipo de formulario'
                ]);
            }
            break;
            
        case 'actualizar':
            $id = $_POST['id'] ?? 0;
            $datos = [
                'nombre' => $_POST['nombre'] ?? '',
                'descripcion' => $_POST['descripcion'] ?? '',
                'codigo' => $_POST['codigo'] ?? '',
                'activo' => isset($_POST['activo']) ? (bool)$_POST['activo'] : true,
                'modificado_por' => $_SESSION['usuario_id'] ?? 1
            ];
            
            if ($id <= 0 || empty($datos['nombre']) || empty($datos['codigo'])) {
                echo json_encode([
                    'success' => false,
                    'message' => 'ID, nombre y código son obligatorios'
                ]);
                break;
            }
            
            $resultado = TipoFormulariosController::actualizar($id, $datos);
            if ($resultado) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Tipo de formulario actualizado exitosamente'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Error al actualizar el tipo de formulario'
                ]);
            }
            break;
            
        case 'eliminar':
            $id = $_POST['id'] ?? $_GET['id'] ?? 0;
            if ($id > 0) {
                $resultado = TipoFormulariosController::eliminar($id);
                if ($resultado) {
                    echo json_encode([
                        'success' => true,
                        'message' => 'Tipo de formulario eliminado exitosamente'
                    ]);
                } else {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Error al eliminar el tipo de formulario'
                    ]);
                }
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'ID no válido'
                ]);
            }
            break;
            
        case 'activar':
            $id = $_POST['id'] ?? 0;
            if ($id > 0) {
                $resultado = TipoFormulariosController::activar($id);
                if ($resultado) {
                    echo json_encode([
                        'success' => true,
                        'message' => 'Tipo de formulario activado exitosamente'
                    ]);
                } else {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Error al activar el tipo de formulario'
                    ]);
                }
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'ID no válido'
                ]);
            }
            break;
            
        case 'desactivar':
            $id = $_POST['id'] ?? 0;
            if ($id > 0) {
                $resultado = TipoFormulariosController::desactivar($id);
                if ($resultado) {
                    echo json_encode([
                        'success' => true,
                        'message' => 'Tipo de formulario desactivado exitosamente'
                    ]);
                } else {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Error al desactivar el tipo de formulario'
                    ]);
                }
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'ID no válido'
                ]);
            }
            break;
            
        default:
            echo json_encode([
                'success' => false,
                'message' => 'Acción no válida'
            ]);
            break;
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error del servidor: ' . $e->getMessage()
    ]);
}
?>
