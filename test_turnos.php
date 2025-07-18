<?php
// Test AJAX turnos

echo "<h2>Test AJAX Turnos</h2>";

// Test 1: Verificar que el archivo AJAX existe
$ajaxFile = __DIR__ . "/ajax/turnos.ajax.php";
if(file_exists($ajaxFile)) {
    echo "<p style='color: green;'>✅ Archivo AJAX existe: $ajaxFile</p>";
} else {
    echo "<p style='color: red;'>❌ Archivo AJAX no existe: $ajaxFile</p>";
}

// Test 2: Simular petición AJAX para obtener turnos
echo "<h3>Test: Obtener Turnos</h3>";

$_POST = array('accion' => 'obtenerTurnos');

ob_start();
include 'ajax/turnos.ajax.php';
$response = ob_get_clean();

echo "<p><strong>Respuesta:</strong></p>";
echo "<pre style='background: #f4f4f4; padding: 10px; border: 1px solid #ddd;'>";
echo htmlspecialchars($response);
echo "</pre>";

// Test 3: Verificar conexión directa con modelo
echo "<h3>Test: Conexión Directa con Modelo</h3>";

require_once 'model/TurnosModel.php';

try {
    $turnos = TurnosModel::mdlMostrarTurnos('turnos', null, null);
    echo "<p style='color: green;'>✅ Conexión con modelo exitosa</p>";
    echo "<p>Número de turnos encontrados: " . (is_array($turnos) ? count($turnos) : 0) . "</p>";
    
    if(is_array($turnos) && count($turnos) > 0) {
        echo "<h4>Datos de turnos:</h4>";
        echo "<pre style='background: #f4f4f4; padding: 10px; border: 1px solid #ddd;'>";
        print_r($turnos);
        echo "</pre>";
    } else {
        echo "<p style='color: orange;'>⚠️ No se encontraron turnos en la base de datos</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error en modelo: " . $e->getMessage() . "</p>";
}

// Test 4: Verificar tabla en base de datos
echo "<h3>Test: Verificar Tabla en Base de Datos</h3>";

require_once 'model/conexion.php';

try {
    $conexion = Conexion::conectar();
    
    // Verificar si la tabla existe
    $stmt = $conexion->prepare("SELECT count(*) FROM turnos");
    $stmt->execute();
    $count = $stmt->fetchColumn();
    
    echo "<p style='color: green;'>✅ Tabla 'turnos' existe con $count registros</p>";
    
    // Mostrar algunos registros
    if($count > 0) {
        $stmt = $conexion->prepare("SELECT * FROM turnos LIMIT 3");
        $stmt->execute();
        $turnos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h4>Primeros registros:</h4>";
        echo "<pre style='background: #f4f4f4; padding: 10px; border: 1px solid #ddd;'>";
        print_r($turnos);
        echo "</pre>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error en base de datos: " . $e->getMessage() . "</p>";
}

// Test 5: Verificar JavaScript
echo "<h3>Test: Verificar JavaScript</h3>";

$jsFile = __DIR__ . "/view/js/turnos.js";
if(file_exists($jsFile)) {
    echo "<p style='color: green;'>✅ Archivo JavaScript existe: $jsFile</p>";
} else {
    echo "<p style='color: red;'>❌ Archivo JavaScript no existe: $jsFile</p>";
}

echo "<h3>Enlaces de navegación:</h3>";
echo "<p><a href='index.php?ruta=turnos' target='_blank'>→ Ir al módulo de turnos</a></p>";
echo "<p><a href='ajax/turnos.ajax.php' target='_blank'>→ Probar AJAX directamente</a></p>";

?>
