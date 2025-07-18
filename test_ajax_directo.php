<?php
// Test directo del endpoint AJAX
echo "<h2>Test AJAX Endpoint</h2>";

// Simular POST
$_POST["accion"] = "obtenerTurnos";

echo "<p>POST data simulado: ";
var_dump($_POST);
echo "</p>";

echo "<h3>Incluir archivo AJAX:</h3>";

// Capturar errores
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Capturar salida
ob_start();

try {
    include 'ajax/turnos.ajax.php';
    $output = ob_get_clean();
    
    echo "<h4>Salida del AJAX:</h4>";
    echo "<pre>" . htmlspecialchars($output) . "</pre>";
    
    // Intentar decodificar como JSON
    $json = json_decode($output, true);
    if($json !== null) {
        echo "<h4>JSON decodificado:</h4>";
        echo "<pre>";
        var_dump($json);
        echo "</pre>";
    } else {
        echo "<p style='color: red;'>❌ La salida no es JSON válido</p>";
        echo "<p>Error JSON: " . json_last_error_msg() . "</p>";
    }
    
} catch(Exception $e) {
    $output = ob_get_clean();
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
    echo "<p>Salida capturada: " . htmlspecialchars($output) . "</p>";
}

echo "<h3>Test directo del modelo:</h3>";

try {
    require_once 'model/TurnosModel.php';
    
    $turnos = TurnosModel::mdlMostrarTurnos("turnos", null, null);
    
    echo "<h4>Resultado del modelo:</h4>";
    echo "<pre>";
    var_dump($turnos);
    echo "</pre>";
    
    if(is_array($turnos)) {
        echo "<p>Total de turnos: " . count($turnos) . "</p>";
    }
    
} catch(Exception $e) {
    echo "<p style='color: red;'>❌ Error en modelo: " . $e->getMessage() . "</p>";
}

echo "<h3>Test de conexión a base de datos:</h3>";

try {
    require_once 'model/conexion.php';
    
    $conexion = Conexion::conectar();
    echo "<p style='color: green;'>✅ Conexión exitosa</p>";
    
    // Query directo
    $stmt = $conexion->prepare("SELECT * FROM turnos ORDER BY turno_id DESC");
    $stmt->execute();
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h4>Query directo:</h4>";
    echo "<pre>";
    var_dump($resultados);
    echo "</pre>";
    
    echo "<p>Total registros: " . count($resultados) . "</p>";
    
} catch(Exception $e) {
    echo "<p style='color: red;'>❌ Error en conexión: " . $e->getMessage() . "</p>";
}
?>
