<?php

class ControladorTipoFormularios {

    /*=============================================
    MOSTRAR TIPOS DE FORMULARIOS
    =============================================*/
    static public function ctrMostrarTipoFormularios($item, $valor) {
        $tabla = "tipo_formularios";
        $respuesta = ModeloTipoFormularios::mdlMostrarTipoFormularios($tabla, $item, $valor);
        return $respuesta;
    }

    /*=============================================
    CREAR TIPO DE FORMULARIO
    =============================================*/
    static public function ctrCrearTipoFormulario() {
        if(isset($_POST["nuevoNombre"])) {
            
            // Validar que no existan campos vacíos
            if(preg_match('/^[a-zA-ZñÑáéíóúÁÉÍÓÚ0-9\s]+$/', $_POST["nuevoNombre"]) &&
               preg_match('/^[a-zA-Z0-9_]+$/', $_POST["nuevoCodigo"])) {
                
                $tabla = "tipo_formularios";
                
                // Verificar que el código no exista
                if(!ModeloTipoFormularios::mdlVerificarCodigo($tabla, $_POST["nuevoCodigo"])) {
                    
                    // Verificar que el nombre no exista
                    if(!ModeloTipoFormularios::mdlVerificarNombre($tabla, $_POST["nuevoNombre"])) {
                        
                        $datos = array(
                            "nombre" => $_POST["nuevoNombre"],
                            "descripcion" => $_POST["nuevaDescripcion"],
                            "codigo" => strtolower($_POST["nuevoCodigo"]),
                            "creado_por" => $_SESSION["user_id"]
                        );

                        $respuesta = ModeloTipoFormularios::mdlIngresarTipoFormulario($tabla, $datos);

                        if($respuesta == "ok") {
                            echo '<script>
                                Swal.fire({
                                    icon: "success",
                                    title: "¡El tipo de formulario ha sido guardado correctamente!",
                                    showConfirmButton: true,
                                    confirmButtonText: "Cerrar"
                                }).then(function(result) {
                                    if (result.value) {
                                        window.location = "preformatos";
                                    }
                                })
                            </script>';
                        }
                    } else {
                        echo '<script>
                            Swal.fire({
                                icon: "error",
                                title: "¡El nombre ya existe!",
                                text: "Por favor ingrese un nombre diferente",
                                showConfirmButton: true,
                                confirmButtonText: "Cerrar"
                            })
                        </script>';
                    }
                } else {
                    echo '<script>
                        Swal.fire({
                            icon: "error",
                            title: "¡El código ya existe!",
                            text: "Por favor ingrese un código diferente",
                            showConfirmButton: true,
                            confirmButtonText: "Cerrar"
                        })
                    </script>';
                }
            } else {
                echo '<script>
                    Swal.fire({
                        icon: "error",
                        title: "¡El nombre o código no puede ir vacío o llevar caracteres especiales!",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    })
                </script>';
            }
        }
    }

    /*=============================================
    EDITAR TIPO DE FORMULARIO
    =============================================*/
    static public function ctrEditarTipoFormulario() {
        if(isset($_POST["editarNombre"])) {
            
            // Validar que no existan campos vacíos
            if(preg_match('/^[a-zA-ZñÑáéíóúÁÉÍÓÚ0-9\s]+$/', $_POST["editarNombre"]) &&
               preg_match('/^[a-zA-Z0-9_]+$/', $_POST["editarCodigo"])) {
                
                $tabla = "tipo_formularios";
                
                // Verificar que el código no exista (excluyendo el actual)
                if(!ModeloTipoFormularios::mdlVerificarCodigo($tabla, $_POST["editarCodigo"], $_POST["editarId"])) {
                    
                    // Verificar que el nombre no exista (excluyendo el actual)
                    if(!ModeloTipoFormularios::mdlVerificarNombre($tabla, $_POST["editarNombre"], $_POST["editarId"])) {
                        
                        $datos = array(
                            "id" => $_POST["editarId"],
                            "nombre" => $_POST["editarNombre"],
                            "descripcion" => $_POST["editarDescripcion"],
                            "codigo" => strtolower($_POST["editarCodigo"]),
                            "modificado_por" => $_SESSION["user_id"]
                        );

                        $respuesta = ModeloTipoFormularios::mdlEditarTipoFormulario($tabla, $datos);

                        if($respuesta == "ok") {
                            echo '<script>
                                Swal.fire({
                                    icon: "success",
                                    title: "¡El tipo de formulario ha sido editado correctamente!",
                                    showConfirmButton: true,
                                    confirmButtonText: "Cerrar"
                                }).then(function(result) {
                                    if (result.value) {
                                        window.location = "preformatos";
                                    }
                                })
                            </script>';
                        }
                    } else {
                        echo '<script>
                            Swal.fire({
                                icon: "error",
                                title: "¡El nombre ya existe!",
                                text: "Por favor ingrese un nombre diferente",
                                showConfirmButton: true,
                                confirmButtonText: "Cerrar"
                            })
                        </script>';
                    }
                } else {
                    echo '<script>
                        Swal.fire({
                            icon: "error",
                            title: "¡El código ya existe!",
                            text: "Por favor ingrese un código diferente",
                            showConfirmButton: true,
                            confirmButtonText: "Cerrar"
                        })
                    </script>';
                }
            } else {
                echo '<script>
                    Swal.fire({
                        icon: "error",
                        title: "¡El nombre o código no puede ir vacío o llevar caracteres especiales!",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
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
            
            $tabla = "tipo_formularios";
            $datos = array(
                "id" => $_GET["idTipoFormulario"],
                "modificado_por" => $_SESSION["user_id"]
            );

            $respuesta = ModeloTipoFormularios::mdlBorrarTipoFormulario($tabla, $datos);

            if($respuesta == "ok") {
                echo '<script>
                    Swal.fire({
                        icon: "success",
                        title: "¡El tipo de formulario ha sido borrado correctamente!",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    }).then(function(result) {
                        if (result.value) {
                            window.location = "preformatos";
                        }
                    })
                </script>';
            }
        }
    }

    /*=============================================
    ACTIVAR/DESACTIVAR TIPO DE FORMULARIO
    =============================================*/
    static public function ctrActivarTipoFormulario() {
        if(isset($_GET["activarTipoFormulario"])) {
            
            $tabla = "tipo_formularios";
            $item1 = "activo";
            $valor1 = $_GET["activarTipoFormulario"];
            $item2 = "id";
            $valor2 = $_GET["idTipoFormulario"];

            $activarUsuario = ModeloTipoFormularios::mdlActivarTipoFormulario($tabla, array(
                "id" => $valor2,
                "activo" => $valor1,
                "modificado_por" => $_SESSION["user_id"]
            ));

            if($activarUsuario == "ok") {
                echo '<script>
                    window.location = "preformatos";
                </script>';
            }
        }
    }

    /*=============================================
    OBTENER TIPOS DE FORMULARIOS PARA SELECT
    =============================================*/
    static public function ctrObtenerTiposFormulariosSelect() {
        $respuesta = ModeloTipoFormularios::mdlObtenerTiposFormulariosSelect();
        return $respuesta;
    }
}
