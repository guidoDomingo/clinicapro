<?php

if (file_exists("model/ProfesionesModel.php")) {
    // When included from index.php or similar root-level file
    require_once "model/ProfesionesModel.php";
} else if (file_exists("../model/ProfesionesModel.php")) {
    // When included from an AJAX file in a subdirectory
    require_once "../model/ProfesionesModel.php";
}

class ProfesionesController {
    
    /*=============================================
    MOSTRAR TODAS LAS PROFESIONES
    =============================================*/
    static public function ctrMostrarProfesiones() {
        return ProfesionesModel::mdlObtenerProfesiones();
    }
    
    /*=============================================
    CREAR PROFESION
    =============================================*/
    static public function ctrCrearProfesion() {
        if(isset($_POST["nuevaProfesion"])) {
            if(preg_match('/^[a-zA-ZñÑáéíóúÁÉÍÓÚüÜ\s]+$/', $_POST["nuevaProfesion"])) {
                
                $resultado = ProfesionesModel::mdlCrearProfesion($_POST["nuevaProfesion"]);
                
                if($resultado == "ok") {
                    echo '<script>
                        swal({
                            type: "success",
                            title: "¡La profesión ha sido guardada correctamente!",
                            showConfirmButton: true,
                            confirmButtonText: "Cerrar"
                        }).then(function(result){
                            if(result.value){
                                window.location = "profesiones";
                            }
                        });
                    </script>';
                }
                
            } else {
                echo '<script>
                    swal({
                        type: "error",
                        title: "¡La profesión no puede ir vacía o llevar caracteres especiales!",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    }).then(function(result){
                        if(result.value){
                            window.location = "profesiones";
                        }
                    });
                </script>';
            }
        }
    }
    
    /*=============================================
    EDITAR PROFESION
    =============================================*/
    static public function ctrEditarProfesion() {
        if(isset($_POST["editarProfesion"])) {
            if(preg_match('/^[a-zA-ZñÑáéíóúÁÉÍÓÚüÜ\s]+$/', $_POST["editarProfesion"])) {
                
                $resultado = ProfesionesModel::mdlActualizarProfesion($_POST["idProfesion"], $_POST["editarProfesion"], $_POST["estado"]);

                if($resultado["status"] == "ok") {
                    echo '<script>
                        swal({
                            type: "success",
                            title: "¡La profesión ha sido actualizada correctamente!",
                            showConfirmButton: true,
                            confirmButtonText: "Cerrar"
                        }).then(function(result){
                            if(result.value){
                                window.location = "profesiones";
                            }
                        });
                    </script>';
                }
                
            } else {
                echo '<script>
                    swal({
                        type: "error",
                        title: "¡La profesión no puede ir vacía o llevar caracteres especiales!",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    }).then(function(result){
                        if(result.value){
                            window.location = "profesiones";
                        }
                    });
                </script>';
            }
        }
    }
    
    /*=============================================
    BORRAR PROFESION
    =============================================*/
    static public function ctrBorrarProfesion() {
        if(isset($_GET["idProfesion"])) {
            $resultado = ProfesionesModel::mdlEliminarProfesion($_GET["idProfesion"]);
            
            if($resultado == "ok") {
                echo '<script>
                    swal({
                        type: "success",
                        title: "¡La profesión ha sido eliminada correctamente!",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    }).then(function(result){
                        if(result.value){
                            window.location = "profesiones";
                        }
                    });
                </script>';
            }
        }
    }
}