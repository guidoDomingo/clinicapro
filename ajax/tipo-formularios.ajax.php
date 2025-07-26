<?php

require_once "../controller/tipo-formularios.controller.php";
require_once "../model/tipo-formularios.model.php";

class AjaxTipoFormularios {

    /*=============================================
    EDITAR TIPO DE FORMULARIO
    =============================================*/
    public $idTipoFormulario;

    public function ajaxEditarTipoFormulario() {
        $item = "id";
        $valor = $this->idTipoFormulario;
        
        $respuesta = ControladorTipoFormularios::ctrMostrarTipoFormularios($item, $valor);
        
        echo json_encode($respuesta);
    }

    /*=============================================
    ACTIVAR/DESACTIVAR TIPO DE FORMULARIO
    =============================================*/
    public $activarTipoFormulario;
    public $activarId;

    public function ajaxActivarTipoFormulario() {
        $tabla = "tipo_formularios";
        
        $item1 = "activo";
        $valor1 = $this->activarTipoFormulario;
        
        $item2 = "id";
        $valor2 = $this->activarId;

        $respuesta = ModeloTipoFormularios::mdlActivarTipoFormulario($tabla, array(
            "id" => $valor2,
            "activo" => $valor1,
            "modificado_por" => $_SESSION["user_id"]
        ));

        echo $respuesta;
    }

    /*=============================================
    VALIDAR CÓDIGO ÚNICO
    =============================================*/
    public $validarCodigo;
    public $validarId;

    public function ajaxValidarCodigo() {
        $tabla = "tipo_formularios";
        
        $existe = ModeloTipoFormularios::mdlVerificarCodigo($tabla, $this->validarCodigo, $this->validarId);
        
        echo json_encode($existe);
    }

    /*=============================================
    VALIDAR NOMBRE ÚNICO
    =============================================*/
    public $validarNombre;

    public function ajaxValidarNombre() {
        $tabla = "tipo_formularios";
        
        $existe = ModeloTipoFormularios::mdlVerificarNombre($tabla, $this->validarNombre, $this->validarId);
        
        echo json_encode($existe);
    }

    /*=============================================
    OBTENER TIPOS PARA SELECT
    =============================================*/
    public $obtenerTiposSelect;

    public function ajaxObtenerTiposSelect() {
        $respuesta = ControladorTipoFormularios::ctrObtenerTiposFormulariosSelect();
        
        echo json_encode($respuesta);
    }
}

/*=============================================
EDITAR TIPO DE FORMULARIO
=============================================*/
if(isset($_POST["idTipoFormulario"])) {
    $editar = new AjaxTipoFormularios();
    $editar -> idTipoFormulario = $_POST["idTipoFormulario"];
    $editar -> ajaxEditarTipoFormulario();
}

/*=============================================
ACTIVAR/DESACTIVAR TIPO DE FORMULARIO
=============================================*/
if(isset($_POST["activarTipoFormulario"])) {
    $activarTipoFormulario = new AjaxTipoFormularios();
    $activarTipoFormulario -> activarTipoFormulario = $_POST["activarTipoFormulario"];
    $activarTipoFormulario -> activarId = $_POST["idTipoFormulario"];
    $activarTipoFormulario -> ajaxActivarTipoFormulario();
}

/*=============================================
VALIDAR CÓDIGO ÚNICO
=============================================*/
if(isset($_POST["validarCodigo"])) {
    $validarCodigo = new AjaxTipoFormularios();
    $validarCodigo -> validarCodigo = $_POST["validarCodigo"];
    $validarCodigo -> validarId = isset($_POST["validarId"]) ? $_POST["validarId"] : null;
    $validarCodigo -> ajaxValidarCodigo();
}

/*=============================================
VALIDAR NOMBRE ÚNICO
=============================================*/
if(isset($_POST["validarNombre"])) {
    $validarNombre = new AjaxTipoFormularios();
    $validarNombre -> validarNombre = $_POST["validarNombre"];
    $validarNombre -> validarId = isset($_POST["validarId"]) ? $_POST["validarId"] : null;
    $validarNombre -> ajaxValidarNombre();
}

/*=============================================
OBTENER TIPOS PARA SELECT
=============================================*/
if(isset($_POST["obtenerTiposSelect"])) {
    $obtenerTipos = new AjaxTipoFormularios();
    $obtenerTipos -> ajaxObtenerTiposSelect();
}
