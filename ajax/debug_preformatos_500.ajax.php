<?php
// Debug específico del error 500 con manejo detallado

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', '../logs/debug_500_detallado.log');

// Iniciar captura de errores
ob_start();

// Función para registrar errores detallados
function registrarError($mensaje, $contexto = []) {
    $log = date('[Y-m-d H:i:s] ') . $mensaje;
    if (!empty($contexto)) {
        $log .= ' | Contexto: ' . json_encode($contexto);
    }
    file_put_contents('../logs/debug_500_detallado.log', $log . PHP_EOL, FILE_APPEND);
}

registrarError("Iniciando debug del endpoint AJAX", $_POST);

try {
    // Verificar y cargar conexión
    $conexion_path = "../model/conexion.php";
    if (!file_exists($conexion_path)) {
        throw new Exception("Archivo conexion.php no encontrado en: $conexion_path");
    }
    
    registrarError("Cargando conexión desde: $conexion_path");
    require_once $conexion_path;
    
    if (!class_exists('Conexion')) {
        throw new Exception("Clase Conexion no disponible después de incluir $conexion_path");
    }
    
    // Test de conexión
    $db = Conexion::conectar();
    if (!$db) {
        throw new Exception("Conexión a base de datos falló");
    }
    registrarError("Conexión a BD exitosa");
    
    // Verificar y cargar modelo
    $modelo_path = "../model/preformatos.model.php";
    if (!file_exists($modelo_path)) {
        throw new Exception("Archivo preformatos.model.php no encontrado en: $modelo_path");
    }
    
    registrarError("Cargando modelo desde: $modelo_path");
    require_once $modelo_path;
    
    if (!class_exists('ModelPreformatos')) {
        throw new Exception("Clase ModelPreformatos no disponible después de incluir $modelo_path");
    }
    
    // Verificar y cargar controlador
    $controller_path = "../controller/preformatos.controller.php";
    if (!file_exists($controller_path)) {
        throw new Exception("Archivo preformatos.controller.php no encontrado en: $controller_path");
    }
    
    registrarError("Cargando controlador desde: $controller_path");
    require_once $controller_path;
    
    if (!class_exists('ControllerPreformatos')) {
        throw new Exception("Clase ControllerPreformatos no disponible después de incluir $controller_path");
    }
    
    registrarError("Todas las dependencias cargadas correctamente");
    
    // Procesar operación específica
    if (isset($_POST['operacion']) && $_POST['operacion'] === 'getDoctorByUserId') {
        registrarError("Procesando operación getDoctorByUserId", ['user_id' => $_POST['user_id'] ?? 'no definido']);
        
        if (!isset($_POST['user_id'])) {
            throw new Exception("user_id no especificado en la petición");
        }
        
        $userId = $_POST['user_id'];
        registrarError("Consultando doctor para usuario ID: $userId");
        
        // Consulta directa sin usar el modelo complejo
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
        
        $stmt->bindParam(":user_id", $userId, PDO::PARAM_INT);
        $stmt->execute();
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        
        registrarError("Resultado de consulta", ['encontrado' => $resultado ? 'sí' : 'no', 'datos' => $resultado]);
        
        // Limpiar buffer antes de enviar respuesta
        $buffer_content = ob_get_clean();
        if (!empty($buffer_content)) {
            registrarError("Buffer content capturado", ['content' => $buffer_content]);
        }
        
        header('Content-Type: application/json');
        
        if ($resultado) {
            echo json_encode([
                'status' => 'success',
                'data' => $resultado
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'No se encontró un doctor asociado a este usuario'
            ]);
        }
        
        registrarError("Respuesta JSON enviada exitosamente");
        
    } else {
        // Limpiar buffer
        ob_get_clean();
        
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'error',
            'message' => 'Operación no soportada en este endpoint de debug'
        ]);
    }
    
} catch (ParseError $e) {
    ob_get_clean();
    registrarError("Error de sintaxis PHP", [
        'mensaje' => $e->getMessage(),
        'archivo' => $e->getFile(),
        'linea' => $e->getLine()
    ]);
    
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'error',
        'type' => 'ParseError',
        'message' => $e->getMessage(),
        'file' => basename($e->getFile()),
        'line' => $e->getLine()
    ]);
    
} catch (Error $e) {
    ob_get_clean();
    registrarError("Error fatal PHP", [
        'mensaje' => $e->getMessage(),
        'archivo' => $e->getFile(),
        'linea' => $e->getLine()
    ]);
    
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'error',
        'type' => 'Fatal Error',
        'message' => $e->getMessage(),
        'file' => basename($e->getFile()),
        'line' => $e->getLine()
    ]);
    
} catch (Exception $e) {
    ob_get_clean();
    registrarError("Excepción capturada", [
        'mensaje' => $e->getMessage(),
        'archivo' => $e->getFile(),
        'linea' => $e->getLine()
    ]);
    
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'error',
        'type' => 'Exception',
        'message' => $e->getMessage(),
        'file' => basename($e->getFile()),
        'line' => $e->getLine()
    ]);
}
?>
