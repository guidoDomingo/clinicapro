<?php
// Test directo del endpoint AJAX
// Cambiar directorio para que las rutas relativas funcionen
chdir(__DIR__);

// Ahora incluir con las rutas correctas
require_once "controller/consultas.controller.php";
require_once "model/consultas.model.php";

// Incluir la clase ConsultaAjax después de las dependencias
require_once "ajax/consultas.ajax.php";

echo "<h1>Test Directo del AJAX</h1>";
echo "<h2>Simulando POST para detalleConsulta</h2>";

// Simular POST
$_POST["id_consulta"] = "1";
$_POST["operacion"] = "detalleConsulta";

echo "<pre>";
echo "POST simulado:\n";
print_r($_POST);
echo "</pre>";

echo "<h3>Respuesta del AJAX:</h3>";
echo "<pre>";

// Capturar la salida
ob_start();

if (isset($_POST["id_consulta"]) && isset($_POST["operacion"]) && $_POST["operacion"] === "detalleConsulta") {
    echo "Condición cumplida, ejecutando...\n";
    $detalleConsulta = new ConsultaAjax();
    $detalleConsulta->ajaxGetDetalleConsulta($_POST["id_consulta"]);
} else {
    echo "Condición no cumplida\n";
    echo "id_consulta isset: " . (isset($_POST["id_consulta"]) ? "true" : "false") . "\n";
    echo "operacion isset: " . (isset($_POST["operacion"]) ? "true" : "false") . "\n";
    echo "operacion value: " . ($_POST["operacion"] ?? "null") . "\n";
}

$output = ob_get_clean();
echo htmlspecialchars($output);
echo "</pre>";

// También probar directamente el modelo
echo "<h3>Test directo del modelo:</h3>";
echo "<pre>";

try {
    require_once "model/consultas.model.php";
    
    ob_start();
    $response = ModelConsulta::mdlGetDetalleConsulta("1");
    $modelOutput = ob_get_clean();
    
    echo "Salida del modelo (capturada): " . htmlspecialchars($modelOutput) . "\n";
    echo "Respuesta del modelo: " . (is_string($response) ? htmlspecialchars($response) : print_r($response, true)) . "\n";
    
} catch (Exception $e) {
    echo "Error en modelo: " . $e->getMessage();
}

echo "</pre>";
?>
