<?php

require_once "../controller/ProfesionesController.php";
require_once "../model/ProfesionesModel.php";

class AjaxProfesiones {
    
    /*=============================================
    OBTENER TODAS LAS PROFESIONES
    =============================================*/
    public function ajaxObtenerProfesiones() {
        $profesiones = ProfesionesController::ctrMostrarProfesiones();
        echo json_encode($profesiones);
    }
    
    /*=============================================
    CREAR PROFESIÓN
    =============================================*/
    public function ajaxCrearProfesion() {
        if(isset($_POST['nuevaProfesion'])) {
            $resultado = ProfesionesModel::mdlCrearProfesion($_POST['nuevaProfesion']);
            echo $resultado;
        }
    }
    
    /*=============================================
    ACTUALIZAR PROFESIÓN
    =============================================*/
    public function ajaxActualizarProfesion() {
        if(isset($_POST['idProfesion']) && isset($_POST['editarProfesion'])) {
            $resultado = ProfesionesModel::mdlActualizarProfesion($_POST['idProfesion'], $_POST['editarProfesion']);
            echo $resultado;
        }
    }
    
    /*=============================================
    ELIMINAR PROFESIÓN
    =============================================*/
    public function ajaxEliminarProfesion() {
        if(isset($_POST['idProfesion'])) {
            $resultado = ProfesionesModel::mdlEliminarProfesion($_POST['idProfesion']);
            echo $resultado;
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
        case 'actualizar':
            $profesiones->ajaxActualizarProfesion();
            break;
        case 'eliminar':
            $profesiones->ajaxEliminarProfesion();
            break;
    }
}