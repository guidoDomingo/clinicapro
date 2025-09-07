<?php
require_once "../controller/agendas.controller.php";

class AjaxAgendas {
    /**
     * Obtiene todas las agendas médicas
     */
    public function ajaxObtenerAgendas() {
        $agendas = ControllerAgendas::ctrObtenerAgendas();
        echo json_encode(["status" => "success", "data" => $agendas]);
    }

    /**
     * Obtiene una agenda específica por ID
     */
    public function ajaxObtenerAgendaPorId() {
        if (isset($_POST["agenda_id"])) {
            $agenda = ControllerAgendas::ctrObtenerAgendaPorId($_POST["agenda_id"]);
            echo json_encode(["status" => "success", "data" => $agenda]);
        } else {
            echo json_encode(["status" => "error", "message" => "ID de agenda no proporcionado"]);
        }
    }

    /**
     * Obtiene agendas por médico
     */
    public function ajaxObtenerAgendasPorMedico() {
        if (isset($_POST["medico_id"])) {
            $agendas = ControllerAgendas::ctrObtenerAgendasPorMedico($_POST["medico_id"]);
            echo json_encode(["status" => "success", "data" => $agendas]);
        } else {
            echo json_encode(["status" => "error", "message" => "ID de médico no proporcionado"]);
        }
    }

    /**
     * Guarda una agenda (crear o actualizar)
     */
    public function ajaxGuardarAgenda() {
        if (isset($_POST["medico_id"])) {
            $datos = [
                "agenda_id" => isset($_POST["agenda_id"]) ? $_POST["agenda_id"] : "",
                "medico_id" => $_POST["medico_id"],
                "agenda_descripcion" => isset($_POST["agenda_descripcion"]) ? $_POST["agenda_descripcion"] : "",
                "agenda_estado" => isset($_POST["agenda_estado"]) ? $_POST["agenda_estado"] === "true" : true
            ];

            $resultado = ControllerAgendas::ctrGuardarAgenda($datos);
            echo json_encode($resultado);
        } else {
            echo json_encode(["error" => true, "mensaje" => "Datos incompletos"]);
        }
    }

    /**
     * Elimina una agenda
     */
    public function ajaxEliminarAgenda() {
        if (isset($_POST["agenda_id"])) {
            $resultado = ControllerAgendas::ctrEliminarAgenda($_POST["agenda_id"]);
            echo json_encode($resultado);
        } else {
            echo json_encode(["error" => true, "mensaje" => "ID de agenda no proporcionado"]);
        }
    }

    /**
     * Obtiene los detalles de horarios de una agenda
     */
    public function ajaxObtenerDetallesAgenda() {
        if (isset($_POST["agenda_id"])) {
            $detalles = ControllerAgendas::ctrObtenerDetallesAgenda($_POST["agenda_id"]);
            echo json_encode(["status" => "success", "data" => $detalles]);
        } else {
            echo json_encode(["status" => "error", "message" => "ID de agenda no proporcionado"]);
        }
    }
    
    /**
     * Obtiene un detalle específico de agenda por ID
     */
    public function ajaxObtenerDetalleAgenda() {
        if (isset($_POST["detalle_id"])) {
            $detalle = ControllerAgendas::ctrObtenerDetalleAgenda($_POST["detalle_id"]);
            echo json_encode(["status" => "success", "data" => $detalle]);
        } else {
            echo json_encode(["status" => "error", "message" => "ID de detalle no proporcionado"]);
        }
    }

    /**
     * Guarda un detalle de horario (crear o actualizar)
     */
    public function ajaxGuardarDetalleAgenda() {
        if (isset($_POST["agenda_id"]) && isset($_POST["dia_semana"]) && 
            isset($_POST["turno_id"]) && isset($_POST["sala_id"]) && 
            isset($_POST["servicio_id"]) && isset($_POST["hora_inicio"]) && isset($_POST["hora_fin"])) {
            
            $datos = [
                "detalle_id" => isset($_POST["detalle_id"]) ? $_POST["detalle_id"] : "",
                "agenda_id" => $_POST["agenda_id"],
                "turno_id" => $_POST["turno_id"],
                "sala_id" => $_POST["sala_id"],
                "servicio_id" => $_POST["servicio_id"],
                "dia_semana" => $_POST["dia_semana"],
                "hora_inicio" => $_POST["hora_inicio"],
                "hora_fin" => $_POST["hora_fin"],
                "intervalo_minutos" => isset($_POST["intervalo_minutos"]) ? $_POST["intervalo_minutos"] : 15,
                "cupo_maximo" => isset($_POST["cupo_maximo"]) ? $_POST["cupo_maximo"] : 1,
                "detalle_estado" => isset($_POST["detalle_estado"]) ? $_POST["detalle_estado"] === "true" : true
            ];

            $resultado = ControllerAgendas::ctrGuardarDetalleAgenda($datos);
            echo json_encode($resultado);
        } else {
            echo json_encode(["error" => true, "mensaje" => "Todos los campos marcados con * son obligatorios"]);
        }
    }

    /**
     * Elimina un detalle de horario
     */
    public function ajaxEliminarDetalleAgenda() {
        if (isset($_POST["detalle_id"])) {
            $resultado = ControllerAgendas::ctrEliminarDetalleAgenda($_POST["detalle_id"]);
            echo json_encode($resultado);
        } else {
            echo json_encode(["error" => true, "mensaje" => "ID de detalle no proporcionado"]);
        }
    }

    /**
     * Obtiene todos los médicos disponibles
     */
    public function ajaxObtenerMedicos() {
        $medicos = ControllerAgendas::ctrObtenerMedicos();
        echo json_encode(["status" => "success", "data" => $medicos]);
    }

    /**
     * Obtiene todos los turnos disponibles
     */
    public function ajaxObtenerTurnos() {
        $turnos = ControllerAgendas::ctrObtenerTurnos();
        echo json_encode(["status" => "success", "data" => $turnos]);
    }

    /**
     * Obtiene todas las salas disponibles
     */
    public function ajaxObtenerSalas() {
        $salas = ControllerAgendas::ctrObtenerSalas();
        echo json_encode(["status" => "success", "data" => $salas]);
    }

    /**
     * Verifica si existe un horario duplicado
     */
    public function ajaxVerificarHorarioDuplicado() {
        $detalle_id = isset($_POST["detalle_id"]) ? $_POST["detalle_id"] : 0;
        $dia_semana = $_POST["dia_semana"];
        $turno_id = $_POST["turno_id"];
        $sala_id = $_POST["sala_id"];
        $hora_inicio = $_POST["hora_inicio"];
        $hora_fin = $_POST["hora_fin"];
        
        $duplicado = ControllerAgendas::ctrVerificarHorarioDuplicado(
            $detalle_id,
            $dia_semana, 
            $turno_id, 
            $sala_id, 
            $hora_inicio, 
            $hora_fin
        );
        
        echo json_encode($duplicado);
    }

    /**
     * ========== SERVICIOS POR DOCTOR ==========
     */

    /**
     * Obtiene todos los servicios disponibles
     */
    public function ajaxObtenerServicios() {
        $servicios = ControllerAgendas::ctrObtenerServicios();
        echo json_encode(["status" => "success", "data" => $servicios]);
    }

    /**
     * Obtiene todas las asociaciones servicio-doctor
     */
    public function ajaxObtenerServiciosDoctor() {
        $filtros = [];
        if (isset($_POST["medico_id"]) && !empty($_POST["medico_id"])) {
            $filtros["medico_id"] = $_POST["medico_id"];
        }
        if (isset($_POST["servicio_id"]) && !empty($_POST["servicio_id"])) {
            $filtros["servicio_id"] = $_POST["servicio_id"];
        }

        $serviciosDoctor = ControllerAgendas::ctrObtenerServiciosDoctor($filtros);
        echo json_encode(["status" => "success", "data" => $serviciosDoctor]);
    }

    /**
     * Obtiene una asociación servicio-doctor específica
     */
    public function ajaxObtenerServicioDoctor() {
        if (isset($_POST["id"])) {
            $servicioDoctor = ControllerAgendas::ctrObtenerServicioDoctorPorId($_POST["id"]);
            echo json_encode(["status" => "success", "data" => $servicioDoctor]);
        } else {
            echo json_encode(["status" => "error", "message" => "ID no proporcionado"]);
        }
    }

    /**
     * Crear nueva asociación servicio-doctor
     */
    public function ajaxCrearServicioDoctor() {
        if (isset($_POST["doctor_id"]) && isset($_POST["servicio_id"])) {
            $datos = [
                "doctor_id" => $_POST["doctor_id"],
                "servicio_id" => $_POST["servicio_id"],
                "is_active" => isset($_POST["is_active"]) ? $_POST["is_active"] === "on" : false,
                "horarios_especificos" => $this->procesarHorariosEspecificos()
            ];

            $resultado = ControllerAgendas::ctrCrearServicioDoctor($datos);
            echo json_encode($resultado);
        } else {
            echo json_encode(["status" => "error", "message" => "Datos incompletos para crear asociación"]);
        }
    }

    /**
     * Actualizar asociación servicio-doctor
     */
    public function ajaxActualizarServicioDoctor() {
        if (isset($_POST["servicioDoctor_id"]) && isset($_POST["doctor_id"]) && isset($_POST["servicio_id"])) {
            $datos = [
                "id" => $_POST["servicioDoctor_id"],
                "doctor_id" => $_POST["doctor_id"],
                "servicio_id" => $_POST["servicio_id"],
                "is_active" => isset($_POST["is_active"]) ? $_POST["is_active"] === "on" : false,
                "horarios_especificos" => $this->procesarHorariosEspecificos()
            ];

            $resultado = ControllerAgendas::ctrActualizarServicioDoctor($datos);
            echo json_encode($resultado);
        } else {
            echo json_encode(["status" => "error", "message" => "Datos incompletos para actualizar asociación"]);
        }
    }

    /**
     * Eliminar asociación servicio-doctor
     */
    public function ajaxEliminarServicioDoctor() {
        if (isset($_POST["id"])) {
            $resultado = ControllerAgendas::ctrEliminarServicioDoctor($_POST["id"]);
            echo json_encode($resultado);
        } else {
            echo json_encode(["status" => "error", "message" => "ID no proporcionado"]);
        }
    }

    /**
     * Procesar horarios específicos desde el formulario
     */
    private function procesarHorariosEspecificos() {
        $horarios = [];
        $dias = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado'];

        foreach ($dias as $dia) {
            if (isset($_POST["dias"]) && in_array(strtoupper($dia), $_POST["dias"])) {
                $inicio = $_POST["{$dia}_inicio"] ?? null;
                $fin = $_POST["{$dia}_fin"] ?? null;
                $intervalo = $_POST["{$dia}_intervalo"] ?? 15;
                $cupo = $_POST["{$dia}_cupo"] ?? 1;

                if (!empty($inicio) && !empty($fin)) {
                    $horarios[] = [
                        "dia_semana" => strtoupper($dia),
                        "hora_inicio" => $inicio,
                        "hora_fin" => $fin,
                        "intervalo_minutos" => (int)$intervalo,
                        "cupo_maximo" => (int)$cupo
                    ];
                }
            }
        }

        return $horarios;
    }
}

// Procesar las solicitudes AJAX
if (isset($_POST["action"])) {
    $ajax = new AjaxAgendas();
    
    switch ($_POST["action"]) {
        case "obtenerAgendas":
            $ajax->ajaxObtenerAgendas();
            break;
        case "obtenerAgendaPorId":
            $ajax->ajaxObtenerAgendaPorId();
            break;
        case "obtenerAgendasPorMedico":
            $ajax->ajaxObtenerAgendasPorMedico();
            break;
        case "guardarAgenda":
            $ajax->ajaxGuardarAgenda();
            break;
        case "eliminarAgenda":
            $ajax->ajaxEliminarAgenda();
            break;
        case "obtenerDetallesAgenda":
            $ajax->ajaxObtenerDetallesAgenda();
            break;
        case "obtenerDetalleAgenda":
            $ajax->ajaxObtenerDetalleAgenda();
            break;
        case "guardarDetalleAgenda":
            $ajax->ajaxGuardarDetalleAgenda();
            break;
        case "eliminarDetalleAgenda":
            $ajax->ajaxEliminarDetalleAgenda();
            break;
        case "obtenerMedicos":
            $ajax->ajaxObtenerMedicos();
            break;
        case "getMedicos":
            $ajax->ajaxObtenerMedicos();
            break;
        case "obtenerTurnos":
            $ajax->ajaxObtenerTurnos();
            break;
        case "obtenerSalas":
            $ajax->ajaxObtenerSalas();
            break;
        case "verificarHorarioDuplicado":
            $ajax->ajaxVerificarHorarioDuplicado();
            break;
        
        // Servicios por Doctor
        case "getServicios":
            $ajax->ajaxObtenerServicios();
            break;
        case "getServiciosDoctor":
            $ajax->ajaxObtenerServiciosDoctor();
            break;
        case "getServicioDoctor":
            $ajax->ajaxObtenerServicioDoctor();
            break;
        case "createServicioDoctor":
            $ajax->ajaxCrearServicioDoctor();
            break;
        case "updateServicioDoctor":
            $ajax->ajaxActualizarServicioDoctor();
            break;
        case "deleteServicioDoctor":
            $ajax->ajaxEliminarServicioDoctor();
            break;
    }
}