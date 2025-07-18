<?php
// Test directo del AJAX de turnos

echo "<h2>Test AJAX Turnos</h2>";

// Simular POST request para obtener turnos
$_POST["accion"] = "obtenerTurnos";

// Capturar la salida
ob_start();
include 'ajax/turnos.ajax.php';
$output = ob_get_clean();

echo "<h3>Respuesta AJAX:</h3>";
echo "<pre>";
echo htmlspecialchars($output);
echo "</pre>";

// Test directo del modelo
echo "<h3>Test directo del modelo:</h3>";
require_once 'model/TurnosModel.php';

try {
    $turnos = TurnosModel::mdlMostrarTurnos("turnos", null, null);
    echo "<pre>";
    var_dump($turnos);
    echo "</pre>";
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}

// Test de conexión a base de datos
echo "<h3>Test de conexión:</h3>";
require_once 'model/conexion.php';

try {
    $conexion = Conexion::conectar();
    echo "<p style='color: green;'>✅ Conexión exitosa</p>";
    
    // Verificar datos en la tabla
    $stmt = $conexion->prepare("SELECT COUNT(*) as total FROM turnos");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<p>Total registros en tabla turnos: " . $result['total'] . "</p>";
    
    if($result['total'] == 0) {
        echo "<p style='color: orange;'>⚠️ La tabla está vacía. Insertando datos de prueba...</p>";
        
        $insert = $conexion->prepare("
            INSERT INTO turnos (turno_nombre, turno_descripcion) VALUES 
            ('Mañana', 'Turno matutino de 08:00 a 12:00'),
            ('Tarde', 'Turno vespertino de 14:00 a 18:00'),
            ('Noche', 'Turno nocturno de 20:00 a 24:00')
        ");
        
        if($insert->execute()) {
            echo "<p style='color: green;'>✅ Datos insertados</p>";
        } else {
            echo "<p style='color: red;'>❌ Error al insertar datos</p>";
        }
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error de conexión: " . $e->getMessage() . "</p>";
}
?>
