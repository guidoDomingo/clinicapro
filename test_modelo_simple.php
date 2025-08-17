<?php
// Test simple del modelo de consultas
require_once "model/conexion.php";
require_once "model/consultas.model.php";

echo "<h1>Test del Modelo de Consultas</h1>";

try {
    echo "<h2>Test 1: Conexión a la base de datos</h2>";
    $db = Conexion::conectar();
    if ($db) {
        echo "✅ Conexión exitosa<br>";
        echo "Tipo de conexión: " . get_class($db) . "<br>";
    } else {
        echo "❌ Error de conexión<br>";
    }
    
    echo "<h2>Test 2: Obtener detalle de consulta</h2>";
    echo "Intentando obtener consulta con ID 1...<br>";
    
    // Probar el método del modelo directamente
    $response = ModelConsulta::mdlGetDetalleConsulta("1");
    
    echo "<h3>Respuesta del modelo:</h3>";
    echo "<pre>";
    if (is_string($response)) {
        echo "Tipo: string\n";
        echo "Contenido: " . htmlspecialchars($response) . "\n";
        
        // Intentar decodificar JSON si es string
        $decoded = json_decode($response, true);
        if ($decoded !== null) {
            echo "\nJSON válido decodificado:\n";
            print_r($decoded);
        } else {
            echo "\nNo es JSON válido o hay error en decodificación\n";
            echo "JSON Error: " . json_last_error_msg() . "\n";
        }
    } else {
        echo "Tipo: " . gettype($response) . "\n";
        print_r($response);
    }
    echo "</pre>";
    
    echo "<h2>Test 3: Verificar estructura de tabla consultas</h2>";
    $stmt = $db->query("DESCRIBE consultas LIMIT 5");
    if ($stmt) {
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['Field']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Type']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Null']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Key']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Default']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Extra']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    echo "<h2>Test 4: Consultas existentes</h2>";
    $stmt = $db->query("SELECT id_consulta, id_persona, fecha_consulta FROM consultas LIMIT 3");
    if ($stmt) {
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>ID Consulta</th><th>ID Persona</th><th>Fecha</th></tr>";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['id_consulta']) . "</td>";
            echo "<td>" . htmlspecialchars($row['id_persona']) . "</td>";
            echo "<td>" . htmlspecialchars($row['fecha_consulta']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
} catch (Exception $e) {
    echo "<h2>❌ Error:</h2>";
    echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}
?>
