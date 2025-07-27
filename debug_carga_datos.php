<?php
// Debug para verificar carga de datos de informe_imagen
header('Content-Type: application/json');

// Incluir configuración
include_once('conexion.php');

// Obtener ID de consulta desde parámetros
$consulta_id = $_GET['consulta_id'] ?? null;

if (!$consulta_id) {
    echo json_encode(['error' => 'No se proporcionó ID de consulta']);
    exit;
}

try {
    // Consultar datos completos
    $query = "SELECT * FROM consulta_informe_imagen WHERE consulta_id = $1";
    $result = pg_query_params($con, $query, [$consulta_id]);
    
    if (!$result) {
        throw new Exception('Error en query: ' . pg_last_error($con));
    }
    
    $consulta = pg_fetch_assoc($result);
    
    if (!$consulta) {
        echo json_encode(['error' => 'Consulta no encontrada', 'consulta_id' => $consulta_id]);
        exit;
    }
    
    // Debug: mostrar estructura completa
    echo json_encode([
        'success' => true,
        'consulta_id' => $consulta_id,
        'datos_completos' => $consulta,
        'archivos_od_raw' => $consulta['archivos_od'] ?? 'NULL',
        'archivos_oi_raw' => $consulta['archivos_oi'] ?? 'NULL',
        'archivos_od_parsed' => !empty($consulta['archivos_od']) ? json_decode($consulta['archivos_od'], true) : [],
        'archivos_oi_parsed' => !empty($consulta['archivos_oi']) ? json_decode($consulta['archivos_oi'], true) : [],
        'debug_info' => [
            'php_version' => phpversion(),
            'timestamp' => date('Y-m-d H:i:s')
        ]
    ], JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    echo json_encode([
        'error' => $e->getMessage(),
        'consulta_id' => $consulta_id
    ]);
}
?>
