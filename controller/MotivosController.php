<?php

if (file_exists("model/MotivosModel.php")) {
    // When included from index.php or similar root-level file
    require_once "model/MotivosModel.php";
} else if (file_exists("../model/MotivosModel.php")) {
    // When included from an AJAX file in a subdirectory
    require_once "../model/MotivosModel.php";
}

class MotivosController {
    
    /*=============================================
    MOSTRAR TODOS LOS MOTIVOS COMUNES
    =============================================*/
    static public function ctrMostrarMotivos() {
        return MotivosModel::mdlObtenerMotivos();
    }
    
    /*=============================================
    CREAR MOTIVO COMÚN
    =============================================*/
    static public function ctrCrearMotivo() {
        if(isset($_POST["nuevoMotivo"])) {
            if(preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚüÜ\s.,\-]+$/', $_POST["nuevoMotivo"])) {
                
                $descripcion = isset($_POST["nuevaDescripcionMotivo"]) ? $_POST["nuevaDescripcionMotivo"] : '';
                $estado = isset($_POST["estadoMotivo"]) ? $_POST["estadoMotivo"] : 1;
                $tipo_formulario = isset($_POST["tipoFormularioMotivo"]) ? $_POST["tipoFormularioMotivo"] : 'general';
                
                // Obtener el ID del usuario que crea el motivo (si está disponible)
                $creado_por = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
                
                $resultado = MotivosModel::mdlCrearMotivo($_POST["nuevoMotivo"], $descripcion, $estado, $creado_por, $tipo_formulario);
                
                if($resultado["status"] == "ok") {
                    echo '<script>
                        swal({
                            type: "success",
                            title: "¡El motivo ha sido guardado correctamente!",
                            showConfirmButton: true,
                            confirmButtonText: "Cerrar"
                        }).then(function(result){
                            if(result.value){
                                window.location = "motivos";
                            }
                        });
                    </script>';
                }
                
            } else {
                echo '<script>
                    swal({
                        type: "error",
                        title: "¡El motivo no puede ir vacío o llevar caracteres especiales no permitidos!",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    }).then(function(result){
                        if(result.value){
                            window.location = "motivos";
                        }
                    });
                </script>';
            }
        }
    }
    
    /*=============================================
    EDITAR MOTIVO COMÚN
    =============================================*/
    static public function ctrEditarMotivo() {
        if(isset($_POST["editarMotivo"])) {
            if(preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚüÜ\s.,\-]+$/', $_POST["editarMotivo"])) {
                
                $id = $_POST["idMotivo"];
                $nombre = $_POST["editarMotivo"];
                $descripcion = isset($_POST["editarDescripcionMotivo"]) ? $_POST["editarDescripcionMotivo"] : '';
                $estado = isset($_POST["editarEstadoMotivo"]) ? $_POST["editarEstadoMotivo"] : 1;
                $tipo_formulario = isset($_POST["editarTipoFormularioMotivo"]) ? $_POST["editarTipoFormularioMotivo"] : 'general';
                
                $resultado = MotivosModel::mdlActualizarMotivo($id, $nombre, $descripcion, $estado, $tipo_formulario);

                if($resultado["status"] == "ok") {
                    echo '<script>
                        swal({
                            type: "success",
                            title: "¡El motivo ha sido actualizado correctamente!",
                            showConfirmButton: true,
                            confirmButtonText: "Cerrar"
                        }).then(function(result){
                            if(result.value){
                                window.location = "motivos";
                            }
                        });
                    </script>';
                }
                
            } else {
                echo '<script>
                    swal({
                        type: "error",
                        title: "¡El motivo no puede ir vacío o llevar caracteres especiales no permitidos!",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    }).then(function(result){
                        if(result.value){
                            window.location = "motivos";
                        }
                    });
                </script>';
            }
        }
    }
    
    /*=============================================
    BORRAR MOTIVO COMÚN
    =============================================*/
    static public function ctrBorrarMotivo() {
        if(isset($_GET["idMotivo"])) {
            $resultado = MotivosModel::mdlEliminarMotivo($_GET["idMotivo"]);
            
            if($resultado["status"] == "ok") {
                echo '<script>
                    swal({
                        type: "success",
                        title: "¡El motivo ha sido eliminado correctamente!",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    }).then(function(result){
                        if(result.value){
                            window.location = "motivos";
                        }
                    });
                </script>';
            }
        }
    }
}
