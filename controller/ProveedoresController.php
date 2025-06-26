<?php
// Controlador para la gestión de proveedores y acreedores
require_once __DIR__ . "/../model/ProveedoresModel.php";

class ProveedoresController {
    
    /**
     * Mostrar todos los proveedores
     */
    public static function ctrMostrarProveedores($item = null, $valor = null) {
        $tabla = "cm_proveedores_acreedores";
        $respuesta = ProveedoresModel::mdlMostrarProveedores($tabla, $item, $valor);
        return $respuesta;
    }

    /**
     * Mostrar proveedores con información relacionada
     */
    public static function ctrMostrarProveedoresCompleto($item = null, $valor = null) {
        $tabla = "cm_proveedores_acreedores";
        $respuesta = ProveedoresModel::mdlMostrarProveedoresCompleto($tabla, $item, $valor);
        return $respuesta;
    }

    /**
     * Crear un nuevo proveedor
     */
    public function ctrCrearProveedor() {
        if(isset($_POST["nombreProveedor"])) {
            
            // Validar RUC: al menos 5 dígitos y máximo 15
            if(preg_match('/^[0-9]{5,15}$/', $_POST["rucProveedor"])) {
                
                // Validar email si se proporciona
                if(!empty($_POST["emailProveedor"]) && !filter_var($_POST["emailProveedor"], FILTER_VALIDATE_EMAIL)) {
                    echo '<script>
                        Swal.fire({
                            icon: "error",
                            title: "¡Error!",
                            text: "¡El email no es válido!",
                            confirmButtonText: "Cerrar"
                        });
                    </script>';
                    return;
                }
                
                // Preparar datos para el modelo
                $datos = array(
                    "tipo_cod" => $_POST["tipoCodProveedor"],
                    "tipo_persona" => $_POST["tipoPersonaProveedor"],
                    "nombre" => trim($_POST["nombreProveedor"]),
                    "apellido" => isset($_POST["apellidoProveedor"]) ? trim($_POST["apellidoProveedor"]) : '',
                    "razon_social" => trim($_POST["razonSocialProveedor"]),
                    "ruc" => trim($_POST["rucProveedor"]),
                    "dv" => isset($_POST["dvProveedor"]) ? trim($_POST["dvProveedor"]) : '',
                    "timbrado" => isset($_POST["timbradoProveedor"]) ? trim($_POST["timbradoProveedor"]) : '',
                    "telefono" => isset($_POST["telefonoProveedor"]) ? trim($_POST["telefonoProveedor"]) : '',
                    "email" => isset($_POST["emailProveedor"]) ? trim($_POST["emailProveedor"]) : '',
                    "direccion" => isset($_POST["direccionProveedor"]) ? trim($_POST["direccionProveedor"]) : '',
                    "business_id" => !empty($_POST["empresaProveedor"]) ? intval($_POST["empresaProveedor"]) : null,
                    "is_active" => isset($_POST["estadoProveedor"]) ? $_POST["estadoProveedor"] : 1
                );
                
                // Llamar al modelo para guardar el proveedor
                $respuesta = ProveedoresModel::mdlCrearProveedor("cm_proveedores_acreedores", $datos);
                
                // Verificar resultado
                if($respuesta == "ok") {
                    echo '<script>
                        Swal.fire({
                            icon: "success",
                            title: "¡Éxito!",
                            text: "¡El proveedor ha sido guardado correctamente!",
                            confirmButtonText: "Cerrar"
                        }).then(function(result){
                            if(result.value){
                                window.location = "proveedores";
                            }
                        });
                    </script>';
                } else {
                    echo '<script>
                        Swal.fire({
                            icon: "error",
                            title: "¡Error!",
                            text: "¡Hubo un problema al guardar el proveedor!",
                            confirmButtonText: "Cerrar"
                        });
                    </script>';
                }
            } else {
                echo '<script>
                    Swal.fire({
                        icon: "error",
                        title: "¡Error!",
                        text: "¡El RUC no tiene el formato correcto!",
                        confirmButtonText: "Cerrar"
                    });
                </script>';
            }
        }
    }
    
    /**
     * Editar un proveedor existente
     */
    public function ctrEditarProveedor() {
        if(isset($_POST["idProveedor"])) {
            
            // Validar RUC: al menos 5 dígitos y máximo 15
            if(preg_match('/^[0-9]{5,15}$/', $_POST["editarRucProveedor"])) {
                
                // Validar email si se proporciona
                if(!empty($_POST["editarEmailProveedor"]) && !filter_var($_POST["editarEmailProveedor"], FILTER_VALIDATE_EMAIL)) {
                    echo '<script>
                        Swal.fire({
                            icon: "error",
                            title: "¡Error!",
                            text: "¡El email no es válido!",
                            confirmButtonText: "Cerrar"
                        });
                    </script>';
                    return;
                }
                
                // Preparar datos para el modelo
                $datos = array(
                    "id" => $_POST["idProveedor"],
                    "tipo_cod" => $_POST["editarTipoCodProveedor"],
                    "tipo_persona" => $_POST["editarTipoPersonaProveedor"],
                    "nombre" => trim($_POST["editarNombreProveedor"]),
                    "apellido" => isset($_POST["editarApellidoProveedor"]) ? trim($_POST["editarApellidoProveedor"]) : '',
                    "razon_social" => trim($_POST["editarRazonSocialProveedor"]),
                    "ruc" => trim($_POST["editarRucProveedor"]),
                    "dv" => isset($_POST["editarDvProveedor"]) ? trim($_POST["editarDvProveedor"]) : '',
                    "timbrado" => isset($_POST["editarTimbradoProveedor"]) ? trim($_POST["editarTimbradoProveedor"]) : '',
                    "telefono" => isset($_POST["editarTelefonoProveedor"]) ? trim($_POST["editarTelefonoProveedor"]) : '',
                    "email" => isset($_POST["editarEmailProveedor"]) ? trim($_POST["editarEmailProveedor"]) : '',
                    "direccion" => isset($_POST["editarDireccionProveedor"]) ? trim($_POST["editarDireccionProveedor"]) : '',
                    "business_id" => !empty($_POST["editarEmpresaProveedor"]) ? intval($_POST["editarEmpresaProveedor"]) : null,
                    "is_active" => isset($_POST["editarEstadoProveedor"]) ? $_POST["editarEstadoProveedor"] : 1
                );
                
                // Llamar al modelo para actualizar el proveedor
                $respuesta = ProveedoresModel::mdlEditarProveedor("cm_proveedores_acreedores", $datos);
                
                // Verificar resultado
                if($respuesta == "ok") {
                    echo '<script>
                        Swal.fire({
                            icon: "success",
                            title: "¡Éxito!",
                            text: "¡El proveedor ha sido actualizado correctamente!",
                            confirmButtonText: "Cerrar"
                        }).then(function(result){
                            if(result.value){
                                window.location = "proveedores";
                            }
                        });
                    </script>';
                } else {
                    echo '<script>
                        Swal.fire({
                            icon: "error",
                            title: "¡Error!",
                            text: "¡Hubo un problema al actualizar el proveedor!",
                            confirmButtonText: "Cerrar"
                        });
                    </script>';
                }
            } else {
                echo '<script>
                    Swal.fire({
                        icon: "error",
                        title: "¡Error!",
                        text: "¡El RUC no tiene el formato correcto!",
                        confirmButtonText: "Cerrar"
                    });
                </script>';
            }
        }
    }
    
    /**
     * Borrar un proveedor
     */
    public function ctrBorrarProveedor() {
        if(isset($_GET["idProveedor"])) {
            
            $tabla = "cm_proveedores_acreedores";
            $datos = $_GET["idProveedor"];
            
            $respuesta = ProveedoresModel::mdlBorrarProveedor($tabla, $datos);
            
            if($respuesta == "ok") {
                echo '<script>
                    Swal.fire({
                        icon: "success",
                        title: "¡Éxito!",
                        text: "El proveedor ha sido eliminado correctamente",
                        confirmButtonText: "Cerrar"
                    }).then(function(result){
                        if(result.value){
                            window.location = "proveedores";
                        }
                    });
                </script>';
            } else {
                echo '<script>
                    Swal.fire({
                        icon: "error",
                        title: "¡Error!",
                        text: "No fue posible eliminar el proveedor",
                        confirmButtonText: "Cerrar"
                    }).then(function(result){
                        if(result.value){
                            window.location = "proveedores";
                        }
                    });
                </script>';
            }
        }
    }
}
