<?php
// Versión simplificada para debug - ajax/preformatos_simple.ajax.php

// Configurar manejo de errores
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', '../logs/preformatos_simple.log');

// Configurar header JSON
header('Content-Type: application/json');

try {
    // Verificar rutas de archivos antes de incluir
    $controller_path = "../controller/preformatos.controller.php";
    $conexion_path = "../model/conexion.php";
    
    if (!file_exists($controller_path)) {
        throw new Exception("No se encuentra el archivo controller: $controller_path");
    }
    
    if (!file_exists($conexion_path)) {
        throw new Exception("No se encuentra el archivo conexion: $conexion_path");
    }
    
    // Incluir archivos necesarios
    require_once $conexion_path;
    require_once $controller_path;
    
    // Verificar que las clases existen
    if (!class_exists('Conexion')) {
        throw new Exception("Clase Conexion no encontrada");
    }
    
    if (!class_exists('ControllerPreformatos')) {
        throw new Exception("Clase ControllerPreformatos no encontrada");
    }
    
    // Test de conexión básica
    $db = Conexion::conectar();
    if (!$db) {
        throw new Exception("No se pudo conectar a la base de datos");
    }
    
    // Procesar operación simple
    if (isset($_POST['operacion'])) {
        switch ($_POST['operacion']) {
            case 'test':
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Test básico exitoso',
                    'data' => [
                        'timestamp' => date('Y-m-d H:i:s'),
                        'post_data' => $_POST
                    ]
                ]);
                break;
                
            case 'getDoctorByUserId':
                if (!isset($_POST['user_id'])) {
                    throw new Exception("user_id no especificado");
                }
                
                $userId = $_POST['user_id'];
                
                // Consulta simple y directa
                $stmt = $db->prepare("SELECT doctor_id, person_id FROM rh_doctors WHERE doctor_id = :user_id LIMIT 1");
                $stmt->bindParam(":user_id", $userId, PDO::PARAM_INT);
                $stmt->execute();
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                
                echo json_encode([
                    'status' => 'success',
                    'data' => $result ?: null,
                    'message' => $result ? 'Doctor encontrado' : 'Doctor no encontrado'
                ]);
                break;
                
            default:
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Operación no reconocida: ' . $_POST['operacion']
                ]);
        }
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'No se especificó operación'
        ]);
    }
    
} catch (Exception $e) {
    // Log del error
    error_log("Error en preformatos_simple: " . $e->getMessage());
    
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
}
?>
