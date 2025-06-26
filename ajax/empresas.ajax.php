<?php

require_once "../controller/EmpresasController.php";
require_once "../model/EmpresasModel.php";

/**
 * Clase para manejar las peticiones Ajax de empresas
 */
class AjaxEmpresas {

    /**
     * Propiedad para ID de empresa
     */
    public $idEmpresa;

    /**
     * Obtener datos de una empresa
     */
    public function ajaxEditarEmpresa() {
        $item = "business_id";
        $valor = $this->idEmpresa;
        $respuesta = EmpresasController::ctrMostrarEmpresas($item, $valor);
        echo json_encode($respuesta);
    }

    /**
     * Activar o desactivar empresa
     */
    public $activarEmpresa;
    public $activarId;

    public function ajaxActivarEmpresa() {
        $tabla = "sys_business";
        $item1 = "business_is_active";
        
        // Convertir a booleano para PostgreSQL
        $valor1 = $this->activarEmpresa;
        if($valor1 === "1" || $valor1 === 1 || $valor1 === true || $valor1 === "true") {
            $valor1 = true; // Para PostgreSQL
        } else {
            $valor1 = false; // Para PostgreSQL
        }
        
        $item2 = "business_id";
        $valor2 = $this->activarId;

        $respuesta = EmpresasModel::mdlActualizarEmpresa($tabla, $item1, $valor1, $item2, $valor2);
    }

    /**
     * Validar si ya existe una empresa con ese RUC
     */
    public $validarRUC;

    public function ajaxValidarRUC() {
        $item = "business_ruc";
        $valor = $this->validarRUC;
        $respuesta = EmpresasController::ctrMostrarEmpresas($item, $valor);
        echo json_encode($respuesta);
    }

    /**
     * Mostrar lista de empresas
     */
    public function ajaxMostrarEmpresas() {
        $respuesta = EmpresasController::ctrMostrarEmpresas(null, null);
        echo json_encode($respuesta);
    }
    
    /**
     * Crear empresa desde petición Ajax
     */
    public function ajaxCrearEmpresa() {
        $datos = array(
            "business_name" => $_POST["nombreEmpresa"],
            "business_ruc" => $_POST["rucEmpresa"],
            "business_email" => $_POST["emailEmpresa"],
            "business_phone" => $_POST["telefonoEmpresa"],
            "business_address" => $_POST["direccionEmpresa"],
            "business_is_active" => $_POST["estadoEmpresa"]
        );
        
        $respuesta = EmpresasModel::mdlIngresarEmpresa("sys_business", $datos);
        echo $respuesta;
    }
    
    /**
     * Actualizar empresa desde petición Ajax
     */
    public function ajaxActualizarEmpresa() {
        // Convertir el estado según lo que viene del formulario
        // Asegurarse que sea booleano o entero según lo que espera PostgreSQL
        $estadoEmpresa = $_POST["editarEstadoEmpresa"];
        if($estadoEmpresa === "1" || $estadoEmpresa === 1 || $estadoEmpresa === true || $estadoEmpresa === "true") {
            $estadoEmpresa = true; // O 1 dependiendo de lo que acepte PostgreSQL
        } else {
            $estadoEmpresa = false; // O 0 dependiendo de lo que acepte PostgreSQL
        }
        
        $datos = array(
            "business_id" => $_POST["idEmpresa"],
            "business_name" => $_POST["editarNombreEmpresa"],
            "business_ruc" => $_POST["editarRucEmpresa"],
            "business_email" => $_POST["editarEmailEmpresa"],
            "business_phone" => $_POST["editarTelefonoEmpresa"],
            "business_address" => $_POST["editarDireccionEmpresa"],
            "business_is_active" => $estadoEmpresa
        );
        
        $respuesta = EmpresasModel::mdlEditarEmpresa("sys_business", $datos);
        echo $respuesta;
    }
    
    /**
     * Eliminar empresa
     */
    public $idEliminar;
    
    public function ajaxEliminarEmpresa() {
        $respuesta = EmpresasModel::mdlBorrarEmpresa("sys_business", $this->idEliminar);
        echo $respuesta;
    }
}

/**
 * Procesar peticiones Ajax
 */

// Editar Empresa (Get data)
if(isset($_POST["idEmpresa"]) && !isset($_POST["editarNombreEmpresa"])) {
    $editar = new AjaxEmpresas();
    $editar->idEmpresa = $_POST["idEmpresa"];
    $editar->ajaxEditarEmpresa();
}

// Activar Empresa
if(isset($_POST["activarEmpresa"])) {
    $activarEmpresa = new AjaxEmpresas();
    $activarEmpresa->activarEmpresa = $_POST["activarEmpresa"];
    $activarEmpresa->activarId = $_POST["activarId"];
    $activarEmpresa->ajaxActivarEmpresa();
}

// Validar RUC existente
if(isset($_POST["validarRUC"])) {
    $validarRUC = new AjaxEmpresas();
    $validarRUC->validarRUC = $_POST["validarRUC"];
    $validarRUC->ajaxValidarRUC();
}

// Mostrar empresas
if(isset($_POST["mostrarEmpresas"])) {
    $mostrar = new AjaxEmpresas();
    $mostrar->ajaxMostrarEmpresas();
}

// Crear empresa
if(isset($_POST["nombreEmpresa"]) && isset($_POST["rucEmpresa"]) && !isset($_POST["idEmpresa"])) {
    $crearEmpresa = new AjaxEmpresas();
    $crearEmpresa->ajaxCrearEmpresa();
}

// Actualizar empresa
if(isset($_POST["editarNombreEmpresa"]) && isset($_POST["idEmpresa"])) {
    $actualizarEmpresa = new AjaxEmpresas();
    $actualizarEmpresa->ajaxActualizarEmpresa();
}

// Eliminar empresa
if(isset($_POST["idEliminarEmpresa"])) {
    $eliminarEmpresa = new AjaxEmpresas();
    $eliminarEmpresa->idEliminar = $_POST["idEliminarEmpresa"];
    $eliminarEmpresa->ajaxEliminarEmpresa();
}
