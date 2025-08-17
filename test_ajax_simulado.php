<?php
// Test AJAX simulado
session_start();

// Simular POST como lo haría jQuery
$_POST["id_consulta"] = "1";
$_POST["operacion"] = "detalleConsulta";

echo "<h1>Test AJAX Simulado</h1>";
echo "<h2>Simulando petición AJAX POST</h2>";

echo "<pre>";
echo "POST Data:\n";
print_r($_POST);
echo "</pre>";

// Cambiar al directorio correcto para las rutas relativas
$originalDir = getcwd();
chdir(dirname(__FILE__));

echo "<h3>Respuesta del endpoint:</h3>";
echo "<div style='background: #f0f0f0; padding: 10px; border: 1px solid #ccc;'>";

// Capturar toda la salida
ob_start();

try {
    // Incluir los archivos necesarios en el orden correcto
    require_once "controller/consultas.controller.php";
    require_once "model/consultas.model.php";
    
    // Crear la clase AJAX inline para evitar problemas de rutas
    class ConsultaAjaxTest {
        public function ajaxGetDetalleConsulta($idConsulta) {
            echo "<!-- DEBUG: Ejecutando ajaxGetDetalleConsulta con ID: $idConsulta -->\n";
            
            try {
                $response = ModelConsulta::mdlGetDetalleConsulta($idConsulta);
                
                if (is_string($response)) {
                    echo $response;
                } else {
                    echo json_encode($response);
                }
            } catch (Exception $e) {
                echo json_encode([
                    'error' => true,
                    'message' => 'Error: ' . $e->getMessage()
                ]);
            }
        }
    }
    
    // Ejecutar la lógica del AJAX
    if (isset($_POST["id_consulta"]) && isset($_POST["operacion"]) && $_POST["operacion"] === "detalleConsulta") {
        echo "<!-- Condición cumplida -->\n";
        $detalleConsulta = new ConsultaAjaxTest();
        $detalleConsulta->ajaxGetDetalleConsulta($_POST["id_consulta"]);
    } else {
        echo json_encode(['error' => 'Condición no cumplida']);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'error' => true,
        'message' => 'Exception: ' . $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
}

$output = ob_get_clean();

// Mostrar la salida raw
echo "<strong>Salida Raw:</strong><br>";
echo "<pre>" . htmlspecialchars($output) . "</pre>";

echo "</div>";

// Intentar decodificar como JSON para verificar
echo "<h3>Análisis de la respuesta:</h3>";
$cleanOutput = trim(preg_replace('/<!--.*?-->/s', '', $output));
$decoded = json_decode($cleanOutput, true);

if ($decoded !== null) {
    echo "✅ JSON válido<br>";
    echo "<pre>" . print_r($decoded, true) . "</pre>";
} else {
    echo "❌ No es JSON válido<br>";
    echo "JSON Error: " . json_last_error_msg() . "<br>";
    echo "Contenido limpio: " . htmlspecialchars($cleanOutput) . "<br>";
}

// Restaurar directorio original
chdir($originalDir);
?>
