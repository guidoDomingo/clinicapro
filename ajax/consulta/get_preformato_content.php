<?php
/**
 * Endpoint para obtener contenido de preformatos específicos desde la base de datos
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Manejar preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    // Incluir conexión a base de datos
    require_once __DIR__ . '/../../model/conexion.php';
    
    // Obtener datos del request
    $input = json_decode(file_get_contents('php://input'), true);
    $preformato_id = isset($input['preformato_id']) ? intval($input['preformato_id']) : 0;
    
    if (!$preformato_id) {
        throw new Exception('ID de preformato requerido');
    }
    
    // Conectar a la base de datos
    $pdo = Conexion::conectar();
    
    if (!$pdo) {
        throw new Exception('No se pudo conectar a la base de datos');
    }
    
    // Consultar el contenido del preformato desde la base de datos
    $sql = "SELECT 
                id_preformato as id,
                nombre,
                contenido,
                tipo_formulario,
                tipo
            FROM preformatos 
            WHERE id_preformato = :preformato_id 
            AND activo = true";
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':preformato_id', $preformato_id, PDO::PARAM_INT);
    $stmt->execute();
    
    $preformato = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$preformato) {
        throw new Exception('Preformato no encontrado');
    }
    
    // Respuesta exitosa
    echo json_encode([
        'success' => true,
        'contenido' => $preformato['contenido'] ?: '',
        'preformato_id' => $preformato['id'],
        'nombre' => $preformato['nombre'],
        'tipo_formulario' => $preformato['tipo_formulario'],
        'tipo' => $preformato['tipo'],
        'message' => 'Contenido de preformato obtenido correctamente desde la base de datos'
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error de base de datos: ' . $e->getMessage(),
        'contenido' => ''
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'contenido' => ''
    ]);
}
?>
