<?php

class ControladorTipoFormularios {

    /*=============================================
    CREAR TIPO DE FORMULARIO
    =============================================*/
    static public function ctrCrearTipoFormulario() {

        if(isset($_POST["nuevoNombre"])) {

            if(preg_match('/^[a-zA-ZñÑáéíóúÁÉÍÓÚ ]+$/', $_POST["nuevoNombre"]) &&
               preg_match('/^[a-zA-Z0-9_]+$/', $_POST["nuevoCodigo"])) {

                // Validar que no exista el código
                $codigoExiste = ModeloTipoFormularios::mdlVerificarCodigoExistente("tipos_formularios", $_POST["nuevoCodigo"]);
                
                if($codigoExiste) {
                    echo '<script>
                        swal({
                            type: "error",
                            title: "¡El código ya existe!",
                            text: "¡El código del tipo de formulario ya está en uso, por favor elija otro!",
                            showConfirmButton: true,
                            confirmButtonText: "Cerrar"
                        }).then(function(result){
                            if (result.value) {
                                window.location = "preformatos";
                            }
                        })
                    </script>';
                    return;
                }

                // Validar que no exista el nombre
                $nombreExiste = ModeloTipoFormularios::mdlVerificarNombreExistente("tipos_formularios", $_POST["nuevoNombre"]);
                
                if($nombreExiste) {
                    echo '<script>
                        swal({
                            type: "error",
                            title: "¡El nombre ya existe!",
                            text: "¡El nombre del tipo de formulario ya está en uso, por favor elija otro!",
                            showConfirmButton: true,
                            confirmButtonText: "Cerrar"
                        }).then(function(result){
                            if (result.value) {
                                window.location = "preformatos";
                            }
                        })
                    </script>';
                    return;
                }

                $tabla = "tipos_formularios";

                $datos = array("nombre" => $_POST["nuevoNombre"],
                              "codigo" => strtolower($_POST["nuevoCodigo"]),
                              "descripcion" => $_POST["nuevaDescripcion"]);

                $respuesta = ModeloTipoFormularios::mdlIngresarTipoFormulario($tabla, $datos);

                if($respuesta == "ok") {

                    echo '<script>
                        swal({
                            type: "success",
                            title: "¡El tipo de formulario ha sido guardado correctamente!",
                            showConfirmButton: true,
                            confirmButtonText: "Cerrar"
                        }).then(function(result){
                            if (result.value) {
                                window.location = "preformatos";
                            }
                        })
                    </script>';

                }

            } else {

                echo '<script>
                    swal({
                        type: "error",
                        title: "¡El tipo de formulario no puede ir vacío o llevar caracteres especiales!",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    }).then(function(result){
                        if (result.value) {
                            window.location = "preformatos";
                        }
                    })
                </script>';

            }

        }

    }

    /*=============================================
    MOSTRAR TIPOS DE FORMULARIOS
    =============================================*/
    static public function ctrMostrarTipoFormularios($item, $valor) {

        $tabla = "tipos_formularios";

        $respuesta = ModeloTipoFormularios::mdlMostrarTipoFormularios($tabla, $item, $valor);

        return $respuesta;

    }

    /*=============================================
    OBTENER TIPOS DE FORMULARIOS PARA SELECT
    =============================================*/
    static public function ctrObtenerTiposFormulariosSelect() {

        $tabla = "tipos_formularios";

        $respuesta = ModeloTipoFormularios::mdlObtenerTiposFormulariosSelect($tabla);

        return $respuesta;

    }

    /*=============================================
    EDITAR TIPO DE FORMULARIO
    =============================================*/
    static public function ctrEditarTipoFormulario() {

        if(isset($_POST["editarNombre"])) {

            if(preg_match('/^[a-zA-ZñÑáéíóúÁÉÍÓÚ ]+$/', $_POST["editarNombre"]) &&
               preg_match('/^[a-zA-Z0-9_]+$/', $_POST["editarCodigo"])) {

                // Validar que no exista el código (excluyendo el registro actual)
                $codigoExiste = ModeloTipoFormularios::mdlVerificarCodigoExistente("tipos_formularios", $_POST["editarCodigo"], $_POST["editarId"]);
                
                if($codigoExiste) {
                    echo '<script>
                        swal({
                            type: "error",
                            title: "¡El código ya existe!",
                            text: "¡El código del tipo de formulario ya está en uso, por favor elija otro!",
                            showConfirmButton: true,
                            confirmButtonText: "Cerrar"
                        }).then(function(result){
                            if (result.value) {
                                window.location = "preformatos";
                            }
                        })
                    </script>';
                    return;
                }

                // Validar que no exista el nombre (excluyendo el registro actual)
                $nombreExiste = ModeloTipoFormularios::mdlVerificarNombreExistente("tipos_formularios", $_POST["editarNombre"], $_POST["editarId"]);
                
                if($nombreExiste) {
                    echo '<script>
                        swal({
                            type: "error",
                            title: "¡El nombre ya existe!",
                            text: "¡El nombre del tipo de formulario ya está en uso, por favor elija otro!",
                            showConfirmButton: true,
                            confirmButtonText: "Cerrar"
                        }).then(function(result){
                            if (result.value) {
                                window.location = "preformatos";
                            }
                        })
                    </script>';
                    return;
                }

                $tabla = "tipos_formularios";

                $datos = array("nombre" => $_POST["editarNombre"],
                              "codigo" => strtolower($_POST["editarCodigo"]),
                              "descripcion" => $_POST["editarDescripcion"],
                              "id" => $_POST["editarId"]);

                $respuesta = ModeloTipoFormularios::mdlEditarTipoFormulario($tabla, $datos);

                if($respuesta == "ok") {

                    echo '<script>
                        swal({
                            type: "success",
                            title: "¡El tipo de formulario ha sido editado correctamente!",
                            showConfirmButton: true,
                            confirmButtonText: "Cerrar"
                        }).then(function(result){
                            if (result.value) {
                                window.location = "preformatos";
                            }
                        })
                    </script>';

                }

            } else {

                echo '<script>
                    swal({
                        type: "error",
                        title: "¡El tipo de formulario no puede ir vacío o llevar caracteres especiales!",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    }).then(function(result){
                        if (result.value) {
                            window.location = "preformatos";
                        }
                    })
                </script>';

            }

        }

    }

    /*=============================================
    BORRAR TIPO DE FORMULARIO
    =============================================*/
    static public function ctrBorrarTipoFormulario() {

        if(isset($_GET["idTipoFormulario"])) {

            $tabla = "tipos_formularios";
            $datos = $_GET["idTipoFormulario"];

            $respuesta = ModeloTipoFormularios::mdlBorrarTipoFormulario($tabla, $datos);

            if($respuesta == "ok") {

                echo '<script>
                    swal({
                        type: "success",
                        title: "¡El tipo de formulario ha sido borrado correctamente!",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    }).then(function(result){
                        if (result.value) {
                            window.location = "preformatos";
                        }
                    })
                </script>';

            }

        }

    }

    /*=============================================
    ACTIVAR TIPO DE FORMULARIO
    =============================================*/
    static public function ctrActivarTipoFormulario() {

        if(isset($_GET["activarTipoFormulario"])) {

            $tabla = "tipos_formularios";

            $item1 = "activo";
            $valor1 = $_GET["activarTipoFormulario"];

            $item2 = "id";
            $valor2 = $_GET["idTipoFormulario"];

            $respuesta = ModeloTipoFormularios::mdlActivarTipoFormulario($tabla, $item1, $valor1, $item2, $valor2);

            if($respuesta == "ok") {

                echo '<script>
                    swal({
                        type: "success",
                        title: "¡El tipo de formulario ha sido actualizado correctamente!",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    }).then(function(result){
                        if (result.value) {
                            window.location = "preformatos";
                        }
                    })
                </script>';

            }

        }

    }

}
