<?php
/**
 * Controlador para la gestión de tipos de proveedores
 */
class TiposProveedoresController {

    /**
     * Mostrar todos los tipos de proveedores
     */
    static public function ctrMostrarTiposProveedores($item, $valor) {
        $tabla = "cm_tipos_proveedores";
        $respuesta = TiposProveedoresModel::mdlMostrarTiposProveedores($tabla, $item, $valor);
        return $respuesta;
    }

    /**
     * Crear un nuevo tipo de proveedor
     */
    public function ctrCrearTipoProveedor() {
        if(isset($_POST["nombreTipoProveedor"])) {
            if(preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ\s\.\,\-]+$/', $_POST["nombreTipoProveedor"])) {
                
                $tabla = "cm_tipos_proveedores";
                
                $datos = array(
                    "tipo_nombre" => $_POST["nombreTipoProveedor"],
                    "descripcion" => $_POST["descripcionTipoProveedor"]
                );

                $respuesta = TiposProveedoresModel::mdlIngresarTipoProveedor($tabla, $datos);

                if($respuesta == "ok") {
                    echo '<script>
                        Swal.fire({
                            icon: "success",
                            title: "¡El tipo de proveedor ha sido guardado correctamente!",
                            showConfirmButton: true,
                            confirmButtonText: "Cerrar",
                            closeOnConfirm: false
                        }).then((result) => {
                            if (result.value) {
                                window.location = "tipos_proveedores";
                            }
                        });
                    </script>';
                }

            } else {
                echo '<script>
                    Swal.fire({
                        icon: "error",
                        title: "¡El nombre del tipo de proveedor no puede ir vacío o llevar caracteres especiales!",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar",
                        closeOnConfirm: false
                    }).then((result) => {
                        if (result.value) {
                            window.location = "tipos_proveedores";
                        }
                    });
                </script>';
            }
        }
    }

    /**
     * Editar un tipo de proveedor
     */
    public function ctrEditarTipoProveedor() {
        if(isset($_POST["idTipoProveedor"])) {
            if(preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ\s\.\,\-]+$/', $_POST["editarNombreTipoProveedor"])) {
                
                $tabla = "cm_tipos_proveedores";
                
                $datos = array(
                    "tipo_cod" => $_POST["idTipoProveedor"],
                    "tipo_nombre" => $_POST["editarNombreTipoProveedor"],
                    "descripcion" => $_POST["editarDescripcionTipoProveedor"]
                );

                $respuesta = TiposProveedoresModel::mdlEditarTipoProveedor($tabla, $datos);

                if($respuesta == "ok") {
                    echo '<script>
                        Swal.fire({
                            icon: "success",
                            title: "¡El tipo de proveedor ha sido editado correctamente!",
                            showConfirmButton: true,
                            confirmButtonText: "Cerrar",
                            closeOnConfirm: false
                        }).then((result) => {
                            if (result.value) {
                                window.location = "tipos_proveedores";
                            }
                        });
                    </script>';
                }

            } else {
                echo '<script>
                    Swal.fire({
                        icon: "error",
                        title: "¡El nombre del tipo de proveedor no puede ir vacío o llevar caracteres especiales!",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar",
                        closeOnConfirm: false
                    }).then((result) => {
                        if (result.value) {
                            window.location = "tipos_proveedores";
                        }
                    });
                </script>';
            }
        }
    }

    /**
     * Borrar un tipo de proveedor
     */
    public function ctrBorrarTipoProveedor() {
        if(isset($_GET["idTipoProveedor"])) {
            $tabla = "cm_tipos_proveedores";
            $datos = $_GET["idTipoProveedor"];

            $respuesta = TiposProveedoresModel::mdlBorrarTipoProveedor($tabla, $datos);

            if($respuesta == "ok") {
                echo '<script>
                    Swal.fire({
                        icon: "success",
                        title: "¡El tipo de proveedor ha sido borrado correctamente!",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar",
                        closeOnConfirm: false
                    }).then((result) => {
                        if (result.value) {
                            window.location = "tipos_proveedores";
                        }
                    });
                </script>';
            }
        }
    }
}
