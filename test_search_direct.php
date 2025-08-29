<?php
header('Content-Type: application/json');
$_SERVER['REQUEST_METHOD'] = 'POST';

// Datos de prueba para búsqueda
$searchData = [
    'action' => 'search',
    'table' => 'rh_person', 
    'search' => [
        'first_name' => 'andres'  // Usar el nombre que vimos en los datos
    ]
];

// Simular el envío JSON
$json_input = json_encode($searchData);
file_put_contents('php://input', $json_input);
$_SERVER['CONTENT_TYPE'] = 'application/json';

// Capturar y mostrar respuesta
ob_start();

try {
    include 'modules/consultas/api/livewire-system.php';
} catch (Exception $e) {
    echo json_encode(['error' => 'Exception: ' . $e->getMessage()]);
}

$output = ob_get_clean();
echo $output;
?>