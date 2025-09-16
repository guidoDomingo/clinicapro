<?php
require_once "../controller/preformatos.controller.php";

class CargarDatosAjax {
    /**
     * Obtiene todos los motivos comunes activos
     */
    public function ajaxGetMotivosComunes() {
        error_log("[" . date('Y-m-d H:i:s') . "] Solicitando motivos comunes", 3, "/var/log/clinica/database.log");
        $motivos = ControllerPreformatos::ctrGetMotivosComunes();
        error_log("[" . date('Y-m-d H:i:s') . "] Motivos comunes obtenidos: " . json_encode($motivos), 3, "/var/log/clinica/database.log");
        echo json_encode([
            'status' => 'success',
            'data' => $motivos
        ]);
    }
    
    /**
     * Obtiene todos los preformatos activos de un tipo específico
     * @param string $tipo Tipo de preformato ('consulta' o 'receta')
     */
    public function ajaxGetPreformatos($tipo) {
        error_log("[" . date('Y-m-d H:i:s') . "] Solicitando preformatos de tipo: " . $tipo, 3, "/var/log/clinica/database.log");
        $preformatos = ControllerPreformatos::ctrGetPreformatos($tipo);
        error_log("[" . date('Y-m-d H:i:s') . "] Preformatos obtenidos: " . json_encode($preformatos), 3, "/var/log/clinica/database.log");
        echo json_encode([
            'status' => 'success',
            'data' => $preformatos
        ]);
    }
}

// Procesamiento de la solicitud AJAX
if (isset($_POST['operacion'])) {
    error_log("[" . date('Y-m-d H:i:s') . "] Operación solicitada: " . $_POST['operacion'], 3, "/var/log/clinica/database.log");
    $cargarDatos = new CargarDatosAjax();
    
    switch ($_POST['operacion']) {
        case 'getMotivosComunes':
            $cargarDatos->ajaxGetMotivosComunes();
            break;
            
        case 'getPreformatosConsulta':
            $cargarDatos->ajaxGetPreformatos('consulta');
            break;
            
        case 'getPreformatosReceta':
            $cargarDatos->ajaxGetPreformatos('receta');
            break;
            
        default:
            error_log("[" . date('Y-m-d H:i:s') . "] Operación no válida: " . $_POST['operacion'], 3, "/var/log/clinica/database.log");
            echo json_encode([
                'status' => 'error',
                'message' => 'Operación no válida'
            ]);
    }
} else {
    error_log("[" . date('Y-m-d H:i:s') . "] No se recibió operación", 3, "/var/log/clinica/database.log");
    echo json_encode([
        'status' => 'error',
        'message' => 'No se especificó operación'
    ]);
}