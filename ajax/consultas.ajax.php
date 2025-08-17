<?php
// Configuración para respuestas AJAX limpias
ini_set('display_errors', 0);
error_reporting(E_ERROR | E_PARSE);

// Iniciar buffer de salida y configurar headers para JSON limpio
ob_start();
header('Content-Type: application/json; charset=utf-8');

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

    public function ajaxEliminarConsulta($idConsulta) {
        try {
            // Obtener conexión
            require_once "../model/conexion.php";
            $db = Conexion::conectar();
            
            if (!$db) {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Error de conexión a la base de datos'
                ]);
                return;
            }
            
            // Comenzar transacción
            $db->beginTransaction();
            
            try {
                // Verificar que la consulta existe
                $stmt_check = $db->prepare("SELECT id_consulta FROM consultas WHERE id_consulta = :id_consulta");
                $stmt_check->bindParam(":id_consulta", $idConsulta, PDO::PARAM_INT);
                $stmt_check->execute();
                
                if ($stmt_check->rowCount() === 0) {
                    $db->rollBack();
                    echo json_encode([
                        'status' => 'error',
                        'message' => 'La consulta no existe o ya fue eliminada'
                    ]);
                    return;
                }
                
                // Eliminar datos específicos de anteojos si existen
                try {
                    $stmt_anteojos = $db->prepare("DELETE FROM consulta_anteojos WHERE id_consulta = :id_consulta");
                    $stmt_anteojos->bindParam(":id_consulta", $idConsulta, PDO::PARAM_INT);
                    $stmt_anteojos->execute();
                } catch (Exception $e) {
                    // Tabla puede no existir, continuar
                }
                
                // Eliminar datos específicos de informe imagen si existen
                try {
                    $stmt_informe = $db->prepare("DELETE FROM consulta_informe_imagen WHERE id_consulta = :id_consulta");
                    $stmt_informe->bindParam(":id_consulta", $idConsulta, PDO::PARAM_INT);
                    $stmt_informe->execute();
                } catch (Exception $e) {
                    // Tabla puede no existir, continuar
                }
                
                // Eliminar la consulta principal
                $stmt_consulta = $db->prepare("DELETE FROM consultas WHERE id_consulta = :id_consulta");
                $stmt_consulta->bindParam(":id_consulta", $idConsulta, PDO::PARAM_INT);
                $stmt_consulta->execute();
                
                if ($stmt_consulta->rowCount() > 0) {
                    // Confirmar transacción
                    $db->commit();
                    
                    echo json_encode([
                        'status' => 'success',
                        'message' => 'Consulta eliminada correctamente',
                        'eliminado' => true
                    ]);
                } else {
                    $db->rollBack();
                    echo json_encode([
                        'status' => 'error',
                        'message' => 'No se pudo eliminar la consulta'
                    ]);
                }
                
            } catch (Exception $e) {
                $db->rollBack();
                throw $e;
            }
            
        } catch (Exception $e) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Error interno: ' . $e->getMessage()
            ]);
        }
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
        // Log para debugging
        error_log("AJAX DEBUG: ajaxGetDetalleConsulta llamado con ID: " . $idConsulta);
        
        try {
            // Limpiar buffer de salida antes de enviar respuesta
            ob_clean();
            
            $response = ModelConsulta::mdlGetDetalleConsulta($idConsulta);
            
            // Verificar si $response ya es un string JSON o un array
            if (is_string($response)) {
                // Si es string, asumir que ya es JSON y enviarlo
                error_log("AJAX DEBUG: Respuesta es string: " . substr($response, 0, 200));
                echo $response;
            } else {
                // Si es array, convertir a JSON
                error_log("AJAX DEBUG: Respuesta es array, convirtiendo a JSON");
                echo json_encode($response);
            }
        } catch (Exception $e) {
            error_log("AJAX ERROR: " . $e->getMessage());
            ob_clean(); // Limpiar cualquier salida previa
            echo json_encode([
                'error' => true,
                'message' => 'Error al obtener detalle de consulta: ' . $e->getMessage()
            ]);
        }
        
        // Finalizar y enviar buffer
        ob_end_flush();
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
    error_log("AJAX DEBUG: Procesando detalleConsulta para ID: " . $_POST["id_consulta"]);
    $detalleConsulta = new ConsultaAjax();
    $detalleConsulta->ajaxGetDetalleConsulta($_POST["id_consulta"]);
    exit; // Agregar exit para evitar output adicional
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
// Procesar eliminaci�n de consulta
if (isset($_POST["id_consulta"]) && isset($_POST["operacion"]) && $_POST["operacion"] === "eliminarConsulta") {
    $eliminarConsulta = new ConsultaAjax();
    $eliminarConsulta->ajaxEliminarConsulta($_POST["id_consulta"]);
}
