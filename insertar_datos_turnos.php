<?php
// Script para asegurar que hay datos en la tabla turnos

require_once 'model/conexion.php';

try {
    $conexion = Conexion::conectar();
    
    echo "<h2>Verificando datos en tabla turnos</h2>";
    
    // Contar registros
    $stmt = $conexion->prepare("SELECT COUNT(*) as total FROM turnos");
    $stmt->execute();
    $total = $stmt->fetchColumn();
    
    echo "<p>Total de registros actuales: <strong>$total</strong></p>";
    
    if($total == 0) {
        echo "<p style='color: orange;'>⚠️ Tabla vacía. Insertando datos de ejemplo...</p>";
        
        $insert = $conexion->prepare("
            INSERT INTO turnos (turno_nombre, turno_descripcion, turno_estado) VALUES 
            ('Mañana', 'Turno matutino de 08:00 a 12:00', true),
            ('Tarde', 'Turno vespertino de 14:00 a 18:00', true),
            ('Noche', 'Turno nocturno de 20:00 a 24:00', true)
        ");
        
        if($insert->execute()) {
            echo "<p style='color: green;'>✅ Datos insertados exitosamente</p>";
            
            // Verificar nuevamente
            $stmt->execute();
            $total = $stmt->fetchColumn();
            echo "<p>Nuevos total de registros: <strong>$total</strong></p>";
        } else {
            echo "<p style='color: red;'>❌ Error al insertar datos</p>";
        }
    }
    
    // Mostrar todos los datos
    $mostrar = $conexion->prepare("SELECT * FROM turnos ORDER BY turno_id");
    $mostrar->execute();
    $turnos = $mostrar->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>Datos en la tabla:</h3>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>ID</th><th>Nombre</th><th>Descripción</th><th>Estado</th><th>Fecha Creación</th></tr>";
    
    foreach($turnos as $turno) {
        echo "<tr>";
        echo "<td>" . $turno['turno_id'] . "</td>";
        echo "<td>" . $turno['turno_nombre'] . "</td>";
        echo "<td>" . ($turno['turno_descripcion'] ?: 'Sin descripción') . "</td>";
        echo "<td>" . ($turno['turno_estado'] ? 'Activo' : 'Inactivo') . "</td>";
        echo "<td>" . $turno['fecha_creacion'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<h3>Test del AJAX:</h3>";
    
    // Simular petición AJAX
    $_POST["accion"] = "obtenerTurnos";
    
    ob_start();
    include 'ajax/turnos.ajax.php';
    $output = ob_get_clean();
    
    echo "<p><strong>Respuesta AJAX:</strong></p>";
    echo "<pre>" . htmlspecialchars($output) . "</pre>";
    
    $json = json_decode($output, true);
    if($json !== null) {
        echo "<p style='color: green;'>✅ JSON válido con " . count($json) . " elementos</p>";
    } else {
        echo "<p style='color: red;'>❌ JSON inválido: " . json_last_error_msg() . "</p>";
    }
    
} catch(Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

echo "<p><a href='index.php?ruta=turnos'>→ Probar módulo de turnos</a></p>";
?>
