<?php

require_once "../controller/TiposProveedoresController.php";
require_once "../model/TiposProveedoresModel.php";

/**
 * Clase para manejar las peticiones Ajax de tipos de proveedores
 */
class AjaxTiposProveedores {

    /**
     * Propiedad para ID del tipo de proveedor
     */
    public $idTipoProveedor;

    /**
     * Obtener datos de un tipo de proveedor
     */
    public function ajaxEditarTipoProveedor() {
        $item = "tipo_cod";
        $valor = $this->idTipoProveedor;
        $respuesta = TiposProveedoresController::ctrMostrarTiposProveedores($item, $valor);
        echo json_encode($respuesta);
    }

    /**
     * Validar si ya existe un tipo de proveedor con ese nombre
     */
    public $validarNombre;

    public function ajaxValidarNombre() {
        $item = "tipo_nombre";
        $valor = $this->validarNombre;
        $respuesta = TiposProveedoresController::ctrMostrarTiposProveedores($item, $valor);
        echo json_encode($respuesta);
    }

    /**
     * Mostrar lista de tipos de proveedores
     */
    public function ajaxMostrarTiposProveedores() {
        $respuesta = TiposProveedoresController::ctrMostrarTiposProveedores(null, null);
        echo json_encode($respuesta);
    }
    
    /**
     * Crear tipo de proveedor desde petición Ajax
     */
    public function ajaxCrearTipoProveedor() {
        $datos = array(
            "tipo_nombre" => $_POST["nombreTipoProveedor"],
            "descripcion" => $_POST["descripcionTipoProveedor"]
        );
        
        $respuesta = TiposProveedoresModel::mdlIngresarTipoProveedor("cm_tipos_proveedores", $datos);
        echo $respuesta;
    }
    
    /**
     * Actualizar tipo de proveedor desde petición Ajax
     */
    public function ajaxActualizarTipoProveedor() {
        $datos = array(
            "tipo_cod" => $_POST["idTipoProveedor"],
            "tipo_nombre" => $_POST["editarNombreTipoProveedor"],
            "descripcion" => $_POST["editarDescripcionTipoProveedor"]
        );
        
        $respuesta = TiposProveedoresModel::mdlEditarTipoProveedor("cm_tipos_proveedores", $datos);
        echo $respuesta;
    }
    
    /**
     * Eliminar tipo de proveedor
     */
    public $idEliminar;
    
    public function ajaxEliminarTipoProveedor() {
        $respuesta = TiposProveedoresModel::mdlBorrarTipoProveedor("cm_tipos_proveedores", $this->idEliminar);
        echo $respuesta;
    }
}

/**
 * Procesar peticiones Ajax
 */

// Editar Tipo de Proveedor (Get data)
if(isset($_POST["idTipoProveedor"]) && !isset($_POST["editarNombreTipoProveedor"])) {
    $editar = new AjaxTiposProveedores();
    $editar->idTipoProveedor = $_POST["idTipoProveedor"];
    $editar->ajaxEditarTipoProveedor();
}

// Validar nombre existente
if(isset($_POST["validarNombre"])) {
    $validarNombre = new AjaxTiposProveedores();
    $validarNombre->validarNombre = $_POST["validarNombre"];
    $validarNombre->ajaxValidarNombre();
}

// Mostrar tipos de proveedores
if(isset($_POST["mostrarTiposProveedores"]) || (isset($_POST["accion"]) && $_POST["accion"] == "mostrarTodos")) {
    $mostrar = new AjaxTiposProveedores();
    $mostrar->ajaxMostrarTiposProveedores();
}

// Crear tipo de proveedor
if(isset($_POST["nombreTipoProveedor"]) && !isset($_POST["idTipoProveedor"])) {
    $crearTipoProveedor = new AjaxTiposProveedores();
    $crearTipoProveedor->ajaxCrearTipoProveedor();
}

// Actualizar tipo de proveedor
if(isset($_POST["editarNombreTipoProveedor"]) && isset($_POST["idTipoProveedor"])) {
    $actualizarTipoProveedor = new AjaxTiposProveedores();
    $actualizarTipoProveedor->ajaxActualizarTipoProveedor();
}

// Eliminar tipo de proveedor
if(isset($_POST["idEliminarTipoProveedor"])) {
    $eliminarTipoProveedor = new AjaxTiposProveedores();
    $eliminarTipoProveedor->idEliminar = $_POST["idEliminarTipoProveedor"];
    $eliminarTipoProveedor->ajaxEliminarTipoProveedor();
}
