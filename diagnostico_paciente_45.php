<?php
// Script de diagnóstico para verificar por qué no carga el paciente ID 45
echo "<h2>🔍 Diagnóstico: Paciente ID 45</h2>";

// Establecer sesión
session_start();
$_SESSION['id_usuario'] = 1;
$_SESSION['nombre_usuario'] = 'admin';
$_SESSION['rol'] = 'admin';
$_SESSION['permisos'] = ['ver_consultas', 'crear_consultas', 'editar_consultas'];

echo "<h3>1. ✅ Verificar que el paciente existe en la base de datos:</h3>";
try {
    require_once 'model/conexion.php';
    $conexion = Conexion::conectar();
    
    $query = "SELECT * FROM personas WHERE id_persona = 45";
    $stmt = $conexion->prepare($query);
    $stmt->execute();
    $paciente = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($paciente) {
        echo "<div style='background: #d4edda; padding: 10px; border-radius: 5px; border: 1px solid #c3e6cb;'>";
        echo "<strong>✅ Paciente encontrado:</strong><br>";
        echo "ID: " . $paciente['id_persona'] . "<br>";
        echo "Nombre: " . $paciente['nombre'] . "<br>";
        echo "Apellido: " . $paciente['apellido'] . "<br>";
        echo "Documento: " . $paciente['documento'] . "<br>";
        echo "Ficha: " . $paciente['ficha'] . "<br>";
        echo "Teléfono: " . $paciente['telefono'] . "<br>";
        echo "</div>";
    } else {
        echo "<div style='background: #f8d7da; padding: 10px; border-radius: 5px; border: 1px solid #f5c6cb;'>";
        echo "❌ <strong>PROBLEMA:</strong> No se encontró el paciente con ID 45";
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 10px; border-radius: 5px;'>";
    echo "❌ <strong>ERROR de base de datos:</strong> " . $e->getMessage();
    echo "</div>";
}

echo "<h3>2. 🔧 Verificar el archivo AJAX que debería responder:</h3>";
$ajaxFile = 'ajax/persona.ajax.php';
if (file_exists($ajaxFile)) {
    echo "✅ Archivo ajax/persona.ajax.php existe<br>";
    
    // Simular la petición AJAX
    echo "<h4>🧪 Simulando petición AJAX:</h4>";
    
    $_POST['operacion'] = 'getPersonById';
    $_POST['idPersona'] = '45';
    
    echo "<div style='background: #e2e3e5; padding: 10px; border-radius: 5px; font-family: monospace;'>";
    echo "POST data simulada:<br>";
    echo "operacion: getPersonById<br>";
    echo "idPersona: 45<br>";
    echo "</div>";
    
    echo "<h4>📡 Resultado de la petición AJAX:</h4>";
    echo "<iframe src='ajax/persona.ajax.php' style='width: 100%; height: 200px; border: 1px solid #ccc;'></iframe>";
    
} else {
    echo "❌ <strong>PROBLEMA:</strong> No se encontró el archivo ajax/persona.ajax.php";
}

echo "<h3>3. 📋 JavaScript de verificación:</h3>";
echo "<div style='background: #fff3cd; padding: 10px; border-radius: 5px;'>";
echo "<strong>Abrir la consola del navegador (F12) y ejecutar:</strong><br>";
echo "<code>console.log('Testing paciente load'); buscarPersonaPorId(45);</code>";
echo "</div>";

echo "<h3>4. 🌐 URLs de prueba directa:</h3>";
echo "<ul>";
echo "<li><a href='ajax/persona.ajax.php?operacion=getPersonById&idPersona=45' target='_blank'>Prueba AJAX directa (GET)</a></li>";
echo "<li><a href='index.php?ruta=consultas&form_type=general&paciente_id=45' target='_blank'>URL actual (recargar)</a></li>";
echo "</ul>";

echo "<h3>5. 🔄 Limpiar y reintentar:</h3>";
echo "<button onclick='clearStorageAndReload()' style='padding: 10px; background: #dc3545; color: white; border: none; border-radius: 3px; cursor: pointer;'>🧹 Limpiar SessionStorage y Recargar</button>";

echo "<script>";
echo "function clearStorageAndReload() {";
echo "  sessionStorage.clear();";
echo "  localStorage.clear();";
echo "  console.log('Storage limpiado');";
echo "  window.location.href = 'index.php?ruta=consultas&form_type=general&paciente_id=45';";
echo "}";
echo "</script>";
?>
