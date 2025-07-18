<?php
// Archivo AJAX para la gestión de turnos

require_once __DIR__ . "/../model/TurnosModel.php";

class AjaxTurnos {
    
    /**
     * Variable para ID de turno
     */
    public $idTurno;
    
    /**
     * Variable para nombre de turno
     */
    public $nombreTurno;
    
    /**
     * Obtener datos de un turno
     */
    public function ajaxEditarTurno() {
        $item = "turno_id";
        $valor = $this->idTurno;
        
        $respuesta = TurnosModel::mdlMostrarTurnos("turnos", $item, $valor);
        
        echo json_encode($respuesta);
    }
    
    /**
     * Validar nombre único de turno
     */
    public function ajaxValidarNombreTurno() {
        $item = "turno_nombre";
        $valor = $this->nombreTurno;
        
        $respuesta = TurnosModel::mdlMostrarTurnos("turnos", $item, $valor);
        
        echo json_encode($respuesta);
    }
    
    /**
     * Obtener todos los turnos activos para selectores
     */
    public function ajaxObtenerTurnosActivos() {
        $respuesta = TurnosModel::mdlMostrarTurnosActivos("turnos");
        
        if($respuesta && is_array($respuesta)) {
            echo json_encode([
                "status" => true,
                "data" => $respuesta
            ]);
        } else {
            echo json_encode([
                "status" => false,
                "data" => [],
                "message" => "No se encontraron turnos activos"
            ]);
        }
        
        exit;
    }
    
    /**
     * Obtener todos los turnos
     */
    public function ajaxObtenerTurnos() {
        $respuesta = TurnosModel::mdlMostrarTurnos("turnos", null, null);
        
        // Asegurar que siempre devolvemos un array válido
        if($respuesta && is_array($respuesta)) {
            echo json_encode($respuesta);
        } else {
            echo json_encode([]);
        }
    }
    
    /**
     * Crear nuevo turno
     */
    public function ajaxCrearTurno() {
        // Validar que los campos requeridos no estén vacíos
        if(!empty($_POST["turno_nombre"])) {
            
            // Validar que el nombre no contenga caracteres especiales peligrosos
            if(strlen($_POST["turno_nombre"]) <= 50) {
                
                $tabla = "turnos";
                
                $datos = array(
                    "turno_nombre" => trim($_POST["turno_nombre"]),
                    "turno_descripcion" => !empty($_POST["turno_descripcion"]) ? trim($_POST["turno_descripcion"]) : null,
                    "turno_estado" => isset($_POST["turno_estado"]) ? (int)$_POST["turno_estado"] : 1
                );
                
                $respuesta = TurnosModel::mdlIngresarTurno($tabla, $datos);
                
                if($respuesta == "ok") {
                    echo json_encode([
                        "status" => "success",
                        "message" => "El turno ha sido creado correctamente"
                    ]);
                } else {
                    echo json_encode([
                        "status" => "error",
                        "message" => "Error al crear el turno: " . $respuesta
                    ]);
                }
            } else {
                echo json_encode([
                    "status" => "error",
                    "message" => "El nombre del turno no puede exceder los 50 caracteres"
                ]);
            }
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "El campo nombre es obligatorio"
            ]);
        }
    }
    
    /**
     * Editar turno existente
     */
    public function ajaxEditarTurnoGuardar() {
        // Validar que los campos requeridos no estén vacíos
        if(!empty($_POST["turno_nombre"]) && !empty($_POST["turno_id"])) {
            
            // Validar longitud del nombre
            if(strlen($_POST["turno_nombre"]) <= 50) {
                
                $tabla = "turnos";
                
                $datos = array(
                    "turno_id" => (int)$_POST["turno_id"],
                    "turno_nombre" => trim($_POST["turno_nombre"]),
                    "turno_descripcion" => !empty($_POST["turno_descripcion"]) ? trim($_POST["turno_descripcion"]) : null,
                    "turno_estado" => isset($_POST["turno_estado"]) ? (int)$_POST["turno_estado"] : 1
                );
                
                $respuesta = TurnosModel::mdlEditarTurno($tabla, $datos);
                
                if($respuesta == "ok") {
                    echo json_encode([
                        "status" => "success",
                        "message" => "El turno ha sido actualizado correctamente"
                    ]);
                } else {
                    echo json_encode([
                        "status" => "error",
                        "message" => "Error al actualizar el turno: " . $respuesta
                    ]);
                }
            } else {
                echo json_encode([
                    "status" => "error",
                    "message" => "El nombre del turno no puede exceder los 50 caracteres"
                ]);
            }
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "Los campos nombre y ID son obligatorios"
            ]);
        }
    }
    
    /**
     * Eliminar turno
     */
    public function ajaxEliminarTurno() {
        if(!empty($_POST["turno_id"])) {
            
            $tabla = "turnos";
            $datos = (int)$_POST["turno_id"];
            
            $respuesta = TurnosModel::mdlBorrarTurno($tabla, $datos);
            
            if($respuesta == "ok") {
                echo json_encode([
                    "status" => "success",
                    "message" => "El turno ha sido eliminado correctamente"
                ]);
            } else {
                echo json_encode([
                    "status" => "error",
                    "message" => "Error al eliminar el turno: " . $respuesta
                ]);
            }
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "ID de turno requerido"
            ]);
        }
    }
    
    /**
     * Cambiar estado de turno (activar/desactivar)
     */
    public function ajaxCambiarEstadoTurno() {
        if(!empty($_POST["turno_id"]) && isset($_POST["nuevo_estado"])) {
            
            $tabla = "turnos";
            $datos = array(
                "turno_id" => (int)$_POST["turno_id"],
                "turno_estado" => (int)$_POST["nuevo_estado"]
            );
            
            $respuesta = TurnosModel::mdlCambiarEstadoTurno($tabla, $datos);
            
            if($respuesta == "ok") {
                $estado_texto = $datos["turno_estado"] ? "activado" : "desactivado";
                echo json_encode([
                    "status" => "success",
                    "message" => "El turno ha sido {$estado_texto} correctamente"
                ]);
            } else {
                echo json_encode([
                    "status" => "error",
                    "message" => "Error al cambiar el estado del turno: " . $respuesta
                ]);
            }
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "ID de turno y nuevo estado son requeridos"
            ]);
        }
    }
    
    /**
     * Buscar turnos
     */
    public function ajaxBuscarTurnos() {
        if(!empty($_POST["termino_busqueda"])) {
            
            $termino = trim($_POST["termino_busqueda"]);
            $respuesta = TurnosModel::mdlBuscarTurnos("turnos", $termino);
            
            if($respuesta) {
                echo json_encode([
                    "status" => "success",
                    "data" => $respuesta
                ]);
            } else {
                echo json_encode([
                    "status" => "error",
                    "message" => "No se encontraron turnos con ese término de búsqueda",
                    "data" => []
                ]);
            }
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "Término de búsqueda requerido"
            ]);
        }
    }
}

// Procesar las diferentes peticiones AJAX

// Editar turno (obtener datos)
if(isset($_POST["idTurno"])) {
    $editar = new AjaxTurnos();
    $editar->idTurno = $_POST["idTurno"];
    $editar->ajaxEditarTurno();
    exit;
}

// Validar nombre de turno
if(isset($_POST["validarNombreTurno"])) {
    $validarNombre = new AjaxTurnos();
    $validarNombre->nombreTurno = $_POST["validarNombreTurno"];
    $validarNombre->ajaxValidarNombreTurno();
    exit;
}

// Obtener turnos activos para selectores
if(isset($_POST["action"]) && $_POST["action"] == "obtenerTurnosActivos") {
    $obtenerTurnosActivos = new AjaxTurnos();
    $obtenerTurnosActivos->ajaxObtenerTurnosActivos();
    exit;
}

// Obtener todos los turnos
if(isset($_POST["accion"]) && $_POST["accion"] == "obtenerTurnos") {
    $obtenerTurnos = new AjaxTurnos();
    $obtenerTurnos->ajaxObtenerTurnos();
    exit;
}

// Crear nuevo turno
if(isset($_POST["accion"]) && $_POST["accion"] == "crearTurno") {
    $crearTurno = new AjaxTurnos();
    $crearTurno->ajaxCrearTurno();
    exit;
}

// Editar turno (guardar cambios)
if(isset($_POST["accion"]) && $_POST["accion"] == "editarTurno") {
    $editarTurno = new AjaxTurnos();
    $editarTurno->ajaxEditarTurnoGuardar();
    exit;
}

// Eliminar turno
if(isset($_POST["accion"]) && $_POST["accion"] == "eliminarTurno") {
    $eliminarTurno = new AjaxTurnos();
    $eliminarTurno->ajaxEliminarTurno();
    exit;
}

// Cambiar estado de turno
if(isset($_POST["accion"]) && $_POST["accion"] == "cambiarEstadoTurno") {
    $cambiarEstado = new AjaxTurnos();
    $cambiarEstado->ajaxCambiarEstadoTurno();
    exit;
}

// Buscar turnos
if(isset($_POST["accion"]) && $_POST["accion"] == "buscarTurnos") {
    $buscarTurnos = new AjaxTurnos();
    $buscarTurnos->ajaxBuscarTurnos();
    exit;
}

?>
