<?php
// Test de búsqueda de pacientes
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Test de Búsqueda de Pacientes</h2>";

// Simulación de búsqueda
$termino = "vis";
$data = [
    'accion' => 'buscar_por_nombre',
    'termino' => $termino
];

echo "<h3>Probando búsqueda con término: '$termino'</h3>";

// Hacer petición POST simulada
$postdata = http_build_query($data);
$opts = array(
    'http' => array(
        'method'  => 'POST',
        'header'  => 'Content-Type: application/x-www-form-urlencoded',
        'content' => $postdata
    )
);

$context = stream_context_create($opts);
$url = 'http://localhost/clinica/ajax/persona.ajax.php';

try {
    $result = file_get_contents($url, false, $context);
    echo "<h4>Respuesta del servidor:</h4>";
    echo "<pre>" . htmlspecialchars($result) . "</pre>";
    
    $decoded = json_decode($result, true);
    if ($decoded) {
        echo "<h4>JSON decodificado:</h4>";
        echo "<pre>" . print_r($decoded, true) . "</pre>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}

// Test directo de la funcionalidad
echo "<hr><h3>Test directo de funciones</h3>";

// Incluir archivos necesarios
require_once "model/personas.model.php";

// Test directo del modelo
try {
    $datos = [
        'documento' => '',
        'nro_ficha' => '',
        'nombres' => $termino
    ];
    
    echo "<h4>Llamando directamente al modelo con datos:</h4>";
    echo "<pre>" . print_r($datos, true) . "</pre>";
    
    $resultados = ModelPersonas::mdlGetPersonaParam($datos);
    
    echo "<h4>Resultado del modelo:</h4>";
    echo "<pre>" . print_r($resultados, true) . "</pre>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error en modelo: " . $e->getMessage() . "</p>";
}

?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
pre { background: #f4f4f4; padding: 10px; border-radius: 4px; }
h2, h3, h4 { color: #333; }
hr { margin: 20px 0; }
</style>
