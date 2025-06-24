<?php

require_once "../controller/EspecialidadesController.php";
require_once "../model/EspecialidadesModel.php";

class AjaxEspecialidades {
    
    /*=============================================
    OBTENER TODAS LAS ESPECIALIDADES (CON FILTRO)
    =============================================*/
    public function ajaxObtenerEspecialidades() {
        try {
            // Obtener el término de búsqueda si está presente
            $termino = isset($_POST['nombre']) ? $_POST['nombre'] : '';
            
            // Si hay un término de búsqueda, utilizarlo para filtrar
            if (!empty($termino)) {
                $stmt = Conexion::conectar()->prepare("SELECT especialidad_id, nombre, descripcion, fecha_creacion, activo FROM especialidades WHERE nombre LIKE :termino ORDER BY nombre");
                $termino = '%' . $termino . '%'; // Agregar comodines para búsqueda parcial
                $stmt->bindParam(":termino", $termino, PDO::PARAM_STR);
            } else {
                // Si no hay término de búsqueda, obtener todas las especialidades
                $stmt = Conexion::conectar()->prepare("SELECT especialidad_id, nombre, descripcion, fecha_creacion, activo FROM especialidades ORDER BY nombre");
            }

            $stmt->execute();
            $especialidades = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Formatear las fechas si es necesario
            foreach ($especialidades as &$especialidad) {
                if (isset($especialidad['fecha_creacion'])) {
                    // Opcional: formatear fecha si es necesario
                    $especialidad['fecha_creacion'] = date('d/m/Y', strtotime($especialidad['fecha_creacion']));
                }
                
                // Asegurar que activo sea un entero para JavaScript
                if (isset($especialidad['activo'])) {
                    $especialidad['activo'] = (int)$especialidad['activo'];
                }
            }
            
            echo json_encode($especialidades);
        } catch (Exception $e) {
            error_log("Error al obtener especialidades: " . $e->getMessage());
            echo json_encode([]);
        }
    }
    
    /*=============================================
    CREAR ESPECIALIDAD
    =============================================*/
    public function ajaxCrearEspecialidad() {
        if(isset($_POST['nuevaEspecialidad'])) {
            try {
                // Obtener los datos del formulario
                $nombre = $_POST['nuevaEspecialidad'];
                $descripcion = isset($_POST['nuevaDescripcion']) ? $_POST['nuevaDescripcion'] : '';
                $estado = isset($_POST['estado']) ? $_POST['estado'] : 1;
                
                // Llamar al método del modelo
                $resultado = EspecialidadesModel::mdlCrearEspecialidad($nombre, $descripcion, $estado);
                
                // Devolver el resultado en formato JSON
                echo json_encode($resultado);
            } catch (Exception $e) {
                echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'No se recibió el nombre de la especialidad']);
        }
    }

    /*=============================================
    ACTUALIZAR ESPECIALIDAD
    =============================================*/
    public function ajaxActualizarEspecialidad() {
        if(isset($_POST['idEspecialidad']) && isset($_POST['editarEspecialidad'])) {
            try {
                $id = $_POST['idEspecialidad'];
                $nombre = $_POST['editarEspecialidad'];
                $descripcion = isset($_POST['editarDescripcion']) ? $_POST['editarDescripcion'] : '';
                $estado = isset($_POST['estado']) ? $_POST['estado'] : 1;
                
                $resultado = EspecialidadesModel::mdlActualizarEspecialidad($id, $nombre, $descripcion, $estado);
                echo json_encode($resultado);
            } catch (Exception $e) {
                echo json_encode(['status' => 'error', 'message' => 'Error al actualizar: ' . $e->getMessage()]);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Datos incompletos para actualizar']);
        }
    }
    
    /*=============================================
    ELIMINAR ESPECIALIDAD
    =============================================*/
    public function ajaxEliminarEspecialidad() {
        if(isset($_POST['idEspecialidad'])) {
            try {
                $resultado = EspecialidadesModel::mdlEliminarEspecialidad($_POST['idEspecialidad']);
                echo json_encode($resultado);
            } catch (Exception $e) {
                echo json_encode(['status' => 'error', 'message' => 'Error al eliminar: ' . $e->getMessage()]);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'ID no proporcionado']);
        }
    }

    /*=============================================
    OBTENER UNA ESPECIALIDAD POR ID
    =============================================*/
    public function ajaxCargarEspecialidad() {
        if(isset($_POST['idEspecialidad'])) {
            try {
                $idEspecialidad = $_POST['idEspecialidad'];
                
                $stmt = Conexion::conectar()->prepare("SELECT especialidad_id, nombre, descripcion, activo FROM especialidades WHERE especialidad_id = :id");
                $stmt->bindParam(":id", $idEspecialidad, PDO::PARAM_INT);
                $stmt->execute();
                
                $especialidad = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($especialidad) {
                    // Asegurarse de que activo sea un número para JavaScript
                    $especialidad['activo'] = (int)$especialidad['activo']; 
                    echo json_encode($especialidad);
                } else {
                    echo json_encode(['error' => 'Especialidad no encontrada']);
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
    $especialidades = new AjaxEspecialidades();
    
    switch($_POST['accion']) {
        case 'listar':
            $especialidades->ajaxObtenerEspecialidades();
            break;
        case 'crear':
            $especialidades->ajaxCrearEspecialidad();
            break;
        case 'cargar':
            $especialidades->ajaxCargarEspecialidad();
            break;
        case 'actualizar':
            $especialidades->ajaxActualizarEspecialidad();
            break;
        case 'eliminar':
            $especialidades->ajaxEliminarEspecialidad();
            break;
    }
}
