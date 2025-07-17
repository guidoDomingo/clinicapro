<?php
// Archivo AJAX para la gestión de salas

require_once "../model/SalasModel.php";

class AjaxSalas {
    
    /**
     * Variable para ID de sala
     */
    public $idSala;
    
    /**
     * Variable para código de sala
     */
    public $codigoSala;
    
    /**
     * Obtener datos de una sala
     */
    public function ajaxEditarSala() {
        $item = "sala_id";
        $valor = $this->idSala;
        
        $respuesta = SalasModel::mdlMostrarSalas("salas", $item, $valor);
        
        echo json_encode($respuesta);
    }
    
    /**
     * Validar código único de sala
     */
    public function ajaxValidarCodigoSala() {
        $item = "sala_codigo";
        $valor = $this->codigoSala;
        
        $respuesta = SalasModel::mdlMostrarSalas("salas", $item, $valor);
        
        echo json_encode($respuesta);
    }
    
    /**
     * Obtener todas las salas
     */
    public function ajaxObtenerSalas() {
        $respuesta = SalasModel::mdlMostrarSalas("salas", null, null);
        echo json_encode($respuesta);
    }
    
    /**
     * Crear nueva sala
     */
    public function ajaxCrearSala() {
        // Validar que los campos requeridos no estén vacíos
        if(!empty($_POST["sala_codigo"]) && !empty($_POST["sala_nombre"])) {
            
            // Validar que el código no contenga espacios ni caracteres especiales
            if(preg_match('/^[a-zA-Z0-9_-]+$/', $_POST["sala_codigo"])) {
                
                $tabla = "salas";
                
                $datos = array(
                    "sala_codigo" => $_POST["sala_codigo"],
                    "sala_nombre" => $_POST["sala_nombre"],
                    "sala_descripcion" => $_POST["sala_descripcion"] ?? null,
                    "sala_estado" => $_POST["sala_estado"] ?? 1
                );
                
                $respuesta = SalasModel::mdlIngresarSala($tabla, $datos);
                
                if($respuesta == "ok") {
                    echo json_encode([
                        "status" => "success",
                        "message" => "La sala ha sido creada correctamente"
                    ]);
                } else {
                    echo json_encode([
                        "status" => "error",
                        "message" => "Error al crear la sala: " . $respuesta
                    ]);
                }
            } else {
                echo json_encode([
                    "status" => "error",
                    "message" => "El código de sala solo puede contener letras, números, guiones y guiones bajos"
                ]);
            }
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "Los campos código y nombre son obligatorios"
            ]);
        }
    }
    
    /**
     * Editar sala existente
     */
    public function ajaxEditarSalaGuardar() {
        // Validar que los campos requeridos no estén vacíos
        if(!empty($_POST["sala_codigo"]) && !empty($_POST["sala_nombre"]) && !empty($_POST["sala_id"])) {
            
            // Validar que el código no contenga espacios ni caracteres especiales
            if(preg_match('/^[a-zA-Z0-9_-]+$/', $_POST["sala_codigo"])) {
                
                $tabla = "salas";
                
                $datos = array(
                    "sala_id" => $_POST["sala_id"],
                    "sala_codigo" => $_POST["sala_codigo"],
                    "sala_nombre" => $_POST["sala_nombre"],
                    "sala_descripcion" => $_POST["sala_descripcion"] ?? null,
                    "sala_estado" => $_POST["sala_estado"] ?? 1
                );
                
                $respuesta = SalasModel::mdlEditarSala($tabla, $datos);
                
                if($respuesta == "ok") {
                    echo json_encode([
                        "status" => "success",
                        "message" => "La sala ha sido actualizada correctamente"
                    ]);
                } else {
                    echo json_encode([
                        "status" => "error",
                        "message" => "Error al actualizar la sala: " . $respuesta
                    ]);
                }
            } else {
                echo json_encode([
                    "status" => "error",
                    "message" => "El código de sala solo puede contener letras, números, guiones y guiones bajos"
                ]);
            }
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "Los campos código, nombre y ID son obligatorios"
            ]);
        }
    }
}

// Procesar las diferentes peticiones AJAX

// Editar sala (obtener datos)
if(isset($_POST["idSala"])) {
    $editar = new AjaxSalas();
    $editar->idSala = $_POST["idSala"];
    $editar->ajaxEditarSala();
    exit;
}

// Validar código de sala
if(isset($_POST["validarCodigoSala"])) {
    $validarCodigo = new AjaxSalas();
    $validarCodigo->codigoSala = $_POST["validarCodigoSala"];
    $validarCodigo->ajaxValidarCodigoSala();
    exit;
}

// Obtener todas las salas
if(isset($_POST["accion"]) && $_POST["accion"] == "obtenerSalas") {
    $obtenerSalas = new AjaxSalas();
    $obtenerSalas->ajaxObtenerSalas();
    exit;
}

// Crear nueva sala
if(isset($_POST["accion"]) && $_POST["accion"] == "crearSala") {
    $crearSala = new AjaxSalas();
    $crearSala->ajaxCrearSala();
    exit;
}

// Editar sala (guardar cambios)
if(isset($_POST["accion"]) && $_POST["accion"] == "editarSala") {
    $editarSala = new AjaxSalas();
    $editarSala->ajaxEditarSalaGuardar();
    exit;
}

?>
