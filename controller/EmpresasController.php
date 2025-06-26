<?php
/**
 * Controlador para la gestión de empresas
 */
class EmpresasController {

    /**
     * Mostrar todas las empresas
     */
    static public function ctrMostrarEmpresas($item, $valor) {
        $tabla = "sys_business";
        $respuesta = EmpresasModel::mdlMostrarEmpresas($tabla, $item, $valor);
        return $respuesta;
    }

    /**
     * Crear una nueva empresa
     */
    public function ctrCrearEmpresa() {
        if(isset($_POST["nombreEmpresa"])) {
            if(preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ\s\.\,\-]+$/', $_POST["nombreEmpresa"])) {
                
                $tabla = "sys_business";
                
                $datos = array(
                    "business_name" => $_POST["nombreEmpresa"],
                    "business_ruc" => $_POST["rucEmpresa"],
                    "business_email" => $_POST["emailEmpresa"],
                    "business_phone" => $_POST["telefonoEmpresa"],
                    "business_address" => $_POST["direccionEmpresa"],
                    "business_is_active" => $_POST["estadoEmpresa"]
                );

                $respuesta = EmpresasModel::mdlIngresarEmpresa($tabla, $datos);

                if($respuesta == "ok") {
                    echo '<script>
                        Swal.fire({
                            icon: "success",
                            title: "¡La empresa ha sido guardada correctamente!",
                            showConfirmButton: true,
                            confirmButtonText: "Cerrar",
                            closeOnConfirm: false
                        }).then((result) => {
                            if (result.value) {
                                window.location = "empresas";
                            }
                        });
                    </script>';
                }

            } else {
                echo '<script>
                    Swal.fire({
                        icon: "error",
                        title: "¡El nombre de la empresa no puede ir vacío o llevar caracteres especiales!",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar",
                        closeOnConfirm: false
                    }).then((result) => {
                        if (result.value) {
                            window.location = "empresas";
                        }
                    });
                </script>';
            }
        }
    }

    /**
     * Editar una empresa
     */
    public function ctrEditarEmpresa() {
        if(isset($_POST["idEmpresa"])) {
            if(preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ\s\.\,\-]+$/', $_POST["editarNombreEmpresa"])) {
                
                $tabla = "sys_business";
                
                $datos = array(
                    "business_id" => $_POST["idEmpresa"],
                    "business_name" => $_POST["editarNombreEmpresa"],
                    "business_ruc" => $_POST["editarRucEmpresa"],
                    "business_email" => $_POST["editarEmailEmpresa"],
                    "business_phone" => $_POST["editarTelefonoEmpresa"],
                    "business_address" => $_POST["editarDireccionEmpresa"],
                    "business_is_active" => $_POST["editarEstadoEmpresa"]
                );

                $respuesta = EmpresasModel::mdlEditarEmpresa($tabla, $datos);

                if($respuesta == "ok") {
                    echo '<script>
                        Swal.fire({
                            icon: "success",
                            title: "¡La empresa ha sido editada correctamente!",
                            showConfirmButton: true,
                            confirmButtonText: "Cerrar",
                            closeOnConfirm: false
                        }).then((result) => {
                            if (result.value) {
                                window.location = "empresas";
                            }
                        });
                    </script>';
                }

            } else {
                echo '<script>
                    Swal.fire({
                        icon: "error",
                        title: "¡El nombre de la empresa no puede ir vacío o llevar caracteres especiales!",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar",
                        closeOnConfirm: false
                    }).then((result) => {
                        if (result.value) {
                            window.location = "empresas";
                        }
                    });
                </script>';
            }
        }
    }

    /**
     * Borrar una empresa
     */
    public function ctrBorrarEmpresa() {
        if(isset($_GET["idEmpresa"])) {
            $tabla = "sys_business";
            $datos = $_GET["idEmpresa"];

            $respuesta = EmpresasModel::mdlBorrarEmpresa($tabla, $datos);

            if($respuesta == "ok") {
                echo '<script>
                    Swal.fire({
                        icon: "success",
                        title: "¡La empresa ha sido borrada correctamente!",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar",
                        closeOnConfirm: false
                    }).then((result) => {
                        if (result.value) {
                            window.location = "empresas";
                        }
                    });
                </script>';
            }
        }
    }
}
