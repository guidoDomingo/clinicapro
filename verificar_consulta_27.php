<?php
// Verificar consulta ID 27
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include_once "model/conexion.php";

echo "<!DOCTYPE html><html><head><title>Consulta 27</title></head><body>";
echo "<h1>Información de Consulta ID 27</h1>";

try {
    $pdo = Conexion::conectar();
    
    $stmt = $pdo->prepare("SELECT * FROM consultas WHERE id_consulta = 27");
    $stmt->execute();
    $consulta = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($consulta) {
        echo "<h2>✅ Consulta encontrada:</h2>";
        echo "<table border='1' style='border-collapse: collapse;'>";
        foreach ($consulta as $campo => $valor) {
            echo "<tr><td><strong>$campo</strong></td><td>$valor</td></tr>";
        }
        echo "</table>";
        
        echo "<h3>Información clave:</h3>";
        echo "<strong>Tipo de formulario:</strong> " . ($consulta['tipo_formulario'] ?? 'NULL/No definido') . "<br>";
        echo "<strong>ID Paciente:</strong> " . ($consulta['id_persona'] ?? 'NULL') . "<br>";
        echo "<strong>Fecha consulta:</strong> " . ($consulta['fecha_consulta'] ?? 'NULL') . "<br>";
        
    } else {
        echo "<h2>❌ No se encontró consulta con ID 27</h2>";
    }
    
} catch (Exception $e) {
    echo "<h2>❌ Error: " . $e->getMessage() . "</h2>";
}

echo "</body></html>";
?>
