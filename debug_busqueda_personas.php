<?php
// Debug para verificar búsqueda específica de personas
header('Content-Type: application/json');

// Incluir modelos
require_once('model/personas.model.php');

// Simular búsqueda como lo hace el autocompletado
$termino = $_GET['termino'] ?? 'nico';

echo json_encode([
    'debug_info' => [
        'termino_busqueda' => $termino,
        'timestamp' => date('Y-m-d H:i:s'),
        'testing_model' => 'ModelPersonas::mdlGetPersonaParam'
    ]
]);

try {
    // Preparar datos como lo hace el autocompletado
    $datos = [
        'documento' => '',
        'nro_ficha' => '',
        'nombres' => $termino
    ];
    
    // Ejecutar búsqueda
    $resultados = ModelPersonas::mdlGetPersonaParam($datos);
    
    echo json_encode([
        'status' => 'success',
        'termino_busqueda' => $termino,
        'datos_enviados' => $datos,
        'resultados_raw' => $resultados,
        'tipo_resultado' => gettype($resultados),
        'es_array' => is_array($resultados),
        'tiene_multiple' => isset($resultados['multiple']),
        'debug_info' => [
            'timestamp' => date('Y-m-d H:i:s'),
            'model_called' => 'OK'
        ]
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'error_message' => $e->getMessage(),
        'termino_busqueda' => $termino,
        'debug_info' => [
            'timestamp' => date('Y-m-d H:i:s'),
            'error_trace' => $e->getTraceAsString()
        ]
    ]);
}
?>
