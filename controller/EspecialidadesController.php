<?php

if (file_exists("model/EspecialidadesModel.php")) {
    // When included from index.php or similar root-level file
    require_once "model/EspecialidadesModel.php";
} else if (file_exists("../model/EspecialidadesModel.php")) {
    // When included from an AJAX file in a subdirectory
    require_once "../model/EspecialidadesModel.php";
}

class EspecialidadesController {
    
    /*=============================================
    MOSTRAR TODAS LAS ESPECIALIDADES
    =============================================*/
    static public function ctrMostrarEspecialidades() {
        return EspecialidadesModel::mdlObtenerEspecialidades();
    }
    
    /*=============================================
    CREAR ESPECIALIDAD
    =============================================*/
    static public function ctrCrearEspecialidad() {
        if(isset($_POST["nuevaEspecialidad"])) {
            if(preg_match('/^[a-zA-ZñÑáéíóúÁÉÍÓÚüÜ\s]+$/', $_POST["nuevaEspecialidad"])) {
                
                $descripcion = isset($_POST["nuevaDescripcion"]) ? $_POST["nuevaDescripcion"] : '';
                $estado = isset($_POST["estadoEspecialidad"]) ? $_POST["estadoEspecialidad"] : 1;
                
                $resultado = EspecialidadesModel::mdlCrearEspecialidad($_POST["nuevaEspecialidad"], $descripcion, $estado);
                
                if($resultado["status"] == "ok") {
                    echo '<script>
                        swal({
                            type: "success",
                            title: "¡La especialidad ha sido guardada correctamente!",
                            showConfirmButton: true,
                            confirmButtonText: "Cerrar"
                        }).then(function(result){
                            if(result.value){
                                window.location = "especialidades";
                            }
                        });
                    </script>';
                }
                
            } else {
                echo '<script>
                    swal({
                        type: "error",
                        title: "¡La especialidad no puede ir vacía o llevar caracteres especiales!",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    }).then(function(result){
                        if(result.value){
                            window.location = "especialidades";
                        }
                    });
                </script>';
            }
        }
    }
    
    /*=============================================
    EDITAR ESPECIALIDAD
    =============================================*/
    static public function ctrEditarEspecialidad() {
        if(isset($_POST["editarEspecialidad"])) {
            if(preg_match('/^[a-zA-ZñÑáéíóúÁÉÍÓÚüÜ\s]+$/', $_POST["editarEspecialidad"])) {
                
                $id = $_POST["idEspecialidad"];
                $nombre = $_POST["editarEspecialidad"];
                $descripcion = isset($_POST["editarDescripcion"]) ? $_POST["editarDescripcion"] : '';
                $estado = isset($_POST["editarEstadoEspecialidad"]) ? $_POST["editarEstadoEspecialidad"] : 1;
                
                $resultado = EspecialidadesModel::mdlActualizarEspecialidad($id, $nombre, $descripcion, $estado);

                if($resultado["status"] == "ok") {
                    echo '<script>
                        swal({
                            type: "success",
                            title: "¡La especialidad ha sido actualizada correctamente!",
                            showConfirmButton: true,
                            confirmButtonText: "Cerrar"
                        }).then(function(result){
                            if(result.value){
                                window.location = "especialidades";
                            }
                        });
                    </script>';
                }
                
            } else {
                echo '<script>
                    swal({
                        type: "error",
                        title: "¡La especialidad no puede ir vacía o llevar caracteres especiales!",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    }).then(function(result){
                        if(result.value){
                            window.location = "especialidades";
                        }
                    });
                </script>';
            }
        }
    }
    
    /*=============================================
    BORRAR ESPECIALIDAD
    =============================================*/
    static public function ctrBorrarEspecialidad() {
        if(isset($_GET["idEspecialidad"])) {
            $resultado = EspecialidadesModel::mdlEliminarEspecialidad($_GET["idEspecialidad"]);
            
            if($resultado["status"] == "ok") {
                echo '<script>
                    swal({
                        type: "success",
                        title: "¡La especialidad ha sido eliminada correctamente!",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    }).then(function(result){
                        if(result.value){
                            window.location = "especialidades";
                        }
                    });
                </script>';
            }
        }
    }
}
