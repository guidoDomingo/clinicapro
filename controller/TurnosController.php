<?php
// Controlador para la gestión de turnos
require_once __DIR__ . "/../model/TurnosModel.php";

class TurnosController {
    
    /**
     * Mostrar todos los turnos
     */
    public static function ctrMostrarTurnos($item = null, $valor = null) {
        $tabla = "turnos";
        $respuesta = TurnosModel::mdlMostrarTurnos($tabla, $item, $valor);
        return $respuesta;
    }

    /**
     * Crear un nuevo turno
     */
    public function ctrCrearTurno() {
        if(isset($_POST["turno_nombre"])) {
            
            // Validar que los campos requeridos no estén vacíos
            if(!empty($_POST["turno_nombre"])) {
                
                $tabla = "turnos";
                
                $datos = array(
                    "turno_nombre" => $_POST["turno_nombre"],
                    "turno_descripcion" => $_POST["turno_descripcion"] ?? null,
                    "turno_estado" => $_POST["turno_estado"] ?? 1
                );
                
                $respuesta = TurnosModel::mdlIngresarTurno($tabla, $datos);
                
                if($respuesta == "ok") {
                    echo '<script>
                        Swal.fire({
                            position: "center",
                            icon: "success",
                            title: "El turno ha sido creado correctamente",
                            showConfirmButton: false,
                            timer: 1500
                        }).then(function(){
                            window.location = "index.php?ruta=turnos";
                        });
                    </script>';
                } else {
                    echo '<script>
                        Swal.fire({
                            position: "center",
                            icon: "error",
                            title: "Error al crear el turno",
                            text: "' . $respuesta . '",
                            showConfirmButton: true
                        });
                    </script>';
                }
                
            } else {
                echo '<script>
                    Swal.fire({
                        position: "center",
                        icon: "error",
                        title: "¡El nombre del turno es obligatorio!",
                        showConfirmButton: true
                    });
                </script>';
            }
        }
    }

    /**
     * Editar turno
     */
    public function ctrEditarTurno() {
        if(isset($_POST["editarTurno"])) {
            
            if(!empty($_POST["editarTurnoNombre"])) {
                
                $tabla = "turnos";
                
                $datos = array(
                    "turno_id" => $_POST["editarTurno"],
                    "turno_nombre" => $_POST["editarTurnoNombre"],
                    "turno_descripcion" => $_POST["editarTurnoDescripcion"] ?? null,
                    "turno_estado" => $_POST["editarTurnoEstado"] ?? 1
                );
                
                $respuesta = TurnosModel::mdlEditarTurno($tabla, $datos);
                
                if($respuesta == "ok") {
                    echo '<script>
                        Swal.fire({
                            position: "center",
                            icon: "success",
                            title: "El turno ha sido editado correctamente",
                            showConfirmButton: false,
                            timer: 1500
                        }).then(function(){
                            window.location = "index.php?ruta=turnos";
                        });
                    </script>';
                } else {
                    echo '<script>
                        Swal.fire({
                            position: "center",
                            icon: "error",
                            title: "Error al editar el turno",
                            text: "' . $respuesta . '",
                            showConfirmButton: true
                        });
                    </script>';
                }
                
            } else {
                echo '<script>
                    Swal.fire({
                        position: "center",
                        icon: "error",
                        title: "¡El nombre del turno es obligatorio!",
                        showConfirmButton: true
                    });
                </script>';
            }
        }
    }

    /**
     * Borrar turno
     */
    public function ctrBorrarTurno() {
        if(isset($_GET["idTurno"])) {
            
            $tabla = "turnos";
            $datos = $_GET["idTurno"];
            
            $respuesta = TurnosModel::mdlBorrarTurno($tabla, $datos);
            
            if($respuesta == "ok") {
                echo '<script>
                    Swal.fire({
                        position: "center",
                        icon: "success",
                        title: "El turno ha sido borrado correctamente",
                        showConfirmButton: false,
                        timer: 1500
                    }).then(function(){
                        window.location = "index.php?ruta=turnos";
                    });
                </script>';
            } else {
                echo '<script>
                    Swal.fire({
                        position: "center",
                        icon: "error",
                        title: "Error al borrar el turno",
                        text: "' . $respuesta . '",
                        showConfirmButton: true
                    });
                </script>';
            }
        }
    }

    /**
     * Obtener turno por ID para edición
     */
    public static function ctrMostrarTurno($item, $valor) {
        $tabla = "turnos";
        $respuesta = TurnosModel::mdlMostrarTurnos($tabla, $item, $valor);
        return $respuesta;
    }

    /**
     * Buscar turnos por filtro
     */
    public static function ctrBuscarTurnos($filtro) {
        $tabla = "turnos";
        $respuesta = TurnosModel::mdlBuscarTurnos($tabla, $filtro);
        return $respuesta;
    }
}

// Crear una instancia del controlador para manejar las acciones
$turnosController = new TurnosController();

// Procesar acciones según el POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST["turno_nombre"]) && !isset($_POST["editarTurno"])) {
        $turnosController->ctrCrearTurno();
    } elseif (isset($_POST["editarTurno"])) {
        $turnosController->ctrEditarTurno();
    }
}

// Procesar eliminación via GET
if (isset($_GET["idTurno"])) {
    $turnosController->ctrBorrarTurno();
}
?>
