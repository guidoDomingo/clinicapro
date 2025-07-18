<?php
// Incluir archivos necesarios
require_once "model/conexion.php";
require_once "model/servicios.model.php";
require_once "controller/servicios.controller.php";

// Configurar headers para JSON
header('Content-Type: application/json');

// Obtener la fecha de la consulta
$fecha = $_GET['fecha'] ?? date('Y-m-d');

echo json_encode([
    'fecha_consultada' => $fecha,
    'timestamp' => date('Y-m-d H:i:s'),
    'test_directo' => true
]);

try {
    // Probar conexión
    $conexion = Conexion::conectar();
    if (!$conexion) {
        throw new Exception("No se pudo conectar a la base de datos");
    }

    // Probar método del modelo directamente
    $doctoresModelo = ModelServicios::mdlObtenerDoctoresPorFecha($fecha);
    
    // Probar método del controlador
    $doctoresControlador = ControladorServicios::ctrObtenerDoctoresPorFecha($fecha);
    
    // Calcular día de la semana en español (igual que en el modelo)
    $fechaObj = DateTime::createFromFormat('Y-m-d', $fecha);
    $diaSemanaNum = (int)$fechaObj->format('N'); // ISO-8601
    $diasSemanaTexto = [
        1 => 'LUNES', 
        2 => 'MARTES', 
        3 => 'MIERCOLES', 
        4 => 'JUEVES', 
        5 => 'VIERNES', 
        6 => 'SABADO', 
        7 => 'DOMINGO'
    ];
    $diaSemanaTextoEspanol = $diasSemanaTexto[$diaSemanaNum];
    
    // Devolver resultados
    echo json_encode([
        'status' => 'success',
        'fecha' => $fecha,
        'dia_semana' => $diaSemanaNum,
        'dia_semana_texto' => $diaSemanaTextoEspanol,
        'conexion' => 'OK',
        'modelo_resultado' => $doctoresModelo,
        'controlador_resultado' => $doctoresControlador,
        'total_modelo' => count($doctoresModelo),
        'total_controlador' => count($doctoresControlador),
        'timestamp' => date('Y-m-d H:i:s')
    ], JSON_PRETTY_PRINT);

} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'mensaje' => $e->getMessage(),
        'archivo' => $e->getFile(),
        'linea' => $e->getLine(),
        'fecha' => $fecha,
        'timestamp' => date('Y-m-d H:i:s')
    ], JSON_PRETTY_PRINT);
}
?>
