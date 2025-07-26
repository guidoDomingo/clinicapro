<?php

require_once "../controller/tipo_formularios.controller.php";
require_once "../model/tipo_formularios.model.php";

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
    ACTIVAR TIPO DE FORMULARIO
    =============================================*/
    public $activarTipoFormulario;
    public $activarId;

    public function ajaxActivarTipoFormulario() {

        $tabla = "tipos_formularios";

        $item1 = "activo";
        $valor1 = $this->activarTipoFormulario;

        $item2 = "id";
        $valor2 = $this->activarId;

        $respuesta = ModeloTipoFormularios::mdlActivarTipoFormulario($tabla, $item1, $valor1, $item2, $valor2);

        echo $respuesta;

    }

    /*=============================================
    VALIDAR CÓDIGO ÚNICO
    =============================================*/
    public $validarCodigo;
    public $validarId;

    public function ajaxValidarCodigo() {

        $tabla = "tipos_formularios";
        $codigo = $this->validarCodigo;
        $id = $this->validarId;

        $respuesta = ModeloTipoFormularios::mdlVerificarCodigoExistente($tabla, $codigo, $id);

        echo json_encode($respuesta);

    }

    /*=============================================
    VALIDAR NOMBRE ÚNICO
    =============================================*/
    public $validarNombre;
    public $validarIdNombre;

    public function ajaxValidarNombre() {

        $tabla = "tipos_formularios";
        $nombre = $this->validarNombre;
        $id = $this->validarIdNombre;

        $respuesta = ModeloTipoFormularios::mdlVerificarNombreExistente($tabla, $nombre, $id);

        echo json_encode($respuesta);

    }

}

/*=============================================
EDITAR TIPO DE FORMULARIO
=============================================*/
if(isset($_POST["idTipoFormulario"])) {

    $editarTipoFormulario = new AjaxTipoFormularios();
    $editarTipoFormulario->idTipoFormulario = $_POST["idTipoFormulario"];
    $editarTipoFormulario->ajaxEditarTipoFormulario();

}

/*=============================================
ACTIVAR TIPO DE FORMULARIO
=============================================*/
if(isset($_POST["activarTipoFormulario"])) {

    $activarTipoFormulario = new AjaxTipoFormularios();
    $activarTipoFormulario->activarTipoFormulario = $_POST["activarTipoFormulario"];
    $activarTipoFormulario->activarId = $_POST["activarId"];
    $activarTipoFormulario->ajaxActivarTipoFormulario();

}

/*=============================================
VALIDAR CÓDIGO ÚNICO
=============================================*/
if(isset($_POST["validarCodigo"])) {

    $validarCodigo = new AjaxTipoFormularios();
    $validarCodigo->validarCodigo = $_POST["validarCodigo"];
    $validarCodigo->validarId = $_POST["validarId"];
    $validarCodigo->ajaxValidarCodigo();

}

/*=============================================
VALIDAR NOMBRE ÚNICO
=============================================*/
if(isset($_POST["validarNombre"])) {

    $validarNombre = new AjaxTipoFormularios();
    $validarNombre->validarNombre = $_POST["validarNombre"];
    $validarNombre->validarIdNombre = $_POST["validarIdNombre"];
    $validarNombre->ajaxValidarNombre();

}
