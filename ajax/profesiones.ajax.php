<?php

require_once "../controller/ProfesionesController.php";
require_once "../model/ProfesionesModel.php";

class AjaxProfesiones {
    
    /*=============================================
    OBTENER TODAS LAS PROFESIONES (CON FILTRO)
    =============================================*/
    public function ajaxObtenerProfesiones() {
        try {
            // Obtener el término de búsqueda si está presente
            $termino = isset($_POST['nombre']) ? $_POST['nombre'] : '';
            
            // Si hay un término de búsqueda, utilizarlo para filtrar
            if (!empty($termino)) {
                $stmt = Conexion::conectar()->prepare("SELECT id, nombre, fecha_creacion, activo FROM profesiones WHERE nombre LIKE :termino and activo = TRUE ORDER BY nombre");
                $termino = '%' . $termino . '%'; // Agregar comodines para búsqueda parcial
                $stmt->bindParam(":termino", $termino, PDO::PARAM_STR);
            } else {
                // Si no hay término de búsqueda, obtener todas las profesiones
                $stmt = Conexion::conectar()->prepare("SELECT id, nombre, fecha_creacion, activo FROM profesiones WHERE activo = TRUE ORDER BY nombre");
            }

            
            
            $stmt->execute();
            $profesiones = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Formatear las fechas si es necesario
            foreach ($profesiones as &$profesion) {
                if (isset($profesion['fecha_creacion'])) {
                    // Opcional: formatear fecha si es necesario
                    // $profesion['fecha_creacion'] = date('d/m/Y', strtotime($profesion['fecha_creacion']));
                }
            }
            
            echo json_encode($profesiones);
        } catch (Exception $e) {
            error_log("Error al obtener profesiones: " . $e->getMessage());
            echo json_encode([]);
        }
    }
    
    /*=============================================
    CREAR PROFESIÓN
    =============================================*/
    public function ajaxCrearProfesion() {
        if(isset($_POST['nuevaProfesion'])) {
            try {
                // Llamar al método del modelo
                $resultado = ProfesionesModel::mdlCrearProfesion($_POST['nuevaProfesion']);
                
                // Devolver el resultado en formato JSON
                echo json_encode($resultado);
            } catch (Exception $e) {
                echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'No se recibió el nombre de la profesión']);
        }
    }

    /*=============================================
    ACTUALIZAR PROFESIÓN
    =============================================*/
    public function ajaxActualizarProfesion() {
        if(isset($_POST['idProfesion']) && isset($_POST['editarProfesion'])) {
            $resultado = ProfesionesModel::mdlActualizarProfesion($_POST['idProfesion'], $_POST['editarProfesion'], $_POST['estado']);
            echo json_encode($resultado);
        }
    }
    
    /*=============================================
    ELIMINAR PROFESIÓN
    =============================================*/
    public function ajaxEliminarProfesion() {
        if(isset($_POST['idProfesion'])) {
            $resultado = ProfesionesModel::mdlEliminarProfesion($_POST['idProfesion']);
            echo json_encode($resultado);
        }
    }

    /*=============================================
    OBTENER UNA PROFESIÓN POR ID
    =============================================*/
    public function ajaxCargarProfesion() {
        if(isset($_POST['idProfesion'])) {
            try {
                // Include the necessary controller if it doesn't exist yet
                if (!class_exists('ProfesionesModel')) {
                    if (file_exists('../model/profesiones.model.php')) {
                        require_once '../model/profesiones.model.php';
                    } else {
                        require_once 'model/profesiones.model.php';
                    }
                }

                $stmt = Conexion::conectar()->prepare("SELECT id, nombre, activo FROM profesiones WHERE id = :id");
                $stmt->bindParam(":id", $_POST['idProfesion'], PDO::PARAM_INT);
                $stmt->execute();
                
                $profesion = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($profesion) {
                    echo json_encode($profesion);
                } else {
                    echo json_encode(['error' => 'Profesión no encontrada']);
                }
            } catch (Exception $e) {
                echo json_encode(['error' => 'Error: ' . $e->getMessage()]);
            }
        } else {
            echo json_encode(['error' => 'ID de profesión no especificado']);
        }
    }

    
}

// OBJETOS
if(isset($_POST['accion'])) {
    $profesiones = new AjaxProfesiones();
    
    switch($_POST['accion']) {
        case 'listar':
            $profesiones->ajaxObtenerProfesiones();
            break;
        case 'crear':
            $profesiones->ajaxCrearProfesion();
            break;
        case 'cargar':
            $profesiones->ajaxCargarProfesion();
            break;
        case 'actualizar':
            $profesiones->ajaxActualizarProfesion();
            break;
        case 'eliminar':
            $profesiones->ajaxEliminarProfesion();
            break;
    }
}