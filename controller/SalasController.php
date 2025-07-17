<?php
// Controlador para la gestión de salas
require_once __DIR__ . "/../model/SalasModel.php";

class SalasController {
    
    /**
     * Mostrar todas las salas
     */
    public static function ctrMostrarSalas($item = null, $valor = null) {
        $tabla = "salas";
        $respuesta = SalasModel::mdlMostrarSalas($tabla, $item, $valor);
        return $respuesta;
    }

    /**
     * Crear una nueva sala
     */
    public function ctrCrearSala() {
        if(isset($_POST["sala_codigo"])) {
            
            // Validar que los campos requeridos no estén vacíos
            if(!empty($_POST["sala_codigo"]) && !empty($_POST["sala_nombre"])) {
                
                // Validar que el código no contenga espacios ni caracteres especiales
                if(preg_match('/^[a-zA-Z0-9_-]+$/', $_POST["sala_codigo"])) {
                    
                    $tabla = "salas";
                    
                    $datos = array(
                        "sala_codigo" => $_POST["sala_codigo"],
                        "sala_nombre" => $_POST["sala_nombre"],
                        "sala_descripcion" => $_POST["sala_descripcion"] ?? null,
                        "sala_estado" => $_POST["sala_estado"] ?? 1
                    );
                    
                    $respuesta = SalasModel::mdlIngresarSala($tabla, $datos);
                    
                    if($respuesta == "ok") {
                        echo '<script>
                            Swal.fire({
                                position: "center",
                                icon: "success",
                                title: "La sala ha sido creada correctamente",
                                showConfirmButton: false,
                                timer: 1500
                            }).then(function(){
                                window.location = "index.php?ruta=salas";
                            });
                        </script>';
                    } else {
                        echo '<script>
                            Swal.fire({
                                position: "center", 
                                icon: "error",
                                title: "Error al crear la sala: ' . $respuesta . '",
                                showConfirmButton: false,
                                timer: 1500
                            });
                        </script>';
                    }
                } else {
                    echo '<script>
                        Swal.fire({
                            position: "center",
                            icon: "error", 
                            title: "El código de sala solo puede contener letras, números, guiones y guiones bajos",
                            showConfirmButton: false,
                            timer: 1500
                        });
                    </script>';
                }
            } else {
                echo '<script>
                    Swal.fire({
                        position: "center",
                        icon: "error",
                        title: "Los campos código y nombre son obligatorios",
                        showConfirmButton: false,
                        timer: 1500
                    });
                </script>';
            }
        }
    }

    /**
     * Editar sala
     */
    public function ctrEditarSala() {
        if(isset($_POST["editarSala_codigo"])) {
            
            // Validar que los campos requeridos no estén vacíos
            if(!empty($_POST["editarSala_codigo"]) && !empty($_POST["editarSala_nombre"])) {
                
                // Validar que el código no contenga espacios ni caracteres especiales
                if(preg_match('/^[a-zA-Z0-9_-]+$/', $_POST["editarSala_codigo"])) {
                    
                    $tabla = "salas";
                    
                    $datos = array(
                        "sala_id" => $_POST["editarSala_id"],
                        "sala_codigo" => $_POST["editarSala_codigo"],
                        "sala_nombre" => $_POST["editarSala_nombre"],
                        "sala_descripcion" => $_POST["editarSala_descripcion"] ?? null,
                        "sala_estado" => $_POST["editarSala_estado"] ?? 1
                    );
                    
                    $respuesta = SalasModel::mdlEditarSala($tabla, $datos);
                    
                    if($respuesta == "ok") {
                        echo '<script>
                            Swal.fire({
                                position: "center",
                                icon: "success",
                                title: "La sala ha sido actualizada correctamente",
                                showConfirmButton: false,
                                timer: 1500
                            }).then(function(){
                                window.location = "index.php?ruta=salas";
                            });
                        </script>';
                    } else {
                        echo '<script>
                            Swal.fire({
                                position: "center",
                                icon: "error", 
                                title: "Error al actualizar la sala: ' . $respuesta . '",
                                showConfirmButton: false,
                                timer: 1500
                            });
                        </script>';
                    }
                } else {
                    echo '<script>
                        Swal.fire({
                            position: "center",
                            icon: "error",
                            title: "El código de sala solo puede contener letras, números, guiones y guiones bajos",
                            showConfirmButton: false,
                            timer: 1500
                        });
                    </script>';
                }
            } else {
                echo '<script>
                    Swal.fire({
                        position: "center",
                        icon: "error",
                        title: "Los campos código y nombre son obligatorios",
                        showConfirmButton: false,
                        timer: 1500
                    });
                </script>';
            }
        }
    }

    /**
     * Eliminar sala (cambiar estado a inactivo)
     */
    public function ctrEliminarSala() {
        if(isset($_GET["idSala"])) {
            
            $tabla = "salas";
            $datos = $_GET["idSala"];
            
            $respuesta = SalasModel::mdlEliminarSala($tabla, $datos);
            
            if($respuesta == "ok") {
                echo '<script>
                    Swal.fire({
                        position: "center",
                        icon: "success",
                        title: "La sala ha sido desactivada correctamente",
                        showConfirmButton: false,
                        timer: 1500
                    }).then(function(){
                        window.location = "index.php?ruta=salas";
                    });
                </script>';
            } else {
                echo '<script>
                    Swal.fire({
                        position: "center",
                        icon: "error",
                        title: "Error al desactivar la sala",
                        showConfirmButton: false,
                        timer: 1500
                    });
                </script>';
            }
        }
    }
    
    /**
     * Activar sala
     */
    public function ctrActivarSala() {
        if(isset($_GET["activarSala"])) {
            
            $tabla = "salas";
            $datos = $_GET["activarSala"];
            
            $respuesta = SalasModel::mdlActivarSala($tabla, $datos);
            
            if($respuesta == "ok") {
                echo '<script>
                    Swal.fire({
                        position: "center",
                        icon: "success", 
                        title: "La sala ha sido activada correctamente",
                        showConfirmButton: false,
                        timer: 1500
                    }).then(function(){
                        window.location = "index.php?ruta=salas";
                    });
                </script>';
            } else {
                echo '<script>
                    Swal.fire({
                        position: "center",
                        icon: "error",
                        title: "Error al activar la sala",
                        showConfirmButton: false,
                        timer: 1500
                    });
                </script>';
            }
        }
    }
}

// Procesar las diferentes acciones
$salas = new SalasController();

// Crear sala
$salas->ctrCrearSala();

// Editar sala  
$salas->ctrEditarSala();

// Eliminar sala
$salas->ctrEliminarSala();

// Activar sala
$salas->ctrActivarSala();

?>
