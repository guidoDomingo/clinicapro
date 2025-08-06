<?php

/**
 * Controlador para la gestión de referenciales dinámicos
 * Maneja tipos de formularios, campos dinámicos y valores de referenciales
 */
class ControllerReferenciales {
    
    /**
     * Muestra la vista principal de gestión de referenciales
     */
    static public function ctrMostrarReferenciales() {
        
        $ruta = isset($_GET["ruta"]) ? $_GET["ruta"] : "";
        
        switch ($ruta) {
            case "tipos-formularios":
                include "view/modules/referenciales/tipos-formularios.php";
                break;
                
            case "campos-formularios":
                include "view/modules/referenciales/campos-formularios.php";
                break;
                
            case "tipos-campos":
                include "view/modules/referenciales/tipos-campos.php";
                break;
                
            case "referenciales":
                include "view/modules/referenciales/referenciales.php";
                break;
                
            case "valores-referenciales":
                include "view/modules/referenciales/valores-referenciales.php";
                break;
                
            case "configuraciones-formularios":
                include "view/modules/referenciales/configuraciones-formularios.php";
                break;
                
            default:
                include "view/modules/referenciales/referenciales.php";
                break;
        }
    }
    
    /**
     * Obtiene todos los tipos de formularios
     */
    static public function ctrObtenerTiposFormularios() {
        $tabla = "tipos_formularios";
        $respuesta = ModelReferenciales::mdlObtenerRegistros($tabla, null, null);
        return $respuesta;
    }
    
    /**
     * Obtiene todos los tipos de campos
     */
    static public function ctrObtenerTiposCampos() {
        $tabla = "tipos_campos";
        $respuesta = ModelReferenciales::mdlObtenerRegistros($tabla, null, null);
        return $respuesta;
    }
    
    /**
     * Obtiene todos los referenciales
     */
    static public function ctrObtenerReferenciales() {
        $tabla = "referenciales";
        $respuesta = ModelReferenciales::mdlObtenerRegistros($tabla, null, null);
        return $respuesta;
    }
    
    /**
     * Crear tipo de formulario
     */
    static public function ctrCrearTipoFormulario() {
        if (isset($_POST["nombre"]) && isset($_POST["codigo"])) {
            
            $tabla = "tipos_formularios";
            
            $datos = array(
                "nombre" => $_POST["nombre"],
                "codigo" => $_POST["codigo"],
                "descripcion" => $_POST["descripcion"],
                "activo" => isset($_POST["activo"]) ? $_POST["activo"] : 1,
                "created_by" => $_SESSION["user_id"]
            );
            
            $respuesta = ModelReferenciales::mdlIngresarRegistro($tabla, $datos);
            
            if ($respuesta == "ok") {
                echo '<script>
                    Swal.fire({
                        icon: "success",
                        title: "¡Tipo de formulario creado!",
                        text: "El tipo de formulario ha sido guardado correctamente",
                        showConfirmButton: false,
                        timer: 1500
                    }).then((result) => {
                        window.location = "tipos-formularios";
                    });
                </script>';
            } else {
                echo '<script>
                    Swal.fire({
                        icon: "error",
                        title: "¡Error!",
                        text: "Ha ocurrido un error al crear el tipo de formulario",
                        showConfirmButton: false,
                        timer: 1500
                    });
                </script>';
            }
        }
    }
    
    /**
     * Crear referencial
     */
    static public function ctrCrearReferencial() {
        if (isset($_POST["nombre"]) && isset($_POST["codigo"])) {
            
            $tabla = "referenciales";
            
            $datos = array(
                "nombre" => $_POST["nombre"],
                "codigo" => $_POST["codigo"],
                "descripcion" => $_POST["descripcion"],
                "categoria" => $_POST["categoria"],
                "activo" => isset($_POST["activo"]) ? $_POST["activo"] : 1,
                "created_by" => $_SESSION["user_id"]
            );
            
            $respuesta = ModelReferenciales::mdlIngresarRegistro($tabla, $datos);
            
            if ($respuesta == "ok") {
                echo '<script>
                    Swal.fire({
                        icon: "success",
                        title: "¡Referencial creado!",
                        text: "El referencial ha sido guardado correctamente",
                        showConfirmButton: false,
                        timer: 1500
                    }).then((result) => {
                        window.location = "referenciales";
                    });
                </script>';
            } else {
                echo '<script>
                    Swal.fire({
                        icon: "error",
                        title: "¡Error!",
                        text: "Ha ocurrido un error al crear el referencial",
                        showConfirmButton: false,
                        timer: 1500
                    });
                </script>';
            }
        }
    }
    
    /**
     * Crear valor de referencial
     */
    static public function ctrCrearValorReferencial() {
        if (isset($_POST["referencial_id"]) && isset($_POST["valor"])) {
            
            $tabla = "referencial_valores";
            
            $datos = array(
                "referencial_id" => $_POST["referencial_id"],
                "valor" => $_POST["valor"],
                "etiqueta" => $_POST["etiqueta"],
                "valor_numerico" => !empty($_POST["valor_numerico"]) ? $_POST["valor_numerico"] : null,
                "orden_visualizacion" => !empty($_POST["orden_visualizacion"]) ? $_POST["orden_visualizacion"] : 1,
                "descripcion" => $_POST["descripcion"],
                "activo" => isset($_POST["activo"]) ? $_POST["activo"] : 1
            );
            
            $respuesta = ModelReferenciales::mdlIngresarRegistro($tabla, $datos);
            
            if ($respuesta == "ok") {
                echo '<script>
                    Swal.fire({
                        icon: "success",
                        title: "¡Valor creado!",
                        text: "El valor del referencial ha sido guardado correctamente",
                        showConfirmButton: false,
                        timer: 1500
                    }).then((result) => {
                        window.location = "valores-referenciales";
                    });
                </script>';
            } else {
                echo '<script>
                    Swal.fire({
                        icon: "error",
                        title: "¡Error!",
                        text: "Ha ocurrido un error al crear el valor del referencial",
                        showConfirmButton: false,
                        timer: 1500
                    });
                </script>';
            }
        }
    }
    
    /**
     * Crear campo de formulario
     */
    static public function ctrCrearCampoFormulario() {
        if (isset($_POST["tipo_formulario_id"]) && isset($_POST["nombre_campo"])) {
            
            $tabla = "formulario_campos";
            
            $datos = array(
                "tipo_formulario_id" => $_POST["tipo_formulario_id"],
                "nombre_campo" => $_POST["nombre_campo"],
                "etiqueta" => $_POST["etiqueta"],
                "tipo_campo_id" => $_POST["tipo_campo_id"],
                "placeholder" => $_POST["placeholder"],
                "orden_visualizacion" => !empty($_POST["orden_visualizacion"]) ? $_POST["orden_visualizacion"] : 1,
                "requerido" => isset($_POST["requerido"]) ? 1 : 0,
                "validaciones" => !empty($_POST["validaciones"]) ? $_POST["validaciones"] : null,
                "atributos_html" => !empty($_POST["atributos_html"]) ? $_POST["atributos_html"] : null,
                "descripcion_ayuda" => $_POST["descripcion_ayuda"],
                "grupo_seccion" => $_POST["grupo_seccion"],
                "activo" => isset($_POST["activo"]) ? $_POST["activo"] : 1,
                "created_by" => $_SESSION["user_id"]
            );
            
            $respuesta = ModelReferenciales::mdlIngresarRegistro($tabla, $datos);
            
            if ($respuesta == "ok") {
                echo '<script>
                    Swal.fire({
                        icon: "success",
                        title: "¡Campo creado!",
                        text: "El campo del formulario ha sido guardado correctamente",
                        showConfirmButton: false,
                        timer: 1500
                    }).then((result) => {
                        window.location = "campos-formularios";
                    });
                </script>';
            } else {
                echo '<script>
                    Swal.fire({
                        icon: "error",
                        title: "¡Error!",
                        text: "Ha ocurrido un error al crear el campo del formulario",
                        showConfirmButton: false,
                        timer: 1500
                    });
                </script>';
            }
        }
    }
    
    /**
     * Editar registro
     */
    static public function ctrEditarRegistro() {
        if (isset($_POST["idEditar"])) {
            
            $tabla = $_POST["tabla"];
            $item = $_POST["campo_id"];
            $valor = $_POST["idEditar"];
            
            $datos = array();
            
            // Preparar datos según la tabla
            switch ($tabla) {
                case "tipos_formularios":
                    $datos = array(
                        "nombre" => $_POST["editarNombre"],
                        "codigo" => $_POST["editarCodigo"],
                        "descripcion" => $_POST["editarDescripcion"],
                        "activo" => $_POST["editarActivo"],
                        "updated_by" => $_SESSION["user_id"]
                    );
                    break;
                    
                case "referenciales":
                    $datos = array(
                        "nombre" => $_POST["editarNombre"],
                        "codigo" => $_POST["editarCodigo"],
                        "descripcion" => $_POST["editarDescripcion"],
                        "categoria" => $_POST["editarCategoria"],
                        "activo" => $_POST["editarActivo"]
                    );
                    break;
                    
                // Agregar más casos según necesidad
            }
            
            $respuesta = ModelReferenciales::mdlEditarRegistro($tabla, $datos, $item, $valor);
            
            if ($respuesta == "ok") {
                echo '<script>
                    Swal.fire({
                        icon: "success",
                        title: "¡Registro actualizado!",
                        text: "El registro ha sido actualizado correctamente",
                        showConfirmButton: false,
                        timer: 1500
                    }).then((result) => {
                        window.location = "'.$_GET["ruta"].'";
                    });
                </script>';
            } else {
                echo '<script>
                    Swal.fire({
                        icon: "error",
                        title: "¡Error!",
                        text: "Ha ocurrido un error al actualizar el registro",
                        showConfirmButton: false,
                        timer: 1500
                    });
                </script>';
            }
        }
    }
    
    /**
     * Eliminar registro
     */
    static public function ctrEliminarRegistro() {
        if (isset($_GET["idEliminar"])) {
            
            $tabla = $_GET["tabla"];
            $item = $_GET["campo_id"];
            $valor = $_GET["idEliminar"];
            
            $respuesta = ModelReferenciales::mdlEliminarRegistro($tabla, $item, $valor);
            
            if ($respuesta == "ok") {
                echo '<script>
                    Swal.fire({
                        icon: "success",
                        title: "¡Registro eliminado!",
                        text: "El registro ha sido eliminado correctamente",
                        showConfirmButton: false,
                        timer: 1500
                    }).then((result) => {
                        window.location = "'.$_GET["ruta"].'";
                    });
                </script>';
            } else {
                echo '<script>
                    Swal.fire({
                        icon: "error",
                        title: "¡Error!",
                        text: "No se puede eliminar este registro. Puede estar siendo utilizado por otros datos.",
                        showConfirmButton: false,
                        timer: 2000
                    });
                </script>';
            }
        }
    }
    
    /**
     * Mostrar valores de un referencial específico
     */
    static public function ctrMostrarValoresReferencial($referencial_id) {
        $respuesta = ModelReferenciales::mdlObtenerValoresReferencial($referencial_id);
        return $respuesta;
    }
    
    /**
     * Mostrar todos los tipos de formularios
     */
    static public function ctrMostrarTiposFormularios() {
        $respuesta = ModelReferenciales::mdlObtenerRegistros("tipos_formularios", null, null);
        return $respuesta;
    }
    
    /**
     * Mostrar todos los tipos de campos
     */
    static public function ctrMostrarTiposCampos() {
        $respuesta = ModelReferenciales::mdlObtenerRegistros("tipos_campos", null, null);
        return $respuesta;
    }
    
    /**
     * Mostrar campos de formularios con JOIN
     */
    static public function ctrMostrarCamposFormularios() {
        $respuesta = ModelReferenciales::mdlObtenerCamposFormularios();
        return $respuesta;
    }
    
    /**
     * Mostrar configuraciones de formularios con JOIN
     */
    static public function ctrMostrarConfiguraciones() {
        $respuesta = ModelReferenciales::mdlObtenerConfiguraciones();
        return $respuesta;
    }
    
    /**
     * Crear configuración de formulario
     */
    static public function ctrCrearConfiguracion($datos) {
        $respuesta = ModelReferenciales::mdlIngresarRegistro("formulario_configuraciones", $datos);
        return $respuesta;
    }
}

?>
