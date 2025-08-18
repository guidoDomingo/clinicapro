<?php
/**
 * Endpoint para obtener valores referenciales de Adición para anteojos desde la base de datos
 */

try {
    // Incluir conexión a base de datos usando ruta relativa simple
    require_once '../../model/conexion.php';
    
    $pdo = Conexion::conectar();
    
    if (!$pdo) {
        throw new Exception('No se pudo conectar a la base de datos');
    }
    
    // Consultar valores de adición desde la base de datos
    $sql = "SELECT rv.id, rv.valor, rv.etiqueta, rv.orden_visualizacion
            FROM referencial_valores rv
            INNER JOIN referenciales r ON rv.referencial_id = r.id
            WHERE r.codigo = 'valores_adicion' AND rv.activo = 1 
            AND r.activo = 1
            ORDER BY rv.orden_visualizacion, rv.id";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $adicion_valores = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Headers para JSON
    header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
    
    // Manejar preflight request
    if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit();
    }
    
    // Respuesta exitosa
    echo json_encode([
        'success' => true,
        'data' => $adicion_valores,
        'message' => 'Valores de adición cargados desde BD',
        'total' => count($adicion_valores)
    ]);
    
} catch (Exception $e) {
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error al obtener valores de adición: ' . $e->getMessage(),
        'data' => []
    ]);
}
?>
