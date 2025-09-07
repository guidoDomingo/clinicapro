<?php
// Archivo de prueba simple para verificar que las clases se cargan correctamente

// Establecer el directorio base
$baseDir = dirname(__FILE__);

// Cargar archivos necesarios
require_once $baseDir . "/model/conexion.php";
require_once $baseDir . "/model/agendas.model.php";
require_once $baseDir . "/controller/agendas.controller.php";

// Establecer cabeceras para JSON
header('Content-Type: application/json');

// Verificar que se recibió una acción
if (!isset($_POST['action'])) {
    echo json_encode(['status' => 'error', 'message' => 'No action specified']);
    exit;
}

$action = $_POST['action'];

try {
    switch ($action) {
        case 'getMedicos':
            $medicos = ControllerAgendas::ctrObtenerMedicos();
            echo json_encode(['status' => 'success', 'data' => $medicos]);
            break;
            
        case 'getServicios':
            $servicios = ControllerAgendas::ctrObtenerServicios();
            echo json_encode(['status' => 'success', 'data' => $servicios]);
            break;
            
        case 'getServiciosDoctor':
            $serviciosDoctor = ControllerAgendas::ctrObtenerServiciosDoctor();
            echo json_encode(['status' => 'success', 'data' => $serviciosDoctor]);
            break;
            
        default:
            echo json_encode(['status' => 'error', 'message' => 'Invalid action: ' . $action]);
            break;
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>