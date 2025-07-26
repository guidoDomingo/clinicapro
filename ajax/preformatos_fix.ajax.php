<?php

require_once "../controller/preformatos.controller.php";
require_once "../model/preformatos.model.php";

/**
 * Clase para manejar las peticiones Ajax de preformatos
 */
class AjaxPreformatos {

    /**
     * Propiedad para mostrar preformatos
     */
    public $mostrarPreformatos;

    /**
     * Obtener todos los preformatos
     */
    public function ajaxMostrarPreformatos() {
        if ($this->mostrarPreformatos == "ok") {
            $respuesta = ControllerPreformatos::ctrGetAllPreformatos([]);
            echo json_encode($respuesta);
        }
    }

    /**
     * Propiedades para obtener preformatos por tipo
     */
    public $getPreformatosConsulta;
    public $usuario_id;
    public $tipo_formulario;

    /**
     * Obtener preformatos de consulta
     */
    public function ajaxGetPreformatosConsulta() {
        if ($this->getPreformatosConsulta == "ok") {
            $respuesta = ControllerPreformatos::ctrGetPreformatos("consulta", $this->usuario_id, $this->tipo_formulario);
            echo json_encode($respuesta);
        }
    }

    /**
     * Propiedades para obtener preformatos de receta
     */
    public $getPreformatosReceta;

    /**
     * Obtener preformatos de receta
     */
    public function ajaxGetPreformatosReceta() {
        if ($this->getPreformatosReceta == "ok") {
            $respuesta = ControllerPreformatos::ctrGetPreformatos("receta", $this->usuario_id, $this->tipo_formulario);
            echo json_encode($respuesta);
        }
    }

    /**
     * Propiedades para obtener doctor por usuario ID
     */
    public $getDoctorByUserId;
    public $user_id;

    /**
     * Obtener doctor por ID de usuario
     */
    public function ajaxGetDoctorByUserId() {
        if ($this->getDoctorByUserId == "ok") {
            try {
                $db = Conexion::conectar();
                
                // Consulta para obtener doctor asociado al usuario
                $stmt = $db->prepare(
                    "SELECT 
                        d.doctor_id,
                        d.person_id,
                        rp.first_name,
                        rp.last_name,
                        CONCAT(rp.last_name, ', ', rp.first_name) as nombre_completo
                    FROM person_system_user psu 
                    JOIN rh_person rp ON psu.person_id = rp.person_id
                    JOIN rh_doctors d ON rp.person_id = d.person_id
                    WHERE psu.system_user_id = :user_id
                    LIMIT 1"
                );
                
                $stmt->bindParam(":user_id", $this->user_id, PDO::PARAM_INT);
                $stmt->execute();
                $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($resultado) {
                    echo json_encode([
                        'status' => 'success',
                        'data' => $resultado
                    ]);
                } else {
                    // Intentar buscar directamente como doctor_id
                    $stmt2 = $db->prepare("SELECT doctor_id, person_id FROM rh_doctors WHERE doctor_id = :user_id LIMIT 1");
                    $stmt2->bindParam(":user_id", $this->user_id, PDO::PARAM_INT);
                    $stmt2->execute();
                    $doctorDirecto = $stmt2->fetch(PDO::FETCH_ASSOC);
                    
                    if ($doctorDirecto) {
                        echo json_encode([
                            'status' => 'success',
                            'data' => [
                                'doctor_id' => $doctorDirecto['doctor_id'],
                                'nombre_completo' => 'Doctor ID: ' . $doctorDirecto['doctor_id']
                            ]
                        ]);
                    } else {
                        echo json_encode([
                            'status' => 'error',
                            'message' => 'No se encontró un doctor asociado a este usuario'
                        ]);
                    }
                }
                
            } catch (Exception $e) {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Error al consultar datos del doctor: ' . $e->getMessage()
                ]);
            }
        }
    }

    /**
     * Propiedades para obtener motivos comunes
     */
    public $getMotivosComunes;

    /**
     * Obtener motivos comunes
     */
    public function ajaxGetMotivosComunes() {
        if ($this->getMotivosComunes == "ok") {
            $respuesta = ControllerPreformatos::ctrGetMotivosComunes($this->tipo_formulario);
            echo json_encode($respuesta);
        }
    }
}

/*=============================================
MOSTRAR PREFORMATOS
=============================================*/
if (isset($_POST["mostrarPreformatos"])) {
    $mostrar = new AjaxPreformatos();
    $mostrar->mostrarPreformatos = $_POST["mostrarPreformatos"];
    $mostrar->ajaxMostrarPreformatos();
}

/*=============================================
OBTENER PREFORMATOS DE CONSULTA
=============================================*/
if (isset($_POST["operacion"]) && $_POST["operacion"] == "getPreformatosConsulta") {
    $preformatos = new AjaxPreformatos();
    $preformatos->getPreformatosConsulta = "ok";
    $preformatos->usuario_id = isset($_POST["usuario_id"]) ? $_POST["usuario_id"] : null;
    $preformatos->tipo_formulario = isset($_POST["tipo_formulario"]) ? $_POST["tipo_formulario"] : 'general';
    $preformatos->ajaxGetPreformatosConsulta();
}

/*=============================================
OBTENER PREFORMATOS DE RECETA
=============================================*/
if (isset($_POST["operacion"]) && $_POST["operacion"] == "getPreformatosReceta") {
    $preformatos = new AjaxPreformatos();
    $preformatos->getPreformatosReceta = "ok";
    $preformatos->usuario_id = isset($_POST["usuario_id"]) ? $_POST["usuario_id"] : null;
    $preformatos->tipo_formulario = isset($_POST["tipo_formulario"]) ? $_POST["tipo_formulario"] : 'general';
    $preformatos->ajaxGetPreformatosReceta();
}

/*=============================================
OBTENER DOCTOR POR USER ID
=============================================*/
if (isset($_POST["operacion"]) && $_POST["operacion"] == "getDoctorByUserId") {
    $doctor = new AjaxPreformatos();
    $doctor->getDoctorByUserId = "ok";
    $doctor->user_id = $_POST["user_id"];
    $doctor->ajaxGetDoctorByUserId();
}

/*=============================================
OBTENER MOTIVOS COMUNES
=============================================*/
if (isset($_POST["operacion"]) && $_POST["operacion"] == "getMotivosComunes") {
    $motivos = new AjaxPreformatos();
    $motivos->getMotivosComunes = "ok";
    $motivos->tipo_formulario = isset($_POST["tipo_formulario"]) ? $_POST["tipo_formulario"] : 'general';
    $motivos->ajaxGetMotivosComunes();
}
?>
