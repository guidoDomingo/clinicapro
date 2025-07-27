<?php
// Debug para verificar búsqueda de pacientes
header('Content-Type: application/json');

// Incluir configuración
include_once('conexion.php');

// Obtener término de búsqueda
$searchTerm = $_GET['search'] ?? '';

echo json_encode([
    'debug_info' => [
        'search_term' => $searchTerm,
        'php_version' => phpversion(),
        'timestamp' => date('Y-m-d H:i:s'),
        'database_status' => 'Conectando...'
    ]
]);

try {
    // Probar conexión a base de datos
    $pdo = new PDO("pgsql:host=localhost;dbname=clinica", "postgres", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Consulta simple para verificar tabla personas
    $query = "SELECT COUNT(*) as total FROM personas LIMIT 1";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'status' => 'success',
        'search_term' => $searchTerm,
        'database_connection' => 'OK',
        'total_personas' => $result['total'],
        'debug_info' => [
            'timestamp' => date('Y-m-d H:i:s'),
            'query_executed' => $query
        ]
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'error_message' => $e->getMessage(),
        'search_term' => $searchTerm,
        'debug_info' => [
            'timestamp' => date('Y-m-d H:i:s'),
            'error_details' => $e->getTraceAsString()
        ]
    ]);
}
?>
