<?php
// Test directo del AJAX para diagnosticar por qué no cargan los datos

header('Content-Type: text/html; charset=UTF-8');
echo "<h2>Diagnóstico AJAX - Turnos</h2>";

// 1. Test directo del endpoint
echo "<h3>1. Test directo del endpoint AJAX</h3>";

// Simular la petición POST
$_POST["accion"] = "obtenerTurnos";

echo "<p>Datos POST enviados:</p>";
echo "<pre>";
var_dump($_POST);
echo "</pre>";

echo "<p>Ejecutando ajax/turnos.ajax.php...</p>";

// Capturar la salida
ob_start();
try {
    include 'ajax/turnos.ajax.php';
    $output = ob_get_clean();
    
    echo "<h4>Respuesta del servidor:</h4>";
    echo "<pre>" . htmlspecialchars($output) . "</pre>";
    
    // Verificar si es JSON válido
    $json = json_decode($output, true);
    if($json !== null) {
        echo "<p style='color: green;'>✅ JSON válido</p>";
        echo "<p>Tipo de dato: " . gettype($json) . "</p>";
        if(is_array($json)) {
            echo "<p>Total de elementos: " . count($json) . "</p>";
            if(count($json) > 0) {
                echo "<h4>Primer elemento:</h4>";
                echo "<pre>";
                print_r($json[0]);
                echo "</pre>";
            }
        }
    } else {
        echo "<p style='color: red;'>❌ JSON inválido: " . json_last_error_msg() . "</p>";
    }
    
} catch(Exception $e) {
    $output = ob_get_clean();
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
    echo "<p>Output capturado: " . htmlspecialchars($output) . "</p>";
}

// 2. Test directo de la base de datos
echo "<h3>2. Test directo de la base de datos</h3>";

try {
    require_once 'model/conexion.php';
    $conexion = Conexion::conectar();
    
    if($conexion) {
        echo "<p style='color: green;'>✅ Conexión a DB exitosa</p>";
        
        // Query directo
        $stmt = $conexion->prepare("SELECT * FROM turnos ORDER BY turno_id DESC");
        $stmt->execute();
        $turnos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<p>Total de registros en DB: " . count($turnos) . "</p>";
        
        if(count($turnos) == 0) {
            echo "<p style='color: orange;'>⚠️ Tabla vacía. Insertando datos de prueba...</p>";
            
            $insert = $conexion->prepare("
                INSERT INTO turnos (turno_nombre, turno_descripcion, turno_estado) VALUES 
                ('Mañana', 'Turno matutino de 08:00 a 12:00', true),
                ('Tarde', 'Turno vespertino de 14:00 a 18:00', true),
                ('Noche', 'Turno nocturno de 20:00 a 24:00', true)
                ON CONFLICT (turno_nombre) DO NOTHING
            ");
            
            if($insert->execute()) {
                echo "<p style='color: green;'>✅ Datos insertados</p>";
                
                // Volver a consultar
                $stmt->execute();
                $turnos = $stmt->fetchAll(PDO::FETCH_ASSOC);
                echo "<p>Nuevos registros: " . count($turnos) . "</p>";
            }
        }
        
        if(count($turnos) > 0) {
            echo "<h4>Datos en la tabla:</h4>";
            echo "<table border='1' style='border-collapse: collapse;'>";
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
        }
        
    } else {
        echo "<p style='color: red;'>❌ Error de conexión a DB</p>";
    }
    
} catch(Exception $e) {
    echo "<p style='color: red;'>❌ Error en DB: " . $e->getMessage() . "</p>";
}

// 3. Test del modelo
echo "<h3>3. Test del modelo TurnosModel</h3>";

try {
    require_once 'model/TurnosModel.php';
    
    $turnos = TurnosModel::mdlMostrarTurnos("turnos", null, null);
    
    echo "<p>Resultado del modelo:</p>";
    echo "<pre>";
    var_dump($turnos);
    echo "</pre>";
    
    if(is_array($turnos)) {
        echo "<p style='color: green;'>✅ Modelo devuelve array con " . count($turnos) . " elementos</p>";
    } else {
        echo "<p style='color: red;'>❌ Modelo no devuelve array</p>";
    }
    
} catch(Exception $e) {
    echo "<p style='color: red;'>❌ Error en modelo: " . $e->getMessage() . "</p>";
}

echo "<h3>🔗 Enlaces de prueba</h3>";
echo "<p><a href='ajax/turnos.ajax.php' target='_blank'>→ Probar AJAX directo</a></p>";
echo "<p><a href='index.php?ruta=turnos'>→ Volver al módulo de turnos</a></p>";
?>
