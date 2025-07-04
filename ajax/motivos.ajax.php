<?php

require_once "../controller/MotivosController.php";
require_once "../model/MotivosModel.php";

class AjaxMotivos {
    
    /*=============================================
    OBTENER TODOS LOS MOTIVOS COMUNES (CON FILTRO)
    =============================================*/
    public function ajaxObtenerMotivos() {
        try {
            // Obtener el término de búsqueda si está presente
            $termino = isset($_POST['nombre']) ? $_POST['nombre'] : '';
            
            // Si hay un término de búsqueda, utilizarlo para filtrar
            if (!empty($termino)) {
                $stmt = Conexion::conectar()->prepare("SELECT id_motivo, nombre, descripcion, fecha_creacion, activo, tipo_formulario FROM motivos_comunes WHERE nombre LIKE :termino ORDER BY nombre");
                $termino = '%' . $termino . '%'; // Agregar comodines para búsqueda parcial
                $stmt->bindParam(":termino", $termino, PDO::PARAM_STR);
            } else {
                // Si no hay término de búsqueda, obtener todos los motivos
                $stmt = Conexion::conectar()->prepare("SELECT id_motivo, nombre, descripcion, fecha_creacion, activo, tipo_formulario FROM motivos_comunes ORDER BY nombre");
            }

            $stmt->execute();
            $motivos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Formatear las fechas y asegurar que activo sea entero
            foreach ($motivos as &$motivo) {
                if (isset($motivo['fecha_creacion'])) {
                    $motivo['fecha_creacion'] = date('d/m/Y', strtotime($motivo['fecha_creacion']));
                }
                
                // Asegurar que activo sea un entero para JavaScript
                if (isset($motivo['activo'])) {
                    $motivo['activo'] = (int)$motivo['activo'];
                }
            }
            
            echo json_encode($motivos);
        } catch (Exception $e) {
            error_log("Error al obtener motivos: " . $e->getMessage());
            echo json_encode([]);
        }
    }
    
    /*=============================================
    CREAR MOTIVO COMÚN
    =============================================*/
    public function ajaxCrearMotivo() {
        if(isset($_POST['nuevoMotivo'])) {
            try {
                // Obtener los datos del formulario
                $nombre = $_POST['nuevoMotivo'];
                $descripcion = isset($_POST['nuevaDescripcion']) ? $_POST['nuevaDescripcion'] : '';
                $estado = isset($_POST['estado']) ? $_POST['estado'] : 1;
                $tipo_formulario = isset($_POST['tipo_formulario']) ? $_POST['tipo_formulario'] : 'general';
                
                // Obtener el ID del usuario que crea el motivo (si está disponible)
                $creado_por = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
                
                // Llamar al método del modelo
                $resultado = MotivosModel::mdlCrearMotivo($nombre, $descripcion, $estado, $creado_por, $tipo_formulario);
                
                // Devolver el resultado en formato JSON
                echo json_encode($resultado);
            } catch (Exception $e) {
                echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'No se recibió el nombre del motivo']);
        }
    }

    /*=============================================
    ACTUALIZAR MOTIVO COMÚN
    =============================================*/
    public function ajaxActualizarMotivo() {
        if(isset($_POST['idMotivo']) && isset($_POST['editarMotivo'])) {
            try {
                $id = $_POST['idMotivo'];
                $nombre = $_POST['editarMotivo'];
                $descripcion = isset($_POST['editarDescripcion']) ? $_POST['editarDescripcion'] : '';
                $estado = isset($_POST['estado']) ? $_POST['estado'] : 1;
                $tipo_formulario = isset($_POST['tipo_formulario']) ? $_POST['tipo_formulario'] : 'general';
                
                $resultado = MotivosModel::mdlActualizarMotivo($id, $nombre, $descripcion, $estado, $tipo_formulario);
                echo json_encode($resultado);
            } catch (Exception $e) {
                echo json_encode(['status' => 'error', 'message' => 'Error al actualizar: ' . $e->getMessage()]);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Datos incompletos para actualizar']);
        }
    }
    
    /*=============================================
    ELIMINAR MOTIVO COMÚN
    =============================================*/
    public function ajaxEliminarMotivo() {
        if(isset($_POST['idMotivo'])) {
            try {
                $resultado = MotivosModel::mdlEliminarMotivo($_POST['idMotivo']);
                echo json_encode($resultado);
            } catch (Exception $e) {
                echo json_encode(['status' => 'error', 'message' => 'Error al eliminar: ' . $e->getMessage()]);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'ID no proporcionado']);
        }
    }

    /*=============================================
    OBTENER UN MOTIVO COMÚN POR ID
    =============================================*/
    public function ajaxCargarMotivo() {
        if(isset($_POST['idMotivo'])) {
            try {
                $idMotivo = $_POST['idMotivo'];
                
                $stmt = Conexion::conectar()->prepare("SELECT id_motivo, nombre, descripcion, activo, tipo_formulario FROM motivos_comunes WHERE id_motivo = :id");
                $stmt->bindParam(":id", $idMotivo, PDO::PARAM_INT);
                $stmt->execute();
                
                $motivo = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($motivo) {
                    // Asegurarse de que activo sea un número para JavaScript
                    $motivo['activo'] = (int)$motivo['activo']; 
                    echo json_encode($motivo);
                } else {
                    echo json_encode(['error' => 'Motivo no encontrado']);
                }
            } catch (Exception $e) {
                echo json_encode(['error' => 'Error: ' . $e->getMessage()]);
            }
        } else {
            echo json_encode(['error' => 'ID no proporcionado']);
        }
    }
}

// OBJETOS
if(isset($_POST['accion'])) {
    $motivos = new AjaxMotivos();
    
    switch($_POST['accion']) {
        case 'listar':
            $motivos->ajaxObtenerMotivos();
            break;
        case 'crear':
            $motivos->ajaxCrearMotivo();
            break;
        case 'cargar':
            $motivos->ajaxCargarMotivo();
            break;
        case 'actualizar':
            $motivos->ajaxActualizarMotivo();
            break;
        case 'eliminar':
            $motivos->ajaxEliminarMotivo();
            break;
    }
}
