<?php
require_once "../controller/consultas.controller.php";
require_once "../model/consultas.model.php";

class ConsultaAjax {
    public function ajaxGetConsultaPersona($persona) {
        $response = ModelConsulta::mdlGetConsultaPersona($persona);
        echo $response;
    }
    public function ajaxGetResumenConsulta($datos) {
        $response = ModelConsulta::mdlGetConsultaResumen($datos);
        echo json_encode($response);
    }
    
    public function ajaxObtenerPacientePorConsulta($idConsulta) {
        // Crear una instancia del modelo para usar métodos no estáticos
        $modelo = new ModelConsulta();
        
        // Primero obtener la consulta con datos del paciente
        $consulta = $modelo->obtenerConsulta($idConsulta);
        
        if ($consulta && isset($consulta['id_persona'])) {
            // Si tenemos ID de persona, obtener datos completos
            $paciente = $modelo->obtenerDatosPersona($consulta['id_persona']);
            
            if ($paciente) {
                echo json_encode($paciente);
                return;
            }
        }
        
        // Si llegamos aquí, no se encontraron datos
        echo json_encode([
            'status' => 'error',
            'message' => 'No se encontró el paciente asociado a esta consulta'
        ]);
    }
    
    public function ajaxGetHistorialConsultas($idPersona) {
        $response = ModelConsulta::mdlGetConsultaPersona($idPersona);
        // Asegurarse de que la respuesta sea un JSON válido
        $data = json_decode($response, true);
        if (isset($data['status']) && $data['status'] === 'success' && isset($data['data'])) {
            echo json_encode($data['data']);
        } else {
            echo json_encode([]);
        }
    }
    
    public function ajaxGetDetalleConsulta($idConsulta) {
        $response = ModelConsulta::mdlGetDetalleConsulta($idConsulta);
        echo $response; // El modelo ya devuelve un JSON formateado
    }
    
    public function ajaxGetAllConsultas($tipoFormulario = null) {
        $response = ModelConsulta::mdlGetAllConsultas($tipoFormulario);
        
        // Verificar si hay error y devolverlo como JSON válido
        if(isset($response['error'])) {
            echo json_encode(['error' => $response['error']]);
            return;
        }
        
        // Asegurar que la respuesta sea JSON válido
        echo json_encode($response);
    }
    
    public function ajaxGetConsultasByPaciente($idPersona, $tipoFormulario = null) {
        $response = ModelConsulta::mdlGetConsultasByPaciente($idPersona, $tipoFormulario);
        
        // Verificar si hay error y devolverlo como JSON válido
        if(isset($response['error'])) {
            echo json_encode(['error' => $response['error']]);
            return;
        }
        
        // Asegurar que la respuesta sea JSON válido
        echo json_encode($response);
    }
}
// Procesar la eliminación de una consulta
if (isset($_POST["id_persona"]) && isset($_POST["operacion"]) && $_POST["operacion"] === "buscarConsultaPersona") {
    $getConsultaPersona = new ConsultaAjax();
    $getConsultaPersona->ajaxGetConsultaPersona($_POST["id_persona"]);
    
}
// Procesar consulta cantidad de consultas y ultima consulta
if (isset($_POST["id_persona"]) && isset($_POST["operacion"]) && $_POST["operacion"] === "resumenConsulta") {
    $getResumenConsulta = new ConsultaAjax();
    $datos = array();
    foreach ($_POST as $key => $value) {
        $datos[$key] = $value;
    }
    $getResumenConsulta->ajaxGetResumenConsulta($datos);
    
}

// Procesar historial de consultas
if (isset($_POST["id_persona"]) && isset($_POST["operacion"]) && $_POST["operacion"] === "historialConsultas") {
    $historialConsultas = new ConsultaAjax();
    $historialConsultas->ajaxGetHistorialConsultas($_POST["id_persona"]);
}

// Procesar detalle de consulta
if (isset($_POST["id_consulta"]) && isset($_POST["operacion"]) && $_POST["operacion"] === "detalleConsulta") {
    $detalleConsulta = new ConsultaAjax();
    $detalleConsulta->ajaxGetDetalleConsulta($_POST["id_consulta"]);
}

// Procesar lista de todas las consultas
if (isset($_POST["operacion"]) && $_POST["operacion"] === "getAllConsultas") {
    $tipoFormulario = isset($_POST["tipo_formulario"]) ? $_POST["tipo_formulario"] : null;
    $allConsultas = new ConsultaAjax();
    $allConsultas->ajaxGetAllConsultas($tipoFormulario);
}

// Procesar consultas por paciente
if (isset($_POST["id_persona"]) && isset($_POST["operacion"]) && $_POST["operacion"] === "getConsultasByPaciente") {
    $tipoFormulario = isset($_POST["tipo_formulario"]) ? $_POST["tipo_formulario"] : null;
    $consultasPaciente = new ConsultaAjax();
    $consultasPaciente->ajaxGetConsultasByPaciente($_POST["id_persona"], $tipoFormulario);
}

// Procesar obtención de paciente por consulta
if (isset($_POST["id_consulta"]) && isset($_POST["operacion"]) && $_POST["operacion"] === "obtenerPacientePorConsulta") {
    $consultaPaciente = new ConsultaAjax();
    $consultaPaciente->ajaxObtenerPacientePorConsulta($_POST["id_consulta"]);
}